<?php

namespace App\Services\Interfaces;

use App\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CityManagementServiceInterface
{
    /**
     * Lấy danh sách các tỉnh / thành phố kèm số lượng điểm đến và bộ lọc.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCities(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Lấy thông tin chi tiết một tỉnh / thành phố theo ID.
     *
     * @param int $cityId
     * @return City|null
     */
    public function getCityById(int $cityId): ?City;

    /**
     * Tạo mới một tỉnh / thành phố.
     *
     * @param array $data
     * @return City
     */
    public function createCity(array $data): City;

    /**
     * Cập nhật thông tin tỉnh / thành phố.
     *
     * @param int $cityId
     * @param array $data
     * @return City
     */
    public function updateCity(int $cityId, array $data): City;

    /**
     * Xóa một tỉnh / thành phố khỏi hệ thống (Bảo vệ toàn vẹn dữ liệu BR-17).
     *
     * @param int $cityId
     * @return bool
     */
    public function deleteCity(int $cityId): bool;

    /**
     * Lấy thống kê tổng quan về tỉnh / thành phố và điểm đến du lịch.
     *
     * @return array
     */
    public function getCityStatistics(): array;
}
