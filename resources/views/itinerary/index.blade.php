<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch trình Ngày {{ $current_day }} - {{ $trip->name }} - Travel Planner</title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --primary: #0066FF;
            --primary-dark: #0052CC;
            --primary-light: #EBF5FF;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --bg-page: #F8FAFC;
            --card-bg: #FFFFFF;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --radius-card: 16px;
            --radius-pill: 9999px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.08);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* 1. Header Navigation */
        .navbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 12px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 800;
            font-size: 19px;
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
            gap: 12px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .nav-link.active {
            background-color: #EBF5FF;
            color: #0066FF;
            font-weight: 600;
        }

        .nav-link:hover:not(.active) {
            color: #0066FF;
            background-color: #F8FAFC;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-admin {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 20px;
            border: 1px solid #0F172A;
            background: #FFFFFF;
            color: #0F172A;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-admin:hover {
            background: #F8FAFC;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #0F172A;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            padding: 4px 10px;
            border-radius: 20px;
            transition: all 0.15s ease;
        }

        .user-dropdown:hover {
            background-color: #F1F5F9;
            color: #0066FF;
        }

        .user-avatar-img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            background: #E2E8F0;
        }

        .btn-detail-blue {
            background: #0066FF;
            color: #FFFFFF;
            padding: 7px 18px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-detail-blue:hover {
            background: #0052CC;
        }

        /* 2. Main Wrapper */
        .page-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 24px 60px;
            width: 100%;
            flex: 1;
        }

        /* Breadcrumb */
        .breadcrumb-box {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .breadcrumb-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .breadcrumb-link:hover {
            text-decoration: underline;
        }

        .breadcrumb-sep {
            color: #94A3B8;
        }

        .breadcrumb-current {
            color: var(--text-muted);
            font-weight: 500;
        }

        /* 3. Hero Trip Header Card */
        @php
            $bannerBg = 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=1400&q=80';
            $tripNameLower = mb_strtolower($trip->name);
            if (str_contains($tripNameLower, 'hà nội') || str_contains($tripNameLower, 'hoàn kiếm')) {
                $bannerBg = 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1400&q=80';
            } elseif (str_contains($tripNameLower, 'đà nẵng') || str_contains($tripNameLower, 'hội an')) {
                $bannerBg = 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=1400&q=80';
            } elseif (str_contains($tripNameLower, 'hạ long')) {
                $bannerBg = 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1400&q=80';
            }
            $nightsCount = max(0, $days_count - 1);
        @endphp
        .trip-hero-card {
            background: linear-gradient(rgba(15, 23, 42, 0.72), rgba(15, 23, 42, 0.82)), url('{{ $bannerBg }}') center/cover no-repeat;
            border-radius: 20px;
            padding: 36px 32px;
            color: #FFFFFF;
            margin-bottom: 24px;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .trip-hero-header-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 14px;
        }

        .hero-badges-line {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge-pill-status {
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            color: #FFFFFF;
        }

        .badge-pill-duration {
            padding: 4px 12px;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 700;
            background: #FFFFFF;
            color: var(--primary);
        }

        .trip-title-main {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .trip-meta-info {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 14.5px;
            color: #CBD5E1;
            flex-wrap: wrap;
        }

        .trip-meta-info span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hero-actions-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .btn-hero-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: var(--radius-pill);
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-hero-white {
            background: #FFFFFF;
            color: var(--text-dark);
            border: none;
        }

        .btn-hero-white:hover {
            background: #F1F5F9;
            transform: translateY(-2px);
        }

        .btn-hero-warning {
            background-color: #F59E0B;
            color: #0F172A;
            border: none;
            box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);
        }

        .btn-hero-warning:hover {
            background-color: #D97706;
            color: #0F172A;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3);
        }

        .btn-hero-edit {
            background: #FFFFFF;
            color: #0F172A;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .btn-hero-edit:hover {
            background: #F8FAFC;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        .btn-hero-delete {
            background: #FFFFFF;
            color: #DC2626;
            border: 1.5px solid #DC2626;
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.08);
        }

        .btn-hero-delete:hover {
            background: #FEF2F2;
            color: #B91C1C;
            border-color: #B91C1C;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.15);
        }

        .btn-hero-outline {
            background: transparent;
            color: #FFFFFF;
            border: 1.5px solid rgba(255, 255, 255, 0.4);
        }

        .btn-hero-outline:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: #FFFFFF;
        }

        /* 4. Days Tabs Navigation (Chuyển đổi từng ngày) */
        .days-nav-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: var(--shadow-sm);
        }

        .days-pills-list {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
            max-width: 100%;
        }

        .day-pill-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 18px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: #F8FAFC;
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
            min-width: 90px;
            cursor: pointer;
        }

        .day-pill-btn:hover {
            background: var(--primary-light);
            border-color: #BFDBFE;
            color: var(--primary);
        }

        .day-pill-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
        }

        .day-pill-title {
            font-size: 14px;
            font-weight: 800;
        }

        .day-pill-date {
            font-size: 11.5px;
            opacity: 0.85;
            font-weight: 500;
        }

        .day-pill-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.1);
            margin-top: 2px;
        }

        .day-pill-btn.active .day-pill-badge {
            background: rgba(255, 255, 255, 0.25);
            color: #FFFFFF;
        }

        .btn-add-activity-main {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: var(--radius-pill);
            background: var(--primary);
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 102, 255, 0.25);
            transition: var(--transition);
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-add-activity-main:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            color: #FFFFFF;
        }

        /* 5. Daily Schedule Header & Alert Banner */
        .schedule-header-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .schedule-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge-count-activities {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: var(--radius-pill);
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
        }

        .tips-banner-box {
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 12px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #1E40AF;
            font-size: 13.5px;
            margin-bottom: 24px;
        }

        /* 6. Timeline Activities List */
        .timeline-container {
            position: relative;
            padding-left: 32px;
        }

        .timeline-container::before {
            content: '';
            position: absolute;
            top: 15px;
            bottom: 20px;
            left: 11px;
            width: 2px;
            background: #CBD5E1;
        }

        .timeline-node {
            position: relative;
            margin-bottom: 24px;
        }

        .timeline-node:last-child {
            margin-bottom: 0;
        }

        .timeline-dot {
            position: absolute;
            left: -32px;
            top: 20px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #FFFFFF;
            border: 3px solid var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
            z-index: 2;
        }

        .activity-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .activity-card:hover {
            border-color: #CBD5E1;
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .activity-thumb-box {
            width: 130px;
            height: 100px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            background: #E2E8F0;
        }

        .activity-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .activity-info-box {
            flex: 1;
            min-width: 250px;
        }

        .activity-time-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
        }

        .activity-name-title {
            font-size: 17px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .activity-meta-line {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .badge-cat-tag {
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            background: #F1F5F9;
            color: #475569;
        }

        .activity-note-box {
            background: #F8FAFC;
            border-left: 3px solid #94A3B8;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13.5px;
            color: #475569;
            margin-bottom: 10px;
        }

        .activity-actions-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid #F1F5F9;
            margin-top: 6px;
        }

        .activity-price-tag {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--success);
        }

        .activity-btns {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-act-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid var(--border-color);
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 13px;
        }

        .btn-act-icon:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        /* 7. Empty State (Khi ngày chưa có hoạt động) */
        .empty-day-box {
            background: var(--card-bg);
            border: 2px dashed #CBD5E1;
            border-radius: var(--radius-card);
            padding: 50px 24px;
            text-align: center;
            margin-top: 8px;
        }

        .empty-icon-wrap {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .empty-desc {
            font-size: 14px;
            color: var(--text-muted);
            max-width: 460px;
            margin: 0 auto 20px;
            line-height: 1.5;
        }

        /* 8. Modal Thêm Hoạt Động Mới (Hình 19) */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .modal-card {
            background-color: #FFFFFF;
            width: 100%;
            max-width: 580px;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transform: scale(0.95);
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            max-height: calc(100vh - 40px);
            display: flex;
            flex-direction: column;
            margin: auto;
        }

        .modal-overlay.active .modal-card {
            transform: scale(1);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: #0F172A;
            margin: 0;
        }

        .modal-close-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: #64748B;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }

        .modal-close-btn:hover {
            color: #0F172A;
            background-color: #F1F5F9;
        }

        .modal-form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex: 1;
            min-height: 0;
        }

        .modal-footer {
            flex-shrink: 0;
            padding: 16px 24px;
            border-top: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            background-color: #FFFFFF;
            position: sticky;
            bottom: 0;
            z-index: 2;
        }

        .form-group-modal {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .modal-label {
            font-size: 14px;
            font-weight: 600;
            color: #0F172A;
        }

        .modal-input {
            width: 100%;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            color: #0F172A;
            background-color: #FFFFFF;
            transition: all 0.2s;
            outline: none;
            font-family: inherit;
        }

        .modal-input:focus {
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }

        .modal-input.is-invalid {
            border-color: #EF4444;
            background-color: #FEF2F2;
        }

        .modal-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 520px) {
            .modal-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .modal-input[type="time"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }

        .modal-textarea {
            min-height: 85px;
            resize: vertical;
        }

        .btn-modal-cancel {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            color: #0F172A;
            font-weight: 600;
            font-size: 14px;
            padding: 9px 22px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-modal-cancel:hover {
            background-color: #F8FAFC;
            border-color: #CBD5E1;
        }

        .btn-modal-submit {
            background: #0066FF;
            border: none;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 14px;
            padding: 9px 24px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s;
            box-shadow: 0 2px 6px rgba(0, 102, 255, 0.25);
        }

        .btn-modal-submit:hover {
            background: #0052CC;
        }

        .modal-error-msg {
            font-size: 12.5px;
            color: #EF4444;
            font-weight: 500;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <!-- 1. Navigation Header -->
    <header class="navbar">
        <a href="{{ route('home') }}" class="navbar-brand">
            <div class="brand-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
            </div>
            TRAVEL PLANNER
        </a>

        <ul class="navbar-nav">
            <li>
                <a href="{{ route('home') }}" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                    Trang chủ
                </a>
            </li>
            <li>
                <a href="{{ route('home') }}#destinations" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    Địa điểm
                </a>
            </li>
            <li>
                <a href="{{ route('trips.index') }}" class="nav-link active">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    Chuyến đi
                </a>
            </li>
            <li>
                <a href="{{ route('favorites.index') }}" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    Yêu thích
                </a>
            </li>
            <li>
                <a href="#" class="nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" x2="16" y1="2" y2="6"></line><line x1="8" x2="8" y1="2" y2="6"></line><line x1="3" x2="21" y1="10" y2="10"></line></svg>
                    Booking
                </a>
            </li>
        </ul>

        <div class="nav-actions">
            @auth
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="btn-admin">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Admin
                    </a>
                @endif
                <a href="{{ route('profile.show') }}" class="user-dropdown" title="Hồ sơ cá nhân">
                    <img src="{{ Auth::user()->avatar ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100' }}" class="user-avatar-img" alt="Avatar">
                    <span>{{ Auth::user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-admin">Đăng nhập</a>
                <a href="{{ route('register') }}" class="btn-detail-blue">Đăng ký</a>
            @endauth
        </div>
    </header>

    <!-- 2. Page Content -->
    <main class="page-wrapper">
        @if(session('success'))
            <div class="flash-alert flash-success" style="background-color: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 18px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: #065F46; font-size: 18px;">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="flash-alert flash-danger" style="background-color: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: flex-start; justify-content: space-between;">
                <div style="display: flex; gap: 8px;">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 18px; margin-top: 2px;"></i>
                    <div>
                        <strong>Không thể lưu hoạt động:</strong>
                        <ul style="margin: 4px 0 0 16px; padding: 0;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; color: #991B1B; font-size: 18px;">&times;</button>
            </div>
        @endif

        <!-- Breadcrumb -->
        <div class="breadcrumb-box">
            <a href="{{ route('home') }}" class="breadcrumb-link">Trang chủ</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('trips.index') }}" class="breadcrumb-link">Chuyến đi của tôi</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('itinerary.index', $trip->id) }}" class="breadcrumb-link">{{ $trip->name }}</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">Lịch trình chi tiết</span>
        </div>

        <!-- 3. Trip Hero Banner -->
        <div class="trip-hero-card">
            <div class="trip-hero-header-line">
                <div class="hero-badges-line">
                    <span class="badge-pill-status">
                        <i class="bi bi-flag-fill me-1"></i>
                        {{ $trip->status === 'completed' ? 'Đã hoàn thành' : ($trip->status === 'ongoing' ? 'Đang diễn ra' : 'Đã lên kế hoạch') }}
                    </span>
                    <span class="badge-pill-duration">
                        <i class="bi bi-clock-history me-1"></i>{{ $days_count }} ngày {{ $nightsCount }} đêm
                    </span>
                </div>
                <a href="{{ route('trips.index') }}" class="btn-hero-action btn-hero-outline" title="Quay lại danh sách chuyến đi">
                    <i class="bi bi-arrow-left"></i> Danh sách chuyến đi
                </a>
            </div>

            <h1 class="trip-title-main">{{ $trip->name }}</h1>
            <div class="trip-meta-info">
                <span>
                    <i class="bi bi-calendar3 text-warning"></i>
                    {{ $trip->start_date ? $trip->start_date->format('d/m/Y') : '--/--/----' }} — {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : '--/--/----' }}
                </span>
                <span>
                    <i class="bi bi-wallet2 text-success"></i>
                    Ngân sách: <strong>{{ number_format($trip->budget, 0, ',', '.') }}₫</strong>
                </span>
                <span>
                    <i class="bi bi-list-check text-info"></i>
                    Tổng hoạt động: <strong>{{ $total_activities_count }}</strong>
                </span>
            </div>

            <div class="hero-actions-group">
                <a href="javascript:void(0)" onclick="alert('Tính năng Quản lý Ngân sách đang được phát triển!')" class="btn-hero-action btn-hero-warning" title="Quản lý ngân sách chuyến đi">
                    <i class="bi bi-wallet2"></i> Quản lý Ngân sách
                </a>
                <a href="{{ route('trips.edit', $trip->id) }}" class="btn-hero-action btn-hero-edit" title="Chỉnh sửa thông tin chuyến đi">
                    <i class="bi bi-pencil" style="color: #0066FF;"></i> Sửa Trip
                </a>
                <button type="button" onclick="showDeleteTripModal({{ $trip->id }}, '{{ addslashes($trip->name) }}')" class="btn-hero-action btn-hero-delete" title="Xóa chuyến đi này">
                    <i class="bi bi-trash" style="color: #DC2626;"></i> Xóa Trip
                </button>
            </div>
        </div>

        <!-- 4. Days Tabs Navigation (Chức năng 5: Xem lịch trình theo từng ngày) -->
        <div class="days-nav-container">
            <div class="days-pills-list">
                @foreach($days_list as $dayItem)
                    <a href="{{ route('itinerary.index', ['trip' => $trip->id, 'day_number' => $dayItem['day_number']]) }}" 
                       class="day-pill-btn {{ $dayItem['is_active'] ? 'active' : '' }}">
                        <span class="day-pill-title">Ngày {{ $dayItem['day_number'] }}</span>
                        @if($dayItem['date_short'])
                            <span class="day-pill-date">{{ $dayItem['date_short'] }}</span>
                        @endif
                        <span class="day-pill-badge">{{ $dayItem['items_count'] }} mục</span>
                    </a>
                @endforeach
            </div>

            <div>
                <button type="button" class="btn-add-activity-main" onclick="openAddActivityModal({{ $current_day }})">
                    <i class="bi bi-plus-lg"></i> Thêm hoạt động vào Ngày {{ $current_day }}
                </button>
            </div>
        </div>

        <!-- 5. Header Ngày đang chọn -->
        <div class="schedule-header-box">
            <div class="schedule-title">
                <i class="bi bi-calendar2-check text-primary"></i>
                <span>Lịch trình Ngày {{ $current_day }}</span>
                @if($current_date)
                    <span style="font-size: 15px; font-weight: 600; color: var(--text-muted);">
                        — {{ $current_day_of_week }}, ngày {{ $current_date->format('d/m/Y') }}
                    </span>
                @endif
                <span class="badge-count-activities">{{ $items->count() }} hoạt động</span>
            </div>
        </div>

        <!-- Tips Banner -->
        <div class="tips-banner-box">
            <i class="bi bi-lightbulb-fill" style="font-size: 18px;"></i>
            <span><strong>Gợi ý lịch trình:</strong> Các hoạt động trong ngày được sắp xếp tự động theo thứ tự thời gian từ sáng đến tối. Bạn có thể thêm các địa điểm yêu thích hoặc tạo lịch trình mới.</span>
        </div>

        <!-- 6. Timeline Danh Sách Hoạt Động Của Ngày Được Chọn -->
        @if($items->count() > 0)
            <div class="timeline-container">
                @foreach($items as $item)
                    @php
                        $dest = $item->destination;
                        $img = $dest && $dest->primaryImage ? $dest->primaryImage->image_path : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400';
                        $cat = $dest && $dest->category ? $dest->category->name : 'Tham quan';
                        $cityName = $dest && $dest->city ? $dest->city->name : null;
                    @endphp
                    <div class="timeline-node">
                        <div class="timeline-dot"></div>
                        <div class="activity-card">
                            @if($dest)
                                <div class="activity-thumb-box">
                                    <img src="{{ $img }}" alt="{{ $dest->name }}" class="activity-thumb-img" loading="lazy">
                                </div>
                            @endif

                            <div class="activity-info-box">
                                <div class="activity-time-row">
                                    <i class="bi bi-clock"></i>
                                    <span>
                                        {{ $item->start_time ? substr($item->start_time, 0, 5) : '08:00' }}
                                        -
                                        {{ $item->end_time ? substr($item->end_time, 0, 5) : '10:00' }}
                                    </span>
                                    @if($item->start_time && $item->end_time)
                                        @php
                                            $sTime = substr($item->start_time, 0, 5);
                                            $eTime = substr($item->end_time, 0, 5);
                                            $diffM = (int) substr($eTime, 0, 2) * 60 + (int) substr($eTime, 3, 2) - ((int) substr($sTime, 0, 2) * 60 + (int) substr($sTime, 3, 2));
                                            $durH = floor($diffM / 60);
                                            $durMin = $diffM % 60;
                                            $durText = ($durH > 0 ? $durH . 'h' : '') . ($durMin > 0 ? ($durH > 0 ? ' ' : '') . $durMin . 'm' : ($durH == 0 ? '0m' : ''));
                                        @endphp
                                        @if($diffM > 0)
                                            <span style="display: inline-flex; align-items: center; gap: 3px; background: #F1F5F9; color: #475569; font-size: 11.5px; font-weight: 600; padding: 2px 7px; border-radius: 6px; margin-left: 6px;">
                                                <i class="bi bi-hourglass-split" style="font-size: 10px;"></i>{{ $durText }}
                                            </span>
                                        @endif
                                    @endif
                                </div>

                                <h3 class="activity-name-title">
                                    {{ $item->custom_title ?: ($dest ? $dest->name : ($item->note ?: 'Hoạt động trải nghiệm')) }}
                                </h3>

                                <div class="activity-meta-line">
                                    <span class="badge-cat-tag">
                                        <i class="bi bi-tag-fill me-1"></i>{{ $cat }}
                                    </span>
                                    @if($cityName)
                                        <span>
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $cityName }}
                                        </span>
                                    @endif
                                    @if($dest && $dest->rating)
                                        <span>
                                            <i class="bi bi-star-fill text-warning me-1"></i>{{ number_format($dest->rating, 1) }}
                                        </span>
                                    @endif
                                </div>

                                @if($item->note)
                                    <div class="activity-note-box" onclick="openNoteModal({{ $item->id }}, '{{ addslashes($item->custom_title ?: ($dest ? $dest->name : 'Hoạt động')) }}', '{{ addslashes($item->note) }}')" style="cursor: pointer;" title="Nhấn để xem / chỉnh sửa ghi chú">
                                        <i class="bi bi-chat-left-text me-1 text-primary"></i>
                                        <strong>Ghi chú:</strong> {!! nl2br(e($item->note)) !!}
                                    </div>
                                @endif

                                <div class="activity-actions-bar">
                                    <div class="activity-price-tag">
                                        @if($dest && $dest->entrance_fee > 0)
                                            <i class="bi bi-ticket-perforated me-1"></i>{{ number_format($dest->entrance_fee, 0, ',', '.') }}₫ / vé
                                        @else
                                            <i class="bi bi-check-circle me-1"></i>Miễn phí tham quan
                                        @endif
                                    </div>

                                    <div class="activity-btns">
                                        <button type="button" class="btn-act-icon" title="{{ $item->note ? 'Xem & sửa ghi chú' : 'Thêm ghi chú' }}"
                                                onclick="openNoteModal({{ $item->id }}, '{{ addslashes($item->custom_title ?: ($dest ? $dest->name : 'Hoạt động')) }}', '{{ addslashes($item->note ?? '') }}')">
                                            <i class="bi {{ $item->note ? 'bi-journal-check text-primary' : 'bi-journal-plus' }}"></i>
                                        </button>
                                        <button type="button" class="btn-act-icon" title="Chỉnh sửa hoạt động"
                                                onclick="openEditActivityModal({{ $item->id }}, '{{ addslashes($item->custom_title ?: ($dest ? $dest->name : '')) }}', '{{ $item->start_time ? substr($item->start_time, 0, 5) : '08:00' }}', '{{ $item->end_time ? substr($item->end_time, 0, 5) : '10:00' }}', {{ $item->day_number }}, '{{ addslashes($item->note ?? '') }}', {{ $item->destination_id ?: 'null' }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn-act-icon" title="Xóa khỏi lịch trình" style="color: var(--danger);"
                                                onclick="confirmDeleteActivity({{ $item->id }}, '{{ addslashes($item->custom_title ?: ($dest ? $dest->name : 'Hoạt động')) }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- 7. Empty State (Khi ngày được chọn chưa có hoạt động) -->
            <div class="empty-day-box">
                <div class="empty-icon-wrap">
                    <i class="bi bi-calendar-plus"></i>
                </div>
                <h3 class="empty-title">Chưa có hoạt động nào trong Ngày {{ $current_day }}</h3>
                <p class="empty-desc">
                    Lịch trình ngày này đang để trống. Bạn có thể thêm các địa điểm từ bộ sưu tập yêu thích hoặc thêm hoạt động mới để chuyến đi thêm trọn vẹn!
                </p>
                <button type="button" class="btn-add-activity-main" onclick="openAddActivityModal({{ $current_day }})">
                    <i class="bi bi-plus-lg"></i> Thêm hoạt động vào Ngày {{ $current_day }} ngay
                </button>
            </div>
        @endif
    </main>

    <!-- 8. Modal Thêm Hoạt Động Mới (Hình 19) -->
    <div class="modal-overlay" id="activityModalOverlay">
        <div class="modal-card">
            <div class="modal-header">
                <h2 class="modal-title">Thêm Hoạt Động Mới</h2>
                <button type="button" class="modal-close-btn" onclick="closeAddActivityModal()" title="Đóng">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form action="{{ route('itinerary.store', $trip->id) }}" method="POST" id="addActivityForm" class="modal-form">
                @csrf
                <div class="modal-body">
                    <!-- Tên hoạt động * -->
                    <div class="form-group-modal">
                        <label class="modal-label" for="modal_activity_name">Tên hoạt động <span style="color: #EF4444;">*</span></label>
                        <input type="text" 
                               name="activity_name" 
                               id="modal_activity_name" 
                               class="modal-input @error('activity_name') is-invalid @enderror" 
                               placeholder="Ví dụ: Tham quan Bán đảo Sơn Trà & Chùa Linh Ứng"
                               value="{{ old('activity_name') }}"
                               required>
                        <input type="hidden" name="destination_id" id="modal_destination_id" value="{{ old('destination_id') }}">
                        @error('activity_name')
                            <span class="modal-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Ngày thực hiện trong chuyến đi -->
                    <div class="form-group-modal">
                        <label class="modal-label" for="modal_day_number">Ngày thực hiện <span style="color: #EF4444;">*</span></label>
                        <select name="day_number" id="modal_day_number" class="modal-input @error('day_number') is-invalid @enderror">
                            @foreach($days_list as $dayItem)
                                <option value="{{ $dayItem['day_number'] }}" {{ old('day_number', $current_day) == $dayItem['day_number'] ? 'selected' : '' }}>
                                    Ngày {{ $dayItem['day_number'] }}{{ $dayItem['date'] ? ' — ' . $dayItem['day_of_week'] . ' (' . $dayItem['date'] . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('day_number')
                            <span class="modal-error-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Giờ bắt đầu & Giờ kết thúc -->
                    <div class="modal-grid-2">
                        <div class="form-group-modal">
                            <label class="modal-label" for="modal_start_time">Giờ bắt đầu</label>
                            <input type="time" 
                                   name="start_time" 
                                   id="modal_start_time" 
                                   class="modal-input @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time', '14:00') }}">
                            @error('start_time')
                                <span class="modal-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group-modal">
                            <label class="modal-label" for="modal_end_time">Giờ kết thúc</label>
                            <input type="time" 
                                   name="end_time" 
                                   id="modal_end_time" 
                                   class="modal-input @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time', '16:30') }}">
                            @error('end_time')
                                <span class="modal-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div id="add_duration_hint" style="font-size: 12.5px; font-weight: 600; color: #0066FF; margin-top: -6px; display: none;"></div>

                    <!-- Loại hoạt động & Chi phí dự kiến (đ) -->
                    <div class="modal-grid-2">
                        <div class="form-group-modal">
                            <label class="modal-label" for="modal_activity_type">Loại hoạt động</label>
                            <select name="activity_type" id="modal_activity_type" class="modal-input @error('activity_type') is-invalid @enderror">
                                @foreach($activity_types as $type)
                                    <option value="{{ $type }}" {{ old('activity_type', 'Tham quan / Check-in') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('activity_type')
                                <span class="modal-error-msg">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group-modal">
                            <label class="modal-label" for="modal_estimated_cost">Chi phí dự kiến (đ)</label>
                            <input type="number" 
                                   name="estimated_cost" 
                                   id="modal_estimated_cost" 
                                   class="modal-input @error('estimated_cost') is-invalid @enderror" 
                                   placeholder="200000" 
                                   min="0" 
                                   step="1000"
                                   value="{{ old('estimated_cost', '200000') }}">
                            @error('estimated_cost')
                                <span class="modal-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Ghi chú thêm -->
                    <div class="form-group-modal">
                        <label class="modal-label" for="modal_note">Ghi chú thêm</label>
                        <textarea name="note" 
                                  id="modal_note" 
                                  class="modal-input modal-textarea @error('note') is-invalid @enderror" 
                                  placeholder="Ghi chú chi tiết hoặc lưu ý đặc biệt...">{{ old('note') }}</textarea>
                        @error('note')
                            <span class="modal-error-msg">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeAddActivityModal()">Hủy</button>
                    <button type="submit" class="btn-modal-submit">Lưu hoạt động</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 9. Modal Chỉnh Sửa Hoạt Động (Hình 20 Báo cáo Đồ án - Chức năng 7) -->
    <div class="modal-overlay" id="editActivityModalOverlay">
        <div class="modal-card">
            <div class="modal-header">
                <h2 class="modal-title">Chỉnh Sửa Hoạt Động</h2>
                <button type="button" class="modal-close-btn" onclick="closeEditActivityModal()" title="Đóng">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form action="" method="POST" id="editActivityForm" class="modal-form">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <!-- Tên hoạt động * -->
                    <div class="form-group-modal">
                        <label class="modal-label" for="edit_activity_name">Tên hoạt động <span style="color: #EF4444;">*</span></label>
                        <input type="text" 
                               name="activity_name" 
                               id="edit_activity_name" 
                               class="modal-input" 
                               placeholder="Ví dụ: Đáp chuyến bay xuống Sân bay Quốc tế Đà Nẵng"
                               required>
                        <input type="hidden" name="destination_id" id="edit_destination_id" value="">
                    </div>

                    <!-- Ngày thực hiện trong chuyến đi -->
                    <div class="form-group-modal">
                        <label class="modal-label" for="edit_day_number">Ngày thực hiện <span style="color: #EF4444;">*</span></label>
                        <select name="day_number" id="edit_day_number" class="modal-input">
                            @foreach($days_list as $dayItem)
                                <option value="{{ $dayItem['day_number'] }}">
                                    Ngày {{ $dayItem['day_number'] }}{{ $dayItem['date'] ? ' — ' . $dayItem['day_of_week'] . ' (' . $dayItem['date'] . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Giờ bắt đầu & Giờ kết thúc (Hình 20) -->
                    <div class="modal-grid-2">
                        <div class="form-group-modal">
                            <label class="modal-label" for="edit_start_time">Giờ bắt đầu <span style="color: #EF4444;">*</span></label>
                            <input type="time" 
                                   name="start_time" 
                                   id="edit_start_time" 
                                   class="modal-input" 
                                   required>
                        </div>

                        <div class="form-group-modal">
                            <label class="modal-label" for="edit_end_time">Giờ kết thúc <span style="color: #EF4444;">*</span></label>
                            <input type="time" 
                                   name="end_time" 
                                   id="edit_end_time" 
                                   class="modal-input" 
                                   required>
                        </div>
                    </div>
                    <div id="edit_duration_hint" style="font-size: 12.5px; font-weight: 600; color: #0066FF; margin-top: -6px; display: none;"></div>

                    <!-- Ghi chú thêm -->
                    <div class="form-group-modal">
                        <label class="modal-label" for="edit_note">Ghi chú thêm</label>
                        <textarea name="note" 
                                  id="edit_note" 
                                  class="modal-input modal-textarea" 
                                  placeholder="Ghi chú chi tiết hoặc lưu ý đặc biệt..."></textarea>
                    </div>
                </div>

                <!-- Footer (Hình 20: Đóng & Cập nhật) -->
                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeEditActivityModal()">Đóng</button>
                    <button type="submit" class="btn-modal-submit">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 10. Modal Xác Nhận Xóa Hoạt Động (Chức năng 7) -->
    <div class="modal-overlay" id="deleteActivityModalOverlay">
        <div class="modal-card" style="max-width: 440px;">
            <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #FEE2E2; display: flex; align-items: center; justify-content: center; color: #EF4444; font-size: 20px;">
                    <i class="bi bi-trash"></i>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeDeleteActivityModal()" title="Đóng">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form action="" method="POST" id="deleteActivityForm" class="modal-form">
                @csrf
                @method('DELETE')
                <div class="modal-body" style="padding-top: 12px; gap: 8px;">
                    <h3 style="font-size: 18px; font-weight: 700; color: #0F172A; margin: 0;">Xác nhận xóa hoạt động</h3>
                    <p style="font-size: 14px; color: #64748B; line-height: 1.5; margin: 0;">
                        Bạn có chắc chắn muốn xóa hoạt động <strong id="delete_activity_title" style="color: #0F172A;"></strong> khỏi lịch trình không? Thao tác này sẽ không thể hoàn tác.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="closeDeleteActivityModal()">Hủy</button>
                    <button type="submit" class="btn-modal-submit" style="background-color: #EF4444; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);">Xóa hoạt động</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 11. Modal Quản Lý Ghi Chú Hoạt Động (Chức năng 9) -->
    <div class="modal-overlay" id="noteActivityModalOverlay">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <div>
                        <h3 class="modal-title" style="font-size: 17px; margin: 0;">Ghi chú hoạt động</h3>
                        <p id="note_modal_activity_title" style="font-size: 13px; color: #64748B; margin: 0;"></p>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeNoteModal()" title="Đóng">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form action="" method="POST" id="noteActivityForm" class="modal-form">
                @csrf
                @method('PATCH')
                <div class="modal-body" style="padding: 16px 24px;">
                    <div class="form-group-modal">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="modal-label" for="quick_modal_note" style="margin-bottom: 0;">Nội dung ghi chú</label>
                            <span id="noteCharCount" style="font-size: 12px; color: #94A3B8;">0/1000 ký tự</span>
                        </div>
                        <textarea name="note" 
                                  id="quick_modal_note" 
                                  rows="5"
                                  class="modal-input modal-textarea" 
                                  maxlength="1000"
                                  placeholder="Nhập ghi chú chi tiết, lưu ý phương tiện, đồ dùng mang theo, địa chỉ hoặc mẹo trải nghiệm..."></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" class="btn-modal-cancel" style="color: #DC2626; border-color: #FCA5A5;" onclick="clearQuickNote()" id="btnClearNote" title="Xóa nội dung ghi chú">
                        <i class="bi bi-trash me-1"></i> Xóa ghi chú
                    </button>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn-modal-cancel" onclick="closeNoteModal()">Hủy</button>
                        <button type="submit" class="btn-modal-submit" id="btnSaveNote">Lưu ghi chú</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Xác Nhận Xóa Chuyến Đi -->
    <div id="deleteTripModal" class="modal-overlay" onclick="closeDeleteModalOnBackdrop(event)" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="modal-box" style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 480px; padding: 28px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); margin: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px;">
                <h3 style="font-size: 18px; font-weight: 700; color: #DC2626; display: flex; align-items: center; gap: 8px; margin: 0;">
                    <i class="bi bi-exclamation-triangle" style="font-size: 20px;"></i>
                    Xác nhận xóa chuyến đi
                </h3>
                <button type="button" onclick="closeDeleteTripModal()" style="background: none; border: none; font-size: 24px; line-height: 1; color: #64748B; cursor: pointer;">&times;</button>
            </div>
            <p style="font-size: 14.5px; color: #334155; margin-bottom: 10px;">
                Bạn có chắc chắn muốn xóa chuyến đi <strong id="deleteTripTargetName" style="color: #0F172A;"></strong>?
            </p>
            <p style="font-size: 13px; color: #64748B; margin-bottom: 24px; background: #FEF2F2; padding: 10px 14px; border-radius: 8px; border-left: 3px solid #DC2626;">
                Hành động này sẽ xóa vĩnh viễn dữ liệu chuyến đi, toàn bộ hoạt động lịch trình chi tiết và bảng chi tiêu liên quan.
            </p>
            <form id="deleteTripForm" method="POST" style="display: flex; justify-content: flex-end; gap: 10px;">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteTripModal()" style="padding: 9px 20px; border-radius: 9999px; border: 1px solid #CBD5E1; background: #FFFFFF; color: #475569; font-weight: 600; cursor: pointer;">
                    Hủy bỏ
                </button>
                <button type="submit" style="padding: 9px 22px; border-radius: 9999px; border: none; background: #DC2626; color: #FFFFFF; font-weight: 600; cursor: pointer; box-shadow: 0 4px 6px rgba(220, 38, 38, 0.25);">
                    Xác nhận xóa
                </button>
            </form>
        </div>
    </div>

    <script>
        function showDeleteTripModal(tripId, tripName) {
            const modal = document.getElementById('deleteTripModal');
            document.getElementById('deleteTripTargetName').textContent = tripName;
            document.getElementById('deleteTripForm').action = '/trips/' + tripId;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteTripModal() {
            const modal = document.getElementById('deleteTripModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function closeDeleteModalOnBackdrop(e) {
            if (e.target.id === 'deleteTripModal') {
                closeDeleteTripModal();
            }
        }

        function openAddActivityModal(dayNumber) {
            const overlay = document.getElementById('activityModalOverlay');
            if (overlay) {
                if (dayNumber) {
                    const daySelect = document.getElementById('modal_day_number');
                    if (daySelect) daySelect.value = dayNumber;
                }
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                setTimeout(() => {
                    const input = document.getElementById('modal_activity_name');
                    if (input) input.focus();
                }, 100);
            }
        }

        function closeAddActivityModal() {
            const overlay = document.getElementById('activityModalOverlay');
            if (overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        // Tính toán và hiển thị thời lượng hoạt động động (Chức năng 8)
        function updateDurationPreview(startId, endId, hintId) {
            const startElem = document.getElementById(startId);
            const endElem = document.getElementById(endId);
            const hintElem = document.getElementById(hintId);
            if (!startElem || !endElem || !hintElem) return;

            const sVal = startElem.value;
            const eVal = endElem.value;
            if (!sVal || !eVal) {
                hintElem.style.display = 'none';
                return;
            }

            const sM = parseInt(sVal.substring(0, 2), 10) * 60 + parseInt(sVal.substring(3, 5), 10);
            const eM = parseInt(eVal.substring(0, 2), 10) * 60 + parseInt(eVal.substring(3, 5), 10);
            const diff = eM - sM;

            if (diff > 0) {
                const h = Math.floor(diff / 60);
                const m = diff % 60;
                let text = (h > 0 ? h + ' giờ ' : '') + (m > 0 ? m + ' phút' : '');
                hintElem.innerHTML = '<i class="bi bi-clock-history me-1"></i>Thời lượng dự kiến: ' + text;
                hintElem.style.color = '#0066FF';
                hintElem.style.display = 'block';
            } else {
                hintElem.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Giờ kết thúc phải lớn hơn giờ bắt đầu';
                hintElem.style.color = '#EF4444';
                hintElem.style.display = 'block';
            }
        }

        document.getElementById('modal_start_time')?.addEventListener('input', () => updateDurationPreview('modal_start_time', 'modal_end_time', 'add_duration_hint'));
        document.getElementById('modal_end_time')?.addEventListener('input', () => updateDurationPreview('modal_start_time', 'modal_end_time', 'add_duration_hint'));
        document.getElementById('edit_start_time')?.addEventListener('input', () => updateDurationPreview('edit_start_time', 'edit_end_time', 'edit_duration_hint'));
        document.getElementById('edit_end_time')?.addEventListener('input', () => updateDurationPreview('edit_start_time', 'edit_end_time', 'edit_duration_hint'));

        // Mở Modal Chỉnh Sửa Hoạt Động (Hình 20)
        function openEditActivityModal(id, title, startTime, endTime, dayNumber, note, destId) {
            const overlay = document.getElementById('editActivityModalOverlay');
            const form = document.getElementById('editActivityForm');
            if (overlay && form) {
                form.action = '/itinerary/' + id;
                document.getElementById('edit_activity_name').value = title || '';
                document.getElementById('edit_start_time').value = startTime || '';
                document.getElementById('edit_end_time').value = endTime || '';
                document.getElementById('edit_day_number').value = dayNumber || 1;
                document.getElementById('edit_note').value = note || '';
                document.getElementById('edit_destination_id').value = destId || '';

                updateDurationPreview('edit_start_time', 'edit_end_time', 'edit_duration_hint');

                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeEditActivityModal() {
            const overlay = document.getElementById('editActivityModalOverlay');
            if (overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        // Mở Modal Xác Nhận Xóa Hoạt Động
        function confirmDeleteActivity(id, title) {
            const overlay = document.getElementById('deleteActivityModalOverlay');
            const form = document.getElementById('deleteActivityForm');
            const titleElem = document.getElementById('delete_activity_title');
            if (overlay && form) {
                form.action = '/itinerary/' + id;
                if (titleElem) titleElem.textContent = '“' + title + '”';
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeDeleteActivityModal() {
            const overlay = document.getElementById('deleteActivityModalOverlay');
            if (overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        // Đóng khi click ngoài modal card
        document.getElementById('activityModalOverlay')?.addEventListener('click', function(e) {
            if (e.target === this) closeAddActivityModal();
        });
        document.getElementById('editActivityModalOverlay')?.addEventListener('click', function(e) {
            if (e.target === this) closeEditActivityModal();
        });
        document.getElementById('deleteActivityModalOverlay')?.addEventListener('click', function(e) {
            if (e.target === this) closeDeleteActivityModal();
        });
        document.getElementById('noteActivityModalOverlay')?.addEventListener('click', function(e) {
            if (e.target === this) closeNoteModal();
        });

        // Đóng khi nhấn phím Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddActivityModal();
                closeEditActivityModal();
                closeDeleteActivityModal();
                closeNoteModal();
                closeDeleteTripModal();
            }
        });

        // Mở Modal Quản Lý Ghi Chú (Chức năng 9)
        function openNoteModal(id, title, note) {
            const overlay = document.getElementById('noteActivityModalOverlay');
            const form = document.getElementById('noteActivityForm');
            const titleElem = document.getElementById('note_modal_activity_title');
            const noteInput = document.getElementById('quick_modal_note');
            const charCount = document.getElementById('noteCharCount');

            if (overlay && form) {
                form.action = '/itinerary/' + id + '/note';
                if (titleElem) titleElem.textContent = title;
                if (noteInput) {
                    noteInput.value = note || '';
                    if (charCount) charCount.textContent = (note ? note.length : 0) + '/1000 ký tự';
                }
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeNoteModal() {
            const overlay = document.getElementById('noteActivityModalOverlay');
            if (overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function clearQuickNote() {
            const noteInput = document.getElementById('quick_modal_note');
            const charCount = document.getElementById('noteCharCount');
            if (noteInput) {
                noteInput.value = '';
                if (charCount) charCount.textContent = '0/1000 ký tự';
                noteInput.focus();
            }
        }

        document.getElementById('quick_modal_note')?.addEventListener('input', function() {
            const charCount = document.getElementById('noteCharCount');
            if (charCount) {
                charCount.textContent = this.value.length + '/1000 ký tự';
            }
        });

        // Tự động mở modal nếu có lỗi validation từ server
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function() {
                openAddActivityModal({{ old('day_number', $current_day) }});
            });
        @endif

        // Tự động xóa viền đỏ và thông báo lỗi khi người dùng bắt đầu chỉnh sửa lại trường dữ liệu
        document.addEventListener('DOMContentLoaded', function() {
            const modalInputs = document.querySelectorAll('.modal-input');
            modalInputs.forEach(function(input) {
                const clearError = function() {
                    input.classList.remove('is-invalid');
                    const formGroup = input.closest('.form-group-modal');
                    if (formGroup) {
                        const errorMsg = formGroup.querySelector('.modal-error-msg');
                        if (errorMsg) {
                            errorMsg.style.display = 'none';
                        }
                    }
                };
                input.addEventListener('input', clearError);
                input.addEventListener('change', clearError);
            });
        });
    </script>
</body>
</html>
