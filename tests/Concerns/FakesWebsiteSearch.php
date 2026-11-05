<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Http;

trait FakesWebsiteSearch
{
    protected function fakeWebsiteSearch(array $matches): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake([config('search.solr_url').'/select*' => function ($request) use ($matches) {
            $type = explode(' AND ', str_replace('entity_type:', '', $request['fq']))[0];

            return Http::response([
                'response' => ['docs' => array_map(fn ($id) => ['record_id' => $id], $matches[$type] ?? [])],
                'nextCursorMark' => $request['cursorMark'],
            ]);
        }]);
    }
}
