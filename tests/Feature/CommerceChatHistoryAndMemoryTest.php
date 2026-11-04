<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use App\Services\CommerceExperienceService;
use App\Services\Interfaces\AiTravelAssistantInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceChatHistoryAndMemoryTest extends TestCase
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

    public function test_conversation_history_is_saved_to_database_and_retrieved_via_api(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

        // Mock assistant reply to simulate two turns
        $assistant = $this->mock(AiTravelAssistantInterface::class);
        $assistant->shouldReceive('reply')
            ->twice()
            ->andReturn(
                [
                    'answer' => 'Chào bạn! Mình có thể giúp gì cho chuyến đi của bạn?',
                    'presentations' => [],
                    'pending_changes' => [],
                    'conversation' => [
                        'identity' => 'shopping:'.$user->id,
                        'messages' => [],
                        'state' => [],
                        'backend' => [],
                        'memory' => [
                            [
                                'key' => 'fav_city_hanoi',
                                'value' => 'Điểm đến quan tâm: Hà Nội',
                                'category' => 'preference',
                            ],
                        ],
                    ],
                ],
                [
                    'answer' => 'Travel Planner gợi ý khách sạn phố cổ và lịch trình 3 ngày 2 đêm tại Hà Nội.',
                    'presentations' => [],
                    'pending_changes' => [],
                    'conversation' => [
                        'identity' => 'shopping:'.$user->id,
                        'messages' => [],
                        'state' => [],
                        'backend' => [],
                        'memory' => [
                            [
                                'key' => 'fav_city_hanoi',
                                'value' => 'Điểm đến quan tâm: Hà Nội',
                                'category' => 'preference',
                            ],
                            [
                                'key' => 'trip_length',
                                'value' => 'Thời gian chuyến đi: 3 ngày 2 đêm',
                                'category' => 'preference',
                            ],
                        ],
                    ],
                ]
            );

        $assistant->shouldReceive('surface')
            ->andReturnUsing(function ($role, $action, $parameters, $conversation, $user) {
                return (new CommerceExperienceService)->handle($role, $action, $parameters, $conversation, $user);
            });

        // Turn 1
        $response1 = $this->actingAs($user)->postJson('/commerce-api/shopping/chat', [
            'message' => 'Hà Nội',
        ]);
        $response1->assertOk();
        $content1 = $response1->streamedContent();
        $this->assertStringContainsString('turn_complete', $content1);

        // Kiểm tra database lưu tin nhắn lượt 1
        $conv = AiConversation::where('user_id', $user->id)->first();
        $this->assertNotNull($conv);
        $this->assertDatabaseHas('ai_messages', [
            'conversation_id' => $conv->id,
            'role' => 'user',
            'content' => 'Hà Nội',
        ]);
        $this->assertDatabaseHas('ai_messages', [
            'conversation_id' => $conv->id,
            'role' => 'assistant',
            'content' => 'Chào bạn! Mình có thể giúp gì cho chuyến đi của bạn?',
        ]);

        // Turn 2
        $response2 = $this->actingAs($user)->postJson('/commerce-api/shopping/chat', [
            'message' => 'Đi 3 ngày 2 đêm tại Hà Nội',
        ]);
        $response2->assertOk();
        $content2 = $response2->streamedContent();
        $this->assertStringContainsString('turn_complete', $content2);

        // Kiểm tra database có đủ 4 tin nhắn
        $this->assertEquals(4, $conv->messages()->count());

        // Kiểm tra API GET /commerce-api/shopping/history trả về đủ 4 items
        $historyRes = $this->actingAs($user)->getJson('/commerce-api/shopping/history');
        $historyRes->assertOk();
        $items = $historyRes->json('items');
        $this->assertCount(4, $items);
        $this->assertEquals('user', $items[0]['kind']);
        $this->assertEquals('Hà Nội', $items[0]['text']);
        $this->assertEquals('assistant', $items[1]['kind']);
        $this->assertEquals('user', $items[2]['kind']);
        $this->assertEquals('Đi 3 ngày 2 đêm tại Hà Nội', $items[2]['text']);
        $this->assertEquals('assistant', $items[3]['kind']);

        // Kiểm tra API DELETE /commerce-api/shopping/history xóa sạch lịch sử
        $deleteRes = $this->actingAs($user)->deleteJson('/commerce-api/shopping/history');
        $deleteRes->assertOk();
        $this->assertEquals(0, AiMessage::count());
        $this->assertEquals(0, AiConversation::count());

        // Kiểm tra GET lại history sau khi xóa là rỗng
        $afterDelete = $this->actingAs($user)->getJson('/commerce-api/shopping/history');
        $afterDelete->assertOk();
        $this->assertCount(0, $afterDelete->json('items'));
    }

    public function test_clear_chat_history_for_guest(): void
    {
        $assistant = $this->mock(AiTravelAssistantInterface::class);
        $assistant->shouldReceive('surface')
            ->andReturnUsing(function ($role, $action, $parameters, $conversation, $user) {
                return (new CommerceExperienceService)->handle($role, $action, $parameters, $conversation, $user);
            });

        session(['commerce_chat_history' => [
            ['kind' => 'user', 'text' => 'Hello'],
            ['kind' => 'assistant', 'turn' => 1, 'segments' => [['type' => 'text', 'text' => 'Hi']], 'suggestions' => [], 'pending' => false, 'tools' => []],
        ]]);

        $getRes = $this->getJson('/commerce-api/shopping/history');
        $getRes->assertOk();
        $this->assertCount(2, $getRes->json('items'));

        $delRes = $this->deleteJson('/commerce-api/shopping/history');
        $delRes->assertOk();

        $getRes2 = $this->getJson('/commerce-api/shopping/history');
        $getRes2->assertOk();
        $this->assertCount(0, $getRes2->json('items'));
    }

    public function test_when_both_ai_providers_fail_it_reports_server_busy_message(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $origOpenAi = config('commerce.openai_api_key');
        $origGemini = config('commerce.gemini_api_key');
        try {
            config([
                'commerce.openai_api_key' => 'sk-invalid-fake-key-triggering-failure',
                'commerce.gemini_api_key' => 'invalid-fake-key-triggering-failure',
            ]);

            $response = $this->actingAs($user)->postJson('/commerce-api/shopping/chat', [
                'message' => 'Xin chào Travel Planner',
            ]);

            $response->assertOk();
            $content = $response->streamedContent();
            $this->assertStringContainsString('Máy chủ Travel Planner hiện đang bận, xin vui lòng thử lại sau!', $content);
        } finally {
            config([
                'commerce.openai_api_key' => $origOpenAi,
                'commerce.gemini_api_key' => $origGemini,
            ]);
        }
    }

    public function test_guest_memory_and_history_persistence(): void
    {
        $assistant = $this->mock(AiTravelAssistantInterface::class);
        $assistant->shouldReceive('reply')->once()->andReturn([
            'answer' => 'Chào bạn, đây là các bãi biển đẹp tại Đà Nẵng.',
            'presentations' => [],
            'pending_changes' => [],
            'conversation' => [
                'identity' => 'shopping:guest',
                'messages' => [],
                'state' => [],
                'backend' => [],
                'memory' => [
                    [
                        'key' => 'fav_city_da_nang',
                        'value' => 'Điểm đến quan tâm: Đà Nẵng',
                        'category' => 'preference',
                    ],
                    [
                        'key' => 'party_kids',
                        'value' => 'Thành viên chuyến đi: Có trẻ nhỏ / gia đình',
                        'category' => 'constraint',
                    ],
                    [
                        'key' => 'beach_preference',
                        'value' => 'Sở thích lưu trú: Thích nghỉ ngơi gần bãi biển',
                        'category' => 'preference',
                    ],
                ],
            ],
        ]);

        $assistant->shouldReceive('surface')
            ->andReturnUsing(function ($role, $action, $parameters, $conversation, $user) {
                return (new CommerceExperienceService)->handle($role, $action, $parameters, $conversation, $user);
            });

        // Khách vãng lai chat
        $res = $this->postJson('/commerce-api/shopping/chat', [
            'message' => 'Du lịch Đà Nẵng có trẻ nhỏ muốn gần bãi biển',
        ]);
        $res->assertOk();
        $res->streamedContent();

        // Kiểm tra GET /commerce-api/shopping/memory trả về đúng 3 facts đã lưu
        $memRes = $this->getJson('/commerce-api/shopping/memory');
        $memRes->assertOk();
        $facts = $memRes->json('facts');
        $this->assertNotEmpty($facts);
        $this->assertCount(3, $facts);
        $this->assertContains('Điểm đến quan tâm: Đà Nẵng', array_column($facts, 'value'));
        $this->assertContains('Thành viên chuyến đi: Có trẻ nhỏ / gia đình', array_column($facts, 'value'));
        $this->assertContains('Sở thích lưu trú: Thích nghỉ ngơi gần bãi biển', array_column($facts, 'value'));

        // Kiểm tra GET /commerce-api/shopping/history trả về lịch sử khách vãng lai
        $histRes = $this->getJson('/commerce-api/shopping/history');
        $histRes->assertOk();
        $this->assertCount(2, $histRes->json('items'));
    }

    public function test_python_facts_extractor_accurately_extracts_all_required_facets(): void
    {
        $cmd = [
            config('commerce.python'),
            '-c',
            'import sys; sys.path.insert(0, "plugins/commerce-agents/travel_planner"); from runner import extract_all_memory_facts; '
            . 'facts = extract_all_memory_facts("Đi 3 ngày 2 đêm, có trẻ nhỏ, ngân sách 5 triệu, muốn gần bãi biển", "Phòng tại Đà Nẵng", "Đà Nẵng", "test_sess"); '
            . 'print({f.key: f.value for f in facts})'
        ];

        $process = new \Symfony\Component\Process\Process($cmd, base_path());
        $process->run();

        $this->assertTrue($process->isSuccessful(), 'Runner facts extractor execution failed: ' . $process->getErrorOutput());
        $output = $process->getOutput();

        $this->assertStringContainsString('fav_city_', $output);
        $this->assertStringContainsString('budget_limit', $output);
        $this->assertStringContainsString('trip_length', $output);
        $this->assertStringContainsString('party_kids', $output);
        $this->assertStringContainsString('beach_preference', $output);
    }

    public function test_real_service_chat_and_memory_extraction_without_cache_error(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

        // Gửi prompt Thành phố Hồ Chí Minh (giống ảnh 1)
        $res = $this->actingAs($user)->postJson('/commerce-api/shopping/chat', [
            'message' => 'Thành phố Hồ Chí Minh',
        ]);
        $res->assertOk();
        $content = $res->streamedContent();

        // Không bao giờ được chứa lỗi file_put_contents hoặc No such file or directory
        $this->assertStringNotContainsString('file_put_contents', $content);
        $this->assertStringNotContainsString('No such file or directory', $content);
        $this->assertStringContainsString('turn_complete', $content);

        // Kiểm tra memory sau lượt chat
        $memRes = $this->actingAs($user)->getJson('/commerce-api/shopping/memory');
        $memRes->assertOk();
        $facts = $memRes->json('facts');
        $this->assertNotEmpty($facts, 'Trí nhớ AI không được để trống sau khi người dùng chat');

        $this->assertContains('Điểm đến quan tâm: Thành phố Hồ Chí Minh', array_column($facts, 'value'));
    }
}
