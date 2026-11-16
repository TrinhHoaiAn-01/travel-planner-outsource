<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM DON DAT PHONG (BOOKINGS)
 * Trang/Phan he: Quan ly Don dat phong (/admin/bookings) & Lich su dat phong
 * Core Solr: admin_system_search (entity_type = 'booking')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo bang `bookings` trong CSDL MySQL.
 * 2. Trich xuat cac truong tim kiem: Ma don booking_code, Trang thai,
 *    Ten khach hang, Email khach hang, Ten diem den / khach san, Ten hang phong.
 * 3. Nap vao Solr core 'admin_system_search' phuc vu tim kiem trong trang Quan ly Don dat.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\Booking;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM DON DAT PHONG (BOOKINGS)\n";
echo "Trang ap dung: /admin/bookings va /bookings\n";
echo "Thuc the     : Booking (entity_type: booking)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi booking cu
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:booking'],
    ]);

    // 2. Lay du lieu kem quan he nguoi dung, hang phong, diem den
    $totalCount = 0;
    Booking::query()->with(['user', 'roomType.destination'])->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Don dat phong vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo Booking: " . $e->getMessage() . "\n\n";
}
