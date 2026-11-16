@extends('layouts.admin')

@section('title', 'Quản Lý Giao Dịch Thanh Toán')
@section('breadcrumb', 'Thanh toán')

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
        min-width: 980px;
        border-collapse: collapse;
        text-align: left;
    }

    .data-table th {
        background: #F8FAFC;
        padding: 12px 18px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748B;
        border-bottom: 1px solid #E2E8F0;
        white-space: nowrap;
    }

    .data-table td {
        padding: 14px 18px;
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
        justify-content: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.25;
        white-space: nowrap;
        text-align: center;
    }

    .badge-pending { background: #FEF3C7; color: #B45309; }
    .badge-completed { background: #DCFCE7; color: #15803D; }
    .badge-failed { background: #FEE2E2; color: #B91C1C; }
    .badge-refunded { background: #F3E8FF; color: #7E22CE; }

    .btn-action-circle {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid;
        text-decoration: none;
        padding: 0;
        box-sizing: border-box;
    }

    .btn-action-view {
        border-color: #BAE6FD;
        color: #0284C7;
        background-color: #F0F9FF;
    }

    .btn-action-view:hover {
        background-color: #E0F2FE;
        border-color: #0284C7;
        color: #0369A1;
    }

    .btn-action-delete {
        border-color: #FECACA;
        color: #EF4444;
        background-color: #FEF2F2;
    }

    .btn-action-delete:hover {
        background-color: #FEE2E2;
        border-color: #EF4444;
        color: #DC2626;
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
        <h1 class="page-title">Quản Lý Giao Dịch Thanh Toán (Payments)</h1>
        <p class="page-subtitle">Kiểm soát dòng tiền, đối soát giao dịch và quản lý trạng thái thanh toán theo quy tắc BR-21.</p>
    </div>
</div>

<div class="table-container-card">
    <!-- Bộ Lọc Trạng Thái & Tìm Kiếm -->
    <div class="filter-bar">
        <div class="status-tabs">
            <a href="{{ route('admin.payments.index') }}" class="status-tab-btn {{ empty($status) ? 'active' : '' }}">
                Tất cả
            </a>
            <a href="{{ route('admin.payments.index', ['status' => 'completed']) }}" class="status-tab-btn {{ $status === 'completed' ? 'active' : '' }}">
                Thành công
            </a>
            <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="status-tab-btn {{ $status === 'pending' ? 'active' : '' }}">
                Chờ xử lý
            </a>
            <a href="{{ route('admin.payments.index', ['status' => 'failed']) }}" class="status-tab-btn {{ $status === 'failed' ? 'active' : '' }}">
                Thất bại
            </a>
            <a href="{{ route('admin.payments.index', ['status' => 'refunded']) }}" class="status-tab-btn {{ $status === 'refunded' ? 'active' : '' }}">
                Đã hoàn tiền
            </a>
        </div>

        <form action="{{ route('admin.payments.index') }}" method="GET" class="search-input-box">
            @if(!empty($status))
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="search" data-search-suggestions="payment" value="{{ $search ?? '' }}" placeholder="Tìm theo mã giao dịch, mã đơn, khách hàng...">
        </form>
    </div>

    <!-- Bảng Dữ Liệu -->
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="white-space: nowrap;">Mã Giao Dịch</th>
                    <th style="white-space: nowrap;">Mã Đơn Đặt</th>
                    <th>Khách Hàng</th>
                    <th style="white-space: nowrap;">Phương Thức</th>
                    <th style="white-space: nowrap;">Số Tiền</th>
                    <th style="white-space: nowrap;">Thời Gian</th>
                    <th style="white-space: nowrap; min-width: 130px;">Trạng Thái</th>
                    <th style="text-align: right; white-space: nowrap;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700; color: #2563EB; white-space: nowrap;">
                            {{ $payment->transaction_id }}
                        </td>
                        <td style="white-space: nowrap;">
                            <a href="{{ route('admin.bookings.show', $payment->booking->id) }}" style="color: #0F172A; font-weight: 600; text-decoration: none;">
                                #{{ ltrim($payment->booking->booking_code ?? 'N/A', '#') }}
                            </a>
                            <div style="font-size: 11.5px; color: #64748B; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $payment->booking->roomType?->destination?->name ?? '' }} - {{ $payment->booking->roomType?->name ?? '' }}">
                                {{ $payment->booking->roomType?->destination?->name ?? 'Dịch vụ lưu trú' }} ({{ $payment->booking->roomType?->name ?? 'Phòng nghỉ' }})
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $payment->booking->user->name ?? 'N/A' }}</div>
                            <div style="font-size: 12px; color: #64748B;">{{ $payment->booking->user->email ?? '' }}</div>
                        </td>
                        <td style="white-space: nowrap;">
                            <span style="font-weight: 700; text-transform: uppercase; font-size: 12px; color: #475569;">
                                {{ $payment->payment_method }}
                            </span>
                        </td>
                        <td style="font-weight: 800; color: #059669; font-size: 14.5px; white-space: nowrap;">
                            {{ number_format($payment->amount, 0, ',', '.') }}đ
                        </td>
                        <td style="font-size: 12.5px; color: #64748B; white-space: nowrap;">
                            {{ \Carbon\Carbon::parse($payment->payment_date ?? $payment->created_at)->format('H:i d/m/Y') }}
                        </td>
                        <td style="white-space: nowrap;">
                            @switch($payment->status)
                                @case('completed')
                                    <span class="badge-status badge-completed">Thành công</span>
                                    @break
                                @case('pending')
                                    <span class="badge-status badge-pending">Chờ xử lý</span>
                                    @break
                                @case('failed')
                                    <span class="badge-status badge-failed">Thất bại</span>
                                    @break
                                @case('refunded')
                                    <span class="badge-status badge-refunded">Đã hoàn tiền</span>
                                    @break
                                @default
                                    <span class="badge-status">{{ ucfirst($payment->status) }}</span>
                            @endswitch
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                @if($payment->status === 'pending')
                                    <form action="{{ route('admin.payments.update-status', $payment->id) }}" method="POST" style="margin: 0; display: inline;" onsubmit="return handleFormConfirm(this, event, 'Xác nhận duyệt thanh toán cho đơn #{{ ltrim($payment->booking->booking_code ?? '', '#') }}?', 'Duyệt thanh toán', false);">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit"
                                                class="btn-action-circle"
                                                style="color: #059669; border-color: #A7F3D0; background: #ECFDF5; cursor: pointer;"
                                                title="Duyệt">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('admin.payments.show', $payment->id) }}"
                                   class="btn-action-circle btn-action-view"
                                   title="Chi tiết">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>

                                <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST" style="margin: 0; display: inline;" onsubmit="return handleFormConfirm(this, event, 'Bạn có chắc chắn muốn xóa giao dịch thanh toán {{ $payment->transaction_id }} không?', 'Xác nhận xóa', true);">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn-action-circle btn-action-delete"
                                            title="Xóa">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"></path>
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #64748B;">
                            Không tìm thấy giao dịch thanh toán nào phù hợp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    @if($payments->hasPages())
        <div class="pagination-wrapper">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
