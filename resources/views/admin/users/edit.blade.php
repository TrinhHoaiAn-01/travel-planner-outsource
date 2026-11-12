@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Người Dùng - ' . $user->name)
@section('breadcrumb', 'Chỉnh sửa người dùng')

@push('styles')
<style>
    .admin-header-bar,
    .admin-breadcrumbs {
        display: none !important;
    }

    .admin-content-body {
        padding: 24px 32px 48px 32px;
    }

    .form-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .form-page-title {
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

    .user-form-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #F1F5F9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        padding: 32px;
        max-width: 760px;
    }

    .form-grid-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-col-full {
        grid-column: span 2;
    }

    .form-group-item label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .form-control-input,
    .form-control-select,
    .form-control-textarea {
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
    .form-control-select:focus,
    .form-control-textarea:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .field-error-text {
        font-size: 12px;
        color: #EF4444;
        margin-top: 4px;
        display: block;
    }

    .security-notice-box {
        background-color: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #1E40AF;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-action-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #F1F5F9;
    }

    .btn-secondary-cancel {
        padding: 10px 20px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        color: #475569;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-secondary-cancel:hover {
        background-color: #F1F5F9;
        color: #0F172A;
    }

    .btn-primary-submit {
        padding: 10px 24px;
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

    @media (max-width: 640px) {
        .form-grid-row {
            grid-template-columns: 1fr;
        }
        .form-col-full {
            grid-column: span 1;
        }
    }
</style>
@endpush

@section('content')
<div class="user-edit-container">
    {{-- Header & Quay lại --}}
    <div class="form-header-row">
        <h1 class="form-page-title">Chỉnh Sửa Người Dùng</h1>
        <a href="{{ route('admin.users.index') }}" class="btn-back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Quay lại danh sách</span>
        </a>
    </div>

    {{-- Breadcrumb: Admin / Người dùng / Chỉnh sửa --}}
    <div class="user-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="sep">/</span>
        <a href="{{ route('admin.users.index') }}">Người dùng</a>
        <span class="sep">/</span>
        <span class="current">Chỉnh sửa: {{ $user->name }}</span>
    </div>

    {{-- Form Card --}}
    <div class="user-form-card">
        @if($user->role === 'admin')
            <div class="security-notice-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <span>Tài khoản Quản trị viên được bảo vệ đặc biệt để đảm bảo tính sẵn sàng quản trị của hệ thống.</span>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid-row">
                <div class="form-group-item">
                    <label for="name">Họ và tên <span style="color: #EF4444;">*</span></label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           class="form-control-input"
                           required>
                    @error('name')
                        <span class="field-error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group-item">
                    <label for="email">Địa chỉ Email <span style="color: #EF4444;">*</span></label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           class="form-control-input"
                           required>
                    @error('email')
                        <span class="field-error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-grid-row">
                <div class="form-group-item">
                    <label for="password">Mật khẩu mới (Để trống nếu không đổi)</label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control-input"
                           placeholder="Nhập mật khẩu mới">
                    @error('password')
                        <span class="field-error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group-item">
                    <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="form-control-input"
                           placeholder="Xác nhận lại mật khẩu">
                </div>
            </div>

            <div class="form-grid-row">
                <div class="form-group-item">
                    <label for="role">Vai trò</label>
                    <select id="role" name="role" class="form-control-select">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Thành viên (Member)</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Quản trị viên (Administrator)</option>
                    </select>
                    @error('role')
                        <span class="field-error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group-item">
                    <label for="is_active">Trạng thái tài khoản</label>
                    <select id="is_active" name="is_active" class="form-control-select">
                        <option value="1" {{ (string) old('is_active', (int) $user->is_active) === '1' ? 'selected' : '' }}>Hoạt động (Active)</option>
                        <option value="0" {{ (string) old('is_active', (int) $user->is_active) === '0' ? 'selected' : '' }}>Tạm khóa (Disabled)</option>
                    </select>
                    @error('is_active')
                        <span class="field-error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-grid-row">
                <div class="form-group-item">
                    <label for="phone">Số điện thoại</label>
                    <input type="text"
                           id="phone"
                           name="phone"
                           value="{{ old('phone', $user->phone) }}"
                           class="form-control-input"
                           placeholder="Ví dụ: 0912345678">
                    @error('phone')
                        <span class="field-error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group-item">
                    <label for="city">Tỉnh / Thành phố sinh sống</label>
                    <input type="text"
                           id="city"
                           name="city"
                           value="{{ old('city', $user->city) }}"
                           class="form-control-input"
                           placeholder="Ví dụ: Đà Nẵng">
                    @error('city')
                        <span class="field-error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-grid-row">
                <div class="form-group-item form-col-full">
                    <label for="bio">Tiểu sử / Giới thiệu</label>
                    <textarea id="bio"
                              name="bio"
                              rows="3"
                              class="form-control-textarea">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <span class="field-error-text">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-action-footer">
                <a href="{{ route('admin.users.index') }}" class="btn-secondary-cancel">Hủy bỏ</a>
                <button type="submit" class="btn-primary-submit">Cập Nhật Thông Tin</button>
            </div>
        </form>
    </div>
</div>
@endsection
