-- BỔ SUNG DỮ LIỆU MẪU (28/10/2026)
-- Địa danh và tên cơ sở lưu trú là các địa điểm có thật, dùng để thử nghiệm tìm kiếm/đặt lịch.
-- Dữ liệu reviews/expenses/payments/room_types được MÔ PHỎNG, KHÔNG phải trải nghiệm,
-- giao dịch, giá bán, số phòng hay đánh giá thực của người dùng/đơn vị kinh doanh.
-- Ảnh Unsplash bên dưới mang tính MINH HỌA THEO CHỦ ĐỀ, không phải ảnh đã xác minh đúng địa danh.
-- entrance_fee=0 và giờ mở cửa=NULL: CHƯA XÁC MINH thông tin, KHÔNG khẳng định miễn phí.
-- Dùng REG-xxx như mã vùng du lịch nội bộ, không phải mã đơn vị hành chính.
-- Một số khu vực du lịch vẫn sử dụng địa danh quen thuộc trước thay đổi hành chính 2025.
-- Chạy trên cơ sở dữ liệu DEMO. Bản SQL đầy đủ có DROP TABLE, sẽ xóa bảng đang có.
-- Nguồn tham khảo: https://vietnam.travel/ ; https://vietnamtourism.gov.vn/
-- Măng Đen: https://vietnam.travel/things-to-do/discover-mang-den-vietnam/
-- ============================================================================

-- DỮ LIỆU DEMO DU LỊCH VIỆT NAM - BỔ SUNG NGÀY 24/10/2026
-- Bổ sung 20 khu vực trong cities và 80 địa điểm có thật trong destinations.
-- Bản 24/10 có 59 khu vực và 305 điểm đến; bản 28/10 có 79 khu vực và 425 điểm đến.
-- Tên bảng đúng trong tệp là cities (không phải citites).
-- cities dùng làm nhóm du lịch theo địa danh, không phải danh mục hành chính hiện hành.
-- Tên, mô tả, địa chỉ viết bằng tiếng Việt; giữ nguyên tên riêng của thương hiệu.
-- Giữ ID, slug và các liên kết của dữ liệu cũ khi Việt hóa nội dung.
-- Dữ liệu thêm mới: entrance_fee=0 chỉ là giá trị mặc định do cột NOT NULL,
-- KHÔNG có nghĩa miễn phí; opening_time/closing_time=NULL nghĩa là chưa có thông tin.
-- rating=0 nghĩa là chưa có đánh giá; duration là thời lượng demo ước tính, đơn vị phút.
-- Giá vé, giờ mở cửa, điểm đánh giá trong các dòng cũ là mẫu chưa được xác minh.
-- CẢNH BÁO: Đây là bản dump đầy đủ có DROP TABLE, chỉ nhập vào CSDL demo/thử nghiệm.
-- Nguồn đối chiếu địa danh (không sao chép mô tả; không xác nhận giá vé/giờ hoạt động):
-- https://dulichdienbien.vn/vi
-- https://laichau.gov.vn/du-khach/du-lich-lai-chau-nhung-diem-den-hap-dan-du-khach.html
-- https://dulichlaichau.vn/mypicture/Files/Tap-gap-tour-Lai-Chau-15x20-05072020.pdf
-- https://www.vietnam.travel/node/1559
-- https://dulichyenbai.gov.vn/cac-diem-du-lich-dac-sac/?UserKey=Ngo-ngang-Tu-Le
-- https://vietnamtourism.gov.vn/post/63165
-- https://dulichbacninh.gov.vn/kham-pha/vung-dat-con-nguoi-bac-ninh/du-lich-bac-ninh-dau-nam-nhieu-tin-hieu-vui-1149.html
-- https://vietnamtourism.gov.vn/post/67244
-- https://vietnamtourism.gov.vn/printer/62108?type=1
-- https://vietnamtourism.vn/index.php/tourism/items/1712/2
-- https://vietnamtourism.vn/index.php/tourism/items/1249/2
-- https://quangngai.gov.vn/du-lich/tong-quan-du-lich-quang-ngai
-- https://scov.gov.vn/dat-nuoc-con-nguoi/ky-thu-tam-ky.html
-- https://vietnamtourism.gov.vn/en/post/19680
-- https://dulich.daknong.gov.vn/vi/tour/details/tour-ta-dung-bao-loc-dambri-linh-quy-lua-trai-nui-rung-bbq-thit-nuong-2
-- https://vietnamtourism.gov.vn/post/57630
-- https://vietnamtourism.gov.vn/post/39448
-- https://vietnamtourism.gov.vn/printer/48742?type=1
-- https://bvhttdl.gov.vn/danh-thuc-tiem-nang-du-lich-20250908100511814.htm
-- https://vietnamtourism.vn/index.php/tourism/items/1677
-- https://rachgia.angiang.gov.vn/gioi-thieu-ve-thanh-pho-rach-gia
-- https://vietnamtourism.vn/index.php/tourism/items/2676

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 21, 2026 at 05:27 AM
-- Server version: 9.7.2
-- PHP Version: 8.5.11

SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `travel_planner`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_logs_user_created` (`user_id`,`created_at`),
  KEY `idx_logs_subject` (`subject_type`,`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `trip_id` bigint UNSIGNED DEFAULT NULL,
  `room_type_id` bigint UNSIGNED NOT NULL,
  `room_count` int NOT NULL DEFAULT '1',
  `check_in_date` date NOT NULL,
  `check_out_date` date NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `booking_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bookings_code` (`booking_code`),
  KEY `idx_bookings_user` (`user_id`),
  KEY `idx_bookings_trip` (`trip_id`),
  KEY `idx_bookings_availability` (`room_type_id`,`status`,`check_in_date`,`check_out_date`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `trip_id`, `room_type_id`, `room_count`, `check_in_date`, `check_out_date`, `total_price`, `status`, `booking_code`, `created_at`, `updated_at`) VALUES
(1, 275, 43, 42, 2, '2026-11-15', '2026-11-17', 3600000.00, 'pending', 'BK-2026-DANANG', '2026-10-18 23:24:46', '2026-10-18 23:24:46'),
(2, 275, 45, 43, 1, '2026-11-20', '2026-11-22', 3000000.00, 'confirmed', 'BK-2026-HALONG', '2026-10-17 02:24:46', '2026-10-18 02:24:46'),
(3, 275, 46, 42, 1, '2026-08-15', '2026-08-17', 1800000.00, 'completed', 'BK-2026-SAPA', '2026-08-01 03:00:00', '2026-08-17 05:00:00'),
(4, 275, 44, 44, 1, '2026-10-10', '2026-10-12', 5600000.00, 'cancelled', 'BK-2026-PHUQUOC', '2026-10-01 02:30:00', '2026-10-02 07:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categories_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(106, 'Nature', 'nature', NULL, 'Beaches, mountains, lakes and outdoor attractions.', 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39'),
(107, 'Culture & History', 'culture-history', NULL, 'Museums, monuments, temples and heritage sites.', 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39'),
(108, 'Food & Drink', 'food-drink', NULL, 'Local markets, restaurants and culinary experiences.', 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39'),
(109, 'Entertainment', 'entertainment', NULL, 'Theme parks, shows and family activities.', 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39'),
(110, 'Shopping', 'shopping', NULL, 'Markets, malls and local craft stores.', 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39'),
(111, 'Nghỉ dưỡng & Resort', 'resort-spa', 'hotel', 'Trải nghiệm kỳ nghỉ đẳng cấp tại các resort, spa cao cấp ven biển và trung tâm.', 1, '2026-10-19 02:24:42', '2026-10-19 02:24:42'),
(112, 'Biển & Đảo', 'bien-dao', 'waves', 'Khám phá những bãi biển cát trắng nắng vàng, làn nước xanh trong và đảo hoang sơ tuyệt đẹp.', 1, '2026-10-19 02:24:42', '2026-10-19 02:24:42'),
(113, 'Núi Rừng & Trekking', 'nui-rung', 'tree', 'Chinh phục những đỉnh núi hùng vĩ, cung đường trekking thử thách và thiên nhiên kỳ thú.', 1, '2026-10-19 02:24:43', '2026-10-19 02:24:43'),
(114, 'Di Sản Văn Hóa', 'di-san-van-hoa', 'landmark', 'Tìm hiểu lịch sử ngàn năm, đền đài cổ kính, phố cổ trầm mặc và di sản thế giới.', 1, '2026-10-19 02:24:44', '2026-10-19 02:24:44');

-- --------------------------------------------------------

--

-- Dữ liệu bổ sung cho categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(115, 'Hồ, Sông & Thác', 'ho-song-thac', 'waves', 'Hồ nước, sông ngòi, thác nước và các điểm ngắm cảnh ven bờ.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(116, 'Hang động & Địa chất', 'hang-dong-dia-chat', 'mountain', 'Hang động tự nhiên, cao nguyên đá và thắng cảnh địa chất.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(117, 'Du lịch sinh thái', 'du-lich-sinh-thai', 'leaf', 'Công viên thiên nhiên, khu bảo tồn và các trải nghiệm sinh thái.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(118, 'Làng nghề truyền thống', 'lang-nghe-truyen-thong', 'paintbrush', 'Làng nghề thủ công, nghệ thuật và trải nghiệm sản xuất địa phương.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(119, 'Tâm linh & Tín ngưỡng', 'tam-linh-tin-nguong', 'church', 'Chùa, đền, nhà thờ và địa điểm sinh hoạt tín ngưỡng.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(120, 'Bảo tàng & Triển lãm', 'bao-tang-trien-lam', 'museum', 'Bảo tàng, nhà trưng bày và không gian giới thiệu di sản.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(121, 'Nông nghiệp & Miệt vườn', 'nong-nghiep-miet-vuon', 'sprout', 'Vườn cây, nông trại và sinh hoạt văn hóa nông nghiệp.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(122, 'Du lịch cộng đồng', 'du-lich-cong-dong', 'users', 'Bản làng, trải nghiệm văn hóa địa phương và lưu trú cộng đồng.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(123, 'Đường đèo & Ngắm cảnh', 'duong-deo-ngam-canh', 'binoculars', 'Đèo núi, đài quan sát và những cung đường ngắm cảnh.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(124, 'Thể thao & Phiêu lưu', 'the-thao-phieu-luu', 'bike', 'Hoạt động vận động ngoài trời, khám phá và phiêu lưu.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(125, 'Đảo nhỏ & Lặn biển', 'dao-nho-lan-bien', 'ship', 'Khám phá đảo, bãi biển nhỏ và hệ sinh thái ven biển.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(126, 'Công viên & Không gian xanh', 'cong-vien-khong-gian-xanh', 'trees', 'Công viên, vườn thực vật và không gian công cộng xanh.', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00');

-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
CREATE TABLE IF NOT EXISTS `cities` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cities_slug` (`slug`),
  UNIQUE KEY `cities_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `slug`, `code`, `region`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(114, 'Hà Nội', 'ha-noi', 'CTY-HAN', 'Miền Bắc', 'Thủ đô với phố cổ, hồ Hoàn Kiếm, di tích ngàn năm và văn hóa ẩm thực đường phố.', NULL, 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39'),
(115, 'Đà Nẵng', 'da-nang', 'CTY-DAD', 'Miền Trung', 'Thành phố ven biển với bán đảo Sơn Trà, Ngũ Hành Sơn và Bà Nà Hills.', NULL, 1, '2026-10-19 02:24:39', '2026-10-19 02:24:41'),
(116, 'Hội An', 'hoi-an', 'CTY-HOI', 'Miền Trung', 'Đô thị di sản bên sông Hoài với kiến trúc thương cảng cổ và làng nghề truyền thống.', NULL, 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39'),
(117, 'Thành phố Hồ Chí Minh', 'ho-chi-minh-city', 'CTY-SGN', 'Miền Nam', 'Đô thị lớn ở miền Nam, nổi bật với kiến trúc lịch sử, bảo tàng và ẩm thực đa dạng.', NULL, 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39'),
(118, 'Đà Lạt', 'da-lat', 'CTY-DLI', 'Tây Nguyên', 'Thành phố cao nguyên có rừng thông, hồ, vườn hoa và khí hậu mát mẻ.', NULL, 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39'),
(119, 'Hạ Long', 'quang-ninh-ha-long', 'CTY-HLG', 'Miền Bắc', 'Cửa ngõ vịnh Hạ Long, nổi tiếng với đảo đá vôi, hang động và du thuyền.', NULL, 1, '2026-10-19 02:24:41', '2026-10-19 02:24:41'),
(120, 'Phú Quốc', 'phu-quoc-kien-giang', 'CTY-PQC', 'Miền Nam', 'Điểm đến đảo với bãi biển, làng chài, vườn quốc gia và khu nghỉ dưỡng.', NULL, 1, '2026-10-19 02:24:41', '2026-10-19 02:24:41'),
(122, 'Huế', 'hue', 'CTY-HUE', 'Miền Trung', 'Cố đô bên sông Hương với kinh thành, lăng tẩm triều Nguyễn và ẩm thực cung đình.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(123, 'Ninh Bình', 'ninh-binh', 'CTY-NBH', 'Miền Bắc', 'Vùng cảnh quan karst, di tích cố đô Hoa Lư và khu sinh thái Tràng An.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(124, 'Sa Pa', 'sa-pa', 'CTY-SAP', 'Miền Bắc', 'Thị trấn vùng cao nổi tiếng với ruộng bậc thang, bản làng và đỉnh Fansipan.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(125, 'Hà Giang', 'ha-giang', 'CTY-HAG', 'Miền Bắc', 'Vùng cao nguyên đá, đèo núi và những bản làng giàu bản sắc miền cực Bắc.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(126, 'Cao Bằng', 'cao-bang', 'CTY-CAB', 'Miền Bắc', 'Điểm đến núi rừng với thác Bản Giốc, hang động và di tích lịch sử.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(127, 'Mộc Châu', 'moc-chau', 'CTY-MOC', 'Miền Bắc', 'Cao nguyên có đồi chè, vườn mận và cảnh quan núi rừng xanh mát.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(128, 'Mai Châu', 'mai-chau', 'CTY-MCH', 'Miền Bắc', 'Thung lũng yên bình với bản làng, ruộng lúa và du lịch cộng đồng.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(129, 'Hải Phòng', 'hai-phong', 'CTY-HPH', 'Miền Bắc', 'Thành phố cảng với kiến trúc đô thị cũ và các điểm du lịch ven biển.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(130, 'Cát Bà', 'cat-ba', 'CTY-CAT', 'Miền Bắc', 'Quần đảo có vịnh Lan Hạ, vườn quốc gia và những bãi tắm nhỏ.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(131, 'Phong Nha', 'phong-nha', 'CTY-PNH', 'Miền Trung', 'Khu du lịch hang động, sông ngầm và rừng núi thuộc vùng Phong Nha - Kẻ Bàng.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(132, 'Nha Trang', 'nha-trang', 'CTY-NTR', 'Miền Trung', 'Thành phố biển có tháp Chăm, vịnh đảo và nhiều hoạt động trên biển.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(133, 'Quy Nhơn', 'quy-nhon', 'CTY-QNH', 'Miền Trung', 'Thành phố duyên hải với bãi biển, cảnh quan ghềnh đá và dấu tích Chăm Pa.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(134, 'Tuy Hòa', 'tuy-hoa', 'CTY-TUH', 'Miền Trung', 'Điểm dừng chân khám phá biển và thắng cảnh tự nhiên của khu vực Phú Yên.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(135, 'Phan Thiết', 'phan-thiet', 'CTY-PTH', 'Miền Trung', 'Vùng duyên hải nổi tiếng với Mũi Né, đồi cát và làng chài.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(136, 'Vũng Tàu', 'vung-tau', 'CTY-VTA', 'Miền Nam', 'Đô thị biển gần Thành phố Hồ Chí Minh với bãi tắm và hải đăng.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(137, 'Côn Đảo', 'con-dao', 'CTY-CDO', 'Miền Nam', 'Quần đảo nổi tiếng với di tích lịch sử, cảnh biển và hệ sinh thái biển.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(138, 'Cần Thơ', 'can-tho', 'CTY-CTH', 'Miền Nam', 'Trung tâm vùng đồng bằng sông Cửu Long với chợ nổi và cảnh quan sông nước.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(139, 'Bến Tre', 'ben-tre', 'CTY-BTR', 'Miền Nam', 'Xứ dừa với cù lao, vườn cây trái và trải nghiệm du lịch miệt vườn.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(140, 'Châu Đốc', 'chau-doc', 'CTY-CHD', 'Miền Nam', 'Điểm đến văn hóa tín ngưỡng và khám phá rừng tràm vùng An Giang.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(141, 'Buôn Ma Thuột', 'buon-ma-thuot', 'CTY-BMT', 'Tây Nguyên', 'Trung tâm văn hóa cà phê và cửa ngõ khám phá thác, rừng Tây Nguyên.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(142, 'Pleiku', 'pleiku', 'CTY-PLK', 'Tây Nguyên', 'Đô thị cao nguyên với Biển Hồ, núi lửa và không gian cồng chiêng.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(143, 'Tây Ninh', 'tay-ninh', 'CTY-TNI', 'Miền Nam', 'Nổi bật với núi Bà Đen, công trình tôn giáo và hồ Dầu Tiếng.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(144, 'Tam Đảo', 'tam-dao', 'CTY-TDO', 'Miền Bắc', 'Thị trấn nghỉ dưỡng trên núi với không khí mát và cảnh quan mây mù.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(145, 'Thanh Hóa', 'thanh-hoa', 'CTY-THO', 'Miền Bắc', 'Vùng du lịch đa dạng từ biển Sầm Sơn đến Pù Luông và Thành nhà Hồ.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(146, 'Đồng Tháp', 'dong-thap', 'CTY-DTH', 'Miền Nam', 'Vùng đất sen hồng với vườn quốc gia Tràm Chim và làng hoa Sa Đéc.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(147, 'Hà Tiên', 'ha-tien', 'CTY-HTI', 'Miền Nam', 'Đô thị ven biển phía Tây Nam với núi đá vôi, hang động và bãi biển.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(148, 'Lý Sơn', 'ly-son', 'CTY-LSO', 'Miền Trung', 'Đảo núi lửa với cảnh quan biển, cổng đá tự nhiên và ruộng tỏi.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(149, 'Vĩnh Long', 'vinh-long', 'CTY-VLO', 'Miền Nam', 'Điểm đến miệt vườn ven sông với cù lao, nhà cổ và văn hóa sông nước.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(150, 'Hà Nam', 'ha-nam', 'CTY-HNA', 'Miền Bắc', 'Vùng thắng cảnh tâm linh với khu du lịch Tam Chúc và các ngôi chùa cổ.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(151, 'Sơn La', 'son-la', 'CTY-SLA', 'Miền Bắc', 'Vùng núi Tây Bắc có cao nguyên, hồ thủy điện và dấu tích lịch sử.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(152, 'Bắc Kạn', 'bac-kan', 'CTY-BKN', 'Miền Bắc', 'Vùng núi có hồ Ba Bể, cảnh quan karst và các bản làng ven hồ.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(153, 'Ninh Thuận', 'ninh-thuan', 'CTY-NTH', 'Miền Trung', 'Vùng duyên hải với tháp Chăm, vườn nho và vịnh Vĩnh Hy.', NULL, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00'),
(154, 'Điện Biên Phủ', 'dien-bien-phu', 'CTY-DBP', 'Miền Bắc', 'Vùng lòng chảo Mường Thanh với quần thể di tích chiến trường Điện Biên Phủ và văn hóa Tây Bắc.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(155, 'Lai Châu', 'lai-chau', 'CTY-LCH', 'Miền Bắc', 'Vùng núi Tây Bắc với hang động đá vôi, bản làng người Mông và người Dao cùng cảnh quan ruộng bậc thang.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(156, 'Yên Bái', 'yen-bai', 'CTY-YBA', 'Miền Bắc', 'Khu vực du lịch Tây Bắc nổi tiếng với hồ Thác Bà, ruộng bậc thang và văn hóa các dân tộc vùng cao.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(157, 'Lạng Sơn', 'lang-son', 'CTY-LAN', 'Miền Bắc', 'Vùng xứ Lạng với hang động, di tích thành cổ và cảnh quan núi đá vôi.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(158, 'Bắc Ninh', 'bac-ninh', 'CTY-BNI', 'Miền Bắc', 'Không gian văn hóa Kinh Bắc với chùa cổ, di tích triều Lý và làng nghề truyền thống.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(159, 'Thái Nguyên', 'thai-nguyen', 'CTY-TNG', 'Miền Bắc', 'Vùng chè trung du với hồ nước, không gian văn hóa dân tộc và di tích cách mạng.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(160, 'Việt Trì', 'viet-tri', 'CTY-VTR', 'Miền Bắc', 'Vùng Đất Tổ bên hợp lưu sông với tín ngưỡng thờ cúng Hùng Vương và di sản hát xoan.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(161, 'Vinh', 'vinh', 'CTY-VIN', 'Miền Trung', 'Đô thị xứ Nghệ bên sông Lam, thuận tiện kết hợp di tích lịch sử và du lịch biển Cửa Lò.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(162, 'Hà Tĩnh', 'ha-tinh', 'CTY-HAT', 'Miền Trung', 'Vùng Bắc Trung Bộ với di tích cách mạng, di sản văn chương và cảnh quan biển, hồ.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(163, 'Quảng Ngãi', 'quang-ngai', 'CTY-QNG', 'Miền Trung', 'Vùng đất bên sông Trà Khúc với di tích lịch sử, chùa cổ và những bãi biển dài.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(164, 'Tam Kỳ', 'tam-ky', 'CTY-TKY', 'Miền Trung', 'Khu vực du lịch xứ Quảng với làng biển, công trình tưởng niệm và di tích văn hóa.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(165, 'Kon Tum', 'kon-tum', 'CTY-KTU', 'Tây Nguyên', 'Vùng đất bên sông Đăk Bla với nhà rông, làng Ba Na và các công trình lịch sử.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(166, 'Bảo Lộc', 'bao-loc', 'CTY-BLO', 'Tây Nguyên', 'Khu vực cao nguyên với đồi chè, thác nước và hồ, phù hợp nghỉ ngơi giữa cảnh quan xanh.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(167, 'Mỹ Tho', 'my-tho', 'CTY-MTH', 'Miền Nam', 'Điểm đến bên sông Tiền với cù lao miệt vườn, chùa cổ và đời sống sông nước.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(168, 'Trà Vinh', 'tra-vinh', 'CTY-TVI', 'Miền Nam', 'Không gian văn hóa Khmer Nam Bộ với chùa cổ, ao nước và cảnh quan cây xanh.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(169, 'Sóc Trăng', 'soc-trang', 'CTY-STR', 'Miền Nam', 'Vùng giao thoa văn hóa Kinh, Khmer và Hoa, nổi tiếng với những ngôi chùa đặc sắc.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(170, 'Bạc Liêu', 'bac-lieu', 'CTY-BLI', 'Miền Nam', 'Vùng đất gắn với đờn ca tài tử, kiến trúc nhà cổ và văn hóa Khmer ven biển.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(171, 'Cà Mau', 'ca-mau', 'CTY-CMA', 'Miền Nam', 'Vùng cực Nam của Việt Nam với rừng ngập mặn, rừng tràm và cảnh quan biển Tây.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(172, 'Rạch Giá', 'rach-gia', 'CTY-RGI', 'Miền Nam', 'Đô thị ven biển Tây, có di tích văn hóa và các tuyến tàu kết nối những đảo phía Tây Nam.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00'),
(173, 'Long Xuyên', 'long-xuyen', 'CTY-LXY', 'Miền Nam', 'Đô thị bên sông Hậu với di tích lịch sử, văn hóa cộng đồng và cảnh quan cù lao.', NULL, 1, '2026-10-24 00:00:00', '2026-10-24 00:00:00');


-- --------------------------------------------------------

--

-- Dữ liệu bổ sung cho cities
INSERT INTO `cities` (`id`, `name`, `slug`, `code`, `region`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(174, 'Nam Định', 'nam-dinh', 'REG-174', 'Miền Bắc', 'Vùng văn hóa đồng bằng Bắc Bộ, nổi tiếng với di tích nhà Trần và nhiều nhà thờ cổ.', 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(175, 'Thái Bình', 'thai-binh', 'REG-175', 'Miền Bắc', 'Vùng đồng bằng ven biển với các chùa cổ, di tích và cảnh quan cửa sông.', 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(176, 'Hải Dương', 'hai-duong', 'REG-176', 'Miền Bắc', 'Vùng đất xứ Đông có di tích lịch sử, làng gốm và sinh cảnh vùng trũng.', 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(177, 'Hưng Yên', 'hung-yen', 'REG-177', 'Miền Bắc', 'Không gian văn hóa Phố Hiến bên sông Hồng với đền chùa và làng cổ.', 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(178, 'Bắc Giang', 'bac-giang', 'REG-178', 'Miền Bắc', 'Vùng trung du gắn với Phật giáo Trúc Lâm và hệ sinh thái rừng Đông Bắc.', 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(179, 'Quảng Trị', 'quang-tri', 'REG-179', 'Miền Trung', 'Vùng đất lưu giữ nhiều di tích lịch sử và cảnh quan dọc vĩ tuyến 17.', 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(180, 'Đồng Hới', 'dong-hoi', 'REG-180', 'Miền Trung', 'Đô thị cửa ngõ Quảng Bình bên sông Nhật Lệ và các bãi biển miền Trung.', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(181, 'Măng Đen', 'mang-den', 'REG-181', 'Tây Nguyên', 'Cao nguyên nhiều rừng thông, hồ và thác nước, khí hậu mát quanh năm.', 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(182, 'Gia Nghĩa - Đắk Nông', 'gia-nghia-dak-nong', 'REG-182', 'Tây Nguyên', 'Vùng cao nguyên núi lửa và hồ nước của khu vực Đắk Nông.', 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(183, 'Cam Ranh', 'cam-ranh', 'REG-183', 'Miền Trung', 'Vùng vịnh biển Khánh Hòa có nhiều đảo nhỏ, bãi cát và khu nghỉ dưỡng.', 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(184, 'Phan Rang - Tháp Chàm', 'phan-rang-thap-cham', 'REG-184', 'Miền Trung', 'Không gian văn hóa Chăm cùng đồi cát, bãi biển và làng nghề truyền thống.', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(185, 'Hậu Giang', 'hau-giang', 'REG-185', 'Miền Nam', 'Vùng sông nước đồng bằng Cửu Long với chợ, kênh rạch và rừng tràm.', 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(186, 'Quần đảo Nam Du', 'quan-dao-nam-du', 'REG-186', 'Miền Nam', 'Quần đảo ở biển Tây với bãi tắm, làng chài và các đảo nhỏ.', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(187, 'Cù Lao Chàm', 'cu-lao-cham', 'REG-187', 'Miền Trung', 'Cụm đảo ngoài khơi Hội An có làng chài, rạn san hô và di tích tín ngưỡng.', 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(188, 'Pù Luông', 'pu-luong', 'REG-188', 'Miền Bắc', 'Khu bảo tồn núi rừng Thanh Hóa có ruộng bậc thang và làng người Thái.', 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(189, 'Ba Vì - Sơn Tây', 'ba-vi-son-tay', 'REG-189', 'Miền Bắc', 'Vùng núi và làng cổ phía tây Hà Nội với các điểm nghỉ dưỡng cuối tuần.', 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(190, 'Tà Xùa', 'ta-xua', 'REG-190', 'Miền Bắc', 'Vùng núi cao Sơn La nổi tiếng với cung đường săn mây và những sống núi.', 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(191, 'Biên Hòa - Đồng Nai', 'bien-hoa-dong-nai', 'REG-191', 'Miền Nam', 'Khu vực Đông Nam Bộ có di tích, công viên và điểm tham quan ven sông Đồng Nai.', 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(192, 'Bình Phước', 'binh-phuoc', 'REG-192', 'Miền Nam', 'Vùng Đông Nam Bộ có rừng quốc gia, núi và các cộng đồng dân tộc.', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(193, 'Thủ Dầu Một', 'thu-dau-mot', 'REG-193', 'Miền Nam', 'Trung tâm đô thị Bình Dương cũ với di sản nghề thủ công và công trình tín ngưỡng.', 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00');

-- Table structure for table `destinations`
--

DROP TABLE IF EXISTS `destinations`;
CREATE TABLE IF NOT EXISTS `destinations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `city_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrance_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `duration` int DEFAULT NULL COMMENT 'Thoi luong tham quan uoc tinh, don vi phut',
  `rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_lodging` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_destinations_slug` (`slug`),
  KEY `idx_destinations_city` (`city_id`),
  KEY `idx_destinations_category` (`category_id`),
  KEY `idx_destinations_featured_lodging` (`is_featured`,`is_lodging`)
) ENGINE=InnoDB AUTO_INCREMENT=1577 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `city_id`, `category_id`, `name`, `slug`, `description`, `address`, `entrance_fee`, `opening_time`, `closing_time`, `duration`, `rating`, `is_featured`, `created_at`, `updated_at`, `is_lodging`) VALUES
(713, 114, 107, 'Hồ Hoàn Kiếm', 'hoan-kiem-lake', 'Hồ nước lịch sử giữa trung tâm Hà Nội, gắn với truyền thuyết trả gươm và không gian phố cổ.', 'Khu vực Hoàn Kiếm, Hà Nội', 0.0, '00:00:00', '23:59:00', 90, 4.7, 1, '2026-10-19 02:24:39', '2026-10-24 00:00:00', 0),
(714, 114, 107, 'Văn Miếu - Quốc Tử Giám', 'temple-of-literature', 'Quần thể di tích thờ Khổng Tử và gắn với trường Quốc Tử Giám, biểu tượng truyền thống hiếu học.', '58 Quốc Tử Giám, Hà Nội', 70000.0, '08:00:00', '17:00:00', 120, 4.6, 1, '2026-10-19 02:24:40', '2026-10-24 00:00:00', 0),
(715, 115, 106, 'Bãi biển Mỹ Khê Đà Nẵng', 'my-khe-beach', 'Bãi biển cát dài gần trung tâm Đà Nẵng, thuận tiện dạo biển và ngắm bình minh.', 'Đường Võ Nguyên Giáp, Đà Nẵng', 0.0, '00:00:00', '23:59:00', 180, 4.7, 1, '2026-10-19 02:24:40', '2026-10-24 00:00:00', 0),
(716, 115, 109, 'Khu du lịch Bà Nà Hills', 'ba-na-hills', 'Khu du lịch trên núi với cáp treo, vườn hoa và công trình Cầu Vàng.', 'Khu vực Bà Nà, Đà Nẵng', 900000.0, '08:00:00', '22:00:00', 480, 4.5, 1, '2026-10-19 02:24:40', '2026-10-24 00:00:00', 0),
(717, 116, 107, 'Phố cổ Hội An', 'hoi-an-ancient-town', 'Đô thị thương cảng cổ với nhà truyền thống, hội quán và những tuyến phố đèn lồng.', 'Khu phố cổ Hội An', 120000.0, '07:00:00', '21:30:00', 240, 4.9, 1, '2026-10-19 02:24:40', '2026-10-24 00:00:00', 0),
(718, 116, 108, 'Chợ Hội An', 'hoi-an-central-market', 'Khu chợ gần sông với nông sản, món ăn địa phương và các sản phẩm phục vụ đời sống.', 'Đường Trần Quý Cáp, Hội An', 0.0, '06:00:00', '20:00:00', 90, 4.4, 0, '2026-10-19 02:24:40', '2026-10-24 00:00:00', 0),
(719, 117, 107, 'Dinh Độc Lập', 'independence-palace', 'Di tích lịch sử với các phòng khánh tiết, không gian làm việc và khu trưng bày tư liệu.', '135 Nam Kỳ Khởi Nghĩa, Thành phố Hồ Chí Minh', 40000.0, '08:00:00', '15:30:00', 120, 4.6, 1, '2026-10-19 02:24:40', '2026-10-24 00:00:00', 0),
(720, 117, 110, 'Chợ Bến Thành', 'ben-thanh-market', 'Khu chợ trung tâm có các gian hàng ẩm thực, vải vóc, quà lưu niệm và sản phẩm địa phương.', 'Đường Lê Lợi, Thành phố Hồ Chí Minh', 0.0, '06:00:00', '22:00:00', 120, 4.2, 0, '2026-10-19 02:24:40', '2026-10-24 00:00:00', 0),
(721, 118, 106, 'Hồ Xuân Hương', 'xuan-huong-lake', 'Hồ nước giữa trung tâm Đà Lạt, có đường dạo ven bờ và cảnh quan cây xanh.', 'Trung tâm Đà Lạt', 0.0, '00:00:00', '23:59:00', 120, 4.6, 1, '2026-10-19 02:24:40', '2026-10-24 00:00:00', 0),
(722, 118, 106, 'Thác Datanla', 'datanla-waterfall', 'Thác nước trong rừng gần đèo Prenn với đường tham quan và các hoạt động giải trí.', 'Khu vực đèo Prenn, Đà Lạt', 50000.0, '07:00:00', '17:00:00', 180, 4.5, 0, '2026-10-19 02:24:40', '2026-10-24 00:00:00', 0),
(1281, 115, 111, 'Khách sạn Mercure Danang French Village Bana Hills', 'mercure-danang-french-village-bana-hills', 'Khách sạn trong khu French Village tại Bà Nà Hills, thuận tiện nghỉ đêm trên núi.', 'Thôn An Sơn, Xã Hòa Ninh, Huyện Hòa Vang, Đà Nẵng', 0.0, NULL, NULL, NULL, 4.9, 1, '2026-10-19 02:24:44', '2026-10-19 02:24:44', 1),
(1282, 119, 112, 'Du Thuyền Vịnh Hạ Long', 'du-thuyen-vinh-ha-long', 'Trải nghiệm lưu trú trên du thuyền tại vịnh Hạ Long; hành trình và tiện nghi tùy từng gói dịch vụ.', 'Cảng tàu khách quốc tế Tuần Châu, Hạ Long, Quảng Ninh', 0.0, NULL, NULL, NULL, 4.8, 1, '2026-10-19 02:24:45', '2026-10-19 02:24:45', 1),
(1283, 120, 111, 'Vinpearl Resort & Spa Phú Quốc', 'vinpearl-resort-spa-phu-quoc', 'Khu nghỉ dưỡng 5 sao sang trọng bên bờ Bãi Dài với bể bơi ngoài trời rộng lớn và dịch vụ chuẩn quốc tế.', 'Bãi Dài, Xã Gành Dầu, Thành phố Phú Quốc, Kiên Giang', 0.0, NULL, NULL, NULL, 5.0, 1, '2026-10-19 02:24:45', '2026-10-19 02:24:45', 1),
(1285, 114, 114, 'Hoàng thành Thăng Long', 'hoang-thanh-thang-long', 'Quần thể di tích gắn với lịch sử các triều đại tại kinh đô Thăng Long.', 'Ba Đình, Hà Nội', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1286, 114, 107, 'Lăng Chủ tịch Hồ Chí Minh', 'lang-chu-tich-ho-chi-minh', 'Công trình tưởng niệm Chủ tịch Hồ Chí Minh tại trung tâm Ba Đình.', 'Quảng trường Ba Đình, Hà Nội', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1287, 114, 114, 'Chùa Một Cột', 'chua-mot-cot', 'Ngôi chùa có kiến trúc đài sen đặc trưng trong quần thể di tích Ba Đình.', 'Ba Đình, Hà Nội', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1288, 114, 114, 'Phố cổ Hà Nội', 'pho-co-ha-noi', 'Khu phố lâu đời với các tuyến phố nghề, hàng quán và kiến trúc đô thị.', 'Hoàn Kiếm, Hà Nội', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1289, 114, 106, 'Hồ Tây', 'ho-tay', 'Hồ lớn với tuyến đường ven hồ, quán cà phê và nhiều điểm ngắm hoàng hôn.', 'Tây Hồ, Hà Nội', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1290, 114, 114, 'Chùa Trấn Quốc', 'chua-tran-quoc', 'Ngôi chùa cổ nằm gần bờ Hồ Tây và hồ Trúc Bạch.', 'Đường Thanh Niên, Tây Hồ, Hà Nội', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1291, 114, 107, 'Di tích Nhà tù Hỏa Lò', 'di-tich-nha-tu-hoa-lo', 'Không gian trưng bày tư liệu về lịch sử nhà tù và các giai đoạn chiến tranh.', 'Phố Hỏa Lò, Hoàn Kiếm, Hà Nội', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1292, 114, 107, 'Bảo tàng Dân tộc học Việt Nam', 'bao-tang-dan-toc-hoc-viet-nam', 'Bảo tàng giới thiệu văn hóa và đời sống của các dân tộc Việt Nam.', 'Nguyễn Văn Huyên, Cầu Giấy, Hà Nội', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1293, 114, 110, 'Chợ Đồng Xuân', 'cho-dong-xuan', 'Khu chợ lâu đời ở cửa ngõ phố cổ với hàng hóa và ẩm thực địa phương.', 'Đồng Xuân, Hoàn Kiếm, Hà Nội', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1294, 114, 107, 'Nhà thờ Lớn Hà Nội', 'nha-tho-lon-ha-noi', 'Công trình kiến trúc Gothic quen thuộc gần khu phố cổ.', 'Nhà Chung, Hoàn Kiếm, Hà Nội', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1295, 115, 109, 'Cầu Rồng', 'cau-rong', 'Cây cầu biểu tượng bắc qua sông Hàn với tạo hình rồng độc đáo.', 'Sông Hàn, Đà Nẵng', 0.0, NULL, NULL, 45, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1296, 115, 106, 'Bán đảo Sơn Trà', 'ban-dao-son-tra', 'Khu bán đảo có rừng tự nhiên, cung đường ven biển và điểm ngắm cảnh.', 'Sơn Trà, Đà Nẵng', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1297, 115, 114, 'Chùa Linh Ứng Bãi Bụt', 'chua-linh-ung-bai-but', 'Ngôi chùa có tượng Quan Âm lớn hướng ra biển Đà Nẵng.', 'Bán đảo Sơn Trà, Đà Nẵng', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1298, 115, 114, 'Ngũ Hành Sơn', 'ngu-hanh-son', 'Quần thể núi đá vôi với hang động, chùa và các bậc thang tham quan.', 'Ngũ Hành Sơn, Đà Nẵng', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1299, 115, 112, 'Bãi biển Non Nước', 'bai-bien-non-nuoc', 'Bãi cát dài phía nam thành phố thích hợp đi dạo và ngắm bình minh.', 'Ngũ Hành Sơn, Đà Nẵng', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1300, 115, 107, 'Bảo tàng Điêu khắc Chăm', 'bao-tang-dieu-khac-cham', 'Nơi lưu giữ các bộ sưu tập điêu khắc và hiện vật văn hóa Chăm Pa.', 'Bạch Đằng, Hải Châu, Đà Nẵng', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1301, 115, 110, 'Chợ Hàn', 'cho-han', 'Chợ trung tâm nổi tiếng với đặc sản địa phương và quà lưu niệm.', 'Trần Phú, Hải Châu, Đà Nẵng', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1302, 115, 113, 'Đèo Hải Vân', 'deo-hai-van', 'Cung đường đèo ven biển nổi tiếng với cảnh núi và vịnh.', 'Khu vực đèo Hải Vân, Đà Nẵng', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1303, 115, 109, 'Công viên APEC', 'cong-vien-apec', 'Công viên ven sông Hàn với mái vòm kiến trúc uốn lượn.', 'Bình Hiên, Hải Châu, Đà Nẵng', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1304, 116, 114, 'Chùa Cầu Hội An', 'chua-cau-hoi-an', 'Cây cầu có mái che gắn với lịch sử thương cảng Hội An.', 'Phố cổ Hội An', 0.0, NULL, NULL, 45, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1305, 116, 114, 'Nhà cổ Tấn Ký', 'nha-co-tan-ky', 'Ngôi nhà cổ tiêu biểu với kiến trúc giao thoa trong khu phố cổ.', 'Nguyễn Thái Học, Hội An', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1306, 116, 114, 'Hội quán Phúc Kiến', 'hoi-quan-phuc-kien', 'Hội quán cộng đồng người Hoa với sân và các chi tiết trang trí truyền thống.', 'Trần Phú, Hội An', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1307, 116, 108, 'Làng rau Trà Quế', 'lang-rau-tra-que', 'Làng rau nổi tiếng với các vườn trồng rau thơm và trải nghiệm làm nông.', 'Trà Quế, Hội An', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1308, 116, 106, 'Rừng dừa Bảy Mẫu', 'rung-dua-bay-mau', 'Vùng rừng dừa nước với hoạt động đi thuyền thúng trên các lạch nhỏ.', 'Cẩm Thanh, Hội An', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1309, 116, 112, 'Bãi biển An Bàng', 'bai-bien-an-bang', 'Bãi biển có cát sáng màu, quán ven biển và không gian thư giãn.', 'Cẩm An, Hội An', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1310, 116, 112, 'Cù Lao Chàm', 'cu-lao-cham', 'Cụm đảo thuộc khu dự trữ sinh quyển với làng chài và rạn san hô.', 'Cụm đảo Cù Lao Chàm, vùng Hội An', 0.0, NULL, NULL, 360, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1311, 116, 107, 'Làng gốm Thanh Hà', 'lang-gom-thanh-ha', 'Làng nghề gốm truyền thống cho phép tìm hiểu quy trình làm gốm.', 'Thanh Hà, Hội An', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1312, 116, 107, 'Làng mộc Kim Bồng', 'lang-moc-kim-bong', 'Làng mộc lâu đời với các xưởng thủ công và không gian làng ven sông.', 'Cẩm Kim, Hội An', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1313, 117, 107, 'Bưu điện Trung tâm Sài Gòn', 'buu-dien-trung-tam-sai-gon', 'Công trình bưu điện lịch sử có sảnh vòm và kiến trúc thời thuộc địa.', 'Công xã Paris, Quận 1, TP. Hồ Chí Minh', 0.0, NULL, NULL, 60, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1314, 117, 107, 'Nhà thờ Đức Bà Sài Gòn', 'nha-tho-duc-ba-sai-gon', 'Vương cung thánh đường nổi tiếng ở trung tâm thành phố.', 'Công xã Paris, Quận 1, TP. Hồ Chí Minh', 0.0, NULL, NULL, 45, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1315, 117, 107, 'Bảo tàng Chứng tích Chiến tranh', 'bao-tang-chung-tich-chien-tranh', 'Bảo tàng trưng bày hình ảnh và tư liệu về chiến tranh Việt Nam.', 'Võ Văn Tần, Quận 3, TP. Hồ Chí Minh', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1316, 117, 109, 'Phố đi bộ Nguyễn Huệ', 'pho-di-bo-nguyen-hue', 'Quảng trường đi bộ trung tâm thường có các hoạt động cộng đồng.', 'Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1317, 117, 109, 'Thảo Cầm Viên Sài Gòn', 'thao-cam-vien-sai-gon', 'Vườn thú và vườn thực vật lâu đời với nhiều không gian xanh.', 'Nguyễn Bỉnh Khiêm, Quận 1, TP. Hồ Chí Minh', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1318, 117, 107, 'Địa đạo Củ Chi', 'dia-dao-cu-chi', 'Hệ thống địa đạo lịch sử gắn với kháng chiến tại vùng Củ Chi.', 'Củ Chi, TP. Hồ Chí Minh', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1319, 117, 106, 'Bến Bạch Đằng', 'ben-bach-dang', 'Công viên bờ sông có lối đi bộ và góc nhìn về sông Sài Gòn.', 'Bến Bạch Đằng, Quận 1, TP. Hồ Chí Minh', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1320, 117, 109, 'Phố Tây Bùi Viện', 'pho-tay-bui-vien', 'Tuyến phố sôi động về đêm với nhiều hàng quán.', 'Bùi Viện, Quận 1, TP. Hồ Chí Minh', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1321, 117, 114, 'Chùa Ngọc Hoàng', 'chua-ngoc-hoang', 'Ngôi chùa có kiến trúc Hoa và các điện thờ giàu chi tiết trang trí.', 'Mai Thị Lựu, TP. Hồ Chí Minh', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1322, 117, 107, 'Bảo tàng Lịch sử TP. Hồ Chí Minh', 'bao-tang-lich-su-tp-ho-chi-minh', 'Bảo tàng giới thiệu các giai đoạn lịch sử và hiện vật khảo cổ.', 'Nguyễn Bỉnh Khiêm, TP. Hồ Chí Minh', 0.0, NULL, NULL, 100, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1323, 118, 106, 'Hồ Tuyền Lâm', 'ho-tuyen-lam', 'Hồ nước giữa rừng thông phù hợp tham quan cảnh quan và chèo thuyền.', 'Khu vực Hồ Tuyền Lâm, Đà Lạt', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1324, 118, 114, 'Thiền viện Trúc Lâm Đà Lạt', 'thien-vien-truc-lam-da-lat', 'Thiền viện nằm trên đồi thông nhìn xuống khu vực hồ Tuyền Lâm.', 'Hồ Tuyền Lâm, Đà Lạt', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1325, 118, 107, 'Ga Đà Lạt', 'ga-da-lat', 'Nhà ga cổ với kiến trúc đặc trưng và không gian đường sắt di sản.', 'Quang Trung, Đà Lạt', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1326, 118, 106, 'Thung lũng Tình Yêu', 'thung-lung-tinh-yeu', 'Khu du lịch cảnh quan với hồ, rừng thông và vườn hoa.', 'Mai Anh Đào, Đà Lạt', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1327, 118, 113, 'Đỉnh Langbiang', 'dinh-langbiang', 'Khu núi cao nhìn xuống vùng cao nguyên và thung lũng phía dưới.', 'Lạc Dương, gần Đà Lạt', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1328, 118, 110, 'Chợ Đà Lạt', 'cho-da-lat', 'Khu chợ trung tâm với rau củ cao nguyên, hoa và đặc sản.', 'Trung tâm Đà Lạt', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1329, 118, 106, 'Đồi chè Cầu Đất', 'doi-che-cau-dat', 'Vùng trồng chè trên cao nguyên với những luống chè trải dài.', 'Xuân Trường, Đà Lạt', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1330, 118, 106, 'Vườn hoa Đà Lạt', 'vuon-hoa-da-lat', 'Khu vườn giới thiệu các giống hoa trong không gian gần hồ Xuân Hương.', 'Trần Quốc Toản, Đà Lạt', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1331, 118, 109, 'Quảng trường Lâm Viên', 'quang-truong-lam-vien', 'Quảng trường ven hồ với công trình kiến trúc hình nụ hoa và hoa dã quỳ.', 'Trần Quốc Toản, Đà Lạt', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1332, 118, 107, 'Nhà thờ Con Gà Đà Lạt', 'nha-tho-con-ga-da-lat', 'Nhà thờ có tháp chuông cao và kiến trúc châu Âu quen thuộc.', 'Trần Phú, Đà Lạt', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1333, 119, 106, 'Vịnh Hạ Long', 'vinh-ha-long', 'Di sản thiên nhiên với hệ thống đảo đá vôi và hành trình tham quan bằng tàu.', 'Vùng biển Hạ Long, Quảng Ninh', 0.0, NULL, NULL, 300, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1334, 119, 112, 'Đảo Ti Tốp', 'dao-ti-top', 'Đảo có bãi tắm nhỏ và điểm quan sát toàn cảnh vịnh trên cao.', 'Vịnh Hạ Long', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1335, 119, 106, 'Hang Sửng Sốt', 'hang-sung-sot', 'Hang đá vôi lớn với thạch nhũ và tuyến tham quan trong hang.', 'Đảo Bồ Hòn, Vịnh Hạ Long', 0.0, NULL, NULL, 75, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1336, 119, 106, 'Hang Luồn', 'hang-luon', 'Hang xuyên núi dẫn vào vùng nước lặng thường tham quan bằng kayak.', 'Đảo Bồ Hòn, Vịnh Hạ Long', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1337, 119, 112, 'Bãi Cháy', 'bai-chay', 'Khu bãi biển du lịch ở phía tây cầu Bãi Cháy.', 'Bãi Cháy, Hạ Long', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1338, 119, 107, 'Bảo tàng Quảng Ninh', 'bao-tang-quang-ninh', 'Bảo tàng trưng bày tư liệu về văn hóa, lịch sử và thiên nhiên Quảng Ninh.', 'Trần Quốc Nghiễn, Hạ Long', 0.0, NULL, NULL, 100, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1339, 119, 109, 'Sun World Hạ Long', 'sun-world-ha-long', 'Tổ hợp giải trí với các khu vui chơi và cáp treo qua vịnh.', 'Bãi Cháy, Hạ Long', 0.0, NULL, NULL, 240, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1340, 119, 112, 'Đảo Tuần Châu', 'dao-tuan-chau', 'Đảo có bến cảng du thuyền và các hoạt động du lịch ven biển.', 'Tuần Châu, Hạ Long', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1341, 120, 112, 'Bãi Sao Phú Quốc', 'bai-sao-phu-quoc', 'Bãi cát trắng cong hình cánh cung nổi tiếng ở phía nam đảo.', 'An Thới, Phú Quốc', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1342, 120, 112, 'Bãi Khem', 'bai-khem', 'Bãi biển phía nam đảo với làn nước trong và nhiều khu nghỉ dưỡng.', 'An Thới, Phú Quốc', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1343, 120, 112, 'Mũi Gành Dầu', 'mui-ganh-dau', 'Mũi đất ở phía tây bắc đảo với cảnh biển và làng chài.', 'Gành Dầu, Phú Quốc', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1344, 120, 108, 'Chợ đêm Phú Quốc', 'cho-dem-phu-quoc', 'Khu chợ đêm với hải sản, món ăn vặt và quà lưu niệm.', 'Dương Đông, Phú Quốc', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1345, 120, 112, 'Hòn Thơm', 'hon-thom', 'Đảo ở phía nam Phú Quốc có cáp treo và các hoạt động du lịch biển.', 'Quần đảo An Thới, Phú Quốc', 0.0, NULL, NULL, 240, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1346, 120, 106, 'Suối Tranh', 'suoi-tranh', 'Dòng suối trong rừng với đường đi bộ và điểm dừng chân giữa thiên nhiên.', 'Dương Tơ, Phú Quốc', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1347, 120, 109, 'VinWonders Phú Quốc', 'vinwonders-phu-quoc', 'Công viên chủ đề nhiều phân khu giải trí và trò chơi gia đình.', 'Gành Dầu, Phú Quốc', 0.0, NULL, NULL, 300, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1348, 120, 109, 'Grand World Phú Quốc', 'grand-world-phu-quoc', 'Khu phố du lịch, mua sắm và giải trí với nhiều công trình mô phỏng.', 'Gành Dầu, Phú Quốc', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1349, 120, 107, 'Di tích Nhà tù Phú Quốc', 'di-tich-nha-tu-phu-quoc', 'Di tích ghi lại lịch sử trại giam tù binh thời chiến tranh.', 'An Thới, Phú Quốc', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1350, 120, 108, 'Làng chài Hàm Ninh', 'lang-chai-ham-ninh', 'Làng ven biển có cầu cảng và nhiều món hải sản địa phương.', 'Hàm Ninh, Phú Quốc', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1351, 120, 114, 'Dinh Cậu Phú Quốc', 'dinh-cau-phu-quoc', 'Miếu thờ ven biển trên ghềnh đá gần cửa sông Dương Đông.', 'Dương Đông, Phú Quốc', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1352, 122, 114, 'Đại Nội Huế', 'dai-noi-hue', 'Quần thể cung điện, cổng thành và các công trình của triều Nguyễn.', 'Kinh thành Huế', 0.0, NULL, NULL, 210, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1353, 122, 114, 'Chùa Thiên Mụ', 'chua-thien-mu', 'Ngôi chùa cổ bên sông Hương với tháp Phước Duyên nổi bật.', 'Kim Long, Huế', 0.0, NULL, NULL, 75, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1354, 122, 114, 'Lăng Minh Mạng', 'lang-minh-mang', 'Lăng tẩm vua Minh Mạng với hồ, sân chầu và khu vườn theo trục đối xứng.', 'Hương Trà, vùng Huế', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1355, 122, 114, 'Lăng Khải Định', 'lang-khai-dinh', 'Lăng tẩm có kiến trúc pha trộn phong cách Á - Âu và trang trí sành sứ.', 'Thủy Bằng, vùng Huế', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1356, 122, 114, 'Lăng Tự Đức', 'lang-tu-duc', 'Quần thể lăng tẩm có hồ, nhà tạ và không gian cây xanh.', 'Thủy Xuân, Huế', 0.0, NULL, NULL, 105, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1357, 122, 107, 'Cầu Trường Tiền', 'cau-truong-tien', 'Cây cầu thép lâu đời gắn với hình ảnh đô thị bên sông Hương.', 'Sông Hương, Huế', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1358, 122, 108, 'Chợ Đông Ba', 'cho-dong-ba', 'Khu chợ nổi tiếng với ẩm thực Huế và nhiều sản phẩm địa phương.', 'Trần Hưng Đạo, Huế', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1359, 122, 106, 'Sông Hương', 'song-huong', 'Dòng sông chạy qua cố đô với hoạt động thuyền và cảnh quan ven bờ.', 'Trung tâm Huế', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1360, 123, 106, 'Khu du lịch Tràng An', 'khu-du-lich-trang-an', 'Cảnh quan sông nước và núi đá vôi tham quan bằng thuyền qua hang động.', 'Hoa Lư, Ninh Bình', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1361, 123, 106, 'Tam Cốc - Bích Động', 'tam-coc-bich-dong', 'Tuyến thuyền xuyên giữa cánh đồng và dãy núi đá vôi.', 'Ninh Hải, Hoa Lư, Ninh Bình', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1362, 123, 113, 'Hang Múa', 'hang-mua', 'Điểm leo bậc đá lên đỉnh núi ngắm cánh đồng và sông Ngô Đồng.', 'Khê Đầu Hạ, Hoa Lư, Ninh Bình', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1363, 123, 114, 'Chùa Bái Đính', 'chua-bai-dinh', 'Quần thể chùa rộng với nhiều công trình Phật giáo và đường hành lang.', 'Gia Viễn, Ninh Bình', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1364, 123, 114, 'Cố đô Hoa Lư', 'co-do-hoa-lu', 'Khu di tích kinh đô thời Đinh và Tiền Lê.', 'Trường Yên, Ninh Bình', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1365, 123, 106, 'Vườn quốc gia Cúc Phương', 'vuon-quoc-gia-cuc-phuong', 'Khu rừng nguyên sinh nổi tiếng với tuyến đi bộ và bảo tồn động vật.', 'Nho Quan, Ninh Bình', 0.0, NULL, NULL, 240, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1366, 123, 106, 'Khu bảo tồn đất ngập nước Vân Long', 'khu-bao-ton-dat-ngap-nuoc-van-long', 'Vùng đầm nước và núi đá vôi có thể tham quan bằng thuyền.', 'Gia Viễn, Ninh Bình', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1367, 123, 114, 'Nhà thờ đá Phát Diệm', 'nha-tho-da-phat-diem', 'Quần thể kiến trúc tôn giáo kết hợp hình thức Á Đông và nhà thờ Công giáo.', 'Kim Sơn, Ninh Bình', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1368, 124, 113, 'Đỉnh Fansipan', 'dinh-fansipan', 'Đỉnh núi cao nhất Việt Nam với hệ thống cáp treo và đường leo núi.', 'Dãy Hoàng Liên Sơn, Sa Pa', 0.0, NULL, NULL, 300, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1369, 124, 107, 'Bản Cát Cát', 'ban-cat-cat', 'Bản du lịch vùng núi giới thiệu nghề thủ công và đời sống địa phương.', 'San Sả Hồ, Sa Pa', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1370, 124, 106, 'Thung lũng Mường Hoa', 'thung-lung-muong-hoa', 'Thung lũng có ruộng bậc thang, suối và bản làng ven sườn núi.', 'Mường Hoa, Sa Pa', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1371, 124, 107, 'Bản Tả Van', 'ban-ta-van', 'Bản làng nổi tiếng với ruộng lúa bậc thang và trải nghiệm lưu trú cộng đồng.', 'Tả Van, Sa Pa', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1372, 124, 113, 'Đèo Ô Quy Hồ', 'deo-o-quy-ho', 'Đèo núi cao trên dãy Hoàng Liên Sơn với tầm nhìn rộng.', 'Đường nối Sa Pa và Lai Châu', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1373, 124, 113, 'Núi Hàm Rồng', 'nui-ham-rong', 'Khu vườn núi và điểm ngắm cảnh nhìn xuống thị trấn Sa Pa.', 'Trung tâm Sa Pa', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1374, 124, 107, 'Nhà thờ đá Sa Pa', 'nha-tho-da-sa-pa', 'Nhà thờ có kiến trúc đá đặc trưng nằm cạnh quảng trường thị trấn.', 'Trung tâm Sa Pa', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1375, 125, 113, 'Đèo Mã Pí Lèng', 'deo-ma-pi-leng', 'Cung đèo trên cao nguyên đá nổi bật với cảnh hẻm vực sâu.', 'Mèo Vạc - Đồng Văn, Hà Giang', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1376, 125, 106, 'Sông Nho Quế', 'song-nho-que', 'Dòng sông xanh chảy qua hẻm vực đá vôi hùng vĩ.', 'Khu vực Mèo Vạc, Hà Giang', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1377, 125, 114, 'Dinh thự họ Vương', 'dinh-thu-ho-vuong', 'Khu dinh thự lịch sử của dòng họ Vương với kiến trúc đá và gỗ.', 'Sà Phìn, Đồng Văn, Hà Giang', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1378, 125, 107, 'Cột cờ Lũng Cú', 'cot-co-lung-cu', 'Cột cờ biểu tượng tại vùng cực Bắc của Tổ quốc.', 'Lũng Cú, Đồng Văn, Hà Giang', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1379, 125, 114, 'Phố cổ Đồng Văn', 'pho-co-dong-van', 'Khu phố nhỏ có những ngôi nhà trình tường và chợ vùng cao.', 'Thị trấn Đồng Văn, Hà Giang', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1380, 125, 113, 'Dốc Thẩm Mã', 'doc-tham-ma', 'Dốc đèo quanh co nổi tiếng trên tuyến đường qua cao nguyên đá.', 'Đường đi Đồng Văn, Hà Giang', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1381, 126, 106, 'Thác Bản Giốc', 'thac-ban-gioc', 'Thác nước nhiều tầng nằm trên sông Quây Sơn gần biên giới.', 'Đàm Thủy, Trùng Khánh, Cao Bằng', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1382, 126, 106, 'Động Ngườm Ngao', 'dong-nguom-ngao', 'Hang đá vôi với các khoang rộng và nhiều khối thạch nhũ.', 'Trùng Khánh, Cao Bằng', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1383, 126, 106, 'Hồ Thang Hen', 'ho-thang-hen', 'Hệ thống hồ trên núi đá vôi với nước xanh và cảnh quan rừng.', 'Trà Lĩnh, Cao Bằng', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1384, 126, 107, 'Khu di tích Pác Bó', 'khu-di-tich-pac-bo', 'Khu di tích gắn với thời gian Chủ tịch Hồ Chí Minh hoạt động cách mạng.', 'Trường Hà, Hà Quảng, Cao Bằng', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1385, 126, 113, 'Núi Mắt Thần', 'nui-mat-than', 'Ngọn núi có lỗ xuyên tự nhiên giữa thung lũng cỏ.', 'Trà Lĩnh, Cao Bằng', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1386, 127, 106, 'Đồi chè trái tim Mộc Châu', 'doi-che-trai-tim-moc-chau', 'Những luống chè tạo hình trái tim trên cao nguyên.', 'Khu đồi chè Mộc Châu, Sơn La', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1387, 127, 106, 'Rừng thông Bản Áng', 'rung-thong-ban-ang', 'Khu hồ và rừng thông thích hợp đi dạo, cắm trại và đạp xe.', 'Đông Sang, Mộc Châu', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1388, 127, 106, 'Thác Dải Yếm', 'thac-dai-yem', 'Thác nước giữa núi rừng với những tầng nước chảy qua đá.', 'Mường Sang, Mộc Châu', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1389, 127, 109, 'Cầu kính Bạch Long', 'cau-kinh-bach-long', 'Cầu kính dành cho khách tham quan giữa địa hình núi rừng.', 'Mộc Châu, Sơn La', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1390, 127, 113, 'Đỉnh Pha Luông', 'dinh-pha-luong', 'Đỉnh núi có tuyến leo bộ và góc nhìn ra dãy núi vùng biên.', 'Vùng biên giới Mộc Châu, Sơn La', 0.0, NULL, NULL, 300, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1391, 128, 107, 'Bản Lác', 'ban-lac', 'Bản du lịch cộng đồng với nhà sàn và nghề dệt thổ cẩm.', 'Chiềng Châu, Mai Châu', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1392, 128, 113, 'Đèo Thung Khe', 'deo-thung-khe', 'Đèo núi có điểm dừng ngắm thung lũng và địa hình đá vôi.', 'Quốc lộ 6, vùng Mai Châu', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1393, 128, 106, 'Hang Chiều', 'hang-chieu', 'Hang động trên sườn núi phải đi qua nhiều bậc đá để tới cửa hang.', 'Thị trấn Mai Châu', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1394, 128, 107, 'Bản Pom Coọng', 'ban-pom-coong', 'Bản làng với nhà sàn truyền thống và trải nghiệm văn hóa địa phương.', 'Thị trấn Mai Châu', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1395, 129, 107, 'Nhà hát lớn Hải Phòng', 'nha-hat-lon-hai-phong', 'Công trình kiến trúc trung tâm gắn với lịch sử đô thị cảng.', 'Quảng trường Nhà hát thành phố, Hải Phòng', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1396, 129, 112, 'Bãi biển Đồ Sơn', 'bai-bien-do-son', 'Khu nghỉ mát ven biển lâu đời ở phía đông nam thành phố.', 'Đồ Sơn, Hải Phòng', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1397, 129, 113, 'Núi Voi', 'nui-voi', 'Khu thắng cảnh đá vôi với hang động và dấu tích lịch sử.', 'An Lão, Hải Phòng', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1398, 130, 112, 'Vịnh Lan Hạ', 'vinh-lan-ha', 'Vịnh biển có đảo đá vôi, bãi tắm nhỏ và tuyến đi kayak.', 'Vùng biển Cát Bà, Hải Phòng', 0.0, NULL, NULL, 240, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1399, 130, 106, 'Vườn quốc gia Cát Bà', 'vuon-quoc-gia-cat-ba', 'Khu bảo tồn rừng trên đảo với tuyến đi bộ và hệ sinh thái đa dạng.', 'Đảo Cát Bà, Hải Phòng', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1400, 130, 112, 'Đảo Khỉ Cát Bà', 'dao-khi-cat-ba', 'Đảo nhỏ với bãi tắm và hoạt động tham quan bằng tàu.', 'Vịnh Lan Hạ, Cát Bà', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1401, 130, 112, 'Bãi tắm Cát Cò', 'bai-tam-cat-co', 'Những bãi tắm nằm giữa vách núi gần trung tâm thị trấn.', 'Thị trấn Cát Bà', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1402, 130, 107, 'Pháo đài Thần Công Cát Bà', 'phao-dai-than-cong-cat-ba', 'Khu vực trên cao có dấu tích công sự và tầm nhìn ra vịnh.', 'Đảo Cát Bà', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1403, 131, 106, 'Động Phong Nha', 'dong-phong-nha', 'Hang động có sông ngầm và nhiều khối thạch nhũ.', 'Phong Nha, Bố Trạch, Quảng Bình', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1404, 131, 106, 'Động Thiên Đường', 'dong-thien-duong', 'Hang khô lớn với lối đi tham quan giữa những khối thạch nhũ.', 'Vùng Phong Nha - Kẻ Bàng', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1405, 131, 106, 'Vườn quốc gia Phong Nha - Kẻ Bàng', 'vuon-quoc-gia-phong-nha-ke-bang', 'Di sản thiên nhiên nổi bật với rừng núi đá vôi và hệ thống hang động.', 'Bố Trạch, Quảng Bình', 0.0, NULL, NULL, 240, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1406, 131, 106, 'Suối Nước Moọc', 'suoi-nuoc-mooc', 'Khu suối giữa rừng phù hợp tham quan và hoạt động ngoài trời.', 'Vùng Phong Nha - Kẻ Bàng', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1407, 131, 113, 'Hang Én', 'hang-en', 'Hang động lớn thường được khám phá theo hành trình trekking có hướng dẫn.', 'Vùng Phong Nha - Kẻ Bàng', 0.0, NULL, NULL, 480, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1408, 132, 114, 'Tháp Bà Ponagar', 'thap-ba-ponagar', 'Quần thể đền tháp Chăm Pa trên đồi gần cửa sông Cái.', 'Vĩnh Phước, Nha Trang', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1409, 132, 114, 'Chùa Long Sơn', 'chua-long-son', 'Ngôi chùa với tượng Phật trắng lớn trên đồi.', 'Phương Sơn, Nha Trang', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1410, 132, 106, 'Hòn Chồng', 'hon-chong', 'Quần thể tảng đá lớn sát biển với điểm ngắm vịnh.', 'Vĩnh Phước, Nha Trang', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1411, 132, 109, 'VinWonders Nha Trang', 'vinwonders-nha-trang', 'Khu vui chơi giải trí kết hợp công viên nước và các trò chơi.', 'Đảo Hòn Tre, Nha Trang', 0.0, NULL, NULL, 300, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1412, 132, 112, 'Hòn Mun', 'hon-mun', 'Đảo biển nổi tiếng với hoạt động lặn và ngắm san hô.', 'Vịnh Nha Trang', 0.0, NULL, NULL, 210, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1413, 132, 110, 'Chợ Đầm Nha Trang', 'cho-dam-nha-trang', 'Chợ truyền thống bán hải sản khô, đặc sản và hàng tiêu dùng.', 'Trung tâm Nha Trang', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1414, 132, 107, 'Viện Hải dương học Nha Trang', 'vien-hai-duong-hoc-nha-trang', 'Đơn vị nghiên cứu có khu trưng bày mẫu vật sinh vật biển.', 'Cầu Đá, Nha Trang', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1415, 133, 112, 'Eo Gió', 'eo-gio', 'Cung bờ biển uốn cong giữa các vách núi đá.', 'Nhơn Lý, Quy Nhơn', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1416, 133, 112, 'Kỳ Co', 'ky-co', 'Bãi biển nổi bật với nước trong và cảnh quan núi đá ven bờ.', 'Nhơn Lý, Quy Nhơn', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1417, 133, 114, 'Tháp Đôi Quy Nhơn', 'thap-doi-quy-nhon', 'Cặp tháp Chăm Pa còn lại ngay trong đô thị Quy Nhơn.', 'Trần Hưng Đạo, Quy Nhơn', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1418, 133, 106, 'Ghềnh Ráng Tiên Sa', 'ghenh-rang-tien-sa', 'Khu ghềnh đá sát biển, có lối đi bộ và điểm ngắm cảnh.', 'Ghềnh Ráng, Quy Nhơn', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1419, 133, 112, 'Hòn Khô', 'hon-kho', 'Đảo nhỏ có bãi đá và hoạt động khám phá biển.', 'Nhơn Hải, Quy Nhơn', 0.0, NULL, NULL, 180, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1420, 133, 114, 'Chùa Thiên Hưng', 'chua-thien-hung', 'Ngôi chùa có sân vườn rộng và các công trình kiến trúc Phật giáo.', 'An Nhơn, vùng Quy Nhơn', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1421, 134, 106, 'Ghềnh Đá Đĩa', 'ghenh-da-dia', 'Thắng cảnh các khối đá bazan xếp thành cột bên bờ biển.', 'An Ninh Đông, Tuy An, vùng Phú Yên', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1422, 134, 112, 'Mũi Điện', 'mui-dien', 'Mũi đất có hải đăng và lối xuống bãi Môn.', 'Hòa Tâm, Đông Hòa, vùng Phú Yên', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1423, 134, 109, 'Tháp Nghinh Phong', 'thap-nghinh-phong', 'Công trình kiến trúc ven biển lấy cảm hứng từ ghềnh đá tự nhiên.', 'Quảng trường Nghinh Phong, Tuy Hòa', 0.0, NULL, NULL, 45, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1424, 134, 112, 'Bãi Xép Phú Yên', 'bai-xep-phu-yen', 'Bãi biển nhỏ có bãi cỏ xanh và ghềnh đá hai bên.', 'An Chấn, Tuy An, vùng Phú Yên', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1425, 135, 106, 'Đồi cát bay Mũi Né', 'doi-cat-bay-mui-ne', 'Đồi cát đổi hình theo gió, thích hợp ngắm cảnh và chụp ảnh.', 'Mũi Né, Phan Thiết', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1426, 135, 106, 'Bàu Trắng', 'bau-trang', 'Hồ nước nằm cạnh đồi cát trắng, nổi tiếng với cảnh quan cát và nước.', 'Hòa Thắng, vùng Phan Thiết', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1427, 135, 106, 'Suối Tiên Mũi Né', 'suoi-tien-mui-ne', 'Dòng suối cạn chảy giữa những vách cát đỏ cam.', 'Hàm Tiến, Phan Thiết', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1428, 135, 114, 'Tháp Poshanu', 'thap-poshanu', 'Di tích tháp Chăm Pa trên đồi gần khu vực Phú Hài.', 'Phú Hài, Phan Thiết', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1429, 135, 108, 'Làng chài Mũi Né', 'lang-chai-mui-ne', 'Bến thuyền và làng chài nhộn nhịp với hải sản vào buổi sáng.', 'Mũi Né, Phan Thiết', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1430, 136, 112, 'Bãi Sau Vũng Tàu', 'bai-sau-vung-tau', 'Bãi biển dài dọc khu đô thị với hoạt động tắm biển và nghỉ dưỡng.', 'Thùy Vân, Vũng Tàu', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1431, 136, 107, 'Tượng Chúa Kitô Vua', 'tuong-chua-kito-vua', 'Tượng tôn giáo lớn trên núi, có đường bậc thang dẫn lên.', 'Núi Nhỏ, Vũng Tàu', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1432, 136, 107, 'Hải đăng Vũng Tàu', 'hai-dang-vung-tau', 'Hải đăng lâu đời nằm trên cao nhìn ra thành phố biển.', 'Núi Nhỏ, Vũng Tàu', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1433, 136, 107, 'Bạch Dinh Vũng Tàu', 'bach-dinh-vung-tau', 'Dinh thự lịch sử trên sườn Núi Lớn với không gian trưng bày.', 'Trần Phú, Vũng Tàu', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1434, 136, 109, 'Khu du lịch Hồ Mây', 'ho-may-park', 'Khu du lịch vui chơi trên núi có cáp treo và các trò giải trí.', 'Núi Lớn, Vũng Tàu', 0.0, NULL, NULL, 240, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1435, 137, 107, 'Di tích Nhà tù Côn Đảo', 'di-tich-nha-tu-con-dao', 'Quần thể nhà tù lịch sử phản ánh nhiều giai đoạn giam giữ tù nhân.', 'Côn Sơn, Côn Đảo', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1436, 137, 107, 'Nghĩa trang Hàng Dương', 'nghia-trang-hang-duong', 'Nơi an nghỉ của nhiều chiến sĩ cách mạng và người yêu nước.', 'Côn Sơn, Côn Đảo', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1437, 137, 112, 'Bãi Đầm Trầu', 'bai-dam-trau', 'Bãi biển hình vòng cung với cát vàng và các ghềnh đá.', 'Gần sân bay Côn Đảo', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1438, 137, 112, 'Hòn Bảy Cạnh', 'hon-bay-canh', 'Đảo có hệ sinh thái rừng và những vùng biển được bảo tồn.', 'Vùng biển Côn Đảo', 0.0, NULL, NULL, 240, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1439, 138, 108, 'Chợ nổi Cái Răng', 'cho-noi-cai-rang', 'Chợ mua bán trên sông, nổi bật với ghe thuyền và nông sản buổi sáng.', 'Cái Răng, Cần Thơ', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1440, 138, 106, 'Bến Ninh Kiều', 'ben-ninh-kieu', 'Công viên ven sông Hậu có bến thuyền và khu phố đi bộ.', 'Ninh Kiều, Cần Thơ', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1441, 138, 107, 'Nhà cổ Bình Thủy', 'nha-co-binh-thuy', 'Ngôi nhà cổ của gia đình Nam Bộ với không gian kiến trúc giao thoa.', 'Bình Thủy, Cần Thơ', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1442, 138, 114, 'Thiền viện Trúc Lâm Phương Nam', 'thien-vien-truc-lam-phuong-nam', 'Thiền viện có khuôn viên cây xanh và các hạng mục kiến trúc truyền thống.', 'Phong Điền, Cần Thơ', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1443, 139, 106, 'Cồn Phụng', 'con-phung', 'Cù lao trên sông Tiền với trải nghiệm vườn cây và thủ công xứ dừa.', 'Châu Thành, Bến Tre', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1444, 139, 109, 'Khu du lịch Lan Vương', 'khu-du-lich-lan-vuong', 'Khu sinh thái có các trò chơi dân gian và trải nghiệm miệt vườn.', 'Thành phố Bến Tre', 0.0, NULL, NULL, 180, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1445, 139, 108, 'Vườn trái cây Cái Mơn', 'vuon-trai-cay-cai-mon', 'Vùng trồng cây ăn trái và cây giống nổi tiếng.', 'Chợ Lách, Bến Tre', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1446, 139, 106, 'Sân chim Vàm Hồ', 'san-chim-vam-ho', 'Khu sinh thái rừng ngập mặn là nơi cư trú của nhiều loài chim.', 'Ba Tri, Bến Tre', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1447, 140, 114, 'Miếu Bà Chúa Xứ Núi Sam', 'mieu-ba-chua-xu-nui-sam', 'Điểm hành hương tín ngưỡng quan trọng dưới chân Núi Sam.', 'Núi Sam, Châu Đốc', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1448, 140, 106, 'Rừng tràm Trà Sư', 'rung-tram-tra-su', 'Khu rừng ngập nước với tuyến xuồng len qua thảm bèo xanh.', 'Tịnh Biên, vùng An Giang', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1449, 140, 107, 'Lăng Thoại Ngọc Hầu', 'lang-thoai-ngoc-hau', 'Khu lăng mộ gắn với danh nhân Thoại Ngọc Hầu.', 'Núi Sam, Châu Đốc', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1450, 140, 114, 'Chùa Tây An', 'chua-tay-an', 'Ngôi chùa lâu đời với kiến trúc kết hợp nhiều ảnh hưởng.', 'Núi Sam, Châu Đốc', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1451, 141, 107, 'Bảo tàng Thế giới Cà phê', 'bao-tang-the-gioi-ca-phe', 'Không gian kiến trúc và trưng bày về văn hóa cà phê thế giới.', 'Tân Lợi, Buôn Ma Thuột', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1452, 141, 106, 'Thác Dray Nur', 'thac-dray-nur', 'Thác nước lớn trên hệ thống sông Sêrêpốk.', 'Vùng Krông Ana, Đắk Lắk', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1453, 141, 108, 'Làng cà phê Trung Nguyên', 'lang-ca-phe-trung-nguyen', 'Khu trải nghiệm cà phê kết hợp không gian cây xanh và kiến trúc Tây Nguyên.', 'Trung tâm Buôn Ma Thuột', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1454, 141, 106, 'Vườn quốc gia Yok Đôn', 'vuon-quoc-gia-yok-don', 'Khu rừng khộp đặc trưng với hoạt động tìm hiểu hệ sinh thái.', 'Vùng Buôn Đôn, Đắk Lắk', 0.0, NULL, NULL, 240, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1455, 142, 106, 'Biển Hồ Tơ Nưng', 'bien-ho-to-nung', 'Hồ tự nhiên trên cao nguyên với bờ hồ xanh và rừng cây.', 'Biển Hồ, Pleiku', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1456, 142, 113, 'Núi lửa Chư Đăng Ya', 'nui-lua-chu-dang-ya', 'Miệng núi lửa đã ngừng hoạt động với ruộng nương trên sườn núi.', 'Chư Păh, vùng Pleiku', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1457, 142, 114, 'Chùa Minh Thành', 'chua-minh-thanh', 'Ngôi chùa có kiến trúc nhiều tầng và khu vườn chăm sóc công phu.', 'Pleiku, Gia Lai', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1458, 142, 106, 'Biển Hồ chè', 'bien-ho-che', 'Đồi chè xanh với lối đi giữa các hàng cây và khung cảnh cao nguyên.', 'Vùng đồi chè Biển Hồ, Pleiku', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1459, 143, 113, 'Núi Bà Đen', 'nui-ba-den', 'Ngọn núi cao nổi bật của Nam Bộ với cáp treo và điểm hành hương.', 'Tây Ninh', 0.0, NULL, NULL, 240, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1460, 143, 114, 'Tòa Thánh Cao Đài', 'toa-thanh-cao-dai', 'Trung tâm tín ngưỡng Cao Đài với kiến trúc nhiều màu sắc.', 'Hòa Thành, Tây Ninh', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1461, 143, 106, 'Hồ Dầu Tiếng', 'ho-dau-tieng', 'Hồ nước rộng có những bãi cỏ và điểm ngắm cảnh ven hồ.', 'Vùng hồ Dầu Tiếng, Tây Ninh', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1462, 143, 114, 'Chùa Gò Kén', 'chua-go-ken', 'Ngôi chùa lâu đời có sân vườn và kiến trúc Phật giáo.', 'Hòa Thành, Tây Ninh', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1463, 144, 107, 'Nhà thờ đá Tam Đảo', 'nha-tho-da-tam-dao', 'Nhà thờ đá cổ nằm trên sườn đồi gần quảng trường thị trấn.', 'Trung tâm Tam Đảo', 0.0, NULL, NULL, 60, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1464, 144, 106, 'Thác Bạc Tam Đảo', 'thac-bac-tam-dao', 'Thác trong rừng với lối xuống dốc và nhiều bậc đá.', 'Tam Đảo', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1465, 144, 106, 'Cầu Mây Tam Đảo', 'cau-may-tam-dao', 'Điểm ngắm cảnh và chụp ảnh giữa sườn núi có nhiều mây.', 'Tam Đảo', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1466, 144, 114, 'Thiền viện Trúc Lâm Tây Thiên', 'thien-vien-truc-lam-tay-thien', 'Khu thiền viện giữa núi rừng gần vùng danh thắng Tây Thiên.', 'Đại Đình, Tam Đảo', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1467, 145, 114, 'Thành nhà Hồ', 'thanh-nha-ho', 'Di sản thành đá cổ được xây dựng bằng các khối đá lớn.', 'Vĩnh Lộc, Thanh Hóa', 0.0, NULL, NULL, 120, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1468, 145, 112, 'Bãi biển Sầm Sơn', 'bai-bien-sam-son', 'Bãi biển du lịch nổi tiếng của Bắc Trung Bộ.', 'Sầm Sơn, Thanh Hóa', 0.0, NULL, NULL, 150, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1469, 145, 113, 'Khu bảo tồn thiên nhiên Pù Luông', 'khu-bao-ton-thien-nhien-pu-luong', 'Cảnh quan ruộng bậc thang, bản làng và rừng núi.', 'Bá Thước, Thanh Hóa', 0.0, NULL, NULL, 240, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1470, 145, 106, 'Suối cá thần Cẩm Lương', 'suoi-ca-than-cam-luong', 'Dòng suối nhỏ nổi tiếng với đàn cá tự nhiên dày đặc.', 'Cẩm Thủy, Thanh Hóa', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1471, 146, 106, 'Làng hoa Sa Đéc', 'lang-hoa-sa-dec', 'Vùng làng nghề trồng hoa và cây cảnh nổi tiếng của miền Tây.', 'Sa Đéc, Đồng Tháp', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1472, 146, 106, 'Vườn quốc gia Tràm Chim', 'vuon-quoc-gia-tram-chim', 'Khu đất ngập nước bảo tồn các loài chim và hệ sinh thái mùa nước nổi.', 'Tam Nông, Đồng Tháp', 0.0, NULL, NULL, 240, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1473, 146, 107, 'Nhà cổ Huỳnh Thủy Lê', 'nha-co-huynh-thuy-le', 'Ngôi nhà cổ có kiến trúc pha trộn phong cách Việt - Hoa - Pháp.', 'Nguyễn Huệ, Sa Đéc', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1474, 146, 107, 'Khu di tích Xẻo Quýt', 'khu-di-tich-xeo-quyt', 'Khu căn cứ lịch sử nằm giữa rừng tràm và các đường mòn nhỏ.', 'Cao Lãnh, Đồng Tháp', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1475, 147, 106, 'Thạch Động', 'thach-dong', 'Núi đá vôi có hang động và lối đi lên điểm ngắm cảnh.', 'Bình San, Hà Tiên', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1476, 147, 112, 'Bãi biển Mũi Nai', 'bai-bien-mui-nai', 'Bãi biển ở phía tây nam Hà Tiên với không gian nghỉ dưỡng.', 'Pháo Đài, Hà Tiên', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1477, 147, 113, 'Núi Đá Dựng', 'nui-da-dung', 'Khu núi đá vôi với những hang nhỏ và đường tham quan.', 'Mỹ Đức, Hà Tiên', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1478, 148, 106, 'Cổng Tò Vò', 'cong-to-vo', 'Vòm đá núi lửa tự nhiên ven biển, điểm ngắm hoàng hôn phổ biến.', 'Đảo Lớn, Lý Sơn', 0.0, NULL, NULL, 60, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1479, 148, 114, 'Chùa Hang Lý Sơn', 'chua-hang-ly-son', 'Ngôi chùa nằm trong hang đá ven sườn núi trên đảo.', 'Đảo Lớn, Lý Sơn', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1480, 148, 113, 'Đỉnh Thới Lới', 'dinh-thoi-loi', 'Miệng núi lửa cũ có tầm nhìn toàn cảnh đảo và vùng biển.', 'Đảo Lớn, Lý Sơn', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1481, 149, 106, 'Cù lao An Bình', 'cu-lao-an-binh', 'Cù lao sông Tiền có vườn trái cây và trải nghiệm sinh hoạt miệt vườn.', 'Long Hồ, Vĩnh Long', 0.0, NULL, NULL, 150, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1482, 149, 114, 'Văn Thánh Miếu Vĩnh Long', 'van-thanh-mieu-vinh-long', 'Di tích thờ Khổng Tử và tôn vinh truyền thống hiếu học.', 'Thành phố Vĩnh Long', 0.0, NULL, NULL, 60, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1483, 149, 107, 'Nhà cổ Cai Cường', 'nha-co-cai-cuong', 'Nhà cổ Nam Bộ với nhiều chi tiết trang trí gỗ.', 'Cù lao An Bình, Vĩnh Long', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1484, 150, 114, 'Khu du lịch Tam Chúc', 'khu-du-lich-tam-chuc', 'Quần thể chùa và hồ nước giữa các dãy núi đá vôi.', 'Kim Bảng, Hà Nam', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1485, 150, 114, 'Địa Tạng Phi Lai Tự', 'dia-tang-phi-lai-tu', 'Ngôi chùa yên tĩnh có khu vườn và sân sỏi trắng.', 'Thanh Liêm, Hà Nam', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1486, 150, 114, 'Chùa Bà Đanh', 'chua-ba-danh', 'Ngôi chùa cổ bên sông Đáy gắn với nhiều câu chuyện dân gian.', 'Kim Bảng, Hà Nam', 0.0, NULL, NULL, 75, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1487, 151, 107, 'Nhà tù Sơn La', 'nha-tu-son-la', 'Di tích nhà tù được xây dựng trong thời kỳ thuộc địa.', 'Đồi Khau Cả, Sơn La', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1488, 151, 106, 'Hồ thủy điện Sơn La', 'ho-thuy-dien-son-la', 'Hồ nước lớn giữa các dãy núi với tuyến du lịch lòng hồ.', 'Vùng Quỳnh Nhai, Sơn La', 0.0, NULL, NULL, 180, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1489, 151, 107, 'Bản Mòng Sơn La', 'ban-mong-son-la', 'Bản văn hóa dân tộc Thái với dịch vụ nghỉ dưỡng suối nước nóng.', 'Hua La, Sơn La', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1490, 152, 106, 'Hồ Ba Bể', 'ho-ba-be', 'Hồ nước ngọt tự nhiên nằm giữa núi đá vôi và rừng.', 'Vườn quốc gia Ba Bể, Bắc Kạn', 0.0, NULL, NULL, 240, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1491, 152, 106, 'Động Puông', 'dong-puong', 'Hang xuyên núi có dòng sông Năng chảy qua.', 'Vườn quốc gia Ba Bể, Bắc Kạn', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1492, 152, 106, 'Thác Đầu Đẳng', 'thac-dau-dang', 'Thác ghềnh trên sông Năng giữa khung cảnh núi rừng.', 'Vùng Ba Bể, Bắc Kạn', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1493, 153, 114, 'Tháp Po Klong Garai', 'thap-po-klong-garai', 'Quần thể tháp Chăm Pa nổi bật trên đồi Trầu.', 'Phan Rang - Tháp Chàm, Ninh Thuận', 0.0, NULL, NULL, 90, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1494, 153, 112, 'Vịnh Vĩnh Hy', 'vinh-vinh-hy', 'Vịnh biển được bao bọc bởi núi, có hoạt động tàu đáy kính.', 'Vĩnh Hải, Ninh Thuận', 0.0, NULL, NULL, 180, 0.0, 1, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1495, 153, 106, 'Hang Rái', 'hang-rai', 'Bãi đá san hô cổ và ghềnh đá sát biển với nhiều tầng địa hình.', 'Vĩnh Hải, Ninh Thuận', 0.0, NULL, NULL, 120, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1496, 153, 108, 'Làng nho Thái An', 'lang-nho-thai-an', 'Vùng trồng nho ven biển có trải nghiệm thăm vườn và mua nông sản.', 'Vĩnh Hải, Ninh Thuận', 0.0, NULL, NULL, 90, 0.0, 0, '2026-10-21 05:30:00', '2026-10-21 05:30:00', 0),
(1497, 154, 107, 'Đồi A1', 'doi-a1', 'Di tích cứ điểm trên đồi, lưu giữ dấu tích chiến đấu trong chiến dịch Điện Biên Phủ.', 'Khu vực đồi A1, Điện Biên Phủ', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1498, 154, 107, 'Hầm Đờ Cát', 'ham-do-cat', 'Hầm chỉ huy của quân Pháp tại tập đoàn cứ điểm Điện Biên Phủ.', 'Khu vực Mường Thanh, Điện Biên Phủ', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1499, 154, 107, 'Bảo tàng Chiến thắng lịch sử Điện Biên Phủ', 'bao-tang-chien-thang-lich-su-dien-bien-phu', 'Bảo tàng giới thiệu tư liệu, hiện vật và không gian tái hiện chiến dịch Điện Biên Phủ.', 'Đường Võ Nguyên Giáp, Điện Biên Phủ', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1500, 154, 107, 'Sở Chỉ huy chiến dịch Điện Biên Phủ tại Mường Phăng', 'so-chi-huy-chien-dich-dien-bien-phu-tai-muong-phang', 'Khu di tích trong rừng Mường Phăng gắn với cơ quan chỉ huy chiến dịch năm 1954.', 'Mường Phăng, Điện Biên', 0, NULL, NULL, 150, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1501, 155, 106, 'Quần thể động Pu Sam Cáp', 'quan-the-dong-pu-sam-cap', 'Hệ thống hang động đá vôi có nhiều nhũ đá trên dãy Pu Sam Cáp.', 'Dãy Pu Sam Cáp, khu vực Lai Châu', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1502, 155, 114, 'Bản Sin Suối Hồ', 'ban-sin-suoi-ho', 'Bản du lịch cộng đồng của người Mông với nhà truyền thống, vườn hoa và cảnh núi.', 'Sin Suối Hồ, Lai Châu', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1503, 155, 114, 'Bản Sì Thâu Chải', 'ban-si-thau-chai', 'Bản người Dao trên núi có không gian sinh hoạt cộng đồng và tầm nhìn về thung lũng Tam Đường.', 'Khu vực Tam Đường, Lai Châu', 0, NULL, NULL, 150, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1504, 155, 110, 'Chợ San Thàng', 'cho-san-thang', 'Chợ vùng cao với nông sản, món ăn và sản phẩm thủ công của các cộng đồng địa phương.', 'Khu vực San Thàng, Lai Châu', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1505, 156, 106, 'Hồ Thác Bà', 'ho-thac-ba', 'Hồ thủy điện có nhiều đảo nhỏ, thích hợp khám phá cảnh quan mặt nước và làng ven hồ.', 'Vùng hồ Thác Bà, Yên Bái', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1506, 156, 106, 'Ruộng bậc thang Mù Cang Chải', 'ruong-bac-thang-mu-cang-chai', 'Cảnh quan ruộng bậc thang trên sườn núi, gắn với hoạt động canh tác của người Mông.', 'Khu vực Mù Cang Chải', 0, NULL, NULL, 240, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1507, 156, 113, 'Đèo Khau Phạ', 'deo-khau-pha', 'Cung đèo trên quốc lộ 32 nhìn xuống các thung lũng và ruộng bậc thang vùng cao.', 'Quốc lộ 32, khu vực Cao Phạ', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1508, 156, 106, 'Thung lũng Tú Lệ', 'thung-lung-tu-le', 'Thung lũng trồng lúa nếp với bản làng người Thái dưới chân đèo Khau Phạ.', 'Khu vực Tú Lệ', 0, NULL, NULL, 150, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1509, 157, 106, 'Động Tam Thanh', 'dong-tam-thanh', 'Hang động đá vôi có không gian thờ Phật và những dấu tích văn hóa lâu đời.', 'Khu vực Tam Thanh, Lạng Sơn', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1510, 157, 106, 'Động Nhị Thanh', 'dong-nhi-thanh', 'Hang tự nhiên có dòng suối Ngọc Tuyền chảy qua và nhiều văn bia trên vách đá.', 'Khu vực Tam Thanh, Lạng Sơn', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1511, 157, 107, 'Thành nhà Mạc Lạng Sơn', 'thanh-nha-mac-lang-son', 'Dấu tích thành cổ bằng đá nằm giữa các khối núi ở khu vực trung tâm xứ Lạng.', 'Khu vực Tam Thanh, Lạng Sơn', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1512, 157, 106, 'Núi Tô Thị', 'nui-to-thi', 'Khối núi gắn với truyền thuyết nàng Tô Thị bồng con chờ chồng.', 'Khu vực Tam Thanh, Lạng Sơn', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1513, 158, 114, 'Đền Đô', 'den-do', 'Quần thể đền thờ các vị vua nhà Lý tại vùng đất Đình Bảng.', 'Đình Bảng, khu vực Từ Sơn, Bắc Ninh', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1514, 158, 114, 'Chùa Dâu', 'chua-dau', 'Ngôi chùa cổ của vùng Luy Lâu, gắn với lịch sử Phật giáo và tín ngưỡng Tứ Pháp.', 'Khu vực Thuận Thành, Bắc Ninh', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1515, 158, 114, 'Chùa Bút Tháp', 'chua-but-thap', 'Chùa cổ nổi bật với kiến trúc gỗ, tháp đá và nghệ thuật điêu khắc Phật giáo.', 'Khu vực Thuận Thành, Bắc Ninh', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1516, 158, 114, 'Làng tranh Đông Hồ', 'lang-tranh-dong-ho', 'Làng nghề tranh dân gian sử dụng bản khắc gỗ, giấy điệp và sắc màu truyền thống.', 'Đông Hồ, khu vực Thuận Thành, Bắc Ninh', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1517, 159, 106, 'Hồ Núi Cốc', 'ho-nui-coc', 'Hồ nước giữa đồi núi với các đảo nhỏ và cảnh quan sinh thái trung du.', 'Vùng hồ Núi Cốc, Thái Nguyên', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1518, 159, 108, 'Vùng chè Tân Cương', 'vung-che-tan-cuong', 'Vùng trồng chè nổi tiếng, phù hợp tìm hiểu nghề làm chè và thưởng trà địa phương.', 'Tân Cương, Thái Nguyên', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1519, 159, 107, 'Bảo tàng Văn hóa các dân tộc Việt Nam', 'bao-tang-van-hoa-cac-dan-toc-viet-nam', 'Không gian trưng bày hiện vật, trang phục và đời sống văn hóa của các dân tộc Việt Nam.', 'Đường Đội Cấn, khu vực trung tâm Thái Nguyên', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1520, 159, 107, 'Khu di tích ATK Định Hóa', 'khu-di-tich-atk-dinh-hoa', 'Quần thể di tích an toàn khu gắn với cơ quan lãnh đạo kháng chiến chống Pháp.', 'Khu vực Định Hóa, Thái Nguyên', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1521, 160, 114, 'Khu di tích lịch sử Đền Hùng', 'khu-di-tich-lich-su-den-hung', 'Quần thể đền trên núi Nghĩa Lĩnh gắn với tín ngưỡng thờ cúng Hùng Vương.', 'Núi Nghĩa Lĩnh, khu vực Việt Trì', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1522, 160, 114, 'Đình Hùng Lô', 'dinh-hung-lo', 'Ngôi đình cổ có kiến trúc và nghệ thuật chạm khắc gỗ, gắn với không gian hát xoan.', 'Hùng Lô, khu vực Việt Trì', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1523, 160, 114, 'Miếu Lãi Lèn', 'mieu-lai-len', 'Di tích gắn với truyền thuyết và thực hành hát xoan của các phường xoan vùng Đất Tổ.', 'Khu vực Kim Đức, Việt Trì', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1524, 160, 114, 'Đền Tam Giang', 'den-tam-giang', 'Ngôi đền thuộc quần thể di tích bên vùng hợp lưu sông Bạch Hạc.', 'Khu vực Bạch Hạc, Việt Trì', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1525, 161, 107, 'Quảng trường Hồ Chí Minh tại Vinh', 'quang-truong-ho-chi-minh-tai-vinh', 'Không gian quảng trường và tượng đài Chủ tịch Hồ Chí Minh giữa khu đô thị Vinh.', 'Khu vực trung tâm Vinh, Nghệ An', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1526, 161, 107, 'Bảo tàng Xô viết Nghệ Tĩnh', 'bao-tang-xo-viet-nghe-tinh', 'Bảo tàng trưng bày tư liệu và hiện vật về phong trào Xô viết Nghệ Tĩnh.', 'Đường Đào Tấn, Vinh', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1527, 161, 107, 'Thành cổ Vinh', 'thanh-co-vinh', 'Dấu tích thành cổ với các cổng thành còn được lưu giữ trong khu đô thị.', 'Khu vực thành cổ, Vinh', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1528, 161, 112, 'Bãi biển Cửa Lò', 'bai-bien-cua-lo', 'Bãi biển cát dài của xứ Nghệ với không gian dạo biển và thưởng thức hải sản.', 'Khu vực Cửa Lò, Nghệ An', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1529, 162, 107, 'Khu di tích Ngã ba Đồng Lộc', 'khu-di-tich-nga-ba-dong-loc', 'Khu tưởng niệm thanh niên xung phong tại trọng điểm giao thông trong thời kỳ chiến tranh.', 'Đồng Lộc, khu vực Can Lộc, Hà Tĩnh', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1530, 162, 112, 'Bãi biển Thiên Cầm', 'bai-bien-thien-cam', 'Bãi biển hình cánh cung dưới chân núi Thiên Cầm với cảnh quan ven bờ thoáng rộng.', 'Khu vực Cẩm Xuyên, Hà Tĩnh', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1531, 162, 106, 'Hồ Kẻ Gỗ', 'ho-ke-go', 'Hồ chứa nước lớn giữa vùng đồi rừng, gắn với cảnh quan thiên nhiên Hà Tĩnh.', 'Vùng hồ Kẻ Gỗ, Hà Tĩnh', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1532, 162, 107, 'Khu lưu niệm Đại thi hào Nguyễn Du', 'khu-luu-niem-dai-thi-hao-nguyen-du', 'Không gian tưởng niệm và giới thiệu cuộc đời, sự nghiệp của tác giả Truyện Kiều.', 'Tiên Điền, khu vực Nghi Xuân, Hà Tĩnh', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1533, 163, 114, 'Chùa Thiên Ấn', 'chua-thien-an', 'Ngôi chùa trên núi Thiên Ấn, gắn với cảnh quan và đời sống tâm linh vùng sông Trà.', 'Núi Thiên Ấn, Quảng Ngãi', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1534, 163, 107, 'Khu chứng tích Sơn Mỹ', 'khu-chung-tich-son-my', 'Khu tưởng niệm và trưng bày tư liệu về vụ thảm sát Sơn Mỹ năm 1968.', 'Khu vực Tịnh Khê, Quảng Ngãi', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1535, 163, 112, 'Bãi biển Mỹ Khê Quảng Ngãi', 'bai-bien-my-khe-quang-ngai', 'Bãi biển cát dài gần cửa sông Kinh, tách biệt với bãi biển Mỹ Khê ở Đà Nẵng.', 'Khu vực Tịnh Khê, Quảng Ngãi', 0, NULL, NULL, 150, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1536, 163, 112, 'Bãi biển Sa Huỳnh', 'bai-bien-sa-huynh', 'Bãi biển ven vùng văn hóa Sa Huỳnh, có cảnh quan làng chài và đầm nước gần bờ.', 'Khu vực Sa Huỳnh, Quảng Ngãi', 0, NULL, NULL, 150, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1537, 164, 114, 'Làng bích họa Tam Thanh', 'lang-bich-hoa-tam-thanh', 'Làng chài có tranh tường trên những ngôi nhà, phản ánh sinh hoạt và cảnh sắc miền biển.', 'Khu vực Tam Thanh, Tam Kỳ', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1538, 164, 112, 'Bãi biển Tam Thanh', 'bai-bien-tam-thanh', 'Bãi biển trải dài với hàng phi lao và không gian sinh hoạt của cộng đồng làng chài.', 'Khu vực Tam Thanh, Tam Kỳ', 0, NULL, NULL, 150, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1539, 164, 107, 'Tượng đài Mẹ Việt Nam anh hùng', 'tuong-dai-me-viet-nam-anh-hung', 'Công trình tưởng niệm những bà mẹ Việt Nam anh hùng trên núi Cấm.', 'Núi Cấm, khu vực Tam Phú, Tam Kỳ', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1540, 164, 114, 'Văn Thánh - Khổng Miếu Tam Kỳ', 'van-thanh-khong-mieu-tam-ky', 'Di tích gắn với truyền thống hiếu học và sinh hoạt văn hóa của vùng Tam Kỳ.', 'Khu vực trung tâm Tam Kỳ', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1541, 165, 107, 'Nhà thờ gỗ Kon Tum', 'nha-tho-go-kon-tum', 'Nhà thờ có kết cấu gỗ và kiến trúc kết hợp phong cách phương Tây với nét bản địa.', 'Đường Nguyễn Huệ, khu vực Kon Tum', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1542, 165, 107, 'Cầu treo Kon Klor', 'cau-treo-kon-klor', 'Cầu treo bắc qua sông Đăk Bla, gần không gian làng và nhà rông của người Ba Na.', 'Khu vực Kon Klor, Kon Tum', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1543, 165, 107, 'Nhà ngục Kon Tum', 'nha-nguc-kon-tum', 'Di tích nhà tù thời thuộc địa gắn với phong trào đấu tranh của các chiến sĩ cách mạng.', 'Khu vực Kon Tum, gần sông Đăk Bla', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1544, 165, 114, 'Làng Kon K’Tu', 'lang-kon-ktu', 'Làng truyền thống của người Ba Na bên sông Đăk Bla với nhà rông và sinh hoạt cộng đồng.', 'Khu vực Đăk Rơ Wa, Kon Tum', 0, NULL, NULL, 150, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1545, 166, 106, 'Thác Đambri', 'thac-dambri', 'Thác nước giữa rừng với dòng đổ xuống vách đá và cảnh quan xanh của cao nguyên.', 'Khu vực Đambri, Bảo Lộc', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1546, 166, 108, 'Đồi chè Tâm Châu', 'doi-che-tam-chau', 'Những đồi chè xanh với đường đi giữa các luống chè và không gian tìm hiểu văn hóa trà.', 'Khu vực Lộc Tân, vùng Bảo Lộc', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1547, 166, 114, 'Chùa Linh Quy Pháp Ấn', 'chua-linh-quy-phap-an', 'Ngôi chùa trên đồi có không gian thiền tịnh và tầm nhìn ra núi rừng.', 'Khu vực Lộc Thành, vùng Bảo Lộc', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1548, 166, 106, 'Hồ Nam Phương', 'ho-nam-phuong', 'Hồ nước trong khu vực Bảo Lộc với cảnh quan ven bờ và không gian dạo mát.', 'Khu vực Bảo Lộc', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1549, 167, 114, 'Chùa Vĩnh Tràng', 'chua-vinh-trang', 'Ngôi chùa nổi bật với kiến trúc giao thoa và khuôn viên có nhiều tượng Phật.', 'Đường Nguyễn Trung Trực, khu vực Mỹ Tho', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1550, 167, 106, 'Cù lao Thới Sơn', 'cu-lao-thoi-son', 'Cù lao trên sông Tiền với vườn cây, kênh rạch và trải nghiệm du lịch cộng đồng.', 'Thới Sơn, vùng sông Tiền, Mỹ Tho', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1551, 167, 114, 'Chùa Bửu Lâm Mỹ Tho', 'chua-buu-lam-my-tho', 'Ngôi chùa cổ lưu giữ nhiều chi tiết chạm khắc gỗ và không gian thờ tự truyền thống.', 'Đường Anh Giác, khu vực Mỹ Tho', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1552, 167, 114, 'Đình Điều Hòa', 'dinh-dieu-hoa', 'Ngôi đình có các hạng mục kiến trúc và hiện vật gắn với lịch sử cư dân Mỹ Tho.', 'Đường Trịnh Hoài Đức, khu vực Mỹ Tho', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1553, 168, 106, 'Ao Bà Om', 'ao-ba-om', 'Ao nước được bao quanh bởi cây cổ thụ, gắn với truyền thuyết và lễ hội của người Khmer.', 'Khu danh thắng Ao Bà Om, khu vực Trà Vinh', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1554, 168, 114, 'Chùa Âng', 'chua-ang', 'Ngôi chùa Khmer cổ trong cụm danh thắng Ao Bà Om với mái và hoa văn truyền thống.', 'Cụm danh thắng Ao Bà Om, khu vực Trà Vinh', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1555, 168, 107, 'Bảo tàng Văn hóa dân tộc Khmer Trà Vinh', 'bao-tang-van-hoa-dan-toc-khmer-tra-vinh', 'Bảo tàng giới thiệu hiện vật và đời sống văn hóa của đồng bào Khmer Nam Bộ.', 'Cụm danh thắng Ao Bà Om, khu vực Trà Vinh', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1556, 168, 114, 'Chùa Hang Trà Vinh', 'chua-hang-tra-vinh', 'Ngôi chùa Khmer có cổng dạng hang vòm, khuôn viên cây xanh và nghề điêu khắc gỗ.', 'Khu vực Châu Thành, vùng Trà Vinh', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1557, 169, 114, 'Chùa Dơi', 'chua-doi', 'Ngôi chùa Khmer còn có tên Mahatup, nổi bật với kiến trúc truyền thống và khuôn viên cây lớn.', 'Đường Văn Ngọc Chính, khu vực Sóc Trăng', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1558, 169, 114, 'Chùa Đất Sét', 'chua-dat-set', 'Ngôi chùa còn gọi là Bửu Sơn Tự, được biết đến với nhiều tác phẩm tạo hình bằng đất sét.', 'Đường Tôn Đức Thắng, khu vực Sóc Trăng', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1559, 169, 114, 'Chùa Khleang', 'chua-khleang', 'Chùa Khmer có chính điện và các họa tiết trang trí mang dấu ấn văn hóa Nam Bộ.', 'Đường Tôn Đức Thắng, khu vực Sóc Trăng', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1560, 169, 114, 'Chùa Som Rong', 'chua-som-rong', 'Ngôi chùa Khmer có bảo tháp và tượng Phật nằm trong khuôn viên rộng.', 'Đường Tôn Đức Thắng, khu vực Sóc Trăng', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1561, 170, 107, 'Nhà Công tử Bạc Liêu', 'nha-cong-tu-bac-lieu', 'Ngôi nhà gắn với gia đình Trần Trinh Trạch, lưu giữ dấu ấn kiến trúc và sinh hoạt một thời.', 'Đường Điện Biên Phủ, khu vực Bạc Liêu', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1562, 170, 107, 'Khu lưu niệm nhạc sĩ Cao Văn Lầu', 'khu-luu-niem-nhac-si-cao-van-lau', 'Không gian tưởng niệm tác giả Dạ cổ hoài lang và tìm hiểu nghệ thuật đờn ca tài tử.', 'Đường Ninh Bình, khu vực Bạc Liêu', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1563, 170, 114, 'Chùa Xiêm Cán', 'chua-xiem-can', 'Ngôi chùa Khmer ven biển với khuôn viên rộng và các mảng trang trí nhiều màu sắc.', 'Khu vực Vĩnh Trạch Đông, Bạc Liêu', 0, NULL, NULL, 90, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1564, 170, 106, 'Vườn chim Bạc Liêu', 'vuon-chim-bac-lieu', 'Khu bảo tồn sinh cảnh có nhiều loài chim nước và cảnh quan rừng ven biển.', 'Khu vực Hiệp Thành, Bạc Liêu', 0, NULL, NULL, 150, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1565, 171, 106, 'Khu du lịch Mũi Cà Mau', 'khu-du-lich-mui-ca-mau', 'Điểm tham quan tại Đất Mũi với biểu tượng mốc tọa độ và cảnh quan rừng ngập mặn.', 'Đất Mũi, Cà Mau', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1566, 171, 106, 'Vườn quốc gia U Minh Hạ', 'vuon-quoc-gia-u-minh-ha', 'Khu bảo tồn rừng tràm trên đất than bùn, có hệ sinh thái ngập nước đặc trưng.', 'Vùng U Minh Hạ, Cà Mau', 0, NULL, NULL, 240, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1567, 171, 106, 'Hòn Đá Bạc', 'hon-da-bac', 'Cụm đảo đá ven biển Tây có lối đi nối bờ và những khối đá tự nhiên.', 'Khu vực Đá Bạc, Cà Mau', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1568, 171, 112, 'Bãi biển Khai Long', 'bai-bien-khai-long', 'Bãi biển gần Đất Mũi với cảnh quan bãi bồi và rừng ngập mặn ven bờ.', 'Khu vực Khai Long, Đất Mũi, Cà Mau', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1569, 172, 114, 'Đình Nguyễn Trung Trực Rạch Giá', 'dinh-nguyen-trung-truc-rach-gia', 'Đình thờ anh hùng Nguyễn Trung Trực, là địa điểm sinh hoạt tín ngưỡng của người dân.', 'Đường Nguyễn Công Trứ, khu vực Rạch Giá', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1570, 172, 107, 'Cổng Tam Quan Rạch Giá', 'cong-tam-quan-rach-gia', 'Công trình cổng ba lối mang nét kiến trúc truyền thống tại khu vực đô thị Rạch Giá.', 'Đường Nguyễn Trung Trực, khu vực Rạch Giá', 0, NULL, NULL, 45, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1571, 172, 114, 'Chùa Tam Bảo Rạch Giá', 'chua-tam-bao-rach-gia', 'Ngôi chùa cổ gắn với lịch sử Phật giáo và các hoạt động cách mạng tại Rạch Giá.', 'Đường Sư Thiện Ân, khu vực Rạch Giá', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1572, 172, 114, 'Chùa Láng Cát', 'chua-lang-cat', 'Ngôi chùa Khmer với kiến trúc và không gian sinh hoạt văn hóa cộng đồng tại Rạch Giá.', 'Đường Ngô Quyền, khu vực Rạch Giá', 0, NULL, NULL, 75, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1573, 173, 107, 'Khu lưu niệm Chủ tịch Tôn Đức Thắng', 'khu-luu-niem-chu-tich-ton-duc-thang', 'Quần thể lưu niệm tại quê hương Chủ tịch Tôn Đức Thắng, gồm nhà lưu niệm và không gian trưng bày.', 'Mỹ Hòa Hưng, khu vực Long Xuyên', 0, NULL, NULL, 120, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1574, 173, 114, 'Đình Mỹ Phước', 'dinh-my-phuoc', 'Ngôi đình cổ gắn với lịch sử khai phá và tín ngưỡng cộng đồng vùng Long Xuyên.', 'Khu vực Mỹ Phước, Long Xuyên', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1575, 173, 114, 'Chùa Ông Bắc', 'chua-ong-bac', 'Cơ sở thờ tự còn có tên Bắc Đế miếu, mang dấu ấn văn hóa của cộng đồng người Hoa.', 'Đường Phạm Hồng Thái, khu vực Long Xuyên', 0, NULL, NULL, 60, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0),
(1576, 173, 106, 'Cù lao Ông Hổ', 'cu-lao-ong-ho', 'Cù lao trên sông Hậu có vườn cây, nhà dân và trải nghiệm sinh hoạt miệt vườn.', 'Mỹ Hòa Hưng, vùng sông Hậu, Long Xuyên', 0, NULL, NULL, 180, 0, 0, '2026-10-24 00:00:00', '2026-10-24 00:00:00', 0);




-- --------------------------------------------------------

--

-- Dữ liệu bổ sung cho destinations
INSERT INTO `destinations` (`id`, `city_id`, `category_id`, `name`, `slug`, `description`, `address`, `entrance_fee`, `opening_time`, `closing_time`, `duration`, `rating`, `is_featured`, `created_at`, `updated_at`, `is_lodging`) VALUES
(1577, 174, 119, 'Đền Trần Nam Định', 'den-tran-nam-dinh', 'Quần thể đền thờ các vua Trần, nơi diễn ra lễ khai ấn đầu xuân.', 'Phường Lộc Vượng, khu vực Nam Định', 0.00, NULL, NULL, 150, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1578, 174, 114, 'Tháp Phổ Minh', 'thap-pho-minh', 'Tháp gạch thời Trần nằm trong khu di tích chùa Phổ Minh.', 'Khu di tích Tức Mặc, Nam Định', 0.00, NULL, NULL, 180, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1579, 174, 119, 'Chùa Cổ Lễ', 'chua-co-le', 'Ngôi chùa nổi bật với tháp Cửu Phẩm Liên Hoa và kiến trúc pha trộn Đông - Tây.', 'Khu vực Cổ Lễ, Nam Định', 0.00, NULL, NULL, 60, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1580, 174, 119, 'Nhà thờ Phú Nhai', 'nha-tho-phu-nhai', 'Vương cung thánh đường nổi tiếng với kiến trúc Gothic và hai tháp chuông.', 'Xuân Trường, Nam Định', 0.00, NULL, NULL, 75, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1581, 174, 119, 'Phủ Dầy', 'phu-day', 'Quần thể di tích gắn với tín ngưỡng thờ Mẫu Liễu Hạnh.', 'Vụ Bản, Nam Định', 0.00, NULL, NULL, 150, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1582, 175, 114, 'Chùa Keo Thái Bình', 'chua-keo-thai-binh', 'Ngôi chùa cổ nổi bật với gác chuông bằng gỗ và kiến trúc truyền thống.', 'Vũ Thư, Thái Bình', 0.00, NULL, NULL, 90, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1583, 175, 119, 'Đền Tiên La', 'den-tien-la', 'Di tích thờ nữ tướng Bát Nàn, gắn với truyền thuyết thời Hai Bà Trưng.', 'Hưng Hà, Thái Bình', 0.00, NULL, NULL, 75, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1584, 175, 112, 'Biển Cồn Vành', 'bien-con-vanh', 'Bãi biển cửa sông với rừng ngập mặn và hoạt động đánh bắt ven bờ.', 'Tiền Hải, Thái Bình', 0.00, NULL, NULL, 75, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1585, 175, 117, 'Khu sinh thái Cồn Đen', 'khu-sinh-thai-con-den', 'Cồn cát ven biển với rừng phi lao và không gian dã ngoại.', 'Thái Thụy, Thái Bình', 0.00, NULL, NULL, 180, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1586, 175, 121, 'Làng vườn Bách Thuận', 'lang-vuon-bach-thuan', 'Làng vườn truyền thống với cây cảnh, cây ăn quả và nhà cổ.', 'Vũ Thư, Thái Bình', 0.00, NULL, NULL, 60, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1587, 176, 114, 'Khu di tích Côn Sơn', 'khu-di-tich-con-son', 'Di tích gắn với Nguyễn Trãi, rừng thông và các công trình Phật giáo.', 'Chí Linh, Hải Dương', 0.00, NULL, NULL, 60, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1588, 176, 114, 'Khu di tích Kiếp Bạc', 'khu-di-tich-kiep-bac', 'Không gian thờ Hưng Đạo Đại Vương Trần Quốc Tuấn bên sông Lục Đầu.', 'Chí Linh, Hải Dương', 0.00, NULL, NULL, 120, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1589, 176, 117, 'Đảo Cò Chi Lăng Nam', 'dao-co-chi-lang-nam', 'Khu vực hồ An Dương có đàn cò và vạc sinh sống theo mùa.', 'Thanh Miện, Hải Dương', 0.00, NULL, NULL, 180, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1590, 176, 118, 'Làng gốm Chu Đậu', 'lang-gom-chu-dau', 'Làng nghề gốm sứ có lịch sử lâu đời, nổi tiếng với dòng gốm Chu Đậu.', 'Nam Sách, Hải Dương', 0.00, NULL, NULL, 120, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1591, 176, 119, 'Chùa Thanh Mai', 'chua-thanh-mai', 'Ngôi chùa trên núi Tam Ban gắn với Thiền phái Trúc Lâm.', 'Chí Linh, Hải Dương', 0.00, NULL, NULL, 75, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1592, 177, 114, 'Phố Hiến', 'pho-hien', 'Thương cảng xưa lưu dấu sự giao thoa văn hóa Việt, Hoa và nhiều cộng đồng.', 'Khu vực Phố Hiến, Hưng Yên', 0.00, NULL, NULL, 180, 5.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1593, 177, 119, 'Chùa Chuông', 'chua-chuong', 'Danh thắng Phố Hiến với hệ tượng Phật và kiến trúc chùa Bắc Bộ.', 'Phố Hiến, Hưng Yên', 0.00, NULL, NULL, 120, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1594, 177, 119, 'Đền Mẫu Hưng Yên', 'den-mau-hung-yen', 'Ngôi đền bên hồ Bán Nguyệt, thuộc quần thể di tích Phố Hiến.', 'Phố Hiến, Hưng Yên', 0.00, NULL, NULL, 150, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1595, 177, 114, 'Làng Nôm', 'lang-nom', 'Làng cổ còn lưu giữ cầu đá, cổng làng và nhiều công trình tín ngưỡng.', 'Văn Lâm, Hưng Yên', 0.00, NULL, NULL, 75, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1596, 177, 114, 'Văn Miếu Xích Đằng', 'van-mieu-xich-dang', 'Di tích tôn vinh truyền thống khoa bảng và hiếu học ở xứ Nhãn.', 'Phố Hiến, Hưng Yên', 0.00, NULL, NULL, 180, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1597, 178, 119, 'Chùa Vĩnh Nghiêm', 'chua-vinh-nghiem', 'Trung tâm Phật giáo Trúc Lâm nổi tiếng với kho mộc bản cổ.', 'Yên Dũng, Bắc Giang', 0.00, NULL, NULL, 75, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1598, 178, 115, 'Khu du lịch sinh thái Suối Mỡ', 'khu-du-lich-sinh-thai-suoi-mo', 'Dòng suối và thác nhỏ giữa rừng, gắn với truyền thuyết công chúa Quế Mỵ Nương.', 'Lục Nam, Bắc Giang', 0.00, NULL, NULL, 120, 3.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1599, 178, 119, 'Khu du lịch tâm linh Tây Yên Tử', 'khu-du-lich-tam-linh-tay-yen-tu', 'Tuyến tham quan bên sườn tây dãy Yên Tử với cảnh núi rừng và công trình Phật giáo.', 'Sơn Động, Bắc Giang', 0.00, NULL, NULL, 90, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1600, 178, 117, 'Rừng nguyên sinh Khe Rỗ', 'rung-nguyen-sinh-khe-ro', 'Khu rừng tự nhiên với suối, thác và nhiều loài động thực vật.', 'Sơn Động, Bắc Giang', 0.00, NULL, NULL, 120, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1601, 178, 122, 'Bản Ven', 'ban-ven', 'Bản du lịch cộng đồng của người Cao Lan, gắn với đồi chè và ẩm thực bản địa.', 'Yên Thế, Bắc Giang', 0.00, NULL, NULL, 90, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1602, 179, 114, 'Thành cổ Quảng Trị', 'thanh-co-quang-tri', 'Di tích gắn với trận chiến 81 ngày đêm năm 1972.', 'Khu vực thành cổ Quảng Trị', 0.00, NULL, NULL, 75, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1603, 179, 114, 'Cầu Hiền Lương', 'cau-hien-luong', 'Cây cầu lịch sử qua sông Bến Hải tại khu vực giới tuyến cũ.', 'Vĩnh Linh, Quảng Trị', 0.00, NULL, NULL, 90, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1604, 179, 114, 'Địa đạo Vịnh Mốc', 'dia-dao-vinh-moc', 'Hệ thống hầm ngầm của cư dân ven biển trong thời chiến.', 'Vĩnh Linh, Quảng Trị', 0.00, NULL, NULL, 120, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1605, 179, 114, 'Nghĩa trang Liệt sĩ Trường Sơn', 'nghia-trang-liet-si-truong-son', 'Công trình tưởng niệm những người hy sinh trên tuyến đường Trường Sơn.', 'Gio Linh, Quảng Trị', 0.00, NULL, NULL, 60, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1606, 179, 125, 'Đảo Cồn Cỏ', 'dao-con-co', 'Đảo nhỏ ngoài khơi với hệ sinh thái biển và dấu tích lịch sử.', 'Đặc khu đảo Cồn Cỏ, Quảng Trị', 0.00, NULL, NULL, 150, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1607, 180, 112, 'Bãi biển Nhật Lệ', 'bai-bien-nhat-le', 'Bãi cát dài gần cửa sông Nhật Lệ, phù hợp tắm biển và dạo chiều.', 'Ven biển Đồng Hới, Quảng Bình', 0.00, NULL, NULL, 90, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1608, 180, 106, 'Cồn cát Quang Phú', 'con-cat-quang-phu', 'Đồi cát ven biển với những dải cát trắng thay đổi hình dáng theo gió.', 'Quang Phú, Đồng Hới', 0.00, NULL, NULL, 60, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1609, 180, 114, 'Tượng đài Mẹ Suốt', 'tuong-dai-me-suot', 'Tượng đài tưởng nhớ người mẹ chèo đò đưa bộ đội qua sông Nhật Lệ.', 'Bờ sông Nhật Lệ, Đồng Hới', 0.00, NULL, NULL, 60, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1610, 180, 114, 'Quảng Bình Quan', 'quang-binh-quan', 'Di tích cổng thành trong hệ thống lũy phòng thủ phía nam kinh đô xưa.', 'Trung tâm Đồng Hới', 0.00, NULL, NULL, 75, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1611, 180, 112, 'Bãi biển Bảo Ninh', 'bai-bien-bao-ninh', 'Bãi biển trên bán đảo Bảo Ninh, đối diện khu trung tâm Đồng Hới.', 'Bảo Ninh, Đồng Hới', 0.00, NULL, NULL, 75, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1612, 181, 115, 'Thác Pa Sỹ', 'thac-pa-sy', 'Thác nước giữa rừng trên cao nguyên Măng Đen, được tạo bởi ba dòng suối hội tụ.', 'Măng Đen, vùng Kon Plông', 0.00, NULL, NULL, 60, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1613, 181, 115, 'Hồ Đắk Ke', 'ho-dak-ke', 'Hồ nước trong rừng thông với đường đi bộ và cảnh quan yên tĩnh.', 'Măng Đen, vùng Kon Plông', 0.00, NULL, NULL, 90, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1614, 181, 119, 'Chùa Khánh Lâm', 'chua-khanh-lam', 'Ngôi chùa trên đồi, đường lên có bậc đá giữa rừng.', 'Măng Đen, vùng Kon Plông', 0.00, NULL, NULL, 120, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1615, 181, 122, 'Làng Kon Bring', 'lang-kon-bring', 'Làng văn hóa cộng đồng có nhà rông và nét sinh hoạt truyền thống Tây Nguyên.', 'Măng Đen, vùng Kon Plông', 0.00, NULL, NULL, 180, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1616, 181, 123, 'Đèo Măng Đen', 'deo-mang-den', 'Cung đường đèo uốn lượn giữa rừng, có nhiều góc nhìn ra cao nguyên.', 'Quốc lộ 24, vùng Măng Đen', 0.00, NULL, NULL, 180, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1617, 182, 115, 'Hồ Tà Đùng', 'ho-ta-dung', 'Hồ chứa với nhiều đảo nhỏ, thường được gọi là vịnh Hạ Long của Tây Nguyên.', 'Khu vực Tà Đùng, Đắk Nông', 0.00, NULL, NULL, 120, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1618, 182, 115, 'Thác Liêng Nung', 'thac-lieng-nung', 'Thác nước đổ qua vách đá bazan tạo hình cột đặc trưng.', 'Gần Gia Nghĩa, Đắk Nông', 0.00, NULL, NULL, 150, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1619, 182, 119, 'Chùa Pháp Hoa Gia Nghĩa', 'chua-phap-hoa-gia-nghia', 'Ngôi chùa nằm trên đồi, là điểm sinh hoạt Phật giáo ở Gia Nghĩa.', 'Gia Nghĩa, Đắk Nông', 0.00, NULL, NULL, 75, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1620, 182, 115, 'Thác Đắk G’lun', 'thac-dak-g-lun', 'Thác nước rừng núi gắn với hoạt động du lịch sinh thái của vùng Đắk Nông.', 'Tuy Đức, Đắk Nông', 0.00, NULL, NULL, 75, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1621, 182, 116, 'Công viên địa chất Đắk Nông', 'cong-vien-dia-chat-dak-nong', 'Không gian di sản địa chất núi lửa và hang động dung nham của khu vực.', 'Khu vực Đắk Nông', 0.00, NULL, NULL, 75, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1622, 183, 112, 'Bãi Dài Cam Ranh', 'bai-dai-cam-ranh', 'Dải bờ biển cát mịn dọc bán đảo Cam Ranh với nhiều khu nghỉ dưỡng.', 'Bán đảo Cam Ranh, Khánh Hòa', 0.00, NULL, NULL, 180, 3.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1623, 183, 125, 'Đảo Bình Ba', 'dao-binh-ba', 'Đảo ven vịnh Cam Ranh nổi tiếng với cảnh biển và các bãi tắm nhỏ.', 'Vịnh Cam Ranh, Khánh Hòa', 0.00, NULL, NULL, 60, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1624, 183, 125, 'Đảo Bình Hưng', 'dao-binh-hung', 'Đảo nhỏ nước trong gần vịnh Cam Ranh, thường được khám phá bằng tàu.', 'Vùng biển Cam Ranh, Khánh Hòa', 0.00, NULL, NULL, 60, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1625, 183, 112, 'Bãi Nồm Bình Ba', 'bai-nom-binh-ba', 'Bãi cát nằm ở phía đông đảo Bình Ba, thích hợp ngắm bình minh.', 'Đảo Bình Ba, Cam Ranh', 0.00, NULL, NULL, 180, 5.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1626, 183, 112, 'Bãi Kinh Bình Hưng', 'bai-kinh-binh-hung', 'Bãi tắm và điểm đi tàu gần đảo Bình Hưng.', 'Khu vực Bình Hưng, Cam Ranh', 0.00, NULL, NULL, 60, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1627, 184, 114, 'Tháp Po Klong Garai', 'thap-po-klong-garai-phan-rang-thap-cham', 'Quần thể tháp Chăm gạch đỏ trên đồi Trầu, gắn với vua Po Klong Garai.', 'Phan Rang - Tháp Chàm', 0.00, NULL, NULL, 180, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1628, 184, 118, 'Làng gốm Bàu Trúc', 'lang-gom-bau-truc', 'Làng gốm Chăm nổi tiếng với kỹ thuật tạo hình bằng tay.', 'Khu vực Bàu Trúc, Ninh Thuận', 0.00, NULL, NULL, 90, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1629, 184, 118, 'Làng dệt Mỹ Nghiệp', 'lang-det-my-nghiep', 'Làng dệt thổ cẩm Chăm với hoa văn truyền thống.', 'Khu vực Mỹ Nghiệp, Ninh Thuận', 0.00, NULL, NULL, 180, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1630, 184, 106, 'Đồi cát Nam Cương', 'doi-cat-nam-cuong', 'Đồi cát rộng có bề mặt liên tục thay đổi theo gió.', 'Khu vực An Hải, Ninh Thuận', 0.00, NULL, NULL, 120, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1631, 184, 112, 'Biển Ninh Chữ', 'bien-ninh-chu', 'Bãi biển cong với hàng dương và không gian nghỉ dưỡng ven bờ.', 'Ninh Chữ, Ninh Thuận', 0.00, NULL, NULL, 120, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1632, 185, 117, 'Khu bảo tồn thiên nhiên Lung Ngọc Hoàng', 'khu-bao-ton-thien-nhien-lung-ngoc-hoang', 'Vùng đất ngập nước có hệ sinh thái tràm và chim nước phong phú.', 'Phụng Hiệp, Hậu Giang', 0.00, NULL, NULL, 75, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1633, 185, 108, 'Chợ nổi Ngã Bảy', 'cho-noi-nga-bay', 'Điểm giao thương trên kênh rạch gắn với văn hóa chợ nổi miền Tây.', 'Ngã Bảy, Hậu Giang', 0.00, NULL, NULL, 120, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1634, 185, 114, 'Khu di tích Chiến thắng Chương Thiện', 'khu-di-tich-chien-thang-chuong-thien', 'Khu tưởng niệm lịch sử gắn với các cuộc chiến đấu ở vùng Chương Thiện.', 'Vị Thanh, Hậu Giang', 0.00, NULL, NULL, 60, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1635, 185, 115, 'Kênh Xà No', 'kenh-xa-no', 'Tuyến kênh lịch sử qua vùng Hậu Giang, gắn với sinh hoạt và vận tải đường thủy.', 'Vị Thanh, Hậu Giang', 0.00, NULL, NULL, 90, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1636, 185, 126, 'Công viên Xà No', 'cong-vien-xa-no', 'Không gian dạo chơi và ngắm cảnh hai bên kênh Xà No.', 'Vị Thanh, Hậu Giang', 0.00, NULL, NULL, 150, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1637, 186, 112, 'Bãi Cây Mến', 'bai-cay-men', 'Bãi biển có hàng dừa và nước xanh trên đảo Hòn Lớn.', 'Hòn Lớn, Nam Du', 0.00, NULL, NULL, 150, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1638, 186, 125, 'Hòn Mấu', 'hon-mau', 'Đảo nhỏ có nhiều bãi tắm và làng chài ven biển.', 'Quần đảo Nam Du', 0.00, NULL, NULL, 120, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1639, 186, 125, 'Hòn Ngang Nam Du', 'hon-ngang-nam-du', 'Đảo dân cư đông, điểm cập tàu và sinh hoạt nghề cá.', 'Quần đảo Nam Du', 0.00, NULL, NULL, 75, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1640, 186, 125, 'Hòn Dầu Nam Du', 'hon-dau-nam-du', 'Đảo nhỏ có rừng và bãi biển phù hợp ngắm cảnh.', 'Quần đảo Nam Du', 0.00, NULL, NULL, 90, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1641, 186, 123, 'Hải đăng Nam Du', 'hai-dang-nam-du', 'Điểm cao quan sát quần đảo từ Hòn Lớn.', 'Hòn Lớn, Nam Du', 0.00, NULL, NULL, 75, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1642, 187, 112, 'Bãi Ông Cù Lao Chàm', 'bai-ong-cu-lao-cham', 'Bãi tắm có hàng dừa và dịch vụ du lịch trên đảo.', 'Đảo Hòn Lao, Cù Lao Chàm', 0.00, NULL, NULL, 180, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1643, 187, 112, 'Bãi Chồng Cù Lao Chàm', 'bai-chong-cu-lao-cham', 'Bãi cát có những tảng đá lớn và rừng cây ven biển.', 'Đảo Hòn Lao, Cù Lao Chàm', 0.00, NULL, NULL, 60, 5.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1644, 187, 122, 'Bãi Hương Cù Lao Chàm', 'bai-huong-cu-lao-cham', 'Làng chài truyền thống ở phía nam đảo Hòn Lao.', 'Đảo Hòn Lao, Cù Lao Chàm', 0.00, NULL, NULL, 90, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1645, 187, 119, 'Chùa Hải Tạng', 'chua-hai-tang', 'Ngôi chùa cổ trên đảo, gắn với đời sống tín ngưỡng cư dân biển.', 'Đảo Hòn Lao, Cù Lao Chàm', 0.00, NULL, NULL, 180, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1646, 187, 123, 'Eo Gió Cù Lao Chàm', 'eo-gio-cu-lao-cham', 'Điểm ngắm cảnh bờ đá và sóng biển phía đông đảo.', 'Đảo Hòn Lao, Cù Lao Chàm', 0.00, NULL, NULL, 75, 3.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1647, 188, 122, 'Bản Đôn Pù Luông', 'ban-don-pu-luong', 'Bản làng trên sườn núi với ruộng bậc thang và nhà sàn.', 'Bá Thước, Thanh Hóa', 0.00, NULL, NULL, 120, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1648, 188, 115, 'Bản Hiêu và thác Hiêu', 'ban-hieu-va-thac-hieu', 'Dòng thác nhiều tầng chảy qua bản người Thái giữa núi rừng.', 'Bá Thước, Thanh Hóa', 0.00, NULL, NULL, 90, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1649, 188, 121, 'Guồng nước Chiềng Lau', 'guong-nuoc-chieng-lau', 'Hệ thống bánh xe nước bằng tre phục vụ dẫn nước vào đồng ruộng.', 'Chiềng Lau, Pù Luông', 0.00, NULL, NULL, 75, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1650, 188, 116, 'Kho Mường - Hang Dơi', 'kho-muong-hang-doi', 'Thung lũng có bản nhà sàn và hang động đá vôi tự nhiên.', 'Bá Thước, Thanh Hóa', 0.00, NULL, NULL, 75, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1651, 188, 113, 'Đỉnh Pù Luông', 'dinh-pu-luong', 'Cung leo núi có cảnh quan rừng và thung lũng vùng biên sơn.', 'Khu bảo tồn thiên nhiên Pù Luông', 0.00, NULL, NULL, 150, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1652, 189, 117, 'Vườn quốc gia Ba Vì', 'vuon-quoc-gia-ba-vi', 'Rừng núi với thảm thực vật và các tuyến đường lên đỉnh núi.', 'Ba Vì, Hà Nội', 0.00, NULL, NULL, 180, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1653, 189, 114, 'Nhà thờ đổ Ba Vì', 'nha-tho-do-ba-vi', 'Di tích nhà thờ cũ phủ rêu nằm giữa rừng ở vùng núi Ba Vì.', 'Vườn quốc gia Ba Vì', 0.00, NULL, NULL, 150, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1654, 189, 119, 'Đền Thượng Ba Vì', 'den-thuong-ba-vi', 'Đền thờ Tản Viên Sơn Thánh trên đỉnh núi.', 'Đỉnh núi Ba Vì, Hà Nội', 0.00, NULL, NULL, 120, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1655, 189, 109, 'Khu du lịch Ao Vua', 'khu-du-lich-ao-vua', 'Khu vui chơi và cảnh quan suối, thác ở chân núi Ba Vì.', 'Ba Vì, Hà Nội', 0.00, NULL, NULL, 60, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1656, 189, 114, 'Làng cổ Đường Lâm', 'lang-co-duong-lam', 'Làng cổ với nhà đá ong, cổng làng và kiến trúc truyền thống vùng Sơn Tây.', 'Sơn Tây, Hà Nội', 0.00, NULL, NULL, 60, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1657, 190, 113, 'Sống lưng khủng long Háng Đồng', 'song-lung-khung-long-hang-dong', 'Dải sống núi hẹp với hai sườn dốc, nổi tiếng trong hành trình săn mây.', 'Háng Đồng, Tà Xùa', 0.00, NULL, NULL, 180, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1658, 190, 123, 'Cây cô đơn Tà Xùa', 'cay-co-don-ta-xua', 'Cây đơn lẻ trên đồi, có tầm nhìn ra thung lũng và biển mây.', 'Tà Xùa, Sơn La', 0.00, NULL, NULL, 75, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1659, 190, 123, 'Mỏm Cá Heo Tà Xùa', 'mom-ca-heo-ta-xua', 'Cụm đá tự nhiên có hình dáng gợi liên tưởng đến đầu cá heo.', 'Tà Xùa, Sơn La', 0.00, NULL, NULL, 75, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1660, 190, 106, 'Thảo nguyên Tà Xùa', 'thao-nguyen-ta-xua', 'Vùng đồi cỏ ở cao độ lớn, phù hợp ngắm núi và mây.', 'Tà Xùa, Sơn La', 0.00, NULL, NULL, 180, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1661, 190, 117, 'Rừng nguyên sinh Tà Xùa', 'rung-nguyen-sinh-ta-xua', 'Rừng núi cao với rêu, cây cổ thụ và đường trekking.', 'Vùng Tà Xùa, Sơn La', 0.00, NULL, NULL, 60, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1662, 191, 109, 'Khu du lịch Bửu Long', 'khu-du-lich-buu-long', 'Khu thắng cảnh hồ nước và núi đá gần trung tâm Biên Hòa.', 'Biên Hòa, Đồng Nai', 0.00, NULL, NULL, 150, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1663, 191, 114, 'Văn miếu Trấn Biên', 'van-mieu-tran-bien', 'Công trình tôn vinh truyền thống học hành tại vùng đất Đồng Nai.', 'Biên Hòa, Đồng Nai', 0.00, NULL, NULL, 75, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1664, 191, 119, 'Chùa Ông Biên Hòa', 'chua-ong-bien-hoa', 'Ngôi miếu của cộng đồng người Hoa với kiến trúc trang trí đặc trưng.', 'Biên Hòa, Đồng Nai', 0.00, NULL, NULL, 90, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1665, 191, 115, 'Thác Giang Điền', 'thac-giang-dien', 'Thác nước giữa khu sinh thái, điểm dã ngoại phổ biến ở Đồng Nai.', 'Trảng Bom, Đồng Nai', 0.00, NULL, NULL, 120, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1666, 191, 121, 'Làng bưởi Tân Triều', 'lang-buoi-tan-trieu', 'Vùng vườn bưởi lâu đời ven sông Đồng Nai.', 'Vĩnh Cửu, Đồng Nai', 0.00, NULL, NULL, 120, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1667, 192, 117, 'Vườn quốc gia Bù Gia Mập', 'vuon-quoc-gia-bu-gia-map', 'Khu rừng nhiệt đới có hệ sinh thái phong phú vùng biên giới.', 'Bù Gia Mập, Bình Phước', 0.00, NULL, NULL, 120, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1668, 192, 113, 'Núi Bà Rá', 'nui-ba-ra', 'Ngọn núi nổi bật giữa đồng bằng, có tuyến tham quan lên cao.', 'Phước Long, Bình Phước', 0.00, NULL, NULL, 90, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1669, 192, 115, 'Hồ thủy điện Thác Mơ', 'ho-thuy-dien-thac-mo', 'Hồ chứa rộng giữa đồi núi và rừng ở vùng Phước Long.', 'Phước Long, Bình Phước', 0.00, NULL, NULL, 60, 4.50, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1670, 192, 122, 'Sóc Bom Bo', 'soc-bom-bo', 'Địa danh gắn với văn hóa người S’tiêng và lịch sử kháng chiến.', 'Bù Đăng, Bình Phước', 0.00, NULL, NULL, 60, 3.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1671, 192, 106, 'Trảng cỏ Bù Lạch', 'trang-co-bu-lach', 'Quần thể trảng cỏ tự nhiên xen hồ nước và rừng thưa.', 'Bù Đăng, Bình Phước', 0.00, NULL, NULL, 180, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1672, 193, 119, 'Chùa Bà Thiên Hậu Thủ Dầu Một', 'chua-ba-thien-hau-thu-dau-mot', 'Miếu thờ Thiên Hậu nổi tiếng với lễ hội rằm tháng Giêng.', 'Thủ Dầu Một, Bình Dương', 0.00, NULL, NULL, 75, 5.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1673, 193, 114, 'Nhà thờ Phú Cường', 'nha-tho-phu-cuong', 'Nhà thờ Công giáo với kiến trúc tháp nhọn nổi bật tại trung tâm đô thị.', 'Thủ Dầu Một, Bình Dương', 0.00, NULL, NULL, 60, 5.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1674, 193, 118, 'Làng sơn mài Tương Bình Hiệp', 'lang-son-mai-tuong-binh-hiep', 'Làng nghề sơn mài thủ công có lịch sử lâu đời.', 'Tương Bình Hiệp, Thủ Dầu Một', 0.00, NULL, NULL, 120, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1675, 193, 119, 'Chùa Hội Khánh', 'chua-hoi-khanh', 'Ngôi chùa lâu đời nổi tiếng với tượng Phật nhập Niết-bàn.', 'Thủ Dầu Một, Bình Dương', 0.00, NULL, NULL, 120, 4.00, 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1676, 193, 109, 'Khu du lịch Đại Nam', 'khu-du-lich-dai-nam', 'Khu giải trí quy mô lớn kết hợp cảnh quan, công trình và trò chơi.', 'Thủ Dầu Một, Bình Dương', 0.00, NULL, NULL, 60, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 0),
(1677, 114, 111, 'Sofitel Legend Metropole Hanoi', 'sofitel-legend-metropole-hanoi', 'Khách sạn lâu đời nằm gần hồ Hoàn Kiếm và Nhà hát Lớn.', 'Khu vực Hà Nội, Việt Nam', 0.00, NULL, NULL, NULL, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1678, 114, 111, 'La Siesta Premium Hang Be', 'la-siesta-premium-hang-be', 'Khách sạn tại khu phố cổ, thuận tiện khám phá trung tâm Hà Nội.', 'Khu vực Hà Nội, Việt Nam', 0.00, NULL, NULL, NULL, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1679, 115, 111, 'InterContinental Danang Sun Peninsula Resort', 'intercontinental-danang-sun-peninsula-resort', 'Khu nghỉ dưỡng trên bán đảo Sơn Trà, hướng ra biển.', 'Khu vực Đà Nẵng, Việt Nam', 0.00, NULL, NULL, NULL, 5.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1680, 115, 111, 'Furama Resort Danang', 'furama-resort-danang', 'Khu nghỉ dưỡng ven biển Bắc Mỹ An tại Đà Nẵng.', 'Khu vực Đà Nẵng, Việt Nam', 0.00, NULL, NULL, NULL, 5.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1681, 116, 111, 'Hotel Royal Hoi An - MGallery', 'hotel-royal-hoi-an-mgallery', 'Khách sạn ven sông Thu Bồn gần khu phố cổ Hội An.', 'Khu vực Hội An, Việt Nam', 0.00, NULL, NULL, NULL, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1682, 117, 111, 'New World Saigon Hotel', 'new-world-saigon-hotel', 'Khách sạn trung tâm gần khu vực chợ Bến Thành.', 'Khu vực Thành phố Hồ Chí Minh, Việt Nam', 0.00, NULL, NULL, NULL, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1683, 117, 111, 'Lotte Hotel Saigon', 'lotte-hotel-saigon', 'Khách sạn ven sông Sài Gòn tại khu trung tâm.', 'Khu vực Thành phố Hồ Chí Minh, Việt Nam', 0.00, NULL, NULL, NULL, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1684, 118, 111, 'Ana Mandara Villas Dalat Resort & Spa', 'ana-mandara-villas-dalat-resort-spa', 'Khu biệt thự nghỉ dưỡng giữa đồi thông Đà Lạt.', 'Khu vực Đà Lạt, Việt Nam', 0.00, NULL, NULL, NULL, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1685, 124, 111, 'Hotel de la Coupole - MGallery', 'hotel-de-la-coupole-mgallery', 'Khách sạn trung tâm Sa Pa có phong cách kiến trúc đặc trưng.', 'Khu vực Sa Pa, Việt Nam', 0.00, NULL, NULL, NULL, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1686, 124, 111, 'Topas Ecolodge', 'topas-ecolodge', 'Khu lưu trú trên sườn đồi, nhìn ra ruộng bậc thang vùng Sa Pa.', 'Khu vực Sa Pa, Việt Nam', 0.00, NULL, NULL, NULL, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1687, 119, 111, 'Wyndham Legend Halong Hotel', 'wyndham-legend-halong-hotel', 'Khách sạn tại Bãi Cháy hướng ra vịnh Hạ Long.', 'Khu vực Hạ Long, Việt Nam', 0.00, NULL, NULL, NULL, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1688, 120, 111, 'Salinda Resort Phu Quoc Island', 'salinda-resort-phu-quoc-island', 'Khu nghỉ dưỡng ven biển phía tây đảo Phú Quốc.', 'Khu vực Phú Quốc, Việt Nam', 0.00, NULL, NULL, NULL, 5.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1689, 122, 111, 'Azerai La Residence Hue', 'azerai-la-residence-hue', 'Khách sạn ven sông Hương trong công trình kiến trúc phong cách Art Deco.', 'Khu vực Huế, Việt Nam', 0.00, NULL, NULL, NULL, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1690, 122, 111, 'Pilgrimage Village Boutique Resort & Spa', 'pilgrimage-village-boutique-resort-spa', 'Khu nghỉ dưỡng sân vườn tại vùng ngoại ô Huế.', 'Khu vực Huế, Việt Nam', 0.00, NULL, NULL, NULL, 4.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1691, 123, 111, 'Emeralda Resort Ninh Binh', 'emeralda-resort-ninh-binh', 'Khu nghỉ dưỡng giữa vùng sinh thái Vân Long.', 'Khu vực Ninh Bình, Việt Nam', 0.00, NULL, NULL, NULL, 5.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1692, 132, 111, 'Mia Resort Nha Trang', 'mia-resort-nha-trang', 'Khu nghỉ dưỡng trên sườn đồi ven biển ở khu vực Nha Trang.', 'Khu vực Nha Trang, Việt Nam', 0.00, NULL, NULL, NULL, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1693, 183, 111, 'The Anam Cam Ranh', 'the-anam-cam-ranh', 'Khu nghỉ dưỡng gần bãi biển Bãi Dài Cam Ranh.', 'Khu vực Cam Ranh, Việt Nam', 0.00, NULL, NULL, NULL, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1694, 136, 111, 'Meliá Ho Tram Beach Resort', 'melia-ho-tram-beach-resort', 'Khu nghỉ dưỡng ven biển Hồ Tràm, thuộc vùng du lịch Bà Rịa - Vũng Tàu.', 'Khu vực Vũng Tàu, Việt Nam', 0.00, NULL, NULL, NULL, 3.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1695, 188, 111, 'Pù Luông Retreat', 'pu-luong-retreat', 'Khu lưu trú giữa ruộng bậc thang và núi rừng Pù Luông.', 'Khu vực Pù Luông, Việt Nam', 0.00, NULL, NULL, NULL, 4.50, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1),
(1696, 124, 111, 'Silk Path Grand Sapa Resort & Spa', 'silk-path-grand-sapa-resort-spa', 'Khách sạn nghỉ dưỡng trên sườn đồi gần trung tâm Sa Pa.', 'Khu vực Sa Pa, Việt Nam', 0.00, NULL, NULL, NULL, 5.00, 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00', 1);

-- Table structure for table `destination_images`
--

DROP TABLE IF EXISTS `destination_images`;
CREATE TABLE IF NOT EXISTS `destination_images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `destination_id` bigint UNSIGNED NOT NULL,
  `image_path` varchar(2048) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `primary_marker` tinyint GENERATED ALWAYS AS ((case when (`is_primary` = 1) then 1 else NULL end)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_images_one_primary` (`destination_id`,`primary_marker`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `destination_images`
--

INSERT INTO `destination_images` (`id`, `destination_id`, `image_path`, `is_primary`, `created_at`, `updated_at`) VALUES
(178, 1281, 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=600&q=80', 1, '2026-10-19 02:24:45', '2026-10-19 02:24:45'),
(179, 1281, 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80', 0, '2026-10-19 02:24:45', '2026-10-19 02:24:45'),
(180, 1281, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=600&q=80', 0, '2026-10-19 02:24:45', '2026-10-19 02:24:45'),
(181, 1281, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80', 0, '2026-10-19 02:24:45', '2026-10-19 02:24:45'),
(182, 1281, 'https://images.unsplash.com/photo-1569154941061-e231b4725ef1?auto=format&fit=crop&w=600&q=80', 0, '2026-10-19 02:24:45', '2026-10-19 02:24:45'),
(183, 1281, 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=600&q=80', 0, '2026-10-19 02:24:45', '2026-10-19 02:24:45'),
(184, 1282, 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=400&q=80', 1, '2026-10-19 02:24:45', '2026-10-19 02:24:45'),
(185, 1283, 'https://images.unsplash.com/photo-1589308078059-be1415eab4c3?auto=format&fit=crop&w=400&q=80', 1, '2026-10-19 02:24:45', '2026-10-19 02:24:45');

-- --------------------------------------------------------

--

-- Dữ liệu bổ sung cho destination_images
INSERT INTO `destination_images` (`id`, `destination_id`, `image_path`, `is_primary`, `created_at`, `updated_at`) VALUES
(187, 1577, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(188, 1578, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(189, 1579, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(190, 1580, 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(191, 1581, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(192, 1582, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(193, 1583, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(194, 1584, 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(195, 1585, 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(196, 1586, 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(197, 1587, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(198, 1588, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(199, 1589, 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(200, 1590, 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(201, 1591, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(202, 1592, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(203, 1593, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(204, 1594, 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(205, 1595, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(206, 1596, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(207, 1597, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(208, 1598, 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(209, 1599, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(210, 1600, 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(211, 1601, 'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(212, 1602, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(213, 1603, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(214, 1604, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(215, 1605, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(216, 1606, 'https://images.unsplash.com/photo-1497436072909-60f362e1d4b1?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(217, 1607, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(218, 1608, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(219, 1609, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(220, 1610, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(221, 1611, 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(222, 1612, 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(223, 1613, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(224, 1614, 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(225, 1615, 'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(226, 1616, 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(227, 1617, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(228, 1618, 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(229, 1619, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(230, 1620, 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(231, 1621, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(232, 1622, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(233, 1623, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(234, 1624, 'https://images.unsplash.com/photo-1497436072909-60f362e1d4b1?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(235, 1625, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(236, 1626, 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(237, 1627, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(238, 1628, 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(239, 1629, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(240, 1630, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(241, 1631, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(242, 1632, 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(243, 1633, 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(244, 1634, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(245, 1635, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(246, 1636, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(247, 1637, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(248, 1638, 'https://images.unsplash.com/photo-1497436072909-60f362e1d4b1?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(249, 1639, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(250, 1640, 'https://images.unsplash.com/photo-1497436072909-60f362e1d4b1?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(251, 1641, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(252, 1642, 'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(253, 1643, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(254, 1644, 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(255, 1645, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(256, 1646, 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(257, 1647, 'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(258, 1648, 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(259, 1649, 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(260, 1650, 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(261, 1651, 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(262, 1652, 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(263, 1653, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(264, 1654, 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(265, 1655, 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(266, 1656, 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(267, 1657, 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(268, 1658, 'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(269, 1659, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(270, 1660, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(271, 1661, 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(272, 1662, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(273, 1663, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(274, 1664, 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(275, 1665, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(276, 1666, 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(277, 1667, 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(278, 1668, 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(279, 1669, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(280, 1670, 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(281, 1671, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(282, 1672, 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(283, 1673, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(284, 1674, 'https://images.unsplash.com/photo-1452860606245-08befc0ff44b?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(285, 1675, 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(286, 1676, 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(287, 1677, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(288, 1677, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(289, 1678, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(290, 1678, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(291, 1679, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(292, 1679, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(293, 1680, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(294, 1680, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(295, 1681, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(296, 1681, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(297, 1682, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(298, 1682, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(299, 1683, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(300, 1683, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(301, 1684, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(302, 1684, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(303, 1685, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(304, 1685, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(305, 1686, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(306, 1686, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(307, 1687, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(308, 1687, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(309, 1688, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(310, 1688, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(311, 1689, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(312, 1689, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(313, 1690, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(314, 1690, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(315, 1691, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(316, 1691, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(317, 1692, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(318, 1692, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(319, 1693, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(320, 1693, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(321, 1694, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(322, 1694, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(323, 1695, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(324, 1695, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(325, 1696, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(326, 1696, 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 0, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(327, 713, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(328, 714, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(329, 715, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(330, 716, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(331, 717, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(332, 718, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(333, 719, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(334, 720, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(335, 721, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(336, 722, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(337, 1285, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(338, 1286, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(339, 1287, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(340, 1288, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(341, 1289, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(342, 1290, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(343, 1291, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(344, 1292, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(345, 1293, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(346, 1294, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(347, 1295, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(348, 1296, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(349, 1297, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(350, 1298, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(351, 1299, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(352, 1300, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(353, 1301, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(354, 1302, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(355, 1303, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(356, 1304, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(357, 1305, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(358, 1306, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(359, 1307, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(360, 1308, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(361, 1309, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(362, 1310, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(363, 1311, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(364, 1312, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(365, 1313, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(366, 1314, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(367, 1315, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(368, 1316, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(369, 1317, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(370, 1318, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(371, 1319, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(372, 1320, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(373, 1321, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(374, 1322, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(375, 1323, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(376, 1324, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(377, 1325, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(378, 1326, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(379, 1327, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(380, 1328, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(381, 1329, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(382, 1330, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(383, 1331, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(384, 1332, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(385, 1333, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(386, 1334, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(387, 1335, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(388, 1336, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(389, 1337, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(390, 1338, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(391, 1339, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(392, 1340, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(393, 1341, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(394, 1342, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(395, 1343, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(396, 1344, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(397, 1345, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(398, 1346, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(399, 1347, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(400, 1348, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(401, 1349, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(402, 1350, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(403, 1351, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(404, 1352, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(405, 1353, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(406, 1354, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(407, 1355, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(408, 1356, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(409, 1357, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(410, 1358, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(411, 1359, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(412, 1360, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(413, 1361, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(414, 1362, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(415, 1363, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(416, 1364, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(417, 1365, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(418, 1366, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(419, 1367, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(420, 1368, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(421, 1369, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(422, 1370, 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(423, 1371, 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=1200&q=80', 1, '2026-10-27 09:00:00', '2026-10-27 09:00:00');

-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `trip_id` bigint UNSIGNED NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amount` decimal(12,2) NOT NULL,
  `expense_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_expenses_trip_date` (`trip_id`,`expense_date`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `trip_id`, `category`, `description`, `amount`, `expense_date`, `created_at`, `updated_at`) VALUES
(7, 43, 'transport', 'Round-trip flight', 2400000.00, '2026-11-18', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(8, 43, 'accommodation', 'Hotel for two nights', 1800000.00, '2026-11-18', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(9, 43, 'ticket', 'Ba Na Hills ticket', 900000.00, '2026-11-18', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(10, 43, 'food', 'Food budget', 1200000.00, '2026-11-18', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(11, 46, 'other', 'Chi phí tour và ăn uống Fansipan', 7850000.00, '2026-08-16', '2026-10-19 02:24:46', '2026-10-19 02:24:46');

-- --------------------------------------------------------

--

-- Dữ liệu bổ sung cho expenses
INSERT INTO `expenses` (`id`, `trip_id`, `category`, `description`, `amount`, `expense_date`, `created_at`, `updated_at`) VALUES
(12, 43, 'transport', 'Xe công nghệ từ sân bay về khách sạn', 180000.00, '2026-11-18', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(13, 43, 'food', 'Bữa sáng với mì Quảng', 90000.00, '2026-11-19', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(14, 43, 'food', 'Ăn nhẹ và nước uống khi tham quan', 130000.00, '2026-11-19', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(15, 43, 'transport', 'Di chuyển từ Đà Nẵng tới Hội An', 270000.00, '2026-11-19', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(16, 43, 'food', 'Cao lầu và đặc sản Hội An', 170000.00, '2026-11-19', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(17, 43, 'shopping', 'Quà lưu niệm địa phương', 200000.00, '2026-11-20', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(18, 43, 'transport', 'Xe đưa đón sân bay ngày về', 190000.00, '2026-11-20', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(19, 44, 'transport', 'Xe di chuyển sân bay', 300000.00, '2026-10-10', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(20, 44, 'accommodation', 'Đặt cọc nơi lưu trú', 1200000.00, '2026-10-10', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(21, 44, 'food', 'Bún chả cá Đà Nẵng', 130000.00, '2026-10-10', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(22, 44, 'ticket', 'Vé tham quan Ngũ Hành Sơn (ước tính)', 90000.00, '2026-10-11', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(23, 44, 'food', 'Ăn tối hải sản', 480000.00, '2026-10-11', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(24, 44, 'transport', 'Thuê xe máy 2 ngày', 300000.00, '2026-10-12', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(25, 44, 'food', 'Bánh xèo và nem lụi', 150000.00, '2026-10-12', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(26, 44, 'shopping', 'Mua đèn lồng làm quà', 220000.00, '2026-10-13', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(27, 44, 'ticket', 'Tham quan di tích và làng nghề', 160000.00, '2026-10-13', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(28, 44, 'food', 'Cao lầu Hội An', 100000.00, '2026-10-14', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(29, 44, 'transport', 'Taxi về khách sạn', 220000.00, '2026-10-14', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(30, 44, 'other', 'Dự phòng chi phí phát sinh', 500000.00, '2026-10-15', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(31, 45, 'transport', 'Di chuyển đến bến tàu', 280000.00, '2026-09-26', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(32, 45, 'food', 'Ăn trưa tại Hạ Long', 250000.00, '2026-09-26', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(33, 45, 'ticket', 'Vé tham quan khu vực vịnh (ước tính)', 300000.00, '2026-09-27', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(34, 45, 'food', 'Đồ uống trên hành trình', 150000.00, '2026-09-27', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(35, 45, 'transport', 'Xe đưa đón ngày thứ ba', 240000.00, '2026-09-28', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(36, 45, 'food', 'Bữa tối địa phương', 370000.00, '2026-09-28', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(37, 45, 'shopping', 'Đặc sản mua làm quà', 280000.00, '2026-09-29', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(38, 45, 'food', 'Bữa sáng cuối chuyến', 150000.00, '2026-09-30', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(39, 45, 'other', 'Chi phí phát sinh nhỏ', 200000.00, '2026-09-30', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(40, 46, 'food', 'Bữa sáng Sa Pa', 130000.00, '2026-08-15', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(41, 46, 'transport', 'Xe đưa đón ngắn trong thị trấn', 160000.00, '2026-08-15', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(42, 46, 'food', 'Ăn tối đặc sản địa phương', 280000.00, '2026-08-16', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(43, 46, 'food', 'Nước uống và đồ ăn khi trekking', 150000.00, '2026-08-17', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(44, 46, 'shopping', 'Quà thổ cẩm địa phương', 240000.00, '2026-08-18', '2026-10-27 09:00:00', '2026-10-27 09:00:00');

-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `destination_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_favorites_user_destination` (`user_id`,`destination_id`),
  KEY `idx_favorites_destination` (`destination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `destination_id`, `created_at`) VALUES
(8, 275, 717, '2026-10-19 02:24:40'),
(9, 275, 715, '2026-10-19 02:24:40'),
(10, 275, 721, '2026-10-19 02:24:40'),
(11, 275, 713, '2026-10-19 02:24:40');

-- --------------------------------------------------------

--
-- Table structure for table `itinerary_items`
--

DROP TABLE IF EXISTS `itinerary_items`;
CREATE TABLE IF NOT EXISTS `itinerary_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `trip_id` bigint UNSIGNED NOT NULL,
  `destination_id` bigint UNSIGNED DEFAULT NULL,
  `day_number` int NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `booking_id` bigint UNSIGNED DEFAULT NULL,
  `custom_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_itinerary_trip_day_order` (`trip_id`,`day_number`,`sort_order`),
  KEY `idx_itinerary_destination` (`destination_id`),
  KEY `idx_itinerary_booking` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `itinerary_items`
--

INSERT INTO `itinerary_items` (`id`, `trip_id`, `destination_id`, `day_number`, `start_time`, `end_time`, `note`, `sort_order`, `created_at`, `updated_at`, `booking_id`, `custom_title`) VALUES
(7, 43, 715, 1, '06:00:00', '08:00:00', 'Watch the sunrise.', 1, '2026-10-19 02:24:40', '2026-10-19 02:24:40', NULL, NULL),
(8, 43, 716, 1, '09:30:00', '17:00:00', 'Buy cable car tickets in advance.', 2, '2026-10-19 02:24:40', '2026-10-19 02:24:40', NULL, NULL),
(9, 43, 718, 2, '08:00:00', '10:00:00', 'Try cao lau for breakfast.', 1, '2026-10-19 02:24:40', '2026-10-19 02:24:40', NULL, NULL),
(10, 43, 717, 2, '15:00:00', '21:00:00', 'Stay until the lanterns are lit.', 2, '2026-10-19 02:24:40', '2026-10-19 02:24:40', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_16_000001_add_travel_planner_fields_to_users_table', 1),
(5, '2026_09_16_000010_create_cities_table', 1),
(6, '2026_09_16_000011_create_categories_table', 1),
(7, '2026_09_16_000020_create_destinations_table', 1),
(8, '2026_09_16_000021_create_destination_images_table', 1),
(9, '2026_09_16_000030_create_trips_table', 1),
(10, '2026_09_16_000040_create_itinerary_items_table', 1),
(11, '2026_09_16_000050_create_favorites_table', 1),
(12, '2026_09_16_000060_create_expenses_table', 1),
(13, '2026_09_16_000070_create_reviews_table', 1),
(14, '2026_10_01_000000_align_travel_planner_erd', 1),
(15, '2026_10_07_000001_add_profile_details_to_users_table', 1),
(16, '2026_10_09_000001_add_admin_fields_to_cities_table', 1),
(17, '2026_10_09_000002_add_admin_fields_to_categories_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payments_transaction` (`transaction_id`),
  KEY `idx_payments_booking_status` (`booking_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--

-- Dữ liệu bổ sung cho payments
INSERT INTO `payments` (`id`, `booking_id`, `amount`, `payment_method`, `transaction_id`, `status`, `payment_date`, `created_at`, `updated_at`) VALUES
(1, 1, 3600000.00, 'vnpay', NULL, 'pending', NULL, '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(2, 1, 3600000.00, 'vnpay', 'DEMO-TXN-2026-00002', 'failed', '2026-10-19 08:00:00', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(3, 2, 1000000.00, 'bank_transfer', 'DEMO-TXN-2026-00003', 'success', '2026-10-18 09:00:00', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(4, 2, 2000000.00, 'momo', 'DEMO-TXN-2026-00004', 'success', '2026-10-18 10:00:00', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(5, 2, 3000000.00, 'credit_card', 'DEMO-TXN-2026-00005', 'failed', '2026-10-17 10:00:00', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(6, 3, 1800000.00, 'vnpay', 'DEMO-TXN-2026-00006', 'success', '2026-08-10 13:45:00', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(7, 3, 1800000.00, 'bank_transfer', 'DEMO-TXN-2026-00007', 'failed', '2026-08-09 18:30:00', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(8, 4, 5600000.00, 'credit_card', 'DEMO-TXN-2026-00008', 'refunded', '2026-10-01 15:00:00', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(9, 4, 5600000.00, 'momo', 'DEMO-TXN-2026-00009', 'failed', '2026-10-01 13:00:00', '2026-10-27 09:00:00', '2026-10-27 09:00:00');

-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `destination_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reviews_user` (`user_id`),
  KEY `idx_reviews_destination_status` (`destination_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `destination_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(15, 275, 717, 5, 'Beautiful old streets and a wonderful atmosphere after sunset.', 'approved', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(16, 276, 715, 5, 'Clean beach and a great place to see the sunrise.', 'approved', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(17, 277, 716, 4, 'The cable car views were excellent, but it was busy.', 'approved', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(18, 278, 714, 5, 'A peaceful and meaningful historical site.', 'pending', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(19, 279, 720, 4, 'Many food choices and souvenirs in one place.', 'approved', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(20, 280, 716, 5, 'Cầu Vàng sáng sớm siêu đẹp và ít đông đúc, không khí mát mẻ dễ chịu vô cùng.', 'pending', '2026-09-28 02:15:00', '2026-09-28 02:15:00'),
(21, 284, 1283, 4, 'Phòng ốc rộng rãi, view biển rất thoáng mát, dịch vụ buffet sáng ngon miệng.', 'pending', '2026-09-27 07:30:00', '2026-09-27 07:30:00'),
(22, 281, 1282, 5, 'Trải nghiệm ngắm hoàng hôn vịnh Hạ Long trên du thuyền rất tuyệt vời, phục vụ chu đáo.', 'approved', '2026-08-15 10:45:00', '2026-08-15 10:45:00'),
(23, 285, 717, 1, 'Nội dung quảng cáo link ngoài không hợp lệ vi phạm quy chuẩn cộng đồng.', 'hidden', '2026-08-10 01:20:00', '2026-08-10 01:20:00');

-- --------------------------------------------------------

--

-- Dữ liệu bổ sung cho reviews
INSERT INTO `reviews` (`id`, `user_id`, `destination_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(24, 275, 1577, 5, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-06-03 00:00:00', '2026-06-03 00:00:00'),
(25, 276, 1578, 4, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-06-06 01:00:00', '2026-06-06 01:00:00'),
(26, 278, 1578, 4, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-06-17 02:00:00', '2026-06-17 02:00:00'),
(27, 277, 1579, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-06-09 02:00:00', '2026-06-09 02:00:00'),
(28, 279, 1579, 3, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-06-20 03:00:00', '2026-06-20 03:00:00'),
(29, 278, 1580, 4, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-06-12 03:00:00', '2026-06-12 03:00:00'),
(30, 279, 1581, 4, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-06-15 04:00:00', '2026-06-15 04:00:00'),
(31, 281, 1581, 5, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-06-26 05:00:00', '2026-06-26 05:00:00'),
(32, 280, 1582, 3, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-06-18 05:00:00', '2026-06-18 05:00:00'),
(33, 284, 1582, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-06-29 06:00:00', '2026-06-29 06:00:00'),
(34, 281, 1583, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-06-21 06:00:00', '2026-06-21 06:00:00'),
(35, 284, 1584, 5, 'Cảnh biển đẹp, những ngày cuối tuần thường có khá đông khách.', 'approved', '2026-06-24 07:00:00', '2026-06-24 07:00:00'),
(36, 276, 1584, 5, 'Nước biển và cảnh quan rất đẹp khi trời quang.', 'approved', '2026-07-05 08:00:00', '2026-07-05 08:00:00'),
(37, 275, 1585, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-06-27 08:00:00', '2026-06-27 08:00:00'),
(38, 277, 1585, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-07-08 00:00:00', '2026-07-08 00:00:00'),
(39, 276, 1586, 4, 'Trải nghiệm thú vị, nên hỏi trước thông tin giờ hoạt động.', 'approved', '2026-06-30 00:00:00', '2026-06-30 00:00:00'),
(40, 277, 1587, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-07-03 01:00:00', '2026-07-03 01:00:00'),
(41, 279, 1587, 3, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-07-14 02:00:00', '2026-07-14 02:00:00'),
(42, 278, 1588, 4, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-07-06 02:00:00', '2026-07-06 02:00:00'),
(43, 280, 1588, 5, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-07-17 03:00:00', '2026-07-17 03:00:00'),
(44, 279, 1589, 4, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-07-09 03:00:00', '2026-07-09 03:00:00'),
(45, 280, 1590, 3, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-07-12 04:00:00', '2026-07-12 04:00:00'),
(46, 284, 1590, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-07-23 05:00:00', '2026-07-23 05:00:00'),
(47, 281, 1591, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-07-15 05:00:00', '2026-07-15 05:00:00'),
(48, 275, 1591, 4, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-07-26 06:00:00', '2026-07-26 06:00:00'),
(49, 284, 1592, 5, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-07-18 06:00:00', '2026-07-18 06:00:00'),
(50, 275, 1593, 5, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-07-21 07:00:00', '2026-07-21 07:00:00'),
(51, 277, 1593, 4, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-08-01 08:00:00', '2026-08-01 08:00:00'),
(52, 276, 1594, 4, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-07-24 08:00:00', '2026-07-24 08:00:00'),
(53, 278, 1594, 4, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-08-04 00:00:00', '2026-08-04 00:00:00'),
(54, 277, 1595, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-07-27 00:00:00', '2026-07-27 00:00:00'),
(55, 278, 1596, 4, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-07-30 01:00:00', '2026-07-30 01:00:00'),
(56, 280, 1596, 5, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'pending', '2026-08-10 02:00:00', '2026-08-10 02:00:00'),
(57, 279, 1597, 4, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-08-02 02:00:00', '2026-08-02 02:00:00'),
(58, 281, 1597, 5, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-08-13 03:00:00', '2026-08-13 03:00:00'),
(59, 280, 1598, 3, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-08-05 03:00:00', '2026-08-05 03:00:00'),
(60, 281, 1599, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-08-08 04:00:00', '2026-08-08 04:00:00'),
(61, 275, 1599, 4, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-08-19 05:00:00', '2026-08-19 05:00:00'),
(62, 284, 1600, 5, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-08-11 05:00:00', '2026-08-11 05:00:00'),
(63, 276, 1600, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-08-22 06:00:00', '2026-08-22 06:00:00'),
(64, 275, 1601, 5, 'Điểm tham quan có nét đặc trưng riêng và dễ kết hợp vào lịch trình.', 'approved', '2026-08-14 06:00:00', '2026-08-14 06:00:00'),
(65, 276, 1602, 4, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-08-17 07:00:00', '2026-08-17 07:00:00'),
(66, 278, 1602, 4, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-08-28 08:00:00', '2026-08-28 08:00:00'),
(67, 277, 1603, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-08-20 08:00:00', '2026-08-20 08:00:00'),
(68, 279, 1603, 3, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-08-31 00:00:00', '2026-08-31 00:00:00'),
(69, 278, 1604, 4, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-08-23 00:00:00', '2026-08-23 00:00:00'),
(70, 279, 1605, 4, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-08-26 01:00:00', '2026-08-26 01:00:00'),
(71, 281, 1605, 5, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-09-06 02:00:00', '2026-09-06 02:00:00'),
(72, 280, 1606, 3, 'Có thể ngắm bình minh; nên chú ý sóng và biển báo an toàn.', 'approved', '2026-08-29 02:00:00', '2026-08-29 02:00:00'),
(73, 284, 1606, 5, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-09-09 03:00:00', '2026-09-09 03:00:00'),
(74, 281, 1607, 5, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-09-01 03:00:00', '2026-09-01 03:00:00'),
(75, 284, 1608, 5, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-04 04:00:00', '2026-09-04 04:00:00'),
(76, 276, 1608, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-09-15 05:00:00', '2026-09-15 05:00:00'),
(77, 275, 1609, 5, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-09-07 05:00:00', '2026-09-07 05:00:00'),
(78, 277, 1609, 4, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-09-18 06:00:00', '2026-09-18 06:00:00'),
(79, 276, 1610, 4, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-09-10 06:00:00', '2026-09-10 06:00:00'),
(80, 277, 1611, 5, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-09-13 07:00:00', '2026-09-13 07:00:00'),
(81, 279, 1611, 3, 'Cảnh biển đẹp, những ngày cuối tuần thường có khá đông khách.', 'approved', '2026-09-24 08:00:00', '2026-09-24 08:00:00'),
(82, 278, 1612, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-16 08:00:00', '2026-09-16 08:00:00'),
(83, 280, 1612, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-09-27 00:00:00', '2026-09-27 00:00:00'),
(84, 279, 1613, 4, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-09-19 00:00:00', '2026-09-19 00:00:00'),
(85, 280, 1614, 3, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-09-22 01:00:00', '2026-09-22 01:00:00'),
(86, 284, 1614, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-10-03 02:00:00', '2026-10-03 02:00:00'),
(87, 281, 1615, 5, 'Phù hợp chuyến đi ngắn để tìm hiểu thêm đời sống địa phương.', 'approved', '2026-09-25 02:00:00', '2026-09-25 02:00:00'),
(88, 275, 1615, 4, 'Nên đi ngày thường nếu muốn có nhiều thời gian tham quan.', 'pending', '2026-10-06 03:00:00', '2026-10-06 03:00:00'),
(89, 284, 1616, 5, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-28 03:00:00', '2026-09-28 03:00:00'),
(90, 275, 1617, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-10-01 04:00:00', '2026-10-01 04:00:00'),
(91, 277, 1617, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-10-12 05:00:00', '2026-10-12 05:00:00'),
(92, 276, 1618, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-10-04 05:00:00', '2026-10-04 05:00:00'),
(93, 278, 1618, 4, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-10-15 06:00:00', '2026-10-15 06:00:00'),
(94, 277, 1619, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-10-07 06:00:00', '2026-10-07 06:00:00'),
(95, 278, 1620, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-10-10 07:00:00', '2026-10-10 07:00:00'),
(96, 280, 1620, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-06-04 08:00:00', '2026-06-04 08:00:00'),
(97, 279, 1621, 4, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-10-13 08:00:00', '2026-10-13 08:00:00'),
(98, 281, 1621, 5, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-06-07 00:00:00', '2026-06-07 00:00:00'),
(99, 280, 1622, 3, 'Có thể ngắm bình minh; nên chú ý sóng và biển báo an toàn.', 'approved', '2026-10-16 00:00:00', '2026-10-16 00:00:00'),
(100, 281, 1623, 5, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-10-19 01:00:00', '2026-10-19 01:00:00'),
(101, 275, 1623, 4, 'Cảnh biển đẹp, những ngày cuối tuần thường có khá đông khách.', 'approved', '2026-06-13 02:00:00', '2026-06-13 02:00:00'),
(102, 284, 1624, 5, 'Cảnh biển đẹp, những ngày cuối tuần thường có khá đông khách.', 'approved', '2026-06-05 02:00:00', '2026-06-05 02:00:00'),
(103, 276, 1624, 5, 'Nước biển và cảnh quan rất đẹp khi trời quang.', 'approved', '2026-06-16 03:00:00', '2026-06-16 03:00:00'),
(104, 275, 1625, 5, 'Nước biển và cảnh quan rất đẹp khi trời quang.', 'approved', '2026-06-08 03:00:00', '2026-06-08 03:00:00'),
(105, 276, 1626, 4, 'Có thể ngắm bình minh; nên chú ý sóng và biển báo an toàn.', 'approved', '2026-06-11 04:00:00', '2026-06-11 04:00:00'),
(106, 278, 1626, 4, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-06-22 05:00:00', '2026-06-22 05:00:00'),
(107, 277, 1627, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-06-14 05:00:00', '2026-06-14 05:00:00'),
(108, 279, 1627, 3, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-06-25 06:00:00', '2026-06-25 06:00:00'),
(109, 278, 1628, 4, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-06-17 06:00:00', '2026-06-17 06:00:00'),
(110, 279, 1629, 4, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-06-20 07:00:00', '2026-06-20 07:00:00'),
(111, 281, 1629, 5, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-07-01 08:00:00', '2026-07-01 08:00:00'),
(112, 280, 1630, 3, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-06-23 08:00:00', '2026-06-23 08:00:00'),
(113, 284, 1630, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-07-04 00:00:00', '2026-07-04 00:00:00'),
(114, 281, 1631, 5, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-06-26 00:00:00', '2026-06-26 00:00:00'),
(115, 284, 1632, 5, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-06-29 01:00:00', '2026-06-29 01:00:00'),
(116, 276, 1632, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-07-10 02:00:00', '2026-07-10 02:00:00'),
(117, 275, 1633, 5, 'Điểm tham quan có nét đặc trưng riêng và dễ kết hợp vào lịch trình.', 'approved', '2026-07-02 02:00:00', '2026-07-02 02:00:00'),
(118, 277, 1633, 4, 'Trải nghiệm thú vị, nên hỏi trước thông tin giờ hoạt động.', 'approved', '2026-07-13 03:00:00', '2026-07-13 03:00:00'),
(119, 276, 1634, 4, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-07-05 03:00:00', '2026-07-05 03:00:00'),
(120, 277, 1635, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-07-08 04:00:00', '2026-07-08 04:00:00'),
(121, 279, 1635, 3, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-07-19 05:00:00', '2026-07-19 05:00:00'),
(122, 278, 1636, 4, 'Nên đi ngày thường nếu muốn có nhiều thời gian tham quan.', 'approved', '2026-07-11 05:00:00', '2026-07-11 05:00:00'),
(123, 280, 1636, 5, 'Điểm tham quan có nét đặc trưng riêng và dễ kết hợp vào lịch trình.', 'approved', '2026-07-22 06:00:00', '2026-07-22 06:00:00'),
(124, 279, 1637, 4, 'Nước biển và cảnh quan rất đẹp khi trời quang.', 'approved', '2026-07-14 06:00:00', '2026-07-14 06:00:00'),
(125, 280, 1638, 3, 'Có thể ngắm bình minh; nên chú ý sóng và biển báo an toàn.', 'approved', '2026-07-17 07:00:00', '2026-07-17 07:00:00'),
(126, 284, 1638, 5, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-07-28 08:00:00', '2026-07-28 08:00:00'),
(127, 281, 1639, 5, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-07-20 08:00:00', '2026-07-20 08:00:00'),
(128, 275, 1639, 4, 'Cảnh biển đẹp, những ngày cuối tuần thường có khá đông khách.', 'approved', '2026-07-31 00:00:00', '2026-07-31 00:00:00'),
(129, 284, 1640, 5, 'Cảnh biển đẹp, những ngày cuối tuần thường có khá đông khách.', 'approved', '2026-07-23 00:00:00', '2026-07-23 00:00:00'),
(130, 275, 1641, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-07-26 01:00:00', '2026-07-26 01:00:00'),
(131, 277, 1641, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-08-06 02:00:00', '2026-08-06 02:00:00'),
(132, 276, 1642, 4, 'Có thể ngắm bình minh; nên chú ý sóng và biển báo an toàn.', 'approved', '2026-07-29 02:00:00', '2026-07-29 02:00:00'),
(133, 278, 1642, 4, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-08-09 03:00:00', '2026-08-09 03:00:00'),
(134, 277, 1643, 5, 'Bãi biển thoáng, phù hợp chụp ảnh và đi dạo.', 'approved', '2026-08-01 03:00:00', '2026-08-01 03:00:00'),
(135, 278, 1644, 4, 'Nên đi ngày thường nếu muốn có nhiều thời gian tham quan.', 'approved', '2026-08-04 04:00:00', '2026-08-04 04:00:00'),
(136, 280, 1644, 5, 'Điểm tham quan có nét đặc trưng riêng và dễ kết hợp vào lịch trình.', 'approved', '2026-08-15 05:00:00', '2026-08-15 05:00:00'),
(137, 279, 1645, 4, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-08-07 05:00:00', '2026-08-07 05:00:00'),
(138, 281, 1645, 5, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-08-18 06:00:00', '2026-08-18 06:00:00'),
(139, 280, 1646, 3, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-08-10 06:00:00', '2026-08-10 06:00:00'),
(140, 281, 1647, 5, 'Phù hợp chuyến đi ngắn để tìm hiểu thêm đời sống địa phương.', 'approved', '2026-08-13 07:00:00', '2026-08-13 07:00:00'),
(141, 275, 1647, 4, 'Nên đi ngày thường nếu muốn có nhiều thời gian tham quan.', 'approved', '2026-08-24 08:00:00', '2026-08-24 08:00:00'),
(142, 284, 1648, 5, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-08-16 08:00:00', '2026-08-16 08:00:00'),
(143, 276, 1648, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-08-27 00:00:00', '2026-08-27 00:00:00'),
(144, 275, 1649, 5, 'Điểm tham quan có nét đặc trưng riêng và dễ kết hợp vào lịch trình.', 'approved', '2026-08-19 00:00:00', '2026-08-19 00:00:00'),
(145, 276, 1650, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-08-22 01:00:00', '2026-08-22 01:00:00'),
(146, 278, 1650, 4, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-09-02 02:00:00', '2026-09-02 02:00:00'),
(147, 277, 1651, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-08-25 02:00:00', '2026-08-25 02:00:00'),
(148, 279, 1651, 3, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-05 03:00:00', '2026-09-05 03:00:00'),
(149, 278, 1652, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-08-28 03:00:00', '2026-08-28 03:00:00'),
(150, 279, 1653, 4, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-08-31 04:00:00', '2026-08-31 04:00:00'),
(151, 281, 1653, 5, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'pending', '2026-09-11 05:00:00', '2026-09-11 05:00:00'),
(152, 280, 1654, 3, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-09-03 05:00:00', '2026-09-03 05:00:00'),
(153, 284, 1654, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-09-14 06:00:00', '2026-09-14 06:00:00'),
(154, 281, 1655, 5, 'Phù hợp chuyến đi ngắn để tìm hiểu thêm đời sống địa phương.', 'approved', '2026-09-06 06:00:00', '2026-09-06 06:00:00'),
(155, 284, 1656, 5, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-09-09 07:00:00', '2026-09-09 07:00:00'),
(156, 276, 1656, 5, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-09-20 08:00:00', '2026-09-20 08:00:00'),
(157, 275, 1657, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-09-12 08:00:00', '2026-09-12 08:00:00'),
(158, 277, 1657, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-09-23 00:00:00', '2026-09-23 00:00:00'),
(159, 276, 1658, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-09-15 00:00:00', '2026-09-15 00:00:00'),
(160, 277, 1659, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-09-18 01:00:00', '2026-09-18 01:00:00'),
(161, 279, 1659, 3, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-29 02:00:00', '2026-09-29 02:00:00'),
(162, 278, 1660, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-21 02:00:00', '2026-09-21 02:00:00'),
(163, 280, 1660, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-10-02 03:00:00', '2026-10-02 03:00:00'),
(164, 279, 1661, 4, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-09-24 03:00:00', '2026-09-24 03:00:00'),
(165, 280, 1662, 3, 'Trải nghiệm thú vị, nên hỏi trước thông tin giờ hoạt động.', 'approved', '2026-09-27 04:00:00', '2026-09-27 04:00:00'),
(166, 284, 1662, 5, 'Phù hợp chuyến đi ngắn để tìm hiểu thêm đời sống địa phương.', 'approved', '2026-10-08 05:00:00', '2026-10-08 05:00:00'),
(167, 281, 1663, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-09-30 05:00:00', '2026-09-30 05:00:00'),
(168, 275, 1663, 4, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-10-11 06:00:00', '2026-10-11 06:00:00'),
(169, 284, 1664, 5, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-10-03 06:00:00', '2026-10-03 06:00:00'),
(170, 275, 1665, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-10-06 07:00:00', '2026-10-06 07:00:00'),
(171, 277, 1665, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-10-17 08:00:00', '2026-10-17 08:00:00'),
(172, 276, 1666, 4, 'Trải nghiệm thú vị, nên hỏi trước thông tin giờ hoạt động.', 'approved', '2026-10-09 08:00:00', '2026-10-09 08:00:00'),
(173, 278, 1666, 4, 'Phù hợp chuyến đi ngắn để tìm hiểu thêm đời sống địa phương.', 'approved', '2026-06-03 00:00:00', '2026-06-03 00:00:00'),
(174, 277, 1667, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-10-12 00:00:00', '2026-10-12 00:00:00'),
(175, 278, 1668, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-10-15 01:00:00', '2026-10-15 01:00:00'),
(176, 280, 1668, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-06-09 02:00:00', '2026-06-09 02:00:00'),
(177, 279, 1669, 4, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-10-18 02:00:00', '2026-10-18 02:00:00'),
(178, 281, 1669, 5, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-06-12 03:00:00', '2026-06-12 03:00:00'),
(179, 280, 1670, 3, 'Trải nghiệm thú vị, nên hỏi trước thông tin giờ hoạt động.', 'approved', '2026-06-04 03:00:00', '2026-06-04 03:00:00'),
(180, 281, 1671, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-06-07 04:00:00', '2026-06-07 04:00:00'),
(181, 275, 1671, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-06-18 05:00:00', '2026-06-18 05:00:00'),
(182, 284, 1672, 5, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-06-10 05:00:00', '2026-06-10 05:00:00'),
(183, 276, 1672, 5, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'pending', '2026-06-21 06:00:00', '2026-06-21 06:00:00'),
(184, 275, 1673, 5, 'Di tích giàu giá trị lịch sử, đáng dành thời gian đọc bảng giới thiệu.', 'approved', '2026-06-13 06:00:00', '2026-06-13 06:00:00'),
(185, 276, 1674, 4, 'Kiến trúc đẹp, thích hợp tham quan trong buổi sáng.', 'approved', '2026-06-16 07:00:00', '2026-06-16 07:00:00'),
(186, 278, 1674, 4, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-06-27 08:00:00', '2026-06-27 08:00:00'),
(187, 277, 1675, 5, 'Không gian thanh tĩnh, nên ăn mặc lịch sự khi ghé thăm.', 'approved', '2026-06-19 08:00:00', '2026-06-19 08:00:00'),
(188, 279, 1675, 3, 'Có nhiều góc tìm hiểu văn hóa địa phương, trải nghiệm khá thú vị.', 'approved', '2026-06-30 00:00:00', '2026-06-30 00:00:00'),
(189, 278, 1676, 4, 'Nên đi ngày thường nếu muốn có nhiều thời gian tham quan.', 'approved', '2026-06-22 00:00:00', '2026-06-22 00:00:00'),
(190, 279, 1677, 4, 'Vị trí thuận tiện, phòng yên tĩnh và dịch vụ nhìn chung chu đáo.', 'approved', '2026-06-25 01:00:00', '2026-06-25 01:00:00'),
(191, 281, 1677, 5, 'Không gian lưu trú đẹp, thích hợp nghỉ ngơi sau lịch trình tham quan.', 'approved', '2026-07-06 02:00:00', '2026-07-06 02:00:00'),
(192, 280, 1678, 3, 'Không gian lưu trú đẹp, thích hợp nghỉ ngơi sau lịch trình tham quan.', 'approved', '2026-06-28 02:00:00', '2026-06-28 02:00:00'),
(193, 284, 1678, 5, 'Trải nghiệm ổn, cần kiểm tra giá và hạng phòng trước khi đặt.', 'approved', '2026-07-09 03:00:00', '2026-07-09 03:00:00'),
(194, 281, 1679, 5, 'Trải nghiệm ổn, cần kiểm tra giá và hạng phòng trước khi đặt.', 'approved', '2026-07-01 03:00:00', '2026-07-01 03:00:00'),
(195, 284, 1680, 5, 'Cảnh quan dễ chịu, nhưng thời gian nhận phòng có thể đông vào cuối tuần.', 'approved', '2026-07-04 04:00:00', '2026-07-04 04:00:00'),
(196, 276, 1680, 5, 'Vị trí thuận tiện, phòng yên tĩnh và dịch vụ nhìn chung chu đáo.', 'approved', '2026-07-15 05:00:00', '2026-07-15 05:00:00'),
(197, 275, 1681, 5, 'Vị trí thuận tiện, phòng yên tĩnh và dịch vụ nhìn chung chu đáo.', 'approved', '2026-07-07 05:00:00', '2026-07-07 05:00:00'),
(198, 277, 1681, 4, 'Không gian lưu trú đẹp, thích hợp nghỉ ngơi sau lịch trình tham quan.', 'approved', '2026-07-18 06:00:00', '2026-07-18 06:00:00'),
(199, 276, 1682, 4, 'Không gian lưu trú đẹp, thích hợp nghỉ ngơi sau lịch trình tham quan.', 'approved', '2026-07-10 06:00:00', '2026-07-10 06:00:00'),
(200, 277, 1683, 5, 'Trải nghiệm ổn, cần kiểm tra giá và hạng phòng trước khi đặt.', 'approved', '2026-07-13 07:00:00', '2026-07-13 07:00:00'),
(201, 279, 1683, 3, 'Cảnh quan dễ chịu, nhưng thời gian nhận phòng có thể đông vào cuối tuần.', 'approved', '2026-07-24 08:00:00', '2026-07-24 08:00:00'),
(202, 278, 1684, 4, 'Cảnh quan dễ chịu, nhưng thời gian nhận phòng có thể đông vào cuối tuần.', 'approved', '2026-07-16 08:00:00', '2026-07-16 08:00:00'),
(203, 280, 1684, 5, 'Vị trí thuận tiện, phòng yên tĩnh và dịch vụ nhìn chung chu đáo.', 'approved', '2026-07-27 00:00:00', '2026-07-27 00:00:00'),
(204, 279, 1685, 4, 'Vị trí thuận tiện, phòng yên tĩnh và dịch vụ nhìn chung chu đáo.', 'approved', '2026-07-19 00:00:00', '2026-07-19 00:00:00'),
(205, 280, 1686, 3, 'Không gian lưu trú đẹp, thích hợp nghỉ ngơi sau lịch trình tham quan.', 'approved', '2026-07-22 01:00:00', '2026-07-22 01:00:00'),
(206, 284, 1686, 5, 'Trải nghiệm ổn, cần kiểm tra giá và hạng phòng trước khi đặt.', 'approved', '2026-08-02 02:00:00', '2026-08-02 02:00:00'),
(207, 281, 1687, 5, 'Trải nghiệm ổn, cần kiểm tra giá và hạng phòng trước khi đặt.', 'approved', '2026-07-25 02:00:00', '2026-07-25 02:00:00'),
(208, 275, 1687, 4, 'Cảnh quan dễ chịu, nhưng thời gian nhận phòng có thể đông vào cuối tuần.', 'approved', '2026-08-05 03:00:00', '2026-08-05 03:00:00'),
(209, 284, 1688, 5, 'Cảnh quan dễ chịu, nhưng thời gian nhận phòng có thể đông vào cuối tuần.', 'approved', '2026-07-28 03:00:00', '2026-07-28 03:00:00'),
(210, 275, 1689, 5, 'Vị trí thuận tiện, phòng yên tĩnh và dịch vụ nhìn chung chu đáo.', 'approved', '2026-07-31 04:00:00', '2026-07-31 04:00:00'),
(211, 277, 1689, 4, 'Không gian lưu trú đẹp, thích hợp nghỉ ngơi sau lịch trình tham quan.', 'approved', '2026-08-11 05:00:00', '2026-08-11 05:00:00'),
(212, 276, 1690, 4, 'Không gian lưu trú đẹp, thích hợp nghỉ ngơi sau lịch trình tham quan.', 'approved', '2026-08-03 05:00:00', '2026-08-03 05:00:00'),
(213, 278, 1690, 4, 'Trải nghiệm ổn, cần kiểm tra giá và hạng phòng trước khi đặt.', 'approved', '2026-08-14 06:00:00', '2026-08-14 06:00:00'),
(214, 277, 1691, 5, 'Trải nghiệm ổn, cần kiểm tra giá và hạng phòng trước khi đặt.', 'approved', '2026-08-06 06:00:00', '2026-08-06 06:00:00'),
(215, 278, 1692, 4, 'Cảnh quan dễ chịu, nhưng thời gian nhận phòng có thể đông vào cuối tuần.', 'approved', '2026-08-09 07:00:00', '2026-08-09 07:00:00'),
(216, 280, 1692, 5, 'Vị trí thuận tiện, phòng yên tĩnh và dịch vụ nhìn chung chu đáo.', 'approved', '2026-08-20 08:00:00', '2026-08-20 08:00:00'),
(217, 279, 1693, 4, 'Vị trí thuận tiện, phòng yên tĩnh và dịch vụ nhìn chung chu đáo.', 'approved', '2026-08-12 08:00:00', '2026-08-12 08:00:00'),
(218, 281, 1693, 5, 'Không gian lưu trú đẹp, thích hợp nghỉ ngơi sau lịch trình tham quan.', 'approved', '2026-08-23 00:00:00', '2026-08-23 00:00:00'),
(219, 280, 1694, 3, 'Không gian lưu trú đẹp, thích hợp nghỉ ngơi sau lịch trình tham quan.', 'approved', '2026-08-15 00:00:00', '2026-08-15 00:00:00'),
(220, 281, 1695, 5, 'Trải nghiệm ổn, cần kiểm tra giá và hạng phòng trước khi đặt.', 'approved', '2026-08-18 01:00:00', '2026-08-18 01:00:00'),
(221, 275, 1695, 4, 'Cảnh quan dễ chịu, nhưng thời gian nhận phòng có thể đông vào cuối tuần.', 'approved', '2026-08-29 02:00:00', '2026-08-29 02:00:00'),
(222, 284, 1696, 5, 'Cảnh quan dễ chịu, nhưng thời gian nhận phòng có thể đông vào cuối tuần.', 'approved', '2026-08-21 02:00:00', '2026-08-21 02:00:00'),
(223, 276, 1696, 5, 'Vị trí thuận tiện, phòng yên tĩnh và dịch vụ nhìn chung chu đáo.', 'approved', '2026-09-01 03:00:00', '2026-09-01 03:00:00'),
(224, 275, 713, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-08-24 03:00:00', '2026-08-24 03:00:00'),
(225, 276, 714, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-08-27 04:00:00', '2026-08-27 04:00:00'),
(226, 278, 714, 4, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-09-07 05:00:00', '2026-09-07 05:00:00'),
(227, 277, 715, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-08-30 05:00:00', '2026-08-30 05:00:00'),
(228, 279, 715, 3, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-10 06:00:00', '2026-09-10 06:00:00'),
(229, 278, 716, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-02 06:00:00', '2026-09-02 06:00:00'),
(230, 279, 717, 4, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-09-05 07:00:00', '2026-09-05 07:00:00'),
(231, 281, 717, 5, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-09-16 08:00:00', '2026-09-16 08:00:00'),
(232, 280, 718, 3, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-09-08 08:00:00', '2026-09-08 08:00:00'),
(233, 284, 718, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-09-19 00:00:00', '2026-09-19 00:00:00'),
(234, 281, 719, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-09-11 00:00:00', '2026-09-11 00:00:00'),
(235, 284, 720, 5, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-14 01:00:00', '2026-09-14 01:00:00'),
(236, 276, 720, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-09-25 02:00:00', '2026-09-25 02:00:00'),
(237, 275, 721, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-09-17 02:00:00', '2026-09-17 02:00:00'),
(238, 277, 721, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-09-28 03:00:00', '2026-09-28 03:00:00'),
(239, 276, 722, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-09-20 03:00:00', '2026-09-20 03:00:00'),
(240, 277, 1281, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-09-23 04:00:00', '2026-09-23 04:00:00'),
(241, 279, 1281, 3, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-10-04 05:00:00', '2026-10-04 05:00:00'),
(242, 278, 1282, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-09-26 05:00:00', '2026-09-26 05:00:00'),
(243, 280, 1282, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-10-07 06:00:00', '2026-10-07 06:00:00'),
(244, 279, 1283, 4, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-09-29 06:00:00', '2026-09-29 06:00:00'),
(245, 280, 1285, 3, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-10-02 07:00:00', '2026-10-02 07:00:00'),
(246, 284, 1285, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'pending', '2026-10-13 08:00:00', '2026-10-13 08:00:00'),
(247, 281, 1290, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-10-05 08:00:00', '2026-10-05 08:00:00'),
(248, 275, 1290, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-10-16 00:00:00', '2026-10-16 00:00:00'),
(249, 284, 1291, 5, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-10-08 00:00:00', '2026-10-08 00:00:00'),
(250, 275, 1295, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-10-11 01:00:00', '2026-10-11 01:00:00'),
(251, 277, 1295, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-06-05 02:00:00', '2026-06-05 02:00:00'),
(252, 276, 1296, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-10-14 02:00:00', '2026-10-14 02:00:00'),
(253, 278, 1296, 4, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-06-08 03:00:00', '2026-06-08 03:00:00'),
(254, 277, 1297, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-10-17 03:00:00', '2026-10-17 03:00:00'),
(255, 278, 1298, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-06-03 04:00:00', '2026-06-03 04:00:00'),
(256, 280, 1298, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-06-14 05:00:00', '2026-06-14 05:00:00'),
(257, 279, 1299, 4, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-06-06 05:00:00', '2026-06-06 05:00:00'),
(258, 281, 1299, 5, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-06-17 06:00:00', '2026-06-17 06:00:00'),
(259, 280, 1300, 3, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-06-09 06:00:00', '2026-06-09 06:00:00'),
(260, 281, 1301, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-06-12 07:00:00', '2026-06-12 07:00:00'),
(261, 275, 1301, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-06-23 08:00:00', '2026-06-23 08:00:00'),
(262, 284, 1302, 5, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-06-15 08:00:00', '2026-06-15 08:00:00'),
(263, 276, 1302, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-06-26 00:00:00', '2026-06-26 00:00:00'),
(264, 275, 1303, 5, 'Cảnh quan ấn tượng, sáng sớm trời mát và dễ chụp ảnh.', 'approved', '2026-06-18 00:00:00', '2026-06-18 00:00:00'),
(265, 276, 1304, 4, 'Đường đi có một vài đoạn gập ghềnh, nên chuẩn bị giày phù hợp.', 'approved', '2026-06-21 01:00:00', '2026-06-21 01:00:00'),
(266, 278, 1304, 4, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-07-02 02:00:00', '2026-07-02 02:00:00'),
(267, 277, 1305, 5, 'Không gian thoáng và yên bình, thích hợp đi cùng bạn bè.', 'approved', '2026-06-24 02:00:00', '2026-06-24 02:00:00'),
(268, 279, 1305, 3, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-07-05 03:00:00', '2026-07-05 03:00:00'),
(269, 278, 1306, 4, 'Phong cảnh đẹp nhưng cần kiểm tra thời tiết trước chuyến đi.', 'approved', '2026-06-27 03:00:00', '2026-06-27 03:00:00');

-- Table structure for table `room_types`
--

DROP TABLE IF EXISTS `room_types`;
CREATE TABLE IF NOT EXISTS `room_types` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `destination_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `price_per_night` decimal(12,2) NOT NULL,
  `quantity` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_rooms_destination` (`destination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`id`, `destination_id`, `name`, `capacity`, `price_per_night`, `quantity`, `description`, `created_at`, `updated_at`) VALUES
(42, 1281, 'Mercure Danang French Village Bana Hills', 2, 900000.00, 15, 'Phòng nghỉ tiện nghi tại Sun World Bà Nà Hills', '2026-10-19 02:24:45', '2026-10-19 02:24:45'),
(43, 1282, 'Cabin Du Thuyền Ambassador Cruise', 2, 1500000.00, 15, 'Phòng nghỉ tiện nghi tại Du Thuyền Vịnh Hạ Long', '2026-10-19 02:24:45', '2026-10-19 02:24:45'),
(44, 1283, 'Phòng Deluxe King Hướng Biển', 2, 2800000.00, 15, 'Phòng nghỉ tiện nghi tại Vinpearl Resort & Spa Phú Quốc', '2026-10-19 02:24:45', '2026-10-19 02:24:45');

-- --------------------------------------------------------

--

-- Dữ liệu bổ sung cho room_types
INSERT INTO `room_types` (`id`, `destination_id`, `name`, `capacity`, `price_per_night`, `quantity`, `description`, `created_at`, `updated_at`) VALUES
(45, 1677, 'Phòng tiêu chuẩn (demo)', 2, 2650000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(46, 1677, 'Phòng Deluxe (demo)', 2, 3700000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(47, 1677, 'Suite gia đình (demo)', 4, 5550000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(48, 1678, 'Phòng tiêu chuẩn (demo)', 2, 1000000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(49, 1678, 'Phòng Deluxe (demo)', 2, 1400000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(50, 1678, 'Suite gia đình (demo)', 4, 2100000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(51, 1679, 'Phòng tiêu chuẩn (demo)', 2, 2650000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(52, 1679, 'Phòng Deluxe (demo)', 2, 3700000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(53, 1679, 'Suite gia đình (demo)', 4, 5550000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(54, 1680, 'Phòng tiêu chuẩn (demo)', 2, 1550000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(55, 1680, 'Phòng Deluxe (demo)', 2, 2150000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(56, 1680, 'Suite gia đình (demo)', 4, 3250000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(57, 1681, 'Phòng tiêu chuẩn (demo)', 2, 1550000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(58, 1681, 'Phòng Deluxe (demo)', 2, 2150000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(59, 1681, 'Suite gia đình (demo)', 4, 3250000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(60, 1682, 'Phòng tiêu chuẩn (demo)', 2, 800000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(61, 1682, 'Phòng Deluxe (demo)', 2, 1150000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(62, 1682, 'Suite gia đình (demo)', 4, 1700000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(63, 1683, 'Phòng tiêu chuẩn (demo)', 2, 1000000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(64, 1683, 'Phòng Deluxe (demo)', 2, 1400000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(65, 1683, 'Suite gia đình (demo)', 4, 2100000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(66, 1684, 'Phòng tiêu chuẩn (demo)', 2, 1550000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(67, 1684, 'Phòng Deluxe (demo)', 2, 2150000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(68, 1684, 'Suite gia đình (demo)', 4, 3250000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(69, 1685, 'Phòng tiêu chuẩn (demo)', 2, 1350000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(70, 1685, 'Phòng Deluxe (demo)', 2, 1900000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(71, 1685, 'Suite gia đình (demo)', 4, 2850000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(72, 1686, 'Phòng tiêu chuẩn (demo)', 2, 1450000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(73, 1686, 'Phòng Deluxe (demo)', 2, 2050000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(74, 1686, 'Suite gia đình (demo)', 4, 3050000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(75, 1687, 'Phòng tiêu chuẩn (demo)', 2, 800000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(76, 1687, 'Phòng Deluxe (demo)', 2, 1150000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(77, 1687, 'Suite gia đình (demo)', 4, 1700000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(78, 1688, 'Phòng tiêu chuẩn (demo)', 2, 1000000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(79, 1688, 'Phòng Deluxe (demo)', 2, 1400000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(80, 1688, 'Suite gia đình (demo)', 4, 2100000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(81, 1689, 'Phòng tiêu chuẩn (demo)', 2, 1200000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(82, 1689, 'Phòng Deluxe (demo)', 2, 1650000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(83, 1689, 'Suite gia đình (demo)', 4, 2500000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(84, 1690, 'Phòng tiêu chuẩn (demo)', 2, 1350000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(85, 1690, 'Phòng Deluxe (demo)', 2, 1900000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(86, 1690, 'Suite gia đình (demo)', 4, 2850000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(87, 1691, 'Phòng tiêu chuẩn (demo)', 2, 1550000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(88, 1691, 'Phòng Deluxe (demo)', 2, 2150000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(89, 1691, 'Suite gia đình (demo)', 4, 3250000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(90, 1692, 'Phòng tiêu chuẩn (demo)', 2, 800000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(91, 1692, 'Phòng Deluxe (demo)', 2, 1150000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(92, 1692, 'Suite gia đình (demo)', 4, 1700000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(93, 1693, 'Phòng tiêu chuẩn (demo)', 2, 2100000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(94, 1693, 'Phòng Deluxe (demo)', 2, 2950000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(95, 1693, 'Suite gia đình (demo)', 4, 4400000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(96, 1694, 'Phòng tiêu chuẩn (demo)', 2, 2650000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(97, 1694, 'Phòng Deluxe (demo)', 2, 3700000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(98, 1694, 'Suite gia đình (demo)', 4, 5550000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(99, 1695, 'Phòng tiêu chuẩn (demo)', 2, 1450000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(100, 1695, 'Phòng Deluxe (demo)', 2, 2050000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(101, 1695, 'Suite gia đình (demo)', 4, 3050000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(102, 1696, 'Phòng tiêu chuẩn (demo)', 2, 1550000.00, 12, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(103, 1696, 'Phòng Deluxe (demo)', 2, 2150000.00, 8, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(104, 1696, 'Suite gia đình (demo)', 4, 3250000.00, 4, 'Hạng phòng giả lập để kiểm thử đặt phòng; giá và số phòng không phải dữ liệu kinh doanh thực tế.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(105, 1281, 'Phòng gia đình (demo)', 4, 1900000.00, 5, 'Loại phòng và mức giá minh họa cho môi trường kiểm thử.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(106, 1281, 'Suite cao cấp (demo)', 2, 2500000.00, 3, 'Loại phòng và mức giá minh họa cho môi trường kiểm thử.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(107, 1282, 'Phòng gia đình (demo)', 4, 3420000.00, 5, 'Loại phòng và mức giá minh họa cho môi trường kiểm thử.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(108, 1282, 'Suite cao cấp (demo)', 2, 4500000.00, 3, 'Loại phòng và mức giá minh họa cho môi trường kiểm thử.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(109, 1283, 'Phòng gia đình (demo)', 4, 5700000.00, 5, 'Loại phòng và mức giá minh họa cho môi trường kiểm thử.', '2026-10-27 09:00:00', '2026-10-27 09:00:00'),
(110, 1283, 'Suite cao cấp (demo)', 2, 7500000.00, 3, 'Loại phòng và mức giá minh họa cho môi trường kiểm thử.', '2026-10-27 09:00:00', '2026-10-27 09:00:00');

-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sessions_user` (`user_id`),
  KEY `idx_sessions_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Session data intentionally excluded from the seed (ephemeral/auth-related).
--

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

DROP TABLE IF EXISTS `trips`;
CREATE TABLE IF NOT EXISTS `trips` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_trips_user_status` (`user_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `user_id`, `name`, `description`, `start_date`, `end_date`, `budget`, `status`, `created_at`, `updated_at`) VALUES
(43, 275, 'Da Nang & Hoi An Getaway', 'A sample three-day central Viet Nam itinerary.', '2026-11-18', '2026-11-20', 8000000.00, 'planned', '2026-10-19 02:24:40', '2026-10-19 02:24:40'),
(44, 275, 'Hành trình Khám phá Đà Nẵng - Hội An', 'Bà Nà Hills, Bán đảo Sơn Trà, Ngũ Hành Sơn và phố đèn lồng Hội An thơ mộng.', '2026-10-10', '2026-10-15', 15000000.00, 'planned', '2026-10-19 02:24:46', '2026-10-19 02:24:46'),
(45, 275, 'Du Thuyền Nghỉ Dưỡng Vịnh Hạ Long', 'Trải nghiệm chèo thuyền kayak hang Luồn, tắm biển đảo Ti Tốp và tiệc trà hoàng hôn.', '2026-09-26', '2026-09-30', 10000000.00, 'ongoing', '2026-10-19 02:24:46', '2026-10-19 02:24:46'),
(46, 275, 'Săn Mây Đỉnh Fansipan Sa Pa', 'Chinh phục đỉnh núi cao nhất Việt Nam, trekking bản Cát Cát và thưởng thức lẩu cá tầm.', '2026-08-15', '2026-08-18', 10000000.00, 'completed', '2026-10-19 02:24:46', '2026-10-19 02:24:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `avatar` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_role_active` (`role`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=286 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `avatar`, `phone`, `city`, `bio`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(274, 'Travel Planner Admin', 'admin@travelplanner.test', '2026-10-19 02:24:38', '$2y$12$ndFyDwkR/E8M2xb512oMheUcjg/Vk2WS3mazB3qRusc4XvZKi1S2C', 'admin', NULL, NULL, NULL, NULL, 1, '2026-10-19 02:24:38', '2026-10-19 02:24:38', NULL),
(275, 'Nguyen Minh Anh', 'user@travelplanner.test', '2026-10-19 02:24:38', '$2y$12$RrNzdaepNqzHNyaRtU3gvOMgO6BL2tXZBOzd8FkZuWM7nHgruEnum', 'user', NULL, NULL, NULL, NULL, 1, '2026-10-19 02:24:38', '2026-10-19 02:24:38', NULL),
(276, 'Tran Gia Han', 'han@travelplanner.test', '2026-10-19 02:24:38', '$2y$12$y5Gc9DjobpxPsAdhQKLequbGT17aSv4GMgoaD8A.55PigXtViWg8O', 'user', NULL, NULL, NULL, NULL, 1, '2026-10-19 02:24:38', '2026-10-19 02:24:38', NULL),
(277, 'Le Quoc Bao', 'bao@travelplanner.test', '2026-10-19 02:24:39', '$2y$12$o/tRQGpsnCASo/8u6mWX5u.3GnmmZEO0aOD/EApTyabCWKbM2xzEq', 'user', NULL, NULL, NULL, NULL, 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39', NULL),
(278, 'Pham Ngoc Linh', 'linh@travelplanner.test', '2026-10-19 02:24:39', '$2y$12$8ILBIjZa5Ef/KWVP9MWxieoDJoEueW2horKjzOo8OBXEeqtsE.sRm', 'user', NULL, NULL, NULL, NULL, 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39', NULL),
(279, 'Vo Tuan Kiet', 'kiet@travelplanner.test', '2026-10-19 02:24:39', '$2y$12$ev4.6oiTQU8D4xnWC/utBe8IUtv7zMmgMOytXfk9N4uRtjex95H.e', 'user', NULL, NULL, NULL, NULL, 1, '2026-10-19 02:24:39', '2026-10-19 02:24:39', NULL),
(280, 'Nguyễn Bảo Ngọc', 'ngoc.travel@travelplanner.com', '2026-10-19 02:24:39', '$2y$12$X4SC5yN5KM7sOuYudoHCMuZPhQOphtcGqB1c4w8M6Jdb/FD4E33lW', 'user', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop', '0912 345 678', 'Đà Nẵng', 'Đam mê du lịch tự túc, nhiếp ảnh phong cảnh và khám phá ẩm thực đường phố các vùng miền.', 1, '2026-01-15 02:30:00', '2026-10-19 02:24:41', NULL),
(281, 'Trần Hoàng Long', 'long.tran@travelplanner.com', NULL, '$2y$12$8eOpmty5Uq9IfMHAnpC6Tuap5GO3QJeuNx9/u9V69haAJawGFlEJW', 'user', NULL, NULL, NULL, NULL, 1, '2026-02-02 07:20:00', '2026-10-19 02:24:41', NULL),
(282, 'Hệ Thống Admin', 'admin@travelplanner.com', NULL, '$2y$12$6klsSkIeSIA8OXQ5GrVU..O/QsaO79atTKSiSUCdn1wvLELy0FRUO', 'admin', NULL, NULL, NULL, NULL, 1, '2026-01-01 01:00:00', '2026-10-19 02:24:41', NULL),
(283, 'Lê Thu Hà (Tạm khóa)', 'ha.le@travelplanner.com', NULL, '$2y$12$6tb9vlg9blM76FVsXqppnu5PGGz6p51Jd7OWqWUfJ.PCvMkLTWGr6', 'user', NULL, NULL, NULL, NULL, 0, '2026-03-18 09:45:00', '2026-10-19 02:24:41', NULL),
(284, 'Hoàng Đức Anh', 'ducanh@gmail.com', NULL, '$2y$12$ff1lsKaatT5FaIA5he.pQ.KwByUJa6NxHkyyk/Q0BkCcxGLwRJctK', 'user', NULL, NULL, NULL, NULL, 1, '2026-10-19 02:24:45', '2026-10-19 02:24:45', NULL),
(285, 'Spam Account', 'spam@bot.net', NULL, '$2y$12$3QIfiCbeTnj.jkk1tQb5zOg6IzDwSLSKhGFLcLeZUoPE6Bc9gDTNm', 'user', NULL, NULL, NULL, NULL, 1, '2026-10-19 02:24:45', '2026-10-19 02:24:45', NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_room` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_bookings_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `destinations`
--
ALTER TABLE `destinations`
  ADD CONSTRAINT `fk_destinations_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_destinations_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `destination_images`
--
ALTER TABLE `destination_images`
  ADD CONSTRAINT `fk_images_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `fk_expenses_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `itinerary_items`
--
ALTER TABLE `itinerary_items`
  ADD CONSTRAINT `fk_itinerary_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_itinerary_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_itinerary_trip` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `room_types`
--
ALTER TABLE `room_types`
  ADD CONSTRAINT `fk_rooms_destination` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `fk_trips_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
