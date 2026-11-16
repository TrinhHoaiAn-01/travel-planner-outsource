<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardService $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = new DashboardService();
    }

    /**
     * Kiểm thử phương thức getSummaryStatistics trả về đầy đủ các chỉ số thống kê tổng quan realtime.
     */
    public function test_get_summary_statistics_returns_expected_structure_and_values(): void
    {
        // Tạo dữ liệu mẫu trong DB để kiểm tra tính toán realtime
        $user = User::create([
            'name' => 'Nguyễn Văn An',
            'email' => 'an@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $city = City::create([
            'name' => 'Đà Nẵng',
            'slug' => 'da-nang',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Khách Sạn',
            'slug' => 'khach-san',
            'icon' => 'hotel',
            'is_active' => true,
        ]);

        $destination = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Vinpearl Resort',
            'slug' => 'vinpearl-resort',
            'entrance_fee' => 1000000,
            'rating' => 4.8,
            'is_featured' => true,
        ]);

        $roomType = RoomType::create([
            'destination_id' => $destination->id,
            'name' => 'Phòng Deluxe',
            'price_per_night' => 1000000,
            'capacity' => 2,
            'quantity' => 5,
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'room_type_id' => $roomType->id,
            'room_count' => 1,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'total_price' => 2000000,
            'status' => 'completed',
            'booking_code' => '#BK-2026-TEST',
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'amount' => 2000000,
            'payment_method' => 'banking',
            'transaction_id' => 'TXN-TEST-001',
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        $summary = $this->dashboardService->getSummaryStatistics();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_users', $summary);
        $this->assertArrayHasKey('user_growth', $summary);
        $this->assertArrayHasKey('total_destinations', $summary);
        $this->assertArrayHasKey('total_bookings', $summary);
        $this->assertArrayHasKey('total_revenue', $summary);
        $this->assertArrayHasKey('pending_reviews', $summary);
        $this->assertArrayHasKey('system_status', $summary);

        $this->assertSame('1', $summary['total_users']);
        $this->assertSame('1', $summary['total_destinations']);
        $this->assertSame('1', $summary['total_bookings']);
        $this->assertSame('2.000.000 đ', $summary['total_revenue']);
        $this->assertSame('Hoạt động (Realtime)', $summary['system_status']);
    }

    /**
     * Kiểm thử phương thức getMonthlyStatistics trả về đúng 12 tháng dữ liệu năm 2026.
     */
    public function test_get_monthly_statistics_returns_12_months_for_year_2026(): void
    {
        $monthlyStats = $this->dashboardService->getMonthlyStatistics(2026);

        $this->assertIsArray($monthlyStats);
        $this->assertSame(2026, $monthlyStats['year']);
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
    }

    /**
     * Kiểm thử phương thức getBookingCategories trả về tỷ lệ đặt phòng theo loại.
     */
    public function test_get_booking_categories_returns_expected_distribution(): void
    {
        $categories = $this->dashboardService->getBookingCategories(4);

        $this->assertIsArray($categories);
        $this->assertCount(4, $categories);

        foreach ($categories as $cat) {
            $this->assertArrayHasKey('rank', $cat);
            $this->assertArrayHasKey('name', $cat);
            $this->assertArrayHasKey('percentage', $cat);
            $this->assertArrayHasKey('color', $cat);
            $this->assertIsInt($cat['percentage']);
        }
    }

    /**
     * Kiểm thử phương thức getTopDestinations trả về danh sách điểm đến phổ biến được xếp hạng.
     */
    public function test_get_top_destinations_returns_ranked_destinations(): void
    {
        $destinations = $this->dashboardService->getTopDestinations(4);

        $this->assertIsArray($destinations);
        $this->assertCount(4, $destinations);

        foreach ($destinations as $dest) {
            $this->assertArrayHasKey('rank', $dest);
            $this->assertArrayHasKey('name', $dest);
            $this->assertArrayHasKey('percentage', $dest);
            $this->assertArrayHasKey('color', $dest);
        }
    }

    /**
     * Kiểm thử phương thức getRecentBookings trả về danh sách giao dịch đặt phòng mới nhất.
     */
    public function test_get_recent_bookings_returns_formatted_bookings(): void
    {
        $user = User::create([
            'name' => 'Nguyễn Văn An',
            'email' => 'an@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_active' => true,
        ]);

        $city = City::create([
            'name' => 'Nha Trang',
            'slug' => 'nha-trang',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Resort',
            'slug' => 'resort',
            'icon' => 'hotel',
            'is_active' => true,
        ]);

        $destination = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Vinpearl Resort Nha Trang',
            'slug' => 'vinpearl-resort-nha-trang',
            'entrance_fee' => 3500000,
            'rating' => 5.0,
            'is_featured' => true,
        ]);

        $roomType = RoomType::create([
            'destination_id' => $destination->id,
            'name' => 'Biệt thự biển',
            'price_per_night' => 3500000,
            'capacity' => 4,
            'quantity' => 2,
        ]);

        Booking::create([
            'user_id' => $user->id,
            'room_type_id' => $roomType->id,
            'room_count' => 1,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(3)->toDateString(),
            'total_price' => 3500000,
            'status' => 'completed',
            'booking_code' => '#BK-2026-0891',
        ]);

        $bookings = $this->dashboardService->getRecentBookings(5);

        $this->assertIsArray($bookings);
        $this->assertCount(1, $bookings);

        $first = $bookings[0];
        $this->assertSame('#BK-2026-0891', $first['booking_code']);
        $this->assertSame('Nguyễn Văn An', $first['customer_name']);
        $this->assertSame('Vinpearl Resort Nha Trang', $first['destination_name']);
        $this->assertSame(3500000.0, $first['total_price']);
        $this->assertSame('3.500.000 đ', $first['formatted_price']);
        $this->assertSame('completed', $first['status']);
        $this->assertSame('Hoàn thành', $first['status_label']);
        $this->assertSame('success', $first['status_badge']);
    }
}
