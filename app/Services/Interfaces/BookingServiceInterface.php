<?php

namespace App\Services\Interfaces;

use App\Models\Booking;

interface BookingServiceInterface
{
    /**
     * Tạo một đơn đặt phòng mới.
     */
    public function createBooking(array $data, int $userId): Booking;

    /**
     * Kiểm tra tình trạng phòng còn trống theo khoảng ngày.
     */
    public function checkRoomAvailability(int $roomTypeId, string $checkIn, string $checkOut, int $roomCount): bool;

    /**
     * Tính toán tổng chi phí đặt phòng theo số đêm và số lượng phòng.
     */
    public function calculateBookingTotal(int $roomTypeId, string $checkIn, string $checkOut, int $roomCount): float;

    /**
     * Tự động sinh mã đặt phòng duy nhất dạng #BK-YYYY-XXXX.
     */
    public function generateBookingCode(): string;

    /**
     * Kiểm tra thời gian đặt phòng phải nằm trong thời gian của chuyến đi.
     */
    public function validateBookingDates(?int $tripId, string $checkIn, string $checkOut, int $userId): void;
}
