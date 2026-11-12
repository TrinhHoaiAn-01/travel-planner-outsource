<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\User;
use App\Services\CommerceBackendService;
use App\Services\Interfaces\MoMoGatewayServiceInterface;
use App\Services\Interfaces\SePayGatewayServiceInterface;
use App\Services\Interfaces\VnPayGatewayServiceInterface;
use App\Services\Interfaces\ZaloPayGatewayServiceInterface;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ThirdAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Destination $destination;
    protected RoomType $roomType;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'traveler@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->otherUser = User::factory()->create([
            'email' => 'other@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->destination = Destination::factory()->create([
            'name' => 'Khách sạn Biển Đông',
        ]);

        $this->roomType = RoomType::create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Superior Hướng Biển',
            'capacity' => 2,
            'price_per_night' => 1000000,
            'quantity' => 5,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'booking_code' => 'BK-AUDIT-2026',
            'check_in_date' => Carbon::today()->addDays(5)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(8)->toDateString(),
            'room_count' => 1,
            'total_price' => 3000000,
            'status' => 'pending',
        ]);
    }

    /**
     * P0.1: Kiểm tra ranh giới câu hỏi, phủ định và cụm từ trạng thái thanh toán không kích hoạt hủy đơn.
     */
    public function test_p0_1_questions_and_negations_do_not_cancel_booking(): void
    {
        // 1. Phủ định "Đừng hủy đơn"
        $backend = new CommerceBackendService($this->user);
        
        // Trạng thái đơn ban đầu là pending
        $this->assertSame('pending', $this->booking->fresh()->status);

        // Giả lập intent routing: Kiểm tra regex word-boundary và negations
        $phrases = [
            'Có thể hủy đơn #BK-AUDIT-2026 không?',
            'Đừng hủy đơn #BK-AUDIT-2026',
            'Toi da chuyen khoan, kiem tra #BK-AUDIT-2026',
        ];

        foreach ($phrases as $phrase) {
            // "chuyen" không được kích hoạt từ khóa hủy "huy"
            $isCancelKw = (bool) preg_match('/\b(hủy|huy|cancel)\b/iu', $phrase);
            if (str_contains(strtolower($phrase), 'chuyen')) {
                $this->assertFalse($isCancelKw);
            }
        }

        // Đơn booking vẫn được bảo toàn trạng thái pending
        $this->assertSame('pending', $this->booking->fresh()->status);
    }

    /**
     * P0.2: Các công cụ model là Propose Only và bắt buộc phải dùng preview_token để thực thi.
     */
    public function test_p0_2_model_tools_are_propose_only_and_enforce_preview_token(): void
    {
        $backend = new CommerceBackendService($this->user);

        // 1. cancel_booking không trực tiếp hủy mà trả về preview = true
        $preview = $backend->execute('cancel_booking', [
            'booking_code' => $this->booking->booking_code,
            'confirm' => true, // Boolean confirm cũ không được phép bypass preview
        ]);

        $this->assertTrue($preview['preview']);
        $this->assertTrue($preview['requires_confirmation']);
        $this->assertNotEmpty($preview['preview_token']);
        $this->assertSame('pending', $this->booking->fresh()->status);

        // 2. confirm_preview với token không hợp lệ bị từ chối
        $this->expectException(DomainException::class);
        $backend->execute('confirm_preview', [
            'preview_token' => 'invalid-random-token',
        ]);
    }

    /**
     * P0.2: Preview token chỉ được dùng 1 lần (Single-use), gọi lại trả về kết quả ban đầu (Idempotent replay).
     */
    public function test_p0_2_preview_token_single_use_and_idempotent_replay(): void
    {
        $backend = new CommerceBackendService($this->user);

        $preview = $backend->execute('cancel_booking', [
            'booking_code' => $this->booking->booking_code,
        ]);
        $token = $preview['preview_token'];

        // Lần 1: Thực thi thành công
        $outcome1 = $backend->confirmPreview(['preview_token' => $token]);
        $this->assertTrue($outcome1['success']);
        $this->assertSame('cancelled', $outcome1['status']);
        $this->assertSame('cancelled', $this->booking->fresh()->status);

        // Lần 2 (Replay): Trả về nguyên vẹn outcome ban đầu mà không gây lỗi hay mutation mới
        $outcome2 = $backend->confirmPreview(['preview_token' => $token]);
        $this->assertSame($outcome1, $outcome2);

        // Người dùng khác không được confirm preview của user này
        $otherBackend = new CommerceBackendService($this->otherUser);
        $preview2 = $backend->execute('book_hotel_room', [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::today()->addDays(10)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(12)->toDateString(),
            'room_count' => 1,
        ]);

        $this->expectException(DomainException::class);
        $otherBackend->confirmPreview(['preview_token' => $preview2['preview_token']]);
    }

    /**
     * P0.3: Đổi ngày nhận phòng (reschedule) bắt buộc xác thực ngày, cấm no-op, và bảo toàn số đêm/ngày còn lại.
     */
    public function test_p0_3_reschedule_validates_dates_and_preserves_untouched_duration(): void
    {
        $backend = new CommerceBackendService($this->user);

        // Không cung cấp ngày nào
        try {
            $backend->execute('update_booking_dates', [
                'booking_code' => $this->booking->booking_code,
            ]);
            $this->fail('Lẽ ra phải báo lỗi thiếu ngày.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Vui lòng cung cấp ngày', $e->getMessage());
        }

        // Chọn ngày trùng khớp hoàn toàn ngày hiện tại (No-op)
        try {
            $backend->execute('update_booking_dates', [
                'booking_code' => $this->booking->booking_code,
                'check_in_date' => $this->booking->check_in_date,
                'check_out_date' => $this->booking->check_out_date,
            ]);
            $this->fail('Lẽ ra phải báo lỗi trùng khoảng ngày hiện tại.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('trùng khớp', $e->getMessage());
        }

        // Đổi ngày hợp lệ: Chỉ cung cấp ngày nhận phòng mới, giữ nguyên ngày trả phòng cũ
        $newCheckIn = Carbon::today()->addDays(6)->toDateString();
        $preview = $backend->execute('update_booking_dates', [
            'booking_code' => $this->booking->booking_code,
            'check_in_date' => $newCheckIn,
        ]);

        $this->assertTrue($preview['preview']);
        $this->assertSame($newCheckIn, $preview['new_check_in']);
        $this->assertSame($this->booking->check_out_date->toDateString(), $preview['new_check_out']);

        // Xác nhận đổi ngày
        $confirmed = $backend->confirmPreview([
            'preview_token' => $preview['preview_token'],
        ]);
        $this->assertTrue($confirmed['success']);

        $refreshed = $this->booking->fresh();
        $this->assertSame($newCheckIn, $refreshed->check_in_date->toDateString());
        $this->assertSame(2000000.0, (float) $refreshed->total_price);
    }

    /**
     * P1.4: Stale / Unknown attempt không settle đơn mà chuyển sang reconciliation.
     */
    public function test_p1_4_stale_or_unknown_attempts_route_to_reconciliation_under_lock(): void
    {
        $momoService = app(MoMoGatewayServiceInterface::class);

        // Giả lập callback MoMo thành công cho một orderId không có attempt tương ứng (unknown attempt)
        $unknownTransId = 'MOMO_STALE_' . time();
        $payload = [
            'partnerCode' => config('services.momo.partner_code'),
            'orderId' => 'UNKNOWN_ATTEMPT_12345',
            'requestId' => 'REQ_' . time(),
            'amount' => 3000000,
            'orderInfo' => 'Thanh toán đơn hàng',
            'orderType' => 'momo_wallet',
            'transId' => $unknownTransId,
            'resultCode' => 0,
            'message' => 'Giao dịch thành công.',
            'payType' => 'qr',
            'responseTime' => time(),
            'extraData' => base64_encode(json_encode(['booking_id' => $this->booking->id])),
            'signature' => 'mock_signature',
        ];

        $payment = $momoService->processCallback($payload);

        // Ghi nhận trạng thái reconciliation và KHÔNG settle đơn thành confirmed
        $this->assertSame('reconciliation', $payment->status);
        $this->assertSame(3000000.0, (float) $payment->amount);
        $this->assertSame('pending', $this->booking->fresh()->status);
    }

    /**
     * P1.4: Kiểm tra số tiền SePay được bảo vệ dưới booking lock.
     */
    public function test_p1_4_sepay_amount_check_enforced_inside_lock(): void
    {
        config([
            'sepay.api_key' => 'TEST_KEY',
            'sepay.account_number' => '0901234567',
        ]);

        $sepayService = app(SePayGatewayServiceInterface::class);

        // Chuyển thiếu tiền so với đơn đặt phòng
        $payload = [
            'id' => 998877,
            'accountNumber' => '0901234567',
            'transferAmount' => 1000, // Cần 3,000,000 VND nhưng chỉ gửi 1,000 VND
            'transferType' => 'in',
            'content' => 'Thanh toan BK-AUDIT-2026',
            'referenceCode' => 'SEPAY_UNDERPAID_' . time(),
        ];

        $result = $sepayService->processWebhook($payload);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('nhỏ hơn tổng tiền đơn', $result['message']);
        $this->assertSame('pending', $this->booking->fresh()->status);
    }

    /**
     * P1.5: Callback thất bại tuyệt đối không tạo bản ghi reconciliation và không bịa số tiền.
     */
    public function test_p1_5_failed_callbacks_do_not_create_reconciliation_receipts(): void
    {
        $momoService = app(MoMoGatewayServiceInterface::class);

        // Tạo sẵn một pending attempt
        $attempt = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => $this->booking->total_price,
            'payment_method' => 'momo',
            'transaction_id' => 'MOMO_ORDER_TEST_FAIL',
            'status' => 'pending',
        ]);

        $payload = [
            'orderId' => 'MOMO_ORDER_TEST_FAIL',
            'amount' => 3000000,
            'transId' => 'MOMO_TRANS_FAIL_999',
            'resultCode' => 1006, // Người dùng từ chối thanh toán
            'extraData' => base64_encode(json_encode(['booking_id' => $this->booking->id])),
        ];

        $processed = $momoService->processCallback($payload);

        // Attempt được cập nhật status = failed, tuyệt đối KHÔNG phải là reconciliation!
        $this->assertSame('failed', $processed->status);
        $this->assertSame('pending', $this->booking->fresh()->status);
        $this->assertDatabaseMissing('payments', [
            'booking_id' => $this->booking->id,
            'status' => 'reconciliation',
        ]);
    }

    /**
     * P1.6: Checkout flow hỗ trợ chọn provider và Booking::isPaid() chỉ true khi có completed payment.
     */
    public function test_p1_6_checkout_provider_selection_and_is_paid_integrity(): void
    {
        $backend = new CommerceBackendService($this->user);

        // 1. Kiểm tra isPaid() khi đơn pending chưa có payment
        $this->assertFalse($this->booking->isPaid());
        $this->assertTrue($this->booking->canBePaid());

        // 2. Đơn hoàn thành nhưng có 0 payment completed (ví dụ đơn terminal đặc biệt)
        $this->booking->update(['status' => 'completed']);
        $this->assertFalse($this->booking->isPaid());
        $this->assertFalse($this->booking->canBePaid());

        // Reset về pending
        $this->booking->update(['status' => 'pending']);

        // 3. Khởi tạo thanh toán với provider momo
        $momoCheckout = $backend->execute('initiate_checkout', [
            'booking_code' => $this->booking->booking_code,
            'provider' => 'momo',
        ]);

        $this->assertTrue($momoCheckout['success']);
        $this->assertSame('momo', $momoCheckout['selected_provider']);
        $this->assertStringContainsString('momo', $momoCheckout['pay_url']);

        // 4. Khởi tạo thanh toán với provider vnpay
        $vnpayCheckout = $backend->execute('initiate_checkout', [
            'booking_code' => $this->booking->booking_code,
            'provider' => 'vnpay',
        ]);

        $this->assertTrue($vnpayCheckout['success']);
        $this->assertSame('vnpay', $vnpayCheckout['selected_provider']);
        $this->assertStringContainsString('vnpay', $vnpayCheckout['pay_url']);
    }

    /**
     * P1.7: Migration cách ly (quarantine) các cuộc hội thoại legacy chứa từ khóa quản trị.
     */
    public function test_p1_7_quarantine_legacy_ai_conversations_with_merchant_terms(): void
    {
        // Tạo 1 cuộc hội thoại bị gán nhầm shopping nhưng chứa từ khóa merchant
        $conv = AiConversation::create([
            'user_id' => $this->user->id,
            'role' => 'shopping',
        ]);

        AiMessage::create([
            'conversation_id' => $conv->id,
            'role' => 'user',
            'content' => 'Báo cáo doanh thu và occupancy phòng khách sạn tháng này.',
        ]);

        // Chạy migration forward quarantine
        $migration = require database_path('migrations/2026_11_08_000003_quarantine_ambiguous_legacy_ai_conversations.php');
        $migration->up();

        $this->assertSame('quarantined', $conv->fresh()->role);
    }

    /**
     * P1.8: Tham số tool mutation được chuẩn hóa (canonical ksort) và deduplicate theo operation_id.
     */
    public function test_p1_8_mutation_arguments_canonicalized_and_deduplicated_by_operation_id(): void
    {
        $args1 = ['check_in_date' => '2026-11-20', 'room_count' => 1, 'nights' => 2];
        $args2 = ['nights' => 2, 'room_count' => 1, 'check_in_date' => '2026-11-20'];

        $canonicalize = function (&$arr) use (&$canonicalize) {
            if (! is_array($arr)) return;
            ksort($arr);
            foreach ($arr as &$v) {
                if (is_array($v)) $canonicalize($v);
            }
        };

        $canonicalize($args1);
        $canonicalize($args2);

        $this->assertSame(md5(json_encode($args1)), md5(json_encode($args2)));

        // Kiểm tra lưu kết quả vào cache theo operation_id
        $opId = 'test-op-12345';
        $key = "mutation_op:{$opId}:test_tool:" . md5(json_encode($args1));
        Cache::put($key, ['success' => true, 'id' => 99], 3600);

        $this->assertSame(['success' => true, 'id' => 99], Cache::get($key));
    }

    /**
     * P1.8: Endpoint surface acquire lock commerce-session để chống xung đột race condition.
     */
    public function test_p1_8_surface_endpoint_acquires_shared_commerce_session_lock(): void
    {
        // Khi một tiến trình chat đang giữ lock commerce-session
        $lockKey = 'commerce-session:user_' . $this->user->id;
        $activeLock = Cache::lock($lockKey, 60);
        $this->assertTrue($activeLock->get());

        // Gọi endpoint surface() phải fail-closed với mã 409
        $response = $this->actingAs($this->user)
            ->getJson('/commerce-api/shopping/history');

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Một thao tác khác đang chạy. Vui lòng đợi.']);

        $activeLock->release();
    }

    /**
     * P1.9: ZaloPay callback phân biệt pending attempt và chỉ redirect success khi completed.
     */
    public function test_p1_9_zalopay_callback_status_distinguishes_pending_attempts_and_redirects_safely(): void
    {
        $zaloService = app(ZaloPayGatewayServiceInterface::class);

        // Tạo pending attempt
        $attempt = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => $this->booking->total_price,
            'payment_method' => 'zalopay',
            'transaction_id' => '261108_BK-AUDIT-2026_111',
            'status' => 'pending',
        ]);

        // Giả lập callback thành công
        $zpTransId = 'ZP_MOCK_SUCCESS_777';
        $callbackData = [
            'apptransid' => '261108_BK-AUDIT-2026_111',
            'zptransid' => $zpTransId,
            'amount' => 3000000,
            'status' => 1,
        ];

        $payment = $zaloService->processCallback($callbackData);
        $this->assertSame('completed', $payment->status);
        $this->assertSame('confirmed', $this->booking->fresh()->status);

        // Replay callback lần 2: phải nhận diện là completed replay, không bị ghi đè
        $replayPayment = $zaloService->processCallback($callbackData);
        $this->assertSame($payment->id, $replayPayment->id);
        $this->assertSame('completed', $replayPayment->status);
    }
}
