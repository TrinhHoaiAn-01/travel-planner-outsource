<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM NGUOI DUNG (USERS)
 * Trang/Phan he: Quan ly Nguoi dung & Thanh vien (/admin/users)
 * Core Solr: admin_system_search (entity_type = 'user')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc bang `users` trong CSDL MySQL (bao gom ca tai khoan da bi khoa/xoa mem).
 * 2. Trich xuat cac truong tim kiem an toan: Ho ten, Email, So dien thoai, Tinh thanh, Vai tro.
 *    (Tuyet doi khong dua mat khau hay token xac thuc vao chi muc tim kiem).
 * 3. Nap vao Solr core 'admin_system_search' phuc vu tim kiem trong trang Quan tri Nguoi dung.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\User;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM NGUOI DUNG (USERS)\n";
echo "Trang ap dung: /admin/users (Quan ly Thanh vien & Tai khoan)\n";
echo "Thuc the     : User (entity_type: user - Bao gom ca tai khoan soft-deleted)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi user cu
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:user'],
    ]);

    // 2. Lay ca tai khoan active va soft-deleted de Admin co the tra cuu
    $totalCount = 0;
    User::withTrashed()->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Nguoi dung vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo User: " . $e->getMessage() . "\n\n";
}
