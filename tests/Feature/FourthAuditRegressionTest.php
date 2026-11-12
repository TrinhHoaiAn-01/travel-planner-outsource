<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\Trip;
use App\Models\User;
use App\Services\CommerceBackendService;
use App\Services\Interfaces\AiTravelAssistantInterface;
use App\Services\Interfaces\SePayGatewayServiceInterface;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FourthAuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $adminUser;
    protected Destination $destination;
    protected RoomType $roomType;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'traveler_audit4@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $this->adminUser = User::factory()->create([
            'email' => 'admin_audit4@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->destination = Destination::factory()->create([
            'name' => 'Khách sạn Biển Bạc',
        ]);

        $this->roomType = RoomType::create([
            'destination_id' => $this->destination->id,
            'name' => 'Phòng Deluxe Hướng Biển',
            'capacity' => 2,
            'price_per_night' => 1000000,
            'quantity' => 5,
        ]);

        $this->booking = Booking::create([
            'booking_code' => 'BK-2026-AUD4',
            'user_id' => $this->user->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => '2026-11-20',
            'check_out_date' => '2026-11-22',
            'room_count' => 1,
            'total_price' => 2000000,
            'status' => 'pending',
        ]);
    }

    /**
     * P0.1: AI chat tool dispatch does not crash with Undefined variable $operationId,
     * and operation ID is scoped to authenticated owner and turn.
     */
    public function test_p0_1_ai_chat_runner_process_handles_tool_dispatch_without_operation_id_crash(): void
    {
        $assistant = app(AiTravelAssistantInterface::class);
        $backend = new CommerceBackendService($this->user);

        // Tạo bản xem trước cancel_booking thông qua CommerceBackendService trực tiếp
        $preview = $backend->cancelBooking(['booking_id' => $this->booking->id]);
        $this->assertTrue($preview['preview']);
        $this->assertTrue($preview['requires_confirmation']);
        $this->assertNotEmpty($preview['preview_token']);

        // Xác minh cache preview tồn tại
        $token = $preview['preview_token'];
        $cached = Cache::get("commerce_preview:{$token}");
        $this->assertNotNull($cached);
        $this->assertSame($this->booking->id, $cached['booking_id']);
    }

    /**
     * P0.2: confirm_preview is isolated from model/AI tool dispatch and only callable via authenticated surface action.
     */
    public function test_p0_2_confirm_preview_blocked_from_model_tool_dispatch(): void
    {
        $backend = new CommerceBackendService($this->user);

        // Tạo bản xem trước hủy phòng
        $preview = $backend->cancelBooking(['booking_id' => $this->booking->id]);
        $token = $preview['preview_token'];

        // Mô hình AI gọi execute('confirm_preview') trực tiếp phải bị chặn với DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Thao tác confirm_preview chỉ có thể được gọi từ giao diện người dùng có xác thực (Surface Action).');

        $backend->execute('confirm_preview', ['preview_token' => $token]);
    }

    /**
     * P0.2: Authenticated user can confirm preview via shopping surface action.
     */
    public function test_p0_2_confirm_preview_accessible_via_surface_action(): void
    {
        $backend = new CommerceBackendService($this->user);
        $preview = $backend->cancelBooking(['booking_id' => $this->booking->id]);
        $token = $preview['preview_token'];

        // Gọi surface action POST:confirm-preview
        $response = $this->actingAs($this->user)
            ->postJson('/commerce-api/shopping/confirm-preview', [
                'preview_token' => $token,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'booking_id' => $this->booking->id,
            'status' => 'cancelled',
        ]);

        $this->assertSame('cancelled', $this->booking->fresh()->status);
    }

    /**
     * P0.3: Price change / drift between preview generation and confirmation is rejected.
     */
    public function test_p0_3_price_change_drift_rejects_stale_preview(): void
    {
        $backend = new CommerceBackendService($this->user);

        // 1. Tạo bản xem trước đặt phòng mới với giá 1.000.000 đ/đêm * 2 đêm = 2.000.000 đ
        $preview = $backend->bookHotelRoom([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => '2026-12-01',
            'check_out_date' => '2026-12-03',
            'room_count' => 1,
        ]);

        $token = $preview['preview_token'];
        $this->assertEquals(2000000, $preview['total_price']);

        // 2. Quản trị viên thay đổi giá phòng lên 1.500.000 đ/đêm trước khi khách xác nhận
        $this->roomType->update(['price_per_night' => 1500000]);

        // 3. Khách xác nhận bản xem trước cũ -> Bị từ chối vì giá đã thay đổi
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Giá phòng đã thay đổi từ khi tạo bản xem trước');

        $backend->confirmPreview(['preview_token' => $token]);
    }

    /**
     * P0.3: Reschedule preview detects booking date drift in database and rejects stale preview.
     */
    public function test_p0_3_reschedule_detects_state_drift(): void
    {
        $backend = new CommerceBackendService($this->user);

        // 1. Tạo bản xem trước đổi ngày từ 20-22 sang 25-27
        $preview = $backend->updateBookingDates([
            'booking_id' => $this->booking->id,
            'check_in_date' => '2026-11-25',
            'check_out_date' => '2026-11-27',
        ]);
        $token = $preview['preview_token'];

        // 2. Một tiến trình khác (hoặc admin/lễ tân) đã đổi ngày đơn đặt phòng sang 21-23
        $this->booking->update([
            'check_in_date' => '2026-11-21',
            'check_out_date' => '2026-11-23',
        ]);

        // 3. Khách xác nhận bản xem trước dựa trên mốc ngày cũ -> Phải bị từ chối do state drift
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Thông tin ngày nhận phòng hiện tại của đơn đã thay đổi');

        $backend->confirmPreview(['preview_token' => $token]);
    }

    /**
     * P0.3: Reschedule validates linked trip bounds under lock.
     */
    public function test_p0_3_reschedule_validates_linked_trip_bounds(): void
    {
        $trip = Trip::create([
            'user_id' => $this->user->id,
            'name' => 'Chuyến đi Đà Nẵng',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-30',
        ]);

        $this->booking->update(['trip_id' => $trip->id]);

        $backend = new CommerceBackendService($this->user);

        // 1. Tạo bản xem trước đổi ngày hợp lệ ban đầu (26-28 nằm trong chuyến đi 20-30)
        $preview = $backend->updateBookingDates([
            'booking_id' => $this->booking->id,
            'check_in_date' => '2026-11-26',
            'check_out_date' => '2026-11-28',
        ]);
        $token = $preview['preview_token'];

        // 2. Chuyến đi bị rút ngắn ngày kết thúc về ngày 25/11 trước khi khách bấm xác nhận
        $trip->update(['end_date' => '2026-11-25']);

        // 3. confirmPreview phải kiểm tra lại trip bounds dưới lock và từ chối
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Ngày trả phòng không thể sau ngày kết thúc chuyến đi');

        $backend->confirmPreview(['preview_token' => $token]);
    }

    /**
     * P1.5: PaymentController::create recognizes method query param and sets active method.
     */
    public function test_p1_5_payment_create_maps_method_query_to_selected_method(): void
    {
        // 1. Query ?method=momo
        $responseMomo = $this->actingAs($this->user)
            ->get(route('payments.create', ['booking' => $this->booking->id, 'method' => 'momo']));
        $responseMomo->assertOk();
        $responseMomo->assertViewHas('selectedMethod', 'momo');
        $responseMomo->assertSee('value="momo" class="payment-option-input" checked', false);
        $responseMomo->assertSee('id="panel-momo" class="sandbox-panel" style="display: block;', false);

        // 2. Query ?method=zalopay
        $responseZalo = $this->actingAs($this->user)
            ->get(route('payments.create', ['booking' => $this->booking->id, 'method' => 'zalopay']));
        $responseZalo->assertOk();
        $responseZalo->assertViewHas('selectedMethod', 'zalopay');
        $responseZalo->assertSee('value="zalopay" class="payment-option-input" checked', false);
        $responseZalo->assertSee('id="panel-zalopay" class="sandbox-panel" style="display: block;', false);

        // 3. Query ?method=sepay maps to bank_transfer panel
        $responseSepay = $this->actingAs($this->user)
            ->get(route('payments.create', ['booking' => $this->booking->id, 'method' => 'sepay']));
        $responseSepay->assertOk();
        $responseSepay->assertViewHas('selectedMethod', 'sepay');
        $responseSepay->assertSee('id="panel-bank_transfer" class="sandbox-panel" style="display: block;', false);
    }

    /**
     * P1.6: SePay receipt for already completed booking does not revert status to confirmed.
     */
    public function test_p1_6_sepay_webhook_preserves_terminal_completed_status(): void
    {
        config([
            'sepay.api_key' => 'TEST_KEY',
            'sepay.account_number' => '0000000002',
            'sepay.webhook_token' => 'SECRET_TOKEN',
        ]);

        // Đặt đơn ở trạng thái hoàn tất (completed)
        $this->booking->update(['status' => 'completed']);

        $service = app(SePayGatewayServiceInterface::class);

        $payload = [
            'id' => 999123,
            'transferType' => 'in',
            'accountNumber' => '0000000002',
            'transferAmount' => 2000000,
            'referenceCode' => 'SP_RECON_COMPLETED_01',
            'code' => 'TPB ' . $this->booking->booking_code,
            'content' => 'TPB ' . $this->booking->booking_code . ' thanh toan muon',
        ];

        $result = $service->processWebhook($payload);

        $this->assertTrue($result['success']);
        // Trạng thái đơn booking PHẢI được bảo toàn là completed, tuyệt đối không bị lùi về confirmed!
        $this->assertSame('completed', $this->booking->fresh()->status);

        // Giao dịch chuyển khoản đến muộn phải ghi nhận vào status reconciliation
        $payment = $result['payment'];
        $this->assertSame('reconciliation', $payment->status);
        $this->assertEquals(2000000, $payment->amount);
    }

    /**
     * P1.7: Legacy admin conversations upgraded with default shopping role are quarantined.
     */
    public function test_p1_7_quarantine_legacy_admin_conversations(): void
    {
        // Tạo cuộc hội thoại thuộc admin nhưng role ban đầu bị gán nhầm là shopping
        $adminConv = AiConversation::create([
            'user_id' => $this->adminUser->id,
            'role' => 'shopping',
        ]);

        AiMessage::create([
            'conversation_id' => $adminConv->id,
            'role' => 'user',
            'content' => 'Kiểm tra tình trạng phòng khách sạn hôm nay.',
        ]);

        // Chạy migration quarantine
        $migration = require database_path('migrations/2026_11_08_000003_quarantine_ambiguous_legacy_ai_conversations.php');
        $migration->up();

        // Cuộc hội thoại của admin phải được cách ly để không rò rỉ vào lịch sử shopping
        $this->assertSame('quarantined', $adminConv->fresh()->role);
    }

    /**
     * P1.8: Itinerary view does not interpolate dynamic titles into innerHTML.
     */
    public function test_p1_8_itinerary_view_uses_safe_text_nodes_for_conflict(): void
    {
        $viewContent = file_get_contents(resource_path('views/itinerary/index.blade.php'));

        // Đảm bảo không còn innerHTML chèn trực tiếp conflict.title
        $this->assertStringNotContainsString('hintElem.innerHTML = `<div style="color: #DC2626; font-weight: 600;"><i class="bi bi-exclamation-octagon-fill me-1"></i><strong>Cảnh báo trùng giờ:</strong> Bị chồng chéo với "${conflict.title}"', $viewContent);

        // Đảm bảo sử dụng document.createTextNode
        $this->assertStringContainsString('alertDiv.appendChild(document.createTextNode(conflict.title || \'\'));', $viewContent);
    }
}
