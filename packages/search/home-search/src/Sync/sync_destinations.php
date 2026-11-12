<?php

/**
 * Script Đồng bộ dữ liệu từ CSDL MySQL sang Apache Solr 9 (Core: home_search).
 * Nằm trong Package: Search\HomeSearch\Sync
 *
 * Mục đích:
 * 1. Đọc toàn bộ dữ liệu địa điểm du lịch từ bảng `destinations` trong MySQL.
 * 2. Kết hợp thông tin Thành phố (`cities`), Danh mục (`categories`), Ảnh đại diện (`destination_images`)
 *    và kiểm tra có phòng lưu trú hay không (`room_types`).
 * 3. Chuẩn hóa thành các Solr Document có cấu trúc tương thích 100% với `SolrHomeSearchEngine`.
 * 4. Nạp dữ liệu vào Solr theo từng lô (Batch 200 bản ghi) qua endpoint `/update?commit=true`.
 *
 * Cách chạy:
 *   docker exec travel-planner-app php packages/search/home-search/src/Sync/sync_destinations.php
 *   hoặc:
 *   php packages/search/home-search/src/Sync/sync_destinations.php
 */

// Xác định đường dẫn máy chủ Solr
$solrBaseUrl = rtrim(getenv('SOLR_URL') ?: (file_exists('/.dockerenv') ? 'http://solr:8983/solr/home_search' : 'http://127.0.0.1:8983/solr/home_search'), '/');

echo "====================================================================\n";
echo "  [SOLR 9 DATA SYNCHRONIZATION] - ĐỒNG BỘ DỮ LIỆU MYSQL SANG SOLR 9\n";
echo "  Core Target : home_search\n";
echo "  Solr URL    : {$solrBaseUrl}\n";
echo "====================================================================\n\n";

if (!function_exists('solr_sync_request')) {
    /**
     * Hàm gửi yêu cầu HTTP tới Solr API bằng cURL.
     */
    function solr_sync_request(string $url, string $method = 'GET', ?array $body = null): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
            throw new RuntimeException("Lỗi kết nối tới Solr tại {$url}: {$error}");
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("Phản hồi Solr không hợp lệ (HTTP {$httpCode}): {$response}");
        }

        return $decoded;
    }
}

if (!function_exists('get_mysql_pdo_connection')) {
    /**
     * Hàm thiết lập kết nối PDO tới CSDL MySQL.
     * Tự động đọc cấu hình từ .env và hỗ trợ cả môi trường Docker lẫn máy Host.
     */
    function get_mysql_pdo_connection(): PDO
    {
        // 1. Nếu đang chạy trong ngữ cảnh Laravel Framework đã khởi tạo
        if (class_exists('\Illuminate\Support\Facades\DB')) {
            try {
                return \Illuminate\Support\Facades\DB::connection()->getPdo();
            } catch (Throwable $e) {
                // Chuyển sang đọc file .env thủ công
            }
        }

        // 2. Tìm file .env của dự án
        $possibleEnvPaths = [
            __DIR__ . '/../../../../../.env',
            __DIR__ . '/../../../../.env',
            dirname(__DIR__, 5) . '/.env',
            getcwd() . '/.env',
        ];

        $envConfig = [];
        foreach ($possibleEnvPaths as $envPath) {
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
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
                break;
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
}

// 1. Kiểm tra kết nối Solr
try {
    $ping = solr_sync_request("{$solrBaseUrl}/select?q=*:*&rows=0&wt=json");
    if (!isset($ping['response']['numFound'])) {
        throw new RuntimeException("Solr không phản hồi đúng.");
    }
    echo "✅ [1/4] Kết nối máy chủ Solr thành công!\n";
} catch (Throwable $e) {
    echo "❌ LỖI: Không thể kết nối tới Apache Solr tại [{$solrBaseUrl}]!\n";
    echo "   Nguyên nhân: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Kết nối MySQL
try {
    $pdo = get_mysql_pdo_connection();
    echo "✅ [2/4] Kết nối CSDL MySQL thành công!\n\n";
} catch (Throwable $e) {
    echo "❌ LỖI: Không thể kết nối CSDL MySQL!\n";
    echo "   Nguyên nhân: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Làm sạch chỉ mục cũ trên Solr để tránh trùng lặp dữ liệu
echo "🧹 [3/4] Đang làm sạch toàn bộ chỉ mục cũ trong core 'home_search'...\n";
try {
    solr_sync_request("{$solrBaseUrl}/update?commit=true", 'POST', [
        'delete' => ['query' => '*:*'],
    ]);
    echo "   ✅ Đã làm sạch toàn bộ chỉ mục cũ!\n\n";
} catch (Throwable $e) {
    echo "   ⚠️ Cảnh báo khi làm sạch chỉ mục: " . $e->getMessage() . "\n\n";
}

// 4. Truy vấn lấy toàn bộ dữ liệu địa điểm du lịch từ MySQL
echo "📦 [4/4] Bắt đầu nạp và đồng bộ dữ liệu địa điểm từ MySQL sang Solr...\n";

$sql = "
    SELECT 
        d.id,
        d.name,
        d.slug,
        d.description,
        d.address,
        COALESCE(d.entrance_fee, 0) AS entrance_fee,
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
        ) AS primary_image,
        EXISTS (
            SELECT 1 
            FROM room_types rt 
            WHERE rt.destination_id = d.id
        ) AS has_hotel
    FROM destinations d
    LEFT JOIN cities c ON d.city_id = c.id
    LEFT JOIN categories cat ON d.category_id = cat.id
    ORDER BY d.id ASC
";

try {
    $stmt = $pdo->query($sql);
    $totalRows = 0;
    $batchSize = 200;
    $batchDocs = [];

    while ($row = $stmt->fetch()) {
        $totalRows++;

        // Chuẩn hóa thành 1 Document tương thích 100% với SolrHomeSearchEngine
        $batchDocs[] = [
            'id' => (string) $row['id'],
            'name' => (string) $row['name'],
            'slug' => (string) ($row['slug'] ?? ''),
            'city_name' => (string) ($row['city_name'] ?? ''),
            'city_id' => (int) ($row['city_id'] ?? 0),
            'category_name' => (string) ($row['category_name'] ?? ''),
            'category_id' => (int) ($row['category_id'] ?? 0),
            'entrance_fee' => (float) $row['entrance_fee'],
            'price' => (float) $row['entrance_fee'], // Giữ trường price để tương thích ngược
            'has_hotel' => (bool) $row['has_hotel'],   // Trường boolean cho bộ lọc lưu trú
            'rating' => (float) $row['rating'],
            'address' => (string) ($row['address'] ?? ''),
            'description' => strip_tags((string) ($row['description'] ?? '')),
            'image_url' => (string) ($row['primary_image'] ?: 'https://images.unsplash.com/photo-1528127269322-539801943592?w=600'),
        ];

        // Đẩy lên Solr theo từng lô (Batch)
        if (count($batchDocs) >= $batchSize) {
            solr_sync_request("{$solrBaseUrl}/update?commit=true", 'POST', $batchDocs);
            echo "   -> Đã đẩy " . count($batchDocs) . " bản ghi (Lũy kế: {$totalRows})...\n";
            $batchDocs = [];
        }
    }

    // Đẩy nốt số bản ghi còn lại trong mảng
    if (!empty($batchDocs)) {
        solr_sync_request("{$solrBaseUrl}/update?commit=true", 'POST', $batchDocs);
        echo "   -> Đã đẩy " . count($batchDocs) . " bản ghi cuối cùng (Lũy kế: {$totalRows})...\n";
    }

    echo "\n====================================================================\n";
    echo "🎉 HOÀN THÀNH ĐỒNG BỘ: Đã đồng bộ {$totalRows} địa điểm vào Solr core 'home_search'!\n";
    echo "   Tất cả các bộ lọc: Từ khóa, Thành phố, Danh mục, Ngân sách (entrance_fee), Lưu trú (has_hotel)\n";
    echo "   đều đã sẵn sàng và tương thích 100% với Packages/Search/HomeSearch.\n";
    echo "====================================================================\n";
} catch (Throwable $e) {
    echo "❌ LỖI trong quá trình đồng bộ: " . $e->getMessage() . "\n";
    exit(1);
}

