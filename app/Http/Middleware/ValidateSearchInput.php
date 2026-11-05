<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateSearchInput
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->isMethod('GET')) {
            $request->validate([
                'search' => ['sometimes', 'nullable', 'string', 'max:200'],
                'keyword' => ['sometimes', 'nullable', 'string', 'max:200'],
                'query' => ['sometimes', 'nullable', 'string', 'max:200'],
            ]);
        }

        return $next($request);
    }
}
