<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommerceCatalogObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Destination|City|Category $model): void
    {
        if (! config('commerce.enabled') || app()->runningUnitTests()) {
            return;
        }
        try {
            $query = Destination::with(['city', 'category']);
            if ($model instanceof Destination) {
                $query->whereKey($model->id);
            } else {
                $query->where($model instanceof City ? 'city_id' : 'category_id', $model->id);
            }
            $query->chunkById(100, function ($destinations) {
                $documents = $destinations->map(fn ($destination) => [
                    'id' => (string) $destination->id, 'name' => $destination->name,
                    'city' => $destination->city?->name ?? '', 'category' => $destination->category?->name ?? '',
                    'description' => strip_tags($destination->description ?? ''), 'address' => $destination->address ?? '',
                ])->all();
                Http::timeout(10)->post(config('commerce.solr_url').'/update?commit=true', $documents)->throw();
            });
        } catch (\Throwable $exception) {
            Log::warning('Chưa đồng bộ chỉ mục Commerce Solr; chạy php artisan commerce:index để đồng bộ lại.', ['model' => $model::class, 'id' => $model->id]);
        }
    }

    public function deleted(Destination|City|Category $model): void
    {
        if (! $model instanceof Destination || ! config('commerce.enabled') || app()->runningUnitTests()) {
            return;
        }
        try {
            Http::timeout(10)->post(config('commerce.solr_url').'/update?commit=true', ['delete' => ['id' => (string) $model->id]])->throw();
        } catch (\Throwable $exception) {
            Log::warning('Chưa xóa điểm đến khỏi Commerce Solr.', ['destination_id' => $model->id]);
        }
    }
}
