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

    /**
     * Khách chưa đăng nhập không thể truy cập trang tạo hoặc lưu hoạt động lịch trình.
     */
    public function test_guest_cannot_access_create_or_store_activity(): void
    {
        $user = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $user->id,
            'name' => 'Trip kiểm tra bảo mật',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $this->get(route('itinerary.create', $trip->id))->assertRedirect(route('login'));
        $this->post(route('itinerary.store', $trip->id), [
            'activity_name' => 'Tắm biển Mỹ Khê',
            'day_number' => 1,
        ])->assertRedirect(route('login'));
    }

    /**
     * Người dùng không thể thêm hoạt động vào chuyến đi của người khác (Chống IDOR - BR-03).
     */
    public function test_user_cannot_add_activity_to_another_users_trip(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip của Owner',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $this->actingAs($stranger)
            ->get(route('itinerary.create', $trip->id))
            ->assertStatus(403);

        $this->actingAs($stranger)
            ->post(route('itinerary.store', $trip->id), [
                'activity_name' => 'Hoạt động bất hợp pháp',
                'day_number' => 1,
            ])
            ->assertStatus(403);
    }

    /**
     * Chủ chuyến đi có thể mở màn hình thêm hoạt động (Hình 19).
     */
    public function test_owner_can_view_create_activity_screen(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Khám phá Phú Quốc',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 10000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($owner)->get(route('itinerary.create', ['trip' => $trip->id, 'day_number' => 2]));

        $response->assertStatus(200);
        $response->assertSee('Thêm Hoạt Động Mới');
        $response->assertSee('Tên hoạt động');
        $response->assertSee('Giờ bắt đầu');
        $response->assertSee('Giờ kết thúc');
        $response->assertSee('Chi phí dự kiến (đ)');
        $response->assertSee('Lưu hoạt động');
    }

    /**
     * Thêm hoạt động thành công vào lịch trình của ngày được chọn.
     */
    public function test_owner_can_successfully_add_activity_to_itinerary(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Đà Nẵng',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 6000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($owner)->post(route('itinerary.store', $trip->id), [
            'activity_name' => 'Tham quan Bán đảo Sơn Trà & Chùa Linh Ứng',
            'day_number' => 1,
            'start_time' => '14:00',
            'end_time' => '16:30',
            'activity_type' => 'Tham quan / Check-in',
            'estimated_cost' => 200000,
            'note' => 'Nhớ mang theo máy ảnh và nón rộng vành',
        ]);

        $response->assertRedirect(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 1]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('itinerary_items', [
            'trip_id' => $trip->id,
            'day_number' => 1,
            'custom_title' => 'Tham quan Bán đảo Sơn Trà & Chùa Linh Ứng',
            'start_time' => '14:00:00',
            'end_time' => '16:30:00',
            'note' => 'Nhớ mang theo máy ảnh và nón rộng vành',
        ]);

        // Kiểm tra đồng bộ khoản chi vào expenses
        $this->assertDatabaseHas('expenses', [
            'trip_id' => $trip->id,
            'amount' => 200000,
        ]);
    }

    /**
     * Bắt buộc phải nhập tên hoạt động (Validation: Bắt buộc nhập).
     */
    public function test_adding_activity_validates_required_activity_name(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip thử nghiệm',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02',
            'budget' => 3000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($owner)->post(route('itinerary.store', $trip->id), [
            'activity_name' => '',
            'day_number' => 1,
        ]);

        $response->assertSessionHasErrors(['activity_name']);
        $this->assertEquals(0, ItineraryItem::where('trip_id', $trip->id)->count());
    }

    /**
     * Giờ kết thúc phải lớn hơn giờ bắt đầu (Validation: Phải lớn hơn giờ bắt đầu).
     */
    public function test_adding_activity_validates_end_time_must_be_after_start_time(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip kiểm tra giờ',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02',
            'budget' => 3000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($owner)->post(route('itinerary.store', $trip->id), [
            'activity_name' => 'Đi dạo biển',
            'day_number' => 1,
            'start_time' => '16:00',
            'end_time' => '14:00', // Không hợp lệ: giờ kết thúc trước giờ bắt đầu
        ]);

        $response->assertSessionHasErrors(['end_time']);
    }

    /**
     * Chặn nhập khung giờ chồng chéo / trùng lặp trong cùng ngày (Quy tắc FR19).
     */
    public function test_adding_activity_validates_time_overlap_on_same_day(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip kiểm tra trùng lịch',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02',
            'budget' => 3000000,
            'status' => 'planned',
        ]);

        // Tạo sẵn hoạt động 1: 08:00 - 11:00
        ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Hoạt động buổi sáng',
            'day_number' => 1,
            'start_time' => '08:00:00',
            'end_time' => '11:00:00',
        ]);

        // Thử thêm hoạt động 2 bị chồng lấn: 09:30 - 12:00
        $response = $this->actingAs($owner)->post(route('itinerary.store', $trip->id), [
            'activity_name' => 'Hoạt động bị trùng',
            'day_number' => 1,
            'start_time' => '09:30',
            'end_time' => '12:00',
        ]);

        $response->assertSessionHasErrors(['start_time']);
        $this->assertEquals(1, ItineraryItem::where('trip_id', $trip->id)->count());
    }

    /**
     * Ngày thực hiện phải nằm trong khoảng thời gian của chuyến đi (Quy tắc FR18).
     */
    public function test_adding_activity_validates_day_number_within_trip_range(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip 2 ngày',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02',
            'budget' => 3000000,
            'status' => 'planned',
        ]);

        // Chuyến đi có 2 ngày, thử thêm vào Ngày 5
        $response = $this->actingAs($owner)->post(route('itinerary.store', $trip->id), [
            'activity_name' => 'Hoạt động ngày 5',
            'day_number' => 5,
        ]);

        $response->assertSessionHasErrors(['day_number']);
    }

    /**
     * Chủ sở hữu chuyến đi có thể mở màn hình chỉnh sửa hoạt động (Chức năng 7).
     */
    public function test_owner_can_view_edit_activity_screen(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Hà Nội',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Dạo quanh Hồ Gươm',
            'day_number' => 1,
            'start_time' => '08:30:00',
            'end_time' => '10:30:00',
            'note' => 'Chụp ảnh tháp Rùa',
        ]);

        $response = $this->actingAs($owner)->get(route('itinerary.edit', $item->id));

        $response->assertStatus(200);
        $response->assertSee('Chỉnh Sửa Hoạt Động');
        $response->assertSee('Dạo quanh Hồ Gươm');
        $response->assertSee('08:30');
        $response->assertSee('10:30');
    }

    /**
     * Khách hoặc người dùng khác không thể mở màn hình chỉnh sửa hoạt động của người khác.
     */
    public function test_unauthorized_user_cannot_access_edit_activity_screen(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip riêng tư',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 2000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Hoạt động mật',
            'day_number' => 1,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        // Khách chưa đăng nhập -> chuyển hướng đăng nhập
        $this->get(route('itinerary.edit', $item->id))->assertRedirect(route('login'));

        // Người dùng khác không có quyền -> 403 Forbidden
        $this->actingAs($stranger)->get(route('itinerary.edit', $item->id))->assertStatus(403);
    }

    /**
     * Chủ sở hữu cập nhật hoạt động thành công (Chức năng 7).
     */
    public function test_owner_can_successfully_update_activity(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip Đà Lạt',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'budget' => 4000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Tên cũ',
            'day_number' => 1,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'note' => 'Ghi chú cũ',
        ]);

        $response = $this->actingAs($owner)->patch(route('itinerary.update', $item->id), [
            'activity_name' => 'Tên mới sau chỉnh sửa',
            'day_number' => 1,
            'start_time' => '09:00',
            'end_time' => '11:30',
            'note' => 'Ghi chú mới',
        ]);

        $response->assertRedirect(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 1]));
        $this->assertDatabaseHas('itinerary_items', [
            'id' => $item->id,
            'custom_title' => 'Tên mới sau chỉnh sửa',
            'start_time' => '09:00:00',
            'end_time' => '11:30:00',
            'note' => 'Ghi chú mới',
        ]);
    }

    /**
     * Chỉnh sửa hoạt động kiểm tra giờ kết thúc phải sau giờ bắt đầu.
     */
    public function test_updating_activity_validates_end_time_after_start_time(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip kiểm tra giờ',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'budget' => 4000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Hoạt động test giờ',
            'day_number' => 1,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        $response = $this->actingAs($owner)->patch(route('itinerary.update', $item->id), [
            'activity_name' => 'Hoạt động test giờ',
            'day_number' => 1,
            'start_time' => '10:00',
            'end_time' => '09:00', // Không hợp lệ: end_time <= start_time
        ]);

        $response->assertSessionHasErrors(['end_time']);
    }

    /**
     * Cập nhật hoạt động kiểm tra trùng giờ, loại trừ chính mục đang sửa (FR19, FR20).
     */
    public function test_updating_activity_validates_time_overlap_excluding_self(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip kiểm tra xung đột',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'budget' => 4000000,
            'status' => 'planned',
        ]);

        // Mục 1: 08:00 - 10:00
        $item1 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục 1',
            'day_number' => 1,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        // Mục 2: 14:00 - 16:00
        $item2 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục 2',
            'day_number' => 1,
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
        ]);

        // Cập nhật Mục 1 điều chỉnh khung giờ nội bộ (08:30 - 10:30) không trùng Mục 2 -> Phải Thành Công
        $responseSuccess = $this->actingAs($owner)->patch(route('itinerary.update', $item1->id), [
            'activity_name' => 'Mục 1 sửa giờ an toàn',
            'day_number' => 1,
            'start_time' => '08:30',
            'end_time' => '10:30',
        ]);
        $responseSuccess->assertSessionHasNoErrors();

        // Cập nhật Mục 1 sang khung giờ 14:30 - 17:00 (chồng lấn Mục 2) -> Báo lỗi trùng giờ
        $responseConflict = $this->actingAs($owner)->patch(route('itinerary.update', $item1->id), [
            'activity_name' => 'Mục 1 sửa giờ bị trùng Mục 2',
            'day_number' => 1,
            'start_time' => '14:30',
            'end_time' => '17:00',
        ]);
        $responseConflict->assertSessionHasErrors(['start_time']);
    }

    /**
     * Người dùng khác không thể cập nhật hoạt động của người khác.
     */
    public function test_unauthorized_user_cannot_update_activity(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip của Owner',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'budget' => 4000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục không được đụng vào',
            'day_number' => 1,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        $this->actingAs($stranger)->patch(route('itinerary.update', $item->id), [
            'activity_name' => 'Hacker sửa lén',
            'day_number' => 1,
            'start_time' => '08:00',
            'end_time' => '10:00',
        ])->assertStatus(403);
    }

    /**
     * Chủ sở hữu có thể xóa hoạt động khỏi lịch trình (Chức năng 7).
     */
    public function test_owner_can_delete_activity_from_itinerary(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip test xóa',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'budget' => 4000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục cần xóa',
            'day_number' => 1,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        $this->assertEquals(1, ItineraryItem::where('trip_id', $trip->id)->count());

        $response = $this->actingAs($owner)->delete(route('itinerary.destroy', $item->id));

        $response->assertRedirect(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 1]));
        $this->assertEquals(0, ItineraryItem::where('trip_id', $trip->id)->count());
        $this->assertDatabaseMissing('itinerary_items', ['id' => $item->id]);
    }

    /**
     * Người lạ không thể xóa hoạt động của người khác.
     */
    public function test_unauthorized_user_cannot_delete_activity(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip bảo vệ',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-12',
            'budget' => 4000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục quan trọng',
            'day_number' => 1,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        // Khách vãng lai
        $this->delete(route('itinerary.destroy', $item->id))->assertRedirect(route('login'));

        // Người dùng khác
        $this->actingAs($stranger)->delete(route('itinerary.destroy', $item->id))->assertStatus(403);

        $this->assertDatabaseHas('itinerary_items', ['id' => $item->id]);
    }

    /**
     * Chức năng 8: Chủ sở hữu có thể chuyển hoạt động sang ngày khác trong phạm vi chuyến đi.
     */
    public function test_owner_can_move_activity_to_another_day_within_trip(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip 3 ngày đổi lịch',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Hoạt động ngày 1',
            'day_number' => 1,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
        ]);

        // Chuyển sang Ngày 2
        $response = $this->actingAs($owner)->patch(route('itinerary.update', $item->id), [
            'activity_name' => 'Hoạt động ngày 1 chuyển sang ngày 2',
            'day_number' => 2,
            'start_time' => '14:00',
            'end_time' => '16:00',
        ]);

        $response->assertRedirect(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 2]));
        $this->assertDatabaseHas('itinerary_items', [
            'id' => $item->id,
            'day_number' => 2,
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
        ]);
    }

    /**
     * Chức năng 8: Chuyển hoạt động sang ngày nằm ngoài khoảng thời gian chuyến đi sẽ bị từ chối.
     */
    public function test_moving_activity_to_out_of_range_day_is_rejected(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip 2 ngày',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục ngày 1',
            'day_number' => 1,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        // Chuyến đi 2 ngày, thử đổi sang Ngày 5
        $response = $this->actingAs($owner)->patch(route('itinerary.update', $item->id), [
            'activity_name' => 'Mục ngày 1',
            'day_number' => 5,
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $response->assertSessionHasErrors(['day_number']);
    }

    /**
     * Chức năng 8: Chuyển hoạt động sang ngày khác nhưng bị trùng khung giờ với hoạt động ngày đó sẽ bị chặn (FR19).
     */
    public function test_moving_activity_to_day_with_time_overlap_is_rejected(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip kiểm tra trùng khi đổi ngày',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        // Mục ở Ngày 1
        $item1 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục ngày 1',
            'day_number' => 1,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
        ]);

        // Mục sẵn có ở Ngày 2 (10:00 - 12:00)
        ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục sẵn có ngày 2',
            'day_number' => 2,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
        ]);

        // Chuyển Mục 1 từ Ngày 1 sang Ngày 2 với giờ 10:30 - 11:30 (trùng lấn) -> Bị từ chối
        $response = $this->actingAs($owner)->patch(route('itinerary.update', $item1->id), [
            'activity_name' => 'Mục ngày 1',
            'day_number' => 2,
            'start_time' => '10:30',
            'end_time' => '11:30',
        ]);

        $response->assertSessionHasErrors(['start_time']);
        $this->assertEquals(1, $item1->fresh()->day_number); // Giữ nguyên ngày 1
    }

    /**
     * Chức năng 8: Cập nhật giờ hoạt động tự động chuẩn hóa lại thứ tự (sort_order) liên tục từ sớm đến muộn.
     */
    public function test_changing_activity_time_automatically_resequences_sort_order(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip sắp xếp thứ tự',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        // Hoạt động 1: 10:00 - 12:00
        $itemA = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Hoạt động buổi trưa',
            'day_number' => 1,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'sort_order' => 1,
        ]);

        // Hoạt động 2: 15:00 - 17:00
        $itemB = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Hoạt động buổi chiều',
            'day_number' => 1,
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($owner)->patch(route('itinerary.update', $itemB->id), [
            'activity_name' => 'Hoạt động sáng sớm',
            'day_number' => 1,
            'start_time' => '07:00',
            'end_time' => '08:30',
        ]);
        $response->assertSessionHasNoErrors();

        // Kiểm tra thứ tự tự động đảo lại: itemB thành sort_order 1, itemA thành sort_order 2
        $this->assertEquals(1, $itemB->fresh()->sort_order);
        $this->assertEquals(2, $itemA->fresh()->sort_order);
    }

    /**
     * Chức năng 8: Chủ sở hữu có thể sắp xếp lại thứ tự các hoạt động qua API reorder (FR21, Bảng 22).
     */
    public function test_owner_can_reorder_activities_of_a_day(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip reorder',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item1 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục 1',
            'day_number' => 1,
            'sort_order' => 1,
        ]);

        $item2 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục 2',
            'day_number' => 1,
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($owner)->patchJson(route('itinerary.reorder', $trip->id), [
            'day_number' => 1,
            'item_ids' => [$item2->id, $item1->id], // Đảo thứ tự
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(1, $item2->fresh()->sort_order);
        $this->assertEquals(2, $item1->fresh()->sort_order);
    }

    /**
     * Chức năng 8: API reorder chặn request chứa ID trùng, thiếu hoặc ID thuộc ngày khác (FR21).
     */
    public function test_reordering_activities_validates_exact_ids_and_no_duplicates(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip reorder validation',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item1 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục 1',
            'day_number' => 1,
            'sort_order' => 1,
        ]);

        $item2 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Mục 2',
            'day_number' => 1,
            'sort_order' => 2,
        ]);

        // 1. Chứa ID trùng lặp
        $this->actingAs($owner)->patchJson(route('itinerary.reorder', $trip->id), [
            'day_number' => 1,
            'item_ids' => [$item1->id, $item1->id],
        ])->assertStatus(422);

        // 2. Thiếu ID
        $this->actingAs($owner)->patchJson(route('itinerary.reorder', $trip->id), [
            'day_number' => 1,
            'item_ids' => [$item1->id],
        ])->assertStatus(422);

        // 3. Chứa ID không thuộc Trip
        $this->actingAs($owner)->patchJson(route('itinerary.reorder', $trip->id), [
            'day_number' => 1,
            'item_ids' => [$item1->id, 999999],
        ])->assertStatus(422);
    }

    /**
     * Chức năng 9: Chủ sở hữu có thể thêm ghi chú khi tạo hoạt động mới.
     */
    public function test_owner_can_add_note_when_creating_activity(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Hà Nội kiểm tra ghi chú',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($owner)->post(route('itinerary.store', $trip->id), [
            'activity_name' => 'Tham quan Hồ Gươm',
            'day_number' => 1,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'note' => 'Nhớ mang theo máy ảnh và nón rộng vành để chụp ảnh kỷ niệm.',
        ]);

        $response->assertRedirect(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 1]));

        $this->assertDatabaseHas('itinerary_items', [
            'trip_id' => $trip->id,
            'custom_title' => 'Tham quan Hồ Gươm',
            'note' => 'Nhớ mang theo máy ảnh và nón rộng vành để chụp ảnh kỷ niệm.',
        ]);
    }

    /**
     * Chức năng 9: Chủ sở hữu có thể cập nhật ghi chú hoạt động qua tuyến chuyên biệt (PATCH /itinerary/{itinerary}/note).
     */
    public function test_owner_can_update_activity_note_via_dedicated_endpoint(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Hạ Long',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Đi du thuyền',
            'day_number' => 1,
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'note' => 'Ghi chú ban đầu',
            'sort_order' => 1,
        ]);

        // Cập nhật bằng request JSON (AJAX)
        $response = $this->actingAs($owner)->patchJson(route('itinerary.note.update', $item->id), [
            'note' => 'Lưu ý mang kem chống nắng và chuẩn bị vé tàu trước 15 phút.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'note' => 'Lưu ý mang kem chống nắng và chuẩn bị vé tàu trước 15 phút.',
        ]);

        $this->assertDatabaseHas('itinerary_items', [
            'id' => $item->id,
            'note' => 'Lưu ý mang kem chống nắng và chuẩn bị vé tàu trước 15 phút.',
        ]);
    }

    /**
     * Chức năng 9: Chủ sở hữu có thể xóa/để trống ghi chú của hoạt động.
     */
    public function test_owner_can_clear_activity_note(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi Đà Nẵng',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Tắm biển Mỹ Khê',
            'day_number' => 1,
            'start_time' => '16:00:00',
            'end_time' => '18:00:00',
            'note' => 'Ghi chú cần xóa',
            'sort_order' => 1,
        ]);

        // Xóa ghi chú bằng cách gửi rỗng
        $response = $this->actingAs($owner)->patch(route('itinerary.note.update', $item->id), [
            'note' => '',
        ]);

        $response->assertRedirect(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 1]));

        $this->assertDatabaseHas('itinerary_items', [
            'id' => $item->id,
            'note' => null,
        ]);
    }

    /**
     * Chức năng 9: Nhập ghi chú vượt quá 1000 ký tự sẽ bị từ chối (Bảng 26).
     */
    public function test_note_exceeding_1000_characters_is_rejected(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi kiểm tra độ dài ghi chú',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Ăn tối phố cổ',
            'day_number' => 1,
            'start_time' => '19:00:00',
            'end_time' => '20:30:00',
            'sort_order' => 1,
        ]);

        $longNote = str_repeat('Nội dung ghi chú rất dài ', 55); // > 1000 ký tự

        $response = $this->actingAs($owner)->patchJson(route('itinerary.note.update', $item->id), [
            'note' => $longNote,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['note']);
    }

    /**
     * Chức năng 9: Người không phải chủ sở hữu không thể cập nhật ghi chú hoạt động (Bảo mật IDOR).
     */
    public function test_non_owner_cannot_update_activity_note(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip của Owner',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Hoạt động bảo mật',
            'day_number' => 1,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'note' => 'Ghi chú gốc của chủ sở hữu',
            'sort_order' => 1,
        ]);

        // Người lạ cố tình sửa ghi chú
        $response = $this->actingAs($stranger)->patch(route('itinerary.note.update', $item->id), [
            'note' => 'Hacker cố tình thay đổi ghi chú',
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('itinerary_items', [
            'id' => $item->id,
            'note' => 'Ghi chú gốc của chủ sở hữu',
        ]);
    }

    /**
     * Chức năng 9: Ghi chú của hoạt động được hiển thị trực quan trên giao diện lịch trình.
     */
    public function test_activity_note_is_displayed_on_itinerary_page(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi hiển thị ghi chú',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Trải nghiệm cáp treo',
            'day_number' => 1,
            'start_time' => '08:30:00',
            'end_time' => '10:30:00',
            'note' => 'Mang theo áo khoác mỏng vì trên đỉnh núi se lạnh',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($owner)->get(route('itinerary.index', ['trip' => $trip->id, 'day_number' => 1]));

        $response->assertStatus(200);
        $response->assertSee('Mang theo áo khoác mỏng vì trên đỉnh núi se lạnh');
        $response->assertSee('Ghi chú:');
    }

    /**
     * Chức năng 10: Phương thức validateItemWithinTripDates chấp nhận các ngày hợp lệ trong phạm vi chuyến đi.
     */
    public function test_validate_item_within_trip_dates_method_accepts_valid_days(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi 4 ngày',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-04', // 4 ngày
            'budget' => 6000000,
            'status' => 'planned',
        ]);

        $service = app(\App\Services\Interfaces\ItineraryServiceInterface::class);

        // Ngày 1, Ngày 2, Ngày 3, Ngày 4 đều hợp lệ
        $this->assertTrue($service->validateItemWithinTripDates($trip, 1));
        $this->assertTrue($service->validateItemWithinTripDates($trip, 2));
        $this->assertTrue($service->validateItemWithinTripDates($trip, 3));
        $this->assertTrue($service->validateItemWithinTripDates($trip, 4));
    }

    /**
     * Chức năng 10: Phương thức validateItemWithinTripDates ném ValidationException khi ngày nằm ngoài phạm vi chuyến đi.
     */
    public function test_validate_item_within_trip_dates_method_throws_exception_for_out_of_range_day(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi 3 ngày',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03', // 3 ngày
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $service = app(\App\Services\Interfaces\ItineraryServiceInterface::class);

        // Ngày 0 (< 1) bị từ chối
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->validateItemWithinTripDates($trip, 0);
    }

    /**
     * Chức năng 10: Phương thức validateItemWithinTripDates ném ValidationException khi ngày lớn hơn tổng số ngày Trip.
     */
    public function test_validate_item_within_trip_dates_throws_exception_when_day_exceeds_total_days(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi 2 ngày',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02', // 2 ngày
            'budget' => 3000000,
            'status' => 'planned',
        ]);

        $service = app(\App\Services\Interfaces\ItineraryServiceInterface::class);

        // Ngày 3 (> 2) bị từ chối
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->validateItemWithinTripDates($trip, 3);
    }

    /**
     * Chức năng 10: Chuyến đi chưa có ngày bắt đầu / kết thúc mặc định là 1 ngày, chỉ chấp nhận Ngày 1.
     */
    public function test_trip_without_start_or_end_date_only_allows_day_1(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Chuyến đi chưa chốt ngày',
            'start_date' => null,
            'end_date' => null,
            'budget' => 2000000,
            'status' => 'draft',
        ]);

        $service = app(\App\Services\Interfaces\ItineraryServiceInterface::class);

        // Ngày 1 hợp lệ
        $this->assertTrue($service->validateItemWithinTripDates($trip, 1));

        // Thêm hoạt động vào Ngày 2 sẽ bị lỗi
        $response = $this->actingAs($owner)->post(route('itinerary.store', $trip->id), [
            'activity_name' => 'Dạo phố',
            'day_number' => 2,
        ]);

        $response->assertSessionHasErrors(['day_number']);
    }

    /**
     * Chức năng 10: Phương thức hasTimeOverlap kiểm tra chính xác xung đột thời gian giữa các hoạt động.
     */
    public function test_has_time_overlap_detects_conflicts_correctly(): void
    {
        $owner = User::factory()->create();
        $trip = Trip::create([
            'user_id' => $owner->id,
            'name' => 'Trip kiểm tra xung đột',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'budget' => 5000000,
            'status' => 'planned',
        ]);

        $item1 = ItineraryItem::create([
            'trip_id' => $trip->id,
            'custom_title' => 'Hoạt động sáng',
            'day_number' => 1,
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'sort_order' => 1,
        ]);

        $service = app(\App\Services\Interfaces\ItineraryServiceInterface::class);

        // Khung giờ 09:00 - 11:00 bị trùng với 08:00 - 10:00
        $this->assertTrue($service->hasTimeOverlap($trip, 1, '09:00', '11:00'));

        // Khung giờ 10:00 - 12:00 không bị trùng
        $this->assertFalse($service->hasTimeOverlap($trip, 1, '10:00', '12:00'));

        // Loại trừ chính item1 thì 08:00 - 10:00 không bị trùng
        $this->assertFalse($service->hasTimeOverlap($trip, 1, '08:00', '10:00', $item1->id));
    }
}
