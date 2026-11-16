<?php

namespace App\Services\Interfaces;

use App\Models\RoomType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface Quản lý Hạng phòng lưu trú dành cho Quản trị viên (Admin Room Type Management).
 * Thành viên phụ trách: Trần Văn Trọng (22211TT0337) - Mục 10 theo Phân công Đồ án.
 * Tuân thủ nghiêm ngặt chuẩn kiến trúc MVC 3 lớp và Service Interface.
 */
interface RoomTypeManagementServiceInterface
{
    /**
     * Lấy danh sách phân trang các loại phòng kèm bộ lọc tìm kiếm và địa điểm.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getRoomTypes(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy dữ liệu thống kê tổng quan về các loại phòng trong hệ thống.
     *
     * @return array<string, mixed>
     */
    public function getRoomTypeStatistics(): array;

    /**
     * Lấy danh sách địa điểm du lịch để hiển thị trong dropdown chọn địa điểm cho phòng.
     *
     * @return Collection
     */
    public function getDestinationsForSelect(): Collection;

    /**
     * Thêm mới một loại phòng lưu trú vào hệ thống.
     *
     * @param array<string, mixed> $data
     * @return RoomType
     */
    public function createRoomType(array $data): RoomType;

    /**
     * Cập nhật thông tin chi tiết một loại phòng lưu trú.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return RoomType
     */
    public function updateRoomType(int $id, array $data): RoomType;

    /**
     * Xóa một loại phòng lưu trú khỏi hệ thống (kiểm tra an toàn đơn đặt phòng).
     *
     * @param int $id
     * @return bool
     */
    public function deleteRoomType(int $id): bool;
}

