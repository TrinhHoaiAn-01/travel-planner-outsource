<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingDemoSeeder extends Seeder
{
    /**
     * Seed dữ liệu mẫu cho tính năng Quản lý đặt phòng (Booking Management - Task 8).
     */
    public function run(): void
    {
        $user = User::where('email', 'user@travelplanner.test')->first();

        if (! $user) {
            return;
        }

        $trips = Trip::where('user_id', $user->id)->get()->keyBy('name');
        $roomTypes = RoomType::all();

        if ($roomTypes->isEmpty()) {
            return;
        }

        $roomBana = $roomTypes->firstWhere('name', 'Mercure Danang French Village Bana Hills') ?? $roomTypes->first();
        $roomHalong = $roomTypes->firstWhere('name', 'Cabin Du Thuyền Ambassador Cruise') ?? $roomTypes->skip(1)->first() ?? $roomTypes->first();
        $roomPhuQuoc = $roomTypes->firstWhere('name', 'Phòng Deluxe King Hướng Biển') ?? $roomTypes->last();

        $tripDanang = $trips->get('Da Nang & Hoi An Getaway') ?? $trips->first();
        $tripHalong = $trips->get('Du Thuyền Nghỉ Dưỡng Vịnh Hạ Long') ?? $trips->skip(1)->first() ?? $trips->first();
        $tripPhuQuoc = $trips->get('Hành trình Khám phá Đà Nẵng - Hội An') ?? $trips->first();
        $tripSapa = $trips->get('Săn Mây Đỉnh Fansipan Sa Pa') ?? $trips->last();

        // 1. Đơn đặt phòng chờ xác nhận (pending) - có thể hủy trực tiếp trên giao diện
        Booking::updateOrCreate(
            ['booking_code' => 'BK-2026-DANANG'],
            [
                'user_id' => $user->id,
                'trip_id' => $tripDanang?->id,
                'room_type_id' => $roomBana->id,
                'room_count' => 2,
                'check_in_date' => Carbon::parse('2026-11-15'),
                'check_out_date' => Carbon::parse('2026-11-17'),
                'total_price' => (float) $roomBana->price_per_night * 2 * 2,
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(3),
                'updated_at' => Carbon::now()->subHours(3),
            ]
        );

        // 2. Đơn đặt phòng đã xác nhận (confirmed)
        Booking::updateOrCreate(
            ['booking_code' => 'BK-2026-HALONG'],
            [
                'user_id' => $user->id,
                'trip_id' => $tripHalong?->id,
                'room_type_id' => $roomHalong->id,
                'room_count' => 1,
                'check_in_date' => Carbon::parse('2026-11-20'),
                'check_out_date' => Carbon::parse('2026-11-22'),
                'total_price' => (float) $roomHalong->price_per_night * 1 * 2,
                'status' => 'confirmed',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(1),
            ]
        );

        // 3. Đơn đặt phòng đã hoàn thành (completed)
        Booking::updateOrCreate(
            ['booking_code' => 'BK-2026-SAPA'],
            [
                'user_id' => $user->id,
                'trip_id' => $tripSapa?->id,
                'room_type_id' => $roomBana->id,
                'room_count' => 1,
                'check_in_date' => Carbon::parse('2026-08-15'),
                'check_out_date' => Carbon::parse('2026-08-17'),
                'total_price' => (float) $roomBana->price_per_night * 1 * 2,
                'status' => 'completed',
                'created_at' => Carbon::parse('2026-08-01 10:00:00'),
                'updated_at' => Carbon::parse('2026-08-17 12:00:00'),
            ]
        );

        // 4. Đơn đặt phòng đã hủy (cancelled)
        Booking::updateOrCreate(
            ['booking_code' => 'BK-2026-PHUQUOC'],
            [
                'user_id' => $user->id,
                'trip_id' => $tripPhuQuoc?->id,
                'room_type_id' => $roomPhuQuoc->id,
                'room_count' => 1,
                'check_in_date' => Carbon::parse('2026-10-10'),
                'check_out_date' => Carbon::parse('2026-10-12'),
                'total_price' => (float) $roomPhuQuoc->price_per_night * 1 * 2,
                'status' => 'cancelled',
                'created_at' => Carbon::parse('2026-10-01 09:30:00'),
                'updated_at' => Carbon::parse('2026-10-02 14:15:00'),
            ]
        );
    }
}
