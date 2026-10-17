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

class BookingCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private RoomType $roomType;
    private Trip $trip;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $city = City::factory()->create();
        $category = Category::factory()->create();
        $destination = Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'is_lodging' => true,
        ]);

        $this->roomType = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'price_per_night' => 1000000,
            'quantity' => 5,
        ]);

        $this->trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'start_date' => Carbon::now()->addDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(7)->toDateString(),
        ]);
    }

    /**
     * Khách chưa đăng nhập không được tạo booking và bị chuyển hướng về login.
     */
    public function test_guest_cannot_create_booking(): void
    {
        $response = $this->post(route('bookings.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(2)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(4)->toDateString(),
            'room_count' => 1,
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('bookings', 0);
    }

    /**
     * Người dùng đăng nhập tạo đơn đặt phòng thành công có gắn Trip.
     */
    public function test_authenticated_user_can_create_booking_with_trip(): void
    {
        $checkIn = Carbon::now()->addDays(2)->toDateString();
        $checkOut = Carbon::now()->addDays(4)->toDateString(); // 2 đêm

        $response = $this->actingAs($this->user)->post(route('bookings.store'), [
            'room_type_id' => $this->roomType->id,
            'trip_id' => $this->trip->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'room_count' => 2,
        ]);

        $response->assertRedirect(route('trips.show', $this->trip->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'trip_id' => $this->trip->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 2,
            'total_price' => 4000000, // 2 đêm * 1.000.000 * 2 phòng = 4.000.000
            'status' => 'pending',
        ]);

        $booking = Booking::first();
        $this->assertNotNull($booking->booking_code);
        $this->assertStringStartsWith('#BK-', $booking->booking_code);
    }

    /**
     * Người dùng đăng nhập tạo đơn đặt phòng thành công không gắn Trip.
     */
    public function test_authenticated_user_can_create_booking_without_trip(): void
    {
        $checkIn = Carbon::now()->addDays(1)->toDateString();
        $checkOut = Carbon::now()->addDays(3)->toDateString(); // 2 đêm

        $response = $this->actingAs($this->user)->post(route('bookings.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'room_count' => 1,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'trip_id' => null,
            'room_type_id' => $this->roomType->id,
            'total_price' => 2000000,
        ]);
    }

    /**
     * Không thể đặt phòng khi ngày nhận/trả nằm ngoài thời gian của chuyến đi.
     */
    public function test_cannot_book_when_dates_are_outside_trip_dates(): void
    {
        $response = $this->actingAs($this->user)->post(route('bookings.store'), [
            'room_type_id' => $this->roomType->id,
            'trip_id' => $this->trip->id,
            'check_in_date' => Carbon::now()->addDays(1)->toDateString(), // Trước start_date của trip
            'check_out_date' => Carbon::now()->addDays(4)->toDateString(),
            'room_count' => 1,
        ]);

        $response->assertSessionHasErrors('check_in_date');
        $this->assertDatabaseCount('bookings', 0);
    }

    /**
     * Không thể đặt phòng khi vượt quá số lượng phòng còn trống.
     */
    public function test_cannot_book_when_rooms_are_sold_out(): void
    {
        // Loại phòng chỉ có 2 phòng
        $this->roomType->update(['quantity' => 2]);

        $checkIn = Carbon::now()->addDays(2)->toDateString();
        $checkOut = Carbon::now()->addDays(5)->toDateString();

        // Đã có người đặt 2 phòng
        Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'room_count' => 2,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'total_price' => 6000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-TEST',
        ]);

        // Người dùng cố gắng đặt thêm 1 phòng trong cùng khoảng thời gian
        $response = $this->actingAs($this->user)->post(route('bookings.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'room_count' => 1,
        ]);

        $response->assertSessionHasErrors('room_count');
    }

    /**
     * Ràng buộc dữ liệu không hợp lệ (ngày quá khứ, trả trước nhận, số lượng <= 0).
     */
    public function test_validation_fails_for_invalid_input(): void
    {
        $response = $this->actingAs($this->user)->post(route('bookings.store'), [
            'room_type_id' => 99999, // Không tồn tại
            'check_in_date' => Carbon::now()->subDays(2)->toDateString(), // Quá khứ
            'check_out_date' => Carbon::now()->subDays(3)->toDateString(), // Trước check-in
            'room_count' => 0, // <= 0
        ]);

        $response->assertSessionHasErrors(['room_type_id', 'check_in_date', 'check_out_date', 'room_count']);
    }
}
