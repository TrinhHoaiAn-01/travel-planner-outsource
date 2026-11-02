<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đánh Giá Của Tôi - Travel Planner</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3.3 & Bootstrap Icons 1.11.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* Đồng bộ 100% phong cách thiết kế Phần 4.3: Quản lý chuyến đi (Image 1 Style) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            line-height: 1.5;
            min-height: 100vh;
        }

        /* Thanh điều hướng Navbar chuẩn hệ thống */
        .navbar-custom {
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 12px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar-brand-custom {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: -0.5px;
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            background: #0066FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
        }

        .navbar-nav-custom {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item-custom a {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 9999px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            transition: all 0.2s;
        }

        .nav-item-custom a:hover {
            color: #0066FF;
            background-color: #F1F5F9;
        }

        .nav-item-custom.active a {
            background-color: #EBF5FF;
            color: #0066FF;
            font-weight: 600;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-admin {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            color: #0F172A;
            background: #FFFFFF;
            transition: background 0.2s;
        }

        .btn-admin:hover {
            background-color: #F8FAFC;
        }

        .user-dropdown-custom {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .user-avatar-custom {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #2563EB;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        /* Khung nội dung chính */
        .container-custom {
            max-width: 1240px;
            margin: 0 auto;
            padding: 28px 24px 60px;
        }

        /* Breadcrumb điều hướng */
        .breadcrumb-custom {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #64748B;
            margin-bottom: 12px;
        }

        .breadcrumb-custom a {
            color: #0066FF;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-custom a:hover {
            text-decoration: underline;
        }

        /* Header tiêu đề trang */
        .page-header-custom {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.6px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-subtitle {
            font-size: 15px;
            color: #64748B;
            font-weight: 400;
        }

        .btn-create-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #0066FF;
            color: #FFFFFF;
            padding: 12px 24px;
            border-radius: 9999px;
            font-size: 14.5px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0, 102, 255, 0.22);
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-create-action:hover {
            background-color: #0052CC;
            transform: translateY(-1px);
            color: #FFFFFF;
        }

        /* Bảng thống kê tóm tắt 4 trạng thái */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 13px;
            font-weight: 600;
            color: #64748B;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.1;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-icon-all { background-color: #EFF6FF; color: #0066FF; }
        .stat-icon-approved { background-color: #ECFDF5; color: #059669; }
        .stat-icon-pending { background-color: #FEF3C7; color: #D97706; }
        .stat-icon-hidden { background-color: #FEE2E2; color: #DC2626; }

        /* Khối Tabs trạng thái & Thanh tìm kiếm theo chuẩn Image 1 */
        .tabs-and-search-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 30px;
            padding-bottom: 12px;
            border-bottom: 1px solid #E2E8F0;
        }

        .status-tabs {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            scrollbar-width: none;
            padding: 4px 0;
        }

        .status-pill {
            padding: 8px 20px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 500;
            color: #0066FF;
            background-color: transparent;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
        }

        .status-pill:hover {
            background-color: #EFF6FF;
            color: #0066FF;
        }

        .status-pill.active {
            background-color: #0066FF;
            color: #FFFFFF;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 102, 255, 0.2);
        }

        .status-pill .pill-counter {
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 9999px;
            background: rgba(0, 102, 255, 0.12);
            color: #0066FF;
            font-weight: 700;
        }

        .status-pill.active .pill-counter {
            background: rgba(255, 255, 255, 0.25);
            color: #FFFFFF;
        }

        /* Thanh tìm kiếm và bộ lọc số sao */
        .search-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #FFFFFF;
            padding: 10px 16px;
            border-radius: 14px;
            border: 1px solid #CBD5E1;
            flex-wrap: wrap;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .search-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 260px;
        }

        .search-input-group input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 14px;
            color: #0F172A;
            background: transparent;
        }

        .search-input-group input::placeholder {
            color: #94A3B8;
        }

        .rating-filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13.5px;
            color: #64748B;
        }

        .rating-select {
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13.5px;
            color: #0F172A;
            outline: none;
            background: #FFFFFF;
        }

        .btn-filter {
            background-color: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-filter:hover {
            background-color: #0052CC;
        }

        .btn-reset {
            background-color: #FFFFFF;
            color: #475569;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-reset:hover {
            background-color: #F1F5F9;
            color: #0F172A;
        }

        /* Lưới hiển thị danh sách thẻ Đánh giá (Grid 3 Cột Chuẩn Section 4.3) */
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        @media (max-width: 1024px) {
            .reviews-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .reviews-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Thẻ Đánh giá (Review Card) */
        .review-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            transition: transform 0.25s, box-shadow 0.25s;
            position: relative;
        }

        .review-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        }

        .card-banner {
            position: relative;
            height: 190px;
            width: 100%;
            overflow: hidden;
            background: #E2E8F0;
        }

        .card-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
        }

        .review-card:hover .card-banner img {
            transform: scale(1.04);
        }

        /* Huy hiệu trạng thái review ở góc trên bên trái banner */
        .status-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 700;
            z-index: 10;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        /* Đã được duyệt (approved) */
        .badge-approved {
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        /* Đang chờ duyệt (pending) */
        .badge-pending {
            background-color: #FEF3C7;
            color: #92400E;
            border: 1px solid #FDE68A;
        }

        /* Đã bị ẩn (hidden) */
        .badge-hidden {
            background-color: #F1F5F9;
            color: #475569;
            border: 1px solid #CBD5E1;
        }

        /* Nút tùy chọn 3 chấm ở góc trên bên phải banner */
        .menu-button {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #334155;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
            z-index: 10;
            transition: background 0.2s;
        }

        .menu-button:hover {
            background: #FFFFFF;
            color: #0F172A;
        }

        /* Dropdown Menu */
        .menu-dropdown {
            display: none;
            position: absolute;
            top: 52px;
            right: 14px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
            min-width: 180px;
            z-index: 20;
            overflow: hidden;
        }

        .menu-dropdown.show {
            display: block;
        }

        .menu-item-btn {
            width: 100%;
            text-align: left;
            padding: 10px 16px;
            border: none;
            background: transparent;
            font-size: 13.5px;
            font-weight: 500;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
        }

        .menu-item-btn:hover {
            background: #F8FAFC;
            color: #0066FF;
        }

        /* Thân thẻ (Card Body) */
        .card-body-custom {
            padding: 20px 22px 22px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        /* Thông tin ngày đánh giá */
        .review-date-meta {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: #64748B;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .review-date-meta i {
            color: #0066FF;
        }

        .destination-name {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 10px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-decoration: none;
            transition: color 0.2s;
        }

        .destination-name:hover {
            color: #0066FF;
        }

        /* Hàng sao đánh giá & điểm số */
        .stars-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }

        .stars-group {
            display: flex;
            align-items: center;
            gap: 2px;
            color: #F59E0B;
            font-size: 16px;
        }

        .rating-numeric {
            font-size: 14px;
            font-weight: 700;
            color: #0F172A;
            margin-left: 4px;
        }

        /* Đoạn văn bản nhận xét của người dùng */
        .review-comment {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 16px;
            font-style: italic;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 48px;
        }

        /* Hộp thông báo quy tắc và trạng thái kiểm duyệt (Status & Business Rules Info Box) */
        .rule-info-box {
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 12.5px;
            font-weight: 500;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 16px;
        }

        .rule-info-pending {
            background-color: #FFFBEB;
            color: #92400E;
            border: 1px solid #FDE68A;
        }

        .rule-info-approved {
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .rule-info-hidden {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        /* Chân thẻ (Card Footer) */
        .card-footer-custom {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .btn-card-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn-edit-card {
            background-color: #EFF6FF;
            color: #0066FF;
            border-color: #BFDBFE;
        }

        .btn-edit-card:hover {
            background-color: #DBEAFE;
            color: #0052CC;
        }

        .btn-delete-card {
            background-color: #FEF2F2;
            color: #DC2626;
            border-color: #FECACA;
        }

        .btn-delete-card:hover {
            background-color: #FEE2E2;
            color: #B91C1C;
        }

        .btn-view-card {
            background-color: #F8FAFC;
            color: #475569;
            border-color: #E2E8F0;
        }

        .btn-view-card:hover {
            background-color: #F1F5F9;
            color: #0F172A;
        }

        /* Khối hiển thị khi rỗng (Empty State) */
        .empty-state {
            background: #FFFFFF;
            border: 1px dashed #CBD5E1;
            border-radius: 18px;
            padding: 64px 24px;
            text-align: center;
            grid-column: 1 / -1;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            background: #EFF6FF;
            color: #0066FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 6px;
        }

        .empty-desc {
            font-size: 14px;
            color: #64748B;
            max-width: 460px;
            margin: 0 auto 20px;
        }

        /* Phân trang Pagination */
        .pagination-container {
            margin-top: 36px;
            display: flex;
            justify-content: center;
        }

        /* Tương tác sao trong Modal chỉnh sửa */
        .interactive-stars {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 6px;
            cursor: pointer;
        }

        .interactive-stars input {
            display: none;
        }

        .interactive-stars label {
            font-size: 28px;
            color: #CBD5E1;
            cursor: pointer;
            transition: color 0.15s, transform 0.15s;
        }

        .interactive-stars label:hover,
        .interactive-stars label:hover ~ label,
        .interactive-stars input:checked ~ label {
            color: #F59E0B;
            transform: scale(1.08);
        }
    </style>
</head>
<body>

    <!-- 1. Thanh điều hướng Top Navbar đồng bộ giao diện Phần 4.3 -->
    <header class="navbar-custom">
        <a href="{{ route('home') }}" class="navbar-brand-custom">
            <div class="brand-icon">
                <i class="bi bi-compass"></i>
            </div>
            <span>Travel Planner</span>
        </a>

        <ul class="navbar-nav-custom d-none d-md-flex">
            <li class="nav-item-custom">
                <a href="{{ route('trips.index') }}">
                    <i class="bi bi-briefcase"></i>
                    <span>Chuyến đi</span>
                </a>
            </li>
            <li class="nav-item-custom">
                <a href="{{ route('favorites.index') }}">
                    <i class="bi bi-heart"></i>
                    <span>Yêu thích</span>
                </a>
            </li>
            <li class="nav-item-custom active">
                <a href="{{ route('reviews.my-reviews') }}">
                    <i class="bi bi-star"></i>
                    <span>Đánh giá của tôi</span>
                </a>
            </li>
            <li class="nav-item-custom">
                <a href="{{ route('bookings.index') }}">
                    <i class="bi bi-calendar2-check"></i>
                    <span>Đặt phòng</span>
                </a>
            </li>
        </ul>

        <div class="navbar-actions">
            @if(auth()->user()?->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn-admin">
                    <i class="bi bi-shield-lock"></i>
                    <span>Quản trị</span>
                </a>
            @endif

            <div class="user-dropdown-custom dropdown">
                <button class="btn p-0 border-0 d-flex align-items-center gap-2 dropdown-toggle text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar-custom">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="fw-semibold d-none d-sm-inline" style="font-size: 14px;">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                    <li><a class="dropdown-item py-2" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i> Hồ sơ cá nhân</a></li>
                    <li><a class="dropdown-item py-2 text-primary fw-medium" href="{{ route('reviews.my-reviews') }}"><i class="bi bi-star me-2"></i> Đánh giá của tôi</a></li>
                    <li><a class="dropdown-item py-2" href="{{ route('trips.index') }}"><i class="bi bi-briefcase me-2"></i> Chuyến đi của tôi</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Đăng xuất</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- 2. Khung nội dung chính của trang Quản lý đánh giá của User -->
    <main class="container-custom">
        <!-- Breadcrumb dẫn lối -->
        <nav class="breadcrumb-custom" aria-label="breadcrumb">
            <a href="{{ route('home') }}"><i class="bi bi-house-door me-1"></i>Trang chủ</a>
            <span>/</span>
            <span class="text-secondary">Đánh giá của tôi</span>
        </nav>

        <!-- Thông báo Flash Alert (Thành công / Lỗi) -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                <span class="fw-medium">{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i>
                <span class="fw-medium">{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Tiêu đề Header & Nút Thêm mới -->
        <div class="page-header-custom">
            <div>
                <h1 class="page-title">
                    <span>Đánh Giá Của Tôi</span>
                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2 fw-semibold">
                        {{ $stats['total'] }} đánh giá
                    </span>
                </h1>
                <p class="page-subtitle">
                    Theo dõi trạng thái kiểm duyệt, xem điểm xếp hạng và quản lý các bài nhận xét địa điểm du lịch của bạn.
                </p>
            </div>
            <div>
                <a href="{{ route('destinations.index') }}" class="btn-create-action">
                    <i class="bi bi-compass"></i>
                    <span>Khám phá & Viết đánh giá</span>
                </a>
            </div>
        </div>

        <!-- 3. Bảng 4 Thống kê tóm tắt theo trạng thái & quy tắc kiểm duyệt -->
        <div class="stats-grid">
            <!-- Thẻ Tổng số đánh giá -->
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Tổng số đánh giá</span>
                    <span class="stat-value">{{ $stats['total'] }}</span>
                    <span class="text-muted mt-1" style="font-size: 12px;">Đã viết cho các điểm đến</span>
                </div>
                <div class="stat-icon stat-icon-all">
                    <i class="bi bi-chat-square-quote"></i>
                </div>
            </div>

            <!-- Thẻ Đã được duyệt (Công khai) -->
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Đã được duyệt</span>
                    <span class="stat-value text-success">{{ $stats['approved'] }}</span>
                    <span class="text-success mt-1 fw-medium" style="font-size: 12px;"><i class="bi bi-check-circle me-1"></i>Hiển thị công khai</span>
                </div>
                <div class="stat-icon stat-icon-approved">
                    <i class="bi bi-patch-check"></i>
                </div>
            </div>

            <!-- Thẻ Đang chờ duyệt -->
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Đang chờ duyệt</span>
                    <span class="stat-value text-warning">{{ $stats['pending'] }}</span>
                    <span class="text-warning mt-1 fw-medium" style="font-size: 12px;"><i class="bi bi-clock-history me-1"></i>Ban quản trị duyệt</span>
                </div>
                <div class="stat-icon stat-icon-pending">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>

            <!-- Thẻ Đã bị ẩn -->
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Đã bị ẩn</span>
                    <span class="stat-value text-danger">{{ $stats['hidden'] }}</span>
                    <span class="text-secondary mt-1 fw-medium" style="font-size: 12px;"><i class="bi bi-eye-slash me-1"></i>Tạm ẩn do vi phạm</span>
                </div>
                <div class="stat-icon stat-icon-hidden">
                    <i class="bi bi-shield-x"></i>
                </div>
            </div>
        </div>

        <!-- 4. Thanh lọc Tabs Trạng thái và Ô Tìm kiếm theo chuẩn Section 4.3 -->
        <div class="tabs-and-search-container">
            <!-- Tabs lọc trạng thái dạng viên thuốc (Pills) -->
            <div class="status-tabs">
                @php
                    $currentStatus = $filters['status'] ?? 'all';
                @endphp
                <a href="{{ route('reviews.my-reviews', array_merge(request()->query(), ['status' => 'all', 'page' => 1])) }}"
                   class="status-pill {{ $currentStatus === 'all' || empty($currentStatus) ? 'active' : '' }}">
                    <span>Tất cả</span>
                    <span class="pill-counter">{{ $stats['total'] }}</span>
                </a>

                <a href="{{ route('reviews.my-reviews', array_merge(request()->query(), ['status' => 'approved', 'page' => 1])) }}"
                   class="status-pill {{ $currentStatus === 'approved' ? 'active' : '' }}">
                    <i class="bi bi-check-circle"></i>
                    <span>Đã duyệt</span>
                    <span class="pill-counter">{{ $stats['approved'] }}</span>
                </a>

                <a href="{{ route('reviews.my-reviews', array_merge(request()->query(), ['status' => 'pending', 'page' => 1])) }}"
                   class="status-pill {{ $currentStatus === 'pending' ? 'active' : '' }}">
                    <i class="bi bi-clock"></i>
                    <span>Chờ duyệt</span>
                    <span class="pill-counter">{{ $stats['pending'] }}</span>
                </a>

                <a href="{{ route('reviews.my-reviews', array_merge(request()->query(), ['status' => 'hidden', 'page' => 1])) }}"
                   class="status-pill {{ $currentStatus === 'hidden' ? 'active' : '' }}">
                    <i class="bi bi-eye-slash"></i>
                    <span>Đã ẩn</span>
                    <span class="pill-counter">{{ $stats['hidden'] }}</span>
                </a>
            </div>

            <!-- Thanh tìm kiếm từ khóa và lọc số sao rating -->
            <form action="{{ route('reviews.my-reviews') }}" method="GET" class="search-bar">
                <!-- Giữ nguyên tab status hiện tại nếu có -->
                <input type="hidden" name="status" value="{{ $currentStatus }}">

                <div class="search-input-group">
                    <i class="bi bi-search text-muted"></i>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tìm theo tên địa điểm du lịch hoặc nội dung nhận xét...">
                </div>

                <div class="rating-filter-group">
                    <span>Số sao:</span>
                    <select name="rating" class="rating-select">
                        <option value="">Tất cả số sao</option>
                        <option value="5" {{ ($filters['rating'] ?? '') == '5' ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5 sao)</option>
                        <option value="4" {{ ($filters['rating'] ?? '') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ (4 sao)</option>
                        <option value="3" {{ ($filters['rating'] ?? '') == '3' ? 'selected' : '' }}>⭐⭐⭐ (3 sao)</option>
                        <option value="2" {{ ($filters['rating'] ?? '') == '2' ? 'selected' : '' }}>⭐⭐ (2 sao)</option>
                        <option value="1" {{ ($filters['rating'] ?? '') == '1' ? 'selected' : '' }}>⭐ (1 sao)</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter">
                    <i class="bi bi-funnel"></i>
                    <span>Lọc</span>
                </button>

                @if(!empty($filters['search']) || !empty($filters['rating']) || ($currentStatus !== 'all' && !empty($currentStatus)))
                    <a href="{{ route('reviews.my-reviews') }}" class="btn-reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Đặt lại</span>
                    </a>
                @endif
            </form>
        </div>

        <!-- 5. Lưới hiển thị danh sách Thẻ Đánh Giá (Reviews Grid 3 Cột) -->
        <div class="reviews-grid">
            @forelse($reviews as $review)
                @php
                    $dest = $review->destination;
                    // Lấy ảnh đại diện địa điểm du lịch (hoặc ảnh mặc định danh lam thắng cảnh Việt Nam)
                    $destImage = $dest?->images?->first()?->image_path
                        ?? 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&q=80';
                    if (!str_starts_with($destImage, 'http')) {
                        $destImage = asset('storage/' . $destImage);
                    }

                    // Xác định nhãn và class CSS badge trạng thái
                    $badgeClass = 'badge-pending';
                    $badgeText = 'Đang chờ duyệt';
                    $badgeIcon = 'bi-hourglass-split';

                    if ($review->isApproved()) {
                        $badgeClass = 'badge-approved';
                        $badgeText = 'Đã được duyệt';
                        $badgeIcon = 'bi-check-circle-fill';
                    } elseif ($review->isHidden()) {
                        $badgeClass = 'badge-hidden';
                        $badgeText = 'Đã bị ẩn';
                        $badgeIcon = 'bi-eye-slash-fill';
                    }
                @endphp

                <div class="review-card" id="review-card-{{ $review->id }}">
                    <!-- Banner hình ảnh địa điểm -->
                    <div class="card-banner">
                        <!-- Badge trạng thái kiểm duyệt (Quy tắc Review) -->
                        <span class="status-badge {{ $badgeClass }}">
                            <i class="bi {{ $badgeIcon }}"></i>
                            <span>{{ $badgeText }}</span>
                        </span>

                        <!-- Nút 3 chấm mở Dropdown tùy chọn -->
                        <button type="button" class="menu-button" title="Tùy chọn" onclick="toggleMenu('menu-{{ $review->id }}', event)">
                            <i class="bi bi-three-dots"></i>
                        </button>

                        <!-- Menu dropdown hành động -->
                        <div id="menu-{{ $review->id }}" class="menu-dropdown">
                            <!-- Nút mở modal chỉnh sửa đánh giá -->
                            <button type="button" class="menu-item-btn" onclick="openEditModal({{ $review->id }}, {{ $review->rating }}, '{{ addslashes($review->comment ?? '') }}', '{{ addslashes($dest?->name ?? 'Địa điểm') }}')">
                                <i class="bi bi-pencil-square text-primary"></i>
                                <span>Chỉnh sửa đánh giá</span>
                            </button>

                            <!-- Xem chi tiết trang địa điểm -->
                            @if($dest)
                                <a href="{{ route('destinations.show', $dest->slug) }}" class="menu-item-btn">
                                    <i class="bi bi-box-arrow-up-right text-secondary"></i>
                                    <span>Xem địa điểm</span>
                                </a>
                            @endif

                            <!-- Nút mở modal xác nhận xóa đánh giá -->
                            <button type="button" class="menu-item-btn text-danger" onclick="openDeleteModal({{ $review->id }}, '{{ addslashes($dest?->name ?? 'Địa điểm') }}')">
                                <i class="bi bi-trash3 text-danger"></i>
                                <span>Xóa đánh giá</span>
                            </button>
                        </div>

                        <img src="{{ $destImage }}" alt="{{ $dest?->name ?? 'Địa điểm' }}" loading="lazy">
                    </div>

                    <!-- Thân thẻ đánh giá -->
                    <div class="card-body-custom">
                        <!-- Ngày tạo / ngày cập nhật -->
                        <div class="review-date-meta">
                            <i class="bi bi-calendar3"></i>
                            <span>Đánh giá ngày {{ $review->created_at->format('d/m/Y') }}</span>
                            @if($review->updated_at && $review->updated_at->gt($review->created_at))
                                <span class="text-muted">(Đã cập nhật {{ $review->updated_at->format('d/m/Y') }})</span>
                            @endif
                        </div>

                        <!-- Tên địa điểm du lịch -->
                        @if($dest)
                            <a href="{{ route('destinations.show', $dest->slug) }}" class="destination-name" title="{{ $dest->name }}">
                                {{ $dest->name }}
                            </a>
                        @else
                            <h3 class="destination-name">Địa điểm đã đóng hoặc không tồn tại</h3>
                        @endif

                        <!-- Dải sao đánh giá -->
                        <div class="stars-row">
                            <div class="stars-group">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->rating)
                                        <i class="bi bi-star-fill"></i>
                                    @else
                                        <i class="bi bi-star text-secondary text-opacity-25"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="rating-numeric">{{ $review->rating }}.0 / 5.0</span>
                        </div>

                        <!-- Đoạn nhận xét của người dùng -->
                        <div class="review-comment">
                            <i class="bi bi-quote me-1 text-primary text-opacity-50"></i>
                            {{ $review->comment ?: 'Người dùng không để lại lời nhận xét bằng chữ.' }}
                        </div>

                        <!-- Hộp Thông Báo Quy Tắc & Trạng Thái Kiểm Duyệt (Business Rules Explanation) -->
                        @if($review->isPending())
                            <div class="rule-info-box rule-info-pending">
                                <i class="bi bi-hourglass-split fs-6 mt-1 flex-shrink-0"></i>
                                <div>
                                    <strong>Đang chờ duyệt:</strong> Đánh giá của bạn đang được ban quản trị kiểm duyệt trước khi hiển thị công khai để đảm bảo tính xác thực và văn minh.
                                </div>
                            </div>
                        @elseif($review->isApproved())
                            <div class="rule-info-box rule-info-approved">
                                <i class="bi bi-patch-check-fill fs-6 mt-1 flex-shrink-0"></i>
                                <div>
                                    <strong>Đã hiển thị công khai:</strong> Đánh giá đã được duyệt và đang đóng góp trực tiếp vào điểm xếp hạng trung bình của địa điểm này.
                                </div>
                            </div>
                        @elseif($review->isHidden())
                            <div class="rule-info-box rule-info-hidden">
                                <i class="bi bi-exclamation-triangle-fill fs-6 mt-1 flex-shrink-0"></i>
                                <div>
                                    <strong>Đã bị tạm ẩn:</strong> Đánh giá này hiện đang bị ẩn do không đáp ứng tiêu chuẩn cộng đồng. Bạn có thể nhấn Chỉnh sửa để cập nhật lại nội dung phù hợp.
                                </div>
                            </div>
                        @endif

                        <!-- Chân thẻ: Các nút thao tác nhanh -->
                        <div class="card-footer-custom">
                            <button type="button" class="btn-card-action btn-edit-card"
                                    onclick="openEditModal({{ $review->id }}, {{ $review->rating }}, '{{ addslashes($review->comment ?? '') }}', '{{ addslashes($dest?->name ?? 'Địa điểm') }}')">
                                <i class="bi bi-pencil-square"></i>
                                <span>Chỉnh sửa</span>
                            </button>

                            @if($dest)
                                <a href="{{ route('destinations.show', $dest->slug) }}" class="btn-card-action btn-view-card">
                                    <i class="bi bi-eye"></i>
                                    <span>Chi tiết</span>
                                </a>
                            @endif

                            <button type="button" class="btn-card-action btn-delete-card"
                                    onclick="openDeleteModal({{ $review->id }}, '{{ addslashes($dest?->name ?? 'Địa điểm') }}')">
                                <i class="bi bi-trash3"></i>
                                <span>Xóa</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Khối hiển thị khi chưa có bài đánh giá nào (Empty State) -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-star"></i>
                    </div>
                    <h3 class="empty-title">Không tìm thấy bài đánh giá nào</h3>
                    <p class="empty-desc">
                        @if(!empty($filters['search']) || !empty($filters['rating']) || ($currentStatus !== 'all' && !empty($currentStatus)))
                            Không có bài đánh giá nào phù hợp với bộ lọc hiện tại. Hãy thử chọn lại trạng thái khác hoặc xóa từ khóa tìm kiếm.
                        @else
                            Bạn chưa gửi bài đánh giá hoặc nhận xét nào cho các địa điểm du lịch. Hãy khám phá và chia sẻ trải nghiệm tuyệt vời của bạn ngay nhé!
                        @endif
                    </p>
                    <a href="{{ route('destinations.index') }}" class="btn-filter" style="display: inline-flex; text-decoration: none;">
                        <i class="bi bi-compass"></i>
                        <span>Khám phá địa điểm du lịch</span>
                    </a>
                </div>
            @endforelse
        </div>

        <!-- 6. Phân trang Pagination -->
        @if($reviews->hasPages())
            <div class="pagination-container">
                {{ $reviews->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </main>

    <!-- 7. Modal Chỉnh Sửa Đánh Giá (Bootstrap 5 Modal) -->
    <div class="modal fade" id="editReviewModal" tabindex="-1" aria-labelledby="editReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <form id="editReviewForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <!-- Tham số báo hiệu quay lại đúng trang My Reviews sau khi sửa -->
                    <input type="hidden" name="redirect_to" value="my-reviews">

                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <div>
                            <h5 class="modal-title fw-bold text-dark" id="editReviewModalLabel">
                                <i class="bi bi-pencil-square text-primary me-2"></i>Chỉnh Sửa Đánh Giá
                            </h5>
                            <p class="text-muted small mb-0 mt-1" id="editModalDestName">Địa điểm du lịch</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body px-4 py-3">
                        <!-- Chọn số sao đánh giá (1 đến 5 sao) -->
                        <div class="mb-3 text-center p-3 bg-light rounded-3">
                            <label class="form-label fw-bold d-block mb-2 text-dark">
                                Mức độ hài lòng của bạn <span class="text-danger">*</span>
                            </label>

                            <!-- Khối sao tương tác trực quan -->
                            <div class="interactive-stars">
                                <input type="radio" id="modal-star5" name="rating" value="5" required>
                                <label for="modal-star5" title="5 sao - Tuyệt vời"><i class="bi bi-star-fill"></i></label>

                                <input type="radio" id="modal-star4" name="rating" value="4">
                                <label for="modal-star4" title="4 sao - Rất tốt"><i class="bi bi-star-fill"></i></label>

                                <input type="radio" id="modal-star3" name="rating" value="3">
                                <label for="modal-star3" title="3 sao - Bình thường"><i class="bi bi-star-fill"></i></label>

                                <input type="radio" id="modal-star2" name="rating" value="2">
                                <label for="modal-star2" title="2 sao - Tệ"><i class="bi bi-star-fill"></i></label>

                                <input type="radio" id="modal-star1" name="rating" value="1">
                                <label for="modal-star1" title="1 sao - Rất tệ"><i class="bi bi-star-fill"></i></label>
                            </div>
                            <div id="modalRatingText" class="small fw-semibold text-primary mt-2">5 sao - Tuyệt vời</div>
                        </div>

                        <!-- Ô nhập nhận xét -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="modalComment" class="form-label fw-semibold text-dark mb-0">
                                    Nội dung nhận xét:
                                </label>
                                <span class="text-muted small" id="modalCharCounter">0 / 500 ký tự</span>
                            </div>
                            <textarea name="comment" id="modalComment" class="form-control rounded-3" rows="4"
                                      maxlength="500" placeholder="Chia sẻ chi tiết trải nghiệm thực tế, cảnh quan, ẩm thực hoặc dịch vụ tại địa điểm này..."></textarea>
                        </div>

                        <!-- Hộp ghi chú quy tắc kiểm duyệt nội dung -->
                        <div class="alert alert-warning border-0 rounded-3 p-3 mb-0" style="font-size: 13px;">
                            <i class="bi bi-shield-exclamation me-1 fw-bold"></i>
                            <strong>Quy tắc kiểm duyệt:</strong> Khi bạn cập nhật nội dung, bài đánh giá sẽ được đưa về trạng thái <strong>Chờ duyệt</strong> để ban quản trị kiểm tra lại trước khi hiển thị công khai.
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-medium" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" style="background-color: #0066FF; border-color: #0066FF;">
                            <i class="bi bi-save me-1"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 8. Modal Xác Nhận Xóa Đánh Giá (Bootstrap 5 Modal) -->
    <div class="modal fade" id="deleteReviewModal" tabindex="-1" aria-labelledby="deleteReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-4 border-0 shadow-lg text-center p-3">
                <form id="deleteReviewForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <!-- Tham số quay lại đúng trang My Reviews sau khi xóa -->
                    <input type="hidden" name="redirect_to" value="my-reviews">

                    <div class="modal-body py-3">
                        <div class="w-16 h-16 rounded-circle bg-danger bg-opacity-10 text-danger mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; font-size: 26px;">
                            <i class="bi bi-trash3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Xóa bài đánh giá?</h5>
                        <p class="text-muted small mb-0">
                            Bạn có chắc chắn muốn xóa bài đánh giá cho <strong id="deleteDestName">Địa điểm</strong>? Hành động này không thể hoàn tác và hệ thống sẽ tự động tính toán lại điểm rating trung bình.
                        </p>
                    </div>

                    <div class="d-flex justify-content-center gap-2 pt-2">
                        <button type="button" class="btn btn-light rounded-pill px-3 fw-medium" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-3 fw-semibold">
                            <i class="bi bi-trash me-1"></i>Xóa vĩnh viễn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle menu 3 chấm trên từng thẻ bài đánh giá
        function toggleMenu(menuId, event) {
            event.stopPropagation();
            const targetMenu = document.getElementById(menuId);
            const allMenus = document.querySelectorAll('.menu-dropdown');

            allMenus.forEach(menu => {
                if (menu.id !== menuId) {
                    menu.classList.remove('show');
                }
            });

            if (targetMenu) {
                targetMenu.classList.toggle('show');
            }
        }

        // Đóng dropdown menu khi click ra ngoài vùng thẻ
        document.addEventListener('click', function () {
            document.querySelectorAll('.menu-dropdown').forEach(menu => {
                menu.classList.remove('show');
            });
        });

        // Xử lý mở Modal Chỉnh Sửa Đánh Giá
        function openEditModal(reviewId, currentRating, currentComment, destName) {
            const form = document.getElementById('editReviewForm');
            form.action = `/reviews/${reviewId}`;

            document.getElementById('editModalDestName').innerText = destName;
            document.getElementById('modalComment').value = currentComment;

            // Đặt radio star tương ứng
            const starRadio = document.querySelector(`input[name="rating"][value="${currentRating}"]`);
            if (starRadio) {
                starRadio.checked = true;
            }
            updateStarText(currentRating);
            updateCharCount();

            const modal = new bootstrap.Modal(document.getElementById('editReviewModal'));
            modal.show();
        }

        // Cập nhật text mô tả số sao trong modal chỉnh sửa
        const ratingTexts = {
            1: '1 sao - Rất tệ',
            2: '2 sao - Tệ',
            3: '3 sao - Bình thường',
            4: '4 sao - Hài lòng',
            5: '5 sao - Tuyệt vời'
        };

        function updateStarText(val) {
            const textEl = document.getElementById('modalRatingText');
            if (textEl && ratingTexts[val]) {
                textEl.innerText = ratingTexts[val];
            }
        }

        document.querySelectorAll('.interactive-stars input').forEach(input => {
            input.addEventListener('change', function () {
                updateStarText(this.value);
            });
        });

        // Bộ đếm ký tự realtime trong ô textarea modal
        const commentTextarea = document.getElementById('modalComment');
        const counterEl = document.getElementById('modalCharCounter');

        function updateCharCount() {
            if (commentTextarea && counterEl) {
                const len = commentTextarea.value.length;
                counterEl.innerText = `${len} / 500 ký tự`;
            }
        }

        if (commentTextarea) {
            commentTextarea.addEventListener('input', updateCharCount);
        }

        // Xử lý mở Modal Xác Nhận Xóa Đánh Giá
        function openDeleteModal(reviewId, destName) {
            const form = document.getElementById('deleteReviewForm');
            form.action = `/reviews/${reviewId}`;
            document.getElementById('deleteDestName').innerText = destName;

            const modal = new bootstrap.Modal(document.getElementById('deleteReviewModal'));
            modal.show();
        }
    </script>
</body>
</html>
