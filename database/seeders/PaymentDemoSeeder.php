<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentDemoSeeder extends Seeder
{
    /**
     * Seed dữ liệu đặt phòng và giao dịch thanh toán mẫu (VNPay, ZaloPay, MoMo, VietQR, Tiền mặt).
     */
    public function run(): void
    {
        $user = User::where('email', 'user@travelplanner.test')->first() ?? User::where('role', 'user')->first();

        if (! $user) {
            return;
        }

        $trips = Trip::where('user_id', $user->id)->get();
        $trip = $trips->first();

        $roomTypes = RoomType::all();
        if ($roomTypes->isEmpty()) {
            return;
        }

        $room1 = $roomTypes->first();
        $room2 = $roomTypes->skip(1)->first() ?? $room1;
        $room3 = $roomTypes->skip(2)->first() ?? $room1;

        // =========================================================================
        // NHÓM 1: CÁC ĐƠN ĐẶT PHÒNG CHỜ THANH TOÁN (PENDING) ĐỂ TEST TRỰC TIẾP
        // =========================================================================

        // 1. Đơn chờ test cổng VNPay Sandbox (Dùng thẻ test NCB)
        Booking::updateOrCreate(
            ['booking_code' => 'BK-TEST-VNPAY'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room1->id,
                'room_count' => 1,
                'check_in_date' => Carbon::now()->addDays(5)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(7)->toDateString(),
                'total_price' => 5000000,
                'status' => 'pending',
                'created_at' => Carbon::now()->subMinutes(30),
                'updated_at' => Carbon::now()->subMinutes(30),
            ]
        );

        // 2. Đơn chờ test cổng ZaloPay Sandbox (AppID 2553)
        Booking::updateOrCreate(
            ['booking_code' => 'BK-TEST-ZALOPAY'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room2->id,
                'room_count' => 1,
                'check_in_date' => Carbon::now()->addDays(8)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(10)->toDateString(),
                'total_price' => 4000000,
                'status' => 'pending',
                'created_at' => Carbon::now()->subMinutes(20),
                'updated_at' => Carbon::now()->subMinutes(20),
            ]
        );

        // 3. Đơn chờ test cổng MoMo Sandbox (SĐT 0901234567)
        Booking::updateOrCreate(
            ['booking_code' => 'BK-TEST-MOMO'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room3->id,
                'room_count' => 1,
                'check_in_date' => Carbon::now()->addDays(12)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(14)->toDateString(),
                'total_price' => 3000000,
                'status' => 'pending',
                'created_at' => Carbon::now()->subMinutes(15),
                'updated_at' => Carbon::now()->subMinutes(15),
            ]
        );

        // 4. Đơn chờ test chuyển khoản ngân hàng (VietQR Napas 24/7)
        Booking::updateOrCreate(
            ['booking_code' => 'BK-TEST-VIETQR'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room1->id,
                'room_count' => 1,
                'check_in_date' => Carbon::now()->addDays(15)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(17)->toDateString(),
                'total_price' => 2500000,
                'status' => 'pending',
                'created_at' => Carbon::now()->subMinutes(10),
                'updated_at' => Carbon::now()->subMinutes(10),
            ]
        );

        // =========================================================================
        // NHÓM 2: CÁC ĐƠN ĐÃ THANH TOÁN THÀNH CÔNG (CONFIRMED + COMPLETED PAYMENT)
        // =========================================================================

        // 5. Đơn đã thanh toán thành công qua VNPay
        $bookingVnpay = Booking::updateOrCreate(
            ['booking_code' => 'BK-PAID-VNPAY'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room1->id,
                'room_count' => 2,
                'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
                'total_price' => 6000000,
                'status' => 'confirmed',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ]
        );

        Payment::updateOrCreate(
            ['transaction_id' => 'VNP14234567'],
            [
                'booking_id' => $bookingVnpay->id,
                'amount' => 6000000,
                'payment_method' => 'vnpay',
                'status' => 'completed',
                'payment_date' => Carbon::now()->subDays(3),
            ]
        );

        // 6. Đơn đã thanh toán thành công qua ZaloPay
        $bookingZalo = Booking::updateOrCreate(
            ['booking_code' => 'BK-PAID-ZALOPAY'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room2->id,
                'room_count' => 1,
                'check_in_date' => Carbon::now()->addDays(4)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(6)->toDateString(),
                'total_price' => 4500000,
                'status' => 'confirmed',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ]
        );

        Payment::updateOrCreate(
            ['transaction_id' => 'ZP2411040001'],
            [
                'booking_id' => $bookingZalo->id,
                'amount' => 4500000,
                'payment_method' => 'zalopay',
                'status' => 'completed',
                'payment_date' => Carbon::now()->subDays(2),
            ]
        );

        // 7. Đơn đã thanh toán thành công qua MoMo
        $bookingMomo = Booking::updateOrCreate(
            ['booking_code' => 'BK-PAID-MOMO'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room3->id,
                'room_count' => 1,
                'check_in_date' => Carbon::now()->addDays(6)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(8)->toDateString(),
                'total_price' => 3600000,
                'status' => 'confirmed',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ]
        );

        Payment::updateOrCreate(
            ['transaction_id' => 'MM2409876543'],
            [
                'booking_id' => $bookingMomo->id,
                'amount' => 3600000,
                'payment_method' => 'momo',
                'status' => 'completed',
                'payment_date' => Carbon::now()->subDays(1),
            ]
        );

        // 8. Đơn đã thanh toán thành công qua chuyển khoản VietQR
        $bookingVietQr = Booking::updateOrCreate(
            ['booking_code' => 'BK-PAID-VIETQR'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room1->id,
                'room_count' => 1,
                'check_in_date' => Carbon::now()->addDays(7)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(9)->toDateString(),
                'total_price' => 5200000,
                'status' => 'confirmed',
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(12),
            ]
        );

        Payment::updateOrCreate(
            ['transaction_id' => 'TXN-VIETQR-8888'],
            [
                'booking_id' => $bookingVietQr->id,
                'amount' => 5200000,
                'payment_method' => 'bank_transfer',
                'status' => 'completed',
                'payment_date' => Carbon::now()->subHours(12),
            ]
        );

        // =========================================================================
        // NHÓM 3: CÁC TRƯỜNG HỢP THANH TOÁN TIỀN MẶT VÀ GIAO DỊCH THẤT BẠI
        // =========================================================================

        // 9. Đơn thanh toán tiền mặt tại quầy check-in (pending payment)
        $bookingCash = Booking::updateOrCreate(
            ['booking_code' => 'BK-PAID-CASH'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room2->id,
                'room_count' => 1,
                'check_in_date' => Carbon::now()->addDays(10)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(12)->toDateString(),
                'total_price' => 2800000,
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6),
            ]
        );

        Payment::updateOrCreate(
            ['transaction_id' => 'TXN-CASH-1234'],
            [
                'booking_id' => $bookingCash->id,
                'amount' => 2800000,
                'payment_method' => 'cash',
                'status' => 'pending',
                'payment_date' => Carbon::now()->subHours(6),
            ]
        );

        // 10. Giao dịch VNPay người dùng hủy bỏ / thất bại (failed payment)
        $bookingFailed = Booking::updateOrCreate(
            ['booking_code' => 'BK-FAILED-VNPAY'],
            [
                'user_id' => $user->id,
                'trip_id' => $trip?->id,
                'room_type_id' => $room3->id,
                'room_count' => 1,
                'check_in_date' => Carbon::now()->addDays(11)->toDateString(),
                'check_out_date' => Carbon::now()->addDays(13)->toDateString(),
                'total_price' => 3200000,
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
            ]
        );

        Payment::updateOrCreate(
            ['transaction_id' => 'VNP-FAIL-9999'],
            [
                'booking_id' => $bookingFailed->id,
                'amount' => 3200000,
                'payment_method' => 'vnpay',
                'status' => 'failed',
                'payment_date' => Carbon::now()->subHours(2),
            ]
        );
    }
}

