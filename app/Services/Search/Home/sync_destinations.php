<?php

/**
 * File đồng bộ dữ liệu từ CSDL MySQL sang Apache Solr 9 (Core: home_search).
 * Đọc dữ liệu từ bảng destinations (kèm cities, categories, destination_images),
 * chuyển đổi thành cấu trúc Solr Document và nạp vào Solr qua endpoint /update.
 *
 * Cách chạy từ dòng lệnh:
 *   php app/Services/Search/Home/sync_destinations.php
 */

require_once __DIR__ . '/solr.php';

echo "====================================================\n";
echo " BẮT ĐẦU ĐỒNG BỘ DỮ LIỆU MYSQL SANG SOLR 9 (home_search)\n";
echo " Solr URL: " . solr_get_base_url() . "\n";
echo "====================================================\n\n";

// 1. Kiểm tra kết nối tới Solr
if (!solr_ping()) {
    echo "❌ LỖI: Không thể kết nối tới Solr tại [" . solr_get_base_url() . "]!\n";
    exit(1);
}
echo "✅ Kết nối tới Solr thành công!\n";

// 2. Hàm lấy kết nối CSDL MySQL (Hỗ trợ cả môi trường Laravel và chạy Script độc lập)
function get_mysql_connection(): PDO
{
    // Nếu đang chạy trong môi trường Laravel đã nạp
    if (class_exists('\Illuminate\Support\Facades\DB')) {
        try {
            return \Illuminate\Support\Facades\DB::connection()->getPdo();
        } catch (\Throwable $e) {
            // Chuyển sang fallback bên dưới
        }
    }

    // Đọc file .env nếu có để lấy cấu hình kết nối
    $envFile = __DIR__ . '/../../../../.env';
    $envConfig = [];
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $envConfig[trim($k)] = trim($v, " \t\n\r\0\x0B\"'");
            }
        }
    }

    $isDocker = file_exists('/.dockerenv');
    $host = $isDocker ? 'mysql' : ($envConfig['DB_HOST'] ?? '127.0.0.1');
    $port = $isDocker ? '3306' : ($envConfig['DB_PORT'] ?? '3307');
    $dbName = $envConfig['DB_DATABASE'] ?? 'travel_planner';
    $username = $envConfig['DB_USERNAME'] ?? 'root';
    $password = $envConfig['DB_PASSWORD'] ?? 'root@admin';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

try {
    $pdo = get_mysql_connection();
    echo "✅ Kết nối tới CSDL MySQL thành công!\n\n";
} catch (Exception $e) {
    echo "❌ LỖI: Không thể kết nối MySQL: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Xóa dữ liệu cũ trên Solr (nếu muốn làm mới hoàn toàn)
echo "🧹 Đang làm sạch chỉ mục cũ trong core home_search...\n";
solr_clear_all();
echo "✅ Đã làm sạch chỉ mục cũ!\n\n";

// 4. Truy vấn lấy toàn bộ địa điểm du lịch từ MySQL
$sql = "
    SELECT 
        d.id,
        d.name,
        d.slug,
        d.description,
        d.address,
        COALESCE(d.entrance_fee, 0) AS price,
        COALESCE(d.rating, 0) AS rating,
        d.city_id,
        c.name AS city_name,
        d.category_id,
        cat.name AS category_name,
        (
            SELECT di.image_path 
            FROM destination_images di 
            WHERE di.destination_id = d.id 
            ORDER BY di.is_primary DESC, di.id ASC 
            LIMIT 1
        ) AS primary_image
    FROM destinations d
    LEFT JOIN cities c ON d.city_id = c.id
    LEFT JOIN categories cat ON d.category_id = cat.id
    ORDER BY d.id ASC
";

$stmt = $pdo->query($sql);
$totalRows = 0;
$batchSize = 200;
$batchDocs = [];

echo "📦 Bắt đầu nạp và đồng bộ dữ liệu sang Solr...\n";

while ($row = $stmt->fetch()) {
    $totalRows++;

    // Ánh xạ thành 1 Solr Document
    $batchDocs[] = [
        'id' => (string) $row['id'],
        'name' => (string) $row['name'],
        'slug' => (string) ($row['slug'] ?? ''),
        'city_name' => (string) ($row['city_name'] ?? ''),
        'city_id' => (int) ($row['city_id'] ?? 0),
        'category_name' => (string) ($row['category_name'] ?? ''),
        'category_id' => (int) ($row['category_id'] ?? 0),
        'price' => (float) $row['price'],
        'rating' => (float) $row['rating'],
        'address' => (string) ($row['address'] ?? ''),
        'description' => strip_tags((string) ($row['description'] ?? '')),
        'image_url' => (string) ($row['primary_image'] ?: 'https://images.unsplash.com/photo-1528127269322-539801943592?w=600'),
    ];

    // Gửi lên Solr theo từng lô (Batch)
    if (count($batchDocs) >= $batchSize) {
        solr_add_documents($batchDocs);
        echo "   -> Đã đẩy " . count($batchDocs) . " bản ghi (Tổng cộng: {$totalRows})...\n";
        $batchDocs = [];
    }
}

// Đẩy nốt số bản ghi còn lại
if (!empty($batchDocs)) {
    solr_add_documents($batchDocs);
    echo "   -> Đã đẩy " . count($batchDocs) . " bản ghi cuối cùng (Tổng cộng: {$totalRows})...\n";
}

echo "\n====================================================\n";
echo "🎉 HOÀN THÀNH ĐỒNG BỘ: Đã đồng bộ {$totalRows} địa điểm vào Solr core 'home_search'!\n";
echo "====================================================\n";
