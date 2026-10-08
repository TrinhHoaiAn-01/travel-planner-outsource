<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripDetailEditDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $otherUser;
    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create([
            'name' => 'Nguyễn Trần Thành',
            'email' => 'thanh.nguyen@travelplanner.test',
            'role' => 'user',
        ]);

        $this->otherUser = User::factory()->create([
            'name' => 'Người Dùng Khác',
            'email' => 'other.user@travelplanner.test',
            'role' => 'user',
        ]);

        $this->trip = Trip::create([
            'user_id' => $this->owner->id,
            'name' => 'Khám phá Đà Nẵng - Hội An',
            'description' => 'Chuyến đi tham quan danh lam thắng cảnh và thưởng thức ẩm thực miền Trung.',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'budget' => 15000000,
            'status' => Trip::STATUS_PLANNED,
        ]);
    }

    /**
     * Khách chưa đăng nhập không thể xem chi tiết chuyến đi (Redirect 302).
     */
    public function test_guest_cannot_view_trip_details(): void
    {
        $response = $this->get(route('trips.show', $this->trip->id));

        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng không phải chủ sở hữu không thể xem chi tiết chuyến đi (403 Forbidden chống IDOR).
     */
    public function test_user_cannot_view_other_user_trip_details(): void
    {
        $response = $this->actingAs($this->otherUser)->get(route('trips.show', $this->trip->id));

        $response->assertForbidden();
    }

    /**
     * Chủ sở hữu xem chi tiết chuyến đi thành công (HTTP 200).
     */
    public function test_owner_can_view_trip_details_successfully(): void
    {
        $response = $this->actingAs($this->owner)->get(route('trips.show', $this->trip->id));

        $response->assertOk();
        $response->assertSee('Khám phá Đà Nẵng - Hội An');
        $response->assertSee('Chuyến đi tham quan danh lam thắng cảnh');
        $response->assertSee('15.000.000₫');
        $response->assertSee('10/10/2026');
        $response->assertSee('15/10/2026');
    }

    /**
     * Khách chưa đăng nhập không thể mở màn hình chỉnh sửa chuyến đi (Redirect 302).
     */
    public function test_guest_cannot_view_edit_trip_screen(): void
    {
        $response = $this->get(route('trips.edit', $this->trip->id));

        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng không phải chủ sở hữu không thể mở màn hình chỉnh sửa chuyến đi (403 Forbidden chống IDOR).
     */
    public function test_user_cannot_view_other_user_edit_trip_screen(): void
    {
        $response = $this->actingAs($this->otherUser)->get(route('trips.edit', $this->trip->id));

        $response->assertForbidden();
    }

    /**
     * Chủ sở hữu mở màn hình chỉnh sửa chuyến đi thành công (HTTP 200).
     */
    public function test_owner_can_view_edit_trip_screen_successfully(): void
    {
        $response = $this->actingAs($this->owner)->get(route('trips.edit', $this->trip->id));

        $response->assertOk();
        $response->assertSee('Chỉnh Sửa Chuyến Đi');
        $response->assertSee('Khám phá Đà Nẵng - Hội An');
        $response->assertSee('15000000');
    }

    /**
     * Chủ sở hữu cập nhật thông tin chuyến đi và mô tả ghi chú thành công.
     */
    public function test_owner_can_update_trip_and_notes_successfully(): void
    {
        $updateData = [
            'name' => 'Hành trình Đà Nẵng - Hội An Mùa Thu 2026',
            'start_date' => '2026-10-12',
            'end_date' => '2026-10-18',
            'budget' => 20000000,
            'status' => 'ongoing',
            'destination_area' => 'Đà Nẵng & Phố Cổ Hội An',
            'description' => 'Mục tiêu: Chinh phục đỉnh Bà Nà Hills, tắm biển Mỹ Khê và trải nghiệm ẩm thực phố cổ.',
        ];

        $response = $this->actingAs($this->owner)->put(route('trips.update', $this->trip->id), $updateData);

        $response->assertRedirect(route('trips.show', $this->trip->id));
        $response->assertSessionHas('success');

        $this->trip->refresh();
        $this->assertEquals('Hành trình Đà Nẵng - Hội An Mùa Thu 2026', $this->trip->name);
        $this->assertEquals('2026-10-12', $this->trip->start_date->format('Y-m-d'));
        $this->assertEquals('2026-10-18', $this->trip->end_date->format('Y-m-d'));
        $this->assertEquals(20000000, $this->trip->budget);
        $this->assertEquals('ongoing', $this->trip->status);
        $this->assertStringContainsString('Chinh phục đỉnh Bà Nà Hills', $this->trip->description);
    }

    /**
     * Cập nhật thất bại khi ngày kết thúc nhỏ hơn ngày bắt đầu.
     */
    public function test_cannot_update_trip_when_end_date_before_start_date(): void
    {
        $invalidData = [
            'name' => 'Hành trình Lỗi Ngày',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-15',
            'budget' => 10000000,
        ];

        $response = $this->actingAs($this->owner)->put(route('trips.update', $this->trip->id), $invalidData);

        $response->assertSessionHasErrors(['end_date']);
    }

    /**
     * Cập nhật thất bại khi để trống tên chuyến đi.
     */
    public function test_cannot_update_trip_without_name(): void
    {
        $invalidData = [
            'name' => '',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'budget' => 10000000,
        ];

        $response = $this->actingAs($this->owner)->put(route('trips.update', $this->trip->id), $invalidData);

        $response->assertSessionHasErrors(['name']);
    }

    /**
     * Người dùng không phải chủ sở hữu không thể cập nhật chuyến đi (403 Forbidden).
     */
    public function test_user_cannot_update_other_user_trip(): void
    {
        $updateData = [
            'name' => 'Cố tình sửa trộm tên',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'budget' => 5000000,
        ];

        $response = $this->actingAs($this->otherUser)->put(route('trips.update', $this->trip->id), $updateData);

        $response->assertForbidden();
    }

    /**
     * Người dùng không phải chủ sở hữu không thể xóa chuyến đi của người khác (403 Forbidden).
     */
    public function test_user_cannot_delete_other_user_trip(): void
    {
        $response = $this->actingAs($this->otherUser)->delete(route('trips.destroy', $this->trip->id));

        $response->assertForbidden();

        $this->assertDatabaseHas('trips', [
            'id' => $this->trip->id,
        ]);
    }

    /**
     * Chủ sở hữu xóa chuyến đi thành công (Redirect và bản ghi bị xóa).
     */
    public function test_owner_can_delete_trip_successfully(): void
    {
        $response = $this->actingAs($this->owner)->delete(route('trips.destroy', $this->trip->id));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('trips', [
            'id' => $this->trip->id,
        ]);
    }
}
