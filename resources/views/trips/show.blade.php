<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $trip->name }} - Chi tiết chuyến đi - Travel Planner</title>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #0066FF;
            --primary-dark: #0052CC;
            --primary-light: #EBF5FF;
            --secondary: #64748B;
            --success: #059669;
            --warning: #D97706;
            --danger: #DC2626;
            --surface: #FFFFFF;
            --bg-main: #F8FAFC;
            --border-color: #E2E8F0;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -2px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Top Navigation Bar */
        .navbar-travelplanner {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 19px;
            letter-spacing: -0.5px;
            color: var(--text-main);
            text-decoration: none;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #0066FF 0%, #0052CC 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(0, 102, 255, 0.25);
        }

        .nav-link-travelplanner {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px !important;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted) !important;
            transition: var(--transition);
        }

        .nav-link-travelplanner:hover {
            color: var(--primary) !important;
            background-color: #F1F5F9;
        }

        .nav-link-travelplanner.active {
            background-color: var(--primary-light);
            color: var(--primary) !important;
            font-weight: 600;
        }

        /* Card custom */
        .card-custom {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .card-custom:hover {
            box-shadow: var(--shadow-md);
        }

        /* Trip Hero Banner */
        .trip-hero-banner {
            border-radius: var(--radius-lg);
            position: relative;
            overflow: hidden;
            color: #FFFFFF;
            box-shadow: var(--shadow-lg);
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.82) 0%, rgba(30, 41, 59, 0.88) 100%);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .badge-travelplanner {
            font-weight: 600;
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 9999px;
            letter-spacing: 0.2px;
        }

        .badge-soft-primary { background-color: rgba(0, 102, 255, 0.15); color: #0066FF; }
        .badge-soft-warning { background-color: rgba(217, 119, 6, 0.15); color: #D97706; }
        .badge-soft-success { background-color: rgba(5, 150, 105, 0.15); color: #059669; }
        .badge-soft-secondary { background-color: #E2E8F0; color: #475569; }

        /* KPI Metric Cards */
        .metric-card {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: var(--transition);
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: #CBD5E1;
        }

        .metric-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        /* Timeline Styles */
        .itinerary-timeline {
            position: relative;
            padding-left: 2.2rem;
            margin-top: 1.5rem;
        }

        .itinerary-timeline::before {
            content: '';
            position: absolute;
            top: 10px;
            bottom: 10px;
            left: 10px;
            width: 2px;
            background: #CBD5E1;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
            background: #FFFFFF;
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .timeline-item:hover {
            border-color: #93C5FD;
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .timeline-dot {
            position: absolute;
            left: -2.2rem;
            top: 1.35rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #FFFFFF;
            border: 3.5px solid var(--primary);
            z-index: 2;
            box-shadow: 0 0 0 4px rgba(0, 102, 255, 0.15);
        }

        .timeline-time {
            font-weight: 700;
            color: var(--primary);
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }

        /* User Profile Pill */
        .user-avatar-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 12px;
            border-radius: 9999px;
            background: #F1F5F9;
            border: 1px solid var(--border-color);
            text-decoration: none;
            color: var(--text-main);
            transition: var(--transition);
        }

        .user-avatar-btn:hover {
            background: #E2E8F0;
        }

        .user-avatar-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Description Block */
        .description-card {
            background: #FFFFFF;
            border-left: 4px solid var(--primary);
            border-radius: var(--radius-md);
            padding: 20px 24px;
            box-shadow: var(--shadow-sm);
        }
    </style>
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-travelplanner">
        <div class="container">
            <a class="navbar-brand navbar-brand-logo" href="{{ route('home') }}">
                <div class="logo-icon"><i class="bi bi-compass"></i></div>
                <span>TRAVEL PLANNER</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <li class="nav-item">
                        <a class="nav-link nav-link-travelplanner" href="{{ route('home') }}"><i class="bi bi-house-door"></i> Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-travelplanner active" href="{{ route('trips.index') }}"><i class="bi bi-map"></i> Chuyến đi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-travelplanner" href="{{ route('favorites.index') }}"><i class="bi bi-heart"></i> Yêu thích</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    @if(Auth::check() && Auth::user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                            <i class="bi bi-shield-lock me-1"></i> Admin Panel
                        </a>
                    @endif

                    @auth
                        <div class="dropdown">
                            <button class="btn user-avatar-btn dropdown-toggle border-0" type="button" data-bs-toggle="dropdown">
                                <img src="{{ Auth::user()->avatar ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80' }}" alt="Avatar" class="user-avatar-img">
                                <span class="fw-semibold small">{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="bi bi-person me-2"></i>Hồ sơ cá nhân</a></li>
                                <li><a class="dropdown-item active" href="{{ route('trips.index') }}"><i class="bi bi-map me-2"></i>Chuyến đi của tôi</a></li>
                                <li><a class="dropdown-item" href="{{ route('favorites.index') }}"><i class="bi bi-heart me-2"></i>Địa điểm yêu thích</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        <div class="container">
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('trips.index') }}" class="text-decoration-none text-muted">Chuyến đi</a></li>
                    <li class="breadcrumb-item active fw-medium text-dark" aria-current="page">Chi tiết lịch trình</li>
                </ol>
            </nav>

            <!-- Success Message Notification -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @php
                // Xác định ảnh bìa theo vùng miền hoặc ảnh mặc định
                $bannerImage = 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=1400&q=80';
                $lowerName = mb_strtolower($trip->name);
                if (str_contains($lowerName, 'hạ long') || str_contains($lowerName, 'quảng ninh')) {
                    $bannerImage = 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1400&q=80';
                } elseif (str_contains($lowerName, 'sa pa') || str_contains($lowerName, 'fansipan')) {
                    $bannerImage = 'https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&w=1400&q=80';
                } elseif (str_contains($lowerName, 'phú quốc')) {
                    $bannerImage = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80';
                } elseif (str_contains($lowerName, 'đà lạt')) {
                    $bannerImage = 'https://images.unsplash.com/photo-1519451241324-20b4ea2c4220?auto=format&fit=crop&w=1400&q=80';
                }

                // Trạng thái badge
                $statusText = 'Sắp tới';
                $statusClass = 'bg-primary text-white';
                if ($trip->status === 'completed') {
                    $statusText = 'Đã hoàn thành';
                    $statusClass = 'bg-secondary text-white';
                } elseif ($trip->status === 'ongoing' || ($trip->start_date && $trip->end_date && $trip->start_date <= now() && $trip->end_date >= now())) {
                    $statusText = 'Đang diễn ra';
                    $statusClass = 'bg-warning text-dark';
                } elseif ($trip->status === 'draft') {
                    $statusText = 'Bản nháp';
                    $statusClass = 'bg-light text-dark';
                }

                $daysCount = $summary['days_count'] ?? 1;
                $nightsCount = max(0, $daysCount - 1);
            @endphp

            <!-- Trip Hero Banner (Tái hiện 100% detail.html) -->
            <div class="trip-hero-banner p-4 p-md-5 mb-4 position-relative overflow-hidden" style="background: url('{{ $bannerImage }}') center/cover no-repeat;">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge badge-travelplanner {{ $statusClass }}">{{ $statusText }}</span>
                                <span class="badge badge-travelplanner bg-light text-dark shadow-sm">
                                    <i class="bi bi-clock me-1 text-primary"></i>{{ $daysCount }} ngày {{ $nightsCount }} đêm
                                </span>
                            </div>
                            <h1 class="display-6 fw-bold text-white mb-2">{{ $trip->name }}</h1>
                            <p class="text-light opacity-90 mb-3 fs-6">
                                <i class="bi bi-calendar3 me-2 text-warning"></i>
                                {{ $trip->start_date ? $trip->start_date->format('d/m/Y') : '--/--/----' }} — {{ $trip->end_date ? $trip->end_date->format('d/m/Y') : '--/--/----' }}
                                &nbsp;|&nbsp;
                                <i class="bi bi-wallet2 me-1 text-success"></i>
                                Ngân sách: <strong class="text-white">{{ number_format($trip->budget, 0, ',', '.') }}₫</strong>
                                @if($trip->status === 'completed')
                                    &nbsp;|&nbsp;
                                    <i class="bi bi-cash-coin me-1 text-info"></i>
                                    Tổng chi: <strong class="text-white">{{ number_format($summary['total_expenses'], 0, ',', '.') }}₫</strong>
                                @endif
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                <a href="{{ route('trips.edit', $trip->id) }}" class="btn btn-light rounded-pill px-4 fw-semibold shadow-sm">
                                    <i class="bi bi-pencil me-1 text-primary"></i> Sửa Trip
                                </a>
                                <button type="button" class="btn btn-outline-danger bg-white text-danger rounded-pill px-3 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#deleteTripModal">
                                    <i class="bi bi-trash me-1"></i> Xóa Trip
                                </button>
                                <a href="{{ route('trips.index') }}" class="btn btn-outline-light rounded-pill px-3">
                                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPI Metric Cards Summary -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <div class="metric-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-calendar-range"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Thời lượng</span>
                            <span class="fw-bold fs-5">{{ $daysCount }} ngày</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <div class="metric-icon-box bg-success-subtle text-success">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Điểm đến</span>
                            <span class="fw-bold fs-5">{{ $summary['destinations_count'] }} địa điểm</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <div class="metric-icon-box bg-warning-subtle text-warning">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Ngân sách dự kiến</span>
                            <span class="fw-bold fs-5 text-success">{{ number_format($summary['budget'], 0, ',', '.') }}₫</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <div class="metric-icon-box bg-info-subtle text-info">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Đã chi thực tế</span>
                            <span class="fw-bold fs-5 text-primary">{{ number_format($summary['total_expenses'], 0, ',', '.') }}₫</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Khối Quản lý Mô tả và Ghi chú tổng quát của chuyến đi (Nghiệp vụ chức năng 4) -->
            <div class="card-custom p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-card-text text-primary fs-4"></i>
                        <h5 class="fw-bold mb-0 text-dark">Mô tả và Ghi chú mục tiêu chuyến đi</h5>
                        @if($trip->description)
                            <span class="badge bg-light text-muted border small">{{ mb_strlen($trip->description) }} ký tự</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#editTripNotesModal">
                            <i class="bi bi-pencil-square me-1"></i> Sửa nhanh ghi chú
                        </button>
                        <a href="{{ route('trips.edit', $trip->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Chỉnh sửa toàn bộ thông tin">
                            <i class="bi bi-sliders me-1"></i> Chi tiết khác
                        </a>
                    </div>
                </div>
                <div class="description-card">
                    @if($trip->description)
                        <div class="text-dark p-3 rounded-3 bg-light-subtle border border-light-subtle" style="white-space: pre-line; font-size: 15px; line-height: 1.7;">{{ $trip->description }}</div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-journal-text fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                            <p class="mb-2">Chưa có mô tả hay ghi chú nào cho chuyến đi này.</p>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#editTripNotesModal">
                                <i class="bi bi-plus-lg me-1"></i> Thêm mô tả & ghi chú ngay
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Days Tabs & Action Bar (Tái hiện detail.html) -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <ul class="nav nav-pills gap-2" id="daysTab" role="tablist">
                    @for($i = 1; $i <= min(7, max(1, $daysCount)); $i++)
                        @php
                            $dayDate = $trip->start_date ? (clone $trip->start_date)->addDays($i - 1)->format('d/m') : '';
                        @endphp
                        <li class="nav-item">
                            <button class="nav-link {{ $i === 1 ? 'active' : '' }} rounded-pill px-3 fw-bold" data-bs-toggle="pill" data-bs-target="#day{{ $i }}">
                                Ngày {{ $i }} {{ $dayDate ? '(' . $dayDate . ')' : '' }}
                            </button>
                        </li>
                    @endfor
                </ul>

                <div>
                    <a href="{{ route('trips.edit', $trip->id) }}" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Cập nhật chuyến đi
                    </a>
                </div>
            </div>

            <!-- Tab Content (Itinerary Timeline) -->
            <div class="tab-content" id="daysTabContent">
                @for($i = 1; $i <= min(7, max(1, $daysCount)); $i++)
                    @php
                        $dayItems = $trip->itineraryItems->where('day_number', $i);
                    @endphp
                    <div class="tab-pane fade {{ $i === 1 ? 'show active' : '' }}" id="day{{ $i }}">
                        <div class="card-custom p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-primary">
                                    <i class="bi bi-calendar-check me-2"></i>Lịch trình Ngày {{ $i }}
                                </h5>
                                <span class="badge badge-travelplanner badge-soft-primary">{{ $dayItems->count() }} Hoạt động</span>
                            </div>

                            @if($dayItems->count() > 0)
                                <div class="itinerary-timeline">
                                    @foreach($dayItems as $item)
                                        <div class="timeline-item">
                                            <div class="timeline-dot"></div>
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="timeline-time">
                                                        <i class="bi bi-clock me-1"></i>
                                                        {{ $item->start_time ? substr($item->start_time, 0, 5) : '09:00' }} - {{ $item->end_time ? substr($item->end_time, 0, 5) : '11:00' }}
                                                    </div>
                                                    <h6 class="fw-bold mb-1 text-dark">
                                                        {{ $item->destination ? $item->destination->name : ($item->notes ?: 'Hoạt động trải nghiệm') }}
                                                    </h6>
                                                    @if($item->notes)
                                                        <p class="text-muted small mb-2">{{ $item->notes }}</p>
                                                    @endif
                                                    <span class="badge badge-soft-secondary small">
                                                        <i class="bi bi-tag me-1"></i>{{ $item->activity_type ?? 'Tham quan' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bi bi-calendar-plus text-primary fs-1 mb-2 d-block"></i>
                                    <h6 class="fw-bold">Chưa có hoạt động nào trong Ngày {{ $i }}</h6>
                                    <p class="text-muted small mb-3">Lịch trình chi tiết theo từng giờ của ngày này hiện đang được sắp xếp.</p>
                                    <a href="{{ route('trips.edit', $trip->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-pencil me-1"></i> Chỉnh sửa thông tin Trip
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </main>

    <!-- Modal Quản Lý & Chỉnh Sửa Mô Tả / Ghi Chú Tổng Quát Chuyến Đi (Nguyễn Trần Thành) -->
    <div class="modal fade" id="editTripNotesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-primary-subtle text-primary border-0 pb-2">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-journal-check fs-4"></i>
                        <span>Quản lý Mô tả & Ghi chú tổng quát</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('trips.update-notes', $trip->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">
                            Ghi chú mục tiêu hành trình, những điều cần lưu ý về hành lý, trang phục, các điểm hẹn đặc biệt hoặc thông tin liên hệ khẩn cấp.
                        </p>

                        <!-- Gợi ý mẫu nhanh -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted mb-1">Mẫu gợi ý nhanh:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-light border rounded-pill" onclick="appendNoteTemplate('🎯 Mục tiêu: Trải nghiệm ẩm thực địa phương, ngắm hoàng hôn và chụp ảnh kỷ niệm.\n')">
                                    🎯 Mục tiêu
                                </button>
                                <button type="button" class="btn btn-sm btn-light border rounded-pill" onclick="appendNoteTemplate('🎒 Chuẩn bị: Kem chống nắng, kính râm, sạc dự phòng, CCCD và vé máy bay.\n')">
                                    🎒 Hành lý
                                </button>
                                <button type="button" class="btn btn-sm btn-light border rounded-pill" onclick="appendNoteTemplate('⚠️ Lưu ý: Theo dõi dự báo thời tiết, lưu số điện thoại khách sạn và HDV.\n')">
                                    ⚠️ Lưu ý
                                </button>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="tripNotesTextarea" class="form-label small fw-bold text-dark">Nội dung ghi chú & mô tả</label>
                            <textarea
                                class="form-control"
                                id="tripNotesTextarea"
                                name="description"
                                rows="6"
                                maxlength="3000"
                                placeholder="Nhập mục tiêu chuyến đi, lưu ý về hành lý, thời tiết hoặc kế hoạch dự phòng..."
                                oninput="updateNotesCharCount()"
                                style="font-size: 14.5px; line-height: 1.6;"
                            >{{ old('description', $trip->description) }}</textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small text-muted">
                            <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0" onclick="clearTripNotes()">
                                <i class="bi bi-trash3 me-1"></i> Xóa nội dung
                            </button>
                            <span id="notesCharCounter">0 / 3000 ký tự</span>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Lưu ghi chú
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Chỉnh Sửa Nhanh Mô Tả & Ghi Chú Tổng Quát Của Chuyến Đi -->
    <div class="modal fade" id="editTripNotesModal" tabindex="-1" aria-labelledby="editTripNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form action="{{ route('trips.update-notes', $trip->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="editTripNotesModalLabel">
                            <i class="bi bi-pencil-square fs-5"></i>
                            Quản lý mô tả & Ghi chú mục tiêu chuyến đi
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Mẹo gợi ý & Gắn mẫu nhanh -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small mb-2 d-flex justify-content-between align-items-center">
                                <span>Gợi ý chèn mẫu nhanh vào ghi chú:</span>
                                <span class="text-muted small" id="notesCharCounter">{{ mb_strlen($trip->description ?? '') }} / 3000 ký tự</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="appendNoteTemplate('🎯 Mục tiêu: ')">
                                    <i class="bi bi-bullseye me-1"></i> Mục tiêu
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="appendNoteTemplate('🎒 Hành lý: ')">
                                    <i class="bi bi-backpack me-1"></i> Hành lý
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="appendNoteTemplate('⚠️ Lưu ý: ')">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Lưu ý
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="appendNoteTemplate('📞 Số khẩn cấp: ')">
                                    <i class="bi bi-telephone me-1"></i> Số khẩn cấp
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="appendNoteTemplate('☀️ Thời tiết: ')">
                                    <i class="bi bi-cloud-sun me-1"></i> Thời tiết
                                </button>
                            </div>
                        </div>

                        <!-- Ô nhập Textarea -->
                        <div class="mb-3">
                            <label for="tripNotesTextarea" class="form-label fw-semibold text-dark small">Nội dung mô tả & ghi chú chi tiết</label>
                            <textarea name="description"
                                      id="tripNotesTextarea"
                                      class="form-control rounded-3 p-3 @error('description') is-invalid @enderror"
                                      rows="8"
                                      maxlength="3000"
                                      placeholder="Nhập mục tiêu chuyến đi, những điều cần ghi nhớ, danh sách đồ dùng mang theo, phân công thành viên hoặc lưu ý trang phục..."
                                      oninput="updateNotesCharCount()">{{ old('description', $trip->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Ghi chú này sẽ được hiển thị nổi bật tại trang chi tiết để các thành viên dễ dàng theo dõi. Tối đa 3000 ký tự.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-link text-danger text-decoration-none px-0 small" onclick="clearTripNotes()">
                            <i class="bi bi-eraser me-1"></i> Xóa sạch nội dung
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Lưu ghi chú
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Xác Nhận Xóa Chuyến Đi (Chuẩn theo detail.html & index.html) -->
    <div class="modal fade" id="deleteTripModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-danger-subtle text-danger border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Xác nhận xóa chuyến đi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="fs-6 mb-2">Bạn có chắc chắn muốn xóa chuyến đi <strong class="text-dark">{{ $trip->name }}</strong>?</p>
                    <p class="text-muted small mb-0">Hành động này sẽ xóa vĩnh viễn toàn bộ dữ liệu lịch trình chi tiết và thông tin chi tiêu đi kèm của chuyến đi này khỏi hệ thống.</p>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <form action="{{ route('trips.destroy', $trip->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-trash me-1"></i> Xóa vĩnh viễn
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 mt-5 bg-white border-top text-center text-muted small">
        <div class="container">
            <p class="mb-0">&copy; 2026 Travel Planner Platform. Bản quyền thuộc về Nhóm B - Khoa CNTT TDC.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateNotesCharCount() {
            const textarea = document.getElementById('tripNotesTextarea');
            const counter = document.getElementById('notesCharCounter');
            if (textarea && counter) {
                counter.textContent = `${textarea.value.length} / 3000 ký tự`;
            }
        }

        function appendNoteTemplate(templateText) {
            const textarea = document.getElementById('tripNotesTextarea');
            if (textarea) {
                textarea.value = (textarea.value ? textarea.value.trim() + '\n' : '') + templateText;
                updateNotesCharCount();
                textarea.focus();
            }
        }

        function clearTripNotes() {
            const textarea = document.getElementById('tripNotesTextarea');
            if (textarea && confirm('Bạn có chắc chắn muốn xóa sạch nội dung ghi chú này?')) {
                textarea.value = '';
                updateNotesCharCount();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateNotesCharCount();
        });
    </script>
</body>
</html>
