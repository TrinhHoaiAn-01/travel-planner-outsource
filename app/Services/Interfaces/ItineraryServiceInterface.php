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

    /**
     * Lấy dữ liệu khởi tạo cho màn hình / modal thêm hoạt động vào lịch trình.
     * Tuyến: GET /trips/{trip}/itinerary/create (Bảng 22 Báo cáo Đồ án).
     *
     * @param Trip $trip
     * @param int|null $dayNumber
     * @param int|null $destinationId
     * @return array<string, mixed>
     */
    public function getCreateActivityData(Trip $trip, ?int $dayNumber = null, ?int $destinationId = null): array;

    /**
     * Thêm hoạt động mới vào lịch trình của chuyến đi.
     * Tuyến: POST /trips/{trip}/itinerary (Bảng 22 Báo cáo Đồ án - Chức năng 6).
     * Kiểm tra chặt chẽ quy tắc nghiệp vụ thời gian và xung đột khung giờ (FR19).
     *
     * @param Trip $trip
     * @param array $data
     * @return \App\Models\ItineraryItem
     */
    public function storeActivity(Trip $trip, array $data): \App\Models\ItineraryItem;
}
