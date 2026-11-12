<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\User;
use App\Services\CommerceBackendService;
use App\Services\DashboardService;
use App\Services\Interfaces\MoMoGatewayServiceInterface;
use App\Services\Interfaces\SePayGatewayServiceInterface;
use App\Services\PaymentService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class FollowUpRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Destination $destination;
    protected RoomType $roomType;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);

        $this->user = User::factory()->create([
            'email' => 'customer.test@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->destination = Destination::factory()->create(['name' => 'Vinpearl Resort & Spa Phú Quốc']);
        $this->roomType = RoomType::create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Deluxe Hướng Biển',
            'capacity' => 2,
            'price_per_night' => 1500000,
            'quantity' => 10,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(5)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(8)->toDateString(),
            'room_count' => 1,
            'total_price' => 4500000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-FOLLOW1',
        ]);
    }

    /**
     * Khuyết tật 2: Đơn đã thanh toán không thể thanh toán lần 2; đơn confirmed chưa trả tiền vẫn được thanh toán.
     */
    public function test_already_paid_booking_cannot_initiate_second_payment(): void
    {
        // 1. Đơn ban đầu ở trạng thái pending -> canBePaid = true, Gate::allows('pay') = true
        $this->assertTrue($this->booking->canBePaid());
        $this->assertTrue(Gate::forUser($this->user)->allows('pay', $this->booking));

        // 2. Ghi nhận giao dịch hoàn tất (completed payment)
        Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 4500000,
            'payment_method' => 'momo',
            'transaction_id' => 'MOMO_COMPLETED_1',
            'status' => 'completed',
            'payment_date' => now(),
        ]);
        $this->booking->update(['status' => 'completed']);
        $this->booking->refresh();

        // Bây giờ đơn đã hoàn tất thanh toán
        $this->assertTrue($this->booking->isPaid());
        $this->assertFalse($this->booking->canBePaid());
        $this->assertFalse(Gate::forUser($this->user)->allows('pay', $this->booking));

        // Khởi tạo cổng SePay trên đơn đã trả tiền -> Ném ValidationException
        $sepaySvc = app(SePayGatewayServiceInterface::class);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $sepaySvc->createOrGetPayment($this->booking);
    }

    /**
     * Khuyết tật 2 (b): Đơn confirmed nhưng chưa thanh toán vẫn được phép thanh toán.
     */
    public function test_confirmed_unpaid_booking_can_still_pay(): void
    {
        $this->booking->update(['status' => 'confirmed']);
        $this->booking->refresh();

        // Chưa có payment completed nào
        $this->assertFalse($this->booking->isPaid());
        $this->assertTrue($this->booking->canBePaid());
        $this->assertTrue(Gate::forUser($this->user)->allows('pay', $this->booking));
    }

    /**
     * Khuyết tật 3: Webhook SePay fail-closed khi thiếu cấu hình hoặc sai người thụ hưởng / chiều nhận tiền.
     */
    public function test_sepay_webhook_fails_closed_and_validates_required_contract(): void
    {
        config([
            'sepay.api_key' => 'secret_sepay_test_key_12345',
            'sepay.account_number' => '0000000002',
        ]);

        $sepaySvc = app(SePayGatewayServiceInterface::class);

        // 1. Thiếu transferType
        $resMissingDirection = $sepaySvc->processWebhook([
            'content' => 'TPB BK-2026-FOLLOW1',
            'accountNumber' => '0000000002',
            'transferAmount' => 4500000,
            'referenceCode' => 'REF_1',
        ]);
        $this->assertFalse($resMissingDirection['success']);

        // 2. Chiều chuyển đi (out)
        $resOutgoing = $sepaySvc->processWebhook([
            'content' => 'TPB BK-2026-FOLLOW1',
            'accountNumber' => '0000000002',
            'transferAmount' => 4500000,
            'transferType' => 'out',
            'referenceCode' => 'REF_2',
        ]);
        $this->assertFalse($resOutgoing['success']);

        // 3. Sai số tài khoản thụ hưởng
        $resWrongAccount = $sepaySvc->processWebhook([
            'content' => 'TPB BK-2026-FOLLOW1',
            'accountNumber' => '9999999999',
            'transferAmount' => 4500000,
            'transferType' => 'in',
            'referenceCode' => 'REF_3',
        ]);
        $this->assertFalse($resWrongAccount['success']);

        // 4. Webhook cho đơn đã hủy -> Ghi nhận biên lai trạng thái reconciliation (đối soát hoàn tiền)
        $this->booking->update(['status' => 'cancelled']);
        $resCancelled = $sepaySvc->processWebhook([
            'content' => 'TPB BK-2026-FOLLOW1',
            'accountNumber' => '0000000002',
            'transferAmount' => 4500000,
            'transferType' => 'in',
            'referenceCode' => 'REF_LATE_CANCELLED',
        ]);
        $this->assertTrue($resCancelled['success']);
        $this->booking->refresh();
        $this->assertEquals('cancelled', $this->booking->status);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'transaction_id' => 'REF_LATE_CANCELLED',
            'status' => 'reconciliation',
        ]);
    }

    /**
     * Khuyết tật 4: Đổi ngày đặt phòng vô hiệu hóa các giao dịch pending cũ trước khi đổi giá mới.
     */
    public function test_reschedule_expires_pending_payment_attempts_and_locks_safely(): void
    {
        // Tạo giao dịch pending cũ
        $pendingPayment = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 4500000,
            'payment_method' => 'sepay',
            'transaction_id' => 'OLD_PENDING_TXN',
            'status' => 'pending',
            'payment_date' => now(),
        ]);

        $backend = new CommerceBackendService($this->user);
        $newCheckIn = Carbon::now()->addDays(6)->toDateString();
        $newCheckOut = Carbon::now()->addDays(9)->toDateString();

        $preview = $backend->execute('update_booking_dates', [
            'booking_id' => $this->booking->id,
            'check_in_date' => $newCheckIn,
            'check_out_date' => $newCheckOut,
        ]);
        $this->assertTrue($preview['preview']);
        $result = $backend->confirmPreview([
            'preview_token' => $preview['preview_token'],
        ]);

        $this->assertTrue($result['success']);
        $this->booking->refresh();
        $this->assertEquals($newCheckIn, Carbon::parse($this->booking->check_in_date)->toDateString());

        // Giao dịch pending cũ phải chuyển sang status failed (vô hiệu hóa)
        $pendingPayment->refresh();
        $this->assertEquals('failed', $pendingPayment->status);
    }

    /**
     * Khuyết tật 4 (b): Hoàn tiền một giao dịch không làm hủy đơn đặt phòng nếu vẫn còn giao dịch hợp lệ khác.
     */
    public function test_refund_one_payment_does_not_cancel_booking_if_other_completed_payment_exists(): void
    {
        // 2 giao dịch completed hợp lệ trên cùng 1 đơn
        $pay1 = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 2000000,
            'payment_method' => 'momo',
            'transaction_id' => 'TX_1',
            'status' => 'completed',
            'payment_date' => now(),
        ]);
        $pay2 = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 2500000,
            'payment_method' => 'banking',
            'transaction_id' => 'TX_2',
            'status' => 'completed',
            'payment_date' => now(),
        ]);
        $this->booking->update(['status' => 'confirmed']);

        $paymentService = app(PaymentService::class);
        $paymentService->updatePaymentStatus($pay1->id, 'refunded');

        $this->booking->refresh();
        // Đơn không bị chuyển thành cancelled vì pay2 vẫn đang completed
        $this->assertEquals('confirmed', $this->booking->status);
    }

    /**
     * Khuyết tật 5: Công cụ đặt phòng, hủy phòng, đổi ngày bắt buộc có bước Preview (confirm=false không ghi dữ liệu).
     */
    public function test_booking_mutations_require_explicit_confirmation(): void
    {
        $backend = new CommerceBackendService($this->user);

        // 1. Preview đặt phòng: confirm=false không tạo booking
        $initialCount = Booking::count();
        $previewResult = $backend->execute('book_hotel_room', [
            'hotel_name' => $this->destination->name,
            'check_in_date' => Carbon::now()->addDays(10)->toDateString(),
            'nights' => 3,
            'confirm' => false,
        ]);
        $this->assertTrue($previewResult['preview']);
        $this->assertTrue($previewResult['requires_confirmation']);
        $this->assertEquals($initialCount, Booking::count());

        // 2. Không được chọn khách sạn tùy tiện khi không tìm thấy
        $this->expectException(DomainException::class);
        $backend->execute('book_hotel_room', [
            'hotel_name' => 'Khách sạn Không Hề Tồn Tại ABCXYZ',
            'check_in_date' => Carbon::now()->addDays(10)->toDateString(),
            'nights' => 3,
            'confirm' => true,
        ]);
    }

    /**
     * Khuyết tật 8: Dashboard CSV làm sạch các ký tự nguy hiểm (=, +, -, @, \t, \r) chống formula injection.
     */
    public function test_dashboard_csv_neutralizes_formula_injection_and_preserves_vietnamese(): void
    {
        $dashboardSvc = app(DashboardService::class);

        // Chuỗi công thức nguy hiểm
        $malicious = '=cmd|"/C calc"!A0';
        $sanitized = $dashboardSvc->sanitizeCsvCell($malicious);
        $this->assertStringStartsWith("'", $sanitized);

        // Chuỗi bắt đầu bằng +
        $plusFormula = '+1+1';
        $sanitizedPlus = $dashboardSvc->sanitizeCsvCell($plusFormula);
        $this->assertStringStartsWith("'", $sanitizedPlus);

        // Tên tiếng Việt thông thường không bị thêm nháy
        $vietnameseName = 'Nguyễn Văn An';
        $this->assertSame('Nguyễn Văn An', $dashboardSvc->sanitizeCsvCell($vietnameseName));

        // Số nguyên không bị thay đổi
        $number = 1500000;
        $this->assertSame(1500000, $dashboardSvc->sanitizeCsvCell($number));
    }

    /**
     * Khuyết tật 10: initiate_checkout trả về thông tin thanh toán chính xác và đường dẫn tin cậy.
     */
    public function test_initiate_checkout_returns_payment_url_and_instructions(): void
    {
        config([
            'sepay.api_key' => 'TEST_KEY',
            'sepay.account_number' => '0901234567',
        ]);

        $backend = new CommerceBackendService($this->user);

        // 1. Gọi initiate_checkout cho đơn pending hợp lệ với provider sepay
        $res = $backend->execute('initiate_checkout', [
            'booking_code' => $this->booking->booking_code,
            'provider' => 'sepay',
        ]);

        $this->assertTrue($res['success']);
        $this->assertFalse($res['is_paid']);
        $this->assertNotEmpty($res['pay_url']);
        $this->assertNotEmpty($res['payment_instructions']['account_number']);
        $this->assertEquals(4500000, $res['total_price']);

        // 2. Gọi initiate_checkout cho đơn đã hủy -> Báo lỗi
        $this->booking->update(['status' => 'cancelled']);
        $this->expectException(DomainException::class);
        $backend->execute('initiate_checkout', [
            'booking_code' => $this->booking->booking_code,
        ]);
    }

    /**
     * Khuyết tật 9: Kiểm tra nhận diện ngôn ngữ tiếng Việt có dấu, không dấu và phủ định.
     */
    public function test_vietnamese_runner_accent_and_negation_detection(): void
    {
        $python = config('commerce.python');
        $script = base_path('plugins/commerce-agents/travel_planner/runner.py');

        $command = "import sys; sys.path.insert(0, 'plugins/commerce-agents/travel_planner'); from runner import detect_city_in_text, classify_general_prompt, has_negative_intent, resolve_guided_city_or_topic; " .
            "assert detect_city_in_text('Khach san o da nang') == 'Đà Nẵng'; " .
            "assert detect_city_in_text('Lich trinh Hue 2 ngay') == 'Huế'; " .
            "assert classify_general_prompt('Goi y dia diem du lich') == 'destinations'; " .
            "assert has_negative_intent('Đừng đặt phòng') is True; " .
            "assert has_negative_intent('Tôi muốn đặt phòng') is False; " .
            "ans, chips = resolve_guided_city_or_topic('Lich trinh Hue 2 ngay', 'Huế'); " .
            "assert '2 ngày 1 đêm' in ans; " .
            "print('ALL_PYTHON_VIETNAMESE_TESTS_OK')";

        $process = new \Symfony\Component\Process\Process([$python, '-c', $command], base_path());
        $process->mustRun();

        $this->assertStringContainsString('ALL_PYTHON_VIETNAMESE_TESTS_OK', $process->getOutput());
    }
}
