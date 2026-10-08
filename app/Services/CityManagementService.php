<?php

namespace App\Services;

use App\Models\City;
use App\Models\Destination;
use App\Services\Interfaces\CityManagementServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CityManagementService implements CityManagementServiceInterface
{
    /**
     * Lấy danh sách các tỉnh / thành phố kèm số lượng điểm đến và bộ lọc.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCities(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = City::query()->withCount('destinations');

        // Lọc theo từ khóa tìm kiếm (tên, mã tỉnh/thành hoặc khu vực)
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%");
            });
        }

        // Lọc theo khu vực (miền)
        if (! empty($filters['region']) && $filters['region'] !== 'all') {
            $query->where('region', $filters['region']);
        }

        // Lọc theo trạng thái hiển thị
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $query->where('is_active', (bool) $filters['status']);
        }

        return $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();
    }

    /**
     * Lấy thông tin chi tiết một tỉnh / thành phố theo ID.
     *
     * @param int $cityId
     * @return City|null
     */
    public function getCityById(int $cityId): ?City
    {
        return City::withCount('destinations')->find($cityId);
    }

    /**
     * Tạo mới một tỉnh / thành phố.
     *
     * @param array $data
     * @return City
     */
    public function createCity(array $data): City
    {
        return DB::transaction(function () use ($data) {
            $name = trim($data['name']);
            $slug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($name);

            // Đảm bảo tính duy nhất của slug
            $originalSlug = $slug;
            $counter = 1;
            while (City::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$counter}";
                $counter++;
            }

            // Tự động sinh mã thành phố nếu để trống (Định dạng CTY-XXX)
            $code = ! empty($data['code']) ? strtoupper(trim($data['code'])) : $this->generateCityCode($name);

            // Xử lý tệp hình ảnh tải lên hoặc URL ảnh
            $imageUrl = $this->handleImageUpload($data);

            $city = City::create([
                'name' => $name,
                'slug' => $slug,
                'code' => $code,
                'region' => ! empty($data['region']) ? trim($data['region']) : null,
                'description' => $data['description'] ?? null,
                'image' => $imageUrl,
                'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            ]);

            return $city;
        });
    }

    /**
     * Cập nhật thông tin tỉnh / thành phố.
     *
     * @param int $cityId
     * @param array $data
     * @return City
     */
    public function updateCity(int $cityId, array $data): City
    {
        return DB::transaction(function () use ($cityId, $data) {
            $city = City::findOrFail($cityId);

            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = trim($data['name']);
            }

            if (isset($data['slug'])) {
                $updateData['slug'] = Str::slug($data['slug']);
            }

            if (isset($data['code'])) {
                $updateData['code'] = strtoupper(trim($data['code']));
            }

            if (array_key_exists('region', $data)) {
                $updateData['region'] = ! empty($data['region']) ? trim($data['region']) : null;
            }

            if (array_key_exists('description', $data)) {
                $updateData['description'] = $data['description'];
            }

            if (isset($data['is_active'])) {
                $updateData['is_active'] = (bool) $data['is_active'];
            }

            // Xử lý cập nhật ảnh mới nếu có tải lên
            $newImageUrl = $this->handleImageUpload($data);
            if ($newImageUrl !== null) {
                $updateData['image'] = $newImageUrl;
            }

            $city->update($updateData);

            return $city;
        });
    }

    /**
     * Xóa một tỉnh / thành phố khỏi hệ thống (Bảo vệ toàn vẹn dữ liệu BR-17).
     *
     * @param int $cityId
     * @return bool
     * @throws ValidationException
     */
    public function deleteCity(int $cityId): bool
    {
        return DB::transaction(function () use ($cityId) {
            $city = City::findOrFail($cityId);

            // Kiểm tra quy tắc nghiệp vụ BR-17: Không xóa dữ liệu nếu đang có quan hệ ràng buộc
            if ($city->destinations()->exists()) {
                throw ValidationException::withMessages([
                    'delete' => "Không thể xóa tỉnh/thành phố '{$city->name}' vì đang có {$city->destinations()->count()} địa điểm du lịch liên kết. Vui lòng di chuyển hoặc xóa các địa điểm trước theo quy tắc bảo vệ toàn vẹn dữ liệu BR-17.",
                ]);
            }

            return (bool) $city->delete();
        });
    }

    /**
     * Lấy thống kê tổng quan về tỉnh / thành phố và điểm đến du lịch.
     *
     * @return array
     */
    public function getCityStatistics(): array
    {
        return [
            'total_cities' => City::count(),
            'active_cities' => City::where('is_active', true)->count(),
            'total_destinations' => Destination::count(),
            'regions_count' => City::whereNotNull('region')->distinct()->count('region'),
        ];
    }

    /**
     * Xử lý lưu trữ tệp hình ảnh tải lên hoặc trả về URL.
     *
     * @param array $data
     * @return string|null
     */
    protected function handleImageUpload(array $data): ?string
    {
        if (isset($data['image_file']) && $data['image_file'] instanceof UploadedFile) {
            $path = $data['image_file']->store('cities', 'public');
            return Storage::disk('public')->url($path);
        }

        if (! empty($data['image']) && is_string($data['image'])) {
            return trim($data['image']);
        }

        return null;
    }

    /**
     * Tự động sinh mã tỉnh / thành phố chuẩn CTY-XXX.
     *
     * @param string $name
     * @return string
     */
    protected function generateCityCode(string $name): string
    {
        // Chuyển tiếng Việt có dấu sang không dấu
        $ascii = Str::ascii($name);
        $words = explode(' ', $ascii);
        $letters = '';

        foreach ($words as $word) {
            if (! empty($word)) {
                $letters .= strtoupper(substr($word, 0, 1));
            }
        }

        if (strlen($letters) < 3) {
            $cleanAscii = preg_replace('/[^A-Za-z0-9]/', '', $ascii);
            $letters = strtoupper(substr($cleanAscii, 0, 3));
        }

        $code = 'CTY-' . substr($letters, 0, 3);
        $counter = 1;

        while (City::where('code', $code)->exists()) {
            $code = 'CTY-' . substr($letters, 0, 3) . $counter;
            $counter++;
        }

        return $code;
    }
}
