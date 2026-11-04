<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VnPayPaymentGatewayTest extends TestCase
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

        $city = City::create(['name' => 'Đà Nẵng', 'slug' => 'da-nang']);
        $category = Category::create(['name' => 'Resort', 'slug' => 'resort']);

        $destination = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Furama Resort Danang',
            'slug' => 'furama-resort-danang',
            'is_lodging' => true,
        ]);

        $roomType = RoomType::create([
            'destination_id' => $destination->id,
            'name' => 'Ocean Suite',
            'capacity' => 2,
            'price_per_night' => 2500000,
            'quantity' => 10,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $roomType->id,
            'room_count' => 1,
            'check_in_date' => now()->addDays(3)->toDateString(),
            'check_out_date' => now()->addDays(5)->toDateString(),
            'total_price' => 5000000,
            'status' => 'pending',
            'booking_code' => 'BK-VNPAY-TEST',
        ]);
    }

    /**
     * Tạo dữ liệu callback/IPN hợp lệ từ VNPay kèm mã băm HMAC-SHA512.
     */
    private function generateVnPayPayload(array $customParams = []): array
    {
        $hashSecret = config('vnpay.hash_secret', 'VZWQDFXHAPZUJZHIAGELBGWXFDOZVKFB');
        $tmnCode = config('vnpay.tmn_code', 'UTSQQSRL');

        $params = array_merge([
            'vnp_Amount' => (string) (round($this->booking->total_price * 100)),
            'vnp_BankCode' => 'NCB',
            'vnp_BankTranNo' => 'VNP140001',
            'vnp_CardType' => 'ATM',
            'vnp_OrderInfo' => 'Thanh toan don dat phong #' . $this->booking->booking_code,
            'vnp_PayDate' => date('YmdHis'),
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => $tmnCode,
            'vnp_TransactionNo' => '14234567',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $this->booking->booking_code . '_' . time(),
        ], $customParams);

        ksort($params);

        $i = 0;
        $hashData = '';
        foreach ($params as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode((string) $key) . '=' . urlencode((string) $value);
            } else {
                $hashData .= urlencode((string) $key) . '=' . urlencode((string) $value);
                $i = 1;
            }
        }

        $params['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $hashSecret);

        return $params;
    }

    /**
     * Kiểm thử khởi tạo thanh toán VNPay chuyển hướng chính xác sang cổng VNPay Sandbox kèm chữ ký số.
     */
    public function test_user_can_initiate_vnpay_payment_redirect(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('payments.vnpay.process', $this->booking->id));

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');

        $this->assertStringContainsString('https://sandbox.vnpayment.vn/paymentv2/vpcpay.html', $targetUrl);
        $this->assertStringContainsString('vnp_TmnCode=UTSQQSRL', $targetUrl);
        $this->assertStringContainsString('vnp_SecureHash=', $targetUrl);
        $this->assertStringContainsString('vnp_TxnRef=BK-VNPAY-TEST_', $targetUrl);
    }

    /**
     * Kiểm thử khách vãng lai (Guest) không thể thanh toán nếu chưa đăng nhập (HTTP 302 về Login).
     */
    public function test_guest_cannot_initiate_vnpay_payment(): void
    {
        $response = $this->post(route('payments.vnpay.process', $this->booking->id));

        $response->assertRedirect(route('login'));
    }

    /**
     * Kiểm thử người dùng khác không thể thanh toán đơn đặt phòng không thuộc quyền sở hữu (HTTP 403 Forbidden - BR-11).
     */
    public function test_other_user_cannot_initiate_vnpay_payment(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->post(route('payments.vnpay.process', $this->booking->id));

        $response->assertStatus(403);
    }

    /**
     * Kiểm thử xử lý phản hồi thành công (Return Callback) từ VNPay với chữ ký số hợp lệ.
     */
    public function test_vnpay_callback_success_updates_booking_and_payment(): void
    {
        $params = $this->generateVnPayPayload(['vnp_ResponseCode' => '00']);

        $response = $this->get(route('payments.vnpay.callback', $params));

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'vnpay',
            'transaction_id' => '14234567',
            'status' => 'completed',
        ]);

        $this->assertEquals('confirmed', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử chữ ký số VNPay không hợp lệ bị từ chối chuyển tiếp về danh sách đơn kèm thông báo lỗi (BR-21).
     */
    public function test_vnpay_callback_with_invalid_signature_is_rejected(): void
    {
        $params = $this->generateVnPayPayload();
        $params['vnp_SecureHash'] = 'fake_invalid_checksum_hash';

        $response = $this->get(route('payments.vnpay.callback', $params));

        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('error');
        $this->assertEquals('pending', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử khi khách hàng hủy giao dịch trên VNPay (Mã phản hồi vnp_ResponseCode = 24).
     */
    public function test_vnpay_callback_failed_response_code_marks_payment_failed(): void
    {
        $params = $this->generateVnPayPayload(['vnp_ResponseCode' => '24']);

        $response = $this->get(route('payments.vnpay.callback', $params));

        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'vnpay',
            'status' => 'failed',
        ]);
        $this->assertEquals('pending', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử Webhook IPN từ máy chủ VNPay phản hồi hợp lệ trả về mã RspCode = 00.
     */
    public function test_vnpay_ipn_webhook_success(): void
    {
        $params = $this->generateVnPayPayload(['vnp_ResponseCode' => '00']);

        $response = $this->postJson(route('payments.vnpay.ipn'), $params);

        $response->assertStatus(200)
            ->assertJson([
                'RspCode' => '00',
                'Message' => 'Confirm Success',
            ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'vnpay',
            'status' => 'completed',
        ]);
        $this->assertEquals('confirmed', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử Webhook IPN từ máy chủ VNPay với chữ ký sai trả về mã RspCode = 97.
     */
    public function test_vnpay_ipn_webhook_invalid_signature_returns_97(): void
    {
        $params = $this->generateVnPayPayload();
        $params['vnp_SecureHash'] = 'fake_signature';

        $response = $this->postJson(route('payments.vnpay.ipn'), $params);

        $response->assertStatus(200)
            ->assertJson([
                'RspCode' => '97',
                'Message' => 'Invalid Checksum',
            ]);

        $this->assertEquals('pending', $this->booking->fresh()->status);
    }
}
