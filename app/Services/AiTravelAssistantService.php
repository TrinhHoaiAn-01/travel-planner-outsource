<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use App\Services\Interfaces\AiTravelAssistantInterface;
use RuntimeException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class AiTravelAssistantService implements AiTravelAssistantInterface
{
    public function reply(string $message, array $conversation, ?User $user, string $sessionId, ?string $role = null, ?callable $onEvent = null, ?string $provider = null): array
    {
        @ini_set('max_execution_time', '300');
        @set_time_limit(300);

        $openaiKey = config('commerce.openai_api_key');
        $geminiKey = config('commerce.gemini_api_key') ?: config('commerce.api_key');

        $geminiModel = config('commerce.gemini_model', 'gemini-flash-lite-latest');
        if (in_array($geminiModel, ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-2.0-flash-lite'], true)) {
            $geminiModel = 'gemini-flash-lite-latest';
        }
        $openaiModel = config('commerce.openai_model', 'gpt-4o-mini');
        if ($openaiModel === 'gpt-4.1-mini') {
            $openaiModel = 'gpt-4o-mini';
        }

        $role ??= $user?->role === 'admin' ? 'merchant' : 'shopping';
        abort_unless(in_array($role, ['shopping', 'merchant'], true), 422);
        abort_if($role === 'merchant' && ($user?->role !== 'admin' || ! $user?->is_active), 403);
        $identity = $role.':'.($user?->id ?? 'guest');
        if (($conversation['identity'] ?? null) !== $identity) {
            $conversation = [];
        }

        // Khử các ký tự điều khiển ẩn và chuẩn hóa độ dài message để chống tấn công injection
        $sanitizedMessage = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $message);
        $sanitizedMessage = mb_substr(trim($sanitizedMessage), 0, 3000);

        if ($user) {
            $cachedMemory = \Illuminate\Support\Facades\Cache::get("user_memory:{$user->id}", []);
            if (! empty($cachedMemory) && empty($conversation['memory'])) {
                $conversation['memory'] = $cachedMemory;
            }
        } else {
            $cachedMemory = \Illuminate\Support\Facades\Cache::get("guest_memory:{$sessionId}", session('commerce_memory', []));
            if (! empty($cachedMemory) && empty($conversation['memory'])) {
                $conversation['memory'] = $cachedMemory;
            }
        }

        $backend = new CommerceBackendService($user, $conversation['backend'] ?? []);
        $wire = isset($conversation['wire']) ? json_decode($conversation['wire']) : null;

        // Tái tạo lịch sử trò chuyện từ database / cache nếu phiên mới hoặc vừa reload trang
        $messagesPayload = $wire?->messages ?? [];
        if (empty($messagesPayload)) {
            if ($user) {
                $conv = AiConversation::where('user_id', $user->id)->latest('id')->first();
                if ($conv) {
                    $recent = $conv->messages()->orderBy('id', 'asc')->take(20)->get();
                    foreach ($recent as $rm) {
                        $messagesPayload[] = [
                            'role' => $rm->role,
                            'content' => $rm->content,
                        ];
                    }
                }
            }
            if (empty($messagesPayload)) {
                $cachedHistory = \Illuminate\Support\Facades\Cache::get('chat_history:'.($user ? "user_{$user->id}" : "session_{$sessionId}"), session('commerce_chat_history', []));
                foreach ($cachedHistory as $ci) {
                    if (($ci['kind'] ?? '') === 'user' && ! empty($ci['text'])) {
                        $messagesPayload[] = ['role' => 'user', 'content' => $ci['text']];
                    } elseif (($ci['kind'] ?? '') === 'assistant') {
                        $txt = $ci['segments'][0]['text'] ?? '';
                        if ($txt) {
                            $messagesPayload[] = ['role' => 'assistant', 'content' => $txt];
                        }
                    }
                }
            }
        }

        // Luôn luôn ưu tiên chạy OpenAI trước, nếu OpenAI không hoạt động thì tự chuyển sang Gemini âm thầm
        $providerToRun = $openaiKey ? 'openai' : 'gemini';
        $actualProvider = $providerToRun;
        $result = null;
        $wireResult = null;

        if ($onEvent) {
            $onEvent(['type' => 'model_provider', 'data' => ['provider' => $actualProvider]]);
        }

        try {
            [$result, $wireResult, $detectedProvider] = $this->executeRunnerProcess(
                $providerToRun,
                $sanitizedMessage,
                $role,
                $user,
                $sessionId,
                $openaiKey,
                $openaiModel,
                $geminiKey,
                $geminiModel,
                $messagesPayload,
                $wire,
                $conversation,
                $backend,
                $onEvent
            );
            if ($detectedProvider) {
                $actualProvider = $detectedProvider;
            }
        } catch (\Throwable $primaryErr) {
            if ($providerToRun === 'openai' && $geminiKey) {
                \Illuminate\Support\Facades\Log::warning('OpenAI không hoạt động, chuyển đổi âm thầm sang Gemini: ' . $primaryErr->getMessage());
                try {
                    $actualProvider = 'gemini';
                    if ($onEvent) {
                        $onEvent(['type' => 'model_provider', 'data' => ['provider' => 'gemini']]);
                    }
                    [$result, $wireResult, $detectedProvider] = $this->executeRunnerProcess(
                        'gemini',
                        $sanitizedMessage,
                        $role,
                        $user,
                        $sessionId,
                        $openaiKey,
                        $openaiModel,
                        $geminiKey,
                        $geminiModel,
                        $messagesPayload,
                        $wire,
                        $conversation,
                        $backend,
                        $onEvent
                    );
                    if ($detectedProvider) {
                        $actualProvider = $detectedProvider;
                    }
                } catch (\Throwable $geminiErr) {
                    \Illuminate\Support\Facades\Log::error('Cả OpenAI và Gemini đều không hoạt động: ' . $geminiErr->getMessage());
                    throw new RuntimeException('Máy chủ Travel Planner hiện đang bận, xin vui lòng thử lại sau!');
                }
            } else {
                \Illuminate\Support\Facades\Log::error('AI Provider gặp lỗi và không có fallback: ' . $primaryErr->getMessage());
                throw new RuntimeException('Máy chủ Travel Planner hiện đang bận, xin vui lòng thử lại sau!');
            }
        }
        $result['answer'] = $result['answer'] ?: 'Tôi đã chuẩn bị thông tin bên dưới.';

        // Lưu trí nhớ AI bền vững cho cả người dùng đã đăng nhập và khách vãng lai
        if (isset($result['memory']) && is_array($result['memory'])) {
            if ($user) {
                \Illuminate\Support\Facades\Cache::forever("user_memory:{$user->id}", $result['memory']);
            }
            \Illuminate\Support\Facades\Cache::forever("guest_memory:{$sessionId}", $result['memory']);
            session(['commerce_memory' => $result['memory']]);
        }

        return [
            'answer' => $result['answer'], 'presentations' => $result['presentations'],
            'provider' => $actualProvider,
            'pending_changes' => $backend->pendingChanges(),
            'conversation' => ['identity' => $identity, 'messages' => $result['messages'],
                'state' => $result['state'], 'backend' => $backend->state(), 'memory' => $result['memory'] ?? [], 'wire' => $wireResult],
        ];
    }

    public function approveChange(string $changeId, array $conversation, User $user): array
    {
        abort_unless($user->role === 'admin' && $user->is_active, 403);
        abort_unless(($conversation['identity'] ?? '') === 'merchant:'.$user->id, 403);
        if ($user) {
            $cachedMemory = \Illuminate\Support\Facades\Cache::get("user_memory:{$user->id}", []);
            if (! empty($cachedMemory) && empty($conversation['memory'])) {
                $conversation['memory'] = $cachedMemory;
            }
        }

        $backend = new CommerceBackendService($user, $conversation['backend'] ?? []);
        $change = $backend->approveChange($changeId);
        $conversation['backend'] = $backend->state();
        $conversation['state']['seen_changes'][$changeId] = $change;
        if (isset($conversation['wire'])) {
            $wire = json_decode($conversation['wire']);
            $wire->state->seen_changes->{$changeId} = $change;
            $conversation['wire'] = json_encode($wire, JSON_THROW_ON_ERROR);
        }

        return ['change' => $change, 'conversation' => $conversation];
    }

    public function surface(string $role, string $action, array $parameters, array $conversation, ?User $user): array
    {
        abort_if($role === 'merchant' && ($user?->role !== 'admin' || ! $user?->is_active), 403);
        abort_if($user && ! $user->is_active, 403);
        $identity = $role.':'.($user?->id ?? 'guest');
        if (isset($conversation['identity']) && $conversation['identity'] !== $identity) {
            if ($action === 'POST:session' || $conversation['identity'] === 'shopping:guest') {
                $conversation['identity'] = $identity;
            } else {
                abort(403, 'Phiên làm việc không thuộc người dùng này.');
            }
        }
        $conversation['identity'] = $identity;
        return (new CommerceExperienceService)->handle($role, $action, $parameters, $conversation, $user);
    }
    private function executeRunnerProcess(
        string $activeProvider,
        string $sanitizedMessage,
        string $role,
        ?User $user,
        string $sessionId,
        ?string $openaiKey,
        string $openaiModel,
        ?string $geminiKey,
        string $geminiModel,
        array $messagesPayload,
        $wire,
        array $conversation,
        CommerceBackendService $backend,
        ?callable $onEvent
    ): array {
        $activeModel = $activeProvider === 'gemini' ? $geminiModel : $openaiModel;

        $input = new InputStream;
        $input->write(json_encode([
            'message' => $sanitizedMessage,
            'role' => $role,
            'user_id' => (string) ($user?->id ?? 'guest'),
            'session_id' => $sessionId,
            'provider' => $activeProvider,
            'openai_key' => $openaiKey,
            'openai_model' => $openaiModel,
            'gemini_key' => $geminiKey,
            'gemini_model' => $geminiModel,
            'model' => $activeModel,
            'messages' => $messagesPayload,
            'state' => $wire?->state ?? new \stdClass,
            'memory' => $conversation['memory'] ?? [],
        ], JSON_THROW_ON_ERROR)."\n");

        $python = config('commerce.python');
        if (! str_starts_with($python, '/') && ! preg_match('/^[A-Za-z]:[\\\/]/', $python) && (str_contains($python, '/') || str_contains($python, '\\'))) {
            $python = base_path($python);
        }

        $processEnv = array_filter([
            'COMMERCE_AGENT_PROVIDER' => $activeProvider,
            'OPENAI_API_KEY' => $openaiKey,
            'OPENAI_MODEL' => $openaiModel,
            'GEMINI_API_KEY' => $geminiKey,
            'GEMINI_MODEL' => $geminiModel,
            'PYTHONIOENCODING' => 'utf-8',
            'PYTHONUTF8' => '1',
            'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
            'PATH' => getenv('PATH') ?: '',
        ]);

        $process = new Process([
            $python, base_path('plugins/commerce-agents/travel_planner/runner.py'),
        ], base_path(), $processEnv);
        $process->setTimeout(config('commerce.timeout'));
        $process->setInput($input);
        $buffer = '';
        $result = null;
        $failureClass = null;
        $wireResult = null;
        $detectedProvider = null;

        try {
            $process->run(function (string $type, string $output) use (&$buffer, &$result, &$failureClass, &$wireResult, &$detectedProvider, $input, $backend, $onEvent) {
                if ($type !== Process::OUT) {
                    return;
                }
                $buffer .= $output;
                while (($position = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $position);
                    $buffer = substr($buffer, $position + 1);
                    $event = json_decode($line, true, 64, JSON_THROW_ON_ERROR);
                    if ($event['type'] === 'agent_event') {
                        if (($event['event']['type'] ?? '') === 'model_provider' && ! empty($event['event']['data']['provider'])) {
                            $detectedProvider = $event['event']['data']['provider'];
                        }
                        if ($onEvent && $event['event']['type'] !== 'turn_complete') {
                            $onEvent($event['event']);
                        }
                    } elseif ($event['type'] === 'tool') {
                        try {
                            $response = ['result' => $backend->execute($event['name'], $event['arguments'])];
                        } catch (\Throwable $exception) {
                            $response = ['error' => $exception instanceof \DomainException ? $exception->getMessage() : 'Dịch vụ dữ liệu tạm thời không khả dụng.'];
                        }
                        $input->write(json_encode($response, JSON_THROW_ON_ERROR)."\n");
                    } elseif ($event['type'] === 'result') {
                        $result = $event;
                        $wireResult = $line;
                        $input->close();
                    } elseif ($event['type'] === 'error') {
                        $failureClass = $event['error_class'] ?? 'UnknownError';
                        \Illuminate\Support\Facades\Log::warning('Commerce Agent error event:', ['event' => $event]);
                        $input->close();
                    }
                }
            });
        } finally {
            $input->close();
        }

        if (! $process->isSuccessful() || ! is_array($result)) {
            $stderr = trim($process->getErrorOutput());
            \Illuminate\Support\Facades\Log::warning('Commerce Agent không hoàn tất.', [
                'provider' => $activeProvider,
                'exit_code' => $process->getExitCode(),
                'error_class' => $failureClass,
                'stderr' => $stderr,
            ]);
            throw new RuntimeException('Không thể hoàn tất lượt chạy Commerce Agent.'.($stderr ? ' ('.$stderr.')' : ''));
        }

        return [$result, $wireResult, $detectedProvider];
    }
}
