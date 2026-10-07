<?php

namespace Tests\Unit;

use App\Services\DashboardService;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    private DashboardService $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = new DashboardService();
    }

    /**
     * Kiểm thử phương thức getSummaryStatistics trả về đầy đủ các chỉ số thống kê tổng quan.
     */
    public function test_get_summary_statistics_returns_expected_structure_and_values(): void
    {
        $summary = $this->dashboardService->getSummaryStatistics();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_users', $summary);
        $this->assertArrayHasKey('user_growth', $summary);
        $this->assertArrayHasKey('total_trips', $summary);
        $this->assertArrayHasKey('trip_growth', $summary);
        $this->assertArrayHasKey('total_destinations', $summary);
        $this->assertArrayHasKey('destination_note', $summary);
        $this->assertArrayHasKey('pending_reviews', $summary);
        $this->assertArrayHasKey('system_status', $summary);

        $this->assertSame('4.820', $summary['total_users']);
        $this->assertSame('+12% tháng này', $summary['user_growth']);
        $this->assertSame('1.250', $summary['total_trips']);
        $this->assertSame('+8.5% so với kỳ trước', $summary['trip_growth']);
        $this->assertSame('368', $summary['total_destinations']);
        $this->assertSame('Tại 63 tỉnh thành', $summary['destination_note']);
        $this->assertSame(3, $summary['pending_reviews']);
        $this->assertSame('Hệ thống hoạt động bình thường', $summary['system_status']);
    }

    /**
     * Kiểm thử phương thức getMonthlyStatistics trả về đúng 12 tháng dữ liệu năm 2026.
     */
    public function test_get_monthly_statistics_returns_12_months_for_year_2026(): void
    {
        $monthlyStats = $this->dashboardService->getMonthlyStatistics(2026);

        $this->assertIsArray($monthlyStats);
        $this->assertSame(2026, $monthlyStats['year']);
        $this->assertSame('Tháng 7 (1.200 chuyến)', $monthlyStats['peak_info']);
        $this->assertIsArray($monthlyStats['months']);
        $this->assertCount(12, $monthlyStats['months']);

        $expectedLabels = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
        foreach ($monthlyStats['months'] as $index => $monthData) {
            $this->assertArrayHasKey('label', $monthData);
            $this->assertArrayHasKey('value', $monthData);
            $this->assertArrayHasKey('height', $monthData);
            $this->assertSame($expectedLabels[$index], $monthData['label']);
            $this->assertIsInt($monthData['value']);
            $this->assertStringEndsWith('%', $monthData['height']);
        }

        // Kiểm tra tháng đỉnh điểm T7 (Tháng 7)
        $july = $monthlyStats['months'][6];
        $this->assertSame('T7', $july['label']);
        $this->assertSame(1200, $july['value']);
        $this->assertSame('100%', $july['height']);
    }

    /**
     * Kiểm thử phương thức getTopDestinations trả về danh sách điểm đến phổ biến được xếp hạng.
     */
    public function test_get_top_destinations_returns_ranked_destinations(): void
    {
        $destinations = $this->dashboardService->getTopDestinations(4);

        $this->assertIsArray($destinations);
        $this->assertCount(4, $destinations);

        // Kiểm tra các điểm đến theo thứ tự rank
        $this->assertSame(1, $destinations[0]['rank']);
        $this->assertSame('Đà Nẵng', $destinations[0]['name']);
        $this->assertSame(38, $destinations[0]['percentage']);
        $this->assertSame('#2563EB', $destinations[0]['color']);

        $this->assertSame(2, $destinations[1]['rank']);
        $this->assertSame('Vịnh Hạ Long', $destinations[1]['name']);
        $this->assertSame(26, $destinations[1]['percentage']);
        $this->assertSame('#06B6D4', $destinations[1]['color']);

        $this->assertSame(3, $destinations[2]['rank']);
        $this->assertSame('Phú Quốc', $destinations[2]['name']);
        $this->assertSame(20, $destinations[2]['percentage']);
        $this->assertSame('#10B981', $destinations[2]['color']);

        $this->assertSame(4, $destinations[3]['rank']);
        $this->assertSame('Hội An', $destinations[3]['name']);
        $this->assertSame(16, $destinations[3]['percentage']);
        $this->assertSame('#F59E0B', $destinations[3]['color']);
    }

    /**
     * Kiểm thử phương thức getRecentBookings trả về danh sách đơn đặt phòng mới nhất với đúng định dạng tiền tệ.
     */
    public function test_get_recent_bookings_returns_formatted_bookings(): void
    {
        $bookings = $this->dashboardService->getRecentBookings(5);

        $this->assertIsArray($bookings);
        $this->assertCount(3, $bookings);

        // Đơn đầu tiên
        $first = $bookings[0];
        $this->assertSame('#WND-2026-8891', $first['booking_code']);
        $this->assertSame('Nguyễn Bảo Ngọc', $first['customer_name']);
        $this->assertSame('Mercure Danang French Village', $first['destination_name']);
        $this->assertSame('10/10/2026', $first['booking_date']);
        $this->assertSame(3700000, $first['total_price']);
        $this->assertSame('3.700.000đ', $first['formatted_price']);
        $this->assertSame('Confirmed', $first['status']);
        $this->assertSame('success', $first['status_badge']);

        // Đơn thứ hai
        $second = $bookings[1];
        $this->assertSame('#WND-2026-8890', $second['booking_code']);
        $this->assertSame('Trần Hoàng Long', $second['customer_name']);
        $this->assertSame('Vinpearl Resort & Spa Phú Quốc', $second['destination_name']);
        $this->assertSame('Upcoming', $second['status']);
        $this->assertSame('info', $second['status_badge']);

        // Đơn thứ ba
        $third = $bookings[2];
        $this->assertSame('#WND-2026-8889', $third['booking_code']);
        $this->assertSame('Phạm Thanh Thảo', $third['customer_name']);
        $this->assertSame('Terracotta Resort Đà Lạt', $third['destination_name']);
        $this->assertSame('Pending', $third['status']);
        $this->assertSame('warning', $third['status_badge']);
    }
}
