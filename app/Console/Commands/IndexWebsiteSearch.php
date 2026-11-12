<?php

namespace App\Console\Commands;

use App\Services\Interfaces\SearchServiceInterface;
use Illuminate\Console\Command;

class IndexWebsiteSearch extends Command
{
    protected $signature = 'search:index';

    protected $description = 'Đồng bộ chỉ mục tìm kiếm website vào Apache Solr';

    public function handle(SearchServiceInterface $search): int
    {
        try {
            $count = $search->rebuild();
            $this->info("Đã đồng bộ {$count} bản ghi tìm kiếm.");

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            report($exception);
            $this->error('Không thể đồng bộ Solr. Kiểm tra core website_search rồi chạy lại search:index.');

            return self::FAILURE;
        }
    }
}
