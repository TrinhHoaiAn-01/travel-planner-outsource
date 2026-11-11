<?php

namespace App\Services\Search\Home;

use App\Models\Destination;
use Illuminate\Support\Collection;
use PDO;

/**
 * Lớp/File xử lý tìm kiếm Trang chủ sử dụng SQL Index & CSDL MySQL.
 * Chạy song song để so sánh với Solr Engine.
 *
 * Cách chạy độc lập từ dòng lệnh:
 *   php app/Services/Search/Home/home_search_sql_index.php "Đà Nẵng"
 */
class HomeSearchSqlIndex
{
    /**
     * Thực hiện tìm kiếm điểm đến bằng CSDL MySQL (SQL Queries & Indexes).
     *
     * @param array $filters Gồm các tham số: keyword, category_id, budget, city_id
     * @return Collection Danh sách các Destination phù hợp
     */
    public static function search(array $filters = []): Collection
    {
        // 1. Chuẩn hóa tham số đầu vào
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        $categoryId = !empty($filters['category_id']) ? (int) $filters['category_id'] : null;
        $cityId = !empty($filters['city_id']) ? (int) $filters['city_id'] : null;
        $budget = !empty($filters['budget']) ? (int) $filters['budget'] : null;

        $query = Destination::query()
            ->with(['city', 'category', 'primaryImage', 'roomTypes']);

        // 2. Tìm kiếm theo từ khóa trong MySQL (sử dụng LIKE %...% đơn giản, chuẩn cho sinh viên hiểu)
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('address', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%")
                    ->orWhereHas('city', function ($cityQuery) use ($keyword) {
                        $cityQuery->where('name', 'LIKE', "%{$keyword}%");
                    });
            });
        }

        // 3. Lọc theo ID thành phố (tận dụng Index idx_destinations_city)
        if ($cityId !== null) {
            $query->where('city_id', $cityId);
        }

        // 4. Lọc theo ID danh mục (tận dụng Index idx_destinations_category)
        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        // 5. Lọc theo khoảng ngân sách (entrance_fee)
        if ($budget === 1) {
            $query->where('entrance_fee', '<', 500000);
        } elseif ($budget === 2) {
            $query->whereBetween('entrance_fee', [500000, 1500000]);
        } elseif ($budget === 3) {
            $query->where('entrance_fee', '>', 1500000);
        }

        // 6. Lọc nơi lưu trú nếu có yêu cầu
        if (!empty($filters['is_lodging'])) {
            $query->whereHas('roomTypes');
        }

        return $query->orderByDesc('rating')->orderBy('name')->get();
    }
}

// =========================================================================
// Hỗ trợ sinh viên chạy thử trực tiếp từ dòng lệnh CLI:
// php app/Services/Search/Home/home_search_sql_index.php "Đà Nẵng"
// =========================================================================
if (php_sapi_name() === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    $testKeyword = $argv[1] ?? 'Đà Nẵng';
    echo "====================================================\n";
    echo " CHẠY THỬ NGHIỆM TÌM KIẾM TRANG CHỦ BẰNG SQL INDEX\n";
    echo " Từ khóa: '{$testKeyword}'\n";
    echo "====================================================\n\n";

    // Đọc cấu hình MySQL từ .env
    $envFile = __DIR__ . '/../../../../.env';
    $envConfig = [];
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
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

    try {
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $sql = "
            SELECT d.id, d.name, c.name AS city_name, d.rating, COALESCE(d.entrance_fee, 0) AS price
            FROM destinations d
            LEFT JOIN cities c ON d.city_id = c.id
            WHERE d.name LIKE :kw1 
               OR d.address LIKE :kw2 
               OR d.description LIKE :kw3 
               OR c.name LIKE :kw4
            ORDER BY d.rating DESC, d.name ASC
            LIMIT 5
        ";

        $kw = "%{$testKeyword}%";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':kw1' => $kw, ':kw2' => $kw, ':kw3' => $kw, ':kw4' => $kw]);
        $rows = $stmt->fetchAll();

        echo "Tìm thấy " . count($rows) . " bản ghi mẫu bằng MySQL Query:\n\n";
        foreach ($rows as $idx => $r) {
            $price = number_format($r['price'], 0, ',', '.') . 'đ';
            echo ($idx + 1) . ". [ID: {$r['id']}] {$r['name']} - 📍 {$r['city_name']} | ⭐ {$r['rating']} | 💵 {$price}\n";
        }
    } catch (Exception $e) {
        echo "❌ Lỗi kết nối MySQL: " . $e->getMessage() . "\n";
    }

    echo "\n====================================================\n";
}

