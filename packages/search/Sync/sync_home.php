<?php

/**
 * ============================================================================
 * SCRIPT DONG BO DU LIEU TIM KIEM TRANG CHU (HOME DESTINATION SEARCH)
 * Trang/Phan he: Trang chu (Trang chu & Bo loc Dia diem Nhanh)
 * Core Solr: home_destination_search
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc toan bo dia diem du lich tu bang `destinations` trong CSDL MySQL.
 * 2. Ket hop thong tin Tinh/Thanh (`cities`), Danh muc (`categories`),
 *    Anh dai dien (`destination_images`) va kiem tra co phong luu tru (`room_types`).
 * 3. Chuan hoa tai lieu Solr Document de phuc vu bo loc tim kiem Trang chu:
 *    - Tim kiem theo tu khoa (name, city_name, category_name, description).
 *    - Loc theo tinh thanh (city_id) va danh muc (category_id).
 *    - Loc theo khoang gia ve (entrance_fee).
 *    - Loc theo dia diem co phong khach san (has_hotel = true).
 * 4. Xoa sach chi muc cu va nap du lieu moi vao Solr core 'home_destination_search'.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\Destination;

$solrHost = get_solr_base_host();
$homeSolrUrl = "{$solrHost}/solr/home_destination_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM TRANG CHU (HOME DESTINATION SEARCH)\n";
echo "Trang ap dung: Trang chu (http://localhost:8000/) & Bo loc Dia diem\n";
echo "Core Solr    : home_destination_search\n";
echo "------------------------------------------------------------------------\n";

try {
    // 1. Xoa sach toan bo chi muc cu trong core home_destination_search
    solr_http_request("{$homeSolrUrl}/update?commit=true", 'POST', ['delete' => ['query' => '*:*']]);

    // 2. Lay du lieu dia diem kem cac quan he can thiet
    $destinations = Destination::query()
        ->with(['city', 'category', 'primaryImage', 'images', 'roomTypes'])
        ->get();

    $documents = [];
    foreach ($destinations as $dest) {
        $primaryImg = $dest->primaryImage?->image_url ?: ($dest->images->first()?->image_url ?? '');

        $documents[] = [
            'id'            => (string) $dest->id,
            'name'          => (string) $dest->name,
            'slug'          => (string) $dest->slug,
            'city_name'     => (string) ($dest->city?->name ?? ''),
            'city_id'       => (int) ($dest->city_id ?? 0),
            'category_name' => (string) ($dest->category?->name ?? ''),
            'category_id'   => (int) ($dest->category_id ?? 0),
            'entrance_fee'  => (float) ($dest->entrance_fee ?? 0.0),
            'price'         => (float) ($dest->entrance_fee ?? 0.0),
            'has_hotel'     => (bool) ($dest->roomTypes->count() > 0 || (bool) ($dest->is_lodging ?? false)),
            'rating'        => (float) ($dest->rating ?? 0.0),
            'address'       => (string) ($dest->address ?? ''),
            'description'   => (string) strip_tags((string) $dest->description),
            'image_url'     => (string) $primaryImg,
        ];
    }

    // 3. Nap vao Solr theo tung lo (Batch 100 ban ghi)
    $chunks = array_chunk($documents, 100);
    $indexedCount = 0;
    foreach ($chunks as $chunk) {
        solr_http_request("{$homeSolrUrl}/update?commit=true", 'POST', $chunk);
        $indexedCount += count($chunk);
    }

    echo "[HOAN TAT] Da dong bo {$indexedCount} dia diem vao Solr core 'home_destination_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo home_destination_search: " . $e->getMessage() . "\n\n";
}
