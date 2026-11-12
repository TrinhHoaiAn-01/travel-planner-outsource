<?php

/**
 * File cấu hình Schema cho Apache Solr 9 (Core: home_search).
 * Chạy một lần khi khởi tạo hệ thống hoặc khi cần cập nhật cấu trúc trường.
 *
 * Cách chạy từ dòng lệnh:
 *   php app/Services/Search/Home/create_schema.php
 */

require_once __DIR__ . '/solr.php';

echo "====================================================\n";
echo " BẮT ĐẦU CẤU HÌNH SCHEMA CHO SOLR 9 (CORE: home_search)\n";
echo " Solr URL: " . solr_get_base_url() . "\n";
echo "====================================================\n\n";

// 1. Kiểm tra kết nối tới Solr
if (!solr_ping()) {
    echo "❌ LỖI: Không thể kết nối tới Solr tại [" . solr_get_base_url() . "]!\n";
    echo "Vui lòng kiểm tra Docker container Solr 9 đã được bật và Core 'home_search' đã được tạo chưa.\n";
    exit(1);
}
echo "✅ Kết nối tới Solr thành công!\n\n";

// 2. Cấu hình kiểu dữ liệu tiếng Việt (text_vi) hỗ trợ bỏ dấu (ASCII Folding)
try {
    $fieldTypesRes = solr_http_request('/schema/fieldtypes', 'GET');
    $existingTypes = [];
    foreach ($fieldTypesRes['fieldTypes'] ?? [] as $ft) {
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
                ['class' => 'solr.ASCIIFoldingFilterFactory'], // Tự động chuyển Đ/đ -> d, à/á/ả/ã/ạ -> a...
            ],
        ],
    ];

    if (!isset($existingTypes['text_vi'])) {
        solr_http_request('/schema', 'POST', [], ['add-field-type' => $viTypeConfig]);
        echo "➕ Tạo kiểu dữ liệu [text_vi] hỗ trợ tìm kiếm tiếng Việt có dấu và không dấu\n";
    } else {
        echo "✅ Kiểu dữ liệu [text_vi] đã sẵn sàng\n";
    }
} catch (Exception $e) {
    echo "⚠️ Lỗi khi cấu hình field-type text_vi: " . $e->getMessage() . "\n";
}

// 3. Danh sách các trường cần thiết kế cho Tìm kiếm Trang chủ (Dùng text_vi cho các trường văn bản)
$fieldsToCreate = [
    [
        'name' => 'name',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'comment' => 'Tên địa điểm (tìm kiếm tiếng Việt có dấu/không dấu, không phân biệt hoa thường)',
    ],
    [
        'name' => 'slug',
        'type' => 'string',
        'stored' => true,
        'indexed' => true,
        'comment' => 'Slug địa điểm để tạo đường dẫn xem chi tiết',
    ],
    [
        'name' => 'city_name',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'comment' => 'Tên thành phố (Ví dụ: Đà Nẵng, Hà Nội)',
    ],
    [
        'name' => 'city_id',
        'type' => 'pint',
        'stored' => true,
        'indexed' => true,
        'comment' => 'ID thành phố để lọc chính xác',
    ],
    [
        'name' => 'category_name',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'comment' => 'Tên danh mục du lịch (Biển đảo, Di tích...)',
    ],
    [
        'name' => 'category_id',
        'type' => 'pint',
        'stored' => true,
        'indexed' => true,
        'comment' => 'ID danh mục để lọc chính xác',
    ],
    [
        'name' => 'price',
        'type' => 'pdouble',
        'stored' => true,
        'indexed' => true,
        'comment' => 'Giá vé vào cổng (entrance_fee) để lọc theo khoảng ngân sách',
    ],
    [
        'name' => 'rating',
        'type' => 'pfloat',
        'stored' => true,
        'indexed' => true,
        'comment' => 'Đánh giá sao để sắp xếp',
    ],
    [
        'name' => 'address',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'comment' => 'Địa chỉ chi tiết của địa điểm',
    ],
    [
        'name' => 'description',
        'type' => 'text_vi',
        'stored' => true,
        'indexed' => true,
        'comment' => 'Mô tả chi tiết địa điểm',
    ],
    [
        'name' => 'image_url',
        'type' => 'string',
        'stored' => true,
        'indexed' => false,
        'comment' => 'Đường dẫn ảnh đại diện hiển thị ra thẻ card',
    ],
];

// 4. Lấy danh sách trường hiện tại trong Solr
try {
    $currentSchema = solr_http_request('/schema/fields', 'GET');
    $existingFields = [];
    foreach ($currentSchema['fields'] ?? [] as $f) {
        $existingFields[$f['name']] = true;
    }
} catch (Exception $e) {
    echo "❌ Lỗi khi đọc schema hiện tại: " . $e->getMessage() . "\n";
    exit(1);
}

// 5. Lần lượt thêm mới hoặc cập nhật từng trường
$successCount = 0;
foreach ($fieldsToCreate as $field) {
    $fieldName = $field['name'];
    $fieldType = $field['type'];
    $stored = $field['stored'];
    $indexed = $field['indexed'];
    $comment = $field['comment'];

    $fieldConfig = [
        'name' => $fieldName,
        'type' => $fieldType,
        'stored' => $stored,
        'indexed' => $indexed,
    ];

    try {
        if (isset($existingFields[$fieldName])) {
            $action = 'replace-field';
            $payload = [$action => $fieldConfig];
            solr_http_request('/schema', 'POST', [], $payload);
            echo "🔄 Cập nhật trường [{$fieldName}] ({$fieldType}) - {$comment}\n";
        } else {
            $action = 'add-field';
            $payload = [$action => $fieldConfig];
            solr_http_request('/schema', 'POST', [], $payload);
            echo "➕ Thêm mới trường [{$fieldName}] ({$fieldType}) - {$comment}\n";
        }
        $successCount++;
    } catch (Exception $e) {
        echo "⚠️ Lỗi khi xử lý trường [{$fieldName}]: " . $e->getMessage() . "\n";
    }
}

echo "\n====================================================\n";
echo " HOÀN TẤT CẤU HÌNH SCHEMA: {$successCount}/" . count($fieldsToCreate) . " trường thành công!\n";
echo "====================================================\n";
