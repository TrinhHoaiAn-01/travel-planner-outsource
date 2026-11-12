<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingAndPaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $normalUser;
    private Booking $booking;
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->normalUser = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $city = City::create(['name' => 'Nha Trang', 'slug' => 'nha-trang']);
        $category = Category::create(['name' => 'Resort', 'slug' => 'resort']);

        $destination = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Vinpearl Nha Trang',
            'slug' => 'vinpearl-nha-trang',
            'is_lodging' => true,
        ]);

        $roomType = RoomType::create([
            'destination_id' => $destination->id,
            'name' => 'Ocean View Suite',
            'capacity' => 2,
            'price_per_night' => 3000000,
            'quantity' => 10,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->normalUser->id,
            'room_type_id' => $roomType->id,
            'room_count' => 1,
            'check_in_date' => now()->addDays(5)->toDateString(),
            'check_out_date' => now()->addDays(7)->toDateString(),
            'total_price' => 6000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-8888',
        ]);

        $this->payment = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 6000000,
            'payment_method' => 'vnpay',
            'transaction_id' => 'TXN-ADMIN-TEST',
            'status' => 'completed',
            'payment_date' => now(),
        ]);
    }

    /**
     * Khách chưa đăng nhập bị chuyển hướng khi truy cập trang quản trị booking / payment.
     */
    public function test_guest_cannot_access_admin_bookings_and_payments(): void
    {
        $this->get(route('admin.bookings.index'))->assertRedirect(route('login'));
        $this->get(route('admin.payments.index'))->assertRedirect(route('login'));
    }

    /**
     * Người dùng thường bị chặn 403 Forbidden khi cố truy cập admin bookings / payments.
     */
    public function test_normal_user_cannot_access_admin_bookings_and_payments_returns_403(): void
    {
        $this->actingAs($this->normalUser)
            ->get(route('admin.bookings.index'))
            ->assertStatus(403);

        $this->actingAs($this->normalUser)
            ->get(route('admin.payments.index'))
            ->assertStatus(403);
    }

    /**
     * Admin xem danh sách booking và lọc theo trạng thái thành công (HTTP 200).
     */
    public function test_admin_can_view_bookings_list_and_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('#BK-2026-8888');
        $response->assertSee('Vinpearl Nha Trang');
    }

    /**
     * Admin xem chi tiết đơn đặt phòng thành công (HTTP 200).
     */
    public function test_admin_can_view_booking_details(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.show', $this->booking->id));

        $response->assertStatus(200);
        $response->assertSee('#BK-2026-8888');
        $response->assertSee('Ocean View Suite');
        $response->assertSee('TXN-ADMIN-TEST');
    }

    /**
     * Admin cập nhật trạng thái đơn đặt phòng thành công.
     */
    public function test_admin_can_update_booking_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->patch(route('admin.bookings.update-status', $this->booking->id), [
                'status' => 'completed',
            ]);

        $this->booking->refresh();
        $this->assertEquals('completed', $this->booking->status);
        $response->assertSessionHas('success');
    }

    /**
     * Admin xem danh sách giao dịch thanh toán thành công (HTTP 200).
     */
    public function test_admin_can_view_payments_list_and_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payments.index', ['status' => 'completed']));

        $response->assertStatus(200);
        $response->assertSee('TXN-ADMIN-TEST');
        $response->assertSee('6.000.000');
    }

    /**
     * Admin xem chi tiết một giao dịch thanh toán thành công (HTTP 200).
     */
    public function test_admin_can_view_payment_details(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payments.show', $this->payment->id));

        $response->assertStatus(200);
        $response->assertSee('TXN-ADMIN-TEST');
        $response->assertSee('#BK-2026-8888');
    }

    /**
     * Admin cập nhật trạng thái giao dịch thanh toán thành công.
     */
    public function test_admin_can_update_payment_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->patch(route('admin.payments.update-status', $this->payment->id), [
                'status' => 'refunded',
            ]);

        $this->payment->refresh();
        $this->assertEquals('refunded', $this->payment->status);
        $response->assertSessionHas('success');
    }
}
