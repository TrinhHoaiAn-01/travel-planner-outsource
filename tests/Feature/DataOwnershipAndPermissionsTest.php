<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\City;
use App\Models\Destination;
use App\Models\Expense;
use App\Models\ItineraryItem;
use App\Models\Review;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class DataOwnershipAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user1;
    private User $user2;
    private Trip $tripUser1;
    private Trip $tripUser2;
    private Destination $destination;
    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Quản Trị Viên',
            'email' => 'admin.perm@travelplanner.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->user1 = User::factory()->create([
            'name' => 'Người Dùng 1',
            'email' => 'user1.perm@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->user2 = User::factory()->create([
            'name' => 'Người Dùng 2',
            'email' => 'user2.perm@travelplanner.test',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->tripUser1 = Trip::create([
            'user_id' => $this->user1->id,
            'name' => 'Hành trình Đà Lạt của User 1',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-14',
            'budget' => 6000000,
            'status' => 'planned',
        ]);

        $this->tripUser2 = Trip::create([
            'user_id' => $this->user2->id,
            'name' => 'Hành trình Nha Trang của User 2',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
            'budget' => 8000000,
            'status' => 'planned',
        ]);

        $city = City::create(['name' => 'Đà Nẵng', 'slug' => 'da-nang']);
        $category = \App\Models\Category::create(['name' => 'Khách sạn', 'slug' => 'khach-san']);
        $this->destination = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Khách sạn Biển Đông',
            'slug' => 'khach-san-bien-dong',
            'description' => 'Mô tả khách sạn',
        ]);

        $this->roomType = RoomType::create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Deluxe',
            'price_per_night' => 1500000,
            'capacity' => 2,
            'quantity' => 10,
        ]);
    }

    /**
     * Người dùng không thể xem chi tiết chuyến đi của người khác (BR-03, chống IDOR).
     */
    public function test_user_cannot_view_another_users_trip(): void
    {
        $response = $this->actingAs($this->user1)->get(route('trips.show', $this->tripUser2->id));
        $response->assertForbidden();
    }

    /**
     * Người dùng không thể truy cập form chỉnh sửa chuyến đi của người khác.
     */
    public function test_user_cannot_edit_another_users_trip(): void
    {
        $response = $this->actingAs($this->user1)->get(route('trips.edit', $this->tripUser2->id));
        $response->assertForbidden();
    }

    /**
     * Người dùng không thể cập nhật chuyến đi của người khác.
     */
    public function test_user_cannot_update_another_users_trip(): void
    {
        $response = $this->actingAs($this->user1)->put(route('trips.update', $this->tripUser2->id), [
            'name' => 'Cố tình sửa tên chuyến đi',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
            'budget' => 9000000,
            'status' => 'planned',
        ]);

        $response->assertForbidden();
    }

    /**
     * Người dùng không thể xóa chuyến đi của người khác.
     */
    public function test_user_cannot_delete_another_users_trip(): void
    {
        $response = $this->actingAs($this->user1)->delete(route('trips.destroy', $this->tripUser2->id));
        $response->assertForbidden();

        $this->assertDatabaseHas('trips', ['id' => $this->tripUser2->id]);
    }

    /**
     * Người dùng không thể thêm chi phí vào chuyến đi của người khác (BR-04).
     */
    public function test_user_cannot_add_expense_to_another_users_trip(): void
    {
        $response = $this->actingAs($this->user1)->post(route('trips.expenses.store', $this->tripUser2->id), [
            'title' => 'Chi phí lạ',
            'amount' => 500000,
            'category' => 'food',
            'expense_date' => '2026-12-02',
        ]);

        $response->assertForbidden();
    }

    /**
     * Người dùng không thể sửa hoặc xóa chi phí thuộc chuyến đi của người khác (BR-04).
     */
    public function test_user_cannot_update_or_delete_another_users_expense(): void
    {
        $expense = Expense::create([
            'trip_id' => $this->tripUser2->id,
            'title' => 'Vé máy bay',
            'amount' => 2000000,
            'category' => 'transportation',
            'expense_date' => '2026-12-01',
        ]);

        // Thử sửa
        $responseEdit = $this->actingAs($this->user1)->patch(route('expenses.update', $expense->id), [
            'title' => 'Sửa lén vé máy bay',
            'amount' => 1000000,
            'category' => 'transportation',
            'expense_date' => '2026-12-01',
        ]);
        $responseEdit->assertForbidden();

        // Thử xóa
        $responseDelete = $this->actingAs($this->user1)->delete(route('expenses.destroy', $expense->id));
        $responseDelete->assertForbidden();

        $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
    }

    /**
     * Người dùng không thể xem chi tiết đơn đặt phòng của người khác (BR-11).
     */
    public function test_user_cannot_view_another_users_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user2->id,
            'trip_id' => $this->tripUser2->id,
            'room_type_id' => $this->roomType->id,
            'booking_code' => 'BK-TEST-USER2',
            'check_in_date' => '2026-12-01',
            'check_out_date' => '2026-12-04',
            'room_count' => 1,
            'total_price' => 1500000,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->user1)->get(route('bookings.show', $booking->id));
        $response->assertForbidden();
    }

    /**
     * Người dùng không thể hủy đơn đặt phòng của người khác (BR-11).
     */
    public function test_user_cannot_cancel_another_users_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user2->id,
            'trip_id' => $this->tripUser2->id,
            'room_type_id' => $this->roomType->id,
            'booking_code' => 'BK-TEST-CANCEL',
            'check_in_date' => '2026-12-01',
            'check_out_date' => '2026-12-04',
            'room_count' => 1,
            'total_price' => 1500000,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->user1)->patch(route('bookings.cancel', $booking->id));
        $response->assertForbidden();

        $this->assertEquals('confirmed', $booking->fresh()->status);
    }

    /**
     * ItineraryPolicy kiểm tra quyền sở hữu lịch trình và ngăn chặn truy cập trái phép (BR-04).
     */
    public function test_itinerary_policy_ownership_verification(): void
    {
        $itemUser2 = ItineraryItem::create([
            'trip_id' => $this->tripUser2->id,
            'destination_id' => $this->destination->id,
            'day_number' => 1,
            'sort_order' => 1,
            'note' => 'Ghé thăm Hồ Xuân Hương',
        ]);

        // User 1 không có quyền sửa/xóa/thêm mục lịch trình của User 2
        $this->assertFalse(Gate::forUser($this->user1)->allows('view', $itemUser2));
        $this->assertFalse(Gate::forUser($this->user1)->allows('update', $itemUser2));
        $this->assertFalse(Gate::forUser($this->user1)->allows('delete', $itemUser2));
        $this->assertFalse(Gate::forUser($this->user1)->allows('create', [ItineraryItem::class, $this->tripUser2]));

        // Chính chủ User 2 có toàn quyền thao tác trên lịch trình của mình
        $this->assertTrue(Gate::forUser($this->user2)->allows('view', $itemUser2));
        $this->assertTrue(Gate::forUser($this->user2)->allows('update', $itemUser2));
        $this->assertTrue(Gate::forUser($this->user2)->allows('delete', $itemUser2));
        $this->assertTrue(Gate::forUser($this->user2)->allows('create', [ItineraryItem::class, $this->tripUser2]));
    }

    /**
     * ReviewPolicy kiểm tra quyền sở hữu đánh giá và quyền kiểm duyệt của Quản trị viên (BR-06, BR-14).
     */
    public function test_review_policy_ownership_and_moderation(): void
    {
        $reviewUser2 = Review::create([
            'user_id' => $this->user2->id,
            'destination_id' => $this->destination->id,
            'rating' => 5,
            'comment' => 'Dịch vụ rất tuyệt vời',
            'status' => Review::STATUS_PENDING,
        ]);

        // User 1 không thể sửa hoặc xóa review của User 2
        $this->assertFalse(Gate::forUser($this->user1)->allows('update', $reviewUser2));
        $this->assertFalse(Gate::forUser($this->user1)->allows('delete', $reviewUser2));
        $this->assertFalse(Gate::forUser($this->user1)->allows('moderate', Review::class));

        // User 2 có quyền sửa và xóa review của chính mình
        $this->assertTrue(Gate::forUser($this->user2)->allows('update', $reviewUser2));
        $this->assertTrue(Gate::forUser($this->user2)->allows('delete', $reviewUser2));

        // Admin có quyền duyệt, sửa và xóa review
        $this->assertTrue(Gate::forUser($this->admin)->allows('update', $reviewUser2));
        $this->assertTrue(Gate::forUser($this->admin)->allows('delete', $reviewUser2));
        $this->assertTrue(Gate::forUser($this->admin)->allows('moderate', Review::class));
    }

    /**
     * Người dùng thông thường (member) bị từ chối truy cập (HTTP 403) vào tất cả khu vực quản trị Admin.
     */
    public function test_regular_user_forbidden_from_admin_area(): void
    {
        $adminRoutes = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.cities.index'),
            route('admin.categories.index'),
            route('admin.destinations.index'),
            route('admin.reviews.index'),
        ];

        foreach ($adminRoutes as $adminRoute) {
            $response = $this->actingAs($this->user1)->get($adminRoute);
            $response->assertForbidden();
        }
    }

    /**
     * Khách chưa đăng nhập (Guest) bị chuyển hướng về trang đăng nhập khi truy cập tuyến bảo vệ.
     */
    public function test_guest_redirected_to_login(): void
    {
        $protectedRoutes = [
            route('trips.index'),
            route('profile.show'),
            route('bookings.index'),
            route('admin.dashboard'),
        ];

        foreach ($protectedRoutes as $protectedRoute) {
            $response = $this->get($protectedRoute);
            $response->assertRedirect(route('login'));
        }
    }

    /**
     * Yêu cầu JSON của khách chưa đăng nhập trả về HTTP 401 Unauthorized.
     */
    public function test_guest_json_request_receives_401(): void
    {
        $response = $this->getJson(route('admin.dashboard'));
        $response->assertUnauthorized();
    }
}
