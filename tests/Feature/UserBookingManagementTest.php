<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserBookingManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private RoomType $roomType;
    private Destination $destination;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->otherUser = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $category = Category::factory()->create(['name' => 'Nghỉ dưỡng']);
        $this->destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Khách sạn Biển Xanh',
            'is_lodging' => true,
        ]);

        $this->roomType = RoomType::factory()->create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Deluxe Hướng Biển',
            'price_per_night' => 1500000,
            'quantity' => 10,
            'capacity' => 2,
        ]);
    }

    /**
     * Khách chưa đăng nhập không thể truy cập danh sách đặt phòng và bị chuyển hướng về login (BR-01).
     */
    public function test_guest_cannot_access_bookings_index(): void
    {
        $response = $this->get(route('bookings.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Người dùng đăng nhập có thể xem danh sách đơn đặt phòng của chính mình (BR-11).
     */
    public function test_authenticated_user_can_view_own_bookings_list(): void
    {
        // Tạo 2 đơn cho user
        $myBooking1 = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => Carbon::now()->addDays(5)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(7)->toDateString(),
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-MY01',
        ]);

        $myBooking2 = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 2,
            'check_in_date' => Carbon::now()->addDays(10)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(12)->toDateString(),
            'total_price' => 6000000,
            'status' => 'confirmed',
            'booking_code' => '#BK-2026-MY02',
        ]);

        // Tạo 1 đơn cho người khác
        Booking::create([
            'user_id' => $this->otherUser->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => Carbon::now()->addDays(5)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(7)->toDateString(),
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-OTHER',
        ]);

        $response = $this->actingAs($this->user)->get(route('bookings.index'));

        $response->assertStatus(200);
        $response->assertSee('#BK-2026-MY01');
        $response->assertSee('#BK-2026-MY02');
        $response->assertDontSee('#BK-2026-OTHER');
    }

    /**
     * Người dùng có thể lọc danh sách đặt phòng theo trạng thái.
     */
    public function test_user_can_filter_bookings_by_status(): void
    {
        Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-PENDING',
        ]);

        Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => Carbon::now()->addDays(10)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(12)->toDateString(),
            'total_price' => 3000000,
            'status' => 'confirmed',
            'booking_code' => '#BK-2026-CONFIRMED',
        ]);

        // Lọc status=pending
        $response = $this->actingAs($this->user)->get(route('bookings.index', ['status' => 'pending']));
        $response->assertStatus(200);
        $response->assertSee('#BK-2026-PENDING');
        $response->assertDontSee('#BK-2026-CONFIRMED');

        // Lọc status=confirmed
        $responseConfirmed = $this->actingAs($this->user)->get(route('bookings.index', ['status' => 'confirmed']));
        $responseConfirmed->assertStatus(200);
        $responseConfirmed->assertSee('#BK-2026-CONFIRMED');
        $responseConfirmed->assertDontSee('#BK-2026-PENDING');
    }

    /**
     * Chủ sở hữu có thể xem chi tiết đơn đặt phòng của mình (BR-11).
     */
    public function test_owner_can_view_booking_details(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => Carbon::now()->addDays(4)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(6)->toDateString(),
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-DETAIL',
        ]);

        $response = $this->actingAs($this->user)->get(route('bookings.show', $booking->id));

        $response->assertStatus(200);
        $response->assertSee('#BK-2026-DETAIL');
        $response->assertSee('Khách sạn Biển Xanh');
        $response->assertSee('Phòng Deluxe Hướng Biển');
        $response->assertSee('3.000.000đ');
    }

    /**
     * Người dùng không thể xem chi tiết đơn đặt phòng của người khác (trả về 403 Forbidden - BR-11).
     */
    public function test_user_cannot_view_another_users_booking_details(): void
    {
        $otherBooking = Booking::create([
            'user_id' => $this->otherUser->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => Carbon::now()->addDays(4)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(6)->toDateString(),
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-SECRET',
        ]);

        $response = $this->actingAs($this->user)->get(route('bookings.show', $otherBooking->id));

        $response->assertStatus(403);
    }

    /**
     * Chủ sở hữu có thể hủy đơn đặt phòng trước ngày nhận phòng (BR-11).
     */
    public function test_owner_can_cancel_booking_before_check_in(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-CANCEL',
        ]);

        $response = $this->actingAs($this->user)->patch(route('bookings.cancel', $booking->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Người dùng không thể hủy đơn đặt phòng của người khác (trả về 403 Forbidden - BR-11).
     */
    public function test_user_cannot_cancel_another_users_booking(): void
    {
        $otherBooking = Booking::create([
            'user_id' => $this->otherUser->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'total_price' => 3000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-PROTECT',
        ]);

        $response = $this->actingAs($this->user)->patch(route('bookings.cancel', $otherBooking->id));

        $response->assertStatus(403);

        $this->assertDatabaseHas('bookings', [
            'id' => $otherBooking->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Không thể hủy đơn đặt phòng đã bị hủy từ trước (Validation error).
     */
    public function test_cannot_cancel_already_cancelled_booking(): void
    {
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 1,
            'check_in_date' => Carbon::now()->addDays(3)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'total_price' => 3000000,
            'status' => 'cancelled',
            'booking_code' => '#BK-2026-ALREADY',
        ]);

        $response = $this->actingAs($this->user)->patch(route('bookings.cancel', $booking->id));

        // BookingPolicy chặn khi status là cancelled (trả về 403)
        $response->assertStatus(403);
    }
}
