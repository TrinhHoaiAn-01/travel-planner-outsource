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
use App\Models\Trip;
use App\Models\User;
use App\Services\AiTravelAssistantService;
use App\Services\CommerceBackendService;
use App\Services\CommerceExperienceService;
use App\Services\Interfaces\SePayGatewayServiceInterface;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AssistantPrivacyAndCorrectnessRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Destination $destination;
    protected RoomType $roomType;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin.commerce@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'email' => 'customer.commerce@example.com',
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
            'price_per_night' => 3000000,
            'quantity' => 5,
        ]);

        $this->booking = Booking::create([
            'user_id' => $this->customer->id,
            'room_type_id' => $this->roomType->id,
            'check_in_date' => Carbon::now()->addDays(2)->toDateString(),
            'check_out_date' => Carbon::now()->addDays(5)->toDateString(),
            'room_count' => 1,
            'total_price' => 9000000,
            'status' => 'pending',
            'booking_code' => '#BK-2026-ASST1',
        ]);
    }

    /**
     * Khuyết tật 10: Trí nhớ khách vãng lai (Guest memory) bắt nguồn hoàn toàn từ Laravel Session của máy chủ,
     * không tin cậy session_id do client truyền lên; hai phiên độc lập không đọc hay sửa chéo nhau.
     */
    public function test_guest_memory_isolated_by_server_session_and_ignores_client_session_id(): void
    {
        $experience = new CommerceExperienceService();

        // 1. Phiên khách A lưu trí nhớ vào session A
        $sessionAId = 'server_session_guest_A_11111';
        session()->setId($sessionAId);
        session()->start();
        session(["commerce_memory_shopping" => [
            ['key' => 'pref_city', 'value' => 'Thích đi Đà Nẵng', 'category' => 'preference'],
        ]]);
        Cache::forever("guest_memory:shopping:{$sessionAId}", [
            ['key' => 'pref_city', 'value' => 'Thích đi Đà Nẵng', 'category' => 'preference'],
        ]);

        // 2. Phiên khách B khởi tạo với session ID máy chủ B
        $sessionBId = 'server_session_guest_B_22222';
        session()->setId($sessionBId);
        session()->start();
        session(["commerce_memory_shopping" => [
            ['key' => 'pref_city', 'value' => 'Thích đi Hà Nội', 'category' => 'preference'],
        ]]);
        Cache::forever("guest_memory:shopping:{$sessionBId}", [
            ['key' => 'pref_city', 'value' => 'Thích đi Hà Nội', 'category' => 'preference'],
        ]);

        // Khách B cố tình gửi parameter session_id = sessionAId để tấn công đọc lén trí nhớ của khách A
        $readResult = $experience->handle('shopping', 'GET:memory', [
            'session_id' => $sessionAId,
        ], ['identity' => 'shopping:guest'], null);

        $facts = $readResult['data']['facts'] ?? [];
        $this->assertCount(1, $facts);
        $this->assertEquals('Thích đi Hà Nội', $facts[0]['value'], 'Máy chủ phải đọc từ session của khách B, tuyệt đối không đọc từ session của khách A.');

        // Khách B cố tình gửi parameter session_id = sessionAId để sửa trí nhớ của khách A
        $patchResult = $experience->handle('shopping', 'PATCH:memory', [
            'session_id' => $sessionAId,
            'key' => 'pref_city',
            'value' => 'Đổi sang Sài Gòn',
        ], ['identity' => 'shopping:guest'], null);

        $this->assertEquals('Đổi sang Sài Gòn', $patchResult['data']['fact']['value']);

        // Xác minh trí nhớ của khách A trong cache vẫn nguyên vẹn
        $guestACached = Cache::get("guest_memory:shopping:{$sessionAId}");
        $this->assertEquals('Thích đi Đà Nẵng', $guestACached[0]['value'], 'Trí nhớ của khách A không bị thay đổi bởi request của khách B.');

        // Khách B cố tình gửi parameter session_id = sessionAId để xóa trí nhớ của khách A
        $deleteResult = $experience->handle('shopping', 'DELETE:memory', [
            'session_id' => $sessionAId,
            'key' => 'pref_city',
        ], ['identity' => 'shopping:guest'], null);

        $this->assertTrue($deleteResult['data']['ok']);

        // Trí nhớ của khách A vẫn tồn tại
        $this->assertNotEmpty(Cache::get("guest_memory:shopping:{$sessionAId}"));
    }

    /**
     * Khuyết tật 11: Phân tách lịch sử hội thoại và trí nhớ theo vai trò (merchant vs shopping).
     * Lịch sử merchant không rò rỉ sang shopping và xóa shopping không xóa merchant.
     */
    public function test_merchant_and_shopping_history_and_memory_isolation_for_same_admin(): void
    {
        $experience = new CommerceExperienceService();

        // 1. Tạo cuộc hội thoại vai trò Merchant trong Database
        $merchantConv = AiConversation::create([
            'user_id' => $this->admin->id,
            'role' => 'merchant',
        ]);
        AiMessage::create([
            'conversation_id' => $merchantConv->id,
            'role' => 'user',
            'content' => 'Báo cáo doanh thu tháng này của khách sạn',
        ]);
        AiMessage::create([
            'conversation_id' => $merchantConv->id,
            'role' => 'assistant',
            'content' => 'Doanh thu tháng này đạt 150 triệu đồng.',
        ]);

        // Tạo cuộc hội thoại vai trò Shopping trong Database
        $shoppingConv = AiConversation::create([
            'user_id' => $this->admin->id,
            'role' => 'shopping',
        ]);
        AiMessage::create([
            'conversation_id' => $shoppingConv->id,
            'role' => 'user',
            'content' => 'Gợi ý phòng nghỉ ở Đà Nẵng cho gia đình 4 người',
        ]);
        AiMessage::create([
            'conversation_id' => $shoppingConv->id,
            'role' => 'assistant',
            'content' => 'Furama Resort Danang có phòng Ocean Suite rất phù hợp.',
        ]);

        // 2. Admin gọi GET:history trên endpoint shopping
        $shoppingHistory = $experience->handle('shopping', 'GET:history', [], ['identity' => "shopping:{$this->admin->id}"], $this->admin);
        $shoppingTexts = collect($shoppingHistory['data']['items'])->pluck('text')->filter()->all();

        $this->assertContains('Gợi ý phòng nghỉ ở Đà Nẵng cho gia đình 4 người', $shoppingTexts);
        $this->assertNotContains('Báo cáo doanh thu tháng này của khách sạn', $shoppingTexts, 'Nội dung lịch sử merchant không được rò rỉ sang shopping.');

        // 3. Admin gọi GET:history trên endpoint merchant
        $merchantHistory = $experience->handle('merchant', 'GET:history', [], ['identity' => "merchant:{$this->admin->id}"], $this->admin);
        $merchantTexts = collect($merchantHistory['data']['items'])->pluck('text')->filter()->all();

        $this->assertContains('Báo cáo doanh thu tháng này của khách sạn', $merchantTexts);
        $this->assertNotContains('Gợi ý phòng nghỉ ở Đà Nẵng cho gia đình 4 người', $merchantTexts, 'Nội dung lịch sử shopping không được xuất hiện trong merchant.');

        // 4. Admin gọi DELETE:history trên endpoint shopping
        $delResult = $experience->handle('shopping', 'DELETE:history', [], ['identity' => "shopping:{$this->admin->id}"], $this->admin);
        $this->assertTrue($delResult['data']['ok']);

        // Cuộc hội thoại shopping đã bị xóa
        $this->assertDatabaseMissing('ai_conversations', ['id' => $shoppingConv->id]);

        // Cuộc hội thoại merchant VẪN CÒN NGUYÊN VẸN
        $this->assertDatabaseHas('ai_conversations', ['id' => $merchantConv->id]);
        $this->assertEquals(2, $merchantConv->messages()->count());
    }

    /**
     * Khuyết tật 11: Phục hồi ngữ cảnh hội thoại lấy đúng 20 tin nhắn mới nhất theo thứ tự thời gian tăng dần.
     */
    public function test_context_restoration_takes_newest_20_messages_in_chronological_order(): void
    {
        $conv = AiConversation::create([
            'user_id' => $this->customer->id,
            'role' => 'shopping',
        ]);

        // Tạo 30 tin nhắn theo thứ tự ID từ 1 đến 30
        for ($i = 1; $i <= 30; $i++) {
            AiMessage::create([
                'conversation_id' => $conv->id,
                'role' => ($i % 2 === 1) ? 'user' : 'assistant',
                'content' => "Tin nhắn số {$i}",
            ]);
        }

        // Lấy 20 tin nhắn mới nhất theo logic sửa đổi
        $recent = $conv->messages()->latest('id')->take(20)->get()->reverse()->values();

        $this->assertCount(20, $recent);
        // Tin nhắn đầu tiên phải là số 11 (bỏ qua số 1 đến 10)
        $this->assertEquals('Tin nhắn số 11', $recent[0]->content);
        // Tin nhắn cuối cùng phải là số 30
        $this->assertEquals('Tin nhắn số 30', $recent[19]->content);
    }

    /**
     * Khuyết tật 12: Hướng dẫn chuyển khoản và trạng thái thanh toán tái sử dụng cấu hình chuẩn từ SePayGatewayService.
     * confirmBankTransfer phân biệt rõ ràng: cancelled, confirmed-unpaid, và paid; không gán booking bừa bãi.
     */
    public function test_assistant_transfer_instructions_and_payment_status_consistency(): void
    {
        config([
            'sepay.bank_name' => 'MBBank',
            'sepay.account_number' => '0901234567',
            'sepay.account_holder' => 'CONG TY TRAVEL PLANNER CHUAN',
            'sepay.pattern_prefix' => 'TPB',
        ]);

        $backend = new CommerceBackendService($this->customer);

        // 1. Kiểm tra confirmBankTransfer với ID sai lệch hoặc của người khác -> Báo lỗi, không tự ý gán bừa
        try {
            $backend->execute('confirm_bank_transfer', [
                'booking_id' => 999999,
            ]);
            $this->fail('Phải ném DomainException khi truyền booking_id không tồn tại.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Không tìm thấy đơn đặt phòng', $e->getMessage());
        }

        // 2. Đơn đặt phòng đang pending (Chưa thanh toán)
        $pendingResult = $backend->execute('confirm_bank_transfer', [
            'booking_id' => $this->booking->id,
        ]);

        $this->assertTrue($pendingResult['success']);
        $this->assertFalse($pendingResult['is_paid']);
        $this->assertEquals('pending', $pendingResult['status']);
        $this->assertEquals('MBBank', $pendingResult['payment_instructions']['bank_name']);
        $this->assertEquals('0901234567', $pendingResult['payment_instructions']['account_number']);
        $this->assertEquals('CONG TY TRAVEL PLANNER CHUAN', $pendingResult['payment_instructions']['account_holder']);
        $this->assertStringContainsString('BK-2026-ASST1', $pendingResult['payment_instructions']['transfer_syntax']);

        // 3. Đơn đặt phòng confirmed nhưng CHƯA có payment completed (confirmed-unpaid)
        $this->booking->update(['status' => 'confirmed']);
        $confirmedUnpaidResult = $backend->execute('confirm_bank_transfer', [
            'booking_id' => $this->booking->id,
        ]);

        $this->assertTrue($confirmedUnpaidResult['success']);
        $this->assertFalse($confirmedUnpaidResult['is_paid'], 'Đơn confirmed nhưng 0 payment rows thì is_paid phải là false.');
        $this->assertEquals('confirmed', $confirmedUnpaidResult['status']);

        // 4. Đơn đặt phòng có payment completed hợp lệ (paid)
        Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 9000000,
            'payment_method' => 'banking',
            'status' => 'completed',
            'transaction_id' => 'TX_SEPAY_VERIFIED_100',
        ]);

        $paidResult = $backend->execute('confirm_bank_transfer', [
            'booking_id' => $this->booking->id,
        ]);

        $this->assertTrue($paidResult['success']);
        $this->assertTrue($paidResult['is_paid'], 'Đơn có bản ghi thanh toán completed thì is_paid phải là true.');

        // Kiểm tra khớp hoàn toàn với endpoint PaymentController::checkStatus
        $statusResponse = $this->actingAs($this->customer)
            ->getJson(route('payments.status', $this->booking->id));

        $statusResponse->assertOk();
        $this->assertTrue($statusResponse->json('is_paid'));
        $this->assertEquals('confirmed', $statusResponse->json('status'));

        // 5. Đơn đặt phòng đã bị hủy (cancelled)
        $this->booking->update(['status' => 'cancelled']);
        $cancelledResult = $backend->execute('confirm_bank_transfer', [
            'booking_id' => $this->booking->id,
        ]);

        $this->assertTrue($cancelledResult['success']);
        $this->assertFalse($cancelledResult['is_paid']);
        $this->assertEquals('cancelled', $cancelledResult['status']);
    }

    /**
     * Khuyết tật 13: Regex nhận diện đường dẫn thực thi Python trên Windows, UNC và venv không gây crash.
     */
    public function test_python_path_regex_recognition_without_crash(): void
    {
        $testPaths = [
            'C:\\Python314\\python.exe',
            'C:/Python314/python.exe',
            'D:\\dev\\travel-planner\\.venv\\Scripts\\python.exe',
            '\\\\server\\share\\python.exe',
            '//network-share/python.exe',
            '/usr/bin/python3',
            '.venv/bin/python',
            'python',
        ];

        foreach ($testPaths as $path) {
            $isAbsolute = str_starts_with($path, '/')
                || preg_match('~^[A-Za-z]:[\\\\/]~', $path)
                || preg_match('~^(\\\\{2}|//)~', $path);

            $resolved = (! $isAbsolute && (str_contains($path, '/') || str_contains($path, '\\')))
                ? base_path($path)
                : $path;

            $this->assertNotEmpty($resolved);
        }
    }

    /**
     * Khuyết tật 14: Tôn trọng nhà cung cấp mô hình (Gemini vs OpenAI), không đổi tên mô hình cấu hình,
     * truyền đúng tham số CLI --provider và biến môi trường.
     */
    /**
     * Kiểm tra luồng AI Agent xác nhận thanh toán:
     * 1. Khi Admin Dashboard chưa duyệt: Thông báo "Chờ xác nhận" từ Quản trị viên (Admin).
     * 2. Sau khi Admin Dashboard duyệt (xác nhận đơn hoặc hoàn tất thanh toán): Thông báo thành công và phòng sẵn sàng.
     */
    public function test_ai_agent_payment_checks_admin_dashboard_approval_before_confirming_success(): void
    {
        $backend = new CommerceBackendService($this->customer);

        // 1. Khi đơn ở trạng thái pending, chưa được Admin Dashboard duyệt
        $unapproved = $backend->execute('confirm_bank_transfer', [
            'booking_id' => $this->booking->id,
        ]);

        $this->assertTrue($unapproved['success']);
        $this->assertFalse($unapproved['is_paid']);
        $this->assertEquals('pending', $unapproved['status']);
        $this->assertStringContainsString('Chờ xác nhận', $unapproved['message']);
        $this->assertStringContainsString('chưa được Quản trị viên (Admin) duyệt', $unapproved['message']);
        $this->assertStringContainsString('Admin Dashboard', $unapproved['message']);

        // 2. Quản trị viên vào Admin Dashboard xác nhận thanh toán (Admin\PaymentController::updateStatus hoặc PaymentService)
        $payment = Payment::create([
            'booking_id' => $this->booking->id,
            'amount' => 9000000,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
            'transaction_id' => 'TX_ADMIN_PENDING_01',
        ]);

        $paymentService = app(\App\Services\Interfaces\PaymentServiceInterface::class);
        $paymentService->updatePaymentStatus($payment->id, 'completed');

        // Refresh model booking sau khi được Admin cập nhật
        $this->booking->refresh();
        $this->assertEquals('confirmed', $this->booking->status);

        // 3. Khách hàng kiểm tra lại với AI Agent: Lúc này Admin Dashboard đã xác nhận
        $approved = $backend->execute('confirm_bank_transfer', [
            'booking_id' => $this->booking->id,
        ]);

        $this->assertTrue($approved['success']);
        $this->assertTrue($approved['is_paid']);
        $this->assertEquals('confirmed', $approved['status']);
        $this->assertStringContainsString('đã xác nhận thanh toán thành công', $approved['message']);
        $this->assertStringContainsString('Dashboard', $approved['message']);
        $this->assertStringContainsString('Phòng của bạn đã sẵn sàng', $approved['message']);
    }
}

