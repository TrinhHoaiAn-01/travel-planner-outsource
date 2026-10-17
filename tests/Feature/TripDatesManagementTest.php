<?php

namespace Tests\Feature;

use App\Models\ItineraryItem;
use App\Models\Trip;
use App\Models\User;
use App\Services\Interfaces\TripServiceInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripDatesManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Vô hiệu hóa CSRF middleware trong môi trường test để tập trung kiểm thử logic nghiệp vụ
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /**
     * Kiểm tra tính toán số ngày (total_days), số đêm (total_nights),
     * và nhãn thời lượng (duration_label) trên Model Trip cho chuyến đi nhiều ngày.
     */
    public function test_trip_dates_duration_and_nights_calculation(): void
    {
        $user = User::factory()->create();

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15', // 6 ngày 5 đêm
        ]);

        $this->assertSame(6, $trip->total_days);
        $this->assertSame(5, $trip->total_nights);
        $this->assertSame('6 ngày 5 đêm', $trip->duration_label);
        $this->assertSame('10/10/2026 — 15/10/2026', $trip->date_range_formatted);
        $this->assertTrue($trip->isDatesValid());
    }

    /**
     * Kiểm tra tính toán cho chuyến đi trong ngày (1 ngày 0 đêm).
     */
    public function test_single_day_trip_duration_calculation(): void
    {
        $user = User::factory()->create();

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-10', // 1 ngày
        ]);

        $this->assertSame(1, $trip->total_days);
        $this->assertSame(0, $trip->total_nights);
        $this->assertSame('1 ngày', $trip->duration_label);
        $this->assertSame('10/10/2026 — 10/10/2026', $trip->date_range_formatted);
        $this->assertTrue($trip->isDatesValid());
    }

    /**
     * Kiểm tra chuyến đi khi chưa có ngày bắt đầu hoặc kết thúc.
     */
    public function test_trip_without_dates_returns_defaults(): void
    {
        $trip = new Trip([
            'start_date' => null,
            'end_date' => null,
        ]);

        $this->assertSame(0, $trip->total_days);
        $this->assertSame(0, $trip->total_nights);
        $this->assertSame('Chưa xác định', $trip->duration_label);
        $this->assertSame('Chưa xác định', $trip->date_range_formatted);
        $this->assertFalse($trip->isDatesValid());
    }

    /**
     * Kiểm tra hàm tính toán thời lượng trong TripService (calculateTripDuration).
     */
    public function test_trip_service_calculate_duration_helper(): void
    {
        $service = app(TripServiceInterface::class);

        // Trường hợp hợp lệ
        $result = $service->calculateTripDuration('2026-10-10', '2026-10-13');
        $this->assertTrue($result['is_valid']);
        $this->assertSame(4, $result['days']);
        $this->assertSame(3, $result['nights']);
        $this->assertSame('4 ngày 3 đêm', $result['label']);

        // Trường hợp ngày kết thúc trước ngày bắt đầu
        $invalidResult = $service->calculateTripDuration('2026-10-15', '2026-10-10');
        $this->assertFalse($invalidResult['is_valid']);
        $this->assertSame(0, $invalidResult['days']);
        $this->assertSame(0, $invalidResult['nights']);

        // Trường hợp thiếu dữ liệu ngày
        $nullResult = $service->calculateTripDuration(null, '2026-10-10');
        $this->assertFalse($nullResult['is_valid']);
    }

    /**
     * Kiểm tra TripService tạo danh sách các ngày chi tiết (getTripDaysList) kèm thứ trong tuần tiếng Việt.
     */
    public function test_trip_service_get_trip_days_list(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-10-10', // Thứ Bảy
            'end_date' => '2026-10-12',   // Thứ Hai -> 3 ngày
        ]);

        $service = app(TripServiceInterface::class);
        $daysList = $service->getTripDaysList($trip->id);

        $this->assertCount(3, $daysList);

        $this->assertSame(1, $daysList[0]['day_number']);
        $this->assertSame('2026-10-10', $daysList[0]['date']);
        $this->assertSame('10/10/2026', $daysList[0]['formatted_date']);
        $this->assertSame('Thứ Bảy', $daysList[0]['day_name']);

        $this->assertSame(2, $daysList[1]['day_number']);
        $this->assertSame('2026-10-11', $daysList[1]['date']);
        $this->assertSame('Chủ Nhật', $daysList[1]['day_name']);

        $this->assertSame(3, $daysList[2]['day_number']);
        $this->assertSame('2026-10-12', $daysList[2]['date']);
        $this->assertSame('Thứ Hai', $daysList[2]['day_name']);
    }

    /**
     * Kiểm tra form request chặn tạo chuyến đi khi ngày kết thúc trước ngày bắt đầu.
     */
    public function test_user_cannot_create_trip_when_end_date_before_start_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('trips.store'), [
            'name' => 'Chuyến đi lỗi thời gian',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-15',
        ]);

        $response->assertSessionHasErrors(['end_date']);
        $this->assertDatabaseCount('trips', 0);
    }

    /**
     * Kiểm tra form request chặn cập nhật chuyến đi khi ngày kết thúc trước ngày bắt đầu.
     */
    public function test_user_cannot_update_trip_when_end_date_before_start_date(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
        ]);

        $response = $this->actingAs($user)->put(route('trips.update', $trip->id), [
            'name' => $trip->name,
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-15',
        ]);

        $response->assertSessionHasErrors(['end_date']);

        $trip->refresh();
        $this->assertSame('2026-10-10', $trip->start_date->format('Y-m-d'));
        $this->assertSame('2026-10-15', $trip->end_date->format('Y-m-d'));
    }

    /**
     * Kiểm tra người dùng có thể cập nhật ngày chuyến đi thành công.
     */
    public function test_user_can_update_trip_dates_successfully(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Chuyến đi cập nhật ngày',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
        ]);

        $response = $this->actingAs($user)->put(route('trips.update', $trip->id), [
            'name' => 'Chuyến đi đã dời lịch',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
        ]);

        $response->assertRedirect(route('trips.show', $trip->id));
        $response->assertSessionHas('success');

        $trip->refresh();
        $this->assertSame('2026-11-01', $trip->start_date->format('Y-m-d'));
        $this->assertSame('2026-11-05', $trip->end_date->format('Y-m-d'));
        $this->assertSame(5, $trip->total_days);
    }

    /**
     * Kiểm tra khi rút ngắn ngày chuyến đi, các hoạt động lịch trình vượt quá số ngày mới
     * sẽ được dời về ngày cuối cùng an toàn thay vì bị xóa mất dữ liệu.
     */
    public function test_shortening_trip_duration_reassigns_out_of_range_itinerary_items_safely(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-14', // 5 ngày
        ]);

        // Tạo hoạt động ở Ngày 1, Ngày 3, và Ngày 5
        $itemDay1 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'day_number' => 1,
            'title' => 'Check-in khách sạn',
            'sort_order' => 1,
        ]);

        $itemDay5 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'day_number' => 5,
            'title' => 'Mua quà lưu niệm và ra sân bay',
            'sort_order' => 1,
        ]);

        // Cập nhật rút ngắn chuyến đi từ 5 ngày xuống còn 3 ngày (10/10 -> 12/10)
        $response = $this->actingAs($user)->put(route('trips.update', $trip->id), [
            'name' => $trip->name,
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12', // 3 ngày
        ]);

        $response->assertRedirect(route('trips.show', $trip->id));

        // Kiểm tra item ngày 1 giữ nguyên
        $itemDay1->refresh();
        $this->assertSame(1, $itemDay1->day_number);

        // Kiểm tra item ngày 5 đã được tự động dời về ngày 3
        $itemDay5->refresh();
        $this->assertSame(3, $itemDay5->day_number);
    }

    /**
     * Kiểm tra màn hình chi tiết chuyến đi hiển thị đầy đủ các tab Ngày tương ứng theo số ngày.
     */
    public function test_trip_show_displays_dynamic_days_tabs(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Khám phá Hà Giang 3N2Đ',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12', // 3 ngày
        ]);

        $response = $this->actingAs($user)->get(route('trips.show', $trip->id));

        $response->assertStatus(200);
        $response->assertSeeText('Khám phá Hà Giang 3N2Đ', false);
        $response->assertSee('Ngày 1');
        $response->assertSee('Ngày 2');
        $response->assertSee('Ngày 3');
        $response->assertDontSee('Ngày 4');
    }
}
