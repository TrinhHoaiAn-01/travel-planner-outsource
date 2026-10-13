<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bộ Sưu Tập Yêu Thích Của Bạn - Travel Planner</title>
    <!-- Google Fonts Inter & Font Awesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #0066FF;
            --primary-dark: #0052CC;
            --primary-light: #EBF5FF;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --bg-page: #F8FAFC;
            --white: #FFFFFF;
            --border-color: #E2E8F0;
            --danger-bg: #FEE2E2;
            --danger: #EF4444;
            --warning-bg: #FEF08A;
            --warning-text: #854D0E;
            --radius-card: 16px;
            --radius-pill: 20px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* 1. Navbar Header */
        .navbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid #F1F5F9;
            padding: 12px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 800;
            font-size: 19px;
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

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: var(--radius-pill);
            transition: all 0.2s;
        }

        .nav-link.active {
            background-color: #EBF5FF;
            color: #0066FF;
            font-weight: 600;
        }

        .nav-link:hover:not(.active) {
            color: #0066FF;
            background-color: #F8FAFC;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .btn-admin {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: var(--radius-pill);
            border: 1px solid #94A3B8;
            background: #FFFFFF;
            color: #0F172A;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-admin:hover {
            background: #F1F5F9;
            border-color: #64748B;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: var(--radius-pill);
        }

        .user-avatar-img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            background: #E2E8F0;
        }

        /* 2. Main Page Container */
        .main-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 24px 20px 60px;
            width: 100%;
            flex: 1;
        }

        /* Breadcrumb */
        .breadcrumb-box {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .breadcrumb-box a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-box a:hover {
            text-decoration: underline;
        }

        .breadcrumb-box span {
            color: var(--text-muted);
        }

        /* Page Header Row */
        .page-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 28px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .header-title-box h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .header-title-box p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }


        .btn-explore-more {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: var(--radius-pill);
            background: var(--primary);
            color: #FFFFFF;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 2px 6px rgba(0, 102, 255, 0.25);
        }

        .btn-explore-more:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* Floating Toast Container */
        .fav-toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .fav-toast {
            pointer-events: auto;
            background: #0F172A;
            color: #FFFFFF;
            padding: 12px 22px;
            border-radius: 30px;
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            animation: favToastSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        @keyframes favToastSlideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .fav-toast.hide {
            opacity: 0;
            transform: translateY(10px) scale(0.95);
        }

        /* 3. Cards Grid (4 Columns) */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        @media (max-width: 1180px) {
            .cards-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 860px) {
            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
        }

        @media (max-width: 560px) {
            .cards-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .navbar {
                padding: 12px 16px;
            }
        }

        /* Card Styles */
        .dest-card {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.25s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .dest-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.1);
        }

        /* Image Wrapper */
        .card-img-wrap {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: #E2E8F0;
        }

        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }

        .dest-card:hover .card-img {
            transform: scale(1.04);
        }

        /* Category Badge (Top Left) */
        .badge-cat-tag {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            font-size: 11.5px;
            font-weight: 700;
            z-index: 2;
            letter-spacing: 0.2px;
        }

        .badge-cat-blue {
            background: #E0F2FE;
            color: #0284C7;
        }

        .badge-cat-green {
            background: #DCFCE7;
            color: #15803D;
        }

        .badge-cat-yellow {
            background: #FEF3C7;
            color: #B45309;
        }

        /* Delete Trash Button (Top Right) */
        .btn-delete-fav {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #FEE2E2;
            color: #EF4444;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            font-size: 13px;
        }

        .btn-delete-fav:hover {
            background: #EF4444;
            color: #FFFFFF;
            transform: scale(1.1);
        }

        /* Card Content */
        .card-body {
            padding: 16px 18px 18px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .card-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .location-text {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .location-icon-pin {
            color: #EF4444;
            font-size: 13px;
        }

        .rating-badge-pill {
            background: var(--warning-bg);
            color: var(--warning-text);
            font-size: 12px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: var(--radius-pill);
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .card-name-title {
            font-size: 16.5px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 6px;
            line-height: 1.35;
        }

        .card-desc-clamp {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.45;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 14px;
            flex: 1;
        }

        /* Card Footer Box */
        .card-footer-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 14px;
            border-top: 1px solid #F1F5F9;
            margin-top: auto;
        }

        .price-text-blue {
            font-size: 16px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.3px;
        }

        .card-btns-right {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-add-trip-pill {
            padding: 5px 12px;
            border-radius: var(--radius-pill);
            border: 1px solid #BAE6FD;
            background: #EBF5FF;
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-add-trip-pill:hover {
            background: #DBEAFE;
            border-color: #93C5FD;
        }

        .btn-detail-pill {
            padding: 5px 13px;
            border-radius: var(--radius-pill);
            background: var(--primary);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-detail-pill:hover {
            background: var(--primary-dark);
        }

        /* 4. Empty State Box */
        .empty-state-box {
            background: #FFFFFF;
            border: 1px dashed var(--border-color);
            border-radius: var(--radius-card);
            padding: 64px 20px;
            text-align: center;
            max-width: 600px;
            margin: 20px auto;
        }

        .empty-icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #FEE2E2;
            color: #EF4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 18px;
        }

        .empty-title-main {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .empty-desc-sub {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        /* 5. Modal Thêm vào Trip */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-backdrop.active {
            display: flex;
        }

        .modal-window {
            background: #FFFFFF;
            border-radius: 20px;
            max-width: 500px;
            width: 100%;
            padding: 24px 28px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-main);
        }

        .btn-modal-close {
            background: none;
            border: none;
            font-size: 20px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .modal-form-group {
            margin-bottom: 16px;
        }

        .modal-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .modal-input, .modal-select, .modal-textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-main);
            outline: none;
            transition: border-color 0.2s;
        }

        .modal-input:focus, .modal-select:focus, .modal-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
        }

        .modal-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .modal-footer-btns {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-modal-cancel {
            padding: 9px 18px;
            border-radius: var(--radius-pill);
            border: 1px solid var(--border-color);
            background: #FFFFFF;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 13.5px;
            cursor: pointer;
        }

        .btn-modal-submit {
            padding: 9px 22px;
            border-radius: var(--radius-pill);
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            font-weight: 600;
            font-size: 13.5px;
            cursor: pointer;
        }

        /* Custom Confirm Delete Modal */
        .confirm-modal-window {
            max-width: 420px;
            text-align: center;
            padding: 32px 28px 24px;
        }

        .confirm-icon-box {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #FEE2E2;
            color: #EF4444;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 16px;
        }

        .confirm-title {
            font-size: 19px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .confirm-desc {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .confirm-btn-row {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-confirm-cancel {
            flex: 1;
            padding: 10px 18px;
            border-radius: var(--radius-pill);
            border: 1px solid var(--border-color);
            background: #FFFFFF;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-confirm-cancel:hover {
            background: #F1F5F9;
            color: var(--text-main);
        }

        .btn-confirm-delete {
            flex: 1;
            padding: 10px 18px;
            border-radius: var(--radius-pill);
            border: none;
            background: #EF4444;
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }

        .btn-confirm-delete:hover {
            background: #DC2626;
            transform: translateY(-1px);
        }

        /* 6. Footer (Dark Theme Matching Image) */
        .footer-dark {
            background-color: #0B1329;
            color: #94A3B8;
            padding: 48px 36px 24px;
            margin-top: 60px;
        }

        .footer-grid {
            max-width: 1240px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 36px;
            padding-bottom: 36px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        @media (max-width: 860px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 520px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
        }

        .footer-brand-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 800;
            color: #FFFFFF;
            margin-bottom: 12px;
        }

        .footer-desc {
            font-size: 13.5px;
            line-height: 1.6;
            color: #64748B;
            max-width: 340px;
        }

        .footer-col h4 {
            font-size: 14.5px;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 16px;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-col ul a {
            text-decoration: none;
            color: #94A3B8;
            font-size: 13.5px;
            transition: color 0.2s;
        }

        .footer-col ul a:hover {
            color: #FFFFFF;
        }

        .footer-contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #94A3B8;
            margin-bottom: 10px;
        }

        .footer-bottom-copy {
            max-width: 1240px;
            margin: 20px auto 0;
            text-align: center;
            font-size: 12.5px;
            color: #64748B;
        }
    </style>
</head>
<body>

    <!-- 1. Navigation Header -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-compass"></i>
            </div>
            TRAVEL PLANNER
        </a>

        <ul class="navbar-nav">
            <li>
                <a href="{{ route('home') }}" class="nav-link">
                    <i class="fa-solid fa-house"></i>
                    Trang chủ
                </a>
            </li>
            <li>
                <a href="{{ route('home') }}#destinations" class="nav-link">
                    <i class="fa-solid fa-location-dot"></i>
                    Địa điểm
                </a>
            </li>
            <li>
                <a href="{{ route('trips.index') }}" class="nav-link">
                    <i class="fa-solid fa-map-location-dot"></i>
                    Chuyến đi
                </a>
            </li>
            <li>
                <a href="{{ route('favorites.index') }}" class="nav-link active">
                    <i class="fa-solid fa-heart" style="color: #0066FF;"></i>
                    Yêu thích
                </a>
            </li>
            <li>
                <a href="{{ route('home') }}" class="nav-link">
                    <i class="fa-solid fa-calendar-check"></i>
                    Booking
                </a>
            </li>
        </ul>

        <div class="nav-actions">
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn-admin">
                    <i class="fa-solid fa-shield-halved"></i> Admin
                </a>
            @else
                <a href="{{ route('admin.dashboard') }}" class="btn-admin">
                    Admin
                </a>
            @endif

            @auth
                <div class="user-dropdown">
                    <img src="{{ Auth::user()->avatar ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop' }}" 
                         alt="{{ Auth::user()->name }}" class="user-avatar-img">
                    <span>{{ Auth::user()->name }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 11px; color: #64748B;"></i>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn-explore-more" style="padding: 6px 16px;">
                    Đăng nhập
                </a>
            @endauth
        </div>
    </header>

    <!-- 2. Main Page Content -->
    <main class="main-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-box">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <span>Địa điểm yêu thích</span>
        </div>

        <!-- Page Header -->
        <div class="page-header-row">
            <div class="header-title-box">
                <h1>Bộ Sưu Tập Yêu Thích Của Bạn</h1>
                <p>Lưu lại những điểm đến bạn mong muốn khám phá trong các chuyến đi tới</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('home') }}#destinations" class="btn-explore-more">
                    <i class="fa-solid fa-plus"></i> Khám phá thêm địa điểm
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="fav-toast-container" id="sessionToastContainer">
                <div class="fav-toast" id="sessionToast">
                    <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 15px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const toast = document.getElementById('sessionToast');
                    if (toast) {
                        toast.classList.add('hide');
                        setTimeout(() => toast.remove(), 300);
                    }
                }, 3000);
            </script>
        @endif

        <!-- Card Grid Section -->
        <div id="favoritesListSection" @if($favorites->count() === 0) style="display: none;" @endif>
            <div class="cards-grid">
                @foreach($favorites as $fav)
                    @php 
                        $dest = $fav->destination; 
                        if (!$dest) continue;
                        
                        // Badge color logic
                        $catName = $dest->category->name ?? 'Du lịch';
                        $catClass = 'badge-cat-blue';
                        if (str_contains(mb_strtolower($catName), 'biển') || str_contains(mb_strtolower($catName), 'đảo')) {
                            $catClass = 'badge-cat-green';
                        } elseif (str_contains(mb_strtolower($catName), 'nghỉ') || str_contains(mb_strtolower($catName), 'dưỡng')) {
                            $catClass = 'badge-cat-yellow';
                        }
                    @endphp
                    <div class="dest-card" id="fav-card-{{ $dest->id }}">
                        <!-- Image & Badges -->
                        <div class="card-img-wrap">
                            <img src="{{ $dest->primaryImage->image_path ?? ($dest->primaryImage->image_url ?? 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e') }}" 
                                 alt="{{ $dest->name }}" class="card-img"
                                 onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e';">
                            
                            <span class="badge-cat-tag {{ $catClass }}">
                                {{ $catName }}
                            </span>

                            <button type="button" class="btn-delete-fav" title="Xóa khỏi yêu thích"
                                    onclick="openDeleteFavModal({{ $dest->id }}, '{{ addslashes($dest->name) }}')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <div class="card-meta-row">
                                <span class="location-text">
                                    <i class="fa-solid fa-location-dot location-icon-pin"></i>
                                    {{ $dest->city->name ?? 'Việt Nam' }}
                                </span>
                                <span class="rating-badge-pill">
                                    ★ {{ number_format($dest->rating ?? 5.0, 1) }}
                                </span>
                            </div>
<<<<<<< HEAD
                            <div class="card-body">
                                <div class="card-location">
                                    <i class="fa-solid fa-location-dot"></i> {{ $dest->city->name ?? 'Việt Nam' }}
                                </div>
                                <h3 class="card-title">{{ $dest->name }}</h3>
                                <div class="card-rating">
                                    <i class="fa-solid fa-star"></i> {{ number_format($dest->rating, 1) }}
                                    <span>({{ $dest->reviews_count ?? 0 }} đánh giá)</span>
                                </div>
                                <div class="card-footer-box">
                                    <div>
                                        <div class="price-label">Giá vé ước tính</div>
                                        <div class="price-value">
                                            {{ $dest->entrance_fee > 0 ? number_format($dest->entrance_fee, 0, ',', '.') . ' đ' : 'Miễn phí' }}
                                        </div>
                                    </div>
                                    <button type="button" class="btn-add-trip" onclick="openAddToTripModal({{ $dest->id }}, '{{ addslashes($dest->name) }}')">
                                        <i class="fa-solid fa-plus"></i> Thêm vào Trip
                                    </button>
=======

                            <h3 class="card-name-title">{{ $dest->name }}</h3>
                            <p class="card-desc-clamp">
                                {{ $dest->description ?? 'Trải nghiệm du lịch tuyệt vời với cảnh quan thiên nhiên và dịch vụ cao cấp.' }}
                            </p>

                            <!-- Card Footer -->
                            <div class="card-footer-box">
                                <span class="price-text-blue">
                                    {{ $dest->entrance_fee > 0 ? number_format($dest->entrance_fee, 0, ',', '.') . 'đ' : 'Miễn phí' }}
                                </span>
                                <div class="card-btns-right">
                                    <button type="button" class="btn-add-trip-pill" 
                                            onclick="openAddTripModal({{ $dest->id }}, '{{ addslashes($dest->name) }}')">
                                        + Trip
                                    </button>
                                    <a href="{{ route('home') }}#destinations" class="btn-detail-pill">
                                        Chi tiết
                                    </a>
>>>>>>> ngoc_ai/quan_ly_dia_diem_yeu_thich
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($favorites->hasPages())
                <div style="margin-top: 36px; display: flex; justify-content: center;">
                    {{ $favorites->links() }}
                </div>
            @endif
        </div>

        <!-- Empty State Section -->
        <div id="favoritesEmptySection" class="empty-state-box" @if($favorites->count() > 0) style="display: none;" @endif>
            <div class="empty-icon-circle">
                <i class="fa-regular fa-heart"></i>
            </div>
            <h2 class="empty-title-main">Bạn chưa có địa điểm yêu thích nào</h2>
            <p class="empty-desc-sub">
                Lưu lại những điểm đến bạn mong muốn khám phá trong các chuyến đi tới.<br>
                Hãy khám phá các danh thắng tuyệt đẹp và bấm lưu lại nhé!
            </p>
            <a href="{{ route('home') }}#destinations" class="btn-explore-more">
                <i class="fa-solid fa-compass"></i> Khám phá thêm địa điểm
            </a>
        </div>
    </main>

<<<<<<< HEAD
    <!-- MODAL THÊM VÀO TRIP (HÌNH 10 & 34) -->
    <div id="addToTripModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; align-items: center; justify-content: center;">
        <div style="background: var(--white); border-radius: var(--radius-lg); width: 100%; max-width: 480px; box-shadow: var(--shadow-lg); overflow: hidden; animation: modalFadeIn 0.2s ease;">
            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-dark); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-suitcase" style="color: var(--primary);"></i> Thêm vào chuyến đi
                </h3>
                <button type="button" onclick="closeAddToTripModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;">&times;</button>
            </div>
            <form id="addToTripForm" action="{{ route('favorites.add-to-trip') }}" method="POST">
                @csrf
                <input type="hidden" name="destination_id" id="modalDestinationId">
                <div style="padding: 1.5rem;">
                    <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: var(--primary-light); border-radius: var(--radius-md); font-size: 0.9rem; color: var(--primary-dark);">
                        Địa điểm: <strong id="modalDestinationName"></strong>
                    </div>
                    
                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-dark); margin-bottom: 0.4rem;">Chọn chuyến đi của bạn <span style="color:var(--danger)">*</span></label>
                        @if(isset($userTrips) && $userTrips->count() > 0)
                            <select name="trip_id" style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.95rem;" required>
                                <option value="">-- Chọn chuyến đi --</option>
                                @foreach($userTrips as $trip)
                                    <option value="{{ $trip->id }}">{{ $trip->name }} ({{ \Carbon\Carbon::parse($trip->start_date)->format('d/m/Y') }})</option>
                                @endforeach
                            </select>
                        @else
                            <p style="color: var(--text-muted); font-size: 0.85rem;">Bạn chưa có chuyến đi nào. <a href="{{ route('trips.create') }}" style="color:var(--primary); font-weight:700;">Tạo chuyến đi mới ngay</a></p>
                        @endif
                    </div>

                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-dark); margin-bottom: 0.4rem;">Ngày tham quan trong lịch trình <span style="color:var(--danger)">*</span></label>
                        <select name="day_number" style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.95rem;" required>
                            <option value="1">Ngày 1</option>
                            <option value="2">Ngày 2</option>
                            <option value="3">Ngày 3</option>
                            <option value="4">Ngày 4</option>
                            <option value="5">Ngày 5</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1.25rem;">
                        <label style="display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-dark); margin-bottom: 0.4rem;">Ghi chú hoạt động (Tùy chọn)</label>
                        <input type="text" name="note" style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.95rem;" placeholder="Ví dụ: Tham quan buổi sáng, chụp ảnh...">
                    </div>
                </div>
                <div style="padding: 1rem 1.5rem; background: var(--bg-light); border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 0.75rem;">
                    <button type="button" onclick="closeAddToTripModal()" style="background: #e2e8f0; color: var(--text-dark); border: none; padding: 0.6rem 1.2rem; border-radius: var(--radius-md); font-weight: 600; cursor: pointer;">Hủy</button>
                    <button type="submit" style="background: var(--primary); color: var(--white); border: none; padding: 0.6rem 1.4rem; border-radius: var(--radius-md); font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.4rem;" {{ (isset($userTrips) && $userTrips->isEmpty()) ? 'disabled' : '' }}>
                        <i class="fa-solid fa-check"></i> Xác nhận thêm
                    </button>
=======
    <!-- Modal "Thêm vào chuyến đi" (+ Trip) -->
    <div id="addTripModal" class="modal-backdrop">
        <div class="modal-window">
            <div class="modal-header-row">
                <h3 class="modal-title">Thêm vào Chuyến đi</h3>
                <button type="button" class="btn-modal-close" onclick="closeAddTripModal()">&times;</button>
            </div>

            <form action="{{ route('trips.index') }}" method="GET" id="addTripForm">
                <div class="modal-form-group">
                    <label class="modal-label">Địa điểm được chọn</label>
                    <input type="text" id="modalDestName" class="modal-input" readonly style="background: #F8FAFC; font-weight: 600;">
                    <input type="hidden" id="modalDestId" name="destination_id">
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Chọn chuyến đi của bạn</label>
                    @if(isset($userTrips) && $userTrips->count() > 0)
                        <select name="trip_id" class="modal-select" required>
                            @foreach($userTrips as $trip)
                                <option value="{{ $trip->id }}">
                                    {{ $trip->name }} ({{ \Carbon\Carbon::parse($trip->start_date)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div style="padding: 10px; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; font-size: 13px; color: #92400E; margin-bottom: 8px;">
                            Bạn chưa có chuyến đi nào. Hãy tạo chuyến đi mới trước nhé!
                        </div>
                        <a href="{{ route('trips.create') }}" class="btn-explore-more" style="display: inline-block; padding: 6px 14px; font-size: 13px;">
                            + Tạo chuyến đi mới
                        </a>
                    @endif
                </div>

                <div class="modal-form-group">
                    <label class="modal-label">Ghi chú cho hoạt động (tùy chọn)</label>
                    <textarea name="note" class="modal-textarea" placeholder="Nhập lưu ý hoặc kế hoạch tham quan..."></textarea>
                </div>

                <div class="modal-footer-btns">
                    <button type="button" class="btn-modal-cancel" onclick="closeAddTripModal()">Đóng</button>
                    @if(isset($userTrips) && $userTrips->count() > 0)
                        <button type="submit" class="btn-modal-submit">Xác nhận thêm</button>
                    @endif
>>>>>>> ngoc_ai/quan_ly_dia_diem_yeu_thich
                </div>
            </form>
        </div>
    </div>

<<<<<<< HEAD
    <footer>
        <p>&copy; {{ date('Y') }} Travel Planner. Hệ thống gợi ý và lên lịch trình du lịch thông minh.</p>
    </footer>

    <script>
        function openAddToTripModal(destinationId, destinationName) {
            document.getElementById('modalDestinationId').value = destinationId;
            document.getElementById('modalDestinationName').textContent = destinationName;
            var modal = document.getElementById('addToTripModal');
            modal.style.display = 'flex';
        }

        function closeAddToTripModal() {
            var modal = document.getElementById('addToTripModal');
            modal.style.display = 'none';
        }

        // Đóng modal khi click ra ngoài vùng backdrop
        document.getElementById('addToTripModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddToTripModal();
            }
        });
=======
    <!-- Modal Xác nhận Xóa Yêu thích (Custom Confirm Modal) -->
    <div id="deleteFavModal" class="modal-backdrop">
        <div class="modal-window confirm-modal-window">
            <div class="confirm-icon-box">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 class="confirm-title">Bỏ khỏi danh sách yêu thích?</h3>
            <p class="confirm-desc">
                Bạn có chắc chắn muốn bỏ <strong id="deleteDestName" style="color: #0F172A;"></strong> khỏi danh sách yêu thích không?
            </p>
            <form id="deleteFavForm" method="POST" action="">
                @csrf
                <div class="confirm-btn-row">
                    <button type="button" class="btn-confirm-cancel" onclick="closeDeleteFavModal()">Hủy bỏ</button>
                    <button type="submit" class="btn-confirm-delete">Xác nhận bỏ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Footer (Dark Theme Matching Image) -->
    <footer class="footer-dark">
        <div class="footer-grid">
            <!-- Col 1: Brand Info -->
            <div>
                <div class="footer-brand-title">
                    <div class="brand-icon" style="width: 30px; height: 30px;">
                        <i class="fa-solid fa-compass" style="font-size: 14px;"></i>
                    </div>
                    <span>TRAVEL PLANNER</span>
                </div>
                <p class="footer-desc">
                    Hệ thống quản lý và lập kế hoạch chuyến du lịch thông minh, kết nối mọi trải nghiệm khám phá của bạn.
                </p>
            </div>

            <!-- Col 2: Liên kết -->
            <div class="footer-col">
                <h4>Liên kết</h4>
                <ul>
                    <li><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li><a href="{{ route('home') }}#destinations">Địa điểm</a></li>
                    <li><a href="{{ route('trips.index') }}">Chuyến đi</a></li>
                    <li><a href="{{ route('favorites.index') }}">Yêu thích</a></li>
                </ul>
            </div>

            <!-- Col 3: Tài khoản -->
            <div class="footer-col">
                <h4>Tài khoản</h4>
                <ul>
                    <li><a href="{{ route('home') }}">Hồ sơ cá nhân</a></li>
                    <li><a href="{{ route('home') }}">Đơn đặt phòng</a></li>
                    <li><a href="{{ route('login') }}">Đăng nhập</a></li>
                    <li><a href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                </ul>
            </div>

            <!-- Col 4: Hỗ trợ -->
            <div class="footer-col">
                <h4>Hỗ trợ</h4>
                <div class="footer-contact-item">
                    <i class="fa-regular fa-envelope"></i>
                    <span>support@travelplanner.com</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>1900 6868</span>
                </div>
                <div class="footer-contact-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Đà Nẵng, Việt Nam</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom-copy">
            &copy; 2026 Travel Planner Travel Platform. All rights reserved.
        </div>
    </footer>

    <!-- Interactive Scripts -->
    <script>
        // Modal "Thêm vào chuyến đi"
        function openAddTripModal(destId, destName) {
            document.getElementById('modalDestId').value = destId;
            document.getElementById('modalDestName').value = destName;
            document.getElementById('addTripModal').classList.add('active');
        }

        function closeAddTripModal() {
            document.getElementById('addTripModal').classList.remove('active');
        }

        // Đóng modal khi click ra ngoài vùng cửa sổ
        document.getElementById('addTripModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddTripModal();
            }
        });

        // Modal "Xác nhận xóa yêu thích"
        function openDeleteFavModal(destId, destName) {
            document.getElementById('deleteDestName').textContent = '«' + destName + '»';
            document.getElementById('deleteFavForm').action = "{{ url('/favorites/toggle') }}/" + destId;
            document.getElementById('deleteFavModal').classList.add('active');
        }

        function closeDeleteFavModal() {
            document.getElementById('deleteFavModal').classList.remove('active');
        }

        document.getElementById('deleteFavModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteFavModal();
            }
        });


>>>>>>> ngoc_ai/quan_ly_dia_diem_yeu_thich
    </script>
</body>
</html>
