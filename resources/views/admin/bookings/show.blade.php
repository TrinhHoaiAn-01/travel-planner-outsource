@extends('layouts.admin')

@section('title', 'Chi Tiết Đơn Đặt Phòng #' . $booking->booking_code)
@section('breadcrumb', 'Chi tiết đơn đặt phòng')

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
            <a href="{{ route('admin.bookings.index') }}" style="color: #2563EB; text-decoration: none;">&larr; Quay lại danh sách đơn</a>
        </div>
        <h1 class="page-title">Đơn Đặt Phòng #{{ $booking->booking_code }}</h1>
    </div>

    <div>
        @switch($booking->status)
            @case('pending')
                <span style="background: #FEF3C7; color: #B45309; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">Chờ xác nhận</span>
                @break
            @case('confirmed')
                <span style="background: #E0F2FE; color: #0369A1; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">Đã xác nhận</span>
                @break
            @case('completed')
                <span style="background: #DCFCE7; color: #15803D; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">Hoàn thành</span>
                @break
            @case('cancelled')
                <span style="background: #FEE2E2; color: #B91C1C; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">Đã hủy</span>
                @break
            @default
                <span style="background: #F1F5F9; color: #475569; padding: 6px 14px; border-radius: 9999px; font-weight: 700; font-size: 13px;">{{ ucfirst($booking->status) }}</span>
        @endswitch
    </div>
</div>

@if(session('success'))
    <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 14px;">
        {{ session('success') }}
    </div>
@endif

<div class="grid-layout">
    <!-- Cột chính bên trái: Thông tin đơn đặt phòng & Giao dịch -->
    <div>
        <!-- 1. Chi tiết phòng & khách sạn -->
        <div class="card-box">
            <h2 class="card-title">Thông Tin Nơi Lưu Trú & Hạng Phòng</h2>
            <table class="info-list-table">
                <tr>
                    <td class="label-col">Khách sạn / Điểm đến:</td>
                    <td class="value-col">{{ $booking->roomType->destination->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Địa chỉ:</td>
                    <td class="value-col">{{ $booking->roomType->destination->address ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Hạng phòng:</td>
                    <td class="value-col">{{ $booking->roomType->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Sức chứa phòng:</td>
                    <td class="value-col">{{ $booking->roomType->capacity ?? 2 }} người / phòng</td>
                </tr>
                <tr>
                    <td class="label-col">Số lượng phòng đặt:</td>
                    <td class="value-col">{{ $booking->room_count }} phòng</td>
                </tr>
                <tr>
                    <td class="label-col">Ngày nhận phòng:</td>
                    <td class="value-col">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label-col">Ngày trả phòng:</td>
                    <td class="value-col">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}</td>
                </tr>
                @php
                    $nights = max(1, \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)));
                @endphp
                <tr>
                    <td class="label-col">Tổng thời gian:</td>
                    <td class="value-col">{{ $nights }} đêm</td>
                </tr>
                <tr>
                    <td class="label-col" style="font-weight: 700; color: #0F172A;">Tổng giá trị đơn:</td>
                    <td class="value-col" style="font-size: 18px; color: #059669;">{{ number_format($booking->total_price, 0, ',', '.') }}đ</td>
                </tr>
            </table>
        </div>

        <!-- 2. Lịch sử giao dịch thanh toán -->
        <div class="card-box">
            <h2 class="card-title">Lịch Sử Giao Dịch Thanh Toán</h2>
            @if($booking->payments && $booking->payments->isNotEmpty())
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($booking->payments as $payment)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <div style="font-family: monospace; font-weight: 700; color: #2563EB; font-size: 14px;">
                                    {{ $payment->transaction_id }}
                                </div>
                                <div style="font-size: 12.5px; color: #64748B; margin-top: 2px;">
                                    Phương thức: <strong>{{ strtoupper($payment->payment_method) }}</strong> &bull; 
                                    Ngày: {{ \Carbon\Carbon::parse($payment->payment_date ?? $payment->created_at)->format('H:i d/m/Y') }}
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <span style="font-weight: 800; color: #059669; font-size: 16px;">
                                    {{ number_format($payment->amount, 0, ',', '.') }}đ
                                </span>
                                <a href="{{ route('admin.payments.show', $payment->id) }}" style="background: white; border: 1px solid #CBD5E1; color: #2563EB; padding: 6px 12px; border-radius: 6px; font-size: 12.5px; font-weight: 600; text-decoration: none;">
                                    Chi tiết giao dịch &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 24px; color: #64748B; background: #F8FAFC; border-radius: 8px;">
                    Chưa có giao dịch thanh toán nào được thực hiện cho đơn đặt phòng này.
                </div>
            @endif
        </div>
    </div>

    <!-- Cột phụ bên phải: Cập nhật trạng thái & Khách hàng -->
    <div>
        <!-- Cập nhật trạng thái -->
        <div class="card-box">
            <h2 class="card-title">Cập Nhật Trạng Thái</h2>
            <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <label style="display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                    Trạng thái mới:
                </label>
                <select name="status" class="status-select">
                    <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Chờ xác nhận (Pending)</option>
                    <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Đã xác nhận (Confirmed)</option>
                    <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Hoàn thành (Completed)</option>
                    <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Đã hủy (Cancelled)</option>
                </select>

                <button type="submit" class="btn-update-status">
                    Lưu Thay Đổi
                </button>
            </form>
        </div>

        <!-- Khách hàng -->
        <div class="card-box">
            <h2 class="card-title">Thông Tin Khách Hàng</h2>
            <table class="info-list-table">
                <tr>
                    <td class="label-col">Họ tên:</td>
                    <td class="value-col">{{ $booking->user->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Email:</td>
                    <td class="value-col">{{ $booking->user->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Chuyến đi gắn kèm:</td>
                    <td class="value-col">
                        @if($booking->trip)
                            {{ $booking->trip->name }}
                        @else
                            <span style="color: #94A3B8; font-weight: 400;">Không gắn</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
