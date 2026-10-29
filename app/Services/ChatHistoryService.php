<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatHistoryService
{
    private function owner(Request $request): string
    {
        if ($request->user()) {
            return 'web-user:'.$request->user()->getAuthIdentifier();
        }

        if (! $request->session()->has('travel_chat_guest')) {
            $request->session()->put('travel_chat_guest', (string) Str::uuid());
        }

        return 'web-guest:'.$request->session()->get('travel_chat_guest');
    }

    public function read(Request $request): array
    {
        $owner = $this->owner($request);
        // Do not carry a previous account's cached history across login/logout.
        if ($request->session()->get('travel_chat_owner') !== $owner) {
            $request->session()->forget('travel_chat_history');
            $request->session()->forget('travel_chat_pending');
            $request->session()->put('travel_chat_owner', $owner);
        }

        if ($request->session()->has('travel_chat_pending')) {
            return ['history' => $request->session()->get('travel_chat_history', []), 'history_source' => 'session'];
        }

        try {
            $connection = DB::connection('chatbot');
            $sessionId = $connection->table('chat_sessions')->where('user_id', $owner)
                ->where('channel_id', 'website')->orderByDesc('session_id')->value('session_id');
            $history = $sessionId ? $connection->table('chat_messages')->where('session_id', $sessionId)
                ->whereIn('role', ['user', 'assistant'])->orderByDesc('message_id')->limit(20)->get()
                ->reverse()->map(function ($row) {
                    $metadata = json_decode($row->metadata ?? '{}', true);

                    return ['role' => $row->role, 'content' => mb_substr($row->content, 0, 4000),
                        'recommendations' => $metadata['recommendations'] ?? []];
                })->values()->all() : [];
            $request->session()->put('travel_chat_history', $history);

            return ['history' => $history, 'history_source' => 'database'];
        } catch (QueryException $exception) {
            // A missing optional chatbot database must not disable the public widget.
            Log::warning('Chatbot history database unavailable.', ['code' => $exception->getCode()]);

            return ['history' => $request->session()->get('travel_chat_history', []), 'history_source' => 'session'];
        }
    }

    public function append(Request $request, array $history, string $message, array $reply): bool
    {
        $owner = $this->owner($request);
        $pair = [
            ['role' => 'user', 'content' => $message],
            ['role' => 'assistant', 'content' => mb_substr($reply['answer'], 0, 4000),
                'recommendations' => $reply['recommendations']],
        ];
        $request->session()->put('travel_chat_history', array_slice([...$history, ...$pair], -20));
        $pending = array_slice([...$request->session()->get('travel_chat_pending', []), ...$pair], -20);
        $request->session()->put('travel_chat_pending', $pending);

        try {
            $connection = DB::connection('chatbot');
            $connection->transaction(function () use ($connection, $owner, $pending) {
                $sessionId = $connection->table('chat_sessions')->where('user_id', $owner)
                    ->where('channel_id', 'website')->orderByDesc('session_id')->value('session_id');
                if (! $sessionId) {
                    $sessionId = $connection->table('chat_sessions')->insertGetId([
                        'user_id' => $owner, 'channel_id' => 'website', 'created_at' => now(),
                    ], 'session_id');
                }
                foreach ($pending as $item) {
                    $connection->table('chat_messages')->insert([
                        'session_id' => $sessionId, 'role' => $item['role'], 'content' => $item['content'],
                        'metadata' => json_encode(['recommendations' => $item['recommendations'] ?? []], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                    ]);
                }
            });
            $request->session()->forget('travel_chat_pending');

            return true;
        } catch (QueryException $exception) {
            Log::warning('Chatbot reply saved to browser session only.', ['code' => $exception->getCode()]);

            return false;
        }
    }
}
