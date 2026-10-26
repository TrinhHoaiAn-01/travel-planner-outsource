<?php

namespace App\Services;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\DestinationImage;
use App\Models\RoomType;
use App\Services\Interfaces\DestinationManagementServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DestinationManagementService implements DestinationManagementServiceInterface
{
    /**
     * Lấy danh sách điểm đến du lịch có phân trang và áp dụng các bộ lọc tìm kiếm.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDestinations(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Destination::with(['city', 'category', 'primaryImage', 'images', 'roomTypes']);

        // Bộ lọc 1: Tìm kiếm theo tên, địa chỉ hoặc mô tả
        if (! empty($filters['search'])) {
            $searchTerm = trim($filters['search']);
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('address', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Bộ lọc 2: Lọc theo Tỉnh / Thành phố
        if (! empty($filters['city_id']) && $filters['city_id'] !== 'all') {
            $query->where('city_id', $filters['city_id']);
        }

        // Bộ lọc 3: Lọc theo Danh mục du lịch
        if (! empty($filters['category_id']) && $filters['category_id'] !== 'all') {
            $query->where('category_id', $filters['category_id']);
        }

        // Sắp xếp theo ID tăng dần để các mã #DST-001, #DST-002 hiển thị thứ tự chuẩn
        return $query->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Lấy thông tin chi tiết một điểm đến du lịch theo ID kèm các quan hệ liên quan.
     *
     * @param int $destinationId
     * @return Destination|null
     */
    public function getDestinationById(int $destinationId): ?Destination
    {
        return Destination::with(['city', 'category', 'images', 'primaryImage', 'roomTypes'])->find($destinationId);
    }

    /**
     * Tạo mới một điểm đến du lịch (Bọc trong Database Transaction).
     *
     * @param array $data
     * @return Destination
     */
    public function createDestination(array $data): Destination
    {
        return DB::transaction(function () use ($data) {
            $name = trim($data['name']);
            $slug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($name);

            // Đảm bảo tính duy nhất của slug trong bảng destinations
            $originalSlug = $slug;
            $counter = 1;
            while (Destination::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-{$counter}";
                $counter++;
            }

            // Tạo bản ghi điểm đến chính
            $destination = Destination::create([
                'city_id' => $data['city_id'],
                'category_id' => $data['category_id'],
                'name' => $name,
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'entrance_fee' => isset($data['entrance_fee']) ? (float) $data['entrance_fee'] : 0,
                'opening_time' => $data['opening_time'] ?? null,
                'closing_time' => $data['closing_time'] ?? null,
                'duration' => isset($data['duration']) ? (int) $data['duration'] : null,
                'rating' => isset($data['rating']) ? (float) $data['rating'] : 0.0,
                'is_featured' => isset($data['is_featured']) ? (bool) $data['is_featured'] : false,
            ]);

            // Xử lý lưu hình ảnh đại diện (File upload hoặc URL)
            $primaryImagePath = $this->handleImageUpload($data);
            if (! empty($primaryImagePath)) {
                $destination->images()->create([
                    'image_path' => $primaryImagePath,
                    'is_primary' => true,
                ]);
            }

            // Nếu người dùng chọn "Có khách sạn / nơi lưu trú", tạo phòng mẫu mặc định
            if (! empty($data['has_hotel']) && $destination->roomTypes()->doesntExist()) {
                $destination->roomTypes()->create([
                    'name' => 'Phòng Tiêu Chuẩn (Standard Room)',
                    'capacity' => 2,
                    'price_per_night' => $destination->entrance_fee > 0 ? $destination->entrance_fee : 500000,
                    'quantity' => 10,
                    'description' => 'Phòng nghỉ tiện nghi tại ' . $destination->name,
                ]);
            }

            return $destination->fresh(['city', 'category', 'primaryImage', 'roomTypes']);
        });
    }

    /**
     * Cập nhật thông tin điểm đến du lịch.
     *
     * @param int $destinationId
     * @param array $data
     * @return Destination
     */
    public function updateDestination(int $destinationId, array $data): Destination
    {
        return DB::transaction(function () use ($destinationId, $data) {
            $destination = Destination::findOrFail($destinationId);

            $name = trim($data['name']);

            // Cập nhật slug nếu được chỉ định hoặc tên thay đổi
            if (! empty($data['slug'])) {
                $slug = Str::slug($data['slug']);
            } elseif ($destination->name !== $name) {
                $slug = Str::slug($name);
            } else {
                $slug = $destination->slug;
            }

            // Kiểm tra trùng lặp slug với điểm đến khác
            $originalSlug = $slug;
            $counter = 1;
            while (Destination::where('slug', $slug)->where('id', '!=', $destinationId)->exists()) {
                $slug = "{$originalSlug}-{$counter}";
                $counter++;
            }

            $updateData = [
                'city_id' => $data['city_id'],
                'category_id' => $data['category_id'],
                'name' => $name,
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'entrance_fee' => isset($data['entrance_fee']) ? (float) $data['entrance_fee'] : 0,
                'opening_time' => $data['opening_time'] ?? null,
                'closing_time' => $data['closing_time'] ?? null,
                'duration' => isset($data['duration']) ? (int) $data['duration'] : null,
                'rating' => isset($data['rating']) ? (float) $data['rating'] : $destination->rating,
                'is_featured' => isset($data['is_featured']) ? (bool) $data['is_featured'] : false,
            ];

            $destination->update($updateData);

            // Cập nhật ảnh đại diện mới nếu có tải lên
            $newImagePath = $this->handleImageUpload($data);
            if (! empty($newImagePath)) {
                // Đặt các ảnh hiện tại về is_primary = false
                $destination->images()->update(['is_primary' => false]);

                // Tạo hoặc cập nhật ảnh đại diện mới
                $destination->images()->create([
                    'image_path' => $newImagePath,
                    'is_primary' => true,
                ]);
            }

            // Quản lý trạng thái nơi lưu trú
            if (isset($data['has_hotel'])) {
                $hasHotel = (bool) $data['has_hotel'];
                if ($hasHotel && $destination->roomTypes()->doesntExist()) {
                    $destination->roomTypes()->create([
                        'name' => 'Phòng Tiêu Chuẩn (Standard Room)',
                        'capacity' => 2,
                        'price_per_night' => $destination->entrance_fee > 0 ? $destination->entrance_fee : 500000,
                        'quantity' => 10,
                        'description' => 'Phòng nghỉ tiện nghi tại ' . $destination->name,
                    ]);
                } elseif (! $hasHotel && $destination->roomTypes()->exists()) {
                    // Nếu tắt tùy chọn có khách sạn và chưa có đơn đặt phòng nào liên kết, cho phép gỡ phòng
                    $hasBookings = $destination->roomTypes()->whereHas('bookings')->exists();
                    if (! $hasBookings) {
                        $destination->roomTypes()->delete();
                    }
                }
            }

            return $destination->fresh(['city', 'category', 'primaryImage', 'roomTypes']);
        });
    }

    /**
     * Xóa một điểm đến du lịch (Tuân thủ quy tắc bảo vệ toàn vẹn dữ liệu BR-17).
     *
     * @param int $destinationId
     * @return bool
     * @throws ValidationException
     */
    public function deleteDestination(int $destinationId): bool
    {
        return DB::transaction(function () use ($destinationId) {
            $destination = Destination::findOrFail($destinationId);

            // Kiểm tra BR-17: Không xóa nếu đang được sử dụng trong lịch trình của người dùng
            if ($destination->itineraryItems()->exists()) {
                throw ValidationException::withMessages([
                    'delete' => "Không thể xóa địa điểm '{$destination->name}' vì đang có {$destination->itineraryItems()->count()} hoạt động trong lịch trình du lịch sử dụng địa điểm này (Tuân thủ BR-17).",
                ]);
            }

            // Kiểm tra BR-17: Không xóa nếu có phòng đang có đơn đặt phòng của khách hàng
            $hasActiveBookings = $destination->roomTypes()->whereHas('bookings')->exists();
            if ($hasActiveBookings) {
                throw ValidationException::withMessages([
                    'delete' => "Không thể xóa địa điểm '{$destination->name}' vì đang có đơn đặt phòng lưu trú liên quan đến địa điểm này (Tuân thủ BR-17).",
                ]);
            }

            // Xóa các loại phòng chưa có booking và hình ảnh thuộc về địa điểm
            $destination->roomTypes()->delete();
            $destination->images()->delete();

            return (bool) $destination->delete();
        });
    }

    /**
     * Lấy toàn bộ danh sách tỉnh / thành phố để phục vụ bộ lọc và form lựa chọn.
     *
     * @return Collection
     */
    public function getCities(): Collection
    {
        return City::orderBy('name', 'asc')->get();
    }

    /**
     * Lấy toàn bộ danh sách danh mục du lịch để phục vụ bộ lọc và form lựa chọn.
     *
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return Category::orderBy('name', 'asc')->get();
    }

    /**
     * Lấy số liệu thống kê tổng quan về điểm đến du lịch cho quản trị viên.
     *
     * @return array
     */
    public function getDestinationStatistics(): array
    {
        return [
            'total_destinations' => Destination::count(),
            'featured_destinations' => Destination::where('is_featured', true)->count(),
            'destinations_with_hotel' => Destination::whereHas('roomTypes')->count(),
            'average_rating' => round((float) Destination::avg('rating'), 1),
        ];
    }

    /**
     * Xử lý lưu trữ tệp hình ảnh tải lên hoặc sử dụng đường dẫn URL hợp lệ.
     *
     * @param array $data
     * @return string|null
     */
    private function handleImageUpload(array $data): ?string
    {
        if (isset($data['image_file']) && $data['image_file'] instanceof UploadedFile) {
            $file = $data['image_file'];
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'destination_' . Str::random(24) . '.' . $extension;

            // Lưu trữ an toàn trong thư mục public/destinations
            $path = $file->storeAs('destinations', $filename, 'public');

            return Storage::url($path);
        }

        if (! empty($data['image_url'])) {
            return trim($data['image_url']);
        }

        return null;
    }
}
