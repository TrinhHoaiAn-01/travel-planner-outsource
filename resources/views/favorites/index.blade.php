<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bộ Sưu Tập Yêu Thích Của Bạn - Travel Planner</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary: #0066FF;
            --primary-dark: #0052CC;
            --primary-light: #EBF5FF;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --bg-light: #F8FAFC;
            --white: #FFFFFF;
            --border-color: #E2E8F0;
            --danger: #EF4444;
            --warning: #F59E0B;
            --warning-light: #FEF3C7;
            --success: #10B981;
            --radius-pill: 9999px;
            --radius-card: 20px;
            --radius-img: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --shadow-card: 0 4px 16px rgba(0,0,0,0.04);
            --shadow-hover: 0 12px 28px rgba(0,0,0,0.08);
            --transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #F8FAFC;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        /* 1. Header & Navigation */
        .header-nav {
            background-color: var(--white);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 800;
            font-size: 19px;
            letter-spacing: -0.5px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 19px;
            box-shadow: 0 4px 10px rgba(0, 102, 255, 0.25);
        }

        .main-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }

        .menu-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .menu-link:hover {
            color: var(--primary);
            background-color: #F1F5F9;
        }

        .menu-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
        }

        .menu-link.active i {
            color: var(--primary);
        }

        .user-nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-admin-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: var(--radius-pill);
            border: 1px solid #CBD5E1;
            background: var(--white);
            color: var(--text-dark);
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-admin-pill:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .user-profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-pill-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 12px 4px 4px;
            background: #F1F5F9;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-pill);
            cursor: pointer;
            transition: var(--transition);
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-pill-btn:hover {
            background: #E2E8F0;
        }

        .user-avatar-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .dropdown-menu-custom {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            background: var(--white);
            min-width: 200px;
            border-radius: 14px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 6px;
            z-index: 1000;
        }

        .dropdown-menu-custom.show {
            display: block;
        }

        .dropdown-item-custom {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 8px;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            width: 100%;
            background: transparent;
            text-align: left;
            cursor: pointer;
        }

        .dropdown-item-custom:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        /* 2. Page Content */
        .main-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 24px 60px;
            width: 100%;
            flex: 1;
        }

        /* Breadcrumb */
        .breadcrumb-bar {
            margin-bottom: 12px;
            font-size: 14px;
        }

        .breadcrumb-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .breadcrumb-link:hover {
            text-decoration: underline;
        }

        .breadcrumb-sep {
            margin: 0 6px;
            color: var(--text-muted);
        }

        .breadcrumb-current {
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Header Area */
        .favorites-header-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 28px;
        }

        .page-heading-title {
            font-size: 30px;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .page-heading-subtitle {
            font-size: 14.5px;
            color: var(--text-muted);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-demo-empty {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: var(--radius-pill);
            border: 1px solid #94A3B8;
            background: var(--white);
            color: #475569;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-demo-empty:hover {
            background-color: #F1F5F9;
            border-color: #64748B;
            color: var(--text-dark);
        }

        .btn-primary-blue {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: var(--radius-pill);
            background: var(--primary);
            color: var(--white);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.25);
            transition: var(--transition);
            border: none;
        }

        .btn-primary-blue:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 102, 255, 0.35);
        }

        /* 3. Favorites Grid */
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        @media (max-width: 992px) {
            .favorites-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .favorites-grid {
                grid-template-columns: 1fr;
            }
        }

        .fav-card {
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .fav-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: #CBD5E1;
        }

        .fav-card-image-box {
            position: relative;
            height: 200px;
            width: 100%;
            overflow: hidden;
            background: #E2E8F0;
        }

        .fav-card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .fav-card:hover .fav-card-img {
            transform: scale(1.05);
        }

        /* Category Badge (Pill with soft color matching Image 2) */
        .card-category-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 5px 12px;
            border-radius: var(--radius-pill);
            font-size: 12.5px;
            font-weight: 700;
            z-index: 2;
            backdrop-filter: blur(8px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .badge-style-resort {
            background-color: #EBF5FF;
            color: #0066FF;
        }

        .badge-style-island {
            background-color: #E6FBF2;
            color: #059669;
        }

        .badge-style-nature {
            background-color: #FEF3C7;
            color: #D97706;
        }

        .badge-style-default {
            background-color: #F1F5F9;
            color: #475569;
        }

        /* Circular Remove Button with Red Trash Icon */
        .btn-trash-remove {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--white);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            transition: var(--transition);
            z-index: 2;
            color: #EF4444;
        }

        .btn-trash-remove:hover {
            background: #FEE2E2;
            transform: scale(1.12);
        }

        .btn-trash-remove svg {
            width: 18px;
            height: 18px;
            stroke: #EF4444;
        }

        /* Card Content Body */
        .fav-card-body {
            padding: 16px 20px 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .card-meta-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .card-city-name {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13.5px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .card-city-name i {
            color: #EF4444;
            font-size: 14px;
        }

        .card-rating-badge {
            background-color: #F59E0B;
            color: #0F172A;
            font-size: 12.5px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .card-destination-title {
            font-size: 17.5px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
            line-height: 1.35;
        }

        .card-destination-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 18px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 40px;
        }

        /* Card Footer */
        .card-action-footer {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid var(--border-color);
        }

        .card-price-text {
            font-size: 17.5px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.3px;
        }

        .card-buttons-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-pill-trip {
            padding: 6px 14px;
            border-radius: var(--radius-pill);
            border: 1.5px solid var(--primary);
            background: transparent;
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-pill-trip:hover {
            background-color: var(--primary-light);
        }

        .btn-pill-detail {
            padding: 6px 16px;
            border-radius: var(--radius-pill);
            background: var(--primary);
            color: var(--white);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
            border: none;
        }

        .btn-pill-detail:hover {
            background: var(--primary-dark);
        }

        /* 4. Empty State */
        .empty-favorites-box {
            background: var(--white);
            border: 2px dashed #CBD5E1;
            border-radius: var(--radius-card);
            padding: 60px 24px;
            text-align: center;
            max-width: 600px;
            margin: 20px auto;
        }

        .empty-heart-icon-wrapper {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #FEE2E2;
            color: #EF4444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 20px;
        }

        .empty-box-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .empty-box-desc {
            font-size: 14.5px;
            color: var(--text-muted);
            margin-bottom: 24px;
            line-height: 1.6;
        }

        /* 5. Modal Thêm vào Trip */
        .custom-modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1050;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .custom-modal-backdrop.show {
            display: flex;
        }

        .custom-modal-dialog {
            background: var(--white);
            border-radius: 20px;
            max-width: 480px;
            width: 100%;
            padding: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            position: relative;
            animation: modalFadeIn 0.25s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title-text {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-close-modal {
            background: transparent;
            border: none;
            font-size: 20px;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
        }

        .form-label-custom {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
            display: block;
        }

        .form-control-custom, .form-select-custom {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            font-size: 14px;
            outline: none;
            transition: var(--transition);
            margin-bottom: 14px;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }

        .modal-footer-line {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
        }

        /* Toast Message */
        .toast-container-custom {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-item {
            background: var(--white);
            color: var(--text-dark);
            padding: 12px 18px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            border-left: 4px solid var(--primary);
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s ease;
        }

        .toast-item.toast-success { border-left-color: var(--success); }
        .toast-item.toast-info { border-left-color: var(--primary); }
        .toast-item.toast-danger { border-left-color: var(--danger); }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .fade-card-out {
            opacity: 0 !important;
            transform: scale(0.9) !important;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
    </style>
</head>
<body>

    <!-- 1. Header Navigation (Khớp 100% Ảnh 2) -->
    <header class="header-nav">
        <div class="nav-container">
            <a href="{{ route('home') }}" class="brand-logo">
                <span class="brand-icon"><i class="bi bi-compass-fill"></i></span>
                <span>TRAVEL PLANNER</span>
            </a>

            <ul class="main-menu">
                <li><a href="{{ route('home') }}" class="menu-link"><i class="bi bi-house"></i> Trang chủ</a></li>
                <li><a href="{{ route('home') }}#destinations" class="menu-link"><i class="bi bi-geo-alt"></i> Địa điểm</a></li>
                <li><a href="{{ route('trips.index') }}" class="menu-link"><i class="bi bi-map"></i> Chuyến đi</a></li>
                <li><a href="{{ route('favorites.index') }}" class="menu-link active"><i class="bi bi-suit-heart-fill"></i> Yêu thích</a></li>
                <li><a href="{{ route('trips.index') }}" class="menu-link"><i class="bi bi-calendar-event"></i> Booking</a></li>
            </ul>

            <div class="user-nav-actions">
                @if(auth()->check() && auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-admin-pill">
                        <i class="bi bi-shield-check"></i> Admin
                    </a>
                @endif

                @auth
                    <div class="user-profile-dropdown">
                        <button type="button" class="user-pill-btn" onclick="toggleUserDropdown(event)">
                            <img src="{{ auth()->user()->avatar ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100' }}" 
                                 alt="{{ auth()->user()->name }}" class="user-avatar-img">
                            <span>{{ auth()->user()->name }}</span>
                            <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
                        </button>
                        <div class="dropdown-menu-custom" id="userDropdownMenu">
                            <a href="{{ route('trips.index') }}" class="dropdown-item-custom">
                                <i class="bi bi-briefcase"></i> Chuyến đi của tôi
                            </a>
                            <a href="{{ route('favorites.index') }}" class="dropdown-item-custom">
                                <i class="bi bi-heart"></i> Danh sách yêu thích
                            </a>
                            <hr style="margin: 6px 0; border: none; border-top: 1px solid var(--border-color);">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item-custom text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-admin-pill">Đăng nhập</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- 2. Main Container -->
    <main class="main-wrapper">
        <!-- Breadcrumb -->
        <div class="breadcrumb-bar">
            <a href="{{ route('home') }}" class="breadcrumb-link">Trang chủ</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">Địa điểm yêu thích</span>
        </div>

        <!-- Heading Row -->
        <div class="favorites-header-row">
            <div>
                <h1 class="page-heading-title">Bộ Sưu Tập Yêu Thích Của Bạn</h1>
                <p class="page-heading-subtitle">Lưu lại những điểm đến bạn mong muốn khám phá trong các chuyến đi tới</p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn-demo-empty" id="toggleDemoEmptyBtn" onclick="toggleDemoEmptyState()">
                    <i class="bi bi-eye"></i> Xem trạng thái Trống (Demo)
                </button>
                <a href="{{ route('home') }}#destinations" class="btn-primary-blue">
                    <i class="bi bi-plus-lg"></i> Khám phá thêm địa điểm
                </a>
            </div>
        </div>

        <!-- 3. Grid Danh Sách Yêu Thích (Dữ liệu thật 100% từ Database) -->
        <div id="favoritesGridContainer" class="favorites-grid" style="{{ $favorites->count() === 0 ? 'display:none;' : '' }}">
            @foreach($favorites as $fav)
                @php 
                    $dest = $fav->destination;
                    if (!$dest) continue;
                    
                    // Xác định style badge theo danh mục
                    $catName = $dest->category->name ?? 'Du lịch';
                    $badgeStyle = 'badge-style-resort';
                    if (str_contains(mb_strtolower($catName), 'biển') || str_contains(mb_strtolower($catName), 'đảo')) {
                        $badgeStyle = 'badge-style-island';
                    } elseif (str_contains(mb_strtolower($catName), 'thiên nhiên') || str_contains(mb_strtolower($catName), 'núi')) {
                        $badgeStyle = 'badge-style-nature';
                    }
                    
                    // Ảnh đại diện thật của địa điểm
                    $imageUrl = $dest->primaryImage->image_path ?? 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800';
                @endphp
                <div class="fav-card" id="fav-card-{{ $dest->id }}" data-destination-id="{{ $dest->id }}">
                    <!-- Card Image -->
                    <div class="fav-card-image-box">
                        <img src="{{ $imageUrl }}" alt="{{ $dest->name }}" class="fav-card-img" loading="lazy">
                        <span class="card-category-badge {{ $badgeStyle }}">{{ $catName }}</span>
                        <!-- Nút Xóa Khỏi Yêu Thích (Thùng Rác Đỏ Trong Vòng Tròn Trắng - Khớp 100% Ảnh 2) -->
                        <button type="button" class="btn-trash-remove" 
                                onclick="removeFavoriteAjax({{ $dest->id }}, event)"
                                title="Xóa khỏi danh sách yêu thích">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Card Body -->
                    <div class="fav-card-body">
                        <div class="card-meta-line">
                            <span class="card-city-name">
                                <i class="bi bi-geo-alt-fill"></i>
                                {{ $dest->city->name ?? 'Việt Nam' }}
                            </span>
                            <span class="card-rating-badge">
                                ★ {{ number_format($dest->rating ?? 5.0, 1) }}
                            </span>
                        </div>

                        <h3 class="card-destination-title">{{ $dest->name }}</h3>
                        <p class="card-destination-desc">{{ $dest->description ?? 'Điểm đến du lịch hấp dẫn không thể bỏ lỡ trong hành trình khám phá.' }}</p>

                        <!-- Card Footer -->
                        <div class="card-action-footer">
                            <div class="card-price-text">
                                {{ $dest->entrance_fee > 0 ? number_format($dest->entrance_fee, 0, ',', '.') . '₫' : 'Miễn phí' }}
                            </div>
                            <div class="card-buttons-group">
                                <button type="button" class="btn-pill-trip" 
                                        onclick="openAddToTripModal({{ $dest->id }}, '{{ addslashes($dest->name) }}')">
                                    + Trip
                                </button>
                                <a href="{{ route('home') }}#destinations" class="btn-pill-detail">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 4. Empty State (Khi danh sách trống) -->
        <div id="favoritesEmptyState" class="empty-favorites-box" style="{{ $favorites->count() > 0 ? 'display:none;' : '' }}">
            <div class="empty-heart-icon-wrapper">
                <i class="bi bi-heart"></i>
            </div>
            <h2 class="empty-box-title">Chưa có địa điểm yêu thích nào</h2>
            <p class="empty-box-desc">
                Bạn chưa lưu điểm đến nào vào bộ sưu tập. Hãy khám phá các địa danh hấp dẫn và bấm vào biểu tượng trái tim để lên kế hoạch cho chuyến đi của bạn!
            </p>
            <a href="{{ route('home') }}#destinations" class="btn-primary-blue">
                <i class="bi bi-compass"></i> Khám phá địa điểm ngay
            </a>
        </div>
    </main>

    <!-- 5. Modal Thêm vào Trip (FR15) -->
    <div class="custom-modal-backdrop" id="addToTripModal" onclick="closeAddToTripModal(event)">
        <div class="custom-modal-dialog" onclick="event.stopPropagation()">
            <div class="modal-header-line">
                <div class="modal-title-text">
                    <i class="bi bi-calendar-plus text-primary"></i>
                    <span>Thêm vào chuyến đi</span>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeAddToTripModal()">&times;</button>
            </div>

            <form id="addToTripForm" onsubmit="submitAddToTrip(event)">
                <input type="hidden" id="modalDestinationId" name="destination_id" value="">
                
                <div style="margin-bottom: 14px;">
                    <span class="form-label-custom">Điểm đến đã chọn:</span>
                    <div id="modalDestinationName" style="font-weight: 700; color: var(--primary); font-size: 15px;"></div>
                </div>

                @if(isset($userTrips) && $userTrips->count() > 0)
                    <div style="margin-bottom: 14px;">
                        <label for="selectTripId" class="form-label-custom">Chọn chuyến đi của bạn:</label>
                        <select id="selectTripId" name="trip_id" class="form-select-custom" required>
                            @foreach($userTrips as $trip)
                                <option value="{{ $trip->id }}">
                                    {{ $trip->name }} ({{ $trip->start_date ? $trip->start_date->format('d/m/Y') : 'Chưa định ngày' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label for="inputDayNumber" class="form-label-custom">Chọn ngày ghé thăm:</label>
                        <input type="number" id="inputDayNumber" name="day_number" class="form-control-custom" value="1" min="1" required>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label for="inputTripNote" class="form-label-custom">Ghi chú hoạt động (tùy chọn):</label>
                        <textarea id="inputTripNote" name="note" class="form-control-custom" rows="3" placeholder="Ví dụ: Tham quan buổi sáng, chụp ảnh kỷ niệm..."></textarea>
                    </div>

                    <div class="modal-footer-line">
                        <button type="button" class="btn-demo-empty" onclick="closeAddToTripModal()">Hủy</button>
                        <button type="submit" class="btn-primary-blue">
                            <i class="bi bi-check2"></i> Xác nhận thêm
                        </button>
                    </div>
                @else
                    <div style="text-align: center; padding: 20px 0;">
                        <i class="bi bi-map text-muted" style="font-size: 40px; display: block; margin-bottom: 10px;"></i>
                        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 16px;">
                            Bạn hiện chưa có chuyến đi nào để thêm điểm đến này.
                        </p>
                        <a href="{{ route('trips.create') }}" class="btn-primary-blue" style="display: inline-flex;">
                            <i class="bi bi-plus-lg"></i> Tạo chuyến đi mới ngay
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Toast Notifications Container -->
    <div class="toast-container-custom" id="toastContainer"></div>

    <!-- JavaScript Xử Lý AJAX Không Load Lại Trang -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Hiển thị thông báo Toast
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast-item toast-${type}`;
            
            let icon = '<i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 18px;"></i>';
            if (type === 'danger') icon = '<i class="bi bi-exclamation-circle-fill" style="color: var(--danger); font-size: 18px;"></i>';
            if (type === 'info') icon = '<i class="bi bi-info-circle-fill" style="color: var(--primary); font-size: 18px;"></i>';

            toast.innerHTML = `${icon} <span>${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Dropdown người dùng
        function toggleUserDropdown(event) {
            event.stopPropagation();
            const menu = document.getElementById('userDropdownMenu');
            menu.classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('userDropdownMenu');
            if (menu && menu.classList.contains('show')) {
                menu.classList.remove('show');
            }
        });

        // Xóa địa điểm khỏi danh sách yêu thích bằng AJAX (Không load lại trang)
        function removeFavoriteAjax(destinationId, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const card = document.getElementById(`fav-card-${destinationId}`);
            if (!card) return;

            // Hiệu ứng mờ dần và thu nhỏ trước khi xóa
            card.classList.add('fade-card-out');

            fetch(`/favorites/toggle/${destinationId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (res.status === 401) {
                    window.location.href = "{{ route('login') }}";
                    return;
                }
                return res.json();
            })
            .then(data => {
                if (data && data.success) {
                    showToast(data.message || 'Đã xóa địa điểm khỏi danh sách yêu thích!', 'info');
                    
                    setTimeout(() => {
                        card.remove();
                        // Kiểm tra nếu không còn thẻ nào thì hiện Empty State
                        const grid = document.getElementById('favoritesGridContainer');
                        const remainingCards = grid.querySelectorAll('.fav-card');
                        if (remainingCards.length === 0) {
                            grid.style.display = 'none';
                            document.getElementById('favoritesEmptyState').style.display = 'block';
                        }
                    }, 350);
                } else {
                    card.classList.remove('fade-card-out');
                    showToast('Có lỗi xảy ra, vui lòng thử lại.', 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                card.classList.remove('fade-card-out');
                showToast('Không thể kết nối máy chủ.', 'danger');
            });
        }

        // Xem trạng thái Trống (Demo)
        let isDemoEmptyActive = false;
        function toggleDemoEmptyState() {
            const grid = document.getElementById('favoritesGridContainer');
            const emptyBox = document.getElementById('favoritesEmptyState');
            const btn = document.getElementById('toggleDemoEmptyBtn');

            isDemoEmptyActive = !isDemoEmptyActive;

            if (isDemoEmptyActive) {
                grid.style.display = 'none';
                emptyBox.style.display = 'block';
                btn.innerHTML = '<i class="bi bi-grid-3x3-gap"></i> Xem lại danh sách';
                btn.style.borderColor = 'var(--primary)';
                btn.style.color = 'var(--primary)';
            } else {
                const remainingCards = grid.querySelectorAll('.fav-card');
                if (remainingCards.length > 0) {
                    grid.style.display = 'grid';
                    emptyBox.style.display = 'none';
                } else {
                    emptyBox.style.display = 'block';
                }
                btn.innerHTML = '<i class="bi bi-eye"></i> Xem trạng thái Trống (Demo)';
                btn.style.borderColor = '#94A3B8';
                btn.style.color = '#475569';
            }
        }

        // Modal Thêm vào Trip
        function openAddToTripModal(destinationId, destinationName) {
            document.getElementById('modalDestinationId').value = destinationId;
            document.getElementById('modalDestinationName').textContent = destinationName;
            document.getElementById('addToTripModal').classList.add('show');
        }

        function closeAddToTripModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('addToTripModal').classList.remove('show');
        }

        function submitAddToTrip(event) {
            event.preventDefault();
            const form = document.getElementById('addToTripForm');
            const tripId = document.getElementById('selectTripId')?.value;
            const destinationId = document.getElementById('modalDestinationId').value;
            const dayNumber = document.getElementById('inputDayNumber')?.value || 1;
            const note = document.getElementById('inputTripNote')?.value || '';

            if (!tripId) {
                showToast('Vui lòng chọn chuyến đi.', 'danger');
                return;
            }

            fetch('{{ route("favorites.add-to-trip") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    trip_id: tripId,
                    destination_id: destinationId,
                    day_number: dayNumber,
                    note: note
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    closeAddToTripModal();
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Không thể thêm vào chuyến đi.', 'danger');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Lỗi máy chủ khi thêm vào chuyến đi.', 'danger');
            });
        }
    </script>
</body>
</html>
