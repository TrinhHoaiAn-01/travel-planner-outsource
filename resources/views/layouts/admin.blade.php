<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Hệ Thống Quản Trị Travel Planner</title>

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #0F172A;
            --sidebar-hover: #1E293B;
            --sidebar-text: #94A3B8;
            --sidebar-text-active: #FFFFFF;
            --primary: #2563EB;
            --primary-light: #EFF6FF;
            --primary-hover: #1D4ED8;
            --success: #16A34A;
            --success-light: #DCFCE7;
            --warning: #D97706;
            --warning-light: #FEF3C7;
            --info: #0284C7;
            --info-light: #E0F2FE;
            --surface: #FFFFFF;
            --background: #F8FAFC;
            --border: #E2E8F0;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--background);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .admin-sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #FFFFFF;
            text-decoration: none;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-icon-shield {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        .sidebar-nav {
            flex: 1;
            padding: 8px 14px;
            overflow-y: auto;
        }

        .nav-group-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748B;
            margin: 20px 8px 10px 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }

        .nav-item-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-item:hover {
            color: #FFFFFF;
            background-color: var(--sidebar-hover);
        }

        .nav-item.active {
            background-color: var(--primary);
            color: var(--sidebar-text-active);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .nav-badge-pill {
            background-color: #F59E0B;
            color: #0F172A;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 9999px;
            line-height: 1.2;
        }

        /* Sidebar User Profile */
        .sidebar-user {
            padding: 16px 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            background-color: rgba(15, 23, 42, 0.85);
        }

        .user-info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .user-avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #3B82F6;
            background: linear-gradient(135deg, #1E293B, #334155);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 14px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .user-avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-details {
            overflow: hidden;
        }

        .user-name {
            font-size: 13.5px;
            font-weight: 600;
            color: #FFFFFF;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            font-size: 11.5px;
            color: #64748B;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-actions-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-action-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--sidebar-text);
            background-color: #1E293B;
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .user-action-btn:hover {
            color: #FFFFFF;
            background-color: #334155;
        }

        .btn-logout-icon {
            flex: 0 0 34px;
            padding: 6px 0;
            color: #EF4444;
        }

        .btn-logout-icon:hover {
            background-color: rgba(239, 68, 68, 0.15);
            color: #F87171;
        }

        /* Main Content Layout */
        .admin-main-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* Header Bar */
        .admin-header-bar {
            background-color: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 16px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 30;
            gap: 16px;
        }

        .header-left-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-toggle-btn {
            display: none;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px;
            color: var(--text-dark);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .sidebar-toggle-btn:hover {
            background-color: #F1F5F9;
            border-color: #CBD5E1;
        }

        .header-title-text {
            font-size: 19px;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.01em;
            white-space: nowrap;
        }

        .header-right-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .system-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--success-light);
            color: var(--success);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .status-dot-pulse {
            width: 8px;
            height: 8px;
            background-color: var(--success);
            border-radius: 50%;
            display: inline-block;
            position: relative;
        }

        .status-dot-pulse::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            border: 2px solid var(--success);
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.9); opacity: 0.9; }
            50% { transform: scale(1.6); opacity: 0; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        .btn-view-public {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background-color: #FFFFFF;
            color: var(--text-dark);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-view-public:hover {
            background-color: #F1F5F9;
            border-color: #CBD5E1;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* Breadcrumbs */
        .admin-breadcrumbs {
            padding: 20px 28px 4px 28px;
            font-size: 13.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-breadcrumbs a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .admin-breadcrumbs a:hover {
            text-decoration: underline;
        }

        /* Main Content Container */
        .admin-content-body {
            padding: 16px 28px 36px 28px;
        }

        /* Mobile Sidebar Backdrop */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(2px);
            z-index: 45;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .sidebar-backdrop.active {
            display: block;
            opacity: 1;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .sidebar-toggle-btn {
                display: inline-flex;
            }

            .admin-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 50;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-header-bar {
                padding: 12px 18px;
            }

            .header-title-text {
                font-size: 16px;
            }

            .admin-breadcrumbs {
                padding: 16px 18px 4px 18px;
            }

            .admin-content-body {
                padding: 16px 18px 28px 18px;
            }
        }

        @media (max-width: 640px) {
            .header-right-actions .btn-view-public span {
                display: none;
            }

            .btn-view-public {
                padding: 7px 10px;
            }

            .system-status-pill span:last-child {
                display: none;
            }

            .system-status-pill {
                padding: 6px 8px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <div class="brand-icon-shield">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
            <span>Admin Panel</span>
        </a>

        <nav class="sidebar-nav">
            <!-- TỔNG QUAN -->
            <div class="nav-group-title">TỔNG QUAN</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <div class="nav-item-content">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" rx="1"/>
                        <rect x="14" y="3" width="7" height="5" rx="1"/>
                        <rect x="14" y="12" width="7" height="9" rx="1"/>
                        <rect x="3" y="16" width="7" height="5" rx="1"/>
                    </svg>
                    <span>Dashboard</span>
                </div>
            </a>

            <!-- QUẢN LÝ HỆ THỐNG -->
            <div class="nav-group-title">QUẢN LÝ HỆ THỐNG</div>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <div class="nav-item-content">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Người dùng (Users)</span>
                </div>
            </a>

            <a href="{{ route('admin.cities.index') }}" class="nav-item {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">
                <div class="nav-item-content">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span>Thành phố (Cities)</span>
                </div>
            </a>

            <a href="{{ route('admin.categories.index') }}" class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <div class="nav-item-content">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="7" x="3" y="3" rx="1"/>
                        <rect width="7" height="7" x="14" y="3" rx="1"/>
                        <rect width="7" height="7" x="14" y="14" rx="1"/>
                        <rect width="7" height="7" x="3" y="14" rx="1"/>
                    </svg>
                    <span>Danh mục (Categories)</span>
                </div>
            </a>

            <a href="{{ route('admin.destinations.index') }}" class="nav-item {{ request()->routeIs('admin.destinations.*') ? 'active' : '' }}">
                <div class="nav-item-content">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span>Địa điểm (Destinations)</span>
                </div>
            </a>

            <a href="{{ route('admin.destination-images.index') }}" class="nav-item {{ request()->routeIs('admin.destination-images.*') ? 'active' : '' }}">
                <div class="nav-item-content">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                        <circle cx="9" cy="9" r="2"/>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                    <span>Thư viện ảnh (Images)</span>
                </div>
            </a>

            <!-- KIỂM DUYỆT -->
            <div class="nav-group-title">KIỂM DUYỆT</div>
            <a href="{{ route('admin.reviews.index') }}" class="nav-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <div class="nav-item-content">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Đánh giá (Reviews)</span>
                </div>
                <span class="nav-badge-pill">3</span>
            </a>
        </nav>

        <!-- User Profile Widget -->
        <div class="sidebar-user">
            <div class="user-info-row">
                <div class="user-avatar-circle">
                    @if(auth()->check() && auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                    @else
                        <span>AD</span>
                    @endif
                </div>
                <div class="user-details">
                    <div class="user-name">{{ auth()->check() ? auth()->user()->name : 'Administrator' }}</div>
                    <div class="user-email">{{ auth()->check() ? auth()->user()->email : 'admin@travelplanner.com' }}</div>
                </div>
            </div>
            <div class="user-actions-row">
                <a href="{{ route('home') }}" class="user-action-btn" title="Xem giao diện Public">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" x2="22" y1="12" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    <span>Web</span>
                </a>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="user-action-btn btn-logout-icon" title="Đăng xuất">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" x2="9" y1="12" y2="12"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main-wrap">
        <!-- Header Bar -->
        <header class="admin-header-bar">
            <div class="header-left-group">
                <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Mở menu điều hướng">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <h1 class="header-title-text">Hệ Thống Quản Trị Travel Planner</h1>
            </div>
            <div class="header-right-actions">
                <div class="system-status-pill">
                    <span class="status-dot-pulse"></span>
                    <span>Hệ thống hoạt động bình thường</span>
                </div>
                <a href="{{ route('home') }}" class="btn-view-public">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>Xem Giao diện Public</span>
                </a>
            </div>
        </header>

        <!-- Breadcrumbs -->
        <nav class="admin-breadcrumbs" aria-label="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Admin</a>
            <span>/</span>
            <span>@yield('breadcrumb', 'Bảng điều khiển (Dashboard)')</span>
        </nav>

        <!-- Page Body -->
        <main class="admin-content-body">
            @yield('content')
        </main>
    </div>

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.admin-sidebar');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const backdrop = document.getElementById('sidebarBackdrop');

            function toggleSidebar() {
                if (sidebar) {
                    sidebar.classList.toggle('open');
                }
                if (backdrop) {
                    backdrop.classList.toggle('active');
                }
            }

            function closeSidebar() {
                if (sidebar) {
                    sidebar.classList.remove('open');
                }
                if (backdrop) {
                    backdrop.classList.remove('active');
                }
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSidebar);
            }
            if (backdrop) {
                backdrop.addEventListener('click', closeSidebar);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
