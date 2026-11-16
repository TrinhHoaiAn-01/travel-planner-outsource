<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM CHUYEN DI (TRIPS)
 * Trang/Phan he: Quan ly Ke hoach Chuyen di ca nhan (/trips)
 * Core Solr: admin_system_search (entity_type = 'trip')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo bang `trips` trong CSDL MySQL.
 * 2. Trich xuat cac truong tim kiem: Ten chuyen di, Mo ta, Tinh/Thanh den, Ghi chu.
 * 3. Gan `owner_id` (user_id) de phan quyen: nguoi dung chi tim thay chuyen di cua minh.
 * 4. Nap vao Solr core 'admin_system_search' phuc vu tim kiem trong trang Chuyen di.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\Trip;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM CHUYEN DI (TRIPS)\n";
echo "Trang ap dung: /trips (Ke hoach Chuyen di cua toi)\n";
echo "Thuc the     : Trip (entity_type: trip - Phan quyen theo owner_id)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi trip cu
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:trip'],
    ]);

    // 2. Lay du lieu va dong bo theo lo 100 ban ghi
    $totalCount = 0;
    Trip::query()->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Chuyen di vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo Trip: " . $e->getMessage() . "\n\n";
}
