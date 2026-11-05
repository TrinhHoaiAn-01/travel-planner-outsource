<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\SearchServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchSuggestionController extends Controller
{
    public function __invoke(Request $request, string $type, SearchServiceInterface $search): JsonResponse
    {
        $user = $request->user();
        if (! in_array($type, ['destination', 'city', 'category'], true)) {
            abort_unless($user, 401);
            abort_unless($user->is_active && ! $user->trashed(), 403);
            abort_unless($type === 'trip' || $user->isAdmin(), 403);
        }
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:200'],
            'city_id' => ['nullable', 'regex:/^(all|[0-9]+)$/'],
            'category_id' => ['nullable', 'regex:/^(all|[0-9]+)$/'],
            'status' => ['nullable', 'string', 'max:40'],
            'role' => ['nullable', 'string', 'max:40'],
            'region' => ['nullable', 'string', 'max:100'],
            'budget' => ['nullable', 'in:all,1,2,3'],
            'is_lodging' => ['nullable', 'in:0,1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'action' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json(['suggestions' => $search->suggestions(
            $type, (string) ($validated['keyword'] ?? ''), $user, $validated
        )])->header('Cache-Control', 'private, no-store');
    }
}
