@extends('layouts.admin')

@section('title', 'Bảng điều khiển (Dashboard)')
@section('breadcrumb', 'Bảng điều khiển (Dashboard)')

@push('styles')
<style>
    /* Card Styles */
    .dashboard-card {
        background-color: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
        border-color: #CBD5E1;
    }

    /* Grid for 4 Top Stat Cards */
    .stats-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 1200px) {
        .stats-grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .stats-grid-4 {
            grid-template-columns: 1fr;
        }
    }

    .stat-card-inner {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    .stat-label-text {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .stat-value-text {
        font-size: 26px;
        font-weight: 700;
        color: var(--text-dark);
        line-height: 1.2;
        margin-bottom: 6px;
    }

    .stat-subtext {
        font-size: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-subtext.positive {
        color: #16A34A;
    }

    .stat-subtext.info {
        color: #0284C7;
    }

    .stat-subtext.warning-link {
        color: #D97706;
        text-decoration: none;
        font-weight: 600;
    }

    .stat-subtext.warning-link:hover {
        text-decoration: underline;
    }

    .stat-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .dashboard-card:hover .stat-icon-box {
        transform: scale(1.06);
    }

    .stat-icon-box.users {
        background-color: #EFF6FF;
        color: #2563EB;
    }

    .stat-icon-box.trips {
        background-color: #E0F2FE;
        color: #0284C7;
    }

    .stat-icon-box.destinations {
        background-color: #DCFCE7;
        color: #16A34A;
    }

    .stat-icon-box.reviews {
        background-color: #FEF3C7;
        color: #D97706;
    }

    /* Row 2: Charts and Top Destinations */
    .dashboard-mid-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 1024px) {
        .dashboard-mid-row {
            grid-template-columns: 1fr;
        }
    }

    .card-header-flex {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .card-title-lg {
        font-size: 15.5px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .card-subtitle-sm {
        font-size: 12.5px;
        color: var(--text-muted);
    }

    .badge-year-pill {
        background-color: #EFF6FF;
        color: #2563EB;
        font-size: 11.5px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #DBEAFE;
    }

    /* Bar Chart Component */
    .bar-chart-container {
        padding-top: 10px;
    }

    .bar-chart-bars-wrap {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        height: 190px;
        padding-bottom: 8px;
        border-bottom: 1px solid #F1F5F9;
        gap: 10px;
    }

    .bar-col-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        justify-content: flex-end;
    }

    .bar-rect-visual {
        width: 100%;
        max-width: 28px;
        background: linear-gradient(180deg, #38BDF8 0%, #2563EB 100%);
        border-radius: 4px 4px 0 0;
        transition: height 0.4s ease, filter 0.2s ease, transform 0.2s ease;
        cursor: pointer;
        position: relative;
    }

    .bar-rect-visual:hover {
        filter: brightness(1.12);
        transform: scaleY(1.02);
        transform-origin: bottom;
    }

    .bar-rect-visual::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background-color: #0F172A;
        color: #FFFFFF;
        font-size: 11px;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 6px;
        white-space: nowrap;
        z-index: 20;
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.15s ease, transform 0.15s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
    }

    .bar-rect-visual:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    .bar-label-month {
        font-size: 11.5px;
        font-weight: 500;
        color: var(--text-muted);
        margin-top: 8px;
    }

    .chart-footer-legend {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 14px;
        font-size: 12px;
    }

    .legend-item-left {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-dark);
        font-weight: 500;
    }

    .legend-color-box {
        width: 10px;
        height: 10px;
        background: linear-gradient(180deg, #38BDF8 0%, #2563EB 100%);
        border-radius: 2px;
    }

    .legend-peak-right {
        color: var(--text-muted);
    }

    /* Top Destinations Ranking */
    .top-destinations-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 22px;
    }

    .destination-rank-row {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 4px 6px;
        border-radius: 6px;
        transition: background-color 0.15s ease;
    }

    .destination-rank-row:hover {
        background-color: #F8FAFC;
    }

    .dest-row-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--text-dark);
    }

    .dest-percentage-val {
        font-weight: 700;
        color: var(--text-dark);
    }

    .progress-track-bar {
        width: 100%;
        height: 7px;
        background-color: #F1F5F9;
        border-radius: 9999px;
        overflow: hidden;
    }

    .progress-fill-bar {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .btn-manage-destinations {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 9px 16px;
        border: 1px solid var(--border);
        border-radius: 9999px;
        background-color: #FFFFFF;
        color: #2563EB;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-manage-destinations:hover {
        background-color: #EFF6FF;
        border-color: #BFDBFE;
    }

    /* Row 3: Recent Bookings Table */
    .table-container-card {
        background-color: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.2s ease;
    }

    .table-header-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .table-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .table-view-all-link {
        font-size: 13px;
        color: #2563EB;
        text-decoration: none;
        font-weight: 500;
    }

    .table-view-all-link:hover {
        text-decoration: underline;
    }

    .modern-data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }

    .modern-data-table th {
        text-align: left;
        padding: 12px 14px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 1px solid #F1F5F9;
    }

    .modern-data-table td {
        padding: 16px 14px;
        border-bottom: 1px solid #F8FAFC;
        vertical-align: middle;
        color: var(--text-dark);
    }

    .modern-data-table tbody tr {
        transition: background-color 0.15s ease;
    }

    .modern-data-table tbody tr:hover td {
        background-color: #F8FAFC;
    }

    .modern-data-table tr:last-child td {
        border-bottom: none;
    }

    .booking-code-link {
        color: #2563EB;
        text-decoration: none;
        font-weight: 600;
    }

    .booking-code-link:hover {
        text-decoration: underline;
    }

    .price-bold-text {
        font-weight: 700;
        color: var(--text-dark);
    }

    /* Badges */
    .status-badge-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.2;
    }

    .badge-status-confirmed {
        background-color: #DCFCE7;
        color: #16A34A;
    }

    .badge-status-upcoming {
        background-color: #E0F2FE;
        color: #0284C7;
    }

    .badge-status-pending {
        background-color: #FEF3C7;
        color: #D97706;
    }
</style>
@endpush

@section('content')
<!-- Row 1: 4 Stat Cards -->
<div class="stats-grid-4">
    <!-- Card 1: Tổng người dùng -->
    <div class="dashboard-card">
        <div class="stat-card-inner">
            <div>
                <div class="stat-label-text">TỔNG NGƯỜI DÙNG</div>
                <div class="stat-value-text">{{ $summary['total_users'] }}</div>
                <div class="stat-subtext positive">
                    <span>↗ {{ $summary['user_growth'] }}</span>
                </div>
            </div>
            <div class="stat-icon-box users">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Card 2: Tổng chuyến đi -->
    <div class="dashboard-card">
        <div class="stat-card-inner">
            <div>
                <div class="stat-label-text">TỔNG CHUYẾN ĐI (TRIPS)</div>
                <div class="stat-value-text">{{ $summary['total_trips'] }}</div>
                <div class="stat-subtext positive">
                    <span>↗ {{ $summary['trip_growth'] }}</span>
                </div>
            </div>
            <div class="stat-icon-box trips">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="14" x="2" y="7" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Card 3: Tổng địa điểm -->
    <div class="dashboard-card">
        <div class="stat-card-inner">
            <div>
                <div class="stat-label-text">TỔNG ĐỊA ĐIỂM</div>
                <div class="stat-value-text">{{ $summary['total_destinations'] }}</div>
                <div class="stat-subtext info">
                    <span>📍 {{ $summary['destination_note'] }}</span>
                </div>
            </div>
            <div class="stat-icon-box destinations">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Card 4: Đánh giá chờ duyệt -->
    <div class="dashboard-card">
        <div class="stat-card-inner">
            <div>
                <div class="stat-label-text">ĐÁNH GIÁ CHỜ DUYỆT</div>
                <div class="stat-value-text" style="color: #D97706;">{{ $summary['pending_reviews'] }}</div>
                <div class="stat-subtext">
                    <a href="{{ route('admin.reviews.index') }}" class="stat-subtext warning-link">Xem và phê duyệt →</a>
                </div>
            </div>
            <div class="stat-icon-box reviews">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Charts and Top Destinations -->
<div class="dashboard-mid-row">
    <!-- Cột trái: Biểu đồ Thống kê -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title-lg">Thống Kê Lượng Đặt Chuyến Đi & Booking ({{ $monthlyStats['year'] }})</h2>
                <p class="card-subtitle-sm">Số lượt tạo lịch trình theo từng tháng</p>
            </div>
            <span class="badge-year-pill">Biểu đồ năm {{ $monthlyStats['year'] }}</span>
        </div>

        <div class="bar-chart-container">
            <div class="bar-chart-bars-wrap">
                @foreach($monthlyStats['months'] as $month)
                    <div class="bar-col-item">
                        <div class="bar-rect-visual" 
                             style="height: {{ $month['height'] }};" 
                             data-tooltip="{{ $month['label'] }}: {{ $month['value'] }} lượt">
                        </div>
                        <span class="bar-label-month">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="chart-footer-legend">
                <div class="legend-item-left">
                    <span class="legend-color-box"></span>
                    <span>Số lượt đăng ký chuyến đi</span>
                </div>
                <div class="legend-peak-right">
                    Đỉnh điểm: {{ $monthlyStats['peak_info'] }}
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: Top Điểm Đến Phổ Biến -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title-lg">Top Điểm Đến Phổ Biến</h2>
            </div>
        </div>

        <div class="top-destinations-list">
            @foreach($topDestinations as $item)
                <div class="destination-rank-row">
                    <div class="dest-row-header">
                        <span>{{ $item['rank'] }}. {{ $item['name'] }}</span>
                        <span class="dest-percentage-val">{{ $item['percentage'] }}%</span>
                    </div>
                    <div class="progress-track-bar">
                        <div class="progress-fill-bar" style="width: {{ $item['percentage'] }}%; background-color: {{ $item['color'] }};"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <a href="{{ route('admin.destinations.index') }}" class="btn-manage-destinations">
            <span>Quản lý Địa điểm →</span>
        </a>
    </div>
</div>

<!-- Row 3: Đơn Đặt Phòng & Yêu Cầu Mới Nhất -->
<div class="table-container-card">
    <div class="table-header-flex">
        <h2 class="table-title">Đơn Đặt Phòng & Yêu Cầu Mới Nhất</h2>
        <a href="{{ route('admin.bookings.index') }}" class="table-view-all-link">Xem tất cả</a>
    </div>

    <div style="overflow-x: auto;">
        <table class="modern-data-table">
            <thead>
                <tr>
                    <th>Mã Booking</th>
                    <th>Khách hàng</th>
                    <th>Địa điểm / Khách sạn</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $booking)
                    <tr>
                        <td>
                            <a href="{{ route('admin.bookings.show', ['booking' => $booking['booking_code']]) }}" class="booking-code-link">
                                {{ $booking['booking_code'] }}
                            </a>
                        </td>
                        <td>{{ $booking['customer_name'] }}</td>
                        <td>{{ $booking['destination_name'] }}</td>
                        <td>{{ $booking['booking_date'] }}</td>
                        <td class="price-bold-text">{{ $booking['formatted_price'] }}</td>
                        <td>
                            @if(strtolower($booking['status']) === 'confirmed')
                                <span class="status-badge-pill badge-status-confirmed">Confirmed</span>
                            @elseif(strtolower($booking['status']) === 'upcoming')
                                <span class="status-badge-pill badge-status-upcoming">Upcoming</span>
                            @else
                                <span class="status-badge-pill badge-status-pending">Pending</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Chưa có đơn đặt phòng nào gần đây.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
