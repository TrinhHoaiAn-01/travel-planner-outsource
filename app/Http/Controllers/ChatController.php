<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['message' => ['required', 'string', 'max:2000']]);
        $message = trim($validated['message']);
        if ($message === '') {
            return response()->json(['message' => 'Vui lòng nhập câu hỏi.'], 422);
        }

        $history = $request->session()->get('travel_chat_history', []);
        try {
            $response = Http::timeout(120)->acceptJson()->post(config('services.chatbot.url').'/chat', [
                'message' => $message,
                'history' => $history,
            ]);
            $answer = $response->json('answer');
            if (! $response->successful() || ! is_string($answer) || trim($answer) === '') {
                return response()->json(['message' => 'Trợ lý đang bận. Vui lòng thử lại.'], 503);
            }
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Không thể kết nối với trợ lý. Vui lòng thử lại sau.'], 503);
        }

        $history[] = ['role' => 'user', 'content' => $message];
        $history[] = ['role' => 'assistant', 'content' => mb_substr($answer, 0, 4000)];
        $request->session()->put('travel_chat_history', array_slice($history, -20));

        return response()->json(['answer' => $answer]);
    }
}
