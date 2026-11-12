<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MoMoPaymentGatewayTest extends TestCase
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
            'price_per_night' => 3000000,
            'quantity' => 10,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $roomType->id,
            'room_count' => 1,
            'check_in_date' => now()->addDays(5)->toDateString(),
            'check_out_date' => now()->addDays(7)->toDateString(),
            'total_price' => 6000000,
            'status' => 'pending',
            'booking_code' => 'BK-MOMO-TEST',
        ]);
    }

    /**
     * Kiểm thử khởi tạo thanh toán MoMo Live thành công chuyển hướng sang payUrl.
     */
    public function test_user_can_initiate_momo_live_payment_redirect(): void
    {
        Http::fake([
            'https://test-payment.momo.vn/*' => Http::response([
                'partnerCode' => 'MOMOBKUN20180529',
                'orderId' => 'MOMO_BK-MOMO-TEST_12345',
                'requestId' => 'req-12345',
                'amount' => 6000000,
                'responseTime' => 1690000000,
                'message' => 'Success',
                'resultCode' => 0,
                'payUrl' => 'https://test-payment.momo.vn/v2/gateway/pay?token=sample-test-token',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('payments.momo.process', $this->booking->id));

        $response->assertRedirect('https://test-payment.momo.vn/v2/gateway/pay?token=sample-test-token');
    }

    /**
     * Kiểm thử khi cổng MoMo ngoài bị lỗi kết nối hoặc từ chối, chuyển hướng về trang chọn thanh toán kèm cảnh báo.
     */
    public function test_momo_initiation_failure_redirects_with_warning(): void
    {
        Http::fake([
            'https://test-payment.momo.vn/*' => Http::response([
                'resultCode' => 99,
                'message' => 'Hệ thống đang bảo trì',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('payments.momo.process', $this->booking->id));

        $response->assertRedirect(route('payments.create', $this->booking->id));
        $response->assertSessionHas('warning');
    }

    /**
     * Kiểm thử người dùng khác không được phép thanh toán đơn của người khác (BR-11).
     */
    public function test_other_user_cannot_initiate_momo_payment(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->post(route('payments.momo.process', $this->booking->id));

        $response->assertStatus(403);
    }

    /**
     * Kiểm thử xử lý callback thành công từ MoMo với chữ ký số hợp lệ.
     */
    public function test_momo_callback_success_updates_booking_and_payment(): void
    {
        $secretKey = config('momo.secret_key', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2Ac');
        $accessKey = config('momo.access_key', 'klm05TvNBzhg7h7j');
        $partnerCode = config('momo.partner_code', 'MOMOBKUN20180529');

        $extraData = base64_encode(json_encode(['booking_id' => $this->booking->id]));
        $orderId = 'MOMO_' . $this->booking->booking_code . '_123456';
        $requestId = 'req-test-999';
        $amount = 6000000;
        $orderInfo = 'Thanh toan don dat phong #' . $this->booking->booking_code;
        $orderType = 'momo_wallet';
        $transId = '240987654321';
        $resultCode = 0;
        $message = 'Giao dịch thành công.';
        $payType = 'qr';
        $responseTime = 1690000000;

        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&message={$message}&orderId={$orderId}&orderInfo={$orderInfo}&orderType={$orderType}&partnerCode={$partnerCode}&payType={$payType}&requestId={$requestId}&responseTime={$responseTime}&resultCode={$resultCode}&transId={$transId}";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $params = [
            'partnerCode' => $partnerCode,
            'orderId' => $orderId,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderInfo' => $orderInfo,
            'orderType' => $orderType,
            'transId' => $transId,
            'resultCode' => $resultCode,
            'message' => $message,
            'payType' => $payType,
            'responseTime' => $responseTime,
            'extraData' => $extraData,
            'signature' => $signature,
        ];

        $response = $this->get(route('payments.momo.callback', $params));

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'momo',
            'transaction_id' => $transId,
            'status' => 'completed',
        ]);

        $this->assertEquals('confirmed', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử chữ ký số MoMo không hợp lệ sẽ bị từ chối (BR-21 Chống giả mạo dữ liệu).
     */
    public function test_momo_callback_with_invalid_signature_is_rejected(): void
    {
        $params = [
            'partnerCode' => 'MOMOBKUN20180529',
            'orderId' => 'MOMO_BK-MOMO-TEST_123456',
            'requestId' => 'req-hack',
            'amount' => 1000,
            'resultCode' => 0,
            'signature' => 'invalid-fake-signature',
        ];

        $response = $this->get(route('payments.momo.callback', $params));

        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('error');
        $this->assertEquals('pending', $this->booking->fresh()->status);
    }
}
