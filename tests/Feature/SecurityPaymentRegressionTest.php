<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecurityPaymentRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected User $realAdmin;
    protected Destination $destination;
    protected RoomType $roomType;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'regular.user@example.com',
            'role' => 'user',
        ]);

        $this->otherUser = User::factory()->create([
            'email' => 'other.user@example.com',
            'role' => 'user',
        ]);

        $this->realAdmin = User::factory()->create([
            'email' => 'superadmin@example.com',
            'role' => 'admin',
        ]);

        $this->destination = Destination::factory()->create();
        $this->roomType = RoomType::create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Deluxe Hướng Hồ',
            'capacity' => 2,
            'price_per_night' => 1000000,
            'quantity' => 5,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(2)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(4)->toDateString(),
            'room_count' => 1,
            'total_price' => 2000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-SEC1',
        ]);
    }

    /**
     * Khuyết tật 1: Dev login bị vô hiệu hóa mặc định và trong production; không chọn tài khoản thật.
     */
    public function test_dev_logins_disabled_in_production_and_by_default_and_no_accounts_created(): void
    {
        // 1. Mặc định (allow_dev_login = false): cả 2 route trả về 404
        config(['app.allow_dev_login' => false]);
        $this->get('/dev/login-as-admin')->assertStatus(404);
        $this->get('/dev/login-as-user')->assertStatus(404);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'demo-admin@travelplanner.test']);
        $this->assertDatabaseMissing('users', ['email' => 'demo-user@travelplanner.test']);

        // 2. Production: trả về 404 kể cả khi cờ bật
        config(['app.env' => 'production', 'app.allow_dev_login' => true]);
        $this->get('/dev/login-as-admin')->assertStatus(404);
        $this->get('/dev/login-as-user')->assertStatus(404);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'demo-admin@travelplanner.test']);
        $this->assertDatabaseMissing('users', ['email' => 'demo-user@travelplanner.test']);

        // 3. Khi bật ở môi trường dev: chỉ đăng nhập tài khoản demo chuyên dụng, không impersonate realAdmin
        config(['app.env' => 'local', 'app.allow_dev_login' => true]);
        $this->get('/dev/login-as-admin')->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('demo-admin@travelplanner.test', auth()->user()->email);
        $this->assertNotEquals($this->realAdmin->id, auth()->id());
    }

    /**
     * Khuyết tật 2: Chủ booking không thể tự xác nhận thanh toán trực tuyến qua payments.store hoặc simulator.
     */
    public function test_online_methods_in_store_do_not_self_confirm_and_cash_creates_pending(): void
    {
        // 1. Gửi online payment method momo: chuyển tiếp sang gateway, không tạo payment completed
        $this->actingAs($this->user)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('payments.store', $this->booking->id), [
                'payment_method' => 'momo',
                'amount' => 2000000,
            ]);

        $this->booking->refresh();
        $this->assertEquals('pending', $this->booking->status);
        $completedPayments = Payment::where('booking_id', $this->booking->id)->where('status', 'completed')->count();
        $this->assertEquals(0, $completedPayments);

        // 2. Phương thức cash: tạo payment pending và giữ booking pending
        $this->actingAs($this->user)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post(route('payments.store', $this->booking->id), [
                'payment_method' => 'cash',
                'amount' => 2000000,
            ]);

        $this->booking->refresh();
        $this->assertEquals('pending', $this->booking->status);
        $cashPayment = Payment::where('booking_id', $this->booking->id)->where('payment_method', 'cash')->first();
        $this->assertNotNull($cashPayment);
        $this->assertEquals('pending', $cashPayment->status);

        // 3. Simulator mặc định trả về 404
        config(['app.allow_payment_simulator' => false]);
        $this->actingAs($this->user)
            ->post(route('payments.simulate-receive', $this->booking->id), [
                'payment_method' => 'bank_transfer',
            ])->assertStatus(404);
    }

    /**
     * Khuyết tật 3: SePay webhook yêu cầu cấu hình secret tường minh, từ chối Testmode bypass và underpayment.
     */
    public function test_sepay_webhook_security_and_validation(): void
    {
        config(['sepay.api_key' => 'secret_sepay_test_key_12345']);

        // 1. Không có tiêu đề xác thực hoặc sai API Key -> 401
        $this->postJson(route('payments.sepay.webhook'), [
            'content' => 'TPB BK-2026-SEC1',
            'transferAmount' => 2000000,
        ])->assertStatus(401);

        $this->withHeaders(['Authorization' => 'Apikey wrong_key'])
            ->postJson(route('payments.sepay.webhook'), [
                'content' => 'TPB BK-2026-SEC1',
                'transferAmount' => 2000000,
            ])->assertStatus(401);

        // 2. Header User-Agent: Testmode không bỏ qua kiểm tra API key
        $this->withHeaders(['User-Agent' => 'Testmode'])
            ->postJson(route('payments.sepay.webhook'), [
                'content' => 'TPB BK-2026-SEC1',
                'transferAmount' => 1,
            ])->assertStatus(401);

        // 3. Mã booking không tồn tại -> 400, không tự ý gán vào booking ngẫu nhiên
        $this->withHeaders(['Authorization' => 'Apikey secret_sepay_test_key_12345'])
            ->postJson(route('payments.sepay.webhook'), [
                'content' => 'TPB BK-UNKNOWN-9999',
                'transferAmount' => 2000000,
                'transferType' => 'in',
                'referenceCode' => 'REF_UNKNOWN',
            ])->assertStatus(400);

        // 4. Giao dịch rút tiền (transferType = out) -> 400
        $this->withHeaders(['Authorization' => 'Apikey secret_sepay_test_key_12345'])
            ->postJson(route('payments.sepay.webhook'), [
                'content' => 'TPB BK-2026-SEC1',
                'transferAmount' => 2000000,
                'transferType' => 'out',
                'referenceCode' => 'REF_OUT_1',
            ])->assertStatus(400);

        // 5. Thanh toán thiếu tiền (Underpayment: 1 VND thay vì 2,000,000 VND) -> 400
        $this->withHeaders(['Authorization' => 'Apikey secret_sepay_test_key_12345'])
            ->postJson(route('payments.sepay.webhook'), [
                'content' => 'TPB BK-2026-SEC1',
                'transferAmount' => 1,
                'transferType' => 'in',
                'referenceCode' => 'REF_UNDER_1',
            ])->assertStatus(400);

        // 6. Thanh toán hợp lệ -> 200, xác nhận booking và tạo payment completed
        $this->withHeaders(['Authorization' => 'Apikey secret_sepay_test_key_12345'])
            ->postJson(route('payments.sepay.webhook'), [
                'content' => 'TPB BK-2026-SEC1 thanh toan',
                'accountNumber' => '0000000002',
                'transferAmount' => 2000000,
                'transferType' => 'in',
                'referenceCode' => 'SEPAY_TX_VALID_1',
            ])->assertStatus(200);

        $this->booking->refresh();
        $this->assertEquals('confirmed', $this->booking->status);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'transaction_id' => 'SEPAY_TX_VALID_1',
            'status' => 'completed',
        ]);

        // 7. Webhook replay hợp lệ cùng referenceCode -> xử lý idempotent
        $this->withHeaders(['Authorization' => 'Apikey secret_sepay_test_key_12345'])
            ->postJson(route('payments.sepay.webhook'), [
                'content' => 'TPB BK-2026-SEC1 thanh toan',
                'accountNumber' => '0000000002',
                'transferAmount' => 2000000,
                'transferType' => 'in',
                'referenceCode' => 'SEPAY_TX_VALID_1',
            ])->assertStatus(200);

        $this->assertEquals(1, Payment::where('transaction_id', 'SEPAY_TX_VALID_1')->count());
    }

    /**
     * Khuyết tật 4: Bảo toàn trạng thái đơn đã hủy, idempotent callback, và không ghi đè phương thức khác.
     */
    public function test_gateway_callbacks_preserve_cancelled_state_and_do_not_overwrite_other_methods(): void
    {
        config(['sepay.api_key' => 'secret_sepay_test_key_12345']);

        // Tạo 1 pending payment từ MoMo trước
        $momoPending = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 2000000,
            'payment_method' => 'momo',
            'status' => 'pending',
            'transaction_id' => 'MOMO_PENDING_TX',
        ]);

        // Đơn đã bị hủy trước đó
        $this->booking->update(['status' => 'cancelled']);

        // SePay gửi webhook báo đã nhận tiền cho đơn đã hủy
        $response = $this->withHeaders(['Authorization' => 'Apikey secret_sepay_test_key_12345'])
            ->postJson(route('payments.sepay.webhook'), [
                'content' => 'TPB BK-2026-SEC1',
                'accountNumber' => '0000000002',
                'transferAmount' => 2000000,
                'transferType' => 'in',
                'referenceCode' => 'SEPAY_LATE_TX',
            ]);

        $response->assertStatus(200);
        $this->booking->refresh();

        // Trạng thái đơn booking PHẢI giữ nguyên 'cancelled' (không tự ý chuyển thành confirmed)
        $this->assertEquals('cancelled', $this->booking->status);

        // Bản ghi thanh toán MoMo pending ban đầu KHÔNG bị ghi đè phương thức thành banking
        $momoPending->refresh();
        $this->assertEquals('momo', $momoPending->payment_method);
        $this->assertEquals('MOMO_PENDING_TX', $momoPending->transaction_id);

        // Có bản ghi thanh toán SePay mới phục vụ đối soát hoàn tiền
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'transaction_id' => 'SEPAY_LATE_TX',
            'payment_method' => 'banking',
            'status' => 'reconciliation',
        ]);
    }

    /**
     * Khuyết tật 5: Tuyến xác thực email bắt buộc liên kết ký số (signed URL) hợp lệ.
     */
    public function test_email_verification_requires_valid_signed_url(): void
    {
        $unverifiedUser = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 1. Truy cập trực tiếp URL không có chữ ký (unsigned) -> 403
        $unsignedUrl = route('verification.verify', [
            'id' => $unverifiedUser->id,
            'hash' => sha1($unverifiedUser->getEmailForVerification()),
        ]);

        $this->actingAs($unverifiedUser)
            ->get($unsignedUrl)
            ->assertStatus(403);
        $this->assertFalse($unverifiedUser->fresh()->hasVerifiedEmail());

        // 2. Truy cập URL có chữ ký nhưng đã hết hạn (expired) -> 403
        $expiredSignedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->subMinutes(10),
            [
                'id' => $unverifiedUser->id,
                'hash' => sha1($unverifiedUser->getEmailForVerification()),
            ]
        );

        $this->actingAs($unverifiedUser)
            ->get($expiredSignedUrl)
            ->assertStatus(403);
        $this->assertFalse($unverifiedUser->fresh()->hasVerifiedEmail());

        // 3. User khác đăng nhập truy cập link của unverifiedUser -> 403
        $validSignedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $unverifiedUser->id,
                'hash' => sha1($unverifiedUser->getEmailForVerification()),
            ]
        );

        $this->actingAs($this->otherUser)
            ->get($validSignedUrl)
            ->assertStatus(403);
        $this->assertFalse($unverifiedUser->fresh()->hasVerifiedEmail());

        // 4. Đúng user với đúng temporary signed URL hợp lệ -> xác thực thành công
        $this->actingAs($unverifiedUser)
            ->get($validSignedUrl)
            ->assertRedirect(route('home'));
        $this->assertTrue($unverifiedUser->fresh()->hasVerifiedEmail());
    }
}
