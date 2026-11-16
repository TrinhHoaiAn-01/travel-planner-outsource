<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM DANH GIA (REVIEWS)
 * Trang/Phan he: Kiem duyet & Quan ly Danh gia (/admin/reviews)
 * Core Solr: admin_system_search (entity_type = 'review')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo bang `reviews` trong CSDL MySQL.
 * 2. Trich xuat cac truong tim kiem: Noi dung binh luan, Trang thai kiem duyet,
 *    Ten dia diem du lich duoc danh gia, Ho ten nguoi danh gia, Email nguoi danh gia.
 * 3. Nap vao Solr core 'admin_system_search' phuc vu tim kiem trong trang Kiem duyet Danh gia.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\Review;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM DANH GIA (REVIEWS)\n";
echo "Trang ap dung: /admin/reviews (Kiem duyet Danh gia & Binh luan)\n";
echo "Thuc the     : Review (entity_type: review)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi review cu
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:review'],
    ]);

    // 2. Lay du lieu kem quan he dia diem va nguoi dung
    $totalCount = 0;
    Review::query()->with(['destination', 'user'])->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Danh gia vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo Review: " . $e->getMessage() . "\n\n";
}
