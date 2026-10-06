<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripCreateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Khách vãng lai (Guest) chưa đăng nhập không thể truy cập màn hình tạo chuyến đi và bị chuyển hướng về login.
     */
    public function test_guest_is_redirected_to_login_when_accessing_create_trip(): void
    {
        $response = $this->get(route('trips.create'));
        $response->assertRedirect(route('login'));

        $postResponse = $this->post(route('trips.store'), [
            'name' => 'Chuyến đi thử nghiệm',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
        ]);
        $postResponse->assertRedirect(route('login'));
    }

    /**
     * Người dùng đã đăng nhập có thể hiển thị màn hình tạo chuyến đi mới.
     */
    public function test_authenticated_user_can_view_create_trip_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('trips.create'));

        $response->assertStatus(200);
        $response->assertSeeText('Lên Kế Hoạch Chuyến Đi Mới', false);
        $response->assertSee('Tên chuyến đi');
        $response->assertSee('Ngày bắt đầu');
        $response->assertSee('Ngày kết thúc');
        $response->assertSee('Tổng ngân sách dự kiến (VNĐ)');
        $response->assertSee('Địa bàn trọng tâm');
        $response->assertSee('Ảnh bìa chuyến đi (Tùy chọn)');
        $response->assertSeeText('Lưu & Xem Lịch trình', false);
    }

    /**
     * Người dùng có thể tạo chuyến đi mới thành công với dữ liệu hợp lệ.
     */
    public function test_user_can_create_new_trip_successfully(): void
    {
        $user = User::factory()->create();

        $postData = [
            'name' => 'Hành trình Khám phá Đà Nẵng - Hội An',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'budget' => 15000000,
            'destination_area' => 'Đà Nẵng & Hội An',
            'cover_image' => 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&1',
            'description' => 'Chuyến đi 6 ngày 5 đêm khám phá các điểm nổi tiếng tại Đà Nẵng và Hội An.',
        ];

        $response = $this->actingAs($user)->post(route('trips.store'), $postData);

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trips', [
            'user_id' => $user->id,
            'name' => 'Hành trình Khám phá Đà Nẵng - Hội An',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'budget' => 15000000,
            'status' => Trip::STATUS_PLANNED,
        ]);
    }

    /**
     * Không thể tạo chuyến đi khi để trống tên chuyến đi.
     */
    public function test_cannot_create_trip_without_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('trips.store'), [
            'name' => '',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseCount('trips', 0);
    }

    /**
     * Không thể tạo chuyến đi khi ngày kết thúc trước ngày bắt đầu.
     */
    public function test_cannot_create_trip_when_end_date_before_start_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('trips.store'), [
            'name' => 'Chuyến đi lỗi ngày',
            'start_date' => '2026-10-15',
            'end_date' => '2026-10-10', // Ngày kết thúc trước ngày bắt đầu
        ]);

        $response->assertSessionHasErrors(['end_date']);
        $this->assertDatabaseCount('trips', 0);
    }

    /**
     * Không thể tạo chuyến đi khi ngân sách có giá trị âm.
     */
    public function test_cannot_create_trip_with_negative_budget(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('trips.store'), [
            'name' => 'Chuyến đi ngân sách âm',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'budget' => -500000,
        ]);

        $response->assertSessionHasErrors(['budget']);
        $this->assertDatabaseCount('trips', 0);
    }

    /**
     * Người dùng có thể nhân bản chuyến đi của mình (Nghiệp vụ Nguyễn Trần Thành).
     */
    public function test_user_can_clone_own_trip(): void
    {
        $user = User::factory()->create();

        $originalTrip = Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Chuyến đi gốc',
            'budget' => 5000000,
            'status' => Trip::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($user)->post(route('trips.clone', $originalTrip->id));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trips', [
            'user_id' => $user->id,
            'name' => 'Bản sao - Chuyến đi gốc',
            'status' => Trip::STATUS_DRAFT,
        ]);
    }

    /**
     * Người dùng không thể nhân bản chuyến đi của người khác (Bảo vệ BR-03 và IDOR).
     */
    public function test_user_cannot_clone_other_user_trip(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $tripA = Trip::factory()->create([
            'user_id' => $userA->id,
            'name' => 'Chuyến đi của User A',
        ]);

        $response = $this->actingAs($userB)->post(route('trips.clone', $tripA->id));

        $response->assertStatus(403);
    }

    /**
     * Người dùng có thể mở lại chuyến đi đã hoàn thành (Nghiệp vụ Nguyễn Trần Thành).
     */
    public function test_user_can_reopen_own_completed_trip(): void
    {
        $user = User::factory()->create();

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'name' => 'Chuyến đi đã xong',
            'status' => Trip::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($user)->post(route('trips.reopen', $trip->id));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('success');

        $trip->refresh();
        $this->assertSame(Trip::STATUS_PLANNED, $trip->status);
    }

    /**
     * Người dùng không thể mở lại chuyến đi của người khác (Bảo mật BR-03 và IDOR).
     */
    public function test_user_cannot_reopen_other_user_trip(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $tripA = Trip::factory()->create([
            'user_id' => $userA->id,
            'name' => 'Chuyến đi của User A',
            'status' => Trip::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($userB)->post(route('trips.reopen', $tripA->id));

        $response->assertStatus(403);
    }
}
