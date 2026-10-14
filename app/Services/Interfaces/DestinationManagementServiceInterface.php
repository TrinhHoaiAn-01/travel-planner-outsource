<?php

namespace App\Services\Interfaces;

use App\Models\Destination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface DestinationManagementServiceInterface
{
    /**
     * Lấy danh sách điểm đến du lịch có phân trang và áp dụng các bộ lọc tìm kiếm.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getDestinations(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy thông tin chi tiết một điểm đến du lịch theo ID kèm các quan hệ liên quan.
     *
     * @param int $destinationId
     * @return Destination|null
     */
    public function getDestinationById(int $destinationId): ?Destination;

    /**
     * Tạo mới một điểm đến du lịch (Bọc trong Database Transaction).
     *
     * @param array $data
     * @return Destination
     */
    public function createDestination(array $data): Destination;

    /**
     * Cập nhật thông tin điểm đến du lịch.
     *
     * @param int $destinationId
     * @param array $data
     * @return Destination
     */
    public function updateDestination(int $destinationId, array $data): Destination;

    /**
     * Xóa một điểm đến du lịch (Tuân thủ quy tắc bảo vệ toàn vẹn dữ liệu BR-17).
     *
     * @param int $destinationId
     * @return bool
     */
    public function deleteDestination(int $destinationId): bool;

    /**
     * Lấy toàn bộ danh sách tỉnh / thành phố để phục vụ bộ lọc và lựa chọn khi thêm/sửa điểm đến.
     *
     * @return Collection
     */
    public function getCities(): Collection;

    /**
     * Lấy toàn bộ danh sách danh mục du lịch để phục vụ bộ lọc và lựa chọn khi thêm/sửa điểm đến.
     *
     * @return Collection
     */
    public function getCategories(): Collection;

    /**
     * Lấy số liệu thống kê tổng quan về điểm đến du lịch cho quản trị viên.
     *
     * @return array
     */
    public function getDestinationStatistics(): array;
}
