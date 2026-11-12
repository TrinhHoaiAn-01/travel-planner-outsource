<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\DestinationImage;
use App\Services\Interfaces\DestinationImageServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DestinationImageService implements DestinationImageServiceInterface
{
    /**
     * Lấy danh sách hình ảnh theo địa điểm du lịch đã chọn.
     * Sắp xếp ảnh đại diện lên đầu tiên, sau đó theo ID.
     *
     * @param int|null $destinationId
     * @return Collection
     */
    public function getImagesByDestination(?int $destinationId = null): Collection
    {
        $query = DestinationImage::with('destination');

        if ($destinationId !== null && $destinationId > 0) {
            $query->where('destination_id', $destinationId);
        }

        return $query->orderBy('is_primary', 'desc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Tải lên hoặc lưu trữ hình ảnh mới cho địa điểm du lịch (Bọc trong DB Transaction).
     *
     * @param array $data
     * @return DestinationImage
     * @throws ValidationException
     */
    public function uploadImage(array $data): DestinationImage
    {
        return DB::transaction(function () use ($data) {
            $destinationId = (int) $data['destination_id'];
            $destination = Destination::findOrFail($destinationId);

            $imagePath = $this->handleImageUpload($data);

            if (empty($imagePath)) {
                throw ValidationException::withMessages([
                    'image_file' => 'Vui lòng chọn tệp ảnh hoặc nhập đường dẫn URL hình ảnh hợp lệ.',
                ]);
            }

            $isPrimary = ! empty($data['is_primary']);

            // Nếu địa điểm chưa có ảnh nào thì tự động đặt ảnh đầu tiên làm ảnh đại diện
            if (! $isPrimary && $destination->images()->doesntExist()) {
                $isPrimary = true;
            }

            // Nếu đặt làm ảnh đại diện thì hủy trạng thái đại diện của các ảnh cũ
            if ($isPrimary) {
                $destination->images()->update(['is_primary' => false]);
            }

            return DestinationImage::create([
                'destination_id' => $destinationId,
                'image_path' => $imagePath,
                'is_primary' => $isPrimary,
            ]);
        });
    }

    /**
     * Đặt một hình ảnh làm ảnh đại diện chính của địa điểm.
     *
     * @param int $imageId
     * @return bool
     */
    public function setPrimaryImage(int $imageId): bool
    {
        return DB::transaction(function () use ($imageId) {
            $image = DestinationImage::findOrFail($imageId);

            // Đặt tất cả ảnh khác của cùng địa điểm về không phải đại diện
            DestinationImage::where('destination_id', $image->destination_id)
                ->update(['is_primary' => false]);

            $image->update(['is_primary' => true]);

            return true;
        });
    }

    /**
     * Xóa một hình ảnh khỏi thư viện.
     *
     * @param int $imageId
     * @return bool
     */
    public function deleteImage(int $imageId): bool
    {
        return DB::transaction(function () use ($imageId) {
            $image = DestinationImage::findOrFail($imageId);
            $wasPrimary = (bool) $image->is_primary;
            $destinationId = $image->destination_id;

            // Xóa file vật lý nếu được lưu trữ trong storage public
            if (! empty($image->image_path) && str_contains($image->image_path, '/storage/destinations/')) {
                $relativePath = str_replace('/storage/', '', $image->image_path);
                Storage::disk('public')->delete($relativePath);
            }

            $result = (bool) $image->delete();

            // Nếu xóa ảnh đại diện và vẫn còn các ảnh khác, tự động chọn ảnh kế tiếp làm ảnh đại diện
            if ($wasPrimary) {
                $nextImage = DestinationImage::where('destination_id', $destinationId)->first();
                if ($nextImage) {
                    $nextImage->update(['is_primary' => true]);
                }
            }

            return $result;
        });
    }

    /**
     * Lấy danh sách toàn bộ địa điểm du lịch kèm tên thành phố để hiển thị trên dropdown bộ lọc.
     *
     * @return Collection
     */
    public function getDestinationsList(): Collection
    {
        return Destination::with(['city', 'primaryImage', 'category'])
            ->orderBy('name', 'asc')
            ->get();
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
            $filename = 'gallery_' . Str::random(24) . '.' . $extension;

            $path = $file->storeAs('destinations', $filename, 'public');

            return Storage::url($path);
        }

        if (! empty($data['image_url'])) {
            return trim($data['image_url']);
        }

        return null;
    }
}
