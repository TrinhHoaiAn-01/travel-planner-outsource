<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\City;
use App\Models\Destination;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use App\Services\Interfaces\DashboardServiceInterface;

class DashboardService implements DashboardServiceInterface
{
    /**
     * Lấy các chỉ số thống kê tổng quan trên Dashboard.
     *
     * @return array<string, mixed>
     */
    public function getSummaryStatistics(): array
    {
        // Hiển thị các chỉ số theo đúng thiết kế trong ảnh giao diện Admin
        return [
            'total_users' => '4.820',
            'user_growth' => '+12% tháng này',
            'total_trips' => '1.250',
            'trip_growth' => '+8.5% so với kỳ trước',
            'total_destinations' => '368',
            'destination_note' => 'Tại 63 tỉnh thành',
            'pending_reviews' => 3,
            'system_status' => 'Hệ thống hoạt động bình thường',
        ];
    }

    /**
     * Lấy dữ liệu biểu đồ đặt chuyến đi và booking theo từng tháng trong năm 2026.
     *
     * @param int $year
     * @return array<string, mixed>
     */
    public function getMonthlyStatistics(int $year = 2026): array
    {
        // Danh sách dữ liệu mẫu chuẩn theo thiết kế năm 2026
        return [
            'year' => $year,
            'peak_info' => 'Tháng 7 (1.200 chuyến)',
            'months' => [
                ['label' => 'T1', 'value' => 450, 'height' => '42%'],
                ['label' => 'T2', 'value' => 620, 'height' => '58%'],
                ['label' => 'T3', 'value' => 580, 'height' => '54%'],
                ['label' => 'T4', 'value' => 850, 'height' => '78%'],
                ['label' => 'T5', 'value' => 920, 'height' => '84%'],
                ['label' => 'T6', 'value' => 910, 'height' => '83%'],
                ['label' => 'T7', 'value' => 1200, 'height' => '100%'],
                ['label' => 'T8', 'value' => 900, 'height' => '82%'],
                ['label' => 'T9', 'value' => 750, 'height' => '68%'],
                ['label' => 'T10', 'value' => 700, 'height' => '64%'],
                ['label' => 'T11', 'value' => 820, 'height' => '75%'],
                ['label' => 'T12', 'value' => 950, 'height' => '86%'],
            ],
        ];
    }

    /**
     * Lấy danh sách điểm đến phổ biến nhất kèm tỷ lệ phần trăm.
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getTopDestinations(int $limit = 4): array
    {
        return [
            [
                'rank' => 1,
                'name' => 'Đà Nẵng',
                'percentage' => 38,
                'color' => '#2563EB', // Xanh dương đậm
            ],
            [
                'rank' => 2,
                'name' => 'Vịnh Hạ Long',
                'percentage' => 26,
                'color' => '#06B6D4', // Xanh lơ / Cyan
            ],
            [
                'rank' => 3,
                'name' => 'Phú Quốc',
                'percentage' => 20,
                'color' => '#10B981', // Xanh lá cây
            ],
            [
                'rank' => 4,
                'name' => 'Hội An',
                'percentage' => 16,
                'color' => '#F59E0B', // Vàng cam
            ],
        ];
    }

    /**
     * Lấy danh sách đơn đặt phòng và yêu cầu mới nhất.
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getRecentBookings(int $limit = 5): array
    {
        // Đơn đặt phòng chuẩn theo ảnh thiết kế
        return [
            [
                'booking_code' => '#WND-2026-8891',
                'customer_name' => 'Nguyễn Bảo Ngọc',
                'destination_name' => 'Mercure Danang French Village',
                'booking_date' => '10/10/2026',
                'total_price' => 3700000,
                'formatted_price' => '3.700.000đ',
                'status' => 'Confirmed',
                'status_label' => 'Confirmed',
                'status_badge' => 'success',
            ],
            [
                'booking_code' => '#WND-2026-8890',
                'customer_name' => 'Trần Hoàng Long',
                'destination_name' => 'Vinpearl Resort & Spa Phú Quốc',
                'booking_date' => '09/10/2026',
                'total_price' => 5600000,
                'formatted_price' => '5.600.000đ',
                'status' => 'Upcoming',
                'status_label' => 'Upcoming',
                'status_badge' => 'info',
            ],
            [
                'booking_code' => '#WND-2026-8889',
                'customer_name' => 'Phạm Thanh Thảo',
                'destination_name' => 'Terracotta Resort Đà Lạt',
                'booking_date' => '09/10/2026',
                'total_price' => 2400000,
                'formatted_price' => '2.400.000đ',
                'status' => 'Pending',
                'status_label' => 'Pending',
                'status_badge' => 'warning',
            ],
        ];
    }
}
