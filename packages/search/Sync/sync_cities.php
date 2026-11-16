<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM TINH / THANH PHO (CITIES)
 * Trang/Phan he: Quan ly Tinh / Thanh pho (/admin/cities)
 * Core Solr: admin_system_search (entity_type = 'city')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo bang `cities` trong CSDL MySQL.
 * 2. Trich xuat cac truong tim kiem: Ten Tinh/Thanh, Vung mien (Region), Mo ta.
 * 3. Nap vao Solr core 'admin_system_search' phuc vu tim kiem trong trang Quan ly Tinh thanh.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\City;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM TINH / THANH PHO (CITIES)\n";
echo "Trang ap dung: /admin/cities\n";
echo "Thuc the     : City (entity_type: city)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi city cu
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:city'],
    ]);

    // 2. Lay du lieu va dong bo theo lo 100 ban ghi
    $totalCount = 0;
    City::query()->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Tinh/Thanh pho vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo City: " . $e->getMessage() . "\n\n";
}
