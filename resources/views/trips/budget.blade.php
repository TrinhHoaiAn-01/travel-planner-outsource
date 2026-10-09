<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theo Dõi Ngân Sách & Chi Tiêu - {{ $trip->name }} - Travel Planner</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation Header */
        .header-navbar {
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 16px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .brand-logo {
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
            width: 32px;
            height: 32px;
            background: #0066FF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background-color: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 9999px;
            color: #475569;
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background-color: #F1F5F9;
            border-color: #94A3B8;
            color: #0F172A;
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 28px 24px 60px 24px;
            flex: 1;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 16px;
        }

        .breadcrumb a {
            color: #0066FF;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb-sep {
            color: #94A3B8;
        }

        /* Trip Badge & Page Title */
        .trip-badge {
            display: inline-block;
            background-color: #EBF5FF;
            color: #0066FF;
            font-size: 12.5px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 9999px;
            margin-bottom: 8px;
        }

        .title-actions-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.5px;
        }

        .top-action-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-demo-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            background-color: #FFFFFF;
            border: 1.5px solid #F87171;
            border-radius: 9999px;
            color: #EF4444;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-demo-toggle:hover {
            background-color: #FEF2F2;
            border-color: #DC2626;
        }

        .btn-add-expense {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 22px;
            background-color: #0066FF;
            border: none;
            border-radius: 9999px;
            color: #FFFFFF;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 102, 255, 0.25);
            transition: all 0.2s;
        }

        .btn-add-expense:hover {
            background-color: #0052CC;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.35);
        }

        /* Over Budget Warning Banner (Hình 22) */
        .over-budget-banner {
            background-color: #FEE2E2;
            border: 1px solid #FCA5A5;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 24px;
        }

        .warning-icon-box {
            color: #DC2626;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .warning-text-title {
            font-size: 15px;
            font-weight: 700;
            color: #991B1B;
            margin-bottom: 4px;
        }

        .warning-text-desc {
            font-size: 13.5px;
            color: #B91C1C;
            line-height: 1.45;
        }

        /* Toast / Flash Notifications */
        .toast-notification {
            background-color: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* 3 KPI Metric Cards */
        .metric-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .metric-card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .metric-content {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .metric-label {
            font-size: 11.5px;
            font-weight: 700;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-value {
            font-size: 27px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.5px;
        }

        .metric-value-spent {
            color: #0066FF;
        }

        .metric-value-safe {
            color: #16A34A;
        }

        .metric-value-danger {
            color: #DC2626;
        }

        .metric-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            color: #0066FF;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: none;
            padding: 0;
        }

        .metric-link:hover {
            text-decoration: underline;
        }

        .metric-subtext {
            font-size: 13px;
            color: #64748B;
        }

        .metric-badge-safe {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: #16A34A;
            font-weight: 600;
        }

        .metric-badge-danger {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: #DC2626;
            font-weight: 600;
        }

        .metric-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .metric-icon-blue {
            background-color: #EBF5FF;
            color: #0066FF;
        }

        .metric-icon-yellow {
            background-color: #FEF3C7;
            color: #D97706;
        }

        .metric-icon-green {
            background-color: #DCFCE7;
            color: #16A34A;
        }

        /* Progress Card */
        .progress-card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 28px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .progress-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #0F172A;
        }

        .progress-percentage {
            font-size: 14.5px;
            font-weight: 700;
            color: #0066FF;
        }

        .progress-track {
            width: 100%;
            height: 12px;
            background-color: #E2E8F0;
            border-radius: 9999px;
            overflow: hidden;
        }

        .progress-fill-blue {
            height: 100%;
            background-color: #0066FF;
            border-radius: 9999px;
            transition: width 0.4s ease;
        }

        .progress-fill-danger {
            height: 100%;
            background: repeating-linear-gradient(
                -45deg,
                #EF4444,
                #EF4444 8px,
                #DC2626 8px,
                #DC2626 16px
            );
            border-radius: 9999px;
            width: 100%;
        }

        /* Detailed Expense Table Card */
        .table-card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        }

        .table-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .table-title-group h3 {
            font-size: 18px;
            font-weight: 800;
            color: #0F172A;
            margin-bottom: 3px;
        }

        .table-title-group p {
            font-size: 13px;
            color: #64748B;
        }

        .category-filter-select {
            padding: 8px 36px 8px 14px;
            font-size: 13.5px;
            color: #0F172A;
            background-color: #FFFFFF;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }

        .category-filter-select:focus {
            border-color: #0066FF;
        }

        /* Expenses Table */
        .expenses-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .expenses-table th {
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 700;
            color: #0F172A;
            border-bottom: 1px solid #E2E8F0;
            background-color: #FAFAFA;
        }

        .expenses-table td {
            padding: 16px;
            font-size: 13.5px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }

        .expenses-table tr:last-child td {
            border-bottom: none;
        }

        .expense-title-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .expense-main-title {
            font-weight: 700;
            color: #0F172A;
            font-size: 14px;
        }

        .expense-sub-desc {
            font-size: 12px;
            color: #64748B;
        }

        /* Category Badges */
        .badge-category {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-cat-flight, .badge-cat-transport {
            background-color: #EBF5FF;
            color: #0066FF;
        }

        .badge-cat-lodging, .badge-cat-accommodation {
            background-color: #FEF3C7;
            color: #D97706;
        }

        .badge-cat-ticket {
            background-color: #DCFCE7;
            color: #16A34A;
        }

        .badge-cat-food {
            background-color: #FFE4E6;
            color: #E11D48;
        }

        .badge-cat-other {
            background-color: #F1F5F9;
            color: #475569;
        }

        .expense-amount-cell {
            font-weight: 700;
            color: #0F172A;
            font-size: 14px;
        }

        .actions-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid #E2E8F0;
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748B;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-action-icon:hover {
            background-color: #F8FAFC;
            border-color: #CBD5E1;
            color: #0F172A;
        }

        .btn-action-delete {
            border-color: #FECACA;
            color: #EF4444;
            background-color: #FFFFFF;
        }

        .btn-action-delete:hover {
            background-color: #FEF2F2;
            border-color: #F87171;
            color: #DC2626;
        }

        /* Empty State */
        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: #64748B;
        }

        .empty-state p {
            font-size: 14px;
            margin-bottom: 12px;
        }

        /* Footer */
        .footer {
            background-color: #0F172A;
            color: #94A3B8;
            text-align: center;
            padding: 24px;
            font-size: 13px;
            margin-top: auto;
        }

        /* Modal Styles (Hình 23) */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            backdrop-filter: blur(2px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-card {
            background-color: #FFFFFF;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            animation: modalFadeIn 0.2s ease-out;
            overflow: hidden;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(-8px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .modal-header {
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #F1F5F9;
        }

        .modal-title-group {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 17px;
            font-weight: 700;
            color: #0F172A;
        }

        .btn-modal-close {
            background: none;
            border: none;
            color: #64748B;
            font-size: 20px;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .btn-modal-close:hover {
            color: #0F172A;
            background-color: #F1F5F9;
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #0F172A;
            margin-bottom: 6px;
        }

        .form-label span.req {
            color: #EF4444;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            font-size: 14px;
            color: #0F172A;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-input:focus, .form-select:focus {
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .modal-divider {
            height: 1px;
            background-color: #F1F5F9;
            margin: 20px -24px 18px -24px;
        }

        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-modal-cancel {
            padding: 9px 20px;
            background-color: #F1F5F9;
            border: 1px solid #E2E8F0;
            border-radius: 8px;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-modal-cancel:hover {
            background-color: #E2E8F0;
            color: #0F172A;
        }

        .btn-modal-submit {
            padding: 9px 24px;
            background-color: #0066FF;
            border: none;
            border-radius: 8px;
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0, 102, 255, 0.25);
            transition: all 0.2s;
        }

        .btn-modal-submit:hover {
            background-color: #0052CC;
        }

        /* Preset budget buttons */
        .quick-amounts {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .btn-quick-amount {
            background: #F1F5F9;
            border: 1px solid #CBD5E1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            color: #475569;
            cursor: pointer;
        }

        .btn-quick-amount:hover {
            background: #E2E8F0;
            color: #0F172A;
        }

        /* Responsive Breakpoints */
        @media (max-width: 900px) {
            .metric-cards-grid {
                grid-template-columns: 1fr;
            }
            .header-navbar {
                padding: 14px 20px;
            }
            .main-container {
                padding: 20px 16px;
            }
            .title-actions-row {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="header-navbar">
        <a href="{{ route('home') }}" class="brand-logo">
            <div class="brand-icon">
                <!-- Compass icon -->
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                </svg>
            </div>
            <span>TRAVEL PLANNER</span>
        </a>

        <a href="{{ route('trips.index') }}" class="btn-back">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Quay lại Lịch trình Trip</span>
        </a>
    </header>

    <!-- Main Container -->
    <main class="main-container">

        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('trips.index') }}">Chuyến đi</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('trips.index') }}">Chi tiết chuyến đi</a>
            <span class="breadcrumb-sep">/</span>
            <span>Quản lý Ngân sách</span>
        </nav>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="toast-notification">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="over-budget-banner" style="background-color: #FEF2F2; border-color: #F87171; margin-bottom: 20px;">
                <div class="warning-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div>
                    <div class="warning-text-title" style="color: #991B1B;">Đã xảy ra lỗi dữ liệu:</div>
                    <ul style="margin-left: 20px; font-size: 13.5px; color: #B91C1C;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Trip Name Badge & Main Title Row -->
        <span class="trip-badge">{{ $trip->name }}</span>
        <div class="title-actions-row">
            <h1 class="page-title">Theo Dõi Ngân Sách & Chi Tiêu</h1>

            <div class="top-action-buttons">
                <!-- Demo Toggle Button -->
                <button type="button" class="btn-demo-toggle" id="btnToggleDemoOverBudget" onclick="toggleDemoOverBudget()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span id="demoButtonText">{{ $summary['is_over_budget'] ? 'Tắt Giả lập Demo' : 'Giả lập Vượt ngân sách (Demo)' }}</span>
                </button>

                <!-- Add Expense Button -->
                <button type="button" class="btn-add-expense" onclick="openAddExpenseModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Thêm khoản chi mới</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="toast-notification">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div style="background-color: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 14px;">
                <div style="font-weight: 700; margin-bottom: 6px;">Đã xảy ra lỗi, vui lòng kiểm tra lại:</div>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Over Budget Alert Banner (Hình 22) -->
        <div id="overBudgetBanner" class="over-budget-banner" style="display: {{ $summary['is_over_budget'] ? 'flex' : 'none' }};">
            <div class="warning-icon-box">
                <!-- Triangle alert icon -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div>
                <h4 class="warning-text-title">Cảnh báo: Chi tiêu đã vượt quá hạn mức ngân sách!</h4>
                <p class="warning-text-desc">
                    Tổng chi phí hiện tại đã vượt quá tổng ngân sách dự kiến ban đầu. Vui lòng cân đối lại các khoản chi sắp tới hoặc nâng hạn mức ngân sách chuyến đi.
                </p>
            </div>
        </div>

        <!-- Metric KPI Cards (Hình 21 & Hình 22) -->
        <div class="metric-cards-grid">
            <!-- Card 1: Tổng ngân sách dự kiến -->
            <div class="metric-card">
                <div class="metric-content">
                    <span class="metric-label">Tổng ngân sách dự kiến</span>
                    <span class="metric-value" id="displayBudgetValue">{{ number_format($summary['budget'], 0, ',', '.') }}đ</span>
                    <button type="button" class="metric-link" onclick="openEditBudgetModal()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span>Chỉnh sửa hạn mức</span>
                    </button>
                </div>
                <div class="metric-icon-box metric-icon-blue">
                    <!-- Wallet Icon -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <path d="M7 15h0M2 9.5h20"></path>
                    </svg>
                </div>
            </div>

            <!-- Card 2: Tổng đã chi tiêu -->
            <div class="metric-card">
                <div class="metric-content">
                    <span class="metric-label">Tổng đã chi tiêu</span>
                    <span class="metric-value metric-value-spent" id="displaySpentValue">{{ number_format($summary['total_expenses'], 0, ',', '.') }}đ</span>
                    <span class="metric-subtext" id="displaySpentSubtext">
                        {{ $summary['is_over_budget'] ? '100% ngân sách đã dùng' : $summary['percentage_used'] . '% ngân sách đã dùng' }}
                    </span>
                </div>
                <div class="metric-icon-box metric-icon-yellow">
                    <!-- Shopping Cart Icon -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
            </div>

            <!-- Card 3: Số dư khả dụng -->
            <div class="metric-card">
                <div class="metric-content">
                    <span class="metric-label">Số dư khả dụng</span>
                    <span class="metric-value {{ $summary['is_over_budget'] ? 'metric-value-danger' : 'metric-value-safe' }}" id="displayRemainingValue">
                        @if($summary['is_over_budget'])
                            -{{ number_format($summary['over_budget_amount'], 0, ',', '.') }}đ
                        @else
                            {{ number_format($summary['remaining_budget'], 0, ',', '.') }}đ
                        @endif
                    </span>
                    <div id="displayRemainingBadge">
                        @if($summary['is_over_budget'])
                            <span class="metric-badge-danger">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <span>Vượt hạn mức chi tiêu</span>
                            </span>
                        @else
                            <span class="metric-badge-safe">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    <polyline points="9 12 11 14 15 10"></polyline>
                                </svg>
                                <span>Trong vùng an toàn</span>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="metric-icon-box metric-icon-green">
                    <!-- Piggy Bank / Savings Icon -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.5-1 2-2.5 1-2.5.5-4.5-1-6.5.5-.5 0-2 0-2z"></path>
                        <circle cx="9" cy="10" r="1"></circle>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Progress Card -->
        <div class="progress-card">
            <div class="progress-header">
                <span class="progress-title">Tiến độ sử dụng ngân sách</span>
                <span class="progress-percentage" id="displayProgressText">
                    {{ $summary['is_over_budget'] ? '100%' : $summary['percentage_used'] . '%' }}
                </span>
            </div>
            <div class="progress-track">
                <div id="progressTrackBar" class="{{ $summary['is_over_budget'] ? 'progress-fill-danger' : 'progress-fill-blue' }}"
                     style="width: {{ $summary['is_over_budget'] ? '100%' : min(100, $summary['percentage_used']) }}%;">
                </div>
            </div>
        </div>

        <!-- Detailed Expense Table Card -->
        <div class="table-card">
            <div class="table-card-header">
                <div class="table-title-group">
                    <h3>Bảng Kê Chi Phí Chi Tiết</h3>
                    <p>Quản lý và cập nhật các hóa đơn, chi phí phát sinh thực tế</p>
                </div>

                <!-- Category Filter -->
                <form method="GET" action="{{ route('trips.budget.show', $trip) }}" id="categoryFilterForm">
                    <select name="category" class="category-filter-select" onchange="document.getElementById('categoryFilterForm').submit()">
                        <option value="all" {{ $selectedCategory === 'all' ? 'selected' : '' }}>Tất cả danh mục</option>
                        <option value="flight" {{ $selectedCategory === 'flight' ? 'selected' : '' }}>Vé máy bay</option>
                        <option value="lodging" {{ $selectedCategory === 'lodging' ? 'selected' : '' }}>Lưu trú</option>
                        <option value="ticket" {{ $selectedCategory === 'ticket' ? 'selected' : '' }}>Vé tham quan</option>
                        <option value="food" {{ $selectedCategory === 'food' ? 'selected' : '' }}>Ăn uống</option>
                        <option value="transport" {{ $selectedCategory === 'transport' ? 'selected' : '' }}>Đi lại / Thuê xe</option>
                        <option value="other" {{ $selectedCategory === 'other' ? 'selected' : '' }}>Khác</option>
                    </select>
                </form>
            </div>

            <!-- Table Content -->
            @if($expenses->isEmpty())
                <div class="empty-state">
                    <p>Chưa có khoản chi nào được ghi nhận cho chuyến đi này.</p>
                    <button type="button" class="btn-add-expense" onclick="openAddExpenseModal()">
                        + Thêm khoản chi đầu tiên
                    </button>
                </div>
            @else
                <table class="expenses-table">
                    <thead>
                        <tr>
                            <th style="width: 32%;">Tên khoản chi</th>
                            <th style="width: 15%;">Danh mục</th>
                            <th style="width: 14%;">Ngày chi</th>
                            <th style="width: 15%;">Số tiền (VNĐ)</th>
                            <th style="width: 14%;">Ghi chú</th>
                            <th style="width: 10%; text-align: center;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $item)
                            @php
                                $badgeClass = match($item->category) {
                                    'flight' => 'badge-cat-flight',
                                    'transport' => 'badge-cat-transport',
                                    'lodging', 'accommodation' => 'badge-cat-lodging',
                                    'ticket' => 'badge-cat-ticket',
                                    'food' => 'badge-cat-food',
                                    default => 'badge-cat-other',
                                };

                                $categoryName = match($item->category) {
                                    'flight' => 'Vé máy bay',
                                    'transport' => 'Đi lại / Thuê xe',
                                    'lodging', 'accommodation' => 'Lưu trú',
                                    'ticket' => 'Vé tham quan',
                                    'food' => 'Ăn uống',
                                    default => 'Khác',
                                };

                                // Tách tên chính và mô tả phụ nếu có dấu gạch ngang
                                $parts = explode(' - ', $item->description ?? '', 2);
                                $mainTitle = $parts[0] ?: 'Khoản chi';
                                $subDesc = count($parts) > 1 ? $parts[1] : '';
                            @endphp
                            <tr>
                                <td>
                                    <div class="expense-title-cell">
                                        <span class="expense-main-title">{{ $mainTitle }}</span>
                                        @if($subDesc)
                                            <span class="expense-sub-desc">{{ $subDesc }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-category {{ $badgeClass }}">{{ $categoryName }}</span>
                                </td>
                                <td>
                                    {{ $item->expense_date ? $item->expense_date->format('d/m/Y') : 'Chưa đặt' }}
                                </td>
                                <td>
                                    <span class="expense-amount-cell">{{ number_format($item->amount, 0, ',', '.') }}đ</span>
                                </td>
                                <td>
                                    <span style="color: #64748B;">{{ $subDesc ?: 'Không có' }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="actions-cell" style="justify-content: center;">
                                        <!-- Edit button -->
                                        <button type="button" class="btn-action-icon"
                                                onclick="openEditExpenseModal({{ $item->id }}, '{{ addslashes($mainTitle) }}', '{{ $item->category }}', {{ $item->amount }}, '{{ $item->expense_date ? $item->expense_date->format('Y-m-d') : '' }}', '{{ addslashes($subDesc) }}')"
                                                title="Chỉnh sửa khoản chi">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>

                                        <!-- Delete button -->
                                        <form method="POST" action="{{ route('expenses.destroy', $item) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khoản chi này?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-icon btn-action-delete" title="Xóa khoản chi">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2026 Travel Planner Travel Platform. All rights reserved.</p>
    </footer>

    <!-- MODAL 1: THÊM KHOẢN CHI MỚI (Hình 23) -->
    <div id="addExpenseModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title-group">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Thêm Khoản Chi Mới</span>
                </div>
                <button type="button" class="btn-modal-close" onclick="closeAddExpenseModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('trips.expenses.store', $trip) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Tên khoản chi <span class="req">*</span></label>
                        <input type="text" name="title" class="form-input" placeholder="Ví dụ: Thuê xe máy 3 ngày" required>
                    </div>

                    <div class="form-row form-group">
                        <div>
                            <label class="form-label">Danh mục <span class="req">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="transport">Đi lại / Thuê xe</option>
                                <option value="flight">Vé máy bay</option>
                                <option value="lodging">Lưu trú</option>
                                <option value="ticket">Vé tham quan</option>
                                <option value="food">Ăn uống</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Số tiền (VNĐ) <span class="req">*</span></label>
                            <input type="number" name="amount" class="form-input" placeholder="450000" min="1000" max="9999999999" step="1000" required>
                        </div>
                    </div>

                    <div class="form-row form-group">
                        <div>
                            <label class="form-label">Ngày chi</label>
                            <input type="date" name="expense_date" class="form-input" value="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="form-label">Ghi chú</label>
                            <input type="text" name="note" class="form-input" placeholder="Ghi chú chi tiết...">
                        </div>
                    </div>

                    <div class="modal-divider"></div>

                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" onclick="closeAddExpenseModal()">Hủy</button>
                        <button type="submit" class="btn-modal-submit">Lưu khoản chi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: CHỈNH SỬA KHOẢN CHI -->
    <div id="editExpenseModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title-group">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Chỉnh Sửa Khoản Chi</span>
                </div>
                <button type="button" class="btn-modal-close" onclick="closeEditExpenseModal()">✕</button>
            </div>
            <form id="editExpenseForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Tên khoản chi <span class="req">*</span></label>
                        <input type="text" id="edit_expense_title" name="title" class="form-input" required>
                    </div>

                    <div class="form-row form-group">
                        <div>
                            <label class="form-label">Danh mục <span class="req">*</span></label>
                            <select id="edit_expense_category" name="category" class="form-select" required>
                                <option value="transport">Đi lại / Thuê xe</option>
                                <option value="flight">Vé máy bay</option>
                                <option value="lodging">Lưu trú</option>
                                <option value="ticket">Vé tham quan</option>
                                <option value="food">Ăn uống</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Số tiền (VNĐ) <span class="req">*</span></label>
                            <input type="number" id="edit_expense_amount" name="amount" class="form-input" min="1000" max="9999999999" step="1000" required>
                        </div>
                    </div>

                    <div class="form-row form-group">
                        <div>
                            <label class="form-label">Ngày chi</label>
                            <input type="date" id="edit_expense_date" name="expense_date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Ghi chú</label>
                            <input type="text" id="edit_expense_note" name="note" class="form-input">
                        </div>
                    </div>

                    <div class="modal-divider"></div>

                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" onclick="closeEditExpenseModal()">Hủy</button>
                        <button type="submit" class="btn-modal-submit">Cập nhật khoản chi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: CHỈNH SỬA HẠN MỨC NGÂN SÁCH (Hình 21) -->
    <div id="editBudgetModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title-group">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <path d="M7 15h0M2 9.5h20"></path>
                    </svg>
                    <span>Chỉnh Sửa Hạn Mức Ngân Sách</span>
                </div>
                <button type="button" class="btn-modal-close" onclick="closeEditBudgetModal()">✕</button>
            </div>
            <form method="POST" action="{{ route('trips.budget.update', $trip) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Tổng ngân sách dự kiến (VNĐ) <span class="req">*</span></label>
                        <input type="number" id="budgetInput" name="budget" class="form-input"
                               value="{{ (int) $summary['budget'] }}" min="0" max="9999999999" step="100000" required>
                        <div class="quick-amounts">
                            <button type="button" class="btn-quick-amount" onclick="setBudgetAmount(5000000)">5.000.000đ</button>
                            <button type="button" class="btn-quick-amount" onclick="setBudgetAmount(10000000)">10.000.000đ</button>
                            <button type="button" class="btn-quick-amount" onclick="setBudgetAmount(15000000)">15.000.000đ</button>
                            <button type="button" class="btn-quick-amount" onclick="setBudgetAmount(20000000)">20.000.000đ</button>
                        </div>
                    </div>

                    <div class="modal-divider"></div>

                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" onclick="closeEditBudgetModal()">Hủy</button>
                        <button type="submit" class="btn-modal-submit">Lưu hạn mức</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Interactive Logic -->
    <script>
        // Modal Controls
        function openAddExpenseModal() {
            document.getElementById('addExpenseModal').classList.add('active');
        }

        function closeAddExpenseModal() {
            document.getElementById('addExpenseModal').classList.remove('active');
        }

        function openEditExpenseModal(id, title, category, amount, date, note) {
            const form = document.getElementById('editExpenseForm');
            form.action = `/expenses/${id}`;
            document.getElementById('edit_expense_title').value = title;
            document.getElementById('edit_expense_category').value = category;
            document.getElementById('edit_expense_amount').value = amount;
            document.getElementById('edit_expense_date').value = date;
            document.getElementById('edit_expense_note').value = note;
            document.getElementById('editExpenseModal').classList.add('active');
        }

        function closeEditExpenseModal() {
            document.getElementById('editExpenseModal').classList.remove('active');
        }

        function openEditBudgetModal() {
            document.getElementById('editBudgetModal').classList.add('active');
        }

        function closeEditBudgetModal() {
            document.getElementById('editBudgetModal').classList.remove('active');
        }

        function setBudgetAmount(amount) {
            document.getElementById('budgetInput').value = amount;
        }

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddExpenseModal();
                closeEditExpenseModal();
                closeEditBudgetModal();
            }
        });

        // Close on outside click
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        // Demo Over-Budget Toggle (Phục vụ trình chiếu demo theo Hình 22)
        let isDemoOverBudgetActive = false;
        const initialBudget = {{ (float) $summary['budget'] }};
        const initialSpent = {{ (float) $summary['total_expenses'] }};
        const initialRemaining = {{ (float) $summary['remaining_budget'] }};
        const initialPercentage = {{ (float) $summary['percentage_used'] }};
        const isActuallyOverBudget = {{ $summary['is_over_budget'] ? 'true' : 'false' }};

        function formatVND(number) {
            return new Intl.NumberFormat('vi-VN').format(number) + 'đ';
        }

        function toggleDemoOverBudget() {
            if (isActuallyOverBudget) {
                alert('Chuyến đi này hiện đã vượt ngân sách thực tế!');
                return;
            }

            isDemoOverBudgetActive = !isDemoOverBudgetActive;

            const banner = document.getElementById('overBudgetBanner');
            const spentEl = document.getElementById('displaySpentValue');
            const spentSubtext = document.getElementById('displaySpentSubtext');
            const remainingEl = document.getElementById('displayRemainingValue');
            const remainingBadge = document.getElementById('displayRemainingBadge');
            const progressText = document.getElementById('displayProgressText');
            const progressBar = document.getElementById('progressTrackBar');
            const btnText = document.getElementById('demoButtonText');

            if (isDemoOverBudgetActive) {
                // Chuyển sang giao diện demo Vượt ngân sách (Hình 22: Chi 17.500.000đ, Ngân sách 15.000.000đ, Âm -2.500.000đ)
                banner.style.display = 'flex';
                spentEl.textContent = formatVND(17500000);
                spentSubtext.textContent = '100% ngân sách đã dùng';

                remainingEl.textContent = '-2.500.000đ';
                remainingEl.className = 'metric-value metric-value-danger';
                remainingBadge.innerHTML = `
                    <span class="metric-badge-danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>Vượt hạn mức chi tiêu</span>
                    </span>
                `;

                progressText.textContent = '100%';
                progressBar.className = 'progress-fill-danger';
                progressBar.style.width = '100%';

                btnText.textContent = 'Tắt Giả lập Demo';
            } else {
                // Quay lại giao diện trong vùng an toàn (Hình 21)
                banner.style.display = 'none';
                spentEl.textContent = formatVND(initialSpent);
                spentSubtext.textContent = initialPercentage + '% ngân sách đã dùng';

                remainingEl.textContent = formatVND(initialRemaining);
                remainingEl.className = 'metric-value metric-value-safe';
                remainingBadge.innerHTML = `
                    <span class="metric-badge-safe">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <polyline points="9 12 11 14 15 10"></polyline>
                        </svg>
                        <span>Trong vùng an toàn</span>
                    </span>
                `;

                progressText.textContent = initialPercentage + '%';
                progressBar.className = 'progress-fill-blue';
                progressBar.style.width = Math.min(100, initialPercentage) + '%';

                btnText.textContent = 'Giả lập Vượt ngân sách (Demo)';
            }
        }
    </script>
</body>
</html>
