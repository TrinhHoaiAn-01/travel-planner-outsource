<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\RoomType;
use App\Models\User;
use App\Services\AiTravelAssistantService;
use App\Services\CommerceBackendService;
use App\Services\CommerceExperienceService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinalCloseoutRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $customerUser;
    protected Destination $destination;
    protected RoomType $roomType;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin.closeout@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->customerUser = User::factory()->create([
            'email' => 'customer.closeout@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        $city = City::create(['name' => 'Đà Nẵng', 'slug' => 'da-nang']);
        $category = Category::create(['name' => 'Khách sạn', 'slug' => 'khach-san']);

        $this->destination = Destination::create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Furama Resort Danang',
            'slug' => 'furama-resort-danang',
            'is_lodging' => true,
        ]);

        $this->roomType = RoomType::create([
            'destination_id' => $this->destination->id,
            'name' => 'Ocean Suite',
            'capacity' => 2,
            'price_per_night' => 2000000,
            'quantity' => 5,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->customerUser->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(5)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(7)->toDateString(),
            'room_count' => 1,
            'total_price' => 4000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-ABCD',
        ]);
    }

    /**
     * Khuyết tật 1: Cựu quản trị viên (demoted admin) có lịch sử merchant cũ không bị rò rỉ vào shopping.
     * Cuộc hội thoại chứa thông điệp merchant "Phòng 203 đã được khóa đến thứ Hai." bị cách ly hoàn toàn.
     */
    public function test_defect_1_demoted_admin_legacy_merchant_history_is_quarantined_and_not_returned(): void
    {
        // 1. Tạo cựu admin hiện có role = 'user'
        $demotedAdmin = User::factory()->create([
            'email' => 'former.admin@example.com',
            'role' => 'user',
            'is_active' => true,
        ]);

        // Tạo hội thoại legacy được gắn nhầm nhãn 'shopping' trong đợt triển khai cũ
        $legacyConv = AiConversation::create([
            'user_id' => $demotedAdmin->id,
            'role' => 'shopping',
        ]);

        AiMessage::create([
            'conversation_id' => $legacyConv->id,
            'role' => 'user',
            'content' => 'Khóa phòng cho đoàn khách tuần tới.',
        ]);

        AiMessage::create([
            'conversation_id' => $legacyConv->id,
            'role' => 'assistant',
            'content' => 'Phòng 203 đã được khóa đến thứ Hai.',
        ]);

        // 2. Chạy migration forward quarantine 000004
        $migration = require database_path('migrations/2026_11_08_000004_quarantine_ambiguous_legacy_cohort.php');
        $migration->up();

        // Kiểm tra hội thoại đã bị chuyển sang role 'quarantined'
        $this->assertSame('quarantined', $legacyConv->fresh()->role);

        // 3. Kiểm tra GET /commerce-api/shopping/history không trả lại tin nhắn merchant
        $historyRes = $this->actingAs($demotedAdmin)
            ->getJson('/commerce-api/shopping/history');

        $historyRes->assertOk();
        $items = $historyRes->json('data.items') ?? $historyRes->json('items') ?? [];
        $allText = json_encode($items, JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('Phòng 203 đã được khóa đến thứ Hai.', $allText);

        // 4. Kiểm tra AiTravelAssistantService tái tạo ngữ cảnh mô hình không chứa tin nhắn merchant
        $assistant = new AiTravelAssistantService();
        $reconstructedWire = null;
        $assistant->reply(
            message: 'Xin chào Travel Planner',
            conversation: [],
            user: $demotedAdmin,
            sessionId: 'test_session_demoted',
            role: 'shopping',
            onEvent: function ($e) {}
        );

        $cachedHistory = Cache::get("chat_history:shopping:user_{$demotedAdmin->id}", []);
        $cachedText = json_encode($cachedHistory, JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('Phòng 203 đã được khóa đến thứ Hai.', $cachedText);
    }

    /**
     * Khuyết tật 1: Lịch sử mua sắm gần đây hợp lệ (legitimate recent shopping history) được bảo toàn.
     */
    public function test_defect_1_legitimate_recent_shopping_history_preserved_and_restored(): void
    {
        // 1. Tạo hội thoại shopping hợp lệ gần đây với xuất xứ storefront
        $legitConv = AiConversation::create([
            'user_id' => $this->customerUser->id,
            'role' => 'shopping',
            'provenance' => 'storefront',
        ]);

        AiMessage::create([
            'conversation_id' => $legitConv->id,
            'role' => 'user',
            'content' => 'Tôi muốn tìm phòng khách sạn tại Đà Nẵng gần biển.',
        ]);

        AiMessage::create([
            'conversation_id' => $legitConv->id,
            'role' => 'assistant',
            'content' => 'Furama Resort Danang có phòng Ocean Suite hướng biển rất phù hợp với quý khách.',
        ]);

        // Chạy migration forward quarantine 000004
        $migration = require database_path('migrations/2026_11_08_000004_quarantine_ambiguous_legacy_cohort.php');
        $migration->up();

        // Hội thoại hợp lệ vẫn giữ nguyên role 'shopping'
        $this->assertSame('shopping', $legitConv->fresh()->role);

        // GET /commerce-api/shopping/history trả lại lịch sử hợp lệ
        $historyRes = $this->actingAs($this->customerUser)
            ->getJson('/commerce-api/shopping/history');

        $historyRes->assertOk();
        $items = $historyRes->json('data.items') ?? $historyRes->json('items') ?? [];
        $allText = json_encode($items, JSON_UNESCAPED_UNICODE);
        $this->assertStringContainsString('Furama Resort Danang có phòng Ocean Suite hướng biển', $allText);
    }

    /**
     * Khuyết tật 2: Rollback transaction bên ngoài hủy bỏ cả thao tác booking lẫn trạng thái approval trong database.
     * Khi retry không báo thành công giả tạo (fake success) khi booking vẫn ở trạng thái pending.
     */
    public function test_defect_2_approval_rollback_atomicity_and_retry(): void
    {
        $backend = new CommerceBackendService($this->customerUser);

        // 1. Tạo bản xem trước hủy đơn
        $preview = $backend->execute('cancel_booking', [
            'booking_id' => $this->booking->id,
        ]);

        $this->assertTrue($preview['preview']);
        $this->assertTrue($preview['requires_confirmation']);
        $token = $preview['preview_token'];

        // Kiểm tra bản ghi trong bảng commerce_previews là pending
        $this->assertDatabaseHas('commerce_previews', [
            'token' => $token,
            'user_id' => $this->customerUser->id,
            'status' => 'pending',
        ]);

        // 2. Mở một transaction bên ngoài, xác nhận hủy, sau đó rollback transaction
        DB::beginTransaction();

        $confirmOutcome = $backend->confirmPreview(['preview_token' => $token]);
        $this->assertTrue($confirmOutcome['success']);
        $this->assertSame('cancelled', $confirmOutcome['status']);

        // Rollback transaction ngoài
        DB::rollBack();

        // Đơn booking trong DB vẫn ở trạng thái pending
        $this->booking->refresh();
        $this->assertSame('pending', $this->booking->status);

        // Bảng commerce_previews trong DB cũng được rollback về status 'pending' (không bị cache làm lệch trạng thái)
        $previewRow = DB::table('commerce_previews')->where('token', $token)->first();
        $this->assertNotNull($previewRow);
        $this->assertSame('pending', $previewRow->status);

        // 3. Thực hiện retry token trong transaction thực tế
        $retryOutcome = $backend->confirmPreview(['preview_token' => $token]);
        $this->assertTrue($retryOutcome['success']);
        $this->assertSame('cancelled', $retryOutcome['status']);

        $this->booking->refresh();
        $this->assertSame('cancelled', $this->booking->status);

        // 4. Retry lần nữa (Idempotent Replay): Trả lại kết quả cam kết thành công
        $replayOutcome = $backend->confirmPreview(['preview_token' => $token]);
        $this->assertSame($retryOutcome['booking_id'], $replayOutcome['booking_id']);
        $this->assertSame('cancelled', $replayOutcome['status']);
    }

    /**
     * Khuyết tật 2: Bản xem trước hết hạn bị từ chối an toàn.
     */
    public function test_defect_2_expired_preview_rejected(): void
    {
        $backend = new CommerceBackendService($this->customerUser);

        $preview = $backend->execute('cancel_booking', [
            'booking_id' => $this->booking->id,
        ]);
        $token = $preview['preview_token'];

        // Cập nhật expires_at về quá khứ trong DB
        DB::table('commerce_previews')->where('token', $token)->update([
            'expires_at' => now()->subMinutes(5)->timestamp,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Bản xem trước đã hết thời gian hiệu lực');

        $backend->confirmPreview(['preview_token' => $token]);
    }

    /**
     * Khuyết tật 2: Xác nhận preview hoạt động độc lập ngay cả khi Cache driver không phải database (array cache).
     */
    public function test_defect_2_confirm_preview_works_with_array_cache_driver(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();

        $backend = new CommerceBackendService($this->customerUser);
        $preview = $backend->execute('cancel_booking', [
            'booking_id' => $this->booking->id,
        ]);
        $token = $preview['preview_token'];

        // Xóa hoàn toàn cache để đảm bảo DB là nguồn sự thật duy nhất (Single Source of Truth)
        Cache::flush();

        $outcome = $backend->confirmPreview(['preview_token' => $token]);
        $this->assertTrue($outcome['success']);
        $this->assertSame('cancelled', $outcome['status']);
        $this->assertSame('cancelled', $this->booking->fresh()->status);
    }

    /**
     * Khuyết tật 3: Câu hỏi "Cho tôi mã QR để chuyển khoản đơn #BK-2026-ABCD" khởi tạo thanh toán SePay/VietQR
     * với provider được bảo toàn, không gọi nhầm sang confirm_bank_transfer.
     */
    public function test_defect_3_qr_code_request_initiates_sepay_checkout(): void
    {
        $backend = new CommerceBackendService($this->customerUser);

        // Gọi trực tiếp initiateCheckout với provider='sepay'
        $checkoutResult = $backend->execute('initiate_checkout', [
            'booking_code' => $this->booking->booking_code,
            'provider' => 'sepay',
        ]);

        $this->assertTrue($checkoutResult['success']);
        $this->assertSame('sepay', $checkoutResult['selected_provider']);
        $this->assertFalse($checkoutResult['is_paid']);
        $this->assertNotEmpty($checkoutResult['pay_url']);
        $this->assertStringContainsString('method=sepay', $checkoutResult['pay_url']);
    }

    /**
     * Khuyết tật 3: Câu thông báo "Tôi đã chuyển khoản đơn #BK-2026-ABCD" gọi công cụ confirm_bank_transfer
     * và kiểm tra phê duyệt từ Admin Dashboard.
     */
    public function test_defect_3_already_paid_claim_checks_admin_approval(): void
    {
        $backend = new CommerceBackendService($this->customerUser);

        $transferResult = $backend->execute('confirm_bank_transfer', [
            'booking_code' => $this->booking->booking_code,
        ]);

        $this->assertTrue($transferResult['success']);
        $this->assertFalse($transferResult['is_paid']);
        $this->assertSame('pending', $transferResult['status']);
        $this->assertStringContainsString('Chờ xác nhận', $transferResult['message']);
    }

    /**
     * Khuyết tật 3: Yêu cầu đặt phòng cụ thể kèm ngày tháng ở chế độ no-key route trực tiếp đến tra cứu danh mục
     * và tạo preview đặt phòng an toàn thay vì trả về gợi ý chung chung.
     */
    public function test_defect_3_no_key_mode_explicit_hotel_booking_proposes_preview(): void
    {
        $backend = new CommerceBackendService($this->customerUser);

        // Giả lập yêu cầu đặt phòng cụ thể kèm ngày tháng
        $checkIn = Carbon::now()->addDays(10)->toDateString();
        $checkOut = Carbon::now()->addDays(12)->toDateString();

        $previewResult = $backend->execute('book_hotel_room', [
            'hotel_name' => 'Furama Resort Danang',
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'room_count' => 1,
        ]);

        $this->assertTrue($previewResult['preview']);
        $this->assertTrue($previewResult['requires_confirmation']);
        $this->assertSame('book_hotel_room', $previewResult['action']);
        $this->assertSame($this->roomType->id, $previewResult['room_type_id']);
        $this->assertNotEmpty($previewResult['preview_token']);
        $this->assertStringContainsString('Furama Resort Danang', $previewResult['message']);
    }
}

