<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use App\Models\User;
use App\Services\CommerceBackendService;
use App\Services\Interfaces\AiTravelAssistantInterface;
use App\Services\Interfaces\BookingServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Kiểm thử toàn diện tất cả các tính năng tích hợp của Commerce Agent trên Travel Planner.
 */
class CommerceIntegrationAllTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /**
     * 1. Kiểm tra trang chủ chứa Side Panel chuẩn OP.GG, thanh kéo resizable và nút launcher trắng
     */
    public function test_home_page_includes_opgg_style_side_panel_and_launcher(): void
    {
        $response = $this->get('/');
        $response->assertOk();

        // Kiểm tra tồn tại Side Panel và thanh kéo chuột
        $response->assertSee('id="commerceSidePanel"', false);
        $response->assertSee('id="commerceResizeHandle"', false);
        $response->assertSee('id="commercePointerShield"', false);
        $response->assertSee('class="commerce-floating-launcher"', false);

        // Header Agent chỉ có AGENT và BETA, không có chữ Travel bên cạnh
        $response->assertSee('commerce-agent-wordmark', false);
        $response->assertSee('commerce-beta-badge', false);
        $response->assertDontSee('commerce-vertical-pill', false);

        // Font chữ Times New Roman
        $response->assertSee('Times New Roman', false);

        // Đảm bảo chatbot cũ đã bị gỡ hoàn toàn khỏi giao diện
        $response->assertDontSee('chat-widget.js', false);
        $response->assertDontSee('chat-widget.css', false);
    }

    /**
     * 2. Kiểm tra giao diện Shopping Agent nhúng trong Iframe
     */
    public function test_shopping_agent_view_renders_cleanly_with_times_new_roman(): void
    {
        // Khách chưa đăng nhập có thể truy cập để tìm kiếm
        $this->get(route('commerce.shopping'))->assertOk();

        // Đăng nhập thành công truy cập được
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('commerce.shopping'));
        $response->assertOk();

        // Kiểm tra thẻ base target="_top" ngăn lồng trang
        $response->assertSee('<base target="_top">', false);
        $response->assertSee('id="commerce-root"', false);
        $response->assertSee('Times New Roman', false);
    }

    /**
     * 3. Kiểm tra bảo mật trang Merchant Agent
     */
    public function test_merchant_agent_view_requires_active_admin(): void
    {
        // Khách vãng lai bị chuyển hướng đăng nhập
        $this->get(route('commerce.merchant'))->assertRedirect(route('login'));

        // Thành viên thường không có quyền admin bị chặn 403
        $regularUser = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->actingAs($regularUser)->get(route('commerce.merchant'))->assertForbidden();

        // Admin đang bị khóa bị middleware đăng xuất và chuyển hướng về /login
        $lockedAdmin = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $this->actingAs($lockedAdmin)->get(route('commerce.merchant'))->assertRedirect(route('login'));

        // Admin hợp lệ truy cập thành công 200 OK
        $activeAdmin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($activeAdmin)->get(route('commerce.merchant'))->assertOk();
    }

    /**
     * 4. Kiểm tra các API Shopping: Session, Catalog, Cart
     */
    public function test_shopping_api_session_products_and_cart(): void
    {
        // Khách chưa đăng nhập có thể lấy session khách, xem danh mục và giỏ hàng
        $guestSession = $this->postJson('/commerce-api/shopping/session')->assertOk();
        $guestSession->assertJsonPath('name', 'Khách');

        $user = User::factory()->create();

        // Tạo dữ liệu mẫu điểm đến
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $category = Category::factory()->create(['name' => 'Biển']);
        Destination::factory()->create([
            'city_id' => $city->id,
            'category_id' => $category->id,
            'name' => 'Bà Nà Hills',
            'entrance_fee' => 900000,
        ]);

        // API Session người dùng
        $sessionResponse = $this->actingAs($user)->postJson('/commerce-api/shopping/session');
        $sessionResponse->assertOk()
            ->assertJsonStructure(['session_id', 'name']);

        // API Products / Catalog
        $productsResponse = $this->actingAs($user)->getJson('/commerce-api/shopping/products');
        $productsResponse->assertOk()
            ->assertJsonStructure(['products']);

        // API Cart
        $cartResponse = $this->actingAs($user)->getJson('/commerce-api/shopping/cart');
        $cartResponse->assertOk()
            ->assertJsonPath('items', [])
            ->assertJsonPath('subtotal', 0);
    }

    /**
     * 5. Kiểm tra cơ chế chống lồng Iframe trên các trang chính
     */
    public function test_frame_busting_guards_on_main_pages(): void
    {
        $guardScript = 'window.top.location.href = window.location.href';

        // Trang đăng nhập
        $this->get(route('login'))->assertSee($guardScript, false);

        // Trang điểm đến
        $this->get(route('destinations.index'))->assertSee($guardScript, false);

        // Trang quản trị
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)->get('/admin')->assertSee($guardScript, false);
    }

    /**
     * 6. Kiểm tra endpoint chatbot cũ đã bị gỡ hoàn toàn (404)
     */
    public function test_old_chat_endpoint_is_removed(): void
    {
        $this->postJson('/chat', ['message' => 'Xin chào'])->assertNotFound();
    }

    /**
     * 7. Kiểm tra kết nối Service Container chuẩn Interface
     */
    public function test_service_container_binding(): void
    {
        $assistant = app(AiTravelAssistantInterface::class);
        $this->assertInstanceOf(\App\Services\AiTravelAssistantService::class, $assistant);
    }

    /**
     * 8. Kiểm tra bảng và cột dữ liệu Commerce trong Database
     */
    public function test_commerce_database_capabilities(): void
    {
        $this->assertTrue(Schema::hasTable('commerce_campaigns'));
        $this->assertTrue(Schema::hasTable('commerce_promotions'));
        $this->assertTrue(Schema::hasColumn('destinations', 'commerce_paused'));
        $this->assertTrue(Schema::hasColumn('room_types', 'commerce_paused'));
    }

    /**
     * 9. Kiểm tra quy trình khuyến mãi phòng thay đổi giá đặt phòng
     */
    public function test_promotion_pricing_flow(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $destination = Destination::factory()->create();
        $room = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'price_per_night' => 1000000,
        ]);

        $backend = new CommerceBackendService($admin);
        $change = $backend->execute('stage_promotion', [
            'promotion' => [
                'name' => 'Khuyến mãi hè',
                'listing_ids' => ['room:' . $room->id],
                'discount_pct' => 20,
                'starts' => '2026-12-01',
                'ends' => '2026-12-05',
            ],
        ]);

        $bookingService = app(BookingServiceInterface::class);

        // Giá gốc trước khi duyệt: 2 đêm = 2,000,000
        $this->assertEquals(2000000, $bookingService->calculateBookingTotal($room->id, '2026-12-01', '2026-12-03', 1));

        // Duyệt đề xuất khuyến mại
        $backend->approveChange($change['change_id']);

        // Giá sau khi giảm 20%: 2 đêm = 1,600,000
        $this->assertEquals(1600000, $bookingService->calculateBookingTotal($room->id, '2026-12-01', '2026-12-03', 1));
    }

    /**
     * 10. Kiểm tra quy trình tạm dừng phòng chặn đặt phòng
     */
    public function test_pause_room_blocks_booking(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $destination = Destination::factory()->create();
        $room = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'quantity' => 2,
        ]);

        $backend = new CommerceBackendService($admin);
        $change = $backend->execute('stage_inventory_action', [
            'items' => [['listing_id' => 'room:' . $room->id, 'action' => 'pause']],
        ]);

        $bookingService = app(BookingServiceInterface::class);

        // Trước duyệt: phòng còn trống
        $this->assertTrue($bookingService->checkRoomAvailability($room->id, '2026-12-01', '2026-12-02', 1));

        // Duyệt đề xuất tạm dừng phòng
        $backend->approveChange($change['change_id']);

        // Sau duyệt: phòng bị khóa không cho đặt
        $this->assertFalse($bookingService->checkRoomAvailability($room->id, '2026-12-01', '2026-12-02', 1));
    }

    /**
     * 11. Kiểm tra lệnh lập chỉ mục Solr
     */
    public function test_solr_index_command_executes_cleanly(): void
    {
        Destination::factory()->create(['name' => 'Điểm đến test']);
        Http::fake(['*' => Http::response([], 200)]);
        $this->artisan('commerce:index')->assertSuccessful();
    }
}
