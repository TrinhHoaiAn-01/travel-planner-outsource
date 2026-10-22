<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch trình Ngày {{ $current_day }} - {{ $trip->name }} - Travel Planner</title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --primary: #0066FF;
            --primary-dark: #0052CC;
            --primary-light: #EBF5FF;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --bg-page: #F8FAFC;
            --card-bg: #FFFFFF;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --radius-card: 16px;
            --radius-pill: 9999px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* 1. Header Navigation */
        .site-header {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-weight: 800;
            font-size: 19px;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .logo-icon-box {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 17px;
            box-shadow: 0 4px 10px rgba(0, 102, 255, 0.25);
        }

        .main-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-link-custom:hover {
            color: var(--primary);
            background-color: #F1F5F9;
        }

        .nav-link-custom.active {
            color: var(--primary);
            background-color: var(--primary-light);
            font-weight: 700;
        }

        .user-dropdown-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 14px 4px 4px;
            background: #F1F5F9;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-pill);
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-avatar-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* 2. Main Wrapper */
        .page-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 24px 60px;
            width: 100%;
            flex: 1;
        }

        /* Breadcrumb */
        .breadcrumb-box {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
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
            color: #94A3B8;
        }

        .breadcrumb-current {
            color: var(--text-muted);
            font-weight: 500;
        }

        /* 3. Hero Trip Header Card */
        @php
            $bannerBg = 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=1400&q=80';
            $tripNameLower = mb_strtolower($trip->name);
            if (str_contains($tripNameLower, 'hà nội') || str_contains($tripNameLower, 'hoàn kiếm')) {
                $bannerBg = 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1400&q=80';
            } elseif (str_contains($tripNameLower, 'đà nẵng') || str_contains($tripNameLower, 'hội an')) {
                $bannerBg = 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=1400&q=80';
            } elseif (str_contains($tripNameLower, 'hạ long')) {
                $bannerBg = 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1400&q=80';
            }
            $nightsCount = max(0, $days_count - 1);
        @endphp
        .trip-hero-card {
            background: linear-gradient(rgba(15, 23, 42, 0.72), rgba(15, 23, 42, 0.82)), url('{{ $bannerBg }}') center/cover no-repeat;
            border-radius: 20px;
            padding: 36px 32px;
            color: #FFFFFF;
            margin-bottom: 24px;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .trip-hero-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .hero-badges-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .badge-pill-status {
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            color: #FFFFFF;
        }

        .badge-pill-duration {
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 700;
            background: #FFFFFF;
            color: var(--primary);
        }

        .trip-title-main {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .trip-meta-info {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 14.5px;
            color: #CBD5E1;
            flex-wrap: wrap;
        }

        .trip-meta-info span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hero-actions-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-hero-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: var(--radius-pill);
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-hero-white {
            background: #FFFFFF;
            color: var(--text-dark);
            border: none;
        }

        .btn-hero-white:hover {
            background: #F1F5F9;
            transform: translateY(-2px);
        }

        .btn-hero-outline {
            background: transparent;
            color: #FFFFFF;
            border: 1.5px solid rgba(255, 255, 255, 0.4);
        }

        .btn-hero-outline:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: #FFFFFF;
        }

        /* 4. Days Tabs Navigation (Chuyển đổi từng ngày) */
        .days-nav-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: var(--shadow-sm);
        }

        .days-pills-list {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
            max-width: 100%;
        }

        .day-pill-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: #F8FAFC;
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
            min-width: 90px;
            cursor: pointer;
        }

        .day-pill-btn:hover {
            background: var(--primary-light);
            border-color: #BFDBFE;
            color: var(--primary);
        }

        .day-pill-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
        }

        .day-pill-title {
            font-size: 14px;
            font-weight: 800;
        }

        .day-pill-date {
            font-size: 11.5px;
            opacity: 0.85;
            font-weight: 500;
        }

        .day-pill-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.1);
            margin-top: 2px;
        }

        .day-pill-btn.active .day-pill-badge {
            background: rgba(255, 255, 255, 0.25);
            color: #FFFFFF;
        }

        .btn-add-activity-main {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: var(--radius-pill);
            background: var(--primary);
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 102, 255, 0.25);
            transition: var(--transition);
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-add-activity-main:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            color: #FFFFFF;
        }

        /* 5. Daily Schedule Header & Alert Banner */
        .schedule-header-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .schedule-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge-count-activities {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: var(--radius-pill);
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
        }

        .tips-banner-box {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 12px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1E40AF;
            font-size: 13.5px;
            margin-bottom: 24px;
        }

        /* 6. Timeline Activities List */
        .timeline-container {
            position: relative;
            padding-left: 32px;
        }

        .timeline-container::before {
            content: '';
            position: absolute;
            top: 15px;
            bottom: 20px;
            left: 11px;
            width: 2px;
            background: #CBD5E1;
        }

        .timeline-node {
            position: relative;
            margin-bottom: 24px;
        }

        .timeline-node:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -32px;
            top: 20px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #FFFFFF;
            border: 3px solid var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
            z-index: 2;
        }

        .activity-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .activity-card:hover {
            border-color: #CBD5E1;
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .activity-thumb-box {
            width: 130px;
            height: 100px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            background: #E2E8F0;
        }

        .activity-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .activity-info-box {
            flex: 1;
            min-width: 250px;
        }

        .activity-time-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
        }

        .activity-name-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .activity-meta-line {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .badge-cat-tag {
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            background: #F1F5F9;
            color: #475569;
        }

        .activity-note-box {
            background: #F8FAFC;
            border-left: 3px solid #94A3B8;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13.5px;
            color: #475569;
            margin-bottom: 10px;
        }

        .activity-actions-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid #F1F5F9;
            margin-top: 6px;
        }

        .activity-price-tag {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--success);
        }

        .activity-btns {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-act-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid var(--border-color);
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 13px;
        }

        .btn-act-icon:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        /* 7. Empty State (Khi ngày chưa có hoạt động) */
        .empty-day-box {
            background: var(--card-bg);
            border: 2px dashed #CBD5E1;
            border-radius: var(--radius-card);
            padding: 50px 24px;
            text-align: center;
            margin-top: 8px;
        }

        .empty-icon-wrap {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .empty-desc {
            font-size: 14px;
            color: var(--text-muted);
            max-width: 460px;
            margin: 0 auto 20px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <!-- 1. Header Navigation -->
    <header class="site-header">
        <div class="header-inner">
            <a href="{{ route('home') }}" class="logo-area">
                <div class="logo-icon-box">
                    <i class="bi bi-compass"></i>
                </div>
                <span>Travel Planner</span>
            </a>

            <nav>
                <ul class="main-nav">
                    <li><a href="{{ route('home') }}" class="nav-link-custom">Trang chủ</a></li>
                    <li><a href="{{ route('home') }}#destinations" class="nav-link-custom">Địa điểm</a></li>
                    <li><a href="{{ route('trips.index') }}" class="nav-link-custom active">Chuyến đi</a></li>
                    <li><a href="{{ route('favorites.index') }}" class="nav-link-custom">Yêu thích</a></li>
                </ul>
            </nav>

            <div>
                <button type="button" class="user-dropdown-btn">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100" class="user-avatar-img" alt="Avatar">
                    <span>{{ Auth::user()->name ?? 'Người dùng' }}</span>
                </button>
            </div>
        </div>
    </header>

    <!-- 2. Page Content -->
    <main class="page-wrapper">
        <!-- Breadcrumb -->
        <div class="breadcrumb-box">
            <a href="{{ route('home') }}" class="breadcrumb-link">Trang chủ</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('trips.index') }}" class="breadcrumb-link">Chuyến đi của tôi</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('trips.show', $trip->id) }}" class="breadcrumb-link">{{ $trip->name }}</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">Lịch trình chi tiết</span>
        </div>

        <!-- 3. Trip Hero Banner -->
        <div class="trip-hero-card">
            <div class="trip-hero-top">
                <div>
                    <div class="hero-badges-line">
                        <span class="badge-pill-status">
                            <i class="bi bi-flag-fill me-1"></i>
                            {{ $trip->status === 'completed' ? 'Đã hoàn thành' : ($trip->status === 'ongoing' ? 'Đang diễn ra' : 'Đã lên kế hoạch') }}
                        </span>
                        <span class="badge-pill-duration">
                            <i class="bi bi-clock-history me-1"></i>{{ $days_count }} ngày {{ $nightsCount }} đêm
                        </span>
                    </div>
                    <h1 class="trip-title-main">{{ $trip->name }}</h1>
                    <div class="trip-meta-info">
                        <span>
                            <i class="bi bi-calendar3 text-warning"></i>
                            {{ $trip->start_date ? $trip->start_date->format('d/m/Y') : '--/--/----' }} — {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : '--/--/----' }}
                        </span>
                        <span>
                            <i class="bi bi-wallet2 text-success"></i>
                            Ngân sách: <strong>{{ number_format($trip->budget, 0, ',', '.') }}₫</strong>
                        </span>
                        <span>
                            <i class="bi bi-list-check text-info"></i>
                            Tổng hoạt động: <strong>{{ $total_activities_count }}</strong>
                        </span>
                    </div>
                </div>

                <div class="hero-actions-group">
                    <a href="{{ route('trips.show', $trip->id) }}" class="btn-hero-action btn-hero-white">
                        <i class="bi bi-info-circle"></i> Chi tiết chuyến đi
                    </a>
                    <a href="{{ route('trips.index') }}" class="btn-hero-action btn-hero-outline">
                        <i class="bi bi-arrow-left"></i> Danh sách chuyến đi
                    </a>
                </div>
            </div>
        </div>

        <!-- 4. Days Tabs Navigation (Chức năng 5: Xem lịch trình theo từng ngày) -->
        <div class="days-nav-container">
            <div class="days-pills-list">
                @foreach($days_list as $dayItem)
                    <a href="{{ route('itinerary.index', ['trip' => $trip->id, 'day_number' => $dayItem['day_number']]) }}" 
                       class="day-pill-btn {{ $dayItem['is_active'] ? 'active' : '' }}">
                        <span class="day-pill-title">Ngày {{ $dayItem['day_number'] }}</span>
                        @if($dayItem['date_short'])
                            <span class="day-pill-date">{{ $dayItem['date_short'] }}</span>
                        @endif
                        <span class="day-pill-badge">{{ $dayItem['items_count'] }} mục</span>
                    </a>
                @endforeach
            </div>

            <div>
                <a href="{{ route('favorites.index') }}" class="btn-add-activity-main">
                    <i class="bi bi-plus-lg"></i> Thêm hoạt động vào Ngày {{ $current_day }}
                </a>
            </div>
        </div>

        <!-- 5. Header Ngày đang chọn -->
        <div class="schedule-header-box">
            <div class="schedule-title">
                <i class="bi bi-calendar2-check text-primary"></i>
                <span>Lịch trình Ngày {{ $current_day }}</span>
                @if($current_date)
                    <span style="font-size: 15px; font-weight: 600; color: var(--text-muted);">
                        — {{ $current_day_of_week }}, ngày {{ $current_date->format('d/m/Y') }}
                    </span>
                @endif
                <span class="badge-count-activities">{{ $items->count() }} hoạt động</span>
            </div>
        </div>

        <!-- Tips Banner -->
        <div class="tips-banner-box">
            <i class="bi bi-lightbulb-fill" style="font-size: 18px;"></i>
            <span><strong>Gợi ý lịch trình:</strong> Các hoạt động trong ngày được sắp xếp tự động theo thứ tự thời gian từ sáng đến tối. Bạn có thể thêm các địa điểm yêu thích hoặc tạo lịch trình mới.</span>
        </div>

        <!-- 6. Timeline Danh Sách Hoạt Động Của Ngày Được Chọn -->
        @if($items->count() > 0)
            <div class="timeline-container">
                @foreach($items as $item)
                    @php
                        $dest = $item->destination;
                        $img = $dest && $dest->primaryImage ? $dest->primaryImage->image_path : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400';
                        $cat = $dest && $dest->category ? $dest->category->name : 'Tham quan';
                        $cityName = $dest && $dest->city ? $dest->city->name : null;
                    @endphp
                    <div class="timeline-node">
                        <div class="timeline-dot"></div>
                        <div class="activity-card">
                            @if($dest)
                                <div class="activity-thumb-box">
                                    <img src="{{ $img }}" alt="{{ $dest->name }}" class="activity-thumb-img" loading="lazy">
                                </div>
                            @endif

                            <div class="activity-info-box">
                                <div class="activity-time-row">
                                    <i class="bi bi-clock"></i>
                                    <span>
                                        {{ $item->start_time ? substr($item->start_time, 0, 5) : '08:00' }}
                                        -
                                        {{ $item->end_time ? substr($item->end_time, 0, 5) : '10:00' }}
                                    </span>
                                </div>

                                <h3 class="activity-name-title">
                                    {{ $dest ? $dest->name : ($item->note ?: 'Hoạt động trải nghiệm') }}
                                </h3>

                                <div class="activity-meta-line">
                                    <span class="badge-cat-tag">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $cat }}
                                    </span>
                                    @if($cityName)
                                        <span>
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $cityName }}
                                        </span>
                                    @endif
                                    @if($dest && $dest->rating)
                                        <span>
                                            <i class="bi bi-star-fill text-warning me-1"></i>{{ number_format($dest->rating, 1) }}
                                        </span>
                                    @endif
                                </div>

                                @if($item->note)
                                    <div class="activity-note-box">
                                        <i class="bi bi-chat-left-text me-1"></i>
                                        <strong>Ghi chú:</strong> {{ $item->note }}
                                    </div>
                                @endif

                                <div class="activity-actions-bar">
                                    <div class="activity-price-tag">
                                        @if($dest && $dest->entrance_fee > 0)
                                            <i class="bi bi-ticket-perforated me-1"></i>{{ number_format($dest->entrance_fee, 0, ',', '.') }}₫ / vé
                                        @else
                                            <i class="bi bi-check-circle me-1"></i>Miễn phí tham quan
                                        @endif
                                    </div>

                                    <div class="activity-btns">
                                        <button type="button" class="btn-act-icon" title="Chỉnh sửa hoạt động">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn-act-icon" title="Xóa khỏi lịch trình" style="color: var(--danger);">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- 7. Empty State (Khi ngày được chọn chưa có hoạt động) -->
            <div class="empty-day-box">
                <div class="empty-icon-wrap">
                    <i class="bi bi-calendar-plus"></i>
                </div>
                <h3 class="empty-title">Chưa có hoạt động nào trong Ngày {{ $current_day }}</h3>
                <p class="empty-desc">
                    Lịch trình ngày này đang để trống. Bạn có thể thêm các địa điểm từ bộ sưu tập yêu thích hoặc thêm hoạt động mới để chuyến đi thêm trọn vẹn!
                </p>
                <a href="{{ route('favorites.index') }}" class="btn-add-activity-main">
                    <i class="bi bi-plus-lg"></i> Thêm hoạt động vào Ngày {{ $current_day }} ngay
                </a>
            </div>
        @endif
    </main>
</body>
</html>
