@extends('layouts.admin')

@section('title', 'Bảng điều khiển (Dashboard)')
@section('breadcrumb', 'Bảng điều khiển (Dashboard)')

@push('styles')
<style>
    /* Bảng màu và thiết kế thẻ Card chuẩn Dashboard */
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

    /* Thanh Chào Mừng và Bộ Lọc Header trên Dashboard */
    .dashboard-welcome-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .welcome-title-group h1 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text-dark);
        letter-spacing: -0.01em;
        margin-bottom: 4px;
    }

    .welcome-subtitle {
        font-size: 13px;
        color: var(--text-muted);
    }

    .welcome-actions-group {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .date-filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #FFFFFF;
        border: 1px solid var(--border);
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-dark);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    .btn-export-report {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #16A34A;
        color: #FFFFFF;
        border: none;
        padding: 7px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.15s ease;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(22, 163, 74, 0.25);
    }

    .btn-export-report:hover {
        background-color: #15803D;
        transform: translateY(-1px);
    }

    /* Grid 4 Thẻ KPI Thống Kê Đầu Trang */
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

    .stat-subtext.warning {
        color: #D97706;
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

    .stat-icon-box.destinations {
        background-color: #E0F2FE;
        color: #0284C7;
    }

    .stat-icon-box.bookings {
        background-color: #DCFCE7;
        color: #16A34A;
    }

    .stat-icon-box.revenue {
        background-color: #FEF3C7;
        color: #D97706;
    }

    /* Hàng 2: Biểu Đồ Thống Kê Tháng & Tỷ Lệ Đặt Phòng */
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

    .filter-dropdown-select {
        background-color: #FFFFFF;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 500;
        color: var(--text-dark);
        cursor: pointer;
        outline: none;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }

    /* Biểu Đồ Cột Bar Chart */
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

    .legend-growth-right {
        color: #16A34A;
        font-weight: 500;
    }

    /* Danh Sách Tỷ Lệ Đặt Phòng Theo Loại */
    .top-categories-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-bottom: 22px;
    }

    .category-rank-row {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 4px 6px;
        border-radius: 6px;
        transition: background-color 0.15s ease;
    }

    .category-rank-row:hover {
        background-color: #F8FAFC;
    }

    .cat-row-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--text-dark);
    }

    .cat-percentage-val {
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

    .btn-view-details-pill {
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

    .btn-view-details-pill:hover {
        background-color: #EFF6FF;
        border-color: #BFDBFE;
    }

    /* Hàng 3: Bảng Giao Dịch Gần Đây */
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

    /* Huy hiệu trạng thái giao dịch */
    .status-badge-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.2;
    }

    .badge-status-confirmed {
        background-color: #DCFCE7;
        color: #16A34A;
    }

    .badge-status-processing {
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
<!-- Header Banner Bảng Điều Khiển -->
<div class="dashboard-welcome-bar">
    <div class="welcome-title-group">
        <h1>Chào mừng, Quản trị viên hệ thống</h1>
        <p class="welcome-subtitle">Tổng quan các chỉ số vận hành và lượt đặt phòng năm {{ $monthlyStats['year'] ?? 2026 }}</p>
    </div>
    <div class="welcome-actions-group">
        <div class="date-filter-pill">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>01/10/2026 - 07/10/2026</span>
        </div>
        <button type="button" class="btn-export-report" onclick="alert('Đang xuất báo cáo thống kê định dạng Excel/PDF...')">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            <span>Xuất Báo Cáo</span>
        </button>
    </div>
</div>

<!-- Row 1: 4 Thẻ KPI Thống Kê Đầu Trang -->
<div class="stats-grid-4">
    <!-- Card 1: Tổng người dùng -->
    <div class="dashboard-card">
        <div class="stat-card-inner">
            <div>
                <div class="stat-label-text">TỔNG SỐ NGƯỜI DÙNG</div>
                <div class="stat-value-text">{{ $summary['total_users'] }}</div>
                <div class="stat-subtext positive">
                    <span>↗ {{ $summary['user_growth'] }} so với tháng trước</span>
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

    <!-- Card 2: Tổng số điểm đến -->
    <div class="dashboard-card">
        <div class="stat-card-inner">
            <div>
                <div class="stat-label-text">TỔNG SỐ ĐIỂM ĐẾN</div>
                <div class="stat-value-text">{{ $summary['total_destinations'] }}</div>
                <div class="stat-subtext info">
                    <span>↗ {{ $summary['destination_growth'] ?? '+8.2%' }} so với tháng trước</span>
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

    <!-- Card 3: Tổng đặt phòng -->
    <div class="dashboard-card">
        <div class="stat-card-inner">
            <div>
                <div class="stat-label-text">TỔNG ĐẶT PHÒNG</div>
                <div class="stat-value-text">{{ $summary['total_bookings'] ?? '860' }}</div>
                <div class="stat-subtext positive">
                    <span>↗ {{ $summary['booking_growth'] ?? '+5.4%' }} so với tháng trước</span>
                </div>
            </div>
            <div class="stat-icon-box bookings">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Card 4: Tổng doanh thu -->
    <div class="dashboard-card">
        <div class="stat-card-inner">
            <div>
                <div class="stat-label-text">TỔNG DOANH THU</div>
                <div class="stat-value-text">{{ $summary['total_revenue'] ?? '128.500.000 đ' }}</div>
                <div class="stat-subtext warning">
                    <span>↗ {{ $summary['revenue_growth'] ?? '+14.8%' }} so với tháng trước</span>
                </div>
            </div>
            <div class="stat-icon-box revenue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/>
                    <path d="M12 18V6"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Biểu Đồ Thống Kê & Tỷ Lệ Đặt Phòng -->
<div class="dashboard-mid-row">
    <!-- Cột trái: Biểu đồ Thống kê đặt phòng 12 tháng -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title-lg">{{ $monthlyStats['title'] ?? 'Thống Kê Lượt Đặt Phòng Theo Tháng' }}</h2>
                <p class="card-subtitle-sm">{{ $monthlyStats['subtitle'] ?? 'Số lượt đặt phòng trong năm 2026' }}</p>
            </div>
            <div>
                <select class="filter-dropdown-select" aria-label="Chọn năm xem báo cáo">
                    <option value="2026" selected>Năm 2026</option>
                    <option value="2025">Năm 2025</option>
                </select>
            </div>
        </div>

        <div class="bar-chart-container">
            <div class="bar-chart-bars-wrap">
                @foreach($monthlyStats['months'] as $month)
                    <div class="bar-col-item">
                        <div class="bar-rect-visual" 
                             style="height: {{ $month['height'] }};" 
                             data-tooltip="Tháng {{ $month['label'] }}: {{ $month['value'] }} lượt">
                        </div>
                        <span class="bar-label-month">{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="chart-footer-legend">
                <div class="legend-item-left">
                    <span class="legend-color-box"></span>
                    <span>Số lượt đặt phòng</span>
                </div>
                <div class="legend-growth-right">
                    ↗ {{ $monthlyStats['growth_info'] ?? 'Tăng 18.2% so với cùng kỳ năm trước' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: Tỷ Lệ Đặt Phòng - Theo Loại -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title-lg">Tỷ Lệ Đặt Phòng - Theo Loại</h2>
                <p class="card-subtitle-sm">Phân bổ theo hình thức lưu trú</p>
            </div>
        </div>

        <div class="top-categories-list">
            @foreach($bookingCategories as $category)
                <div class="category-rank-row">
                    <div class="cat-row-header">
                        <span>{{ $category['name'] }}</span>
                        <span class="cat-percentage-val">{{ $category['percentage'] }}%</span>
                    </div>
                    <div class="progress-track-bar">
                        <div class="progress-fill-bar" style="width: {{ $category['percentage'] }}%; background-color: {{ $category['color'] }};"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <a href="{{ route('admin.destinations.index') }}" class="btn-view-details-pill">
            <span>Xem chi tiết →</span>
        </a>
    </div>
</div>

<!-- Row 3: Bảng Giao Dịch Gần Đây -->
<div class="table-container-card">
    <div class="table-header-flex">
        <h2 class="table-title">Giao Dịch Gần Đây</h2>
        <a href="{{ route('admin.bookings.index') }}" class="table-view-all-link">Xem tất cả →</a>
    </div>

    <div style="overflow-x: auto;">
        <table class="modern-data-table">
            <thead>
                <tr>
                    <th>Mã Booking</th>
                    <th>Khách Hàng</th>
                    <th>Điểm Đến / Dịch Vụ</th>
                    <th>Ngày Đặt</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
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
                            @if(in_array(strtolower($booking['status']), ['hoàn thành', 'confirmed']))
                                <span class="status-badge-pill badge-status-confirmed">{{ $booking['status_label'] }}</span>
                            @elseif(in_array(strtolower($booking['status']), ['đang xử lý', 'upcoming']))
                                <span class="status-badge-pill badge-status-processing">{{ $booking['status_label'] }}</span>
                            @else
                                <span class="status-badge-pill badge-status-pending">{{ $booking['status_label'] }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                            Chưa có giao dịch đặt phòng nào gần đây.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
