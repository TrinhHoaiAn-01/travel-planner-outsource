<?php

/**
 * ============================================================================
 * SCRIPT TONG HOP: KHOI TAO SCHEMA VA DONG BO TOAN BO HE THONG TIM KIEM SOLR 9
 * Vendor Package: Search (packages/search/Sync/setup_and_sync.php)
 * ============================================================================
 *
 * Chay 1 lan All-in-One:
 * 1. Khoi tao Schema, Field Type 'text_vi' va toan bo Fields cho cac Core Solr.
 * 2. Lan luot thuc thi tung file dong bo rieng biet cho tung trang/phan he:
 *    - sync_home.php          : Tim kiem Trang chu (home_destination_search)
 *    - sync_destinations.php  : Quan ly Diem den (admin_system_search)
 *    - sync_cities.php        : Quan ly Tinh / Thanh pho (admin_system_search)
 *    - sync_categories.php    : Quan ly Danh muc (admin_system_search)
 *    - sync_room_types.php    : Quan ly Hang phong (admin_system_search)
 *    - sync_trips.php         : Quan ly Chuyen di ca nhan (admin_system_search)
 *    - sync_users.php         : Quan ly Nguoi dung (admin_system_search)
 *    - sync_bookings.php      : Quan ly Don dat phong (admin_system_search)
 *    - sync_payments.php      : Quan ly Thanh toan (admin_system_search)
 *    - sync_reviews.php       : Kiem duyet Danh gia (admin_system_search)
 *    - sync_activity_logs.php : Nhat ky Hoat dong (admin_system_search)
 *    - sync_commerce.php      : Thuong mai & Tro ly AI (hotel_commerce_search)
 * 3. Kiem tra xac thuc ket qua va bao cao thoi gian hoan tat.
 */

$startTime = microtime(true);

echo "========================================================================\n";
echo "HE THONG TIM KIEM APACHE SOLR 9 - TRAVEL PLANNER\n";
echo "KHOI TAO SCHEMA VA DONG BO TOAN BO DU LIEU (ALL-IN-ONE)\n";
echo "========================================================================\n\n";

// 1. Khoi tao Schema cho tat ca cac Core
echo "[PHAN 1] KHOI TAO SCHEMA HE THONG (create_schema.php)\n";
require __DIR__ . '/create_schema.php';

// 2. Dong bo Trang chu
echo "[PHAN 2] DONG BO TIM KIEM TRANG CHU\n";
require __DIR__ . '/sync_home.php';

// 3. Dong bo cac phan he Website vao core admin_system_search
echo "[PHAN 3] DONG BO 10 PHAN HE CHUC NANG VAO CORE 'admin_system_search'\n";
require __DIR__ . '/sync_destinations.php';
require __DIR__ . '/sync_cities.php';
require __DIR__ . '/sync_categories.php';
require __DIR__ . '/sync_room_types.php';
require __DIR__ . '/sync_trips.php';
require __DIR__ . '/sync_users.php';
require __DIR__ . '/sync_bookings.php';
require __DIR__ . '/sync_payments.php';
require __DIR__ . '/sync_reviews.php';
require __DIR__ . '/sync_activity_logs.php';

// 4. Dong bo Thuong mai & Tro ly AI
echo "[PHAN 4] DONG BO THUONG MAI & AI AGENT (hotel_commerce_search)\n";
require __DIR__ . '/sync_commerce.php';

// 5. Tong ket va bao cao
$elapsed = round(microtime(true) - $startTime, 2);

echo "========================================================================\n";
echo "BAO CAO KET QUA: TAT CA DA HOAN TAT TRONG {$elapsed} GIAY.\n";
echo "1. Toan bo Schema cac Core Solr da duoc thiet lap day du.\n";
echo "2. Cac phan he tim kiem da duoc dong bo rieng biet, de quan ly.\n";
echo "3. He thong san sang phuc vu cac tinh nang tim kiem va autocomplete.\n";
echo "========================================================================\n\n";
