<?php

namespace App\Services\Interfaces;

use App\Models\DestinationImage;
use Illuminate\Database\Eloquent\Collection;

interface DestinationImageServiceInterface
{
    /**
     * Lấy danh sách hình ảnh theo địa điểm du lịch đã chọn.
     *
     * @param int|null $destinationId
     * @return Collection
     */
    public function getImagesByDestination(?int $destinationId = null): Collection;

    /**
     * Tải lên hoặc lưu trữ hình ảnh mới cho địa điểm du lịch (Bọc trong DB Transaction).
     *
     * @param array $data
     * @return DestinationImage
     */
    public function uploadImage(array $data): DestinationImage;

    /**
     * Đặt một hình ảnh làm ảnh đại diện chính (is_primary = true) của địa điểm.
     *
     * @param int $imageId
     * @return bool
     */
    public function setPrimaryImage(int $imageId): bool;

    /**
     * Xóa một hình ảnh khỏi thư viện.
     *
     * @param int $imageId
     * @return bool
     */
    public function deleteImage(int $imageId): bool;

    /**
     * Lấy danh sách toàn bộ địa điểm du lịch kèm tên thành phố để hiển thị trên dropdown bộ lọc.
     *
     * @return Collection
     */
    public function getDestinationsList(): Collection;
}
