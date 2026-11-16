<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - Travel Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F1F4F9;
            color: #111827;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Thanh điều hướng Header */
        .navbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid #E5E7EB;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 36px;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #111827;
            font-weight: 800;
            font-size: 17px;
            letter-spacing: 0.05em;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background-color: #0066FF;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
        }

        .brand-icon svg {
            width: 18px;
            height: 18px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 28px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: #4B5563;
            font-size: 14.5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.15s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: #0066FF;
        }

        .nav-link svg {
            width: 18px;
            height: 18px;
            stroke-width: 1.8;
        }

        .user-nav-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #111827;
            font-size: 14px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            border: 1px solid #E5E7EB;
            transition: background-color 0.15s ease;
        }

        .user-nav-profile:hover {
            background-color: #F9FAFB;
        }

        .user-nav-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #E0E7FF;
        }

        /* Container & Breadcrumb */
        .main-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 24px 24px 48px;
            flex: 1;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13.5px;
            margin-bottom: 24px;
        }

        .breadcrumb a {
            color: #0066FF;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb-separator {
            color: #9CA3AF;
        }

        .breadcrumb-current {
            color: #6B7280;
            font-weight: 500;
        }

        /* Bố cục 2 cột chính */
        .profile-layout {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 24px;
            align-items: start;
        }

        /* Cột bên trái: Thẻ tóm tắt hồ sơ */
        .profile-summary-card {
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            padding: 36px 28px;
            text-align: center;
            border: 1px solid rgba(229, 231, 235, 0.6);
        }

        .avatar-wrapper {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 18px;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background: #E5E7EB;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            border: 3px solid #FFFFFF;
        }

        .avatar-fallback {
            display: grid;
            place-items: center;
            color: #374151;
            font-size: 42px;
            font-weight: 700;
        }

        .avatar-fallback[hidden] {
            display: none;
        }

        .avatar-upload-btn {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #0066FF;
            border: 2.5px solid #FFFFFF;
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 102, 255, 0.35);
            transition: transform 0.15s ease, background-color 0.15s ease;
        }

        .avatar-upload-btn:hover {
            transform: scale(1.08);
            background-color: #0052CC;
        }

        .avatar-upload-btn svg {
            width: 16px;
            height: 16px;
        }

        .profile-name {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 4px;
        }

        .profile-location {
            font-size: 13.5px;
            color: #6B7280;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-bottom: 12px;
        }

        .profile-location svg {
            width: 15px;
            height: 15px;
            color: #9CA3AF;
        }

        .badge-explorer {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background-color: #FEF9C3;
            color: #854D0E;
            font-size: 12.5px;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 9999px;
            margin-bottom: 24px;
        }

        .badge-explorer svg {
            width: 14px;
            height: 14px;
            fill: #EAB308;
            stroke: #EAB308;
        }

        /* Thống kê 3 chỉ số */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            padding: 16px 0;
            border-top: 1px solid #F3F4F6;
            border-bottom: 1px solid #F3F4F6;
            margin-bottom: 26px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 20px;
            font-weight: 800;
            color: #0066FF;
            line-height: 1.2;
        }

        .stat-label {
            font-size: 12.5px;
            color: #6B7280;
            margin-top: 4px;
            font-weight: 500;
        }

        /* Nút hành động bên trái */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-change-password {
            width: 100%;
            height: 44px;
            background-color: #FFFFFF;
            border: 1.5px solid #0066FF;
            border-radius: 10px;
            color: #0066FF;
            font-size: 14.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-change-password:hover {
            background-color: #F0F6FF;
        }

        .btn-logout {
            width: 100%;
            height: 44px;
            background-color: #FFFFFF;
            border: 1.5px solid #EF4444;
            border-radius: 10px;
            color: #EF4444;
            font-size: 14.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-logout:hover {
            background-color: #FEF2F2;
        }

        /* Cột bên phải: Form thông tin tài khoản */
        .profile-form-card {
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            padding: 38px 40px;
            border: 1px solid rgba(229, 231, 235, 0.6);
        }

        .form-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            padding-bottom: 18px;
            border-bottom: 1px solid #F3F4F6;
        }

        .header-title {
            font-size: 21px;
            font-weight: 800;
            color: #111827;
        }

        .header-subtitle {
            font-size: 13.5px;
            color: #6B7280;
            margin-top: 4px;
        }

        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #DCFCE7;
            color: #166534;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 9999px;
        }

        .badge-unverified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: #FEF3C7;
            color: #92400E;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 9999px;
            text-decoration: none;
        }

        .badge-verified svg, .badge-unverified svg {
            width: 15px;
            height: 15px;
        }

        .alert-box {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            line-height: 1.45;
            margin-bottom: 22px;
        }

        .alert-danger {
            background-color: #FEF2F2;
            color: #DC2626;
            border: 1px solid #FCA5A5;
        }

        .alert-success {
            background-color: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }

        /* Lưới các trường nhập liệu */
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 18px;
        }

        .form-group-full {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 7px;
        }

        .required-mark {
            color: #EF4444;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            width: 18px;
            height: 18px;
            pointer-events: none;
        }

        .form-control-custom {
            width: 100%;
            height: 46px;
            padding: 0 14px 0 42px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            background-color: #FFFFFF;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.15);
        }

        .form-control-custom.is-invalid {
            border-color: #EF4444;
            background-color: #FEF2F2;
        }

        select.form-control-custom {
            appearance: none;
            cursor: pointer;
            padding-right: 36px;
        }

        .select-arrow {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            pointer-events: none;
            width: 16px;
            height: 16px;
        }

        .textarea-custom {
            width: 100%;
            min-height: 90px;
            padding: 12px 14px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .textarea-custom:focus {
            outline: none;
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.15);
        }

        .field-error {
            display: block;
            font-size: 12.5px;
            color: #DC2626;
            margin-top: 5px;
            font-weight: 500;
        }

        /* Nút thao tác dưới cùng */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #F3F4F6;
        }

        .btn-reset {
            height: 44px;
            padding: 0 24px;
            background-color: #F3F4F6;
            color: #374151;
            border: none;
            border-radius: 8px;
            font-size: 14.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .btn-reset:hover {
            background-color: #E5E7EB;
        }

        .btn-save {
            height: 44px;
            padding: 0 24px;
            background-color: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 14.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background-color 0.15s ease, transform 0.05s ease;
        }

        .btn-save:hover {
            background-color: #0052CC;
        }

        .btn-save:active {
            transform: scale(0.99);
        }

        /* Modal đổi mật khẩu */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(17, 24, 39, 0.45);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content-box {
            max-width: 460px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
            padding: 38px 34px;
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(8px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 900px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }

            .form-grid-2 {
                grid-template-columns: 1fr;
            }

            .navbar {
                padding: 0 20px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <!-- Header Navigation Bar -->
    @include('partials.site-header')

    <!-- Main Container -->
    <main class="main-container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Đường dẫn trang">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Hồ sơ cá nhân</span>
        </nav>

        <!-- Layout 2 cột -->
        <div class="profile-layout">
            <!-- Cột trái: Tóm tắt thông tin người dùng -->
            <aside class="profile-summary-card">
                <div class="avatar-wrapper">
                    <span class="avatar-img avatar-fallback" id="userAvatarFallback" @if($user->avatar) hidden @endif
                        aria-label="Ảnh đại diện mặc định">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    <img
                        @if($user->avatar) src="{{ $user->avatar }}" @else hidden @endif
                        alt="{{ $user->name }}"
                        class="avatar-img"
                        id="userAvatarDisplay"
                        onerror="this.hidden = true; document.getElementById('userAvatarFallback').hidden = false"
                    >
                    <!-- Nút camera tải ảnh đại diện -->
                    <label for="directAvatarUploadInput" class="avatar-upload-btn" title="Thay đổi ảnh đại diện">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                    </label>
                    <input
                        type="file"
                        id="directAvatarUploadInput"
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        style="display: none;"
                        aria-describedby="avatarUploadError"
                    >
                </div>
                <p id="avatarUploadError" class="field-error" role="alert" hidden></p>

                <h1 class="profile-name">{{ $user->name }}</h1>

                <p class="profile-location">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="10" r="3"></circle>
                        <path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 7 8 11.7z"></path>
                    </svg>
                    <span>{{ $user->city ?: 'Đà Nẵng' }}, Việt Nam</span>
                </p>

                <div>
                    <span class="badge-explorer">
                        <svg viewBox="0 0 24 24">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        Explorer Member
                    </span>
                </div>

                <!-- Thống kê hoạt động -->
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">{{ $tripsCount }}</div>
                        <div class="stat-label">Chuyến đi</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $favoritesCount }}</div>
                        <div class="stat-label">Yêu thích</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $reviewsCount }}</div>
                        <div class="stat-label">Đánh giá</div>
                    </div>
                </div>

                <!-- Các nút hành động bên trái -->
                <div class="action-buttons">
                    <button type="button" class="btn-change-password" id="openChangePasswordModalBtn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="7.5" cy="15.5" r="5.5"></circle>
                            <path d="M21 2l-9.6 9.6"></path>
                            <path d="M15.5 7.5l3 3L22 7l-3-3"></path>
                        </svg>
                        Đổi mật khẩu
                    </button>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-logout">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Cột phải: Biểu mẫu thông tin tài khoản -->
            <section class="profile-form-card" aria-label="Cập nhật thông tin tài khoản">
                <div class="form-card-header">
                    <div>
                        <h2 class="header-title">Thông tin tài khoản</h2>
                        <p class="header-subtitle">Quản lý và cập nhật thông tin cá nhân của bạn</p>
                    </div>

                    @if($user->hasVerifiedEmail())
                        <div class="badge-verified">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Đã xác minh Email
                        </div>
                    @else
                        <a href="{{ route('verification.notice') }}" class="badge-unverified" title="Nhấn để xác minh email">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            Chưa xác minh Email
                        </a>
                    @endif
                </div>

                <form
                    action="{{ route('profile.update') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="profileUpdateForm"
                    novalidate
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="edit_version" id="profileEditVersion" value="{{ \App\Support\FormInput::oldText('edit_version', $user->editVersion()) }}">

                    <!-- Hàng 1: Họ tên & Email -->
                    <div class="form-grid-2">
                        <div>
                            <label class="form-label" for="name">
                                Họ và tên <span class="required-mark">*</span>
                            </label>
                            <div class="input-group-custom">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control-custom @error('name') is-invalid @enderror"
                                    value="{{ \App\Support\FormInput::oldText('name', $user->name) }}"
                                    required
                                >
                            </div>
                            @error('name')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="email">
                                Địa chỉ Email <span class="required-mark">*</span>
                            </label>
                            <div class="input-group-custom">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control-custom @error('email') is-invalid @enderror"
                                    value="{{ \App\Support\FormInput::oldText('email', $user->email) }}"
                                    required
                                >
                            </div>
                            @error('email')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Hàng 2: Số điện thoại & Thành phố sinh sống -->
                    <div class="form-grid-2">
                        <div>
                            <label class="form-label" for="phone">
                                Số điện thoại
                            </label>
                            <div class="input-group-custom">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <input
                                    type="text"
                                    id="phone"
                                    name="phone"
                                    class="form-control-custom @error('phone') is-invalid @enderror"
                                    placeholder="0912 345 678"
                                    value="{{ \App\Support\FormInput::oldText('phone', $user->phone) }}"
                                >
                            </div>
                            @error('phone')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="city">
                                Thành phố sinh sống
                            </label>
                            <div class="input-group-custom">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="10" r="3"></circle>
                                    <path d="M12 21.7C17.3 17 20 13 20 10a8 8 0 1 0-16 0c0 3 2.7 7 8 11.7z"></path>
                                </svg>
                                <select
                                    id="city"
                                    name="city"
                                    class="form-control-custom @error('city') is-invalid @enderror"
                                >
                                    <option value="">Chọn thành phố sinh sống</option>
                                    @foreach($cities as $cityItem)
                                        <option
                                            value="{{ $cityItem }}"
                                            {{ \App\Support\FormInput::oldText('city', $user->city) === $cityItem ? 'selected' : '' }}
                                        >
                                            {{ $cityItem }}
                                        </option>
                                    @endforeach
                                </select>
                                <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                            @error('city')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Hàng 3: Đường dẫn ảnh đại diện -->
                    <div class="form-group-full">
                        <label class="form-label" for="avatar_url">
                            Đường dẫn ảnh đại diện (Avatar URL)
                        </label>
                        <div class="input-group-custom">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                            </svg>
                            <input
                                type="url"
                                id="avatar_url"
                                name="avatar_url"
                                class="form-control-custom @error('avatar_url') is-invalid @enderror"
                                placeholder="https://images.unsplash.com/photo-..."
                                value="{{ \App\Support\FormInput::oldText('avatar_url', $user->avatar) }}"
                            >
                        </div>
                        @error('avatar_url')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Hàng 4: Giới thiệu ngắn (Bio) -->
                    <div class="form-group-full">
                        <label class="form-label" for="bio">
                            Giới thiệu ngắn (Bio)
                        </label>
                        <textarea
                            id="bio"
                            name="bio"
                            class="textarea-custom @error('bio') is-invalid @enderror"
                            placeholder="Đam mê du lịch tự túc, nhiếp ảnh phong cảnh và khám phá ẩm thực đường phố các vùng miền."
                        >{{ \App\Support\FormInput::oldText('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Các nút thao tác góc dưới phải -->
                    <div class="form-actions">
                        <button type="reset" class="btn-reset" id="btnResetForm">
                            Đặt lại
                        </button>
                        <button type="submit" class="btn-save" id="btnSaveProfile">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <!-- Modal Đổi mật khẩu (Khớp chính xác Hình 9 / Image 3) -->
    <div class="modal-overlay" id="changePasswordModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-content-box">
            <div style="text-align: center; margin-bottom: 24px;">
                <h2 id="modalTitle" style="font-size: 26px; font-weight: 800; color: #111827;">Đổi mật khẩu</h2>
                <p style="font-size: 13.5px; color: #4B5563; margin-top: 6px;">Cập nhật mật khẩu định kỳ để bảo vệ tài khoản của bạn</p>
            </div>

            <div id="modalAlertBox" style="display: none; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px;"></div>

            <form action="{{ route('profile.password.update') }}" method="POST" id="modalPasswordForm" novalidate>
                @csrf

                <!-- Mật khẩu hiện tại -->
                <div style="margin-bottom: 18px;">
                    <div style="position: relative; display: flex; align-items: center;">
                        <input
                            type="password"
                            id="modal_current_password"
                            name="current_password"
                            class="form-control-custom"
                            style="padding-left: 16px; padding-right: 44px;"
                            placeholder="Mật khẩu hiện tại"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-modal-password-btn"
                            data-target="modal_current_password"
                            style="position: absolute; right: 14px; background: none; border: none; cursor: pointer; color: #6B7280;"
                            aria-label="Ẩn/hiện mật khẩu"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    <span class="field-error" id="modalErrorCurrentPassword" style="display: none;"></span>
                </div>

                <!-- Mật khẩu mới -->
                <div style="margin-bottom: 18px;">
                    <div style="position: relative; display: flex; align-items: center;">
                        <input
                            type="password"
                            id="modal_password"
                            name="password"
                            class="form-control-custom"
                            style="padding-left: 16px; padding-right: 44px;"
                            placeholder="Mật khẩu mới"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-modal-password-btn"
                            data-target="modal_password"
                            style="position: absolute; right: 14px; background: none; border: none; cursor: pointer; color: #6B7280;"
                            aria-label="Ẩn/hiện mật khẩu"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    <span class="field-error" id="modalErrorPassword" style="display: none;"></span>
                </div>

                <!-- Xác nhận mật khẩu mới -->
                <div style="margin-bottom: 24px;">
                    <div style="position: relative; display: flex; align-items: center;">
                        <input
                            type="password"
                            id="modal_password_confirmation"
                            name="password_confirmation"
                            class="form-control-custom"
                            style="padding-left: 16px; padding-right: 44px;"
                            placeholder="Xác nhận mật khẩu mới"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-modal-password-btn"
                            data-target="modal_password_confirmation"
                            style="position: absolute; right: 14px; background: none; border: none; cursor: pointer; color: #6B7280;"
                            aria-label="Ẩn/hiện mật khẩu"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                    <span class="field-error" id="modalErrorPasswordConfirmation" style="display: none;"></span>
                </div>

                <!-- Hai nút thao tác trong Modal khớp hoàn toàn Image 3 -->
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <button
                        type="submit"
                        style="width: 100%; height: 48px; background-color: #0066FF; color: #FFFFFF; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;"
                    >
                        Lưu mật khẩu mới
                    </button>
                    <button
                        type="button"
                        id="closeChangePasswordModalBtn"
                        style="width: 100%; height: 48px; background-color: #F3F4F6; color: #1F2937; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;"
                    >
                        Hủy bỏ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mở / Đóng Modal đổi mật khẩu
        const openModalBtn = document.getElementById('openChangePasswordModalBtn');
        const closeModalBtn = document.getElementById('closeChangePasswordModalBtn');
        const passwordModal = document.getElementById('changePasswordModal');

        if (openModalBtn && passwordModal) {
            openModalBtn.addEventListener('click', () => {
                passwordModal.classList.add('active');
            });
        }

        if (closeModalBtn && passwordModal) {
            closeModalBtn.addEventListener('click', () => {
                passwordModal.classList.remove('active');
            });
        }

        // Đóng modal khi bấm ra ngoài nền xám
        if (passwordModal) {
            passwordModal.addEventListener('click', (e) => {
                if (e.target === passwordModal) {
                    passwordModal.classList.remove('active');
                }
            });
        }

        // Xử lý ẩn/hiện mật khẩu trong Modal
        document.querySelectorAll('.toggle-modal-password-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    this.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
                } else {
                    targetInput.type = 'password';
                    this.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
                }
            });
        });

        // Tự động tải ảnh đại diện lên ngay khi chọn tệp từ nút Camera
        const avatarUploadInput = document.getElementById('directAvatarUploadInput');
        const userAvatarDisplay = document.getElementById('userAvatarDisplay');
        const topNavAvatar = document.getElementById('topNavAvatar');
        const avatarUrlInput = document.getElementById('avatar_url');
        const avatarFallback = document.getElementById('userAvatarFallback');
        const avatarUploadError = document.getElementById('avatarUploadError');
        const profileEditVersion = document.getElementById('profileEditVersion');

        if (avatarUploadInput) {
            avatarUploadInput.addEventListener('change', async function () {
                if (!this.files || !this.files[0]) return;

                const file = this.files[0];
                const formData = new FormData();
                formData.append('avatar', file);
                formData.append('edit_version', profileEditVersion.value);
                avatarUploadError.hidden = true;
                this.disabled = true;

                try {
                    const response = await fetch('{{ route("profile.avatar") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success || !data.avatar_url) {
                        avatarUploadError.textContent = data.errors?.avatar?.[0]
                            || data.errors?.edit_version?.[0]
                            || 'Không thể tải ảnh đại diện. Vui lòng thử lại.';
                        avatarUploadError.hidden = false;
                        return;
                    }

                    avatarFallback.hidden = true;
                    userAvatarDisplay.hidden = false;
                    userAvatarDisplay.src = data.avatar_url;
                    if (topNavAvatar) topNavAvatar.src = data.avatar_url;
                    avatarUrlInput.defaultValue = avatarUrlInput.value = data.avatar_url;
                    profileEditVersion.defaultValue = profileEditVersion.value = data.edit_version;
                } catch {
                    avatarUploadError.textContent = 'Không thể tải ảnh đại diện. Vui lòng thử lại.';
                    avatarUploadError.hidden = false;
                } finally {
                    this.value = '';
                    this.disabled = false;
                }
            });
        }
    </script>
    @include('partials.popup-notification')
</body>
</html>
