<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\ItineraryItem;
use App\Models\Trip;
use App\Models\User;
use App\Services\Interfaces\TripServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripShortenDateItineraryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Destination $destinationA;
    private Destination $destinationB;
    private Destination $destinationC;

    protected function setUp(): void
    {
        parent::setUp();
        // Vô hiệu hóa CSRF middleware trong môi trường test để tập trung kiểm thử logic nghiệp vụ
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->user = User::factory()->create();

        $city = City::create([
            'name' => 'Đà Nẵng',
            'slug' => 'da-nang',
            'description' => 'Thành phố đáng sống',
        ]);

        $category = Category::create([
            'name' => 'Danh lam thắng cảnh',
            'slug' => 'danh-lam-thang-canh',
            'description' => 'Các điểm tham quan',
        ]);

        $this->destinationA = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bà Nà Hills',
            'slug' => 'ba-na-hills',
            'description' => 'Khu du lịch trên đỉnh núi',
            'address' => 'Hòa Vang, Đà Nẵng',
            'rating' => 4.8,
        ]);

        $this->destinationB = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bán đảo Sơn Trà',
            'slug' => 'ban-dao-son-tra',
            'description' => 'Khu bảo tồn thiên nhiên',
            'address' => 'Sơn Trà, Đà Nẵng',
            'rating' => 4.7,
        ]);

        $this->destinationC = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Ngũ Hành Sơn',
            'slug' => 'ngu-hanh-son',
            'description' => 'Quần thể núi đá vôi',
            'address' => 'Ngũ Hành Sơn, Đà Nẵng',
            'rating' => 4.6,
        ]);
    }

    /**
     * Kiểm tra khi rút ngắn ngày của chuyến đi (ví dụ từ 5 ngày xuống 3 ngày),
     * các hoạt động lịch trình ở Ngày 4 và Ngày 5 tự động được dời an toàn sang Ngày 3 (ngày cuối cùng mới).
     */
    public function test_shortening_trip_duration_reassigns_out_of_range_itinerary_items_to_new_last_day(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Khám phá Đà Nẵng 5 Ngày 4 Đêm',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-14', // 5 ngày
            'budget' => 10000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        // Tạo các hoạt động lịch trình rải rác từ Ngày 1 đến Ngày 5
        $itemDay1 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationA->id,
            'day_number' => 1,
            'sort_order' => 1,
            'note' => 'Tham quan sáng Ngày 1',
        ]);

        $itemDay2 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationB->id,
            'day_number' => 2,
            'sort_order' => 1,
            'note' => 'Tham quan Ngày 2',
        ]);

        $itemDay4 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationC->id,
            'day_number' => 4,
            'sort_order' => 1,
            'note' => 'Hoạt động Ngày 4 cần bảo toàn',
        ]);

        $itemDay5 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationA->id,
            'day_number' => 5,
            'sort_order' => 1,
            'note' => 'Hoạt động Ngày 5 cần bảo toàn',
        ]);

        // Người dùng cập nhật ngày kết thúc thành 2026-10-12 (rút ngắn xuống 3 ngày)
        $response = $this->actingAs($this->user)->put(route('trips.update', $trip->id), [
            'name' => 'Khám phá Đà Nẵng 3 Ngày (Đã rút ngắn)',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12', // 3 ngày
            'budget' => 10000000,
        ]);

        $response->assertRedirect(route('trips.show', $trip->id));
        $response->assertSessionHas('success');

        // Kiểm tra database: Ngày chuyến đi đã cập nhật
        $trip->refresh();
        $this->assertSame(3, $trip->total_days);
        $this->assertSame('3 ngày 2 đêm', $trip->duration_label);

        // Kiểm tra các hoạt động lịch trình:
        // Mục Ngày 1 và Ngày 2 không bị thay đổi
        $this->assertSame(1, $itemDay1->fresh()->day_number);
        $this->assertSame(2, $itemDay2->fresh()->day_number);

        // Mục Ngày 4 và Ngày 5 đã được tự động dời sang Ngày 3 an toàn
        $this->assertSame(3, $itemDay4->fresh()->day_number);
        $this->assertSame(3, $itemDay5->fresh()->day_number);
    }

    /**
     * Kiểm tra khi các hoạt động lịch trình được dời sang ngày cuối cùng mới,
     * thứ tự sort_order được tăng dần liên tục tiếp nối các mục sẵn có của ngày đó, không bị trùng lặp.
     */
    public function test_reassigned_items_have_proper_consecutive_sort_order(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi kiểm tra thứ tự sắp xếp',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-14', // 5 ngày
            'budget' => 8000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        // Ngày 3 ban đầu đã có 2 hoạt động với sort_order 1 và 2
        $existingDay3Item1 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationA->id,
            'day_number' => 3,
            'sort_order' => 1,
            'note' => 'Mục 1 sẵn có Ngày 3',
        ]);

        $existingDay3Item2 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationB->id,
            'day_number' => 3,
            'sort_order' => 2,
            'note' => 'Mục 2 sẵn có Ngày 3',
        ]);

        // Mục ở Ngày 4 và Ngày 5
        $itemDay4 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationC->id,
            'day_number' => 4,
            'sort_order' => 1,
        ]);

        $itemDay5 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationA->id,
            'day_number' => 5,
            'sort_order' => 1,
        ]);

        /** @var TripServiceInterface $tripService */
        $tripService = app(TripServiceInterface::class);

        // Gọi xử lý rút ngắn ngày về 3 ngày
        $result = $tripService->handleTripDateChange($trip->id, '2026-10-10', '2026-10-12');

        $this->assertTrue($result['is_shortened']);
        $this->assertSame(2, $result['reassigned_count']);
        $this->assertSame(3, $result['target_day']);

        // Kiểm tra sort_order các mục ở Ngày 3:
        $this->assertSame(1, $existingDay3Item1->fresh()->sort_order);
        $this->assertSame(2, $existingDay3Item2->fresh()->sort_order);
        $this->assertSame(3, $itemDay4->fresh()->sort_order);
        $this->assertSame(4, $itemDay5->fresh()->sort_order);
    }

    /**
     * Kiểm tra khi mở rộng số ngày của chuyến đi (ví dụ từ 3 ngày lên 5 ngày),
     * các hoạt động lịch trình hiện có không bị thay đổi và giữ nguyên vẹn vị trí ngày.
     */
    public function test_extending_trip_duration_preserves_all_itinerary_items(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi mở rộng thời gian',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12', // 3 ngày
            'budget' => 5000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        $item1 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationA->id,
            'day_number' => 1,
            'sort_order' => 1,
        ]);

        $item3 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationB->id,
            'day_number' => 3,
            'sort_order' => 1,
        ]);

        /** @var TripServiceInterface $tripService */
        $tripService = app(TripServiceInterface::class);

        // Mở rộng thành 5 ngày
        $result = $tripService->handleTripDateChange($trip->id, '2026-10-10', '2026-10-14');

        $this->assertFalse($result['is_shortened']);
        $this->assertSame(0, $result['reassigned_count']);

        $this->assertSame(1, $item1->fresh()->day_number);
        $this->assertSame(3, $item3->fresh()->day_number);
    }

    /**
     * Kiểm tra khi dịch chuyển toàn bộ khoảng thời gian nhưng giữ nguyên số ngày,
     * các hoạt động lịch trình vẫn giữ nguyên day_number và thứ tự.
     */
    public function test_shifting_trip_dates_without_changing_duration_preserves_day_numbers(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi dời lịch',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-13', // 4 ngày
            'budget' => 6000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        $itemDay2 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationA->id,
            'day_number' => 2,
            'sort_order' => 1,
        ]);

        $itemDay4 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationB->id,
            'day_number' => 4,
            'sort_order' => 1,
        ]);

        /** @var TripServiceInterface $tripService */
        $tripService = app(TripServiceInterface::class);

        // Dời sang tuần sau (2026-10-17 đến 2026-10-20, vẫn đúng 4 ngày)
        $result = $tripService->handleTripDateChange($trip->id, '2026-10-17', '2026-10-20');

        $this->assertFalse($result['is_shortened']);
        $this->assertSame(0, $result['reassigned_count']);

        $this->assertSame(2, $itemDay2->fresh()->day_number);
        $this->assertSame(4, $itemDay4->fresh()->day_number);
    }

    /**
     * Kiểm tra phương thức dịch vụ getAffectedItineraryItems trả về cấu trúc chi tiết
     * phục vụ hiển thị cảnh báo tương tác phía giao diện người dùng.
     */
    public function test_get_affected_itinerary_items_service_method_returns_correct_details(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi kiểm tra xem trước cảnh báo',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15', // 6 ngày
            'budget' => 12000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationA->id,
            'day_number' => 5,
            'sort_order' => 1,
            'start_time' => '09:00:00',
            'end_time' => '11:30:00',
            'note' => 'Ghi chú hoạt động ngày 5',
        ]);

        ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationB->id,
            'day_number' => 6,
            'sort_order' => 1,
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'note' => 'Ghi chú hoạt động ngày 6',
        ]);

        /** @var TripServiceInterface $tripService */
        $tripService = app(TripServiceInterface::class);

        // Giả lập rút ngắn chuyến đi về 4 ngày (2026-10-10 đến 2026-10-13)
        $preview = $tripService->getAffectedItineraryItems($trip->id, '2026-10-10', '2026-10-13');

        $this->assertTrue($preview['is_shortened']);
        $this->assertSame(6, $preview['old_total_days']);
        $this->assertSame(4, $preview['new_total_days']);
        $this->assertSame(4, $preview['target_day']);
        $this->assertSame(2, $preview['affected_count']);
        $this->assertCount(2, $preview['affected_items']);

        $firstAffected = $preview['affected_items'][0];
        $this->assertSame(5, $firstAffected['original_day_number']);
        $this->assertSame(4, $firstAffected['target_day_number']);
        $this->assertSame('Bà Nà Hills', $firstAffected['destination_name']);
        $this->assertSame('09:00', $firstAffected['start_time']);
    }

    /**
     * Kiểm tra màn hình chỉnh sửa chuyến đi (trips.edit) render đúng hộp cảnh báo lịch trình phụ thuộc
     * và nhúng mảng JSON các hoạt động lịch trình của chuyến đi.
     */
    public function test_edit_trip_screen_renders_itinerary_dependency_data_and_warning_container(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi kiểm tra giao diện chỉnh sửa',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-14',
            'budget' => 9000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationA->id,
            'day_number' => 4,
            'sort_order' => 1,
            'note' => 'Điểm đến ngày 4',
        ]);

        $response = $this->actingAs($this->user)->get(route('trips.edit', $trip->id));

        $response->assertStatus(200);
        $response->assertSee('id="itineraryDependencyWarningBox"', false);
        $response->assertSee('id="affectedBadge"', false);
        $response->assertSee('id="affectedItemsList"', false);
        $response->assertSee('tripItineraryItems', false);
        $response->assertSee('originalTotalDays', false);
        $response->assertSee('Bà Nà Hills', false);
    }

    /**
     * Kiểm tra khi cập nhật rút ngắn ngày thành công, hệ thống redirect về trang show
     * kèm flash message thông báo cụ thể số hoạt động đã được bảo toàn dời ngày.
     */
    public function test_trip_update_redirects_with_detailed_flash_message_when_itinerary_reassigned(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi thử nghiệm flash message',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-14', // 5 ngày
            'budget' => 7000000,
            'status' => Trip::STATUS_PLANNED,
        ]);

        ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $this->destinationC->id,
            'day_number' => 5,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->user)->put(route('trips.update', $trip->id), [
            'name' => 'Chuyến đi đã rút ngắn',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12', // 3 ngày
            'budget' => 7000000,
        ]);

        $response->assertRedirect(route('trips.show', $trip->id));
        $response->assertSessionHas('success', function ($msg) {
            return str_contains($msg, 'rút ngắn') && str_contains($msg, 'tự động bảo toàn 1 hoạt động lịch trình sang Ngày 3');
        });
    }

    /**
     * Kiểm tra không thể cập nhật chuyến đi khi ngày kết thúc nhỏ hơn ngày bắt đầu,
     * hệ thống chặn lại và báo lỗi xác thực form.
     */
    public function test_cannot_update_trip_when_end_date_before_start_date(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi kiểm tra ngày sai',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-12',
            'budget' => 5000000,
        ]);

        $response = $this->actingAs($this->user)->put(route('trips.update', $trip->id), [
            'name' => 'Chuyến đi cập nhật ngày sai',
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-10', // Ngày kết thúc trước ngày bắt đầu
            'budget' => 5000000,
        ]);

        $response->assertSessionHasErrors(['end_date']);
    }
}
