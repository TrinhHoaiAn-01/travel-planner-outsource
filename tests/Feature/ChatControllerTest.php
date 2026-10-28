<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    public function test_chat_forwards_server_session_history_and_saves_reply(): void
    {
        Http::fake(['127.0.0.1:8765/chat' => Http::response(['answer' => 'Xin chào!'])]);
        $history = [['role' => 'user', 'content' => 'Trước đó']];
        $this->withSession(['travel_chat_history' => $history])
            ->postJson(route('chat.store'), ['message' => 'Đà Nẵng'])
            ->assertOk()->assertJson(['answer' => 'Xin chào!'])
            ->assertSessionHas('travel_chat_history', [
                ...$history,
                ['role' => 'user', 'content' => 'Đà Nẵng'],
                ['role' => 'assistant', 'content' => 'Xin chào!'],
            ]);
        Http::assertSent(fn ($request) => $request['message'] === 'Đà Nẵng' && $request['history'] === $history);
    }

    public function test_chat_rejects_empty_input_and_handles_service_failure(): void
    {
        Http::fake(['*' => Http::response([], 500)]);
        $this->postJson(route('chat.store'), ['message' => '  '])->assertUnprocessable();
        $this->postJson(route('chat.store'), ['message' => 'Xin chào'])->assertStatus(503)
            ->assertSessionMissing('travel_chat_history');
    }
}
