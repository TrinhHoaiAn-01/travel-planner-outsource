<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hành Trình & Chuyến Đi - Travel Planner</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            line-height: 1.5;
        }

        /* Top Navigation Header */
        .navbar {
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

        .navbar-brand {
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

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }

        .nav-item a {
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

        .nav-item a:hover {
            color: #0066FF;
            background-color: #F1F5F9;
        }

        .nav-item.active a {
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

        .user-dropdown {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            background: #2563EB;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1E293B;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 12px;
        }

        .breadcrumb a {
            color: #0066FF;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Page Title Header */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-title {
            font-size: 30px;
            font-weight: 700;
            color: #0F172A;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .page-subtitle {
            font-size: 15px;
            color: #64748B;
        }

        .btn-create-trip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #0066FF;
            color: #FFFFFF;
            padding: 12px 22px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.2);
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-create-trip:hover {
            background-color: #0052CC;
            transform: translateY(-1px);
        }

        /* Filter Tabs and Search Form */
        .filter-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 28px;
        }

        .status-tabs {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .status-pill {
            padding: 8px 18px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 500;
            color: #0066FF;
            background-color: transparent;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .status-pill:hover {
            background-color: #E2E8F0;
        }

        .status-pill.active {
            background-color: #0066FF;
            color: #FFFFFF;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 102, 255, 0.25);
        }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #FFFFFF;
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            flex-wrap: wrap;
        }

        .search-input-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 240px;
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

        .date-filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
        }

        .date-input {
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 13px;
            color: #0F172A;
            outline: none;
            background: #FFFFFF;
        }

        .date-input:focus {
            border-color: #0066FF;
        }

        .btn-filter {
            background-color: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            padding: 7px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-filter:hover {
            background-color: #0052CC;
        }

        .btn-reset {
            background-color: #F1F5F9;
            color: #475569;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-reset:hover {
            background-color: #E2E8F0;
        }

        /* Trips Grid */
        .trips-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        @media (max-width: 992px) {
            .trips-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .trips-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Trip Card */
        .trip-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .trip-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.06);
        }

        .card-banner {
            position: relative;
            height: 190px;
            width: 100%;
            overflow: hidden;
            background: #CBD5E1;
        }

        .card-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .trip-card:hover .card-banner img {
            transform: scale(1.03);
        }

        /* Badge in banner */
        .status-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            z-index: 10;
        }

        .badge-planned {
            background: rgba(255, 255, 255, 0.95);
            color: #0369A1;
            backdrop-filter: blur(4px);
        }

        .badge-ongoing {
            background: rgba(254, 243, 199, 0.95);
            color: #92400E;
            backdrop-filter: blur(4px);
        }

        .badge-completed {
            background: rgba(255, 255, 255, 0.95);
            color: #334155;
            backdrop-filter: blur(4px);
        }

        .badge-draft {
            background: rgba(241, 245, 249, 0.95);
            color: #64748B;
            backdrop-filter: blur(4px);
        }

        .menu-button {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #334155;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .trip-date-meta {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 8px;
        }

        .trip-date-meta svg {
            color: #0066FF;
        }

        .trip-name {
            font-size: 17px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 8px;
            line-height: 1.35;
        }

        .trip-description {
            font-size: 13.5px;
            color: #64748B;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 18px;
            min-height: 40px;
        }

        .card-footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .budget-block {
            display: flex;
            flex-direction: column;
        }

        .budget-label {
            font-size: 12px;
            color: #64748B;
            margin-bottom: 2px;
        }

        .budget-amount {
            font-size: 16px;
            font-weight: 700;
            color: #059669;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FFFFFF;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-icon:hover {
            background-color: #F8FAFC;
            border-color: #94A3B8;
        }

        .btn-primary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            border-radius: 9999px;
            font-size: 13.5px;
            font-weight: 600;
            background-color: #0066FF;
            color: #FFFFFF;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary-action:hover {
            background-color: #0052CC;
        }

        /* Empty State */
        .empty-state {
            background: #FFFFFF;
            border: 1px dashed #CBD5E1;
            border-radius: 16px;
            padding: 60px 24px;
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
            max-width: 420px;
            margin: 0 auto 20px;
        }

        /* Pagination */
        .pagination-container {
            margin-top: 36px;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                </svg>
            </div>
            <span>TRAVEL PLANNER</span>
        </a>

        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="{{ route('home') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    Trang chủ
                </a>
            </li>
            <li class="nav-item">
                <a href="#">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Địa điểm
                </a>
            </li>
            <li class="nav-item active">
                <a href="{{ route('trips.index') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path><path d="M6 6h10"></path><path d="M6 10h10"></path></svg>
                    Chuyến đi
                </a>
            </li>
            <li class="nav-item">
                <a href="#">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>
                    Yêu thích
                </a>
            </li>
            <li class="nav-item">
                <a href="#">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                    Booking
                </a>
            </li>
        </ul>

        <div class="navbar-actions">
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn-admin">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Admin
                </a>
            @endif

            <div class="user-dropdown">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <span class="user-name">{{ Auth::user()->name ?? 'Người dùng' }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display: inline; margin-left: 6px;">
                    @csrf
                    <button type="submit" title="Đăng xuất" style="background: none; border: none; cursor: pointer; color: #94A3B8;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <span>Chuyến đi của tôi</span>
        </nav>

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Hành Trình & Chuyến Đi</h1>
                <p class="page-subtitle">Quản lý lịch trình, hoạt động từng ngày và theo dõi chi tiết du lịch của bạn</p>
            </div>
            <button type="button" class="btn-create-trip" onclick="alert('Chức năng Tạo chuyến đi sẽ được phát triển theo phân công!');">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Tạo chuyến đi mới
            </button>
        </div>

        <!-- Filter and Search Section -->
        <div class="filter-section">
            <!-- Status Tabs -->
            <div class="status-tabs">
                <a href="{{ route('trips.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                   class="status-pill {{ $currentStatus === 'all' || empty($currentStatus) ? 'active' : '' }}">
                    Tất cả ({{ $counts['all'] ?? 0 }})
                </a>
                <a href="{{ route('trips.index', array_merge(request()->query(), ['status' => 'planned'])) }}"
                   class="status-pill {{ $currentStatus === 'planned' ? 'active' : '' }}">
                    Sắp tới ({{ $counts['planned'] ?? 0 }})
                </a>
                <a href="{{ route('trips.index', array_merge(request()->query(), ['status' => 'ongoing'])) }}"
                   class="status-pill {{ $currentStatus === 'ongoing' ? 'active' : '' }}">
                    Đang diễn ra ({{ $counts['ongoing'] ?? 0 }})
                </a>
                <a href="{{ route('trips.index', array_merge(request()->query(), ['status' => 'completed'])) }}"
                   class="status-pill {{ $currentStatus === 'completed' ? 'active' : '' }}">
                    Đã hoàn thành ({{ $counts['completed'] ?? 0 }})
                </a>
            </div>

            <!-- Search and Date Filter Bar -->
            <form action="{{ route('trips.index') }}" method="GET" class="search-bar">
                <input type="hidden" name="status" value="{{ $currentStatus }}">

                <div class="search-input-group">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="search" value="{{ $searchKeyword }}" placeholder="Tìm kiếm theo tên hoặc mô tả chuyến đi...">
                </div>

                <div class="date-filter-group">
                    <span>Từ ngày:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="date-input">
                    <span>Đến ngày:</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="date-input">
                </div>

                <button type="submit" class="btn-filter">Tìm kiếm & Lọc</button>

                @if($searchKeyword || $startDate || $endDate || ($currentStatus && $currentStatus !== 'all'))
                    <a href="{{ route('trips.index') }}" class="btn-reset">Đặt lại</a>
                @endif
            </form>
        </div>

        <!-- Trips Grid -->
        <div class="trips-grid">
            @forelse($trips as $trip)
                @php
                    // Tính số ngày và số đêm
                    $durationText = 'Chưa xác định thời lượng';
                    if ($trip->start_date && $trip->end_date) {
                        $days = $trip->start_date->diffInDays($trip->end_date) + 1;
                        $nights = max(0, $days - 1);
                        $durationText = "{$days} ngày {$nights} đêm";
                    }

                    // Xác định trạng thái hiển thị
                    $badgeClass = 'badge-planned';
                    $badgeText = 'Sắp tới';
                    $isCompleted = ($trip->status === \App\Models\Trip::STATUS_COMPLETED);

                    $isOngoing = false;
                    if ($trip->start_date && $trip->end_date && now()->between($trip->start_date, $trip->end_date) && ! $isCompleted) {
                        $isOngoing = true;
                    }

                    if ($isCompleted) {
                        $badgeClass = 'badge-completed';
                        $badgeText = 'Đã hoàn thành';
                    } elseif ($isOngoing || $trip->status === 'ongoing') {
                        $badgeClass = 'badge-ongoing';
                        $badgeText = 'Đang diễn ra';
                    } elseif ($trip->status === \App\Models\Trip::STATUS_DRAFT) {
                        $badgeClass = 'badge-draft';
                        $badgeText = 'Bản nháp';
                    }

                    // Hình ảnh minh họa theo tên chuyến đi hoặc fallback
                    $bannerImage = 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=800&q=80';
                    $lowerName = mb_strtolower($trip->name);
                    if (str_contains($lowerName, 'hạ long') || str_contains($lowerName, 'ha long')) {
                        $bannerImage = 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&q=80';
                    } elseif (str_contains($lowerName, 'sa pa') || str_contains($lowerName, 'fansipan')) {
                        $bannerImage = 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80';
                    } elseif (str_contains($lowerName, 'đà nẵng') || str_contains($lowerName, 'hội an')) {
                        $bannerImage = 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=800&q=80';
                    }
                @endphp

                <div class="trip-card">
                    <!-- Banner -->
                    <div class="card-banner">
                        <span class="status-badge {{ $badgeClass }}">{{ $badgeText }}</span>

                        <button type="button" class="menu-button" title="Tùy chọn" onclick="alert('Menu tùy chọn chuyến đi #{{ $trip->id }}');">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                        </button>

                        <img src="{{ $bannerImage }}" alt="{{ $trip->name }}" loading="lazy">
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <div class="trip-date-meta">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                            <span>
                                {{ $trip->start_date ? $trip->start_date->format('d/m/Y') : '--/--/----' }} - {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : '--/--/----' }} • {{ $durationText }}
                            </span>
                        </div>

                        <h3 class="trip-name">{{ $trip->name }}</h3>
                        <p class="trip-description">{{ $trip->description ?: 'Chưa có mô tả chi tiết cho chuyến đi này.' }}</p>

                        <!-- Footer -->
                        <div class="card-footer">
                            <div class="budget-block">
                                @if($isCompleted)
                                    <span class="budget-label">Tổng chi thực tế</span>
                                    <span class="budget-amount">{{ number_format($trip->expenses->sum('amount'), 0, ',', '.') }}đ</span>
                                @else
                                    <span class="budget-label">Ngân sách dự kiến</span>
                                    <span class="budget-amount">{{ number_format($trip->budget, 0, ',', '.') }}đ</span>
                                @endif
                            </div>

                            <div class="action-buttons">
                                <a href="#" class="btn-icon" title="Xem lịch" onclick="alert('Xem lịch chuyến đi #{{ $trip->id }}'); return false;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                                </a>

                                @if($isCompleted)
                                    <a href="#" class="btn-primary-action" onclick="alert('Xem lại chuyến đi #{{ $trip->id }}'); return false;">Xem lại</a>
                                @else
                                    <a href="#" class="btn-primary-action" onclick="alert('Xem lịch trình chuyến đi #{{ $trip->id }}'); return false;">Lịch trình</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <h3 class="empty-title">Không tìm thấy chuyến đi nào</h3>
                    <p class="empty-desc">Không có chuyến đi nào phù hợp với bộ lọc hiện tại. Bạn hãy thử điều chỉnh từ khóa tìm kiếm hoặc chọn bộ lọc trạng thái khác.</p>
                    <a href="{{ route('trips.index') }}" class="btn-filter" style="display: inline-block; text-decoration: none;">Xem tất cả chuyến đi</a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($trips->hasPages())
            <div class="pagination-container">
                {{ $trips->links() }}
            </div>
        @endif
    </main>

</body>
</html>
