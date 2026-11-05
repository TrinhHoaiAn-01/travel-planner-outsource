<?php

namespace App\Console\Commands;

use App\Models\Destination;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class IndexCommerceDestinations extends Command
{
    protected $signature = 'commerce:index';

    protected $aliases = ['commerce:index-destinations'];

    protected $description = 'Đồng bộ điểm đến từ database vào Apache Solr cho Commerce Agents';

    public function handle(): int
    {
        $count = 0;
        try {
            Http::timeout(30)->post(config('commerce.solr_url').'/update?commit=true', ['delete' => ['query' => '*:*']])->throw();
            Destination::where('name', 'NOT LIKE', '%#%')->with(['city', 'category'])->chunkById(100, function ($destinations) use (&$count) {
                $documents = $destinations->map(fn ($destination) => [
                    'id' => (string) $destination->id, 'name' => $destination->name,
                    'city' => $destination->city?->name ?? '', 'category' => $destination->category?->name ?? '',
                    'description' => strip_tags($destination->description ?? ''), 'address' => $destination->address ?? '',
                ])->all();
                Http::timeout(30)->post(config('commerce.solr_url').'/update?commit=true', $documents)->throw();
                $count += count($documents);
            });
        } catch (\Throwable $exception) {
            $this->error('Không thể đồng bộ Apache Solr. Kiểm tra core và schema trong docs/commerce-agents.md.');

            return self::FAILURE;
        }
        $this->info("Đã đồng bộ {$count} điểm đến.");

        return self::SUCCESS;
    }
}
