<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripStatusManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $otherUser;
    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

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
            'name' => 'Hành trình Khám phá Cố Đô Huế',
            'description' => 'Chuyến đi tham quan miền Trung.',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'budget' => 15000000,
            'status' => Trip::STATUS_PLANNED,
        ]);
    }

    /**
     * Khách chưa đăng nhập không thể cập nhật trạng thái chuyến đi (Redirect 302 về trang đăng nhập).
     */
    public function test_guest_cannot_update_trip_status(): void
    {
        $response = $this->patch(route('trips.update-status', $this->trip->id), [
            'status' => Trip::STATUS_COMPLETED,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng khác không thể cập nhật trạng thái chuyến đi của người khác (403 Forbidden - chống IDOR theo BR-01, BR-03).
     */
    public function test_user_cannot_update_status_of_other_user_trip(): void
    {
        $response = $this->actingAs($this->otherUser)->patch(route('trips.update-status', $this->trip->id), [
            'status' => Trip::STATUS_COMPLETED,
        ]);

        $response->assertForbidden();

        $this->trip->refresh();
        $this->assertEquals(Trip::STATUS_PLANNED, $this->trip->status);
    }

    /**
     * Chủ sở hữu cập nhật trạng thái chuyến đi sang bản nháp (draft) thành công.
     */
    public function test_owner_can_update_trip_status_to_draft(): void
    {
        $response = $this->actingAs($this->owner)->patch(route('trips.update-status', $this->trip->id), [
            'status' => Trip::STATUS_DRAFT,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->trip->refresh();
        $this->assertEquals(Trip::STATUS_DRAFT, $this->trip->status);
        $this->assertTrue($this->trip->isDraft());
    }

    /**
     * Chủ sở hữu cập nhật trạng thái chuyến đi sang đã lên kế hoạch (planned) thành công.
     */
    public function test_owner_can_update_trip_status_to_planned(): void
    {
        $this->trip->update(['status' => Trip::STATUS_DRAFT]);

        $response = $this->actingAs($this->owner)->patch(route('trips.update-status', $this->trip->id), [
            'status' => Trip::STATUS_PLANNED,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->trip->refresh();
        $this->assertEquals(Trip::STATUS_PLANNED, $this->trip->status);
        $this->assertTrue($this->trip->isPlanned());
    }

    /**
     * Chủ sở hữu cập nhật trạng thái chuyến đi sang đã hoàn thành (completed) thành công.
     */
    public function test_owner_can_update_trip_status_to_completed(): void
    {
        $response = $this->actingAs($this->owner)->patch(route('trips.update-status', $this->trip->id), [
            'status' => Trip::STATUS_COMPLETED,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->trip->refresh();
        $this->assertEquals(Trip::STATUS_COMPLETED, $this->trip->status);
        $this->assertTrue($this->trip->isCompleted());
    }

    /**
     * Bắt lỗi khi cập nhật trạng thái không hợp lệ (Validation Error).
     */
    public function test_invalid_status_fails_validation(): void
    {
        $response = $this->actingAs($this->owner)->patch(route('trips.update-status', $this->trip->id), [
            'status' => 'trang_thai_khong_hop_le',
        ]);

        $response->assertSessionHasErrors(['status']);

        $this->trip->refresh();
        $this->assertEquals(Trip::STATUS_PLANNED, $this->trip->status);
    }

    /**
     * Bắt lỗi khi không truyền trường trạng thái (Validation Error).
     */
    public function test_empty_status_fails_validation(): void
    {
        $response = $this->actingAs($this->owner)->patch(route('trips.update-status', $this->trip->id), []);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * Người dùng có thể tạo mới chuyến đi với trạng thái Bản nháp (draft).
     */
    public function test_owner_can_create_trip_with_draft_status(): void
    {
        $response = $this->actingAs($this->owner)->post(route('trips.store'), [
            'name' => 'Chuyến đi Đà Lạt mộng mơ',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-05',
            'budget' => 8000000,
            'status' => Trip::STATUS_DRAFT,
            'description' => 'Bản nháp dự thảo kế hoạch.',
        ]);

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trips', [
            'user_id' => $this->owner->id,
            'name' => 'Chuyến đi Đà Lạt mộng mơ',
            'status' => Trip::STATUS_DRAFT,
        ]);
    }

    /**
     * Lọc danh sách chuyến đi theo từng tab trạng thái hiển thị đúng dữ liệu.
     */
    public function test_status_filtering_on_index_page(): void
    {
        // Tạo thêm 1 chuyến đi bản nháp và 1 chuyến đi hoàn thành
        $draftTrip = Trip::create([
            'user_id' => $this->owner->id,
            'name' => 'Trip Bản Nháp Sapa',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
            'budget' => 10000000,
            'status' => Trip::STATUS_DRAFT,
        ]);

        $completedTrip = Trip::create([
            'user_id' => $this->owner->id,
            'name' => 'Trip Hoàn Thành Phú Quốc',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-05',
            'budget' => 20000000,
            'status' => Trip::STATUS_COMPLETED,
        ]);

        // Kiểm tra lọc tab bản nháp
        $draftResponse = $this->actingAs($this->owner)->get(route('trips.index', ['status' => 'draft']));
        $draftResponse->assertOk();
        $draftResponse->assertSee('Trip Bản Nháp Sapa');
        $draftResponse->assertDontSee('Hành trình Khám phá Cố Đô Huế');
        $draftResponse->assertDontSee('Trip Hoàn Thành Phú Quốc');

        // Kiểm tra lọc tab đã hoàn thành
        $completedResponse = $this->actingAs($this->owner)->get(route('trips.index', ['status' => 'completed']));
        $completedResponse->assertOk();
        $completedResponse->assertSee('Trip Hoàn Thành Phú Quốc');
        $completedResponse->assertDontSee('Trip Bản Nháp Sapa');
        $completedResponse->assertDontSee('Hành trình Khám phá Cố Đô Huế');

        // Kiểm tra lọc tab sắp tới (planned)
        $plannedResponse = $this->actingAs($this->owner)->get(route('trips.index', ['status' => 'planned']));
        $plannedResponse->assertOk();
        $plannedResponse->assertSee('Hành trình Khám phá Cố Đô Huế');
        $plannedResponse->assertDontSee('Trip Bản Nháp Sapa');
    }
}
