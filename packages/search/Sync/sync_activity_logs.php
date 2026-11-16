<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM NHAT KY HOAT DONG (ACTIVITY LOGS)
 * Trang/Phan he: Quan ly Nhat ky He thong (/admin/activity-logs)
 * Core Solr: admin_system_search (entity_type = 'activity_log')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo bang `activity_logs` trong CSDL MySQL.
 * 2. Trich xuat cac truong tim kiem: Hanh dong thao tac, Mo ta chi tiet,
 *    Dia chi IP thuc hien, Ho ten nguoi thuc hien, Email nguoi thuc hien.
 * 3. Nap vao Solr core 'admin_system_search' phuc vu tim kiem trong trang Nhat ky Hoat dong.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\ActivityLog;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM NHAT KY HOAT DONG (ACTIVITY LOGS)\n";
echo "Trang ap dung: /admin/activity-logs (Audit Logs & Nhat ky He thong)\n";
echo "Thuc the     : ActivityLog (entity_type: activity_log)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi activity_log cu
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:activity_log'],
    ]);

    // 2. Lay du lieu kem quan he nguoi dung
    $totalCount = 0;
    ActivityLog::query()->with('user')->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Nhat ky vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo ActivityLog: " . $e->getMessage() . "\n\n";
}
