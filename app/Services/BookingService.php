<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RoomType;
use App\Models\Trip;
use App\Services\Interfaces\BookingServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService implements BookingServiceInterface
{
    /**
     * Tạo đơn đặt phòng mới và lưu vào cơ sở dữ liệu.
     */
    public function createBooking(array $data, int $userId): Booking
    {
        // 1. Kiểm tra ngày đặt phòng với Chuyến đi (nếu có chọn Trip)
        if (!empty($data['trip_id'])) {
            $this->validateBookingDates($data['trip_id'], $data['check_in_date'], $data['check_out_date'], $userId);
        }

        // 2. Kiểm tra số lượng phòng còn trống
        if (!$this->checkRoomAvailability($data['room_type_id'], $data['check_in_date'], $data['check_out_date'], (int) $data['room_count'])) {
            throw ValidationException::withMessages([
                'room_count' => 'Loại phòng này hiện không còn đủ số lượng phòng trống cho khoảng thời gian bạn đã chọn.',
            ]);
        }

        // 3. Tính toán tổng tiền và sinh mã đặt phòng duy nhất
        $totalPrice = $this->calculateBookingTotal(
            $data['room_type_id'],
            $data['check_in_date'],
            $data['check_out_date'],
            (int) $data['room_count']
        );
        $bookingCode = $this->generateBookingCode();

        // 4. Lưu Booking vào Database trong transaction
        return DB::transaction(function () use ($data, $userId, $totalPrice, $bookingCode) {
            return Booking::create([
                'user_id'        => $userId,
                'trip_id'        => $data['trip_id'] ?? null,
                'room_type_id'   => $data['room_type_id'],
                'room_count'     => $data['room_count'],
                'check_in_date'  => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'total_price'    => $totalPrice,
                'status'         => 'pending',
                'booking_code'   => $bookingCode,
            ]);
        });
    }

    /**
     * Kiểm tra số lượng phòng còn trống trong khoảng ngày.
     */
    public function checkRoomAvailability(int $roomTypeId, string $checkIn, string $checkOut, int $roomCount): bool
    {
        $roomType = RoomType::findOrFail($roomTypeId);

        // Đếm tổng số phòng đã được đặt và chưa hủy trong khoảng ngày này
        $bookedRooms = Booking::where('room_type_id', $roomTypeId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in_date', '<', $checkOut)
                      ->where('check_out_date', '>', $checkIn);
            })
            ->sum('room_count');

        return ($bookedRooms + $roomCount) <= $roomType->quantity;
    }

    /**
     * Tính toán tổng tiền đặt phòng = số đêm * giá mỗi đêm * số phòng.
     */
    public function calculateBookingTotal(int $roomTypeId, string $checkIn, string $checkOut, int $roomCount): float
    {
        $roomType = RoomType::findOrFail($roomTypeId);
        $startDate = Carbon::parse($checkIn);
        $endDate = Carbon::parse($checkOut);
        $nights = max(1, $startDate->diffInDays($endDate));

        return (float) ($nights * (float) $roomType->price_per_night * $roomCount);
    }

    /**
     * Sinh mã booking duy nhất theo định dạng #BK-YYYY-XXXX.
     */
    public function generateBookingCode(): string
    {
        do {
            $code = '#BK-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
        } while (Booking::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Xác thực khoảng ngày nhận và trả phòng phải nằm trong thời gian của chuyến đi.
     */
    public function validateBookingDates(?int $tripId, string $checkIn, string $checkOut, int $userId): void
    {
        if (!$tripId) {
            return;
        }

        $trip = Trip::where('id', $tripId)->where('user_id', $userId)->firstOrFail();

        if ($trip->start_date && $trip->end_date) {
            if ($checkIn < $trip->start_date->toDateString() || $checkOut > $trip->end_date->toDateString()) {
                throw ValidationException::withMessages([
                    'check_in_date' => 'Khoảng thời gian đặt phòng phải nằm trong thời gian của chuyến đi (' . $trip->start_date->format('d/m/Y') . ' - ' . $trip->end_date->format('d/m/Y') . ').',
                ]);
            }
        }
    }
}
