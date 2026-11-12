@extends('layouts.admin')

@section('title', 'Chi Tiết Giao Dịch ' . $payment->transaction_id)
@section('breadcrumb', 'Chi tiết giao dịch thanh toán')

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
    }

    .grid-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .card-box {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .card-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .info-list-table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-list-table td {
        padding: 10px 0;
        font-size: 14px;
        border-bottom: 1px solid #F8FAFC;
    }

    .label-col {
        color: #64748B;
        width: 38%;
    }

    .value-col {
        color: #0F172A;
        font-weight: 600;
        text-align: right;
    }

    .status-select {
        width: 100%;
        padding: 10px 14px;
        border-radius: 8px;
        border: 1.5px solid #CBD5E1;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 14px;
        outline: none;
    }

    .btn-update-status {
        width: 100%;
        background: #2563EB;
        color: white;
        border: none;
        padding: 11px 18px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-update-status:hover {
        background: #1D4ED8;
    }

    @media (max-width: 900px) {
        .grid-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="page-header-row">
    <div>
        <div style="font-size: 13px; color: #64748B; margin-bottom: 4px;">
            <a href="{{ route('admin.payments.index') }}" style="color: #2563EB; text-decoration: none;">&larr; Quay lại danh sách thanh toán</a>
        </div>
        <h1 class="page-title">Giao Dịch {{ $payment->transaction_id }}</h1>
    </div>

    <div>
        @switch($payment->status)
            @case('completed')
                <span style="background: #DCFCE7; color: #15803D; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">Thành công</span>
                @break
            @case('pending')
                <span style="background: #FEF3C7; color: #B45309; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">Chờ xử lý</span>
                @break
            @case('failed')
                <span style="background: #FEE2E2; color: #B91C1C; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">Thất bại</span>
                @break
            @case('refunded')
                <span style="background: #F3E8FF; color: #7E22CE; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">Đã hoàn tiền</span>
                @break
            @default
                <span style="background: #F1F5F9; color: #475569; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">{{ ucfirst($payment->status) }}</span>
        @endswitch
    </div>
</div>

@if(session('success'))
    <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 14px;">
        {{ session('success') }}
    </div>
@endif

<div class="grid-layout">
    <!-- Cột trái: Thông tin giao dịch & Đơn đặt phòng -->
    <div>
        <!-- 1. Chi tiết giao dịch -->
        <div class="card-box">
            <h2 class="card-title">Thông Tin Giao Dịch Thanh Toán</h2>
            <table class="info-list-table">
                <tr>
                    <td class="label-col">Mã giao dịch (Txn ID):</td>
                    <td class="value-col" style="font-family: monospace; color: #2563EB;">{{ $payment->transaction_id }}</td>
                </tr>
                <tr>
                    <td class="label-col">Số tiền thanh toán:</td>
                    <td class="value-col" style="font-size: 18px; color: #059669; font-weight: 800;">{{ number_format($payment->amount, 0, ',', '.') }}đ</td>
                </tr>
                <tr>
                    <td class="label-col">Phương thức thanh toán:</td>
                    <td class="value-col" style="text-transform: uppercase;">{{ $payment->payment_method }}</td>
                </tr>
                <tr>
                    <td class="label-col">Thời gian ghi nhận:</td>
                    <td class="value-col">{{ \Carbon\Carbon::parse($payment->payment_date ?? $payment->created_at)->format('H:i:s d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label-col">Mã đơn đặt phòng liên quan:</td>
                    <td class="value-col">
                        <a href="{{ route('admin.bookings.show', $payment->booking->id) }}" style="color: #2563EB; text-decoration: none;">
                            #{{ $payment->booking->booking_code }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Tổng giá trị đơn đặt:</td>
                    <td class="value-col">{{ number_format($payment->booking->total_price, 0, ',', '.') }}đ</td>
                </tr>
            </table>
        </div>

        <!-- 2. Thông tin nơi lưu trú & Khách hàng -->
        <div class="card-box">
            <h2 class="card-title">Cơ Sở Lưu Trú & Khách Hàng Nhận Phòng</h2>
            <table class="info-list-table">
                <tr>
                    <td class="label-col">Cơ sở lưu trú:</td>
                    <td class="value-col">{{ $payment->booking->roomType->destination->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Hạng phòng:</td>
                    <td class="value-col">{{ $payment->booking->roomType->name ?? 'N/A' }} ({{ $payment->booking->room_count }} phòng)</td>
                </tr>
                <tr>
                    <td class="label-col">Khách hàng đặt:</td>
                    <td class="value-col">{{ $payment->booking->user->name ?? 'N/A' }} ({{ $payment->booking->user->email ?? '' }})</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Cột phải: Cập nhật trạng thái giao dịch -->
    <div>
        <div class="card-box">
            <h2 class="card-title">Cập Nhật Trạng Thái Giao Dịch</h2>
            <form action="{{ route('admin.payments.update-status', $payment->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                    Trạng thái mới:
                </label>
                <select name="status" class="status-select">
                    <option value="completed" {{ $payment->status === 'completed' ? 'selected' : '' }}>Thành công (Completed)</option>
                    <option value="pending" {{ $payment->status === 'pending' ? 'selected' : '' }}>Chờ xử lý (Pending)</option>
                    <option value="failed" {{ $payment->status === 'failed' ? 'selected' : '' }}>Thất bại (Failed)</option>
                    <option value="refunded" {{ $payment->status === 'refunded' ? 'selected' : '' }}>Đã hoàn tiền (Refunded)</option>
                </select>

                <button type="submit" class="btn-update-status">
                    Cập Nhật Trạng Thái
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
