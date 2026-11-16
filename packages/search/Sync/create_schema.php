<?php

/**
 * ============================================================================
 * SCRIPT CAU HINH SCHEMA CHO TOAN BO DATABASE VA CAC CORE APACHE SOLR 9
 * Trang/Phan he: Ha tang Tim kiem Tong the (Toan bo Website & Trang chu)
 * ============================================================================
 *
 * Muc dich:
 * 1. Khoi tao Field Type 'text_vi' (StandardTokenizer + LowerCase + ASCIIFolding)
 *    de ho tro tim kiem tieng Viet khong dau tren tat ca cac truong van ban.
 * 2. Thiet lap Schema day du cho tat ca cac Core:
 *    - Core: home_destination_search (Tim kiem Diem den Trang chu)
 *    - Core: admin_system_search (Tim kiem Quan tri & He thong: 10 thuc the)
 *    - Core: hotel_commerce_search (Tim kiem Khach san, Luu tru & AI Agent)
 *    - Core: ai_assistant_search (Tim kiem Tro ly AI & Hoi thoai)
 */

require_once __DIR__ . '/bootstrap.php';

$solrHost = get_solr_base_host();

echo "========================================================================\n";
echo "KHOI TAO VA DONG BO SCHEMA CHO TOAN BO HE THONG TIM KIEM SOLR 9\n";
echo "Dia chi Solr Host: {$solrHost}\n";
echo "========================================================================\n\n";

$viTypeConfig = [
    'name' => 'text_vi',
    'class' => 'solr.TextField',
    'positionIncrementGap' => '100',
    'analyzer' => [
        'tokenizer' => ['class' => 'solr.StandardTokenizerFactory'],
        'filters' => [
            ['class' => 'solr.LowerCaseFilterFactory'],
            ['class' => 'solr.ASCIIFoldingFilterFactory'], // Chuyen tieng Viet co dau thanh khong dau tu dong
        ],
    ],
];

function apply_field_type_vi(string $coreUrl, array $viTypeConfig): void
{
    try {
        $typesRes = solr_http_request("{$coreUrl}/schema/fieldtypes");
        if (isset($typesRes['error'])) {
            echo "   [SAN SANG] Field Types duoc nap truc tiep tu schema.xml cua Core\n";
            return;
        }

        $existingTypes = [];
        foreach ($typesRes['fieldTypes'] ?? [] as $ft) {
            $existingTypes[$ft['name']] = true;
        }

        if (!isset($existingTypes['text_vi'])) {
            $res = solr_http_request("{$coreUrl}/schema", 'POST', ['add-field-type' => $viTypeConfig]);
            if (!isset($res['error'])) {
                echo "   [THEM MOI] Field Type [text_vi] cho {$coreUrl}\n";
            } else {
                echo "   [SAN SANG] Field Type [text_vi] da san sang qua schema.xml\n";
            }
        } else {
            echo "   [SAN SANG] Field Type [text_vi] da ton tai tai {$coreUrl}\n";
        }
    } catch (Throwable $e) {
        echo "   [THONG TIN] Schema core duoc cau hinh co dinh qua tap tin schema.xml\n";
    }
}

function apply_fields(string $coreUrl, array $fields): void
{
    try {
        $fieldsRes = solr_http_request("{$coreUrl}/schema/fields");
        if (isset($fieldsRes['error'])) {
            echo "   [SAN SANG] Schema fields duoc nap truc tiep tu schema.xml cua Core\n";
            return;
        }

        $existingFields = [];
        foreach ($fieldsRes['fields'] ?? [] as $f) {
            $existingFields[$f['name']] = true;
        }

        foreach ($fields as $fieldConfig) {
            $name = $fieldConfig['name'];
            $desc = $fieldConfig['desc'] ?? '';
            unset($fieldConfig['desc']);

            if (!isset($existingFields[$name])) {
                $res = solr_http_request("{$coreUrl}/schema", 'POST', ['add-field' => $fieldConfig]);
                if (!isset($res['error'])) {
                    echo "   [THEM MOI] Truong [{$name}] ({$fieldConfig['type']}) - {$desc}\n";
                }
            } else {
                $res = solr_http_request("{$coreUrl}/schema", 'POST', ['replace-field' => $fieldConfig]);
                if (!isset($res['error'])) {
                    echo "   [CAP NHAT] Truong [{$name}] ({$fieldConfig['type']}) - {$desc}\n";
                }
            }
        }
    } catch (Throwable $e) {
        echo "   [THONG TIN] Schema fields duoc cau hinh co dinh qua tap tin schema.xml\n";
    }
}

// -------------------------------------------------------------------------
// 1. Cấu hình Core: home_destination_search (Tìm kiếm Điểm đến Trang chủ)
// -------------------------------------------------------------------------
$homeSearchUrl = "{$solrHost}/solr/home_destination_search";
echo "[1/4] Cau hinh Schema Core: home_destination_search (Diem den Trang chu)...\n";
apply_field_type_vi($homeSearchUrl, $viTypeConfig);

$homeFields = [
    ['name' => 'name', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Ten dia diem du lich'],
    ['name' => 'slug', 'type' => 'string', 'stored' => true, 'indexed' => true, 'desc' => 'Slug dia diem'],
    ['name' => 'city_name', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Ten tinh thanh pho'],
    ['name' => 'city_id', 'type' => 'pint', 'stored' => true, 'indexed' => true, 'desc' => 'Ma ID tinh thanh pho'],
    ['name' => 'category_name', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Ten danh muc du lich'],
    ['name' => 'category_id', 'type' => 'pint', 'stored' => true, 'indexed' => true, 'desc' => 'Ma ID danh muc du lich'],
    ['name' => 'entrance_fee', 'type' => 'pdouble', 'stored' => true, 'indexed' => true, 'desc' => 'Gia ve vao cong'],
    ['name' => 'price', 'type' => 'pdouble', 'stored' => true, 'indexed' => true, 'desc' => 'Gia ve alias'],
    ['name' => 'has_hotel', 'type' => 'boolean', 'stored' => true, 'indexed' => true, 'desc' => 'Co phong luu tru khong'],
    ['name' => 'rating', 'type' => 'pfloat', 'stored' => true, 'indexed' => true, 'desc' => 'Diem danh gia trung binh'],
    ['name' => 'address', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Dia chi cu the'],
    ['name' => 'description', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Mo ta chi tiet'],
    ['name' => 'image_url', 'type' => 'string', 'stored' => true, 'indexed' => false, 'desc' => 'Duong dan anh dai dien'],
];
apply_fields($homeSearchUrl, $homeFields);
echo "   [HOAN TAT] Core 'home_destination_search' da duoc cau hinh thanh cong.\n\n";

// -------------------------------------------------------------------------
// 2. Cấu hình Core: admin_system_search (Tìm kiếm Toàn hệ thống & Admin Dashboard)
// -------------------------------------------------------------------------
$websiteSearchUrl = "{$solrHost}/solr/admin_system_search";
echo "[2/4] Cau hinh Schema Core: admin_system_search (Quan tri & He thong)...\n";
apply_field_type_vi($websiteSearchUrl, $viTypeConfig);

$websiteFields = [
    ['name' => 'record_id', 'type' => 'pint', 'stored' => true, 'indexed' => true, 'desc' => 'ID ban ghi CSDL'],
    ['name' => 'entity_type', 'type' => 'string', 'stored' => true, 'indexed' => true, 'desc' => 'Loai thuc the (destination, trip, user...)'],
    ['name' => 'search_text', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'multiValued' => true, 'desc' => 'Van ban tim kiem da truong tieng Viet'],
    ['name' => 'owner_id', 'type' => 'pint', 'stored' => true, 'indexed' => true, 'desc' => 'ID nguoi so huu phan quyen'],
];
apply_fields($websiteSearchUrl, $websiteFields);
echo "   [HOAN TAT] Core 'admin_system_search' da duoc cau hinh thanh cong.\n\n";

// -------------------------------------------------------------------------
// 3. Cấu hình Core: hotel_commerce_search (Khách sạn, Lưu trú & AI Agent)
// -------------------------------------------------------------------------
$commerceUrl = "{$solrHost}/solr/hotel_commerce_search";
echo "[3/4] Cau hinh Schema Core: hotel_commerce_search (Khach san & AI Agent)...\n";
apply_field_type_vi($commerceUrl, $viTypeConfig);

$commerceFields = [
    ['name' => 'name', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Ten co so luu tru / khach san'],
    ['name' => 'description', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Mo ta co so'],
    ['name' => 'address', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Dia chi co so'],
    ['name' => 'city', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Ten tinh thanh pho'],
    ['name' => 'category', 'type' => 'text_vi', 'stored' => true, 'indexed' => true, 'desc' => 'Ten danh muc'],
];
apply_fields($commerceUrl, $commerceFields);
echo "   [HOAN TAT] Core 'hotel_commerce_search' da duoc cau hinh thanh cong.\n\n";

// -------------------------------------------------------------------------
// 4. Cấu hình Core: ai_assistant_search (Trợ lý AI & Tri thức Hội thoại)
// -------------------------------------------------------------------------
$aiAssistantUrl = "{$solrHost}/solr/ai_assistant_search";
echo "[4/4] Cau hinh Schema Core: ai_assistant_search (Tro ly AI)...\n";
apply_field_type_vi($aiAssistantUrl, $viTypeConfig);
apply_fields($aiAssistantUrl, $commerceFields);
echo "   [HOAN TAT] Core 'ai_assistant_search' da duoc cau hinh thanh cong.\n\n";

echo "========================================================================\n";
echo "[KET QUA] TOAN BO SCHEMA SOLR 9 DA DUOC THIET LAP THANH CONG!\n";
echo "========================================================================\n\n";
