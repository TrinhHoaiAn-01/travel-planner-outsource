<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\City;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->otherUser = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $city = City::create(['name' => 'Đà Lạt', 'slug' => 'da-lat']);
        $category = Category::create(['name' => 'Khách sạn', 'slug' => 'khach-san']);

        $destination = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Khách Sạn Mộng Mơ',
            'slug' => 'khach-san-mong-mo',
            'is_lodging' => true,
        ]);

        $roomType = RoomType::create([
            'destination_id' => $destination->id,
            'name' => 'Phòng Deluxe Hướng Hồ',
            'capacity' => 2,
            'price_per_night' => 1000000,
            'quantity' => 5,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $roomType->id,
            'room_count' => 1,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'total_price' => 2000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-9999',
        ]);
    }

    /**
     * Khách chưa đăng nhập bị chuyển hướng về login khi truy cập trang thanh toán.
     */
    public function test_guest_cannot_access_payment_page(): void
    {
        $response = $this->get(route('payments.create', $this->booking->id));
        $response->assertRedirect(route('login'));
    }

    /**
     * Chủ sở hữu đơn đặt phòng truy cập thành công trang thanh toán (HTTP 200).
     */
    public function test_owner_can_view_payment_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('payments.create', $this->booking->id));

        $response->assertStatus(200);
        $response->assertSee('#BK-2026-9999');
        $response->assertSee('2.000.000');
    }

    /**
     * Người dùng khác cố tình truy cập trang thanh toán của người khác bị trả về 403 Forbidden (BR-11).
     */
    public function test_other_user_cannot_access_payment_page_returns_403(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('payments.create', $this->booking->id));

        $response->assertStatus(403);
    }

    /**
     * Chủ sở hữu thực hiện thanh toán thành công với số tiền chính xác (BR-21).
     */
    public function test_owner_can_process_payment_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('payments.store', $this->booking->id), [
                'payment_method' => 'vnpay',
                'amount' => 2000000,
            ]);

        // Cập nhật trạng thái Booking sang confirmed
        $this->booking->refresh();
        $this->assertEquals('confirmed', $this->booking->status);

        // Tạo bản ghi Payment thành công
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'amount' => 2000000,
            'payment_method' => 'vnpay',
            'status' => 'completed',
        ]);

        $payment = Payment::where('booking_id', $this->booking->id)->first();
        $response->assertRedirect(route('payments.show', $payment->id));
    }

    /**
     * Hệ thống từ chối thanh toán khi số tiền amount gửi lên không khớp với Database (BR-21).
     */
    public function test_payment_rejects_mismatched_amount(): void
    {
        $response = $this->actingAs($this->user)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('payments.store', $this->booking->id), [
                'payment_method' => 'vnpay',
                'amount' => 1000, // Sai lệch cố ý để kiểm thử chống gian lận
            ]);

        $response->assertSessionHasErrors('amount');
        $this->assertEquals(0, Payment::where('booking_id', $this->booking->id)->count());
    }

    /**
     * Chặn thanh toán cho đơn đặt phòng đã bị hủy.
     */
    public function test_cannot_pay_for_cancelled_booking(): void
    {
        $this->booking->update(['status' => 'cancelled']);

        $response = $this->actingAs($this->user)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('payments.store', $this->booking->id), [
                'payment_method' => 'vnpay',
                'amount' => 2000000,
            ]);

        // Gate authorize chặn hoặc trả về 403
        $response->assertStatus(403);
    }

    /**
     * Chủ sở hữu xem biên lai thanh toán thành công (HTTP 200).
     */
    public function test_owner_can_view_payment_receipt(): void
    {
        $payment = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 2000000,
            'payment_method' => 'vnpay',
            'transaction_id' => 'TXN-TEST-12345',
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('payments.show', $payment->id));

        $response->assertStatus(200);
        $response->assertSee('TXN-TEST-12345');
        $response->assertSee('2.000.000');
    }

    /**
     * Người dùng khác không được xem biên lai thanh toán của người khác (BR-11, BR-12).
     */
    public function test_other_user_cannot_view_payment_receipt_returns_403(): void
    {
        $payment = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 2000000,
            'payment_method' => 'vnpay',
            'transaction_id' => 'TXN-TEST-12345',
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        $response = $this->actingAs($this->otherUser)
            ->get(route('payments.show', $payment->id));

        $response->assertStatus(403);
    }

    /**
     * Kiểm thử API thăm dò trạng thái thanh toán thời gian thực (checkStatus).
     */
    public function test_user_can_check_payment_status_when_pending_and_paid(): void
    {
        // Khi đơn chưa thanh toán
        $response = $this->actingAs($this->user)
            ->get(route('payments.status', $this->booking->id));

        $response->assertStatus(200);
        $response->assertJson([
            'is_paid' => false,
            'status' => 'pending',
        ]);

        // Sau khi đã thanh toán
        Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 2000000,
            'payment_method' => 'momo',
            'transaction_id' => 'TXN-PAID-999',
            'status' => 'completed',
            'payment_date' => now(),
        ]);
        $this->booking->update(['status' => 'confirmed']);

        $response = $this->actingAs($this->user)
            ->get(route('payments.status', $this->booking->id));

        $response->assertStatus(200);
        $response->assertJson([
            'is_paid' => true,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Kiểm thử kích hoạt giả lập nhận tiền thành công từ cổng thanh toán (simulateReceive).
     */
    public function test_user_can_simulate_payment_received(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('payments.simulate-receive', $this->booking->id), [
                'payment_method' => 'bank_transfer',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_paid' => true,
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'status' => 'completed',
            'payment_method' => 'bank_transfer',
        ]);

        $this->assertEquals('confirmed', $this->booking->fresh()->status);
    }
}
