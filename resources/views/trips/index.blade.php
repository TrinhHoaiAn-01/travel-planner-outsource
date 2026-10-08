<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hành Trình & Chuyến Đi - Travel Planner</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #FFFFFF;
            color: #0F172A;
            line-height: 1.5;
            min-height: 100vh;
        }

        /* Top Navigation Header */
        .navbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 12px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: -0.5px;
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            background: #0066FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 9999px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            transition: all 0.2s;
        }

        .nav-item a:hover {
            color: #0066FF;
            background-color: #F1F5F9;
        }

        .nav-item.active a {
            background-color: #EBF5FF;
            color: #0066FF;
            font-weight: 600;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-admin {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            color: #0F172A;
            background: #FFFFFF;
            transition: background 0.2s;
        }

        .btn-admin:hover {
            background-color: #F8FAFC;
        }

        .user-dropdown {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #2563EB;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1E293B;
        }

        /* Container */
        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 28px 24px 60px;
        }

        /* Breadcrumb Matching Image 1 */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #64748B;
            margin-bottom: 12px;
        }

        .breadcrumb a {
            color: #0066FF;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Page Title Header Matching Image 1 */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-title {
            font-size: 32px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.6px;
            margin-bottom: 6px;
        }

        .page-subtitle {
            font-size: 15px;
            color: #64748B;
            font-weight: 400;
        }

        .btn-create-trip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #0066FF;
            color: #FFFFFF;
            padding: 12px 24px;
            border-radius: 9999px;
            font-size: 14.5px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0, 102, 255, 0.22);
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-create-trip:hover {
            background-color: #0052CC;
            transform: translateY(-1px);
        }

        /* Flash Message */
        .flash-alert {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }

        .flash-success {
            background-color: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        /* Status Filter Tabs Matching Image 1 */
        .tabs-and-search-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 30px;
            padding-bottom: 12px;
            border-bottom: 1px solid #F1F5F9;
        }

        .status-tabs {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .status-pill {
            padding: 8px 20px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 500;
            color: #0066FF;
            background-color: transparent;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .status-pill:hover {
            background-color: #EFF6FF;
        }

        .status-pill.active {
            background-color: #0066FF;
            color: #FFFFFF;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 102, 255, 0.2);
        }

        /* Compact Search Bar */
        .search-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #F8FAFC;
            padding: 8px 14px;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            flex-wrap: wrap;
        }

        .search-input-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 240px;
        }

        .search-input-group input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 13.5px;
            color: #0F172A;
            background: transparent;
        }

        .search-input-group input::placeholder {
            color: #94A3B8;
        }

        .date-filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
        }

        .date-input {
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 12.5px;
            color: #0F172A;
            outline: none;
            background: #FFFFFF;
        }

        .btn-filter {
            background-color: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            padding: 7px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-filter:hover {
            background-color: #0052CC;
        }

        .btn-reset {
            background-color: #FFFFFF;
            color: #475569;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-reset:hover {
            background-color: #F1F5F9;
        }

        /* Trips Grid - Exactly 3 Columns Matching Image 1 */
        .trips-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        @media (max-width: 1024px) {
            .trips-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .trips-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Trip Card Matching Image 1 Mockup */
        .trip-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            transition: transform 0.25s, box-shadow 0.25s;
            position: relative;
        }

        .trip-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        }

        .card-banner {
            position: relative;
            height: 200px;
            width: 100%;
            overflow: hidden;
            background: #E2E8F0;
        }

        .card-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
        }

        .trip-card:hover .card-banner img {
            transform: scale(1.04);
        }

        /* Badges Matching Image 1 */
        .status-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            padding: 5px 14px;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 600;
            z-index: 10;
        }

        /* Sắp tới: Light cyan pill */
        .badge-planned {
            background-color: #E0F2FE;
            color: #0284C7;
        }

        /* Đang diễn ra: Soft amber/yellow pill */
        .badge-ongoing {
            background-color: #FEF3C7;
            color: #B45309;
        }

        /* Đã hoàn thành: Soft neutral gray pill */
        .badge-completed {
            background-color: #F1F5F9;
            color: #334155;
        }

        .badge-draft {
            background-color: #F8FAFC;
            color: #64748B;
        }

        /* Three dots options button */
        .menu-button {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #334155;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
            z-index: 10;
            transition: background 0.2s;
        }

        .menu-button:hover {
            background: #FFFFFF;
            color: #0F172A;
        }

        /* Dropdown Menu */
        .menu-dropdown {
            display: none;
            position: absolute;
            top: 52px;
            right: 14px;
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            min-width: 180px;
            z-index: 20;
            overflow: hidden;
        }

        .menu-dropdown.show {
            display: block;
        }

        .menu-item-btn {
            width: 100%;
            text-align: left;
            padding: 10px 16px;
            border: none;
            background: transparent;
            font-size: 13.5px;
            font-weight: 500;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.15s;
        }

        .menu-item-btn:hover {
            background: #F8FAFC;
            color: #0066FF;
        }

        .card-body {
            padding: 20px 22px 22px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        /* Date metadata with blue calendar icon */
        .trip-date-meta {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: #475569;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .trip-date-meta svg {
            color: #0066FF;
            flex-shrink: 0;
        }

        .trip-name {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .trip-description {
            font-size: 13.5px;
            color: #64748B;
            line-height: 1.55;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 20px;
            min-height: 42px;
        }

        /* Card Footer Matching Image 1 */
        .card-footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .budget-block {
            display: flex;
            flex-direction: column;
        }

        .budget-label {
            font-size: 12px;
            color: #64748B;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .budget-amount {
            font-size: 17px;
            font-weight: 700;
            color: #059669;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-calendar-icon {
            width: 38px;
            height: 38px;
            border: 1px solid #CBD5E1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FFFFFF;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-calendar-icon:hover {
            background-color: #F8FAFC;
            border-color: #94A3B8;
            color: #0066FF;
        }

        .btn-primary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 22px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 600;
            background-color: #0066FF;
            color: #FFFFFF;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary-action:hover {
            background-color: #0052CC;
        }

        /* Empty State */
        .empty-state {
            background: #FFFFFF;
            border: 1px dashed #CBD5E1;
            border-radius: 18px;
            padding: 64px 24px;
            text-align: center;
            grid-column: 1 / -1;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            background: #EFF6FF;
            color: #0066FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 6px;
        }

        .empty-desc {
            font-size: 14px;
            color: #64748B;
            max-width: 440px;
            margin: 0 auto 20px;
        }

        /* Modal Styles for Image 2 ("Lên Kế Hoạch Chuyến Đi Mới") */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-card {
            background: #FFFFFF;
            border-radius: 20px;
            width: 100%;
            max-width: 680px;
            padding: 36px 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            position: relative;
            animation: modalFadeIn 0.25s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-16px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-close-btn {
            position: absolute;
            top: 24px;
            right: 24px;
            background: transparent;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close-btn:hover {
            color: #0F172A;
            background: #F1F5F9;
        }

        /* Modal Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #0F172A;
            margin-bottom: 8px;
        }

        .form-label .required {
            color: #EF4444;
            margin-left: 2px;
        }

        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        @media (max-width: 640px) {
            .form-row-2 {
                grid-template-columns: 1fr;
            }
            .modal-card {
                padding: 24px 20px;
            }
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            border: 1px solid #CBD5E1;
            border-radius: 12px;
            background: #FFFFFF;
            transition: all 0.2s;
            overflow: hidden;
        }

        .input-wrapper:focus-within {
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }

        .input-icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background-color: #F8FAFC;
            border-right: 1px solid #E2E8F0;
            color: #0066FF;
            flex-shrink: 0;
        }

        .input-suffix {
            padding: 0 16px;
            color: #64748B;
            font-size: 14px;
            font-weight: 500;
            background: #F8FAFC;
            height: 44px;
            display: flex;
            align-items: center;
            border-left: 1px solid #E2E8F0;
            flex-shrink: 0;
        }

        .form-input, .form-select {
            width: 100%;
            height: 44px;
            padding: 0 14px;
            border: none;
            outline: none;
            font-size: 14px;
            color: #0F172A;
            font-family: inherit;
            background: transparent;
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        .form-textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 12px;
            outline: none;
            font-size: 14px;
            color: #0F172A;
            font-family: inherit;
            background: #FFFFFF;
            min-height: 110px;
            line-height: 1.6;
            resize: vertical;
            transition: all 0.2s;
        }

        .form-textarea:focus {
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }

        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #F1F5F9;
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 22px;
            border-radius: 10px;
            background-color: #F8FAFC;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #E2E8F0;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background-color: #F1F5F9;
            color: #0F172A;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 10px;
            background-color: #0066FF;
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.25);
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #0052CC;
        }

        .pagination-container {
            margin-top: 36px;
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                </svg>
            </div>
            <span>TRAVEL PLANNER</span>
        </a>

        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="{{ route('home') }}">Trang chủ</a>
            </li>
            <li class="nav-item active">
                <a href="{{ route('trips.index') }}">Chuyến đi</a>
            </li>
        </ul>

        <div class="navbar-actions">
            @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="btn-admin">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Admin
                </a>
            @endif

            <div class="user-dropdown">
                <a href="{{ route('profile.show') }}" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: inherit;" title="Hồ sơ cá nhân">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="user-name">{{ Auth::user()->name ?? 'Người dùng' }}</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline; margin-left: 6px;">
                    @csrf
                    <button type="submit" title="Đăng xuất" style="background: none; border: none; cursor: pointer; color: #94A3B8;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" x2="9" y1="12" y2="12"></line></svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Breadcrumb Matching Image 1 -->
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <span>Chuyến đi của tôi</span>
        </nav>

        <!-- Flash message -->
        @if(session('success'))
            <div class="flash-alert flash-success">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; cursor: pointer; color: #065F46; font-size: 16px;">&times;</button>
            </div>
        @endif

        <!-- Page Header Matching Image 1 -->
        <div class="page-header">
            <div>
                <h1 class="page-title">Hành Trình & Chuyến Đi</h1>
                <p class="page-subtitle">Quản lý lịch trình, hoạt động từng ngày và theo dõi chi tiết du lịch của bạn</p>
            </div>
            <button type="button" class="btn-create-trip" onclick="openCreateTripModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Tạo chuyến đi mới
            </button>
        </div>

        <!-- Filter & Search Section Matching Image 1 -->
        <div class="tabs-and-search-container">
            <!-- Status Tabs Matching Image 1 -->
            <div class="status-tabs">
                <a href="{{ route('trips.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                   class="status-pill {{ $currentStatus === 'all' || empty($currentStatus) ? 'active' : '' }}">
                    Tất cả ({{ $counts['all'] ?? 0 }})
                </a>
                <a href="{{ route('trips.index', array_merge(request()->query(), ['status' => 'planned'])) }}"
                   class="status-pill {{ $currentStatus === 'planned' ? 'active' : '' }}">
                    Sắp tới ({{ $counts['planned'] ?? 0 }})
                </a>
                <a href="{{ route('trips.index', array_merge(request()->query(), ['status' => 'ongoing'])) }}"
                   class="status-pill {{ $currentStatus === 'ongoing' ? 'active' : '' }}">
                    Đang diễn ra ({{ $counts['ongoing'] ?? 0 }})
                </a>
                <a href="{{ route('trips.index', array_merge(request()->query(), ['status' => 'completed'])) }}"
                   class="status-pill {{ $currentStatus === 'completed' ? 'active' : '' }}">
                    Đã hoàn thành ({{ $counts['completed'] ?? 0 }})
                </a>
            </div>

            <!-- Integrated Search and Date Filter Bar -->
            <form action="{{ route('trips.index') }}" method="GET" class="search-bar">
                <input type="hidden" name="status" value="{{ $currentStatus }}">

                <div class="search-input-group">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="search" value="{{ $searchKeyword }}" placeholder="Tìm kiếm theo tên hoặc mô tả chuyến đi...">
                </div>

                <div class="date-filter-group">
                    <span>Từ:</span>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="date-input">
                    <span>Đến:</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="date-input">
                </div>

                <button type="submit" class="btn-filter">Tìm kiếm</button>

                @if($searchKeyword || $startDate || $endDate || ($currentStatus && $currentStatus !== 'all'))
                    <a href="{{ route('trips.index') }}" class="btn-reset">Đặt lại</a>
                @endif
            </form>
        </div>

        <!-- Trips Grid Matching Image 1 -->
        <div class="trips-grid">
            @forelse($trips as $trip)
                @php
                    // Tính số ngày và số đêm
                    $durationText = 'Chưa xác định thời lượng';
                    if ($trip->start_date && $trip->end_date) {
                        $days = $trip->start_date->diffInDays($trip->end_date) + 1;
                        $nights = max(0, $days - 1);
                        $durationText = "{$days} ngày {$nights} đêm";
                    }

                    // Xác định trạng thái hiển thị
                    $badgeClass = 'badge-planned';
                    $badgeText = 'Sắp tới';
                    $isCompleted = ($trip->status === \App\Models\Trip::STATUS_COMPLETED);

                    $isOngoing = false;
                    if ($trip->start_date && $trip->end_date && now()->between($trip->start_date, $trip->end_date) && ! $isCompleted) {
                        $isOngoing = true;
                    }

                    if ($isCompleted) {
                        $badgeClass = 'badge-completed';
                        $badgeText = 'Đã hoàn thành';
                    } elseif ($isOngoing || $trip->status === 'ongoing') {
                        $badgeClass = 'badge-ongoing';
                        $badgeText = 'Đang diễn ra';
                    } elseif ($trip->status === \App\Models\Trip::STATUS_DRAFT) {
                        $badgeClass = 'badge-draft';
                        $badgeText = 'Bản nháp';
                    }

                    // Hình ảnh minh họa theo tên chuyến đi hoặc ảnh mẫu tương ứng
                    $bannerImage = 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=800&q=80';
                    $lowerName = mb_strtolower($trip->name);
                    if (str_contains($lowerName, 'hạ long') || str_contains($lowerName, 'ha long')) {
                        $bannerImage = 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&q=80';
                    } elseif (str_contains($lowerName, 'sa pa') || str_contains($lowerName, 'fansipan') || str_contains($lowerName, 'sapa')) {
                        $bannerImage = 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80';
                    } elseif (str_contains($lowerName, 'đà nẵng') || str_contains($lowerName, 'hội an') || str_contains($lowerName, 'da nang')) {
                        $bannerImage = 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80';
                    }
                @endphp

                <div class="trip-card">
                    <!-- Banner -->
                    <div class="card-banner">
                        <span class="status-badge {{ $badgeClass }}">{{ $badgeText }}</span>

                        <!-- Three dots options button -->
                        <button type="button" class="menu-button" title="Tùy chọn" onclick="toggleMenu('menu-{{ $trip->id }}', event)">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="12" cy="5" r="1"></circle>
                                <circle cx="12" cy="19" r="1"></circle>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="menu-{{ $trip->id }}" class="menu-dropdown">
                            <!-- Nhân bản chuyến đi (Nguyễn Trần Thành) -->
                            <form action="{{ route('trips.clone', $trip->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="menu-item-btn">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg>
                                    Nhân bản chuyến đi
                                </button>
                            </form>

                            <!-- Mở lại chuyến đi nếu đã hoàn thành (Nguyễn Trần Thành) -->
                            @if($isCompleted)
                                <form action="{{ route('trips.reopen', $trip->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="menu-item-btn">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M8 16H3v5"></path></svg>
                                        Mở lại chuyến đi
                                    </button>
                                </form>
                            @endif
                        </div>

                        <img src="{{ $bannerImage }}" alt="{{ $trip->name }}" loading="lazy">
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <!-- Date metadata with blue calendar icon -->
                        <div class="trip-date-meta">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                <line x1="16" x2="16" y1="2" y2="6"></line>
                                <line x1="8" x2="8" y1="2" y2="6"></line>
                                <line x1="3" x2="21" y1="10" y2="10"></line>
                            </svg>
                            <span>
                                {{ $trip->start_date ? $trip->start_date->format('d/m/Y') : '--/--/----' }} - {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : '--/--/----' }} • {{ $durationText }}
                            </span>
                        </div>

                        <h3 class="trip-name">{{ $trip->name }}</h3>
                        <p class="trip-description">{{ $trip->description ?: 'Bà Nà Hills, Bán đảo Sơn Trà, Ngũ Hành Sơn và phố đèn lồng Hội An thơ mộng.' }}</p>

                        <!-- Footer -->
                        <div class="card-footer">
                            <div class="budget-block">
                                @if($isCompleted)
                                    <span class="budget-label">Tổng chi thực tế</span>
                                    <span class="budget-amount">{{ number_format($trip->expenses->sum('amount'), 0, ',', '.') }}đ</span>
                                @else
                                    <span class="budget-label">Ngân sách dự kiến</span>
                                    <span class="budget-amount">{{ number_format($trip->budget, 0, ',', '.') }}đ</span>
                                @endif
                            </div>

                            <div class="action-buttons">
                                <button type="button" class="btn-calendar-icon" title="Xem lịch trình" onclick="alert('Xem lịch chuyến đi: {{ $trip->name }}');">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                        <line x1="16" x2="16" y1="2" y2="6"></line>
                                        <line x1="8" x2="8" y1="2" y2="6"></line>
                                        <line x1="3" x2="21" y1="10" y2="10"></line>
                                    </svg>
                                </button>

                                @if($isCompleted)
                                    <button type="button" class="btn-primary-action" onclick="alert('Xem lại chi tiết chuyến đi #{{ $trip->id }}');">Xem lại</button>
                                @else
                                    <button type="button" class="btn-primary-action" onclick="alert('Xem lịch trình chuyến đi #{{ $trip->id }}');">Lịch trình</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <h3 class="empty-title">Không tìm thấy chuyến đi nào</h3>
                    <p class="empty-desc">Không có chuyến đi nào phù hợp với bộ lọc hiện tại. Bạn hãy thử điều chỉnh từ khóa tìm kiếm hoặc chọn bộ lọc trạng thái khác.</p>
                    <a href="{{ route('trips.index') }}" class="btn-filter" style="display: inline-block; text-decoration: none;">Xem tất cả chuyến đi</a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($trips->hasPages())
            <div class="pagination-container">
                {{ $trips->links() }}
            </div>
        @endif
    </main>

    <!-- Modal Tạo Chuyến Đi Mới - Tái hiện chuẩn 100% Ảnh 2 Mockup -->
    <div id="createTripModal" class="modal-overlay" onclick="closeModalOnBackdrop(event)">
        <div class="modal-card">
            <button type="button" class="modal-close-btn" onclick="closeCreateTripModal()" title="Đóng">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <div style="margin-bottom: 24px;">
                <h2 style="font-size: 24px; font-weight: 700; color: #0F172A; margin-bottom: 6px;">Lên Kế Hoạch Chuyến Đi Mới</h2>
                <p style="font-size: 14px; color: #64748B;">Điền thông tin cơ bản để bắt đầu sắp xếp lịch trình chi tiết và quản lý ngân sách</p>
            </div>

            <form action="{{ route('trips.store') }}" method="POST">
                @csrf

                <!-- Tên chuyến đi * -->
                <div class="form-group">
                    <label for="modal_name" class="form-label">
                        Tên chuyến đi <span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <div class="input-icon-box">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <input type="text"
                               name="name"
                               id="modal_name"
                               class="form-input"
                               placeholder="Hành trình Khám phá Đà Nẵng - Hội An"
                               value="Hành trình Khám phá Đà Nẵng - Hội An"
                               required>
                    </div>
                </div>

                <!-- Ngày bắt đầu * & Ngày kết thúc * -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="modal_start_date" class="form-label">
                            Ngày bắt đầu <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <div class="input-icon-box">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                    <line x1="16" x2="16" y1="2" y2="6"></line>
                                    <line x1="8" x2="8" y1="2" y2="6"></line>
                                    <line x1="3" x2="21" y1="10" y2="10"></line>
                                </svg>
                            </div>
                            <input type="date"
                                   name="start_date"
                                   id="modal_start_date"
                                   class="form-input"
                                   value="2026-10-10"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="modal_end_date" class="form-label">
                            Ngày kết thúc <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <div class="input-icon-box">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                    <line x1="16" x2="16" y1="2" y2="6"></line>
                                    <line x1="8" x2="8" y1="2" y2="6"></line>
                                    <line x1="3" x2="21" y1="10" y2="10"></line>
                                </svg>
                            </div>
                            <input type="date"
                                   name="end_date"
                                   id="modal_end_date"
                                   class="form-input"
                                   value="2026-10-15"
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Tổng ngân sách dự kiến & Địa bàn trọng tâm -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="modal_budget" class="form-label">
                            Tổng ngân sách dự kiến (VNĐ)
                        </label>
                        <div class="input-wrapper">
                            <div class="input-icon-box">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="12" x="2" y="6" rx="2"></rect>
                                    <circle cx="12" cy="12" r="2"></circle>
                                    <path d="M6 12h.01M18 12h.01"></path>
                                </svg>
                            </div>
                            <input type="number"
                                   name="budget"
                                   id="modal_budget"
                                   class="form-input"
                                   value="15000000"
                                   min="0"
                                   step="100000">
                            <span class="input-suffix">đ</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="modal_destination_area" class="form-label">
                            Địa bàn trọng tâm
                        </label>
                        <div class="input-wrapper">
                            <div class="input-icon-box">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <select name="destination_area" id="modal_destination_area" class="form-select">
                                <option value="Đà Nẵng & Hội An" selected>Đà Nẵng & Hội An</option>
                                <option value="Hà Nội & Miền Bắc">Hà Nội & Miền Bắc</option>
                                <option value="Vịnh Hạ Long">Vịnh Hạ Long</option>
                                <option value="Sa Pa & Fansipan">Sa Pa & Fansipan</option>
                                <option value="Huế - Cố Đô">Huế - Cố Đô</option>
                                <option value="Nha Trang">Nha Trang</option>
                                <option value="Đà Lạt">Đà Lạt</option>
                                <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
                                <option value="Phú Quốc">Phú Quốc</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Ảnh bìa chuyến đi (Tùy chọn) -->
                <div class="form-group">
                    <label for="modal_cover_image" class="form-label">
                        Ảnh bìa chuyến đi (Tùy chọn)
                    </label>
                    <div class="input-wrapper">
                        <div class="input-icon-box">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                <circle cx="9" cy="9" r="2"></circle>
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                            </svg>
                        </div>
                        <input type="text"
                               name="cover_image"
                               id="modal_cover_image"
                               class="form-input"
                               placeholder="https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&1"
                               value="https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&1">
                    </div>
                </div>

                <!-- Mô tả & Ghi chú mục tiêu chuyến đi -->
                <div class="form-group">
                    <label for="modal_description" class="form-label">
                        Mô tả & Ghi chú mục tiêu chuyến đi
                    </label>
                    <textarea name="description"
                              id="modal_description"
                              class="form-textarea"
                              placeholder="Chuyến đi 6 ngày 5 đêm khám phá các điểm nổi tiếng tại Đà Nẵng và Hội An. Dự kiến tham quan Bà Nà Hills, tắm biển Mỹ Khê, chèo SUP ngắm bình minh và thưởng thức đặc sản mì Quảng, bánh mì Phượng.">Chuyến đi 6 ngày 5 đêm khám phá các điểm nổi tiếng tại Đà Nẵng và Hội An. Dự kiến tham quan Bà Nà Hills, tắm biển Mỹ Khê, chèo SUP ngắm bình minh và thưởng thức đặc sản mì Quảng, bánh mì Phượng.</textarea>
                </div>

                <!-- Nút thao tác -->
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeCreateTripModal()">Hủy bỏ</button>
                    <button type="submit" class="btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Lưu & Xem Lịch trình
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts for modal and interactive dropdowns -->
    <script>
        function openCreateTripModal() {
            document.getElementById('createTripModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeCreateTripModal() {
            document.getElementById('createTripModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function closeModalOnBackdrop(e) {
            if (e.target.id === 'createTripModal') {
                closeCreateTripModal();
            }
        }

        function toggleMenu(menuId, event) {
            event.stopPropagation();
            const targetMenu = document.getElementById(menuId);
            const allMenus = document.querySelectorAll('.menu-dropdown');
            allMenus.forEach(menu => {
                if (menu !== targetMenu) {
                    menu.classList.remove('show');
                }
            });
            targetMenu.classList.toggle('show');
        }

        document.addEventListener('click', function() {
            const allMenus = document.querySelectorAll('.menu-dropdown');
            allMenus.forEach(menu => menu.classList.remove('show'));
        });
    </script>
</body>
</html>
