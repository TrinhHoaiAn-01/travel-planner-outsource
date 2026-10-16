<?php

namespace Tests\Feature\Admin;

use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Destination $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->destination = Destination::factory()->create([
            'name' => 'Sun World Bà Nà Hills',
            'slug' => 'sun-world-ba-na-hills',
        ]);
    }

    /**
     * Khách vãng lai chưa đăng nhập bị chuyển hướng đến trang đăng nhập khi truy cập kiểm duyệt đánh giá.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.reviews.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng thông thường không có quyền admin bị từ chối truy cập 403 Forbidden.
     */
    public function test_non_admin_user_cannot_access_review_moderation(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.reviews.index'));

        $response->assertForbidden();
    }

    /**
     * Quản trị viên truy cập thành công màn hình Kiểm duyệt đánh giá khách hàng với đầy đủ thông tin giao diện.
     */
    public function test_admin_can_view_review_moderation_screen(): void
    {
        Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Cầu Vàng sáng sớm siêu đẹp và ít đông đúc.',
            'status' => Review::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.reviews.index'));

        $response->assertOk();
        $response->assertSee('Kiểm Duyệt Đánh Giá Khách Hàng');
        $response->assertSee('Kiểm duyệt đánh giá');
        $response->assertSee('Tất cả');
        $response->assertSee('Chờ duyệt (Pending)');
        $response->assertSee('Đã duyệt (Approved)');
        $response->assertSee('Đã ẩn (Hidden)');
        $response->assertSee('Sun World Bà Nà Hills');
        $response->assertSee('Cầu Vàng sáng sớm siêu đẹp và ít đông đúc.');
    }

    /**
     * Quản trị viên có thể lọc danh sách đánh giá theo các tab trạng thái (all, pending, approved, hidden).
     */
    public function test_admin_can_filter_reviews_by_status(): void
    {
        $pendingUser = User::factory()->create(['name' => 'Nguyễn Chờ Duyệt']);
        $approvedUser = User::factory()->create(['name' => 'Trần Đã Duyệt']);
        $hiddenUser = User::factory()->create(['name' => 'Lê Đã Ẩn']);

        $dest2 = Destination::factory()->create();
        $dest3 = Destination::factory()->create();

        Review::factory()->create([
            'user_id' => $pendingUser->id,
            'destination_id' => $this->destination->id,
            'status' => Review::STATUS_PENDING,
        ]);

        Review::factory()->create([
            'user_id' => $approvedUser->id,
            'destination_id' => $dest2->id,
            'status' => Review::STATUS_APPROVED,
        ]);

        Review::factory()->create([
            'user_id' => $hiddenUser->id,
            'destination_id' => $dest3->id,
            'status' => Review::STATUS_HIDDEN,
        ]);

        // 1. Tab Tất cả
        $responseAll = $this->actingAs($this->admin)->get(route('admin.reviews.index', ['status' => 'all']));
        $responseAll->assertOk();
        $responseAll->assertSee('Nguyễn Chờ Duyệt');
        $responseAll->assertSee('Trần Đã Duyệt');
        $responseAll->assertSee('Lê Đã Ẩn');

        // 2. Tab Chờ duyệt
        $responsePending = $this->actingAs($this->admin)->get(route('admin.reviews.index', ['status' => 'pending']));
        $responsePending->assertOk();
        $responsePending->assertSee('Nguyễn Chờ Duyệt');
        $responsePending->assertDontSee('Trần Đã Duyệt');
        $responsePending->assertDontSee('Lê Đã Ẩn');

        // 3. Tab Đã duyệt
        $responseApproved = $this->actingAs($this->admin)->get(route('admin.reviews.index', ['status' => 'approved']));
        $responseApproved->assertOk();
        $responseApproved->assertDontSee('Nguyễn Chờ Duyệt');
        $responseApproved->assertSee('Trần Đã Duyệt');
        $responseApproved->assertDontSee('Lê Đã Ẩn');

        // 4. Tab Đã ẩn
        $responseHidden = $this->actingAs($this->admin)->get(route('admin.reviews.index', ['status' => 'hidden']));
        $responseHidden->assertOk();
        $responseHidden->assertDontSee('Nguyễn Chờ Duyệt');
        $responseHidden->assertDontSee('Trần Đã Duyệt');
        $responseHidden->assertSee('Lê Đã Ẩn');
    }

    /**
     * Quản trị viên có thể lấy chi tiết đánh giá qua API JSON để hiển thị trên modal.
     */
    public function test_admin_can_view_review_detail_json(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Nhận xét chi tiết cho modal.',
            'status' => Review::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.reviews.show', $review));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $review->id,
                'user_name' => $this->user->name,
                'user_email' => $this->user->email,
                'destination_name' => $this->destination->name,
                'rating' => '5.0',
                'comment' => 'Nhận xét chi tiết cho modal.',
                'status' => 'pending',
            ],
        ]);
    }

    /**
     * Quản trị viên có thể phê duyệt đánh giá thành công.
     */
    public function test_admin_can_approve_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'status' => Review::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.reviews.approve', $review));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Đã phê duyệt đánh giá thành công.');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_APPROVED,
        ]);
    }

    /**
     * Quản trị viên có thể ẩn đánh giá thành công.
     */
    public function test_admin_can_hide_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.reviews.hide', $review));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Đã ẩn đánh giá thành công.');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_HIDDEN,
        ]);
    }

    /**
     * Quản trị viên có thể xóa vĩnh viễn đánh giá khỏi hệ thống.
     */
    public function test_admin_can_delete_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'status' => Review::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.reviews.destroy', $review));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Đã xóa đánh giá thành công.');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }
}
