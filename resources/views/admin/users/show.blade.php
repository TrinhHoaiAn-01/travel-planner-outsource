@extends('layouts.admin')

@section('title', 'Chi Tiết Người Dùng - ' . $user->name)
@section('breadcrumb', 'Chi tiết người dùng')

@push('styles')
<style>
    .admin-header-bar,
    .admin-breadcrumbs {
        display: none !important;
    }

    .admin-content-body {
        padding: 24px 32px 48px 32px;
    }

    .detail-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .detail-page-title {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
    }

    .user-breadcrumb {
        font-size: 13.5px;
        color: #64748B;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .user-breadcrumb a {
        color: #3B82F6;
        text-decoration: none;
        font-weight: 500;
    }

    .user-breadcrumb a:hover {
        text-decoration: underline;
    }

    .user-breadcrumb .sep {
        color: #94A3B8;
    }

    .user-breadcrumb .current {
        color: #64748B;
    }

    .btn-back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        color: #475569;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-back-link:hover {
        background-color: #F8FAFC;
        color: #0F172A;
        border-color: #CBD5E1;
    }

    .user-detail-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #F1F5F9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .profile-hero {
        padding: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #F1F5F9;
        flex-wrap: wrap;
        gap: 20px;
    }

    .profile-hero-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .profile-avatar-large {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        overflow: hidden;
        background-color: #EFF6FF;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        flex-shrink: 0;
    }

    .profile-avatar-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar-large span {
        font-size: 26px;
        font-weight: 700;
        color: #2563EB;
        text-transform: uppercase;
    }

    .profile-title-area h2 {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 6px;
    }

    .profile-sub-badges {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .badge-code {
        font-size: 12px;
        font-weight: 600;
        color: #64748B;
        background-color: #F1F5F9;
        padding: 3px 10px;
        border-radius: 6px;
    }

    .badge-role-admin {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 600;
        background-color: #0F172A;
        color: #FFFFFF;
    }

    .badge-role-member {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 600;
        background-color: #EFF6FF;
        color: #3B82F6;
    }

    .badge-status-active {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        background-color: #ECFDF5;
        color: #10B981;
    }

    .badge-status-disabled {
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        background-color: #FEF2F2;
        color: #EF4444;
    }

    /* KPI Grid 4 cột */
    .kpi-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        padding: 24px 32px;
        background-color: #F8FAFC;
        border-bottom: 1px solid #F1F5F9;
    }

    .kpi-stat-card {
        background-color: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .kpi-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .kpi-stat-info .num {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
        line-height: 1.2;
    }

    .kpi-stat-info .lbl {
        font-size: 12.5px;
        color: #64748B;
        margin-top: 2px;
    }

    /* Grid thông tin chi tiết */
    .detail-body-grid {
        padding: 32px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    .detail-field-item label {
        display: block;
        font-size: 12.5px;
        font-weight: 600;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 6px;
    }

    .detail-field-item .val {
        font-size: 14.5px;
        font-weight: 500;
        color: #0F172A;
        background-color: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 10px 14px;
    }

    .detail-full-width {
        grid-column: span 2;
    }

    @media (max-width: 768px) {
        .kpi-stat-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .detail-body-grid {
            grid-template-columns: 1fr;
        }
        .detail-full-width {
            grid-column: span 1;
        }
    }
</style>
@endpush

@section('content')
<div class="user-detail-container">
    {{-- Header & Nút quay lại --}}
    <div class="detail-header-row">
        <h1 class="detail-page-title">Chi Tiết Người Dùng</h1>
        <a href="{{ route('admin.users.index') }}" class="btn-back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Quay lại danh sách</span>
        </a>
    </div>

    {{-- Breadcrumb: Admin / Người dùng / Chi tiết --}}
    <div class="user-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="sep">/</span>
        <a href="{{ route('admin.users.index') }}">Người dùng</a>
        <span class="sep">/</span>
        <span class="current">{{ $user->name }}</span>
    </div>

    {{-- Khối thông tin chi tiết --}}
    <div class="user-detail-card">
        {{-- Profile Hero --}}
        <div class="profile-hero">
            <div class="profile-hero-left">
                <div class="profile-avatar-large">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
                    @else
                        <span>{{ substr($user->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="profile-title-area">
                    <h2>{{ $user->name }}</h2>
                    <div class="profile-sub-badges">
                        <span class="badge-code">ID: {{ $user->user_code }}</span>
                        @if($user->role === 'admin')
                            <span class="badge-role-admin">Administrator</span>
                        @else
                            <span class="badge-role-member">Member</span>
                        @endif

                        @if($user->is_active)
                            <span class="badge-status-active">Hoạt động (Active)</span>
                        @else
                            <span class="badge-status-disabled">Đã khóa (Disabled)</span>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                @if($user->role !== 'admin')
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}" style="display: inline;">
                        @csrf
                        @if($user->is_active)
                            <button type="submit" style="padding: 9px 18px; border-radius: 8px; border: 1px solid #FCD34D; background-color: #FEF3C7; color: #B45309; font-size: 13px; font-weight: 600; cursor: pointer;">
                                Khóa tài khoản
                            </button>
                        @else
                            <button type="submit" style="padding: 9px 18px; border-radius: 8px; border: 1px solid #6EE7B7; background-color: #ECFDF5; color: #047857; font-size: 13px; font-weight: 600; cursor: pointer;">
                                Mở khóa tài khoản
                            </button>
                        @endif
                    </form>
                @endif
            </div>
        </div>

        {{-- 4 Card Thống Kê Hoạt Động --}}
        <div class="kpi-stat-grid">
            <div class="kpi-stat-card">
                <div class="kpi-icon-wrap" style="background-color: #EFF6FF; color: #2563EB;">✈️</div>
                <div class="kpi-stat-info">
                    <div class="num">{{ $user->trips_count ?? $user->trips()->count() }}</div>
                    <div class="lbl">Chuyến đi đã tạo</div>
                </div>
            </div>

            <div class="kpi-stat-card">
                <div class="kpi-icon-wrap" style="background-color: #ECFDF5; color: #059669;">🏨</div>
                <div class="kpi-stat-info">
                    <div class="num">{{ $user->bookings_count ?? $user->bookings()->count() }}</div>
                    <div class="lbl">Lượt đặt phòng</div>
                </div>
            </div>

            <div class="kpi-stat-card">
                <div class="kpi-icon-wrap" style="background-color: #FEF3C7; color: #D97706;">⭐</div>
                <div class="kpi-stat-info">
                    <div class="num">{{ $user->reviews_count ?? $user->reviews()->count() }}</div>
                    <div class="lbl">Đánh giá gửi đi</div>
                </div>
            </div>

            <div class="kpi-stat-card">
                <div class="kpi-icon-wrap" style="background-color: #FDF2F8; color: #DB2777;">❤️</div>
                <div class="kpi-stat-info">
                    <div class="num">{{ $user->favorites_count ?? $user->favorites()->count() }}</div>
                    <div class="lbl">Địa điểm yêu thích</div>
                </div>
            </div>
        </div>

        {{-- Chi Tiết Thuộc Tính Người Dùng --}}
        <div class="detail-body-grid">
            <div class="detail-field-item">
                <label>Họ và tên</label>
                <div class="val">{{ $user->name }}</div>
            </div>

            <div class="detail-field-item">
                <label>Địa chỉ Email</label>
                <div class="val">{{ $user->email }}</div>
            </div>

            <div class="detail-field-item">
                <label>Số điện thoại</label>
                <div class="val">{{ $user->phone ?? 'Chưa cập nhật' }}</div>
            </div>

            <div class="detail-field-item">
                <label>Tỉnh / Thành phố sinh sống</label>
                <div class="val">{{ $user->city ?? 'Chưa cập nhật' }}</div>
            </div>

            <div class="detail-field-item">
                <label>Ngày tham gia</label>
                <div class="val">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '15/01/2026' }}</div>
            </div>

            <div class="detail-field-item">
                <label>Xác thực Email</label>
                <div class="val">
                    @if($user->email_verified_at)
                        <span style="color: #059669; font-weight: 600;">✓ Đã xác thực ({{ $user->email_verified_at->format('d/m/Y') }})</span>
                    @else
                        <span style="color: #D97706; font-weight: 500;">Chưa xác thực email</span>
                    @endif
                </div>
            </div>

            <div class="detail-field-item detail-full-width">
                <label>Tiểu sử / Giới thiệu</label>
                <div class="val" style="min-height: 70px; line-height: 1.6;">
                    {{ $user->bio ?? 'Người dùng chưa cập nhật tiểu sử cá nhân.' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
