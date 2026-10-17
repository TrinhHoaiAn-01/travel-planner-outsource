<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn đặt phòng {{ $booking->booking_code }} - Travel Planner</title>
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
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08);
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

        /* Header Navbar */
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

        .nav-link:hover, .nav-link.active {
            color: var(--primary);
            background: var(--primary-light);
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 32px 24px 64px;
            flex: 1;
            width: 100%;
        }

        /* Breadcrumbs */
        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .breadcrumbs a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumbs a:hover {
            color: var(--primary);
        }

        /* Page Banner Card */
        .booking-banner-card {
            background: #FFFFFF;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 28px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .booking-code-large {
            font-family: monospace;
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
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

        .booking-time-placed {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Detail Sections Grid */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 24px;
        }

        .content-card {
            background: #FFFFFF;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 28px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
        }

        .card-header-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--secondary);
        }

        /* Hotel Info Card */
        .hotel-summary-box {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
        }

        .hotel-img {
            width: 140px;
            height: 100px;
            border-radius: var(--radius-md);
            object-fit: cover;
            background: #F1F5F9;
        }

        .hotel-name {
            font-size: 20px;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 4px;
        }

        .hotel-address {
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
        }

        /* Info Key-Value Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            border-bottom: 1px solid #F1F5F9;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table td {
            padding: 12px 0;
            font-size: 14px;
        }

        .info-label {
            color: var(--text-muted);
            width: 40%;
            font-weight: 500;
        }

        .info-value {
            color: var(--secondary);
            font-weight: 600;
        }

        /* Summary Price Card (Right Column) */
        .price-breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
            color: var(--text-main);
        }

        .price-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            margin-top: 14px;
            border-top: 2px solid var(--border-color);
        }

        .price-total-label {
            font-size: 16px;
            font-weight: 700;
            color: var(--secondary);
        }

        .price-total-value {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
        }

        .cancellation-note {
            background: #F8FAFC;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            padding: 14px;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        /* Actions Bar */
        .actions-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-action-primary {
            background: var(--primary);
            color: #FFFFFF;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 14px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-action-primary:hover {
            background: var(--primary-dark);
        }

        .btn-action-secondary {
            background: #FFFFFF;
            border: 1.5px solid var(--border-color);
            color: var(--secondary);
            text-decoration: none;
            padding: 12px 20px;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 14px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-action-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .btn-action-danger {
            background: #FEF2F2;
            border: 1.5px solid #FECACA;
            color: var(--danger);
            padding: 12px 20px;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-action-danger:hover {
            background: #FEE2E2;
            border-color: var(--danger);
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
        }

        @media (max-width: 800px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
            .hotel-summary-box {
                flex-direction: column;
            }
            .hotel-img {
                width: 100%;
                height: 180px;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
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

    <!-- Main Container -->
    <main class="main-container">

        <!-- Breadcrumbs -->
        <div class="breadcrumbs">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>&rsaquo;</span>
            <a href="{{ route('bookings.index') }}">Quản lý Booking</a>
            <span>&rsaquo;</span>
            <span style="color: var(--secondary); font-weight: 600;">Đơn đặt {{ $booking->booking_code }}</span>
        </div>

        <!-- Flash Message -->
        @if(session('success'))
            <div class="alert-success">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @php
            $roomType = $booking->roomType;
            $destination = $roomType->destination ?? null;
            $nights = max(1, \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)));
        @endphp

        <!-- Banner Card -->
        <div class="booking-banner-card">
            <div>
                <div class="booking-code-large">
                    <span>Mã Đơn: {{ $booking->booking_code }}</span>
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
                </div>
                <div class="booking-time-placed">
                    Thời gian đặt: {{ $booking->created_at->format('d/m/Y - H:i:s') }}
                </div>
            </div>

            <div>
                <button type="button" class="btn-action-secondary" onclick="window.print()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    In Xác Nhận Đơn
                </button>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="details-grid">

            <!-- Cột Trái: Chi tiết khách sạn & Khách hàng -->
            <div>
                <!-- 1. Thông tin lưu trú & Phòng nghỉ -->
                <div class="content-card">
                    <div class="card-header-line">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <h2 class="card-title">Thông Tin Cơ Sở Lưu Trú & Hạng Phòng</h2>
                    </div>

                    @if($destination)
                        <div class="hotel-summary-box">
                            <img src="{{ $destination->primaryImage->image_url ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=600&q=80' }}" 
                                 alt="{{ $destination->name }}" 
                                 class="hotel-img">
                            <div>
                                <h3 class="hotel-name">{{ $destination->name }}</h3>
                                <div class="hotel-address">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <span>{{ $destination->address ?? 'Khu du lịch sinh thái' }}, {{ $destination->city->name ?? 'Việt Nam' }}</span>
                                </div>
                                <a href="{{ route('destinations.show', $destination->slug) }}" style="font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 600;">
                                    Xem trang địa điểm &rsaquo;
                                </a>
                            </div>
                        </div>
                    @endif

                    <table class="info-table">
                        <tr>
                            <td class="info-label">Hạng phòng đặt</td>
                            <td class="info-value">{{ $roomType->name ?? 'Hạng phòng tiêu chuẩn' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Sức chứa mỗi phòng</td>
                            <td class="info-value">{{ $roomType->capacity ?? 2 }} người lớn</td>
                        </tr>
                        <tr>
                            <td class="info-label">Số lượng phòng đặt</td>
                            <td class="info-value">{{ $booking->room_count }} phòng</td>
                        </tr>
                        <tr>
                            <td class="info-label">Thời gian lưu trú</td>
                            <td class="info-value">
                                {{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }} 
                                &rarr; 
                                {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }} 
                                <strong>({{ $nights }} đêm)</strong>
                            </td>
                        </tr>
                        @if($booking->trip)
                            <tr>
                                <td class="info-label">Chuyến đi liên kết</td>
                                <td class="info-value">
                                    <a href="{{ route('trips.show', $booking->trip->id) }}" style="color: var(--primary); text-decoration: none;">
                                        {{ $booking->trip->title }} (Xem lịch trình)
                                    </a>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>

                <!-- 2. Thông tin người đặt phòng -->
                <div class="content-card">
                    <div class="card-header-line">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <h2 class="card-title">Thông Tin Khách Hàng Nhận Phòng</h2>
                    </div>

                    <table class="info-table">
                        <tr>
                            <td class="info-label">Họ và tên</td>
                            <td class="info-value">{{ $booking->user->name }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Địa chỉ email</td>
                            <td class="info-value">{{ $booking->user->email }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Số điện thoại liên hệ</td>
                            <td class="info-value">{{ $booking->user->phone ?? '0901 234 567' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Ghi chú đặc biệt</td>
                            <td class="info-value" style="color: var(--text-muted); font-weight: 400;">Không có yêu cầu thêm</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Cột Phải: Tóm tắt chi phí & Nút hành động -->
            <div>
                <div class="content-card">
                    <div class="card-header-line">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                        <h2 class="card-title">Chi Phí Đặt Phòng</h2>
                    </div>

                    <div class="price-breakdown-row">
                        <span>Giá mỗi đêm</span>
                        <span>{{ number_format($roomType->price_per_night ?? 0, 0, ',', '.') }}đ</span>
                    </div>

                    <div class="price-breakdown-row">
                        <span>Số đêm lưu trú</span>
                        <span>{{ $nights }} đêm</span>
                    </div>

                    <div class="price-breakdown-row">
                        <span>Số lượng phòng</span>
                        <span>{{ $booking->room_count }} phòng</span>
                    </div>

                    <div class="price-breakdown-row">
                        <span>Thuế & phí dịch vụ</span>
                        <span style="color: var(--success); font-weight: 600;">Đã bao gồm</span>
                    </div>

                    <div class="price-total-row">
                        <span class="price-total-label">Tổng thanh toán</span>
                        <span class="price-total-value">{{ number_format($booking->total_price, 0, ',', '.') }}đ</span>
                    </div>

                    <div class="cancellation-note">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span>Miễn phí hủy phòng nếu hủy trước ngày nhận phòng ({{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }}).</span>
                    </div>

                    <div class="actions-stack">
                        <a href="{{ route('bookings.index') }}" class="btn-action-secondary">
                            &larr; Quay lại danh sách Booking
                        </a>

                        @if($booking->trip)
                            <a href="{{ route('trips.show', $booking->trip->id) }}" class="btn-action-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                Xem trong Lịch trình Trip
                            </a>
                        @endif

                        @if($booking->status !== 'cancelled' && $booking->check_in_date && \Carbon\Carbon::parse($booking->check_in_date)->isFuture())
                            <form action="{{ route('bookings.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn đặt phòng {{ $booking->booking_code }} không?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-action-danger">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                    Hủy Đơn Đặt Phòng
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer>
        <p>© 2026 Travel Planner - Đồ Án Chuyên Đề Phát Triển Web. Nhóm B (Nguyễn Văn Thắng - Booking & Payment Module).</p>
    </footer>

</body>
</html>
