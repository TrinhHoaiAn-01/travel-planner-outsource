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
}
