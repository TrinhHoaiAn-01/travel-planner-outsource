<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Destination;
use App\Models\RoomType;
use App\Models\User;
use App\Services\CommerceBackendService;
use App\Services\Interfaces\AiTravelAssistantInterface;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CommerceAgentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);
    }

    public function test_guest_can_access_shopping_and_chat_for_search_but_restricted_from_user_actions(): void
    {
        // Khách có thể truy cập giao diện Shopping Agent để tìm kiếm thông tin
        $this->get('/travel-assistant')->assertOk();

        // Khách có thể chat để tìm kiếm
        $assistant = $this->mock(AiTravelAssistantInterface::class);
        $assistant->shouldReceive('reply')->once()->andReturnUsing(function ($message, $conversation, $passedUser, $session, $role, $emit) {
            $this->assertSame('shopping', $role);
            $this->assertNull($passedUser);
            $emit(['type' => 'text_delta', 'data' => ['text' => 'Chào bạn! Tôi có thể giúp bạn tìm kiếm thông tin điểm đến và khách sạn.']]);
            return ['conversation' => ['identity' => 'shopping:guest', 'messages' => []]];
        });

        $response = $this->postJson('/commerce-api/shopping/chat', ['message' => 'Xin chào'])->assertOk();
        $this->assertStringContainsString('text_delta', $response->streamedContent());

        // Khách bị hạn chế: Không thể đặt phòng hoặc thực hiện các tính năng tự động hóa
        $backend = new CommerceBackendService(null);

        try {
            $backend->execute('book_hotel_room', ['destination_name' => 'Bà Nà Hills']);
            $this->fail('Guest should not be able to book hotel room.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Vui lòng đăng nhập', $e->getMessage());
        }

        try {
            $backend->execute('add_favorite', ['destination_name' => 'Bà Nà Hills']);
            $this->fail('Guest should not be able to add favorite.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Vui lòng đăng nhập', $e->getMessage());
        }

        try {
            $backend->execute('add_destination_to_trip', ['destination_name' => 'Bà Nà Hills']);
            $this->fail('Guest should not be able to add to trip.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Vui lòng đăng nhập', $e->getMessage());
        }

        try {
            $backend->execute('confirm_bank_transfer', ['booking_code' => 'BK-123']);
            $this->fail('Guest should not be able to confirm bank transfer.');
        } catch (DomainException $e) {
            $this->assertStringContainsString('Vui lòng đăng nhập', $e->getMessage());
        }
    }

    public function test_framework_stream_saves_private_state_but_never_returns_it_to_browser(): void
    {
        $user = User::factory()->create();
        $assistant = $this->mock(AiTravelAssistantInterface::class);
        $assistant->shouldReceive('reply')->once()->andReturnUsing(function ($message, $conversation, $passedUser, $session, $role, $emit) use ($user) {
            $this->assertSame($user->id, $passedUser->id);
            $emit(['type' => 'text_delta', 'data' => ['text' => 'Dữ liệu thật']]);
            return ['conversation' => ['identity' => 'shopping:'.$user->id, 'messages' => ['private tool data']]];
        });
        $response = $this->actingAs($user)->postJson('/commerce-api/shopping/chat', ['message' => 'Xin chào', 'user_id' => $user->id, 'role' => 'merchant'])->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('text_delta', $content);
        $this->assertStringNotContainsString('private tool data', $content);
        $response->assertSessionHas('commerce_shopping.messages', ['private tool data']);
    }

    public function test_guest_cannot_read_admin_metrics(): void
    {
        $this->getJson('/commerce-api/merchant/overview')->assertForbidden();
    }

    public function test_account_context_is_scoped_to_signed_in_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $context = (new CommerceBackendService($user))->execute('get_account_context', []);
        $this->assertSame($user->id, $context['user_id']);
    }

    public function test_search_uses_solr_and_hydrates_current_database_records(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $destination = Destination::factory()->create(['city_id' => $city->id, 'name' => 'Bà Nà Hills', 'price' => 500000]);

        Http::fake([
            config('commerce.solr_url').'*' => Http::response([
                'response' => ['docs' => [['id' => 'dest:'.$destination->id]]],
            ], 200),
        ]);

        $results = (new CommerceBackendService(null))->execute('search_products', ['query' => 'Bà Nà']);
        $this->assertCount(1, $results);
        $this->assertSame('Bà Nà Hills', $results[0]['title']);
    }

    public function test_solr_failure_has_no_database_keyword_search_fallback(): void
    {
        Http::fake([config('commerce.solr_url').'*' => Http::response([], 500)]);
        $results = (new CommerceBackendService(null))->execute('search_products', ['query' => 'fail']);
        $this->assertSame([], $results);
    }

    public function test_merchant_write_is_staged_and_chat_cannot_approve_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $city = City::factory()->create();
        $destination = Destination::factory()->create(['city_id' => $city->id, 'price' => 100000]);

        $backend = new CommerceBackendService($admin);
        $proposal = $backend->execute('propose_price_update', [
            'listing_id' => 'dest:'.$destination->id,
            'new_price' => 120000,
            'reason' => 'Điều chỉnh mùa cao điểm',
        ]);

        $this->assertSame('pending', $proposal['status']);
        $destination->refresh();
        $this->assertEquals(100000, (float) $destination->price);
    }

    public function test_stale_proposal_does_not_overwrite_another_members_edit(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $city = City::factory()->create();
        $destination = Destination::factory()->create(['city_id' => $city->id, 'price' => 100000]);

        $backend = new CommerceBackendService($admin);
        $proposal = $backend->execute('propose_price_update', [
            'listing_id' => 'dest:'.$destination->id,
            'new_price' => 120000,
            'reason' => 'Đổi giá đợt 1',
        ]);

        $destination->update(['price' => 110000]);

        $this->expectException(DomainException::class);
        $backend->approveChange($proposal['change_id']);
    }

    public function test_approval_endpoint_requires_admin_and_owned_server_session(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->actingAs($user)->postJson('/commerce-api/merchant/changes/1/apply')->assertForbidden();
    }

    public function test_locked_user_cannot_start_a_chat(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $this->actingAs($user)->postJson('/commerce-api/shopping/chat', ['message' => 'hi'])->assertForbidden();
    }

    public function test_index_command_updates_solr_with_database_documents(): void
    {
        $city = City::factory()->create(['name' => 'Đà Nẵng']);
        $destination = Destination::factory()->create(['city_id' => $city->id, 'name' => 'Chùa Linh Ứng']);

        Http::fake([
            config('commerce.solr_url').'*' => Http::response(['responseHeader' => ['status' => 0]], 200),
        ]);

        $this->artisan('commerce:index-destinations')->assertSuccessful();
        Http::assertSent(fn ($request) => str_contains($request->url(), '/update'));
    }

    public function test_failed_agent_turn_does_not_replace_session_history(): void
    {
        $assistant = $this->mock(AiTravelAssistantInterface::class);
        $assistant->shouldReceive('reply')->andThrow(new \RuntimeException('Connection failed'));

        $this->withSession(['commerce_shopping' => ['messages' => ['original message']]])
            ->postJson('/commerce-api/shopping/chat', ['message' => 'hello'])
            ->assertOk();

        $this->assertSame(['original message'], session('commerce_shopping.messages'));
    }

    public function test_chat_allows_agent_timeout_to_finish_before_php_execution_limit(): void
    {
        $this->assertGreaterThan(config('commerce.timeout'), 200);
    }

    public function test_agent_timeout_returns_readable_stream_error_without_process_details(): void
    {
        $assistant = $this->mock(AiTravelAssistantInterface::class);
        $assistant->shouldReceive('reply')->andThrow(new \Symfony\Component\Process\Exception\ProcessTimedOutException(
            new \Symfony\Component\Process\Process(['python', '/private/runner.py']),
            1
        ));

        $response = $this->postJson('/commerce-api/shopping/chat', ['message' => 'test timeout'])->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Máy chủ AI phản hồi quá lâu', $content);
        $this->assertStringNotContainsString('/private/runner.py', $content);
    }

    public function test_price_update_above_cap_is_refused_before_any_write(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $city = City::factory()->create();
        $destination = Destination::factory()->create(['city_id' => $city->id, 'price' => 100000]);

        $backend = new CommerceBackendService($admin);

        $this->expectException(DomainException::class);
        $backend->execute('propose_price_update', [
            'listing_id' => 'dest:'.$destination->id,
            'new_price' => 250000,
            'reason' => 'Tăng vượt quá 30%',
        ]);
    }

    public function test_native_api_uses_authenticated_identity_and_rejects_merchant_impersonation(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $this->actingAs($user)
            ->getJson('/commerce-api/merchant/overview')
            ->assertForbidden();
    }

    public function test_empty_cart_and_catalog_match_native_frontend_contracts(): void
    {
        $this->getJson('/commerce-api/shopping/cart')->assertOk()->assertJsonStructure([
            'items', 'item_count', 'subtotal',
        ]);
    }

    public function test_memory_corrections_and_deletion_keep_other_facts(): void
    {
        $this->withSession([
            'commerce_shopping' => [
                'memory' => [
                    ['key' => 'fav_color', 'value' => 'blue', 'category' => 'preference'],
                    ['key' => 'home_city', 'value' => 'Hà Nội', 'category' => 'preference'],
                ],
            ],
        ]);

        $response = $this->deleteJson('/commerce-api/shopping/memory', ['key' => 'fav_color'])->assertOk();
        $response->assertJson(['ok' => true]);

        $remaining = session('commerce_shopping.memory');
        $this->assertCount(1, $remaining);
        $this->assertSame('home_city', $remaining[0]['key']);
    }

    public function test_campaign_is_saved_only_after_approval(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $backend = new CommerceBackendService($admin);

        $proposal = $backend->execute('propose_campaign', [
            'name' => 'Mùa Hè Rực Rỡ',
            'budget' => 50000000,
            'channels' => ['facebook', 'google'],
            'starts' => '2026-06-01',
            'ends' => '2026-06-30',
        ]);

        $this->assertDatabaseMissing('commerce_campaigns', ['name' => 'Mùa Hè Rực Rỡ']);

        $backend->approveChange($proposal['change_id']);
        $this->assertDatabaseHas('commerce_campaigns', ['name' => 'Mùa Hè Rực Rỡ']);
    }

    public function test_approved_promotion_changes_real_booking_price_only_for_its_window(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $city = City::factory()->create();
        $destination = Destination::factory()->create(['city_id' => $city->id]);
        $room = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'price_per_night' => 1000000,
        ]);

        $backend = new CommerceBackendService($admin);
        $proposal = $backend->execute('propose_promotion', [
            'name' => 'Giảm giá cuối năm',
            'discount_pct' => 20,
            'listing_ids' => ['room:'.$room->id],
            'starts' => '2026-12-01',
            'ends' => '2026-12-10',
        ]);

        $backend->approveChange($proposal['change_id']);

        $pricingService = new \App\Services\CommercePricingService;
        $inWindowPrice = $pricingService->calculateRoomPrice($room, '2026-12-02', '2026-12-05');
        $outWindowPrice = $pricingService->calculateRoomPrice($room, '2026-12-15', '2026-12-18');

        $this->assertEquals(800000 * 3, $inWindowPrice);
        $this->assertEquals(1000000 * 3, $outWindowPrice);
    }

    public function test_pausing_room_blocks_booking_after_approval(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $city = City::factory()->create();
        $destination = Destination::factory()->create(['city_id' => $city->id]);
        $room = RoomType::factory()->create(['destination_id' => $destination->id, 'quantity' => 5]);

        $backend = new CommerceBackendService($admin);
        $proposal = $backend->execute('propose_pause_listing', [
            'listing_id' => 'room:'.$room->id,
            'reason' => 'Bảo trì sửa chữa',
        ]);

        $backend->approveChange($proposal['change_id']);
        $room->refresh();
        $this->assertTrue($room->commerce_paused);

        $customer = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $customerBackend = new CommerceBackendService($customer);

        $this->expectException(DomainException::class);
        $customerBackend->execute('book_hotel_room', [
            'room_id' => $room->id,
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-12',
        ]);
    }

    public function test_add_favorite_action(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $city = City::factory()->create();
        $destination = Destination::factory()->create(['city_id' => $city->id, 'name' => 'Bà Nà Hills']);

        $backend = new CommerceBackendService($user);
        $result = $backend->execute('add_favorite', ['destination_name' => 'Bà Nà Hills']);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'destination_id' => $destination->id,
        ]);
    }

    public function test_add_destination_to_trip_action(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $city = City::factory()->create();
        $destination = Destination::factory()->create(['city_id' => $city->id, 'name' => 'Bà Nà Hills']);

        $backend = new CommerceBackendService($user);
        $result = $backend->execute('add_destination_to_trip', ['destination_name' => 'Bà Nà Hills']);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('trips', ['user_id' => $user->id]);
        $this->assertDatabaseHas('itinerary_items', ['destination_id' => $destination->id]);
    }

    public function test_book_hotel_room_and_confirm_bank_transfer_action(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $city = City::factory()->create();
        $destination = Destination::factory()->create(['city_id' => $city->id, 'name' => 'Khách sạn Novotel']);
        $room = RoomType::factory()->create([
            'destination_id' => $destination->id,
            'name' => 'Deluxe Ocean View',
            'price_per_night' => 1500000,
            'quantity' => 5,
        ]);

        $backend = new CommerceBackendService($user);

        // 1. Đặt phòng
        $bookingResult = $backend->execute('book_hotel_room', [
            'hotel_name' => 'Khách sạn Novotel',
            'check_in_date' => '2026-11-10',
            'check_out_date' => '2026-11-13',
            'nights' => 3,
        ]);

        $this->assertTrue($bookingResult['success']);
        $this->assertSame('pending', $bookingResult['status']);
        $this->assertSame(4500000.0, (float) $bookingResult['total_price']);
        $this->assertSame('Khách sạn Novotel', $bookingResult['hotel_name']);
        $this->assertNotEmpty($bookingResult['booking_code']);

        // 2. Xác nhận thanh toán chuyển khoản
        $confirmResult = $backend->execute('confirm_bank_transfer', [
            'booking_code' => $bookingResult['booking_code'],
        ]);

        $this->assertTrue($confirmResult['success']);
        $this->assertArrayHasKey('payment_instructions', $confirmResult);
    }
}
