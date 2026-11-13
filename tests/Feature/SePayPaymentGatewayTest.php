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

class SePayPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->otherUser = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $city = City::create(['name' => 'Đà Lạt', 'slug' => 'da-lat']);
        $category = Category::create(['name' => 'Resort', 'slug' => 'resort']);

        $destination = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Dalat Wonder Resort',
            'slug' => 'dalat-wonder-resort',
            'is_lodging' => true,
        ]);

        $roomType = RoomType::create([
            'destination_id' => $destination->id,
            'name' => 'Bungalow Lake View',
            'capacity' => 2,
            'price_per_night' => 2500000,
            'quantity' => 5,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $roomType->id,
            'room_count' => 1,
            'check_in_date' => now()->addDays(3)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
            'total_price' => 5000000,
            'status' => 'pending',
            'booking_code' => 'BK-SEPAY-TEST',
        ]);
    }

    /**
     * Kiểm thử người dùng hợp lệ khởi tạo thanh toán SePay và xem giao diện quét mã VietQR.
     */
    public function test_user_can_initiate_sepay_payment_and_view_transfer_page(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('payments.sepay.process', $this->booking->id));

        $payment = Payment::where('booking_id', $this->booking->id)
            ->where('payment_method', 'banking')
            ->first();

        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->status);
        $response->assertRedirect(route('payments.sepay', $payment->id));

        // Kiểm tra hiển thị trang VietQR
        $viewResponse = $this->actingAs($this->user)->get(route('payments.sepay', $payment->id));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee($this->booking->booking_code);
        $viewResponse->assertSee('Thanh Toán Chuyển Khoản Ngân Hàng (VietQR)');
        $viewResponse->assertSee('5.000.000 ₫');
    }

    /**
     * Kiểm thử khách vãng lai (Guest) không thể khởi tạo thanh toán SePay.
     */
    public function test_guest_cannot_initiate_sepay_payment(): void
    {
        $response = $this->post(route('payments.sepay.process', $this->booking->id));

        $response->assertRedirect(route('login'));
    }

    /**
     * Kiểm thử người dùng khác không có quyền khởi tạo hoặc xem giao diện thanh toán (BR-11).
     */
    public function test_other_user_cannot_initiate_or_view_sepay_payment(): void
    {
        // 1. Không thể kích hoạt tạo phiên thanh toán cho đơn của người khác
        $response = $this->actingAs($this->otherUser)
            ->post(route('payments.sepay.process', $this->booking->id));

        $response->assertStatus(403);

        // 2. Không thể xem giao diện thanh toán của đơn người khác
        $payment = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 5000000,
            'payment_method' => 'banking',
            'status' => 'pending',
        ]);

        $viewResponse = $this->actingAs($this->otherUser)
            ->get(route('payments.sepay', $payment->id));

        $viewResponse->assertStatus(403);
    }

    /**
     * Kiểm thử máy chủ SePay gửi Webhook POST xác nhận thanh toán thành công (Happy Path).
     */
    public function test_sepay_webhook_success_updates_booking_and_payment(): void
    {
        $apiKey = (string) config('sepay.api_key', 'sepay_demo_api_key_travel_planner');

        $payload = [
            'id' => 998877,
            'gateway' => 'MBBank',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber' => '0901234567',
            'content' => 'TPB BK-SEPAY-TEST thanh toan tien phong',
            'transferType' => 'in',
            'transferAmount' => 5000000,
            'accumulated' => 15000000,
            'referenceCode' => 'FT261106001234',
        ];

        $response = $this->postJson(route('payments.sepay.webhook'), $payload, [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Xác minh đơn đặt phòng đã được xác nhận
        $this->assertEquals('confirmed', $this->booking->fresh()->status);

        // Xác minh bản ghi thanh toán đã được lưu hoàn tất
        $payment = Payment::where('booking_id', $this->booking->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals('banking', $payment->payment_method);
        $this->assertEquals(5000000, $payment->amount);
        $this->assertEquals('FT261106001234', $payment->transaction_id);
    }

    /**
     * Kiểm thử Webhook từ SePay bị từ chối khi API Key không chính xác (Chống giả mạo 401).
     */
    public function test_sepay_webhook_invalid_api_key_is_rejected(): void
    {
        $payload = [
            'id' => 998877,
            'content' => 'TPB BK-SEPAY-TEST',
            'transferType' => 'in',
            'transferAmount' => 5000000,
        ];

        $response = $this->postJson(route('payments.sepay.webhook'), $payload, [
            'Authorization' => 'Apikey GIA_MAO_TOKEN_123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);

        // Đơn hàng vẫn giữ nguyên trạng thái pending
        $this->assertEquals('pending', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử Webhook bỏ qua giao dịch chiều chuyển tiền đi (transferType = out).
     */
    public function test_sepay_webhook_ignores_out_transfer_type(): void
    {
        $apiKey = (string) config('sepay.api_key', 'sepay_demo_api_key_travel_planner');

        $payload = [
            'id' => 998877,
            'content' => 'TPB BK-SEPAY-TEST',
            'transferType' => 'out',
            'transferAmount' => 5000000,
        ];

        $response = $this->postJson(route('payments.sepay.webhook'), $payload, [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertEquals('pending', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử Webhook từ chối khi số tiền chuyển khoản không đủ giá trị đơn hàng (BR-21).
     */
    public function test_sepay_webhook_insufficient_amount_is_rejected(): void
    {
        $apiKey = (string) config('sepay.api_key', 'sepay_demo_api_key_travel_planner');

        $payload = [
            'id' => 998877,
            'content' => 'TPB BK-SEPAY-TEST',
            'transferType' => 'in',
            'transferAmount' => 2000000, // Cần 5.000.000đ nhưng chỉ chuyển 2.000.000đ
        ];

        $response = $this->postJson(route('payments.sepay.webhook'), $payload, [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertEquals('pending', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử tính lũy kế (Idempotency): Khi SePay gửi Webhook lặp lại thì hệ thống không báo lỗi.
     */
    public function test_sepay_webhook_idempotency_for_already_confirmed_booking(): void
    {
        $apiKey = (string) config('sepay.api_key', 'sepay_demo_api_key_travel_planner');

        $payload = [
            'id' => 998877,
            'content' => 'TPB BK-SEPAY-TEST',
            'transferType' => 'in',
            'transferAmount' => 5000000,
            'referenceCode' => 'REF-SEPAY-123',
        ];

        // Lần 1: Thành công
        $res1 = $this->postJson(route('payments.sepay.webhook'), $payload, [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);
        $res1->assertStatus(200);

        // Lần 2: Nhận lại cùng một Webhook
        $res2 = $this->postJson(route('payments.sepay.webhook'), $payload, [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);
        $res2->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals('confirmed', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử SePay gửi gói tin kiểm thử từ nút "Gửi thử" trên Dashboard trả về HTTP 200 thành công.
     */
    public function test_sepay_dashboard_ping_test_returns_success(): void
    {
        $apiKey = (string) config('sepay.api_key', 'sepay_demo_api_key_travel_planner');

        $payload = [
            'id' => 0,
            'gateway' => 'SePay',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber' => '0000000000',
            'subAccount' => null,
            'transferType' => 'in',
            'transferAmount' => 10000,
            'accumulated' => 10000,
            'code' => 'SEPAYTEST',
            'content' => 'SEPAY TEST WEBHOOK',
            'referenceCode' => 'TEST1793917972',
            'description' => 'SePay test webhook delivery',
        ];

        $response = $this->postJson(route('payments.sepay.webhook'), $payload, [
            'Authorization' => 'Apikey ' . $apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Kiểm thử truy cập Webhook bằng phương thức GET trả về thông báo trạng thái hoạt động (Health Check).
     */
    public function test_sepay_webhook_get_endpoint_returns_health_status(): void
    {
        $response = $this->get(route('payments.sepay.webhook.get'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'active',
                'gateway' => 'SePay VietQR Webhook Service',
            ]);
    }
}

