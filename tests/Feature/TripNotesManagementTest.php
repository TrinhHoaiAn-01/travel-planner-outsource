<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripNotesManagementTest extends TestCase
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
            'name' => 'Khám phá Đà Nẵng - Hội An',
            'description' => 'Mô tả ban đầu: Du lịch miền Trung thưởng thức đặc sản.',
            'start_date' => '2026-10-10',
            'end_date' => '2026-10-15',
            'budget' => 15000000,
            'status' => Trip::STATUS_PLANNED,
        ]);
    }

    /**
     * Khách chưa đăng nhập không thể cập nhật ghi chú chuyến đi (Redirect 302).
     */
    public function test_guest_cannot_update_trip_notes(): void
    {
        $response = $this->patch(route('trips.update-notes', $this->trip->id), [
            'description' => 'Ghi chú thử nghiệm từ khách',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng không phải chủ sở hữu không thể cập nhật ghi chú của chuyến đi (403 Forbidden chống IDOR).
     */
    public function test_user_cannot_update_notes_of_other_user_trip(): void
    {
        $response = $this->actingAs($this->otherUser)->patch(route('trips.update-notes', $this->trip->id), [
            'description' => 'Cố tình sửa đổi ghi chú của người khác',
        ]);

        $response->assertForbidden();

        $this->trip->refresh();
        $this->assertEquals('Mô tả ban đầu: Du lịch miền Trung thưởng thức đặc sản.', $this->trip->description);
    }

    /**
     * Chủ sở hữu cập nhật ghi chú và mô tả tổng quát thành công.
     */
    public function test_owner_can_update_trip_notes_successfully(): void
    {
        $newNotes = "🎯 Mục tiêu: Trải nghiệm ẩm thực địa phương, ngắm hoàng hôn Hội An.\n🎒 Hành lý: Kem chống nắng, sạc dự phòng, CCCD.\n⚠️ Lưu ý: Theo dõi dự báo thời tiết.";

        $response = $this->actingAs($this->owner)->patch(route('trips.update-notes', $this->trip->id), [
            'description' => $newNotes,
        ]);

        $response->assertRedirect(route('trips.show', $this->trip->id));
        $response->assertSessionHas('success');

        $this->trip->refresh();
        $this->assertEquals($newNotes, $this->trip->description);
    }

    /**
     * Chủ sở hữu có thể xóa trắng ghi chú (cho phép giá trị null / rỗng).
     */
    public function test_owner_can_clear_trip_notes(): void
    {
        $response = $this->actingAs($this->owner)->patch(route('trips.update-notes', $this->trip->id), [
            'description' => '',
        ]);

        $response->assertRedirect(route('trips.show', $this->trip->id));

        $this->trip->refresh();
        $this->assertNull($this->trip->description);
    }

    /**
     * Không thể lưu ghi chú vượt quá giới hạn 3000 ký tự (Validation Error 422).
     */
    public function test_trip_notes_cannot_exceed_3000_characters(): void
    {
        $tooLongNotes = str_repeat('A', 3001);

        $response = $this->actingAs($this->owner)->patch(route('trips.update-notes', $this->trip->id), [
            'description' => $tooLongNotes,
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    /**
     * Ghi chú và mô tả hiển thị chính xác trên giao diện trang chi tiết chuyến đi.
     */
    public function test_trip_notes_are_displayed_properly_on_show_page(): void
    {
        $specialNotes = 'Lưu ý đặc biệt: Họp đoàn tại Sảnh T1 Sân bay Đà Nẵng lúc 8:00 sáng!';
        $this->trip->update(['description' => $specialNotes]);

        $response = $this->actingAs($this->owner)->get(route('trips.show', $this->trip->id));

        $response->assertOk();
        $response->assertSee('Mô tả và Ghi chú mục tiêu chuyến đi');
        $response->assertSee($specialNotes);
        $response->assertSee(mb_strlen($specialNotes) . ' ký tự');
    }
}
