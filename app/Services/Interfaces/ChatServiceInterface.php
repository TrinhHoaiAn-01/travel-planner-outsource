<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;

interface ChatServiceInterface
{
    public function state(Request $request): array;

    public function reply(Request $request, string $message, ?string $action): array;
}
