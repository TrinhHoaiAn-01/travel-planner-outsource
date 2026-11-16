<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM DANH MUC (CATEGORIES)
 * Trang/Phan he: Quan ly Danh muc du lich (/admin/categories)
 * Core Solr: admin_system_search (entity_type = 'category')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo bang `categories` trong CSDL MySQL.
 * 2. Trich xuat cac truong tim kiem: Ten danh muc, Slug, Mo ta.
 * 3. Nap vao Solr core 'admin_system_search' phuc vu tim kiem trong trang Quan ly Danh muc.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\Category;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM DANH MUC (CATEGORIES)\n";
echo "Trang ap dung: /admin/categories\n";
echo "Thuc the     : Category (entity_type: category)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi category cu
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:category'],
    ]);

    // 2. Lay du lieu va dong bo theo lo 100 ban ghi
    $totalCount = 0;
    Category::query()->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Danh muc vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo Category: " . $e->getMessage() . "\n\n";
}
