<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khám Phá Địa Điểm Du Lịch & Nơi Lưu Trú - Travel Planner</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
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
            display: flex;
            flex-direction: column;
        }

        /* 1. Navbar Header */
        .navbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 14px 36px;
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
            gap: 12px;
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
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .nav-link.active {
            background-color: #EBF5FF;
            color: #0066FF;
            font-weight: 600;
        }

        .nav-link:hover:not(.active) {
            color: #0066FF;
            background-color: #F1F5F9;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-admin {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 20px;
            border: 1px solid #0F172A;
            background: #FFFFFF;
            color: #0F172A;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-admin:hover {
            background: #F8FAFC;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 600;
            font-size: 14px;
            padding: 4px 8px;
            border-radius: 20px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #0066FF;
            color: #FFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
        }

        .btn-login {
            background: #0066FF;
            color: #FFFFFF;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background: #0052CC;
        }

        /* 2. Main Container */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 24px 60px;
            width: 100%;
            flex: 1;
        }

        /* Breadcrumb */
        .breadcrumb-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 20px;
        }

        .breadcrumb-wrap a {
            color: #64748B;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .breadcrumb-wrap a:hover {
            color: #0066FF;
        }

        .breadcrumb-wrap span {
            color: #0066FF;
            font-weight: 600;
        }

        /* Page Header */
        .page-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.5px;
        }

        .badge-results-count {
            background: #EBF5FF;
            color: #0066FF;
            border: 1px solid #BAE6FD;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* 3. Filter Box Card */
        .filter-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        .filter-form-grid {
            display: grid;
            grid-template-columns: 2fr 1.3fr 1.3fr 1.3fr 1.3fr auto;
            gap: 16px;
            align-items: flex-end;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-field label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .filter-input, .filter-select {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            background: #F8FAFC;
            color: #0F172A;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }

        .filter-input:focus, .filter-select:focus {
            border-color: #0066FF;
            background: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }

        .btn-reset-filter {
            height: 42px;
            padding: 0 18px;
            background: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: background 0.2s;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-reset-filter:hover {
            background: #0052CC;
        }

        /* Filter Sub-bar: Toggle Nơi lưu trú & Lọc nhanh */
        .filter-sub-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid #F1F5F9;
            flex-wrap: wrap;
            gap: 16px;
        }

        .toggle-lodging-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .switch-box {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .switch-box input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider-round {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #CBD5E1;
            transition: .3s;
            border-radius: 24px;
        }

        .slider-round:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        .switch-box input:checked + .slider-round {
            background-color: #0066FF;
        }

        .switch-box input:checked + .slider-round:before {
            transform: translateX(20px);
        }

        .toggle-label-text {
            font-size: 14px;
            font-weight: 600;
            color: #1E293B;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .quick-cities-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .quick-city-label {
            font-size: 13px;
            color: #64748B;
            font-weight: 500;
        }

        .pill-city {
            display: inline-block;
            padding: 5px 14px;
            background: #F1F5F9;
            color: #475569;
            border-radius: 20px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .pill-city:hover {
            background: #E2E8F0;
            color: #0F172A;
        }

        .pill-city.active {
            background: #0066FF;
            color: #FFFFFF;
            font-weight: 600;
        }

        /* 4. Destinations Grid (3 Columns) */
        .destinations-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 40px;
        }

        .dest-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .dest-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        }

        .card-img-wrap {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
            background: #E2E8F0;
        }

        .card-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .dest-card:hover .card-img-wrap img {
            transform: scale(1.05);
        }

        .badges-stack {
            position: absolute;
            top: 12px;
            left: 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            z-index: 2;
        }

        .badge-cat {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px);
            color: #0066FF;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            width: fit-content;
        }

        .badge-lodging {
            background: #FEF3C7;
            color: #B45309;
            border: 1px solid #FDE68A;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            width: fit-content;
        }

        .btn-fav-card {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #FFFFFF;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            transition: transform 0.2s;
            z-index: 5;
        }

        .btn-fav-card:hover {
            transform: scale(1.1);
        }

        .card-body {
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .city-tag {
            color: #EF4444;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .rating-badge {
            background: #FEF3C7;
            color: #D97706;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .dest-title {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 8px;
            line-height: 1.35;
        }

        .dest-title a {
            color: #0F172A;
            text-decoration: none;
            transition: color 0.2s;
        }

        .dest-title a:hover {
            color: #0066FF;
        }

        .dest-desc {
            font-size: 13px;
            color: #64748B;
            line-height: 1.5;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid #F1F5F9;
        }

        .price-label-tiny {
            font-size: 12px;
            color: #94A3B8;
        }

        .price-text-large {
            font-size: 17px;
            font-weight: 800;
            color: #0066FF;
        }

        .btn-detail-link {
            padding: 8px 18px;
            background: #0066FF;
            color: #FFFFFF;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .btn-detail-link:hover {
            background: #0052CC;
        }

        /* 5. Pagination Wrap */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }

        .custom-pagination {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
        }

        .custom-pagination li a,
        .custom-pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            color: #475569;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            transition: all 0.2s;
        }

        .custom-pagination li.active span {
            background: #0066FF;
            color: #FFFFFF;
            border-color: #0066FF;
        }

        .custom-pagination li a:hover {
            background: #F1F5F9;
            color: #0066FF;
        }

        .custom-pagination li.disabled span {
            color: #CBD5E1;
            background: #F8FAFC;
            cursor: not-allowed;
        }

        /* Empty State */
        .empty-results-box {
            text-align: center;
            padding: 60px 20px;
            background: #FFFFFF;
            border-radius: 16px;
            border: 1px dashed #CBD5E1;
            grid-column: 1 / -1;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 6px;
        }

        .empty-sub {
            font-size: 14px;
            color: #64748B;
            margin-bottom: 20px;
        }

        @media (max-width: 992px) {
            .filter-form-grid {
                grid-template-columns: 1fr 1fr;
            }
            .destinations-grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .filter-form-grid {
                grid-template-columns: 1fr;
            }
            .destinations-grid-3 {
                grid-template-columns: 1fr;
            }
            .navbar {
                padding: 12px 16px;
            }
            .container {
                padding: 16px 16px 40px;
            }
        }
    </style>
</head>
<body>

    <!-- 1. Thanh điều hướng Navbar -->
    <nav class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                </svg>
            </div>
            TRAVEL PLANNER
        </a>

        <ul class="navbar-nav">
            <li>
                <a href="{{ route('home') }}" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                    Trang chủ
                </a>
            </li>
            <li>
                <a href="{{ route('destinations.index') }}" class="nav-link active">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Địa điểm
                </a>
            </li>
            <li>
                <a href="{{ route('trips.index') }}" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    Chuyến đi
                </a>
            </li>
            <li>
                <a href="{{ route('favorites.index') }}" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    Yêu thích
                </a>
            </li>
            <li>
                <a href="#" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                    Booking
                </a>
            </li>
        </ul>

        <div class="nav-actions">
            @auth
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="btn-admin">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Admin
                    </a>
                @endif
                <a href="{{ route('profile.show') }}" class="user-dropdown" title="Hồ sơ cá nhân">
                    <div class="user-avatar">
                        {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span>{{ Auth::user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-login">Đăng nhập</a>
            @endauth
        </div>
    </nav>

    <!-- 2. Nội dung chính: Khám phá Địa Điểm & Nơi Lưu Trú -->
    <main class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-wrap">
            <a href="{{ route('home') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                Trang chủ
            </a>
            <span>/</span>
            <span>Địa điểm</span>
        </div>

        <!-- Tiêu đề trang & Badge kết quả -->
        <div class="page-header-row">
            <h1 class="page-title">Khám Phá Địa Điểm Du Lịch</h1>
            <div class="badge-results-count" id="resultsCountBadge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"></circle><path d="M20 21a8 8 0 1 0-16 0"></path></svg>
                <span>{{ $destinations->total() }} điểm đến phù hợp</span>
            </div>
        </div>

        <!-- Khối Bộ Lọc (Filter Form) -->
        <div class="filter-card">
            <form action="{{ route('destinations.index') }}" method="GET" id="destinationsFilterForm">
                <div class="filter-form-grid">
                    <!-- 1. Tìm kiếm theo tên -->
                    <div class="filter-field">
                        <label for="filterSearch">🔍 Tìm kiếm</label>
                        <input type="text" name="search" id="filterSearch" class="filter-input" 
                               placeholder="Tên địa điểm..." value="{{ request('search') ?? request('keyword') }}">
                    </div>

                    <!-- 2. Thành phố -->
                    <div class="filter-field">
                        <label for="filterCity">📍 Thành phố</label>
                        <select name="city_id" id="filterCity" class="filter-select">
                            <option value="">Tất cả thành phố</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}" {{ (string)request('city_id') === (string)$c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Danh mục -->
                    <div class="filter-field">
                        <label for="filterCategory">⊞ Danh mục</label>
                        <select name="category_id" id="filterCategory" class="filter-select">
                            <option value="">Tất cả danh mục</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)request('category_id') === (string)$cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Khoảng giá -->
                    <div class="filter-field">
                        <label for="filterBudget">💵 Khoảng giá</label>
                        <select name="budget" id="filterBudget" class="filter-select">
                            <option value="">Tất cả mức giá</option>
                            <option value="1" {{ request('budget') == '1' ? 'selected' : '' }}>Dưới 500.000đ</option>
                            <option value="2" {{ request('budget') == '2' ? 'selected' : '' }}>500.000đ - 1.500.000đ</option>
                            <option value="3" {{ request('budget') == '3' ? 'selected' : '' }}>Trên 1.500.000đ</option>
                        </select>
                    </div>

                    <!-- 5. Sắp xếp -->
                    <div class="filter-field">
                        <label for="filterSort">⋮= Sắp xếp</label>
                        <select name="sort" id="filterSort" class="filter-select">
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Độ phổ biến</option>
                            <option value="rating_desc" {{ request('sort') == 'rating_desc' ? 'selected' : '' }}>Đánh giá cao nhất</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                        </select>
                    </div>

                    <!-- Nút Đặt lại / Tìm kiếm -->
                    <div class="filter-field">
                        <a href="{{ route('destinations.index') }}" class="btn-reset-filter" title="Đặt lại bộ lọc">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                            Đặt lại
                        </a>
                    </div>
                </div>

                <!-- Sub-bar: Toggle Nơi lưu trú & Lọc nhanh Thành phố -->
                <div class="filter-sub-bar">
                    <label class="toggle-lodging-wrap" for="toggleLodging">
                        <span class="switch-box">
                            <input type="checkbox" name="is_lodging" id="toggleLodging" value="1" 
                                   {{ request('is_lodging') == '1' ? 'checked' : '' }} onchange="document.getElementById('destinationsFilterForm').submit();">
                            <span class="slider-round"></span>
                        </span>
                        <span class="toggle-label-text">
                            🏨 Chỉ hiển thị Nơi lưu trú (Khách sạn / Resort)
                        </span>
                    </label>

                    <div class="quick-cities-wrap">
                        <span class="quick-city-label">Lọc nhanh:</span>
                        @php
                            $quickCities = ['Đà Nẵng', 'Phú Quốc', 'Hạ Long', 'Hà Nội', 'Đà Lạt', 'Hội An'];
                        @endphp
                        @foreach($cities as $quickCity)
                            @if(in_array($quickCity->name, $quickCities))
                                <a href="{{ route('destinations.index', array_merge(request()->except(['city_id', 'page']), ['city_id' => $quickCity->id])) }}" 
                                   class="pill-city {{ (string)request('city_id') === (string)$quickCity->id ? 'active' : '' }}">
                                    {{ $quickCity->name }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </form>
        </div>

        <!-- 3. Lưới hiển thị danh sách địa điểm (3 Cột) -->
        <div class="destinations-grid-3">
            @forelse($destinations as $dest)
                <div class="dest-card">
                    <!-- Ảnh & Huy hiệu -->
                    <div class="card-img-wrap">
                        @if($dest->primaryImage)
                            <img src="{{ $dest->primaryImage->image_path }}" alt="{{ $dest->name }}">
                        @elseif($dest->images->isNotEmpty())
                            <img src="{{ $dest->images->first()->image_path }}" alt="{{ $dest->name }}">
                        @else
                            <img src="https://images.unsplash.com/photo-1528127269322-539801943592?w=600" alt="{{ $dest->name }}">
                        @endif

                        <div class="badges-stack">
                            @if($dest->category)
                                <span class="badge-cat">{{ $dest->category->name }}</span>
                            @endif

                            @if($dest->has_hotel)
                                <span class="badge-lodging">🏨 Nơi lưu trú</span>
                            @endif
                        </div>

                        <!-- Nút lưu yêu thích -->
                        <form action="{{ route('favorites.toggle', $dest->id) }}" method="POST" style="position: absolute; top: 12px; right: 12px; z-index: 5;">
                            @csrf
                            <button type="submit" class="btn-fav-card" title="Lưu yêu thích">🤍</button>
                        </form>
                    </div>

                    <!-- Thông tin chi tiết Card -->
                    <div class="card-body">
                        <div class="meta-row">
                            <span class="city-tag">
                                📍 {{ $dest->city->name ?? 'Việt Nam' }}
                            </span>
                            <span class="rating-badge">
                                ⭐ {{ number_format($dest->rating, 1) }}
                            </span>
                        </div>

                        <h3 class="dest-title">
                            <a href="{{ route('destinations.show', $dest->slug) }}">
                                {{ $dest->name }}
                            </a>
                        </h3>

                        <p class="dest-desc">
                            {{ $dest->description }}
                        </p>

                        <div class="card-footer">
                            <div>
                                <span class="price-label-tiny">Giá từ</span>
                                <div class="price-text-large">
                                    @if($dest->has_hotel && $dest->formatted_min_room_price)
                                        {{ $dest->formatted_min_room_price }}
                                    @elseif($dest->entrance_fee > 0)
                                        {{ number_format($dest->entrance_fee, 0, ',', '.') }}đ
                                    @else
                                        Miễn phí
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('destinations.show', $dest->slug) }}" class="btn-detail-link">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-results-box">
                    <div class="empty-icon">🏖️</div>
                    <h3 class="empty-title">Không tìm thấy địa điểm hoặc nơi lưu trú nào phù hợp</h3>
                    <p class="empty-sub">Hãy thử điều chỉnh lại bộ lọc tìm kiếm hoặc tắt tùy chọn lọc nơi lưu trú.</p>
                    <a href="{{ route('destinations.index') }}" class="btn-reset-filter" style="display: inline-flex;">
                        Xem tất cả địa điểm
                    </a>
                </div>
            @endforelse
        </div>

        <!-- 4. Phân trang -->
        @if($destinations->hasPages())
            <div class="pagination-container">
                <ul class="custom-pagination">
                    {{-- Previous Page Link --}}
                    @if ($destinations->onFirstPage())
                        <li class="disabled"><span>&lsaquo; Trước</span></li>
                    @else
                        <li><a href="{{ $destinations->previousPageUrl() }}" rel="prev">&lsaquo; Trước</a></li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($destinations->getUrlRange(1, $destinations->lastPage()) as $page => $url)
                        @if ($page == $destinations->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($destinations->hasMorePages())
                        <li><a href="{{ $destinations->nextPageUrl() }}" rel="next">Tiếp &rsaquo;</a></li>
                    @else
                        <li class="disabled"><span>Tiếp &rsaquo;</span></li>
                    @endif
                </ul>
            </div>
        @endif
    </main>

    <!-- Tự động submit khi thay đổi select lọc -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('destinationsFilterForm');
            ['filterCity', 'filterCategory', 'filterBudget', 'filterSort'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('change', function () {
                        form.submit();
                    });
                }
            });
        });
    </script>
</body>
</html>
