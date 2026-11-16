<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM DIEM DEN (DESTINATIONS)
 * Trang/Phan he: Quan ly Diem den (/admin/destinations) & Danh muc Diem den (/destinations)
 * Core Solr: admin_system_search (entity_type = 'destination')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo bang `destinations` trong CSDL MySQL.
 * 2. Trich xuat cac truong tim kiem: Ten, Dia chi, Mo ta, Ten Tinh/Thanh, Ten Danh muc.
 * 3. Nap vao Solr core 'admin_system_search' de phuc vu tim kiem nhanh va phan trang.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\Destination;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM DIEM DEN (DESTINATIONS)\n";
echo "Trang ap dung: /admin/destinations va /destinations\n";
echo "Thuc the     : Destination (entity_type: destination)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi destination cu khoi core admin_system_search
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:destination'],
    ]);

    // 2. Lay du lieu va dong bo theo lo 100 ban ghi
    $totalCount = 0;
    Destination::query()->with(['city', 'category'])->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Diem den vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo Destination: " . $e->getMessage() . "\n\n";
}
