<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface AiTravelAssistantInterface
{
    public function reply(string $message, array $conversation, ?User $user, string $sessionId, ?string $role = null, ?callable $onEvent = null, ?string $provider = null): array;

    public function surface(string $role, string $action, array $parameters, array $conversation, ?User $user): array;

    public function approveChange(string $changeId, array $conversation, User $user): array;
}
