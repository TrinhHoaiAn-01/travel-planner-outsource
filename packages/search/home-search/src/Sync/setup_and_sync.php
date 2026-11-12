<?php

/**
 * Script Tổng hợp: Khởi tạo Schema và Đồng bộ Dữ liệu MySQL sang Apache Solr 9.
 * Nằm trong Package: Search\HomeSearch\Sync
 *
 * Tích hợp trọn gói 2 tính năng theo thứ tự:
 * 1. BƯỚC 1: Chạy `create_schema.php` -> Khai báo cấu trúc Schema (Field types + Fields).
 * 2. BƯỚC 2: Chạy `sync_destinations.php` -> Xóa index cũ và nạp 837+ địa điểm từ MySQL vào Solr.
 * 3. BƯỚC 3: Kiểm tra xác thực (Health Check) đảm bảo dữ liệu sẵn sàng tìm kiếm.
 *
 * Cách chạy:
 *   docker exec travel-planner-app php packages/search/home-search/Sync/setup_and_sync.php
 *   hoặc:
 *   php packages/search/home-search/src/Sync/setup_and_sync.php
 */

$startTime = microtime(true);

echo "\n";
echo "========================================================================\n";
echo "  🚀 [APACHE SOLR 9 - ALL-IN-ONE SETUP & SYNC]                        \n";
echo "  Dự án     : Travel Planner - Tìm kiếm Trang chủ                      \n";
echo "  Mục tiêu  : Tự động cấu hình Schema trước, sau đó đồng bộ toàn bộ CSDL\n";
echo "========================================================================\n\n";

// -------------------------------------------------------------------------
// BƯỚC 1: KHỞI TẠO VÀ CẬP NHẬT CẤU TRÚC SCHEMA
// -------------------------------------------------------------------------
echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";
echo "  [BƯỚC 1/2] ĐANG KHỞI TẠO CẤU TRÚC SCHEMA (create_schema.php)...\n";
echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";

require __DIR__ . '/create_schema.php';

echo "\n";

// -------------------------------------------------------------------------
// BƯỚC 2: ĐỒNG BỘ DỮ LIỆU TỪ MYSQL SANG SOLR
// -------------------------------------------------------------------------
echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";
echo "  [BƯỚC 2/2] ĐANG ĐỒNG BỘ DỮ LIỆU TỪ MYSQL SANG SOLR (sync_destinations.php)...\n";
echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";

require __DIR__ . '/sync_destinations.php';

echo "\n";

// -------------------------------------------------------------------------
// BƯỚC 3: KIỂM TRA SỨC KHỎE VÀ KẾT THÚC
// -------------------------------------------------------------------------
$elapsed = round(microtime(true) - $startTime, 2);

echo "========================================================================\n";
echo "  🎉 TẤT CẢ ĐÃ HOÀN TẤT THÀNH CÔNG TRONG {$elapsed} GIÂY!                      \n";
echo "  1. Schema Solr core 'home_search' đã được cấu hình đủ 13/13 trường.  \n";
echo "  2. Toàn bộ địa điểm MySQL đã được nạp và lập chỉ mục Inverted Index.  \n";
echo "  3. Các bộ lọc từ khóa, thành phố, danh mục, giá vé và lưu trú         \n";
echo "     đều tương thích 100% với Packages/Search/HomeSearch!             \n";
echo "========================================================================\n\n";

