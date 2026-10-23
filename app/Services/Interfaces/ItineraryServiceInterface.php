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

    /**
     * Lấy dữ liệu khởi tạo cho màn hình / modal chỉnh sửa hoạt động trong lịch trình.
     * Tuyến: GET /itinerary/{itinerary}/edit (Bảng 22 Báo cáo Đồ án - Chức năng 7).
     *
     * @param \App\Models\ItineraryItem $itinerary
     * @return array<string, mixed>
     */
    public function getEditActivityData(\App\Models\ItineraryItem $itinerary): array;

    /**
     * Cập nhật thông tin hoạt động trong lịch trình chuyến đi.
     * Tuyến: PATCH /itinerary/{itinerary} (Bảng 22 Báo cáo Đồ án - Chức năng 7 & 8).
     * Kiểm tra xung đột thời gian, loại trừ chính mục đang sửa.
     *
     * @param \App\Models\ItineraryItem $itinerary
     * @param array $data
     * @return \App\Models\ItineraryItem
     */
    public function updateActivity(\App\Models\ItineraryItem $itinerary, array $data): \App\Models\ItineraryItem;

    /**
     * Xóa mục hoạt động khỏi lịch trình của chuyến đi.
     * Tuyến: DELETE /itinerary/{itinerary} (Bảng 22 Báo cáo Đồ án - Chức năng 7).
     *
     * @param \App\Models\ItineraryItem $itinerary
     * @return bool
     */
    public function deleteActivity(\App\Models\ItineraryItem $itinerary): bool;

    /**
     * Thay đổi thứ tự hiển thị các hoạt động trong một ngày của chuyến đi (FR21).
     * Tuyến: PATCH /trips/{trip}/itinerary/reorder (Bảng 22 Báo cáo Đồ án - Chức năng 8).
     *
     * @param \App\Models\Trip $trip
     * @param int $dayNumber
     * @param array<int> $itemIds Danh sách ID hoạt động theo thứ tự mới
     * @return bool
     */
    public function reorderActivities(\App\Models\Trip $trip, int $dayNumber, array $itemIds): bool;

    /**
     * Cập nhật ghi chú riêng biệt cho một mục hoạt động trong lịch trình (Chức năng 9).
     * Tuyến: PATCH /itinerary/{itinerary}/note (Bảng 22 Báo cáo Đồ án).
     *
     * @param \App\Models\ItineraryItem $itinerary
     * @param string|null $note Nội dung ghi chú mới (tối đa 1000 ký tự)
     * @return \App\Models\ItineraryItem
     */
    public function updateActivityNote(\App\Models\ItineraryItem $itinerary, ?string $note): \App\Models\ItineraryItem;

    /**
     * Kiểm tra số thứ tự ngày (day_number) của hoạt động có nằm trong phạm vi ngày của chuyến đi hay không (Chức năng 10).
     * Ràng buộc nghiệp vụ Bảng 22, Bảng 26: itinerary_items.day_number nằm trong số ngày Trip.
     *
     * @param \App\Models\Trip $trip
     * @param int $dayNumber
     * @return bool True nếu hợp lệ, ném ValidationException nếu không hợp lệ
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateItemWithinTripDates(\App\Models\Trip $trip, int $dayNumber): bool;

    /**
     * Kiểm tra xung đột thời gian giữa các hoạt động trong cùng một ngày của chuyến đi (FR19).
     *
     * @param \App\Models\Trip $trip
     * @param int $dayNumber
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeItemId ID của hoạt động đang sửa (loại trừ chính nó)
     * @return bool True nếu có xung đột, false nếu không trùng
     */
    public function hasTimeOverlap(\App\Models\Trip $trip, int $dayNumber, string $startTime, string $endTime, ?int $excludeItemId = null): bool;
}
