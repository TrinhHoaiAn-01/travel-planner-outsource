<?php

namespace App\Services\Interfaces;

use App\Models\Trip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TripServiceInterface
{
    /**
     * Lấy danh sách chuyến đi của người dùng kèm bộ lọc và tìm kiếm.
     *
     * @param int $userId Mã định danh của người dùng sở hữu chuyến đi
     * @param array $filters Mảng chứa các tiêu chí lọc: search, status, start_date, end_date
     * @param int $perPage Số lượng bản ghi trên mỗi trang
     * @return LengthAwarePaginator
     */
    public function getUserTrips(int $userId, array $filters = [], int $perPage = 9): LengthAwarePaginator;

    /**
     * Đếm số lượng chuyến đi theo từng trạng thái để phục vụ hiển thị các tab điều hướng.
     *
     * @param int $userId Mã định danh của người dùng
     * @return array Mảng chứa số lượng tương ứng: all, planned, ongoing, completed
     */
    public function getTripCountsByStatus(int $userId): array;

    /**
     * Tạo mới một chuyến đi.
     *
     * @param array $data Dữ liệu khởi tạo chuyến đi
     * @return Trip
     */
    public function createTrip(array $data): Trip;

    /**
     * Lấy thông tin chi tiết của chuyến đi.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return Trip
     */
    public function getTripDetails(int $tripId): Trip;

    /**
     * Cập nhật thông tin chuyến đi.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @param array $data Dữ liệu cập nhật
     * @return Trip
     */
    public function updateTrip(int $tripId, array $data): Trip;

    /**
     * Xóa chuyến đi khỏi hệ thống.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return bool
     */
    public function deleteTrip(int $tripId): bool;

    /**
     * Cập nhật trạng thái chuyến đi.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @param string $status Trạng thái mới
     * @return Trip
     */
    public function updateTripStatus(int $tripId, string $status): Trip;

    /**
     * Xác thực khoảng thời gian ngày bắt đầu và kết thúc của chuyến đi.
     *
     * @param string|null $startDate Ngày bắt đầu
     * @param string|null $endDate Ngày kết thúc
     * @return bool
     */
    public function validateTripDates(?string $startDate, ?string $endDate): bool;

    /**
     * Lấy dữ liệu tóm tắt của chuyến đi bao gồm số ngày, số địa điểm, tổng chi phí.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return array
     */
    public function getTripSummary(int $tripId): array;

    /**
     * Nhân bản chuyến đi đã có thành một chuyến đi mới.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return Trip
     */
    public function cloneTrip(int $tripId): Trip;

    /**
     * Mở lại chuyến đi đã hoàn thành.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return Trip
     */
    public function reopenTrip(int $tripId): Trip;

    /**
     * Lấy thông tin chi tiết các hoạt động lịch trình phụ thuộc bị ảnh hưởng khi thay đổi hoặc rút ngắn ngày chuyến đi.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @param string|null $newStartDate Ngày bắt đầu mới
     * @param string|null $newEndDate Ngày kết thúc mới
     * @return array Mảng chứa: is_shortened, old_total_days, new_total_days, target_day, affected_count, affected_items
     */
    public function getAffectedItineraryItems(int $tripId, ?string $newStartDate, ?string $newEndDate): array;

    /**
     * Xử lý khi ngày của chuyến đi bị thay đổi hoặc rút ngắn khi có lịch trình phụ thuộc.
     * Tự động bảo toàn dữ liệu bằng cách dời an toàn các hoạt động vượt quá số ngày về ngày cuối cùng mới và tái cấu trúc thứ tự hiển thị.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @param string|null $newStartDate Ngày bắt đầu mới
     * @param string|null $newEndDate Ngày kết thúc mới
     * @return array Kết quả xử lý: [date_changed, is_shortened, reassigned_count, affected_items, target_day, message]
     */
    public function handleTripDateChange(int $tripId, ?string $newStartDate, ?string $newEndDate): array;

    /**
     * Cập nhật mô tả và ghi chú tổng quát của chuyến đi.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @param string|null $notes Nội dung mô tả hoặc ghi chú mới
     * @return Trip
     */
    public function updateTripNotes(int $tripId, ?string $notes): Trip;

    /**
     * Tính toán thời lượng chuyến đi (số ngày, số đêm, nhãn hiển thị và tính hợp lệ) từ ngày bắt đầu và kết thúc.
     *
     * @param string|null $startDate Ngày bắt đầu
     * @param string|null $endDate Ngày kết thúc
     * @return array Mảng chứa: days, nights, label, is_valid
     */
    public function calculateTripDuration(?string $startDate, ?string $endDate): array;

    /**
     * Lấy danh sách chi tiết các ngày của chuyến đi (từ Ngày 1 đến Ngày N kèm ngày trong tuần tiếng Việt).
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return array Mảng chứa danh sách các ngày với day_number, date, formatted_date, day_name, label
     */
    public function getTripDaysList(int $tripId): array;
}

