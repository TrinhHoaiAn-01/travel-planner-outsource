<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM THANH TOAN (PAYMENTS)
 * Trang/Phan he: Quan ly Giao dich Thanh toan (/admin/payments)
 * Core Solr: admin_system_search (entity_type = 'payment')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo bang `payments` trong CSDL MySQL.
 * 2. Trich xuat cac truong tim kiem: Ma giao dich transaction_id, Phuong thuc thanh toan,
 *    Trang thai giao dich, Ma don booking lien quan, Ho ten khach hang, Email khach hang.
 * 3. Nap vao Solr core 'admin_system_search' phuc vu tim kiem trong trang Quan ly Thanh toan.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\Payment;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM THANH TOAN (PAYMENTS)\n";
echo "Trang ap dung: /admin/payments\n";
echo "Thuc the     : Payment (entity_type: payment)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi payment cu
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:payment'],
    ]);

    // 2. Lay du lieu kem quan he don hang va khach hang
    $totalCount = 0;
    Payment::query()->with('booking.user')->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Thanh toan vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo Payment: " . $e->getMessage() . "\n\n";
}
