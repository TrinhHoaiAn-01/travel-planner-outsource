<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn Đặt Phòng Của Bạn - Travel Planner</title>
    <script>
        if (window.top !== window.self) {
            window.top.location.href = window.location.href;
        }
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #0066FF;
            --primary-dark: #0052CC;
            --primary-light: #EBF5FF;
            --secondary: #0F172A;
            --text-main: #334155;
            --text-muted: #64748B;
            --bg-page: #F8FAFC;
            --border-color: #E2E8F0;
            --border-light: #F1F5F9;
            --success: #10B981;
            --success-light: #E6F9F0;
            --danger: #EF4444;
            --danger-light: #FDE8E8;
            --radius-pill: 9999px;
            --radius-card: 16px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* 1. Header Navigation khớp 100% Hình 29 */
        header {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 32px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-weight: 800;
            font-size: 18px;
            color: var(--secondary);
            letter-spacing: -0.5px;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            color: #FFFFFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 12px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14.5px;
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover {
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .nav-link.active {
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-admin-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px;
            border-radius: var(--radius-pill);
            border: 1.5px solid var(--border-color);
            color: var(--secondary);
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            background: #FFFFFF;
            transition: all 0.15s ease;
        }

        .btn-admin-pill:hover {
            border-color: var(--secondary);
            background-color: #F8FAFC;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--secondary);
            font-weight: 600;
            font-size: 14px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* 2. Main Page Container */
        .main-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 24px 32px 60px 32px;
            width: 100%;
            flex: 1;
        }

        /* Breadcrumb chuẩn ảnh: Trang chủ / Lịch sử đặt phòng */
        .breadcrumb-nav {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .breadcrumb-nav a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-nav a:hover {
            text-decoration: underline;
        }

        .breadcrumb-nav .sep {
            color: #94A3B8;
        }

        .breadcrumb-nav .current {
            color: var(--text-muted);
        }

        /* Page Header Row: Tiêu đề + Nút Đặt phòng mới */
        .page-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--secondary);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .page-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .btn-new-booking {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--primary);
            color: #FFFFFF;
            font-size: 14.5px;
            font-weight: 600;
            padding: 10px 24px;
            border-radius: var(--radius-pill);
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.25);
            transition: all 0.2s ease;
        }

        .btn-new-booking:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 102, 255, 0.35);
        }

        /* 3. Filter Tabs (Khớp 100% Hình 29: Tất cả (3) | Sắp tới (Upcoming) | Đã hoàn thành | Đã hủy) */
        .filter-tabs-row {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 24px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .filter-tab-pill {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: var(--radius-pill);
            transition: all 0.15s ease;
            white-space: nowrap;
            color: var(--primary);
        }

        .filter-tab-pill:hover {
            color: var(--primary-dark);
            background-color: var(--primary-light);
        }

        .filter-tab-pill.active {
            background-color: var(--primary);
            color: #FFFFFF;
            box-shadow: 0 2px 6px rgba(0, 102, 255, 0.25);
        }

        /* 4. Table Card Container (Khớp 100% Hình 29) */
        .booking-table-card {
            background-color: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .booking-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .booking-table thead tr {
            background-color: #F8FAFC;
            border-bottom: 1px solid var(--border-color);
        }

        .booking-table th {
            padding: 18px 24px;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--secondary);
            white-space: nowrap;
        }

        .booking-table tbody tr {
            border-bottom: 1px solid var(--border-light);
            transition: background-color 0.15s ease;
        }

        .booking-table tbody tr:last-child {
            border-bottom: none;
        }

        .booking-table tbody tr:hover {
            background-color: #FAFCFF;
        }

        .booking-table td {
            padding: 20px 24px;
            font-size: 14px;
            color: var(--text-main);
            vertical-align: middle;
        }

        /* Các thành phần cột dữ liệu */
        .code-link {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: inline-block;
        }

        .code-link:hover {
            text-decoration: underline;
        }

        .text-sub-date {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .hotel-name {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 3px;
        }

        .hotel-location {
            font-size: 12.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .hotel-location i {
            font-size: 12px;
            color: #94A3B8;
        }

        .room-type-text {
            font-size: 14px;
            color: var(--text-main);
            font-weight: 500;
        }

        .dates-range {
            font-size: 13.5px;
            color: var(--secondary);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .nights-pill {
            display: inline-block;
            background-color: #F1F5F9;
            color: #475569;
            font-size: 11.5px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: var(--radius-pill);
            border: 1px solid #E2E8F0;
        }

        .room-count-text {
            font-size: 14px;
            color: var(--text-main);
            font-weight: 500;
        }

        .price-text {
            font-size: 15px;
            font-weight: 800;
            color: var(--secondary);
        }

        .price-text.price-active {
            color: var(--primary);
        }

        /* Status Badges */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: var(--radius-pill);
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-upcoming {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .badge-completed {
            background-color: var(--success-light);
            color: var(--success);
        }

        .badge-cancelled {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        /* Action Buttons */
        .action-buttons-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-pill-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 18px;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            background: #FFFFFF;
            cursor: pointer;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .btn-outline-blue {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-outline-blue:hover {
            background-color: var(--primary-light);
        }

        .btn-outline-red {
            border-color: #F87171;
            color: #EF4444;
        }

        .btn-outline-red:hover {
            background-color: var(--danger-light);
        }

        .btn-outline-gray {
            border-color: #94A3B8;
            color: #475569;
        }

        .btn-outline-gray:hover {
            background-color: #F1F5F9;
        }

        .btn-outline-dark {
            border-color: var(--border-color);
            color: var(--secondary);
            background-color: #F8FAFC;
        }

        .btn-outline-dark:hover {
            background-color: #E2E8F0;
        }

        /* Alerts */
        .alert-banner {
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-banner-success {
            background-color: #ECFDF5;
            border: 1px solid #10B981;
            color: #065F46;
        }

        .alert-banner-danger {
            background-color: #FEF2F2;
            border: 1px solid #EF4444;
            color: #991B1B;
        }

        /* Empty State */
        .empty-table-state {
            padding: 64px 24px;
            text-align: center;
        }

        .empty-icon {
            width: 56px;
            height: 56px;
            background-color: #F1F5F9;
            color: #94A3B8;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 6px;
        }

        .empty-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        /* Phân trang */
        .pagination-container {
            margin-top: 28px;
            display: flex;
            justify-content: center;
        }

        /* Footer */
        footer {
            background: #FFFFFF;
            border-top: 1px solid var(--border-color);
            padding: 24px;
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
            margin-top: auto;
        }
    </style>
</head>
<body>

    <!-- 1. Header Navigation -->
    <header>
        <div class="header-container">
            <a href="{{ route('home') }}" class="logo">
                <div class="logo-icon">
                    <i class="bi bi-compass"></i>
                </div>
                <span>TRAVEL PLANNER</span>
            </a>

            <ul class="nav-links">
                <li><a href="{{ route('home') }}" class="nav-link"><i class="bi bi-house"></i> Trang chủ</a></li>
                <li><a href="{{ route('destinations.index') }}" class="nav-link"><i class="bi bi-geo-alt"></i> Địa điểm</a></li>
                <li><a href="{{ route('trips.index') }}" class="nav-link"><i class="bi bi-journal-bookmark"></i> Chuyến đi</a></li>
                <li><a href="{{ route('favorites.index') }}" class="nav-link"><i class="bi bi-heart"></i> Yêu thích</a></li>
                <li><a href="{{ route('bookings.index') }}" class="nav-link active"><i class="bi bi-calendar-check"></i> Booking</a></li>
            </ul>

            <div class="nav-actions">
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn-admin-pill">
                            <i class="bi bi-shield-check"></i> Admin
                        </a>
                    @endif
                    <a href="{{ route('profile.show') }}" class="user-dropdown">
                        <div class="user-avatar">
                            @if(Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}">
                            @else
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <span>{{ Auth::user()->name }}</span>
                        <i class="bi bi-chevron-down" style="font-size: 12px; color: #94A3B8;"></i>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- 2. Main Container -->
    <main class="main-container">

        <!-- Breadcrumb: Trang chủ / Lịch sử đặt phòng -->
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span class="sep">/</span>
            <span class="current">Lịch sử đặt phòng</span>
        </nav>

        <!-- Tiêu đề trang & Nút Đặt phòng mới -->
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Đơn Đặt Phòng Của Bạn</h1>
                <p class="page-subtitle">Theo dõi thông tin chi tiết, trạng thái xác nhận và hóa đơn thanh toán các chuyến lưu trú</p>
            </div>
            <a href="{{ route('destinations.index') }}" class="btn-new-booking">
                <i class="bi bi-plus-lg"></i> Đặt phòng mới
            </a>
        </div>

        <!-- Thông báo Flash Messages -->
        @if(session('success'))
            <div class="alert-banner alert-banner-success" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert-banner alert-banner-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        @php
            $totalCount = Auth::user()->bookings()->count();
        @endphp

        <!-- 3. Bộ lọc Tabs (Khớp 100% Hình 29: Tất cả (3) | Sắp tới (Upcoming) | Đã hoàn thành | Đã hủy) -->
        <div class="filter-tabs-row" role="tablist">
            <a href="{{ route('bookings.index', ['status' => 'all']) }}" 
               class="filter-tab-pill {{ ($currentStatus === 'all' || !$currentStatus) ? 'active' : '' }}">
                Tất cả ({{ $totalCount }})
            </a>
            <a href="{{ route('bookings.index', ['status' => 'upcoming']) }}" 
               class="filter-tab-pill {{ in_array($currentStatus, ['upcoming', 'pending', 'confirmed']) ? 'active' : '' }}">
                Sắp tới (Upcoming)
            </a>
            <a href="{{ route('bookings.index', ['status' => 'completed']) }}" 
               class="filter-tab-pill {{ $currentStatus === 'completed' ? 'active' : '' }}">
                Đã hoàn thành
            </a>
            <a href="{{ route('bookings.index', ['status' => 'cancelled']) }}" 
               class="filter-tab-pill {{ $currentStatus === 'cancelled' ? 'active' : '' }}">
                Đã hủy
            </a>
        </div>

        <!-- 4. Bảng Dữ Liệu Đơn Đặt Phòng (Khớp 100% Hình 29) -->
        <div class="booking-table-card">
            @if($bookings->isNotEmpty())
                <div class="table-responsive">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Mã Booking</th>
                                <th>Khách sạn / Địa điểm</th>
                                <th>Hạng phòng</th>
                                <th>Thời gian</th>
                                <th>Số phòng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                                @php
                                    $roomType = $booking->roomType;
                                    $destination = $roomType->destination ?? null;
                                    $nights = max(1, \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)));
                                    $isUpcoming = in_array($booking->status, ['pending', 'confirmed']);
                                    $isFuture = $booking->check_in_date && \Carbon\Carbon::parse($booking->check_in_date)->isFuture();
                                @endphp
                                <tr>
                                    <!-- Cột 1: Mã Booking -->
                                    <td>
                                        <a href="{{ route('bookings.show', $booking->id) }}" class="code-link">
                                            {{ $booking->booking_code }}
                                        </a>
                                        <div class="text-sub-date">
                                            {{ $booking->created_at->format('d/m/Y') }}
                                        </div>
                                    </td>

                                    <!-- Cột 2: Khách sạn / Địa điểm -->
                                    <td>
                                        <div class="hotel-name">
                                            {{ $destination->name ?? 'Khách sạn lưu trú' }}
                                        </div>
                                        <div class="hotel-location">
                                            <i class="bi bi-geo-alt-fill"></i>
                                            {{ $destination->address ?? ($destination->city->name ?? 'Việt Nam') }}
                                        </div>
                                    </td>

                                    <!-- Cột 3: Hạng phòng -->
                                    <td>
                                        <div class="room-type-text">
                                            {{ $roomType->name ?? 'Hạng phòng tiêu chuẩn' }}
                                        </div>
                                    </td>

                                    <!-- Cột 4: Thời gian -->
                                    <td>
                                        <div class="dates-range">
                                            {{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}
                                        </div>
                                        <span class="nights-pill">{{ $nights }} đêm</span>
                                    </td>

                                    <!-- Cột 5: Số phòng -->
                                    <td>
                                        <span class="room-count-text">{{ $booking->room_count }} Phòng</span>
                                    </td>

                                    <!-- Cột 6: Tổng tiền -->
                                    <td>
                                        <span class="price-text {{ $isUpcoming ? 'price-active' : '' }}">
                                            {{ number_format($booking->total_price, 0, ',', '.') }}đ
                                        </span>
                                    </td>

                                    <!-- Cột 7: Trạng thái -->
                                    <td>
                                        @if($booking->status === 'completed')
                                            <span class="badge-status badge-completed">
                                                <i class="bi bi-check-circle"></i> Completed
                                            </span>
                                        @elseif($booking->status === 'cancelled')
                                            <span class="badge-status badge-cancelled">
                                                <i class="bi bi-x-circle"></i> Cancelled
                                            </span>
                                        @else
                                            <span class="badge-status badge-upcoming">
                                                <i class="bi bi-clock"></i> Upcoming
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Cột 8: Thao tác -->
                                    <td style="text-align: right;">
                                        <div class="action-buttons-group">
                                            @if($isUpcoming)
                                                <a href="{{ route('bookings.show', $booking->id) }}" class="btn-pill-action btn-outline-blue">
                                                    Chi tiết
                                                </a>
                                                @if($booking->status !== 'cancelled' && $isFuture)
                                                    <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn đặt phòng {{ $booking->booking_code }} không?')" style="display:inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn-pill-action btn-outline-red">
                                                            Hủy
                                                        </button>
                                                    </form>
                                                @endif
                                            @elseif($booking->status === 'completed')
                                                <a href="{{ route('bookings.show', $booking->id) }}" class="btn-pill-action btn-outline-gray">
                                                    Hóa đơn
                                                </a>
                                            @else
                                                <a href="{{ $destination ? route('destinations.show', $destination->slug) : route('destinations.index') }}" class="btn-pill-action btn-outline-dark">
                                                    Xem lại
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-table-state">
                    <div class="empty-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h3 class="empty-title">Chưa có đơn đặt phòng nào</h3>
                    <p class="empty-desc">Bạn chưa có đơn đặt phòng lưu trú nào trong danh mục này.</p>
                    <a href="{{ route('destinations.index') }}" class="btn-new-booking">
                        <i class="bi bi-compass"></i> Khám phá nơi lưu trú
                    </a>
                </div>
            @endif
        </div>

        <!-- Phân trang -->
        @if($bookings->hasPages())
            <div class="pagination-container">
                {{ $bookings->appends(['status' => $currentStatus])->links() }}
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; {{ date('Y') }} Travel Planner Travel Platform. All rights reserved.</p>
    </footer>

</body>
</html>
