<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM HANG PHONG (ROOM TYPES)
 * Trang/Phan he: Quan ly Hang phong luu tru (/admin/room-types)
 * Core Solr: admin_system_search (entity_type = 'room_type')
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo bang `room_types` trong CSDL MySQL.
 * 2. Trich xuat cac truong tim kiem: Ten hang phong, Mo ta, Ten dia diem khach san.
 * 3. Nap vao Solr core 'admin_system_search' phuc vu tim kiem trong trang Quan ly Hang phong.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\RoomType;
use Search\HomeSearch\Solr\SolrDocumentBuilder;

$solrHost = get_solr_base_host();
$solrUrl = "{$solrHost}/solr/admin_system_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM HANG PHONG (ROOM TYPES)\n";
echo "Trang ap dung: /admin/room-types\n";
echo "Thuc the     : RoomType (entity_type: room_type)\n";
echo "------------------------------------------------------------------------\n";

try {
    $builder = new SolrDocumentBuilder();

    // 1. Xoa cac ban ghi room_type cu
    solr_http_request("{$solrUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => 'entity_type:room_type'],
    ]);

    // 2. Lay du lieu va dong bo theo lo 100 ban ghi
    $totalCount = 0;
    RoomType::query()->with('destination')->chunkById(100, function ($items) use ($builder, $solrUrl, &$totalCount) {
        $docs = $items->map(fn ($item) => $builder->build($item))->all();
        if (!empty($docs)) {
            solr_http_request("{$solrUrl}/update?commit=true", 'POST', $docs);
            $totalCount += count($docs);
        }
    });

    echo "[HOAN TAT] Da dong bo {$totalCount} ban ghi Hang phong vao core 'admin_system_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo RoomType: " . $e->getMessage() . "\n\n";
}
