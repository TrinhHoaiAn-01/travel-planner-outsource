# Hướng dẫn Tìm kiếm Trang chủ: Solr 9 & SQL Index (Dành cho Sinh viên)

Thư mục này triển khai hệ sinh thái tìm kiếm cho **Trang chủ** của dự án Travel Planner, hỗ trợ chuyển đổi linh hoạt giữa 2 công nghệ tìm kiếm:
1. **Apache Solr 9** (Search Engine chuyên dụng chạy trong Docker, Core: `home_search`).
2. **SQL Index** (Tìm kiếm dựa trên cơ sở dữ liệu MySQL thông thường với B-Tree Index).

Cấu trúc được thiết kế trực quan, dễ hiểu theo đúng chuẩn Service Layer trong kiến trúc MVC:

```text
app/Services/Search/Home/
├── solr.php                   # File dùng chung: Quản lý kết nối & giao tiếp cURL/HTTP với Solr 9
├── create_schema.php          # Bước 1: Cấu hình Schema cho core home_search (chạy 1 lần hoặc khi cập nhật)
├── sync_destinations.php      # Bước 2: Đọc dữ liệu từ MySQL và đồng bộ sang Solr (chạy khi cần sync)
├── home_search.php            # Bước 3 (Solr): Xử lý tìm kiếm bằng Solr Engine (Full-text, Fuzzy, Boost)
├── home_search_sql_index.php  # Bước 3 (SQL): Xử lý tìm kiếm bằng MySQL Index & câu lệnh LIKE
└── README.md                  # Tài liệu hướng dẫn thực hành cho sinh viên
```

---

## 1. So sánh Solr 9 vs SQL Index

| Tiêu chí | Apache Solr 9 (`home_search.php`) | SQL Index (`home_search_sql_index.php`) |
| :--- | :--- | :--- |
| **Bản chất** | Inverted Index (chỉ mục đảo) chuyên cho tìm kiếm văn bản | Truy vấn MySQL truyền thống với `LIKE %...%` |
| **Tìm kiếm từ khóa** | ✅ Tìm kiếm toàn văn (Full-Text), tính điểm độ liên quan | ⚠️ So khớp chuỗi con đơn giản (`LIKE '%keyword%'`) |
| **Gõ sai chính tả (Fuzzy)** | ✅ Hỗ trợ tốt (gõ `mesi` vẫn tìm ra `messi`) | ❌ Không hỗ trợ (gõ `mesi` ra 0 kết quả) |
| **Tiếng Việt không dấu** | ✅ Tự động phân tích (gõ `da nang` ra `Đà Nẵng`) | ⚠️ Hạn chế, phụ thuộc vào Collation của MySQL |
| **Xếp hạng độ liên quan** | ✅ Chấm điểm TF-IDF / BM25, ưu tiên từ khóa ở tiêu đề hơn mô tả | ❌ Không tính điểm liên quan, sắp xếp theo rating/tên |
| **Tốc độ khi dữ liệu lớn** | ⚡ Rất nhanh vì chỉ tìm trên chỉ mục văn bản đã tối ưu | 🐢 Chậm khi dữ liệu triệu dòng vì Table Scan |

---

## 2. Thao tác Thực hành Dành cho Sinh viên

### Bước 1: Cấu hình Schema cho Solr (Chạy 1 lần ban đầu)
File `create_schema.php` sẽ gọi Schema API của Solr 9 để khai báo kiểu dữ liệu `text_vi` (có `ASCIIFoldingFilterFactory`) và các trường: `name`, `city_name`, `category_name`, `price`, `rating`, `address`, `description`...

```bash
php app/Services/Search/Home/create_schema.php
```

### Bước 2: Đồng bộ Dữ liệu từ MySQL sang Solr
File `sync_destinations.php` sẽ kết nối MySQL, nạp 800+ địa điểm vào Solr core `home_search`:

```bash
php app/Services/Search/Home/sync_destinations.php
```

### Bước 3: Chạy Thử nghiệm Trực tiếp từ CLI

**A. Thử nghiệm với Solr Engine (`home_search.php`):**
```bash
# Tìm kiếm từ khóa chuẩn hoặc sai chính tả
php app/Services/Search/Home/home_search.php "Đà Nẵng"
php app/Services/Search/Home/home_search.php "mesi"
```

**B. Thử nghiệm với SQL Index (`home_search_sql_index.php`):**
```bash
# Tìm kiếm trực tiếp bằng câu lệnh SQL Query
php app/Services/Search/Home/home_search_sql_index.php "Đà Nẵng"
php app/Services/Search/Home/home_search_sql_index.php "messi"
```

---

## 3. Tích hợp Dropdown Selector tại Trang chủ (`HomeController`)

Trên giao diện thanh tìm kiếm ở Trang chủ (`resources/views/home.blade.php`), người dùng có thể chọn trực tiếp giữa **Solr** hoặc **SQL**:

```html
<select name="engine" id="homeSearchEngine">
    <option value="solr">Solr</option>
    <option value="sql">SQL</option>
</select>
```

Trong `app/Http/Controllers/HomeController.php`, Controller sẽ điều phối:

```php
$engine = strtolower((string) $request->input('engine', 'solr'));

if ($hasSearch) {
    $filters = $request->only(['keyword', 'category_id', 'budget', 'city_id']);
    $searchResults = ($engine === 'sql')
        ? HomeSearchSqlIndex::search($filters)
        : HomeSearch::search($filters);
}
```
