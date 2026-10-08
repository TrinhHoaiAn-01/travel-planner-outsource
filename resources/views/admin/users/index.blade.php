@extends('layouts.admin')

@section('title', 'Quản Lý Người Dùng')
@section('breadcrumb', 'Người dùng')

@push('styles')
<style>
    /* Ẩn header và breadcrumb mặc định của layout để khớp 100% bố cục ảnh chụp */
    .admin-header-bar,
    .admin-breadcrumbs {
        display: none !important;
    }

    .admin-content-body {
        padding: 24px 32px 48px 32px;
    }

    /* Thanh tiêu đề trang Quản Lý Người Dùng */
    .user-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .user-page-title {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
        letter-spacing: -0.01em;
    }

    .btn-add-user {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: #2563EB;
        color: #FFFFFF;
        font-size: 13.5px;
        font-weight: 600;
        padding: 9px 18px;
        border-radius: 9999px;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
        transition: all 0.2s ease;
    }

    .btn-add-user:hover {
        background-color: #1D4ED8;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
    }

    /* Breadcrumb chuẩn ảnh: Admin / Người dùng */
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

    /* Thẻ Card chứa Bộ lọc & Bảng dữ liệu */
    .user-table-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #F1F5F9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    /* Thanh tìm kiếm & bộ lọc trên đầu Card */
    .user-filter-bar {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .filter-left-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        max-width: 650px;
    }

    .search-input-wrap {
        position: relative;
        flex: 1;
    }

    .search-input-wrap svg {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        pointer-events: none;
    }

    .search-input-control {
        width: 100%;
        padding: 9px 14px 9px 38px;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        font-size: 13.5px;
        color: #0F172A;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-input-control:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .role-select-control {
        padding: 9px 16px;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        font-size: 13.5px;
        color: #334155;
        background-color: #FFFFFF;
        outline: none;
        cursor: pointer;
        min-width: 140px;
        transition: border-color 0.2s;
    }

    .role-select-control:focus {
        border-color: #2563EB;
    }

    .user-count-summary {
        font-size: 13.5px;
        color: #64748B;
        white-space: nowrap;
    }

    .user-count-summary strong {
        color: #0F172A;
        font-weight: 600;
    }

    /* Bảng dữ liệu chính */
    .user-data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .user-data-table thead th {
        padding: 16px 24px;
        font-size: 13.5px;
        font-weight: 600;
        color: #0F172A;
        border-top: 1px solid #F1F5F9;
        border-bottom: 1px solid #F1F5F9;
        background-color: #FFFFFF;
        letter-spacing: -0.01em;
        white-space: nowrap;
    }

    .user-data-table thead th.col-actions {
        text-align: right;
    }

    .user-data-table tbody tr {
        border-bottom: 1px solid #F8FAFC;
        transition: background-color 0.15s ease;
    }

    .user-data-table tbody tr:hover {
        background-color: #F8FAFC;
    }

    .user-data-table tbody tr:last-child {
        border-bottom: none;
    }

    .user-data-table tbody td {
        padding: 16px 24px;
        vertical-align: middle;
        font-size: 13.5px;
        color: #334155;
    }

    /* Cột 1: Thông tin Người dùng (Avatar + Tên + ID) */
    .user-profile-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar-wrap {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        overflow: hidden;
        background-color: #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .user-avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .user-avatar-initials {
        font-size: 13px;
        font-weight: 700;
        color: #3B82F6;
        text-transform: uppercase;
    }

    .user-name-text {
        font-size: 14px;
        font-weight: 700;
        color: #0F172A;
        line-height: 1.3;
    }

    .user-code-text {
        font-size: 12px;
        color: #94A3B8;
        margin-top: 2px;
        font-weight: 500;
    }

    /* Cột 3: Huy hiệu vai trò */
    .badge-role-admin {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.2;
        background-color: #0F172A;
        color: #FFFFFF;
    }

    .badge-role-member {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.2;
        background-color: #EFF6FF;
        color: #3B82F6;
    }

    /* Cột 5: Huy hiệu trạng thái */
    .badge-status-active {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        background-color: #ECFDF5;
        color: #10B981;
    }

    .badge-status-disabled {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        background-color: #FEF2F2;
        color: #EF4444;
    }

    /* Cột 6: Nhóm nút hành động */
    .user-actions-cell {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .label-protected {
        font-size: 13px;
        color: #94A3B8;
        font-weight: 500;
        padding: 6px 12px;
    }

    .btn-status-toggle {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 9999px;
        font-size: 12.5px;
        font-weight: 600;
        background-color: #FFFFFF;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-status-toggle.btn-lock {
        border: 1px solid #FCD34D;
        color: #D97706;
    }

    .btn-status-toggle.btn-lock:hover {
        background-color: #FEF3C7;
        color: #B45309;
        transform: translateY(-1px);
    }

    .btn-status-toggle.btn-unlock {
        border: 1px solid #6EE7B7;
        color: #059669;
    }

    .btn-status-toggle.btn-unlock:hover {
        background-color: #ECFDF5;
        color: #047857;
        transform: translateY(-1px);
    }

    .btn-circle-edit {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-circle-edit:hover {
        border-color: #3B82F6;
        color: #3B82F6;
        background-color: #EFF6FF;
        transform: translateY(-1px);
    }

    /* Thông báo Flash Alert */
    .flash-alert-banner {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13.5px;
    }

    .flash-alert-success {
        background-color: #ECFDF5;
        border: 1px solid #A7F3D0;
        color: #065F46;
    }

    .flash-alert-error {
        background-color: #FEF2F2;
        border: 1px solid #FECACA;
        color: #991B1B;
    }

    /* Modal Styling */
    .custom-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
        z-index: 100;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .custom-modal-backdrop.is-active {
        display: flex;
    }

    .custom-modal-box {
        background-color: #FFFFFF;
        border-radius: 16px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        animation: modalScaleUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalScaleUp {
        from {
            opacity: 0;
            transform: scale(0.96);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .custom-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .custom-modal-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
    }

    .custom-modal-close {
        background: none;
        border: none;
        color: #94A3B8;
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        transition: all 0.15s;
    }

    .custom-modal-close:hover {
        color: #0F172A;
        background-color: #F1F5F9;
    }

    .custom-modal-body {
        padding: 24px;
    }

    .form-group-item {
        margin-bottom: 18px;
    }

    .form-group-item label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .form-control-input,
    .form-control-select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        font-size: 13.5px;
        color: #0F172A;
        font-family: inherit;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-control-input:focus,
    .form-control-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .custom-modal-footer {
        padding: 16px 24px;
        background-color: #F8FAFC;
        border-top: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary-cancel {
        padding: 9px 18px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        color: #475569;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-secondary-cancel:hover {
        background-color: #F1F5F9;
        color: #0F172A;
    }

    .btn-primary-submit {
        padding: 9px 20px;
        border-radius: 8px;
        border: none;
        background-color: #2563EB;
        color: #FFFFFF;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        transition: all 0.2s;
    }

    .btn-primary-submit:hover {
        background-color: #1D4ED8;
    }
</style>
@endpush

@section('content')
<div class="user-management-container">

    {{-- Thông báo phản hồi --}}
    @if(session('success'))
        <div class="flash-alert-banner flash-alert-success">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:inherit; cursor:pointer;">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-alert-banner flash-alert-error">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:inherit; cursor:pointer;">&times;</button>
        </div>
    @endif

    {{-- Tiêu đề trang & Nút thêm người dùng --}}
    <div class="user-header-row">
        <h1 class="user-page-title">Quản Lý Người Dùng</h1>
        <button type="button" class="btn-add-user" onclick="openCreateModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
            <span>+ Thêm Người Dùng</span>
        </button>
    </div>

    {{-- Breadcrumb: Admin / Người dùng --}}
    <div class="user-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="sep">/</span>
        <span class="current">Người dùng</span>
    </div>

    {{-- Card Chứa Bộ lọc và Bảng dữ liệu --}}
    <div class="user-table-card">

        {{-- Thanh tìm kiếm & Lọc vai trò --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="user-filter-bar">
            <div class="filter-left-controls">
                <div class="search-input-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="search-input-control"
                           placeholder="Tìm kiếm theo tên hoặc email..."
                           onchange="this.form.submit()">
                </div>

                <select name="role" class="role-select-control" onchange="this.form.submit()">
                    <option value="all" {{ request('role') == 'all' || !request('role') ? 'selected' : '' }}>Tất cả vai trò</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Thành viên (Member)</option>
                </select>
            </div>

            <div class="user-count-summary">
                Tổng cộng: <strong>{{ $users->total() }} tài khoản</strong>
            </div>
        </form>

        {{-- Bảng dữ liệu --}}
        <table class="user-data-table">
            <thead>
                <tr>
                    <th style="width: 280px;">Người dùng</th>
                    <th style="width: 280px;">Email</th>
                    <th style="width: 140px;">Vai trò</th>
                    <th style="width: 160px;">Ngày tham gia</th>
                    <th style="width: 180px;">Trạng thái</th>
                    <th class="col-actions" style="width: 160px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        {{-- Cột 1: Người dùng (Avatar + Tên + Mã định danh) --}}
                        <td>
                            <div class="user-profile-cell">
                                <div class="user-avatar-wrap">
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="user-avatar-img">
                                    @else
                                        <span class="user-avatar-initials">{{ substr($user->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="user-name-text">{{ $user->name }}</div>
                                    <div class="user-code-text">ID: {{ $user->user_code }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Cột 2: Email --}}
                        <td>
                            <span>{{ $user->email }}</span>
                        </td>

                        {{-- Cột 3: Vai trò --}}
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge-role-admin">Administrator</span>
                            @else
                                <span class="badge-role-member">Member</span>
                            @endif
                        </td>

                        {{-- Cột 4: Ngày tham gia --}}
                        <td>
                            <span>{{ $user->created_at ? $user->created_at->format('d/m/Y') : '15/01/2026' }}</span>
                        </td>

                        {{-- Cột 5: Trạng thái --}}
                        <td>
                            @if($user->is_active)
                                <span class="badge-status-active">Hoạt động (Active)</span>
                            @else
                                <span class="badge-status-disabled">Đã khóa (Disabled)</span>
                            @endif
                        </td>

                        {{-- Cột 6: Hành động --}}
                        <td>
                            <div class="user-actions-cell">
                                @if($user->role === 'admin')
                                    {{-- Tài khoản Admin được bảo vệ đặc biệt --}}
                                    <span class="label-protected">Bảo vệ</span>
                                @else
                                    {{-- Nút Khóa / Mở khóa cho Member --}}
                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}" style="display: inline;">
                                        @csrf
                                        @if($user->is_active)
                                            <button type="submit" class="btn-status-toggle btn-lock" title="Khóa tài khoản này">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                                </svg>
                                                <span>Khóa</span>
                                            </button>
                                        @else
                                            <button type="submit" class="btn-status-toggle btn-unlock" title="Mở khóa tài khoản">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <polyline points="9 12 11 14 15 10"/>
                                                </svg>
                                                <span>Mở khóa</span>
                                            </button>
                                        @endif
                                    </form>

                                    {{-- Nút chỉnh sửa --}}
                                    <button type="button"
                                            class="btn-circle-edit"
                                            title="Chỉnh sửa người dùng"
                                            onclick="openEditModal({{ json_encode([
                                                'id' => $user->id,
                                                'name' => $user->name,
                                                'email' => $user->email,
                                                'role' => $user->role,
                                                'is_active' => (bool) $user->is_active,
                                                'update_url' => route('admin.users.update', $user->id)
                                            ]) }})">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 48px; color: #94A3B8;">
                            Chưa tìm thấy người dùng phù hợp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phân trang nếu có --}}
    @if($users->hasPages())
        <div style="margin-top: 20px; display: flex; justify-content: center;">
            {{ $users->links() }}
        </div>
    @endif
</div>

{{-- MODAL 1: THÊM MỚI NGƯỜI DÙNG --}}
<div id="createUserModal" class="custom-modal-backdrop">
    <div class="custom-modal-box">
        <div class="custom-modal-header">
            <h3 class="custom-modal-title">Thêm Người Dùng Mới</h3>
            <button type="button" class="custom-modal-close" onclick="closeModal('createUserModal')">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="custom-modal-body">
                <div class="form-group-item">
                    <label for="create_name">Họ và tên <span style="color: #EF4444;">*</span></label>
                    <input type="text"
                           id="create_name"
                           name="name"
                           class="form-control-input"
                           placeholder="Ví dụ: Nguyễn Bảo Ngọc"
                           required>
                </div>

                <div class="form-group-item">
                    <label for="create_email">Địa chỉ Email <span style="color: #EF4444;">*</span></label>
                    <input type="email"
                           id="create_email"
                           name="email"
                           class="form-control-input"
                           placeholder="ngoc.travel@travelplanner.com"
                           required>
                </div>

                <div class="form-group-item">
                    <label for="create_password">Mật khẩu <span style="color: #EF4444;">*</span></label>
                    <input type="password"
                           id="create_password"
                           name="password"
                           class="form-control-input"
                           placeholder="Tối thiểu 8 ký tự"
                           required>
                </div>

                <div class="form-group-item">
                    <label for="create_password_confirmation">Xác nhận mật khẩu <span style="color: #EF4444;">*</span></label>
                    <input type="password"
                           id="create_password_confirmation"
                           name="password_confirmation"
                           class="form-control-input"
                           placeholder="Nhập lại mật khẩu"
                           required>
                </div>

                <div class="form-group-item">
                    <label for="create_role">Vai trò</label>
                    <select id="create_role" name="role" class="form-control-select">
                        <option value="user" selected>Thành viên (Member)</option>
                        <option value="admin">Quản trị viên (Administrator)</option>
                    </select>
                </div>

                <div class="form-group-item">
                    <label for="create_is_active">Trạng thái tài khoản</label>
                    <select id="create_is_active" name="is_active" class="form-control-select">
                        <option value="1" selected>Hoạt động (Active)</option>
                        <option value="0">Tạm khóa (Disabled)</option>
                    </select>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn-secondary-cancel" onclick="closeModal('createUserModal')">Hủy bỏ</button>
                <button type="submit" class="btn-primary-submit">Lưu Người Dùng</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: CHỈNH SỬA THÔNG TIN NGƯỜI DÙNG --}}
<div id="editUserModal" class="custom-modal-backdrop">
    <div class="custom-modal-box">
        <div class="custom-modal-header">
            <h3 class="custom-modal-title">Chỉnh Sửa Người Dùng</h3>
            <button type="button" class="custom-modal-close" onclick="closeModal('editUserModal')">&times;</button>
        </div>
        <form id="editUserForm" method="POST">
            @csrf
            @method('PUT')
            <div class="custom-modal-body">
                <div class="form-group-item">
                    <label for="edit_name">Họ và tên <span style="color: #EF4444;">*</span></label>
                    <input type="text"
                           id="edit_name"
                           name="name"
                           class="form-control-input"
                           required>
                </div>

                <div class="form-group-item">
                    <label for="edit_email">Địa chỉ Email <span style="color: #EF4444;">*</span></label>
                    <input type="email"
                           id="edit_email"
                           name="email"
                           class="form-control-input"
                           required>
                </div>

                <div class="form-group-item">
                    <label for="edit_password">Mật khẩu mới (Để trống nếu không đổi)</label>
                    <input type="password"
                           id="edit_password"
                           name="password"
                           class="form-control-input"
                           placeholder="Nhập mật khẩu mới nếu muốn thay đổi">
                </div>

                <div class="form-group-item">
                    <label for="edit_password_confirmation">Xác nhận mật khẩu mới</label>
                    <input type="password"
                           id="edit_password_confirmation"
                           name="password_confirmation"
                           class="form-control-input"
                           placeholder="Xác nhận mật khẩu mới">
                </div>

                <div class="form-group-item">
                    <label for="edit_role">Vai trò</label>
                    <select id="edit_role" name="role" class="form-control-select">
                        <option value="user">Thành viên (Member)</option>
                        <option value="admin">Quản trị viên (Administrator)</option>
                    </select>
                </div>

                <div class="form-group-item" id="edit_status_group">
                    <label for="edit_is_active">Trạng thái tài khoản</label>
                    <select id="edit_is_active" name="is_active" class="form-control-select">
                        <option value="1">Hoạt động (Active)</option>
                        <option value="0">Tạm khóa (Disabled)</option>
                    </select>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn-secondary-cancel" onclick="closeModal('editUserModal')">Hủy bỏ</button>
                <button type="submit" class="btn-primary-submit">Cập Nhật Thông Tin</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCreateModal() {
        document.getElementById('createUserModal').classList.add('is-active');
    }

    function openEditModal(user) {
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_is_active').value = user.is_active ? '1' : '0';
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirmation').value = '';
        document.getElementById('editUserForm').action = user.update_url;

        // Nếu là admin thì vô hiệu hóa chỉnh sửa trạng thái
        const statusGroup = document.getElementById('edit_status_group');
        if (user.role === 'admin') {
            statusGroup.style.display = 'none';
        } else {
            statusGroup.style.display = 'block';
        }

        document.getElementById('editCategoryModal')?.classList.remove('is-active');
        document.getElementById('editUserModal').classList.add('is-active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('is-active');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.custom-modal-backdrop.is-active').forEach(m => m.classList.remove('is-active'));
        }
    });

    document.querySelectorAll('.custom-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('is-active');
            }
        });
    });
</script>
@endpush
@endsection
