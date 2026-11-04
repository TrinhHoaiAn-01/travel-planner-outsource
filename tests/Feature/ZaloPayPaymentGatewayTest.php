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

class ZaloPayPaymentGatewayTest extends TestCase
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

        $city = City::create(['name' => 'Nha Trang', 'slug' => 'nha-trang']);
        $category = Category::create(['name' => 'Khách sạn', 'slug' => 'khach-san']);

        $destination = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Vinpearl Resort Nha Trang',
            'slug' => 'vinpearl-resort-nha-trang',
            'is_lodging' => true,
        ]);

        $roomType = RoomType::create([
            'destination_id' => $destination->id,
            'name' => 'Deluxe King',
            'capacity' => 2,
            'price_per_night' => 2000000,
            'quantity' => 10,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $roomType->id,
            'room_count' => 1,
            'check_in_date' => now()->addDays(2)->toDateString(),
            'check_out_date' => now()->addDays(4)->toDateString(),
            'total_price' => 4000000,
            'status' => 'pending',
            'booking_code' => 'BK-ZALO-TEST',
        ]);
    }

    /**
     * Kiểm thử khởi tạo thanh toán ZaloPay Sandbox chuyển hướng thành công sang order_url.
     */
    public function test_user_can_initiate_zalopay_payment_redirect(): void
    {
        Http::fake([
            'https://sb-openapi.zalopay.vn/*' => Http::response([
                'return_code' => 1,
                'return_message' => 'Giao dịch thành công',
                'order_url' => 'https://qcgateway.zalopay.vn/pay?order=test-order-token-123',
                'zp_trans_token' => 'test-zp-trans-token',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('payments.zalopay.process', $this->booking->id));

        $response->assertRedirect('https://qcgateway.zalopay.vn/pay?order=test-order-token-123');
    }

    /**
     * Kiểm thử khách vãng lai (Guest) không thể thanh toán ZaloPay nếu chưa đăng nhập (HTTP 302 về Login).
     */
    public function test_guest_cannot_initiate_zalopay_payment(): void
    {
        $response = $this->post(route('payments.zalopay.process', $this->booking->id));

        $response->assertRedirect(route('login'));
    }

    /**
     * Kiểm thử người dùng khác không thể thanh toán đơn không thuộc sở hữu (HTTP 403 Forbidden - BR-11).
     */
    public function test_other_user_cannot_initiate_zalopay_payment(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->post(route('payments.zalopay.process', $this->booking->id));

        $response->assertStatus(403);
    }

    /**
     * Kiểm thử khi máy chủ ZaloPay từ chối hoặc trả về mã lỗi, chuyển hướng về trang chọn thanh toán kèm cảnh báo.
     */
    public function test_zalopay_initiation_failure_redirects_with_warning(): void
    {
        Http::fake([
            'https://sb-openapi.zalopay.vn/*' => Http::response([
                'return_code' => 2,
                'return_message' => 'Đơn hàng đã tồn tại hoặc mã đơn trùng lặp',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('payments.zalopay.process', $this->booking->id));

        $response->assertRedirect(route('payments.create', $this->booking->id));
        $response->assertSessionHas('warning');
    }

    /**
     * Kiểm thử xử lý phản hồi thành công (Return URL) từ ZaloPay với chữ ký Checksum hợp lệ.
     */
    public function test_zalopay_callback_success_updates_booking_and_payment(): void
    {
        $key2 = config('zalopay.key2', 'kLtgPl8YESDmyABkQgeZByOUJsbcpNI2');
        $appid = 2553;
        $apptransid = date('ymd') . '_' . $this->booking->booking_code . '_9999';
        $pmcid = 36;
        $bankcode = 'CC';
        $amount = 4000000;
        $discountamount = 0;
        $status = 1;

        $dataString = "{$appid}|{$apptransid}|{$pmcid}|{$bankcode}|{$amount}|{$discountamount}|{$status}";
        $checksum = hash_hmac('sha256', $dataString, $key2);

        $params = [
            'appid' => $appid,
            'apptransid' => $apptransid,
            'pmcid' => $pmcid,
            'bankcode' => $bankcode,
            'amount' => $amount,
            'discountamount' => $discountamount,
            'status' => $status,
            'checksum' => $checksum,
        ];

        $response = $this->get(route('payments.zalopay.callback', $params));

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'zalopay',
            'status' => 'completed',
        ]);

        $this->assertEquals('confirmed', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử chữ ký số Checksum ZaloPay không hợp lệ bị từ chối (BR-21 Chống giả mạo dữ liệu).
     */
    public function test_zalopay_callback_with_invalid_checksum_is_rejected(): void
    {
        $params = [
            'appid' => 2553,
            'apptransid' => date('ymd') . '_' . $this->booking->booking_code . '_9999',
            'pmcid' => 36,
            'bankcode' => 'CC',
            'amount' => 1000,
            'discountamount' => 0,
            'status' => 1,
            'checksum' => 'fake_invalid_checksum',
        ];

        $response = $this->get(route('payments.zalopay.callback', $params));

        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('error');
        $this->assertEquals('pending', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử khi giao dịch thanh toán thất bại trên ZaloPay (status != 1).
     */
    public function test_zalopay_callback_failed_status_marks_payment_failed(): void
    {
        $key2 = config('zalopay.key2', 'kLtgPl8YESDmyABkQgeZByOUJsbcpNI2');
        $appid = 2553;
        $apptransid = date('ymd') . '_' . $this->booking->booking_code . '_9999';
        $pmcid = 36;
        $bankcode = 'CC';
        $amount = 4000000;
        $discountamount = 0;
        $status = 2; // Thất bại

        $dataString = "{$appid}|{$apptransid}|{$pmcid}|{$bankcode}|{$amount}|{$discountamount}|{$status}";
        $checksum = hash_hmac('sha256', $dataString, $key2);

        $params = [
            'appid' => $appid,
            'apptransid' => $apptransid,
            'pmcid' => $pmcid,
            'bankcode' => $bankcode,
            'amount' => $amount,
            'discountamount' => $discountamount,
            'status' => $status,
            'checksum' => $checksum,
        ];

        $response = $this->get(route('payments.zalopay.callback', $params));

        $response->assertRedirect(route('bookings.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'zalopay',
            'status' => 'failed',
        ]);
        $this->assertEquals('pending', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử Webhook Callback IPN từ máy chủ ZaloPay hợp lệ.
     */
    public function test_zalopay_ipn_webhook_success(): void
    {
        $key2 = config('zalopay.key2', 'kLtgPl8YESDmyABkQgeZByOUJsbcpNI2');

        $orderData = [
            'app_id' => 2553,
            'app_trans_id' => date('ymd') . '_' . $this->booking->booking_code . '_8888',
            'amount' => 4000000,
            'zp_trans_id' => '241104000001',
            'server_time' => 1690000000000,
            'channel' => 38,
            'merchant_user_id' => 'user_' . $this->user->id,
            'embed_data' => json_encode(['booking_id' => $this->booking->id]),
        ];

        $dataJson = json_encode($orderData);
        $mac = hash_hmac('sha256', $dataJson, $key2);

        $response = $this->postJson(route('payments.zalopay.callback-ipn'), [
            'data' => $dataJson,
            'mac' => $mac,
            'type' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'return_code' => 1,
                'return_message' => 'success',
            ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'zalopay',
            'status' => 'completed',
        ]);
        $this->assertEquals('confirmed', $this->booking->fresh()->status);
    }

    /**
     * Kiểm thử Webhook Callback IPN từ máy chủ ZaloPay với MAC không hợp lệ.
     */
    public function test_zalopay_ipn_webhook_invalid_mac_returns_negative_return_code(): void
    {
        $dataJson = json_encode(['app_id' => 2553, 'amount' => 1000]);

        $response = $this->postJson(route('payments.zalopay.callback-ipn'), [
            'data' => $dataJson,
            'mac' => 'invalid-fake-mac',
            'type' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'return_code' => -1,
                'return_message' => 'mac not equal',
            ]);

        $this->assertEquals('pending', $this->booking->fresh()->status);
    }
}
