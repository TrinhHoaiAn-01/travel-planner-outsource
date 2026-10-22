<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\ItineraryItem;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Test cho Chức năng 5: Xem lịch trình theo từng ngày của chuyến đi.
 * Thành viên phụ trách: Phạm Thị Ngọc Ái (MSSV: 24211TT3767).
 */
class ItineraryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Khách chưa đăng nhập không thể xem lịch trình và bị chuyển hướng về login (BR-01).
     */
    public function test_guest_cannot_view_trip_itinerary(): void
    {
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Chuyến đi Đà Nẵng 3 ngày',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $response = $this->get(route('itinerary.index', $trip->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng không phải chủ sở hữu chuyến đi bị chặn với mã lỗi 403 Forbidden (BR-03 chống IDOR).
     */
    public function test_user_cannot_view_another_users_trip_itinerary(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $stranger = User::factory()->create(['email' => 'stranger@example.com']);

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi riêng tư của Owner',
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'budget' => 10000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($stranger)->get(route('itinerary.index', $trip->id));

        $response->assertStatus(403);
    }

    /**
     * Chủ chuyến đi có thể xem lịch trình và nhìn thấy đầy đủ thông tin chuyến đi cùng các tab ngày.
     */
    public function test_owner_can_view_trip_itinerary_and_see_trip_details(): void
    {
        $owner = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Khám phá Hà Nội Mùa Thu',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 6000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($owner)->get(route('itinerary.index', $trip->id));

        $response->assertStatus(200);
        $response->assertSee('Khám phá Hà Nội Mùa Thu');
        $response->assertSee('6.000.000₫');
        $response->assertSee('Ngày 1');
        $response->assertSee('Ngày 2');
        $response->assertSee('Ngày 3');
    }

    /**
     * Chủ chuyến đi có thể xem danh sách hoạt động theo từng ngày cụ thể (tham số day_number).
     */
    public function test_owner_can_view_specific_day_itinerary_items(): void
    {
        $owner = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Du lịch Sài Gòn 2 ngày',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-02',
            'budget' => 4000000,
            'status' => 'planned',
        ]);

        $city = \App\Models\City::factory()->create();
        $category = \App\Models\Category::factory()->create();

        $dest1 = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Nhà thờ Đức Bà',
        ]);

        $dest2 = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Chợ Bến Thành',
        ]);

        // Mục ngày 1
        ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $dest1->id,
            'day_number' => 1,
            'start_time' => '08:30:00',
            'end_time' => '10:00:00',
            'note' => 'Chụp hình kỷ niệm',
            'sort_order' => 1,
        ]);

        // Mục ngày 2
        ItineraryItem::create([
            'trip_id' => $trip->id,
            'destination_id' => $dest2->id,
            'day_number' => 2,
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'note' => 'Thưởng thức ẩm thực và mua đặc sản',
            'sort_order' => 1,
        ]);

        // Xem ngày 1
        $responseDay1 = $this->actingAs($owner)->get(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 1]));
        $responseDay1->assertStatus(200);
        $responseDay1->assertSee('Nhà thờ Đức Bà');
        $responseDay1->assertSee('Chụp hình kỷ niệm');
        $responseDay1->assertDontSee('Chợ Bến Thành');

        // Xem ngày 2
        $responseDay2 = $this->actingAs($owner)->get(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 2]));
        $responseDay2->assertStatus(200);
        $responseDay2->assertSee('Chợ Bến Thành');
        $responseDay2->assertSee('Thưởng thức ẩm thực và mua đặc sản');
        $responseDay2->assertDontSee('Nhà thờ Đức Bà');
    }

    /**
     * Ngày không có hoạt động nào sẽ hiển thị giao diện trạng thái rỗng (Empty State).
     */
    public function test_empty_day_displays_empty_state_prompt(): void
    {
        $owner = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi nghỉ dưỡng',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'budget' => 8000000,
            'status' => 'planned',
        ]);

        // Truy cập Ngày 2 (chưa có hoạt động nào)
        $response = $this->actingAs($owner)->get(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 2]));

        $response->assertStatus(200);
        $response->assertSee('Chưa có hoạt động nào trong Ngày 2');
    }
}
