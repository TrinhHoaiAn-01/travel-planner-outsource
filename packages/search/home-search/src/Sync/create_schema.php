<?php

/**
 * Script Cấu hình Schema cho Apache Solr 9 (Core: home_search).
 * Nằm trong Package: Search\HomeSearch\Sync
 *
 * Mục đích:
 * 1. Khởi tạo kiểu dữ liệu tiếng Việt 'text_vi' hỗ trợ tìm kiếm không dấu (ASCII Folding).
 * 2. Khởi tạo và cập nhật đầy đủ các trường (fields) cần thiết để phục vụ tìm kiếm Trang chủ:
 *    - name, city_name, category_name, address, description (văn bản tiếng Việt)
 *    - city_id, category_id (lọc chính xác ID)
 *    - entrance_fee, price (lọc theo khoảng giá/ngân sách)
 *    - has_hotel (lọc địa điểm có khách sạn/lưu trú)
 *    - rating, image_url, slug
 *
 * Cách chạy:
 *   docker exec travel-planner-app php packages/search/home-search/src/Sync/create_schema.php
 *   hoặc:
 *   php packages/search/home-search/src/Sync/create_schema.php
 */

// Xác định đường dẫn máy chủ Solr
$baseUrl = rtrim(getenv('SOLR_URL') ?: (file_exists('/.dockerenv') ? 'http://solr:8983/solr/home_search' : 'http://127.0.0.1:8983/solr/home_search'), '/');

echo "====================================================================\n";
echo "  [SOLR 9 SCHEMA CONFIGURATION] - TẠO CẤU TRÚC SCHEMA TÌM KIẾM TRANG CHỦ\n";
echo "  Core Target : home_search\n";
echo "  Solr URL    : {$baseUrl}\n";
echo "====================================================================\n\n";

if (!function_exists('send_solr_request')) {
    /**
     * Hàm gửi yêu cầu HTTP tới Solr Schema API bằng cURL.
     */
    function send_solr_request(string $url, string $method = 'GET', ?array $body = null): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("Không thể kết nối tới Solr tại {$url}: {$error}");
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("Phản hồi không hợp lệ từ Solr (HTTP {$httpCode}): {$response}");
        }

        return $decoded;
    }
}

// 1. Kiểm tra kết nối tới Solr
try {
    $ping = send_solr_request("{$baseUrl}/select?q=*:*&rows=0&wt=json");
    if (!isset($ping['response']['numFound'])) {
        throw new RuntimeException("Phản hồi ping không chứa kết quả hợp lệ.");
    }
    echo "✅ [1/3] Kết nối máy chủ Solr thành công!\n\n";
} catch (Throwable $e) {
    echo "❌ LỖI: Không thể kết nối tới Solr!\n";
    echo "   Nguyên nhân: " . $e->getMessage() . "\n";
    echo "   Gợi ý: Kiểm tra container Docker 'travel-planner-solr' và đảm bảo core 'home_search' đã được tạo.\n";
    exit(1);
}

// 2. Khai báo kiểu dữ liệu tiếng Việt 'text_vi'
echo "⚙️  [2/3] Cấu hình Field Type 'text_vi' (Standard Tokenizer + Lowercase + ASCII Folding)...\n";
try {
    $typesRes = send_solr_request("{$baseUrl}/schema/fieldtypes");
    $existingTypes = [];
    foreach ($typesRes['fieldTypes'] ?? [] as $ft) {
        $existingTypes[$ft['name']] = true;
    }

    $viTypeConfig = [
        'name' => 'text_vi',
        'class' => 'solr.TextField',
        'positionIncrementGap' => '100',
        'analyzer' => [
            'tokenizer' => ['class' => 'solr.StandardTokenizerFactory'],
            'filters' => [
                ['class' => 'solr.LowerCaseFilterFactory'],
                ['class' => 'solr.ASCIIFoldingFilterFactory'], // Chuyển tiếng Việt có dấu thành không dấu tự động
            ],
        ],
    ];

    if (!isset($existingTypes['text_vi'])) {
        send_solr_request("{$baseUrl}/schema", 'POST', ['add-field-type' => $viTypeConfig]);
        echo "   ➕ Đã tạo mới Field Type [text_vi] thành công!\n";
    } else {
        echo "   ✅ Field Type [text_vi] đã tồn tại và sẵn sàng sử dụng.\n";
    }
} catch (Throwable $e) {
    echo "   ⚠️ Lỗi khi cấu hình field-type: " . $e->getMessage() . "\n";
}
echo "\n";

// 3. Khai báo danh sách các trường (Fields) tương thích 100% với Packages/Search/HomeSearch
echo "📋 [3/3] Cấu hình danh sách trường tìm kiếm tương thích với Packages/Search...\n";

$fields = [
    [
        'name' => 'name',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Tên địa điểm (tìm kiếm tiếng Việt toàn văn, boost cao nhất ^8)',
    ],
    [
        'name' => 'slug',
        'type' => 'string',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Slug địa điểm dùng tạo URL',
    ],
    [
        'name' => 'city_name',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Tên thành phố (boost ^5)',
    ],
    [
        'name' => 'city_id',
        'type' => 'pint',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Mã ID thành phố (lọc fq=city_id:X)',
    ],
    [
        'name' => 'category_name',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Tên danh mục du lịch (boost ^3)',
    ],
    [
        'name' => 'category_id',
        'type' => 'pint',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Mã ID danh mục (lọc fq=category_id:X)',
    ],
    [
        'name' => 'entrance_fee',
        'type' => 'pdouble',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Giá vé vào cổng (cho SolrHomeSearchEngine lọc fq=entrance_fee:[min TO max])',
    ],
    [
        'name' => 'price',
        'type' => 'pdouble',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Giá vé (alias dự phòng tương thích ngược)',
    ],
    [
        'name' => 'has_hotel',
        'type' => 'boolean',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Có phòng khách sạn lưu trú hay không (lọc fq=has_hotel:true)',
    ],
    [
        'name' => 'rating',
        'type' => 'pfloat',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Điểm đánh giá sao trung bình (1.0 - 5.0)',
    ],
    [
        'name' => 'address',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Địa chỉ chi tiết của địa điểm (boost ^2)',
    ],
    [
        'name' => 'description',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'desc' => 'Mô tả chi tiết địa điểm',
    ],
    [
        'name' => 'image_url',
        'type' => 'string',
        'stored' => true,
        'indexed' => false,
        'desc' => 'Đường dẫn ảnh đại diện hiển thị ra thẻ card',
    ],
];

try {
    $fieldsRes = send_solr_request("{$baseUrl}/schema/fields");
    $existingFields = [];
    foreach ($fieldsRes['fields'] ?? [] as $f) {
        $existingFields[$f['name']] = true;
    }

    $successCount = 0;
    foreach ($fields as $fieldConfig) {
        $name = $fieldConfig['name'];
        $desc = $fieldConfig['desc'];
        unset($fieldConfig['desc']);

        if (!isset($existingFields[$name])) {
            send_solr_request("{$baseUrl}/schema", 'POST', ['add-field' => $fieldConfig]);
            echo "   ➕ Tạo mới trường [{$name}] ({$fieldConfig['type']}) - {$desc}\n";
        } else {
            send_solr_request("{$baseUrl}/schema", 'POST', ['replace-field' => $fieldConfig]);
            echo "   🔄 Cập nhật trường [{$name}] ({$fieldConfig['type']}) - {$desc}\n";
        }
        $successCount++;
    }

    echo "\n====================================================================\n";
    echo "🎉 HOÀN TẤT THÀNH CÔNG: Đã cấu hình {$successCount}/" . count($fields) . " trường cho Solr core 'home_search'!\n";
    echo "====================================================================\n";
} catch (Throwable $e) {
    echo "❌ LỖI trong quá trình cấu hình trường: " . $e->getMessage() . "\n";
    exit(1);
}

