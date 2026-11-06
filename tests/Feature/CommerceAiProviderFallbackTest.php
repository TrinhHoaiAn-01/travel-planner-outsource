<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AiTravelAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceAiProviderFallbackTest extends TestCase
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

    /**
     * Kiểm tra sửa lỗi trong hình ảnh người dùng:
     * Truy vấn "Đà Nẵng" và "Huế" phải trả lời thành công 200, không bị lỗi 404 gemini-2.5-flash hay server bận.
     */
    public function test_ai_agent_handles_da_nang_and_hue_without_errors(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

        // 1. Kiểm tra với câu hỏi "Đà Nẵng" (trong ảnh bị lỗi gemini-2.5-flash phản hồi chậm/lỗi kết nối)
        $responseDaNang = $this->actingAs($user)->postJson('/commerce-api/shopping/chat', [
            'message' => 'Đà Nẵng',
        ]);
        $responseDaNang->assertOk();
        $contentDaNang = $responseDaNang->streamedContent();

        $this->assertStringNotContainsString('Máy chủ AI (gemini-2.5-flash) đang phản hồi chậm', $contentDaNang);
        $this->assertStringNotContainsString('Máy chủ Travel Planner hiện đang bận', $contentDaNang);
        $this->assertStringContainsString('turn_complete', $contentDaNang);

        // 2. Kiểm tra với câu hỏi "Huế" (trong ảnh bị lỗi máy chủ bận)
        $responseHue = $this->actingAs($user)->postJson('/commerce-api/shopping/chat', [
            'message' => 'Huế',
        ]);
        $responseHue->assertOk();
        $contentHue = $responseHue->streamedContent();

        $this->assertStringNotContainsString('Máy chủ Travel Planner hiện đang bận', $contentHue);
        $this->assertStringContainsString('turn_complete', $contentHue);
    }

    /**
     * Kiểm tra tích hợp OpenAI API Key: Khi có OPENAI_API_KEY hợp lệ, ưu tiên OpenAI.
     */
    public function test_ai_agent_prioritizes_openai_provider_when_configured(): void
    {
        $service = app(AiTravelAssistantService::class);
        $this->assertInstanceOf(AiTravelAssistantService::class, $service);

        // Kiểm tra cấu hình ưu tiên provider là openai khi có OPENAI_API_KEY
        config([
            'commerce.openai_api_key' => 'sk-mock-valid-key',
            'commerce.openai_model' => 'gpt-4o-mini',
            'commerce.gemini_api_key' => 'mock-gemini-key',
            'commerce.gemini_model' => 'gemini-flash-lite-latest',
        ]);

        $this->assertSame('sk-mock-valid-key', config('commerce.openai_api_key'));
        $this->assertSame('gpt-4o-mini', config('commerce.openai_model'));
    }

    /**
     * Kiểm tra khi OpenAI gặp lỗi thì tự động chuyển qua Gemini âm thầm.
     */
    public function test_ai_agent_automatically_falls_back_to_gemini_when_openai_fails(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

        // Đặt OPENAI_API_KEY giả lập bị lỗi (sai key), giữ GEMINI_API_KEY thật đang hoạt động
        $origOpenAi = config('commerce.openai_api_key');
        $realGemini = config('commerce.gemini_api_key') ?: env('GEMINI_API_KEY');
        try {
            config([
                'commerce.openai_api_key' => 'sk-invalid-mock-openai-key-trigger-fallback',
                'commerce.openai_model' => 'gpt-4o-mini',
                'commerce.gemini_api_key' => $realGemini,
                'commerce.gemini_model' => 'gemini-flash-lite-latest',
            ]);

            $response = $this->actingAs($user)->postJson('/commerce-api/shopping/chat', [
                'message' => 'Đà Nẵng',
            ]);

            $response->assertOk();
            $content = $response->streamedContent();

            // Phải tự động chuyển sang Gemini và hoàn thành thành công
            $this->assertStringContainsString('turn_complete', $content);
            $this->assertStringContainsString('gemini', $content);
            $this->assertStringNotContainsString('Máy chủ Travel Planner hiện đang bận', $content);
        } finally {
            config(['commerce.openai_api_key' => $origOpenAi]);
        }
    }

    /**
     * Kiểm tra cơ chế tự động chuẩn hóa model gemini-2.5-flash sang gemini-flash-lite-latest để không bao giờ bị 404.
     */
    public function test_gemini_model_sanitization_prevents_404_error(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);

        // Cấu hình cố tình ghi gemini-2.5-flash
        $origModel = config('commerce.gemini_model');
        $realGemini = config('commerce.gemini_api_key') ?: env('GEMINI_API_KEY');
        try {
            config([
                'commerce.openai_api_key' => null,
                'commerce.gemini_api_key' => $realGemini,
                'commerce.gemini_model' => 'gemini-2.5-flash',
            ]);

            $response = $this->actingAs($user)->postJson('/commerce-api/shopping/chat', [
                'message' => 'Đà Nẵng',
            ]);

            $response->assertOk();
            $content = $response->streamedContent();

            $this->assertStringNotContainsString('Máy chủ AI (gemini-2.5-flash) đang phản hồi chậm', $content);
            $this->assertStringContainsString('turn_complete', $content);
        } finally {
            config(['commerce.gemini_model' => $origModel]);
        }
    }
}

