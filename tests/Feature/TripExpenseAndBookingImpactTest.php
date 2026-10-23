<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\City;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Expense;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use App\Services\Interfaces\TripServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử tính năng Kiểm tra ảnh hưởng của thay đổi Trip đến chi phí và Booking liên quan.
 * Tuân thủ nghiêm ngặt kiến trúc MVC 3 lớp, Service Interface và quy chuẩn kiểm thử tiếng Việt.
 */
class TripExpenseAndBookingImpactTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $otherUser;
    private Trip $trip;
    private RoomType $roomType;
    private TripServiceInterface $tripService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->tripService = app(TripServiceInterface::class);

        // Khởi tạo người dùng chủ sở hữu chuyến đi
        $this->owner = User::factory()->create([
            'name' => 'Nguyễn Trần Thành',
            'email' => 'thanh.tran@travelplanner.test',
            'role' => 'user',
        ]);

        // Khởi tạo người dùng khác để kiểm thử chống lỗi IDOR
        $this->otherUser = User::factory()->create([
            'name' => 'Lê Thị Thu',
            'email' => 'thu.le@travelplanner.test',
            'role' => 'user',
        ]);

        // Tạo chuyến đi mẫu thời gian 5 ngày từ 10/11/2026 đến 15/11/2026
        $this->trip = Trip::create([
            'user_id' => $this->owner->id,
            'name' => 'Du lịch Nha Trang 6N5Đ',
            'description' => 'Kế hoạch nghỉ dưỡng biển cùng gia đình.',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-15',
            'budget' => 20000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        // Tạo dữ liệu điểm đến và loại phòng để phục vụ Booking
        $city = City::factory()->create(['name' => 'Nha Trang']);
        $category = Category::factory()->create(['name' => 'Khách sạn & Resort']);
        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Vinpearl Resort Nha Trang',
        ]);

        $this->roomType = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'name' => 'Deluxe Ocean View',
            'price_per_night' => 2500000,
        ]);
    }

    /**
     * Kiểm tra quan hệ bookings() trong Model Trip liên kết chính xác các đơn đặt phòng.
     */
    public function test_trip_bookings_relationship(): void
    {
        $booking = Booking::create([
            'user_id' => $this->owner->id,
            'trip_id' => $this->trip->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-12',
            'total_price' => 5000000,
            'status' => 'confirmed',
            'booking_code' => 'TRIP-BKG-001',
        ]);

        $this->assertCount(1, $this->trip->bookings);
        $this->assertEquals($booking->id, $this->trip->bookings->first()->id);
    }

    /**
     * Kiểm tra hàm getAffectedBookings phát hiện các đơn đặt phòng nằm ngoài khoảng thời gian mới của chuyến đi.
     */
    public function test_get_affected_bookings_detects_date_conflicts(): void
    {
        // Booking 1: Check-in ngày 10/11/2026, Check-out ngày 12/11/2026
        Booking::create([
            'user_id' => $this->owner->id,
            'trip_id' => $this->trip->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-12',
            'total_price' => 5000000,
            'status' => 'confirmed',
            'booking_code' => 'BKG-01',
        ]);

        // Booking 2: Check-in ngày 13/11/2026, Check-out ngày 16/11/2026
        Booking::create([
            'user_id' => $this->owner->id,
            'trip_id' => $this->trip->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => '2026-11-13',
            'check_out_date' => '2026-11-16',
            'total_price' => 7500000,
            'status' => 'confirmed',
            'booking_code' => 'BKG-02',
        ]);

        // Khi người dùng đổi ngày chuyến đi thành: Bắt đầu 11/11/2026, Kết thúc 14/11/2026
        // Booking 1 bị ảnh hưởng vì check-in (10/11) trước ngày bắt đầu mới (11/11)
        // Booking 2 bị ảnh hưởng vì check-out (16/11) sau ngày kết thúc mới (14/11)
        $affectedBookings = $this->tripService->getAffectedBookings(
            $this->trip->id,
            '2026-11-11',
            '2026-11-14'
        );

        $this->assertTrue($affectedBookings['has_affected']);
        $this->assertEquals(2, $affectedBookings['affected_count']);
        $this->assertCount(2, $affectedBookings['items']);

        $booking1Impact = collect($affectedBookings['items'])->firstWhere('booking_code', 'BKG-01');
        $this->assertNotNull($booking1Impact);
        $this->assertEquals('before_start', $booking1Impact['issue_type']);

        $booking2Impact = collect($affectedBookings['items'])->firstWhere('booking_code', 'BKG-02');
        $this->assertNotNull($booking2Impact);
        $this->assertEquals('after_end', $booking2Impact['issue_type']);
    }

    /**
     * Kiểm tra khi chuyến đi bị chuyển trạng thái thành cancelled thì toàn bộ Booking đang hoạt động được cảnh báo.
     */
    public function test_get_affected_bookings_when_trip_is_cancelled(): void
    {
        Booking::create([
            'user_id' => $this->owner->id,
            'trip_id' => $this->trip->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-12',
            'total_price' => 5000000,
            'status' => 'confirmed',
            'booking_code' => 'BKG-CANCEL-TEST',
        ]);

        $affectedBookings = $this->tripService->getAffectedBookings(
            $this->trip->id,
            '2026-11-10',
            '2026-11-15',
            Trip::STATUS_CANCELLED
        );

        $this->assertTrue($affectedBookings['has_affected']);
        $this->assertEquals(1, $affectedBookings['affected_count']);
        $this->assertEquals('trip_cancelled', $affectedBookings['items'][0]['issue_type']);
    }

    /**
     * Kiểm tra hàm getAffectedExpenses phát hiện vượt ngân sách và chi tiêu ngoài thời gian chuyến đi.
     */
    public function test_get_affected_expenses_detects_over_budget_and_out_of_range(): void
    {
        // Tạo 2 khoản chi tiêu thực tế: tổng cộng 15.000.000 VNĐ
        // Khoản 1: 8.000.000 VNĐ vào ngày 10/11/2026
        Expense::create([
            'trip_id' => $this->trip->id,
            'category' => 'accommodation',
            'description' => 'Khách sạn Vinpearl 2 đêm',
            'amount' => 8000000,
            'expense_date' => '2026-11-10',
        ]);

        // Khoản 2: 7.000.000 VNĐ vào ngày 15/11/2026
        Expense::create([
            'trip_id' => $this->trip->id,
            'category' => 'food',
            'description' => 'Ăn uống hải sản làng chài',
            'amount' => 7000000,
            'expense_date' => '2026-11-15',
        ]);

        // Giả sử người dùng sửa giảm ngân sách từ 20.000.000 VNĐ xuống còn 10.000.000 VNĐ
        // Và rút ngắn chuyến đi từ 12/11/2026 đến 14/11/2026
        $expenseImpact = $this->tripService->getAffectedExpenses(
            $this->trip->id,
            '2026-11-12',
            '2026-11-14',
            10000000
        );

        // Kiểm tra logic vượt ngân sách
        $this->assertTrue($expenseImpact['is_over_budget']);
        $this->assertEquals(15000000, $expenseImpact['total_expenses_amount']);
        $this->assertEquals(10000000, $expenseImpact['new_budget']);
        $this->assertEquals(5000000, $expenseImpact['over_budget_amount']);
        $this->assertEquals(150.0, $expenseImpact['budget_usage_percent']);

        // Kiểm tra logic chi phí ngoài khoảng ngày
        $this->assertTrue($expenseImpact['has_affected_dates']);
        $this->assertCount(2, $expenseImpact['affected_date_items']);

        $outOfRangeDates = collect($expenseImpact['affected_date_items'])->pluck('expense_date')->toArray();
        $this->assertContains('2026-11-10', $outOfRangeDates);
        $this->assertContains('2026-11-15', $outOfRangeDates);
    }

    /**
     * Kiểm tra hàm checkTripChangeImpact tổng hợp đầy đủ các tác động đến cả Booking và Chi phí.
     */
    public function test_check_trip_change_impact_aggregates_results(): void
    {
        // Tạo booking và expense để kích hoạt cảnh báo
        Booking::create([
            'user_id' => $this->owner->id,
            'trip_id' => $this->trip->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-12',
            'total_price' => 5000000,
            'status' => 'confirmed',
            'booking_code' => 'AGG-BKG',
        ]);

        Expense::create([
            'trip_id' => $this->trip->id,
            'category' => 'transport',
            'description' => 'Vé máy bay khứ hồi',
            'amount' => 12000000,
            'expense_date' => '2026-11-10',
        ]);

        $impact = $this->tripService->checkTripChangeImpact($this->trip->id, [
            'start_date' => '2026-11-11',
            'end_date' => '2026-11-14',
            'budget' => 10000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        $this->assertTrue($impact['has_impact']);
        $this->assertTrue($impact['has_booking_impact']);
        $this->assertTrue($impact['has_expense_impact']);
        $this->assertNotEmpty($impact['summary_messages']);
        $this->assertTrue($impact['bookings']['has_affected']);
        $this->assertTrue($impact['expenses']['is_over_budget']);
        $this->assertTrue($impact['expenses']['has_affected_dates']);
    }

    /**
     * Kiểm tra API endpoint POST /trips/{trip}/check-impact phản hồi JSON hợp lệ cho chủ sở hữu chuyến đi.
     */
    public function test_check_impact_api_endpoint_returns_json_for_authorized_owner(): void
    {
        $response = $this->actingAs($this->owner)->postJson(route('trips.check-impact', $this->trip->id), [
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-14',
            'budget' => 15000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'has_impact',
            'has_booking_impact',
            'has_expense_impact',
            'has_itinerary_impact',
            'bookings' => [
                'has_affected',
                'total_count',
                'affected_count',
                'items',
            ],
            'expenses' => [
                'has_affected_dates',
                'affected_date_count',
                'is_over_budget',
                'over_budget_amount',
                'total_expenses_amount',
                'new_budget',
                'remaining_budget',
                'budget_usage_percent',
                'affected_date_items',
            ],
            'itinerary',
            'summary_messages',
        ]);
    }

    /**
     * Kiểm tra API endpoint POST /trips/{trip}/check-impact từ chối người dùng khác (403 Forbidden).
     */
    public function test_check_impact_api_endpoint_forbidden_for_other_user(): void
    {
        $response = $this->actingAs($this->otherUser)->postJson(route('trips.check-impact', $this->trip->id), [
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-14',
            'budget' => 15000000,
        ]);

        $response->assertForbidden();
    }

    /**
     * Kiểm tra màn hình edit hiển thị đầy đủ giao diện cảnh báo phụ thuộc Booking và Chi phí.
     */
    public function test_edit_trip_view_renders_dependency_warning_boxes(): void
    {
        // Tạo booking và expense để trang edit có dữ liệu cảnh báo ban đầu
        Booking::create([
            'user_id' => $this->owner->id,
            'trip_id' => $this->trip->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-12',
            'total_price' => 5000000,
            'status' => 'confirmed',
            'booking_code' => 'EDIT-BKG',
        ]);

        Expense::create([
            'trip_id' => $this->trip->id,
            'category' => 'shopping',
            'description' => 'Quà lưu niệm',
            'amount' => 1000000,
            'expense_date' => '2026-11-10',
        ]);

        $response = $this->actingAs($this->owner)->get(route('trips.edit', $this->trip->id));

        $response->assertOk();
        $response->assertSee('bookingDependencyWarningBox', false);
        $response->assertSee('expenseDependencyWarningBox', false);
        $response->assertSee('Cảnh báo: Có đơn đặt phòng (Booking) liên quan bị ảnh hưởng bởi thay đổi này', false);
        $response->assertSee('Lưu ý: Ảnh hưởng đến Chi phí & Ngân sách chuyến đi', false);
    }

    /**
     * Kiểm tra cập nhật chuyến đi thành công kèm theo flash cảnh báo ảnh hưởng khi có xung đột.
     */
    public function test_update_trip_flashes_impact_warning_when_conflict_occurs(): void
    {
        // Tạo booking ngày 10/11/2026
        Booking::create([
            'user_id' => $this->owner->id,
            'trip_id' => $this->trip->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-12',
            'total_price' => 5000000,
            'status' => 'confirmed',
            'booking_code' => 'FLASH-BKG',
        ]);

        // Cập nhật ngày bắt đầu chuyến đi thành 12/11/2026 (làm booking ngày 10/11 bị ngoài phạm vi)
        $response = $this->actingAs($this->owner)->put(route('trips.update', $this->trip->id), [
            'name' => 'Du lịch Nha Trang 6N5Đ (Đã dời ngày)',
            'description' => 'Điều chỉnh ngày khởi hành.',
            'start_date' => '2026-11-12',
            'end_date' => '2026-11-15',
            'budget' => 20000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        $response->assertRedirect(route('trips.show', $this->trip->id));
        $response->assertSessionHas('impact_warning');
        $response->assertSessionHas('impact_details');
    }
}
