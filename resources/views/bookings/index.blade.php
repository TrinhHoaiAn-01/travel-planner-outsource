<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn đặt phòng - Travel Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0066FF;
            --primary-dark: #0052CC;
            --primary-light: #EBF3FF;
            --secondary: #0F172A;
            --text-main: #334155;
            --text-muted: #64748B;
            --bg-body: #F8FAFC;
            --bg-card: #FFFFFF;
            --border-color: #E2E8F0;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.03);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Header */
        header {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
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
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #0066FF, #00C6FF);
            color: #FFFFFF;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover {
            color: var(--primary);
            background: var(--primary-light);
        }

        .nav-link.active {
            color: var(--primary);
            background: var(--primary-light);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px;
            background: #F1F5F9;
            border-radius: 30px;
            text-decoration: none;
            color: var(--secondary);
            font-weight: 600;
            font-size: 14px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px 64px;
            flex: 1;
            width: 100%;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--secondary);
            letter-spacing: -0.5px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-subtitle {
            font-size: 14px;
            color: var(--text-muted);
        }

        .btn-new-booking {
            background: linear-gradient(135deg, #0066FF, #0052CC);
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 22px;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(0, 102, 255, 0.3);
            transition: all 0.2s;
        }

        .btn-new-booking:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 102, 255, 0.4);
        }

        /* Alerts */
        .alert-success {
            background: #ECFDF5;
            border: 1px solid #10B981;
            color: #065F46;
            padding: 14px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
        }

        .alert-error {
            background: #FEF2F2;
            border: 1px solid #EF4444;
            color: #991B1B;
            padding: 14px 20px;
            border-radius: var(--radius-md);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        /* Filter Tabs */
        .filter-tabs-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #FFFFFF;
            padding: 8px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            margin-bottom: 24px;
            overflow-x: auto;
        }

        .filter-tab {
            text-decoration: none;
            padding: 8px 18px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            color: var(--text-muted);
            transition: all 0.2s;
            white-space: nowrap;
        }

        .filter-tab:hover {
            color: var(--secondary);
            background: #F1F5F9;
        }

        .filter-tab.active {
            background: var(--primary);
            color: #FFFFFF;
        }

        /* Booking Cards Grid */
        .bookings-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .booking-card {
            background: #FFFFFF;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 24px;
            display: grid;
            grid-template-columns: 200px 1fr auto;
            gap: 24px;
            align-items: center;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
        }

        .booking-card:hover {
            box-shadow: var(--shadow-md);
            border-color: #CBD5E1;
        }

        .booking-thumb {
            width: 100%;
            height: 140px;
            border-radius: var(--radius-md);
            object-fit: cover;
            background: #F1F5F9;
        }

        .booking-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .booking-header-line {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 4px;
        }

        .booking-code {
            font-family: monospace;
            font-weight: 800;
            font-size: 15px;
            color: var(--primary);
            background: var(--primary-light);
            padding: 4px 10px;
            border-radius: 6px;
        }

        .booking-date-placed {
            font-size: 12px;
            color: var(--text-muted);
        }

        .booking-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .booking-title:hover {
            color: var(--primary);
        }

        .booking-room-type {
            font-size: 14px;
            color: var(--text-main);
            font-weight: 600;
        }

        .booking-meta-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .booking-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge-trip-linked {
            background: #F3E8FF;
            color: #7E22CE;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.pending {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-badge.confirmed {
            background: #E0E7FF;
            color: #3730A3;
        }

        .status-badge.completed {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-badge.cancelled {
            background: #FEE2E2;
            color: #991B1B;
        }

        /* Right Actions Column */
        .booking-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
            min-width: 180px;
        }

        .booking-price-tag {
            text-align: right;
        }

        .booking-price-sub {
            font-size: 12px;
            color: var(--text-muted);
        }

        .booking-price-val {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
        }

        .btn-view-detail {
            background: #FFFFFF;
            border: 1.5px solid var(--border-color);
            color: var(--secondary);
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            width: 100%;
            justify-content: center;
        }

        .btn-view-detail:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .btn-cancel-booking {
            background: none;
            border: 1px solid #FECACA;
            color: var(--danger);
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-cancel-booking:hover {
            background: #FEE2E2;
        }

        /* Empty State */
        .empty-state {
            background: #FFFFFF;
            border-radius: var(--radius-lg);
            border: 1px dashed var(--border-color);
            padding: 60px 24px;
            text-align: center;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            background: #F1F5F9;
            color: #94A3B8;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 8px;
        }

        .empty-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        /* Pagination */
        .pagination-wrap {
            margin-top: 32px;
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
        }

        @media (max-width: 900px) {
            .booking-card {
                grid-template-columns: 1fr;
            }
            .booking-actions {
                align-items: flex-start;
                width: 100%;
            }
            .booking-price-tag {
                text-align: left;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="header-container">
            <a href="{{ route('home') }}" class="logo">
                <div class="logo-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                </div>
                <span>TRAVEL PLANNER</span>
            </a>

            <ul class="nav-links">
                <li><a href="{{ route('home') }}" class="nav-link">Trang chủ</a></li>
                <li><a href="{{ route('destinations.index') }}" class="nav-link">Địa điểm</a></li>
                <li><a href="{{ route('trips.index') }}" class="nav-link">Chuyến đi</a></li>
                <li><a href="{{ route('favorites.index') }}" class="nav-link">Yêu thích</a></li>
                <li><a href="{{ route('bookings.index') }}" class="nav-link active">Booking</a></li>
            </ul>

            <div class="nav-actions">
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="nav-link" style="color: #7E22CE; background: #F3E8FF;">Admin</a>
                    @endif
                    <a href="{{ route('profile.show') }}" class="user-dropdown">
                        <div class="user-avatar">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-container">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2.2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                    Quản Lý Đơn Đặt Phòng
                </h1>
                <p class="page-subtitle">Theo dõi trạng thái, chi tiết các đơn đặt phòng lưu trú và quản lý đặt phòng cá nhân</p>
            </div>
            <a href="{{ route('destinations.index') }}" class="btn-new-booking">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Đặt Phòng Mới
            </a>
        </div>

        <!-- Thông báo Flash Messages -->
        @if(session('success'))
            <div class="alert-success">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Filter Tabs (Hình 29 trong Báo cáo) -->
        <div class="filter-tabs-bar">
            <a href="{{ route('bookings.index', ['status' => 'all']) }}" class="filter-tab {{ $currentStatus === 'all' ? 'active' : '' }}">
                Tất cả ({{ Auth::user()->bookings()->count() }})
            </a>
            <a href="{{ route('bookings.index', ['status' => 'pending']) }}" class="filter-tab {{ $currentStatus === 'pending' ? 'active' : '' }}">
                Chờ xác nhận
            </a>
            <a href="{{ route('bookings.index', ['status' => 'confirmed']) }}" class="filter-tab {{ $currentStatus === 'confirmed' ? 'active' : '' }}">
                Đã xác nhận
            </a>
            <a href="{{ route('bookings.index', ['status' => 'completed']) }}" class="filter-tab {{ $currentStatus === 'completed' ? 'active' : '' }}">
                Đã hoàn thành
            </a>
            <a href="{{ route('bookings.index', ['status' => 'cancelled']) }}" class="filter-tab {{ $currentStatus === 'cancelled' ? 'active' : '' }}">
                Đã hủy
            </a>
        </div>

        <!-- Bookings List -->
        @if($bookings->isNotEmpty())
            <div class="bookings-list">
                @foreach($bookings as $booking)
                    @php
                        $roomType = $booking->roomType;
                        $destination = $roomType->destination ?? null;
                        $nights = max(1, \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)));
                    @endphp
                    <div class="booking-card">
                        <!-- Thumbnail Khách sạn -->
                        <div>
                            <img src="{{ $destination->primaryImage->image_url ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80' }}" 
                                 alt="{{ $destination->name ?? 'Khách sạn' }}" 
                                 class="booking-thumb">
                        </div>

                        <!-- Thông tin chi tiết đơn -->
                        <div class="booking-info">
                            <div class="booking-header-line">
                                <span class="booking-code">{{ $booking->booking_code }}</span>
                                <span class="status-badge {{ $booking->status }}">
                                    @if($booking->status === 'pending')
                                        Chờ xác nhận
                                    @elseif($booking->status === 'confirmed')
                                        Đã xác nhận
                                    @elseif($booking->status === 'completed')
                                        Đã hoàn thành
                                    @elseif($booking->status === 'cancelled')
                                        Đã hủy
                                    @else
                                        {{ ucfirst($booking->status) }}
                                    @endif
                                </span>
                                <span class="booking-date-placed">Ngày đặt: {{ $booking->created_at->format('d/m/Y H:i') }}</span>
                            </div>

                            @if($destination)
                                <a href="{{ route('destinations.show', $destination->slug) }}" class="booking-title">
                                    {{ $destination->name }}
                                    <span style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
                                        ({{ $destination->city->name ?? 'Việt Nam' }})
                                    </span>
                                </a>
                            @else
                                <div class="booking-title">Khách sạn lưu trú</div>
                            @endif

                            <div class="booking-room-type">
                                🛏️ {{ $roomType->name ?? 'Hạng phòng tiêu chuẩn' }} 
                                <span style="font-size: 13px; color: var(--text-muted); font-weight: 400;">
                                    · Sức chứa {{ $roomType->capacity ?? 2 }} khách
                                </span>
                            </div>

                            <div class="booking-meta-row">
                                <div class="booking-meta-item">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                                    <span>
                                        {{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }} 
                                        &rarr; 
                                        {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}
                                        <strong>({{ $nights }} đêm)</strong>
                                    </span>
                                </div>

                                <div class="booking-meta-item">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                    <span>Số lượng: <strong>{{ $booking->room_count }} phòng</strong></span>
                                </div>

                                @if($booking->trip)
                                    <a href="{{ route('trips.show', $booking->trip->id) }}" class="badge-trip-linked" style="text-decoration: none;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                        {{ $booking->trip->title }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Cột Thao Tác & Giá Tiền -->
                        <div class="booking-actions">
                            <div class="booking-price-tag">
                                <div class="booking-price-sub">Tổng tiền đặt phòng</div>
                                <div class="booking-price-val">{{ number_format($booking->total_price, 0, ',', '.') }}đ</div>
                            </div>

                            <a href="{{ route('bookings.show', $booking->id) }}" class="btn-view-detail">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                Xem chi tiết
                            </a>

                            @if($booking->status !== 'cancelled' && $booking->check_in_date && \Carbon\Carbon::parse($booking->check_in_date)->isFuture())
                                <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn đặt phòng {{ $booking->booking_code }} không?')" style="width: 100%;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-cancel-booking">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                        Hủy đặt phòng
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Phân trang -->
            <div class="pagination-wrap">
                {{ $bookings->appends(['status' => $currentStatus])->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                </div>
                <h3 class="empty-title">Chưa có đơn đặt phòng nào</h3>
                <p class="empty-desc">
                    @if($currentStatus !== 'all')
                        Không tìm thấy đơn đặt phòng nào ở trạng thái "{{ $currentStatus }}".
                    @else
                        Bạn chưa đặt phòng lưu trú nào. Hãy khám phá các khách sạn và điểm đến tuyệt vời ngay!
                    @endif
                </p>
                <a href="{{ route('destinations.index') }}" class="btn-new-booking">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                    Khám phá điểm đến ngay
                </a>
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer>
        <p>© 2026 Travel Planner - Đồ Án Chuyên Đề Phát Triển Web. Nhóm B (Nguyễn Văn Thắng - Booking & Payment Module).</p>
    </footer>

</body>
</html>
