@extends('layouts.admin')

@section('title', 'Quản Lý Đơn Đặt Phòng')
@section('breadcrumb', 'Đơn đặt phòng')

@push('styles')
<style>
    .page-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #0F172A;
        letter-spacing: -0.01em;
    }

    .page-subtitle {
        font-size: 13.5px;
        color: #64748B;
        margin-top: 4px;
    }

    .table-container-card {
        background: #FFFFFF;
        border-radius: 14px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .filter-bar {
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        border-bottom: 1px solid #F1F5F9;
    }

    .status-tabs {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .status-tab-btn {
        padding: 7px 14px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #64748B;
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        transition: all 0.2s;
    }

    .status-tab-btn:hover {
        background: #F1F5F9;
        color: #0F172A;
    }

    .status-tab-btn.active {
        background: #2563EB;
        color: #FFFFFF;
        border-color: #2563EB;
    }

    .search-input-box {
        position: relative;
        min-width: 280px;
    }

    .search-input-box input {
        width: 100%;
        padding: 8px 14px 8px 36px;
        border-radius: 8px;
        border: 1px solid #CBD5E1;
        font-size: 13.5px;
        outline: none;
    }

    .search-input-box input:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .data-table th {
        background: #F8FAFC;
        padding: 12px 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748B;
        border-bottom: 1px solid #E2E8F0;
    }

    .data-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 13.5px;
        color: #1E293B;
        vertical-align: middle;
    }

    .data-table tr:hover td {
        background-color: #FAFAFA;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-pending { background: #FEF3C7; color: #B45309; }
    .badge-confirmed { background: #E0F2FE; color: #0369A1; }
    .badge-completed { background: #DCFCE7; color: #15803D; }
    .badge-cancelled { background: #FEE2E2; color: #B91C1C; }

    .btn-action-view {
        background: #EFF6FF;
        color: #2563EB;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12.5px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .btn-action-view:hover {
        background: #DBEAFE;
    }

    .pagination-wrapper {
        padding: 16px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid #F1F5F9;
    }
</style>
@endpush

@section('content')
<div class="page-header-row">
    <div>
        <h1 class="page-title">Quản Lý Đơn Đặt Phòng (Bookings)</h1>
        <p class="page-subtitle">Theo dõi, kiểm tra tình trạng lưu trú và quản lý trạng thái các đơn đặt phòng hệ thống.</p>
    </div>
</div>

@if(session('success'))
    <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 14px;">
        {{ session('success') }}
    </div>
@endif

<div class="table-container-card">
    <!-- Bộ Lọc Trạng Thái & Tìm Kiếm -->
    <div class="filter-bar">
        <div class="status-tabs">
            <a href="{{ route('admin.bookings.index') }}" class="status-tab-btn {{ empty($status) ? 'active' : '' }}">
                Tất cả
            </a>
            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" class="status-tab-btn {{ $status === 'pending' ? 'active' : '' }}">
                Chờ xác nhận
            </a>
            <a href="{{ route('admin.bookings.index', ['status' => 'confirmed']) }}" class="status-tab-btn {{ $status === 'confirmed' ? 'active' : '' }}">
                Đã xác nhận
            </a>
            <a href="{{ route('admin.bookings.index', ['status' => 'completed']) }}" class="status-tab-btn {{ $status === 'completed' ? 'active' : '' }}">
                Hoàn thành
            </a>
            <a href="{{ route('admin.bookings.index', ['status' => 'cancelled']) }}" class="status-tab-btn {{ $status === 'cancelled' ? 'active' : '' }}">
                Đã hủy
            </a>
        </div>

        <form action="{{ route('admin.bookings.index') }}" method="GET" class="search-input-box">
            @if(!empty($status))
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tìm theo mã đơn, khách hàng, khách sạn...">
        </form>
    </div>

    <!-- Bảng Dữ Liệu -->
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Cơ Sở Lưu Trú & Phòng</th>
                    <th>Thời Gian Lưu Trú</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Thanh Toán</th>
                    <th style="text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700; color: #2563EB;">
                            #{{ $booking->booking_code }}
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $booking->user->name ?? 'N/A' }}</div>
                            <div style="font-size: 12px; color: #64748B;">{{ $booking->user->email ?? '' }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #0F172A;">{{ $booking->roomType->destination->name ?? 'Điểm đến' }}</div>
                            <div style="font-size: 12.5px; color: #64748B;">{{ $booking->roomType->name ?? 'Phòng' }} &bull; {{ $booking->room_count }} phòng</div>
                        </td>
                        <td>
                            <div>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }} &rarr; {{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}</div>
                            @php
                                $nights = max(1, \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)));
                            @endphp
                            <div style="font-size: 12px; color: #64748B;">{{ $nights }} đêm</div>
                        </td>
                        <td style="font-weight: 700; color: #0F172A;">
                            {{ number_format($booking->total_price, 0, ',', '.') }}đ
                        </td>
                        <td>
                            @switch($booking->status)
                                @case('pending')
                                    <span class="badge-status badge-pending">Chờ xác nhận</span>
                                    @break
                                @case('confirmed')
                                    <span class="badge-status badge-confirmed">Đã xác nhận</span>
                                    @break
                                @case('completed')
                                    <span class="badge-status badge-completed">Hoàn thành</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge-status badge-cancelled">Đã hủy</span>
                                    @break
                                @default
                                    <span class="badge-status">{{ ucfirst($booking->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            @php
                                $completedPayment = $booking->payments->firstWhere('status', 'completed');
                            @endphp
                            @if($completedPayment)
                                <span style="display: inline-block; background: #DCFCE7; color: #15803D; padding: 2px 8px; border-radius: 9999px; font-size: 11.5px; font-weight: 700;">
                                    Đã thanh toán
                                </span>
                            @else
                                <span style="display: inline-block; background: #F1F5F9; color: #64748B; padding: 2px 8px; border-radius: 9999px; font-size: 11.5px; font-weight: 600;">
                                    Chưa thanh toán
                                </span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn-action-view">
                                Chi tiết &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #64748B;">
                            Không tìm thấy đơn đặt phòng nào phù hợp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    @if($bookings->hasPages())
        <div class="pagination-wrapper">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection
