<?php

namespace App\Observers;

use App\Services\Interfaces\SearchServiceInterface;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SearchIndexObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        $this->synchronize($model);
    }

    public function deleted(Model $model): void
    {
        $this->synchronize($model, true);
    }

    public function restored(Model $model): void
    {
        $this->synchronize($model);
    }

    private function synchronize(Model $model, bool $deleted = false): void
    {
        if (! config('search.sync') || (app()->runningUnitTests() && ! config('search.sync_in_tests'))) {
            return;
        }
        try {
            app(SearchServiceInterface::class)->synchronize($model, $deleted);
        } catch (\Throwable $exception) {
            Log::warning('Chưa đồng bộ chỉ mục website. Chạy php artisan search:index để đồng bộ lại.', [
                'model' => $model::class, 'id' => $model->getKey(),
            ]);
        }
    }
}
