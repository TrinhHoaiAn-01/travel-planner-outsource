<?php

/**
 * ============================================================================
 * SCRIPT DONG BO TOAN BO HE THONG TIM KIEM WEBSITE (WEBSITE SEARCH)
 * Trang/Phan he: Tong hop tat ca 10 phan he chuc nang cua Website
 * Core Solr: admin_system_search
 * ============================================================================
 *
 * Nhiem vu:
 * Lan luot thuc thi cac script dong bo rieng biet cho tung phan he:
 * 1. sync_destinations.php  (Quan ly Diem den)
 * 2. sync_cities.php        (Quan ly Tinh / Thanh pho)
 * 3. sync_categories.php    (Quan ly Danh muc)
 * 4. sync_room_types.php    (Quan ly Hang phong luu tru)
 * 5. sync_trips.php         (Quan ly Chuyen di ca nhan)
 * 6. sync_users.php         (Quan ly Nguoi dung & Thanh vien)
 * 7. sync_bookings.php      (Quan ly Don dat phong)
 * 8. sync_payments.php      (Quan ly Giao dich Thanh toan)
 * 9. sync_reviews.php       (Kiem duyet Danh gia)
 * 10. sync_activity_logs.php (Nhat ky Hoat dong)
 */

require_once __DIR__ . '/bootstrap.php';

echo "========================================================================\n";
echo "DONG BO TOAN BO 10 PHAN HE CHUC NANG VAO CORE 'admin_system_search'\n";
echo "========================================================================\n\n";

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

echo "[HOAN TAT] Da dong bo toan bo 10 phan he chuc nang vao core 'admin_system_search'.\n\n";
