<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\Interfaces\AiTravelAssistantInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CommerceAgentController extends Controller
{
    public function __construct(private AiTravelAssistantInterface $assistant) {}

    public function shopping(Request $request)
    {
        return view('commerce.index', ['merchant' => false]);
    }

    public function merchant(Request $request)
    {
        if (! $request->user()) {
            return redirect()->guest(route('login'))->with('info', 'Vui lòng đăng nhập với tài khoản quản trị.');
        }

        abort_unless($request->user()->role === 'admin' && $request->user()->is_active, 403);

        return view('commerce.index', ['merchant' => true]);
    }

    public function surface(Request $request, string $role, string $action = '')
    {
        abort_unless(in_array($role, ['shopping', 'merchant'], true), 404);
        abort_if($role === 'merchant' && (! $request->user() || $request->user()->role !== 'admin' || ! $request->user()->is_active), 403);
        abort_if($request->user() && ! $request->user()->is_active, 403);

        $key = 'commerce_'.$role;
        try {
            $result = $this->assistant->surface($role, $request->method().':'.$action, $request->all(), $request->session()->get($key, []), $request->user());
        } catch (\DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
        $request->session()->put($key, $result['conversation']);
        return response()->json($result['data']);
    }

    public function chat(Request $request, string $role)
    {
        abort_unless(in_array($role, ['shopping', 'merchant'], true), 404);
        abort_if($role === 'merchant' && (! $request->user() || $request->user()->role !== 'admin' || ! $request->user()->is_active), 403);
        abort_if($request->user() && ! $request->user()->is_active, 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
            'provider' => ['nullable', 'string', 'in:auto,openai,gemini'],
        ]);
        $message = trim($validated['message']);
        $provider = $validated['provider'] ?? $request->input('provider', 'openai');
        if ($provider === 'auto') {
            $provider = 'openai';
        }
        abort_if($message === '', 422);
        $session = $request->session();
        $user = $request->user();
        return response()->stream(function () use ($session, $user, $role, $message, $provider) {
            @ini_set('max_execution_time', '300');
            @set_time_limit(300);
            $textDeltaEmitted = false;
            $emit = function (array $event) use (&$textDeltaEmitted) {
                if (($event['type'] ?? '') === 'text_delta') {
                    $textDeltaEmitted = true;
                }
                echo 'event: '.$event['type']."\n".'data: '.json_encode($event['data'] ?? [], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n\n";
                if (! app()->runningUnitTests() && ob_get_level() > 0) {
                    @ob_flush();
                }
                if (! app()->runningUnitTests()) {
                    flush();
                }
            };
            $lock = null;
            try {
                $lock = Cache::lock('commerce-stream:'.$session->getId(), 240);
                $acquired = $lock->get();
            } catch (\Throwable) {
                $acquired = true;
                $lock = null;
            }
            if (! $acquired) {
                $emit(['type' => 'error', 'data' => ['message' => 'Một lượt trả lời khác đang chạy. Vui lòng đợi.']]);
                return;
            }
            try {
                $result = $this->assistant->reply($message, $session->get('commerce_'.$role, []), $user, $session->getId(), $role, $emit, $provider);
                $actualProvider = $result['provider'] ?? (config('commerce.openai_api_key') ? 'openai' : 'gemini');
                $emit(['type' => 'model_provider', 'data' => ['provider' => $actualProvider]]);

                // Đảm bảo luôn phát text_delta nếu trong quá trình stream chưa phát
                $ansText = $result['answer'] ?? 'Tôi đã chuẩn bị thông tin bên dưới.';
                if (! $textDeltaEmitted && trim($ansText) !== '') {
                    $emit(['type' => 'text_delta', 'data' => ['text' => $ansText]]);
                }

                // Lưu lịch sử hội thoại vào Database cho người dùng đăng nhập và Session/Cache
                try {
                    if ($user) {
                        $conv = AiConversation::firstOrCreate(['user_id' => $user->id]);
                        AiMessage::create([
                            'conversation_id' => $conv->id,
                            'role' => 'user',
                            'content' => $message,
                        ]);
                        AiMessage::create([
                            'conversation_id' => $conv->id,
                            'role' => 'assistant',
                            'content' => $ansText,
                        ]);
                    }
                    $history = $session->get('commerce_chat_history', []);
                    $turnNum = (int) (count(array_filter($history, fn ($i) => ($i['kind'] ?? '') === 'assistant')) + 1);
                    $history[] = ['kind' => 'user', 'text' => $message];
                    $history[] = [
                        'kind' => 'assistant',
                        'turn' => $turnNum,
                        'segments' => [['type' => 'text', 'text' => $ansText]],
                        'suggestions' => [],
                        'pending' => false,
                        'tools' => [],
                        'modelProvider' => $actualProvider,
                    ];
                    $session->put('commerce_chat_history', $history);
                    Cache::forever('chat_history:'.($user ? "user_{$user->id}" : 'session_'.$session->getId()), $history);

                    // Đồng bộ trí nhớ cho phiên hiện tại
                    if (! empty($result['conversation']['memory'])) {
                        $memFacts = $result['conversation']['memory'];
                        if ($user) {
                            Cache::forever("user_memory:{$user->id}", $memFacts);
                        }
                        Cache::forever('guest_memory:'.$session->getId(), $memFacts);
                        $session->put('commerce_memory', $memFacts);
                    }
                } catch (\Throwable $histErr) {
                    \Illuminate\Support\Facades\Log::warning('Lưu lịch sử chat controller warning: '.$histErr->getMessage());
                }

                try {
                    $session->put('commerce_'.$role, $result['conversation']);
                    $session->save();
                } catch (\Throwable $saveErr) {
                    \Illuminate\Support\Facades\Log::warning('Session save warning: '.$saveErr->getMessage());
                }
                $emit(['type' => 'turn_complete', 'data' => ['stop_reason' => 'end_turn', 'provider' => $actualProvider]]);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::error('CommerceAgentController chat exception:', [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]);
                $msg = $exception instanceof \Symfony\Component\Process\Exception\ProcessTimedOutException
                    ? 'Máy chủ AI phản hồi quá lâu, vui lòng thử lại câu hỏi ngắn hơn.'
                    : 'Máy chủ Travel Planner hiện đang bận, xin vui lòng thử lại sau!';
                $emit(['type' => 'error', 'data' => ['message' => $msg]]);
                $emit(['type' => 'turn_complete', 'data' => ['stop_reason' => 'error']]);
            } finally {
                try {
                    $lock?->release();
                } catch (\Throwable) {}
            }
        }, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache, no-transform', 'X-Accel-Buffering' => 'no']);
    }
}
