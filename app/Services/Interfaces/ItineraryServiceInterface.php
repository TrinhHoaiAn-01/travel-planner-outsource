<?php

namespace App\Services\Interfaces;

use App\Models\Trip;

/**
 * Ràng buộc Service Interface cho chức năng Quản lý lịch trình chuyến đi.
 * Thành viên phụ trách: Phạm Thị Ngọc Ái (MSSV: 24211TT3767).
 */
interface ItineraryServiceInterface
{
    /**
     * Lấy dữ liệu chi tiết lịch trình theo từng ngày của chuyến đi.
     *
     * @param Trip $trip Chuyến đi cần hiển thị lịch trình
     * @param int|null $dayNumber Ngày cụ thể người dùng chọn (mặc định lấy ngày 1)
     * @return array<string, mixed> Mảng dữ liệu gồm trip, days_count, current_day, days_list, items, total_activities_count
     */
    public function getTripItinerary(Trip $trip, ?int $dayNumber = null): array;
}
