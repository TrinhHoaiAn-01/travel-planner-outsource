<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm thử tính năng Thêm / Sửa / Xóa Nhận xét và Đánh giá Destination (Hình 24, 25, 26 Báo cáo đồ án).
 * Đảm bảo kiến trúc 3 lớp, Service Interface, Policy và bảo vệ toàn vẹn dữ liệu.
 */
class DestinationReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected User $adminUser;
    protected Destination $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // Tạo người dùng thường thứ nhất
        $this->user = User::factory()->create([
            'name' => 'Nguyễn Trần Thành',
            'email' => 'thanh@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);

        // Tạo người dùng thường thứ hai để test phân quyền sở hữu
        $this->otherUser = User::factory()->create([
            'name' => 'Trịnh Hoài An',
            'email' => 'an@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);

        // Tạo tài khoản Quản trị viên (Admin)
        $this->adminUser = User::factory()->create([
            'name' => 'Quản trị viên',
            'email' => 'admin@travelplanner.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Tạo thành phố và danh mục
        $city = City::factory()->create(['name' => 'Đà Lạt']);
        $category = Category::factory()->create(['name' => 'Thắng cảnh']);

        // Tạo địa điểm du lịch
        $this->destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Hồ Tuyền Lâm',
            'slug' => 'ho-tuyen-lam',
            'rating' => 0.0,
        ]);
    }

    /**
     * Khách vãng lai (Guest) chưa đăng nhập không thể gửi đánh giá và bị chuyển hướng về login.
     */
    public function test_guest_cannot_create_review_and_is_redirected_to_login(): void
    {
        $response = $this->post(route('reviews.store', $this->destination->id), [
            'rating' => 5,
            'comment' => 'Địa điểm tuyệt vời, không khí rất trong lành.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * Người dùng đã đăng nhập có thể gửi đánh giá và nhận xét hợp lệ.
     */
    public function test_user_can_create_valid_review(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->destination->id), [
            'rating' => 5,
            'comment' => 'Khung cảnh thiên nhiên hữu tình, chèo thuyền kayak rất thú vị!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra nhận xét đã được lưu vào database với trạng thái pending
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Khung cảnh thiên nhiên hữu tình, chèo thuyền kayak rất thú vị!',
            'status' => Review::STATUS_PENDING,
        ]);
    }

    /**
     * Không thể gửi đánh giá khi thiếu số sao (rating bắt buộc).
     */
    public function test_cannot_create_review_without_rating(): void
    {
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->destination->id), [
            'comment' => 'Nhận xét không có điểm đánh giá.',
        ]);

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * Không thể gửi đánh giá với số sao ngoài phạm vi từ 1 đến 5.
     */
    public function test_cannot_create_review_with_invalid_rating_range(): void
    {
        // Thử rating = 0
        $responseZero = $this->actingAs($this->user)->post(route('reviews.store', $this->destination->id), [
            'rating' => 0,
            'comment' => 'Rating bằng 0 không hợp lệ.',
        ]);
        $responseZero->assertSessionHasErrors('rating');

        // Thử rating = 6
        $responseSix = $this->actingAs($this->user)->post(route('reviews.store', $this->destination->id), [
            'rating' => 6,
            'comment' => 'Rating bằng 6 vượt quá 5 sao.',
        ]);
        $responseSix->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * Không thể gửi đánh giá khi nội dung nhận xét vượt quá 500 ký tự.
     */
    public function test_cannot_create_review_exceeding_character_limit(): void
    {
        $longComment = str_repeat('Nội dung rất dài ', 40); // > 500 ký tự

        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->destination->id), [
            'rating' => 4,
            'comment' => $longComment,
        ]);

        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * Người dùng không thể tạo 2 bài đánh giá cho cùng một địa điểm (Tránh trùng lặp / Spam).
     */
    public function test_user_cannot_create_duplicate_review_for_same_destination(): void
    {
        // Tạo sẵn 1 review của user
        Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 4,
            'comment' => 'Đánh giá ban đầu.',
            'status' => Review::STATUS_APPROVED,
        ]);

        // Cố gắng gửi tiếp review thứ 2
        $response = $this->actingAs($this->user)->post(route('reviews.store', $this->destination->id), [
            'rating' => 5,
            'comment' => 'Đánh giá thứ 2 cố tình gửi thêm.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Số lượng review vẫn là 1
        $this->assertDatabaseCount('reviews', 1);
    }

    /**
     * Người dùng có thể chỉnh sửa đánh giá của chính mình.
     */
    public function test_user_can_update_own_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 3,
            'comment' => 'Cảm nhận lúc đầu bình thường.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->user)->put(route('reviews.update', $review->id), [
            'rating' => 5,
            'comment' => 'Sau khi quay lại trải nghiệm thấy dịch vụ cải thiện vượt bậc!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'Sau khi quay lại trải nghiệm thấy dịch vụ cải thiện vượt bậc!',
            'status' => Review::STATUS_PENDING,
        ]);
    }

    /**
     * Người dùng không được phép chỉnh sửa đánh giá của người khác (Bị chặn 403 Forbidden bởi Policy).
     */
    public function test_user_cannot_update_another_users_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 4,
            'comment' => 'Đánh giá của User 1.',
        ]);

        // User khác cố tình gọi API chỉnh sửa review này
        $response = $this->actingAs($this->otherUser)->put(route('reviews.update', $review->id), [
            'rating' => 1,
            'comment' => 'Cố tình sửa xấu đánh giá của người khác.',
        ]);

        $response->assertStatus(403);

        // Dữ liệu gốc không thay đổi
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => 'Đánh giá của User 1.',
        ]);
    }

    /**
     * Người dùng có thể xóa đánh giá của chính mình, sau khi xóa điểm rating trung bình của địa điểm tự động tính toán lại.
     */
    public function test_user_can_delete_own_review_and_destination_rating_recalculates(): void
    {
        // Tạo đánh giá thứ nhất của user 1 (rating = 5, approved)
        $review1 = Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'status' => Review::STATUS_APPROVED,
        ]);

        // Tạo đánh giá thứ hai của otherUser (rating = 3, approved)
        Review::factory()->create([
            'user_id' => $this->otherUser->id,
            'destination_id' => $this->destination->id,
            'rating' => 3,
            'status' => Review::STATUS_APPROVED,
        ]);

        // Cập nhật rating địa điểm ban đầu: (5 + 3) / 2 = 4.0
        $this->destination->update(['rating' => 4.0]);

        // User 1 xóa đánh giá của mình
        $response = $this->actingAs($this->user)->delete(route('reviews.destroy', $review1->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Review 1 đã bị xóa khỏi database
        $this->assertDatabaseMissing('reviews', ['id' => $review1->id]);

        // Rating địa điểm được tính lại chỉ còn review của otherUser: 3.0 / 1 = 3.0
        $this->destination->refresh();
        $this->assertEquals(3.0, (float) $this->destination->rating);
    }

    /**
     * Người dùng thường không được phép xóa đánh giá của người khác (Bị chặn 403 Forbidden).
     */
    public function test_user_cannot_delete_another_users_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($this->otherUser)->delete(route('reviews.destroy', $review->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    /**
     * Quản trị viên (Admin) có quyền xóa bất kỳ đánh giá nào để kiểm duyệt nội dung.
     */
    public function test_admin_can_delete_any_review(): void
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'destination_id' => $this->destination->id,
            'rating' => 1,
            'comment' => 'Nội dung phản cảm vi phạm tiêu chuẩn cộng đồng.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('reviews.destroy', $review->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /**
     * Trang chi tiết Destination hiển thị chính xác khối Nhận xét & Đánh giá (Hình 24, 25, 26).
     */
    public function test_destination_details_page_renders_reviews_section(): void
    {
        // Tạo 1 review đã duyệt
        Review::factory()->create([
            'user_id' => $this->otherUser->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Cảnh đẹp tuyệt trần, rất đáng để ghé thăm!',
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->user)->get(route('destinations.show', $this->destination->slug));

        $response->assertStatus(200);
        $response->assertSee('Đánh giá từ khách du lịch');
        $response->assertSee('Viết đánh giá của bạn');
        $response->assertSee('Cảnh đẹp tuyệt trần, rất đáng để ghé thăm!');
    }
}
