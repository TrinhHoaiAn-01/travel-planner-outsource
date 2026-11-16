<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TIM KIEM THUONG MAI & AI AGENT (HOTEL COMMERCE SEARCH)
 * Trang/Phan he: Tim kiem Co so Luu tru cho Dai ly & AI Chatbot
 * Core Solr: hotel_commerce_search
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Doc cac co so luu tru / khach san (is_lodging = 1 hoac co phong roomTypes).
 * 2. Trich xuat: Ten co so, Mo ta, Dia chi, Tinh/Thanh, Danh muc.
 * 3. Nap vao Solr core 'hotel_commerce_search' phuc vu Engine tim kiem thuong mai.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Models\Destination;

$solrHost = get_solr_base_host();
$commerceUrl = "{$solrHost}/solr/hotel_commerce_search";
$aiUrl = "{$solrHost}/solr/ai_assistant_search";

echo "------------------------------------------------------------------------\n";
echo "DONG BO TIM KIEM THUONG MAI & AI AGENT (HOTEL COMMERCE SEARCH)\n";
echo "Phan he      : Dai ly Khach san & Tro ly AI (AI Chatbot)\n";
echo "Core Solr    : hotel_commerce_search & ai_assistant_search\n";
echo "------------------------------------------------------------------------\n";

try {
    // 1. Xoa sach toan bo chi muc cu trong core hotel_commerce_search va ai_assistant_search
    solr_http_request("{$commerceUrl}/update?commit=true", 'POST', ['delete' => ['query' => '*:*']]);
    solr_http_request("{$aiUrl}/update?commit=true", 'POST', ['delete' => ['query' => '*:*']]);

    // 2. Lay cac dia diem la co so luu tru
    $listings = Destination::query()
        ->where('is_lodging', true)
        ->orWhereHas('roomTypes')
        ->with(['city', 'category'])
        ->get();

    $docs = [];
    foreach ($listings as $listing) {
        $docs[] = [
            'id'          => (string) $listing->id,
            'name'        => (string) $listing->name,
            'description' => (string) strip_tags((string) $listing->description),
            'address'     => (string) ($listing->address ?? ''),
            'city'        => (string) ($listing->city?->name ?? ''),
            'category'    => (string) ($listing->category?->name ?? ''),
        ];
    }

    $totalCount = 0;
    if (!empty($docs)) {
        solr_http_request("{$commerceUrl}/update?commit=true", 'POST', $docs);
        solr_http_request("{$aiUrl}/update?commit=true", 'POST', $docs);
        $totalCount = count($docs);
    }

    echo "[HOAN TAT] Da dong bo {$totalCount} co so luu tru vao 'hotel_commerce_search' va 'ai_assistant_search'.\n\n";
} catch (Throwable $e) {
    echo "[LOI] Khong the dong bo Commerce: " . $e->getMessage() . "\n\n";
}
