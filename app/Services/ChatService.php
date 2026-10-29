<?php

namespace App\Services;

use App\Services\Interfaces\ChatServiceInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ChatService implements ChatServiceInterface
{
    public function __construct(private ChatHistoryService $history, private ChatSuggestionService $suggestions) {}

    public function state(Request $request): array
    {
        $state = $this->history->read($request);

        return [...$state, 'suggestions' => $this->suggestions->suggestions($state['history'])];
    }

    public function reply(Request $request, string $message, ?string $action): array
    {
        $state = $this->history->read($request);
        $history = $state['history'];
        $currentHistory = [...$history, ['role' => 'user', 'content' => $message]];
        if ($action) {
            $reply = $this->suggestions->recommend($action, $currentHistory);
        } else {
            try {
                $response = Http::connectTimeout(5)->timeout(120)->acceptJson()->post(config('services.chatbot.url').'/chat', [
                    'message' => $message,
                    'history' => array_map(fn ($item) => ['role' => $item['role'], 'content' => $item['content']], $history),
                ]);
                $answer = $response->json('answer');
                if (! $response->successful() || ! is_string($answer) || trim($answer) === '') {
                    throw new HttpException(503, 'Trợ lý đang bận. Vui lòng thử lại.');
                }
            } catch (ConnectionException $exception) {
                throw new HttpException(503, 'Không thể kết nối với trợ lý. Bạn vẫn có thể dùng các nút gợi ý.');
            }
            $reply = ['answer' => $answer, 'recommendations' => []];
        }
        $persisted = $this->history->append($request, $history, $message, $reply);

        return [...$reply, 'suggestions' => $this->suggestions->suggestions($currentHistory),
            'history_source' => $persisted ? 'database' : 'session'];
    }
}
