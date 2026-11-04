<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

interface SearchServiceInterface
{
    public function apply(Builder $query, string $type, string $keyword, ?int $ownerId = null): Builder;

    public function suggestions(string $type, string $keyword, ?User $user, array $filters = []): array;

    public function index(Model $model): void;

    public function remove(Model $model): void;

    public function rebuild(): int;

    public function synchronize(Model $model, bool $deleted = false): void;
}
