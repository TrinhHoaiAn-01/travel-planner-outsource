<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\ChatServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    public function __construct(private ChatServiceInterface $chat) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->chat->state($request))->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'action' => ['nullable', 'string', Rule::in(['hotels', 'attractions', 'explore'])],
        ]);
        $message = trim($validated['message']);
        if ($message === '') {
            return response()->json(['message' => 'Vui lòng nhập câu hỏi.'], 422);
        }

        return response()->json($this->chat->reply($request, $message, $validated['action'] ?? null))
            ->header('Cache-Control', 'no-store, private');
    }
}
