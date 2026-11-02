<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use App\Services\Interfaces\ReviewServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử tính năng: Quản lý trạng thái và quy tắc Review của User (Nguyễn Trần Thành).
 * Đảm bảo 100% tuân thủ quy tắc kiểm duyệt, phân quyền, lọc trạng thái và tính toán điểm số.
 */
class UserReviewStatusAndRulesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected User $adminUser;
    protected Destination $destination;
    protected Destination $destination2;
    protected ReviewServiceInterface $reviewService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->reviewService = app(ReviewServiceInterface::class);

        // Tạo dữ liệu nền tảng
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $category = Category::factory()->create(['name' => 'Bãi biển']);

        $this->destination = Destination::factory()->create([
            'name' => 'Bà Nà Hills',
            'city_id' => $city->id,
            'category_id' => $category->id,
            'rating' => 0.0,
        ]);

        $this->destination2 = Destination::factory()->create([
            'name' => 'Bán đảo Sơn Trà',
            'city_id' => $city->id,
            'category_id' => $category->id,
            'rating' => 0.0,
        ]);

        // Tạo người dùng thường chính chủ
        $this->user = User::factory()->create([
            'name' => 'Nguyễn Trần Thành',
            'email' => 'thanh@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);

        // Tạo người dùng khác
        $this->otherUser = User::factory()->create([
            'name' => 'Trịnh Hoài An',
            'email' => 'an@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);

        // Tạo tài khoản Quản trị viên
        $this->adminUser = User::factory()->create([
            'name' => 'Admin Hệ Thống',
            'email' => 'admin@travelplanner.test',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Quy tắc 1: Khách vãng lai chưa đăng nhập không thể vào trang Quản lý đánh giá của tôi.
     */
    public function test_guest_cannot_access_my_reviews_page_and_is_redirected_to_login(): void
    {
        $response = $this->get(route('reviews.my-reviews'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Quy tắc 2: Người dùng đăng nhập có thể truy cập trang và chỉ thấy đánh giá của chính mình.
     */
    public function test_authenticated_user_can_view_my_reviews_page_with_only_their_own_reviews(): void
    {
        // Tạo review của user chính
        $myReview = Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Cảnh sắc Bà Nà Hills tuyệt đẹp và mát mẻ!',
            'status' => Review::STATUS_APPROVED,
        ]);

        // Tạo review của user khác
        $otherReview = Review::create([
            'user_id' => $this->otherUser->id,
            'destination_id' => $this->destination2->id,
            'rating' => 4,
            'comment' => 'Sơn Trà rất hoang sơ và yên bình.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->user)->get(route('reviews.my-reviews'));

        $response->assertStatus(200);
        $response->assertSee('Đánh Giá Của Tôi');
        $response->assertSee('Cảnh sắc Bà Nà Hills tuyệt đẹp và mát mẻ!');
        // Không nhìn thấy bài đánh giá của user khác
        $response->assertDontSee('Sơn Trà rất hoang sơ và yên bình.');
    }

    /**
     * Quy tắc 3: Lọc danh sách theo trạng thái 'approved' (Đã duyệt).
     */
    public function test_user_can_filter_reviews_by_status_approved(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Bài review đã được duyệt công khai',
            'status' => Review::STATUS_APPROVED,
        ]);

        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination2->id,
            'rating' => 3,
            'comment' => 'Bài review đang chờ duyệt',
            'status' => Review::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->user)->get(route('reviews.my-reviews', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('Bài review đã được duyệt công khai');
        $response->assertDontSee('Bài review đang chờ duyệt');
    }

    /**
     * Quy tắc 4: Lọc danh sách theo trạng thái 'pending' (Chờ duyệt).
     */
    public function test_user_can_filter_reviews_by_status_pending(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Bài review đã được duyệt công khai',
            'status' => Review::STATUS_APPROVED,
        ]);

        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination2->id,
            'rating' => 3,
            'comment' => 'Bài review đang chờ duyệt',
            'status' => Review::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->user)->get(route('reviews.my-reviews', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('Bài review đang chờ duyệt');
        $response->assertDontSee('Bài review đã được duyệt công khai');
    }

    /**
     * Quy tắc 5: Lọc danh sách theo trạng thái 'hidden' (Đã ẩn).
     */
    public function test_user_can_filter_reviews_by_status_hidden(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 1,
            'comment' => 'Bài review bị quản trị viên ẩn do vi phạm',
            'status' => Review::STATUS_HIDDEN,
        ]);

        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination2->id,
            'rating' => 5,
            'comment' => 'Bài review bình thường đã duyệt',
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->user)->get(route('reviews.my-reviews', ['status' => 'hidden']));

        $response->assertStatus(200);
        $response->assertSee('Bài review bị quản trị viên ẩn do vi phạm');
        $response->assertDontSee('Bài review bình thường đã duyệt');
    }

    /**
     * Quy tắc 6: Lọc danh sách theo số sao đánh giá.
     */
    public function test_user_can_filter_reviews_by_star_rating(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Dịch vụ 5 sao tuyệt vời nhất',
            'status' => Review::STATUS_APPROVED,
        ]);

        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination2->id,
            'rating' => 2,
            'comment' => 'Trải nghiệm 2 sao cần cải thiện',
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->user)->get(route('reviews.my-reviews', ['rating' => 5]));

        $response->assertStatus(200);
        $response->assertSee('Dịch vụ 5 sao tuyệt vời nhất');
        $response->assertDontSee('Trải nghiệm 2 sao cần cải thiện');
    }

    /**
     * Quy tắc 7: Tìm kiếm review theo tên địa điểm hoặc từ khóa nhận xét.
     */
    public function test_user_can_search_reviews_by_destination_name_or_keyword(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id, // Bà Nà Hills
            'rating' => 5,
            'comment' => 'Cáp treo hiện đại tuyệt đẹp',
            'status' => Review::STATUS_APPROVED,
        ]);

        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination2->id, // Bán đảo Sơn Trà
            'rating' => 4,
            'comment' => 'Khỉ vàng rất dễ thương',
            'status' => Review::STATUS_APPROVED,
        ]);

        // Tìm theo tên địa điểm "Bà Nà"
        $responseName = $this->actingAs($this->user)->get(route('reviews.my-reviews', ['search' => 'Bà Nà']));
        $responseName->assertStatus(200);
        $responseName->assertSee('Cáp treo hiện đại tuyệt đẹp');
        $responseName->assertDontSee('Khỉ vàng rất dễ thương');

        // Tìm theo nội dung nhận xét "Khỉ vàng"
        $responseComment = $this->actingAs($this->user)->get(route('reviews.my-reviews', ['search' => 'Khỉ vàng']));
        $responseComment->assertStatus(200);
        $responseComment->assertSee('Khỉ vàng rất dễ thương');
        $responseComment->assertDontSee('Cáp treo hiện đại tuyệt đẹp');
    }

    /**
     * Quy tắc 8: Review pending và hidden KHÔNG được hiển thị công khai trên trang điểm đến cho người khác xem.
     */
    public function test_pending_and_hidden_reviews_are_not_shown_to_public_on_destination_page(): void
    {
        // User tạo 1 review pending và 1 review hidden của người khác
        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 4,
            'comment' => 'Review đang chờ duyệt bí mật',
            'status' => Review::STATUS_PENDING,
        ]);

        // Khách vãng lai xem trang chi tiết điểm đến
        $responseGuest = $this->get(route('destinations.show', $this->destination->slug));
        $responseGuest->assertStatus(200);
        $responseGuest->assertDontSee('Review đang chờ duyệt bí mật');

        // Người dùng khác xem trang chi tiết điểm đến
        $responseOther = $this->actingAs($this->otherUser)->get(route('destinations.show', $this->destination->slug));
        $responseOther->assertStatus(200);
        $responseOther->assertDontSee('Review đang chờ duyệt bí mật');
    }

    /**
     * Quy tắc 9: Tác giả bài viết có thể xem review của chính mình trên trang điểm đến dù đang pending/hidden.
     */
    public function test_author_can_see_their_own_pending_or_hidden_review_with_status_badge_on_destination_page(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 4,
            'comment' => 'Review của tôi đang chờ kiểm duyệt',
            'status' => Review::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->user)->get(route('destinations.show', $this->destination->slug));

        $response->assertStatus(200);
        $response->assertSee('Review của tôi đang chờ kiểm duyệt');
        $response->assertSee('Đang chờ duyệt');
    }

    /**
     * Quy tắc 10: Chỉ review 'approved' mới được tính vào điểm rating trung bình của điểm đến.
     */
    public function test_only_approved_reviews_are_calculated_into_destination_rating(): void
    {
        // 1 review approved: 4 sao
        $approvedReview = Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 4,
            'comment' => 'Review tốt đã duyệt',
            'status' => Review::STATUS_APPROVED,
        ]);

        // 1 review pending: 1 sao (không được tính)
        Review::create([
            'user_id' => $this->otherUser->id,
            'destination_id' => $this->destination->id,
            'rating' => 1,
            'comment' => 'Review pending chê bai',
            'status' => Review::STATUS_PENDING,
        ]);

        $rating = $this->reviewService->recalculateDestinationRating($this->destination);

        $this->assertEquals(4.0, $rating);
        $this->destination->refresh();
        $this->assertEquals(4.0, (float) $this->destination->rating);

        // Khi review pending được duyệt -> rating trung bình thay đổi thành (4 + 1) / 2 = 2.5
        $pendingReview = Review::where('user_id', $this->otherUser->id)->first();
        $this->reviewService->approveReview($pendingReview);

        $this->destination->refresh();
        $this->assertEquals(2.5, (float) $this->destination->rating);
    }

    /**
     * Quy tắc 11: Khi user thường cập nhật review, trạng thái chuyển về 'pending' để admin duyệt lại nội dung.
     */
    public function test_user_updating_review_resets_status_to_pending_for_re_moderation(): void
    {
        $review = Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Nội dung ban đầu đã duyệt',
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->user)->put(route('reviews.update', $review->id), [
            'rating' => 4,
            'comment' => 'Nội dung cập nhật mới cần kiểm tra',
            'redirect_to' => 'my-reviews',
        ]);

        $response->assertRedirect(route('reviews.my-reviews'));
        $review->refresh();
        $this->assertEquals(4, $review->rating);
        $this->assertEquals('Nội dung cập nhật mới cần kiểm tra', $review->comment);
        // Trạng thái tự động chuyển thành pending
        $this->assertEquals(Review::STATUS_PENDING, $review->status);
    }

    /**
     * Quy tắc 12: Phân quyền bảo mật - User không thể sửa hoặc xóa review của người dùng khác (403 Forbidden).
     */
    public function test_user_cannot_update_or_delete_another_users_review(): void
    {
        $otherReview = Review::create([
            'user_id' => $this->otherUser->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Review của người khác',
            'status' => Review::STATUS_APPROVED,
        ]);

        // Cố gắng cập nhật review của người khác
        $responseUpdate = $this->actingAs($this->user)->put(route('reviews.update', $otherReview->id), [
            'rating' => 1,
            'comment' => 'Phá hoại review người khác',
        ]);
        $responseUpdate->assertStatus(403);

        // Cố gắng xóa review của người khác
        $responseDelete = $this->actingAs($this->user)->delete(route('reviews.destroy', $otherReview->id));
        $responseDelete->assertStatus(403);
    }

    /**
     * Quy tắc 13: User có thể xóa review của chính mình và hệ thống tự động cập nhật lại rating điểm đến.
     */
    public function test_user_can_delete_their_own_review_from_my_reviews_and_rating_recalculates(): void
    {
        $review = Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Tôi muốn xóa review này',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->reviewService->recalculateDestinationRating($this->destination);
        $this->assertEquals(5.0, (float) $this->destination->fresh()->rating);

        $response = $this->actingAs($this->user)->delete(route('reviews.destroy', $review->id), [
            'redirect_to' => 'my-reviews',
        ]);

        $response->assertRedirect(route('reviews.my-reviews'));
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
        $this->assertEquals(0.0, (float) $this->destination->fresh()->rating);
    }

    /**
     * Quy tắc 14: Thống kê tổng quan số lượng bài viết của User theo từng trạng thái trả về chính xác.
     */
    public function test_user_review_stats_are_accurate(): void
    {
        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Review 1 approved',
            'status' => Review::STATUS_APPROVED,
        ]);

        Review::create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination2->id,
            'rating' => 3,
            'comment' => 'Review 2 pending',
            'status' => Review::STATUS_PENDING,
        ]);

        $stats = $this->reviewService->getUserReviewStats($this->user);

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['approved']);
        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(0, $stats['hidden']);
        $this->assertEquals(4.0, $stats['average_rating']);
    }
}
