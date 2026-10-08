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
        $this->assertArrayHasKey('total_destinations', $summary);
        $this->assertArrayHasKey('total_bookings', $summary);
        $this->assertArrayHasKey('total_revenue', $summary);
        $this->assertArrayHasKey('pending_reviews', $summary);
        $this->assertArrayHasKey('system_status', $summary);

        $this->assertSame('4.520', $summary['total_users']);
        $this->assertSame('+12.5%', $summary['user_growth']);
        $this->assertSame('1.280', $summary['total_destinations']);
        $this->assertSame('+8.2%', $summary['destination_growth']);
        $this->assertSame('860', $summary['total_bookings']);
        $this->assertSame('+5.4%', $summary['booking_growth']);
        $this->assertSame('128.500.000 đ', $summary['total_revenue']);
        $this->assertSame('+14.8%', $summary['revenue_growth']);
        $this->assertSame(3, $summary['pending_reviews']);
        $this->assertSame('Hoạt động (Production)', $summary['system_status']);
    }

    /**
     * Kiểm thử phương thức getMonthlyStatistics trả về đúng 12 tháng dữ liệu năm 2026.
     */
    public function test_get_monthly_statistics_returns_12_months_for_year_2026(): void
    {
        $monthlyStats = $this->dashboardService->getMonthlyStatistics(2026);

        $this->assertIsArray($monthlyStats);
        $this->assertSame(2026, $monthlyStats['year']);
        $this->assertSame('Tháng 7 (880 lượt)', $monthlyStats['peak_info']);
        $this->assertSame('Tăng 18.2% so với cùng kỳ năm trước', $monthlyStats['growth_info']);
        $this->assertIsArray($monthlyStats['months']);
        $this->assertCount(12, $monthlyStats['months']);

        $expectedLabels = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
        foreach ($monthlyStats['months'] as $index => $monthData) {
            $this->assertArrayHasKey('label', $monthData);
            $this->assertArrayHasKey('value', $monthData);
            $this->assertArrayHasKey('height', $monthData);
            $this->assertSame($expectedLabels[$index], $monthData['label']);
            $this->assertIsInt($monthData['value']);
            $this->assertStringEndsWith('%', $monthData['height']);
        }

        // Kiểm tra tháng đỉnh điểm 7 (Tháng 7)
        $july = $monthlyStats['months'][6];
        $this->assertSame('7', $july['label']);
        $this->assertSame(880, $july['value']);
        $this->assertSame('95%', $july['height']);
    }

    /**
     * Kiểm thử phương thức getBookingCategories trả về tỷ lệ đặt phòng theo loại.
     */
    public function test_get_booking_categories_returns_expected_distribution(): void
    {
        $categories = $this->dashboardService->getBookingCategories(4);

        $this->assertIsArray($categories);
        $this->assertCount(4, $categories);

        $this->assertSame('Khách Sạn', $categories[0]['name']);
        $this->assertSame(45, $categories[0]['percentage']);
        $this->assertSame('#2563EB', $categories[0]['color']);

        $this->assertSame('Resort & Nghỉ Dưỡng', $categories[1]['name']);
        $this->assertSame(30, $categories[1]['percentage']);
        $this->assertSame('#06B6D4', $categories[1]['color']);

        $this->assertSame('Homestay', $categories[2]['name']);
        $this->assertSame(15, $categories[2]['percentage']);
        $this->assertSame('#10B981', $categories[2]['color']);

        $this->assertSame('Căn Hộ & Villa', $categories[3]['name']);
        $this->assertSame(10, $categories[3]['percentage']);
        $this->assertSame('#F59E0B', $categories[3]['color']);
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
    }

    /**
     * Kiểm thử phương thức getRecentBookings trả về danh sách giao dịch đặt phòng mới nhất.
     */
    public function test_get_recent_bookings_returns_formatted_bookings(): void
    {
        $bookings = $this->dashboardService->getRecentBookings(5);

        $this->assertIsArray($bookings);
        $this->assertCount(3, $bookings);

        // Đơn đầu tiên
        $first = $bookings[0];
        $this->assertSame('#BK-2026-0891', $first['booking_code']);
        $this->assertSame('Nguyễn Văn An', $first['customer_name']);
        $this->assertSame('Vinpearl Resort Nha Trang', $first['destination_name']);
        $this->assertSame('07/10/2026', $first['booking_date']);
        $this->assertSame(3500000, $first['total_price']);
        $this->assertSame('3.500.000 đ', $first['formatted_price']);
        $this->assertSame('Hoàn thành', $first['status']);
        $this->assertSame('success', $first['status_badge']);

        // Đơn thứ hai
        $second = $bookings[1];
        $this->assertSame('#BK-2026-0890', $second['booking_code']);
        $this->assertSame('Trần Thị Mai', $second['customer_name']);
        $this->assertSame('InterContinental Đà Nẵng', $second['destination_name']);
        $this->assertSame('06/10/2026', $second['booking_date']);
        $this->assertSame('Đang xử lý', $second['status']);
        $this->assertSame('info', $second['status_badge']);

        // Đơn thứ ba
        $third = $bookings[2];
        $this->assertSame('#BK-2026-0889', $third['booking_code']);
        $this->assertSame('Lê Hoàng Nam', $third['customer_name']);
        $this->assertSame('Terracotta Resort Đà Lạt', $third['destination_name']);
        $this->assertSame('05/10/2026', $third['booking_date']);
        $this->assertSame('Chờ duyệt', $third['status']);
        $this->assertSame('warning', $third['status_badge']);
    }
}
