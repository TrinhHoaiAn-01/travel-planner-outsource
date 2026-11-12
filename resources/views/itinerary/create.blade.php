<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Hoạt Động Mới - {{ $trip->name }} - Travel Planner</title>
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
            --danger: #EF4444;
            --radius-card: 16px;
            --radius-pill: 9999px;
            --shadow-md: 0 4px 12px rgba(0,0,0,0.06);
            --shadow-lg: 0 20px 40px rgba(0,0,0,0.1);
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

        /* 2. Main Wrapper */
        .page-wrapper {
            max-width: 760px;
            margin: 0 auto;
            padding: 24px 20px 60px;
            width: 100%;
            flex: 1;
        }

        /* Breadcrumb */
        .breadcrumb-box {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 13.5px;
            flex-wrap: wrap;
        }

        .breadcrumb-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
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

        /* Form Card matching Hình 19 */
        .activity-form-card {
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .form-card-header {
            padding: 20px 28px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .btn-close-header {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 20px;
            padding: 4px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }

        .btn-close-header:hover {
            color: var(--text-dark);
            background-color: #F1F5F9;
        }

        .form-card-body {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group-custom {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control-custom {
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            color: var(--text-dark);
            background-color: #FFFFFF;
            transition: all 0.2s;
            outline: none;
            font-family: inherit;
        }

        .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }

        .form-control-custom.is-invalid {
            border-color: var(--danger);
            background-color: #FEF2F2;
        }

        .form-control-custom[type="time"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 640px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .textarea-custom {
            min-height: 90px;
            resize: vertical;
        }

        .form-card-footer {
            padding: 18px 28px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            background-color: #FFFFFF;
        }

        .btn-cancel {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            font-weight: 600;
            font-size: 14px;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-cancel:hover {
            background-color: #F8FAFC;
            border-color: #CBD5E1;
        }

        .btn-submit {
            background: var(--primary);
            border: none;
            color: #FFFFFF;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s;
            box-shadow: 0 2px 6px rgba(0, 102, 255, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

        .error-feedback {
            font-size: 12.5px;
            color: var(--danger);
            font-weight: 500;
            margin-top: 2px;
        }

        .alert-error-box {
            background-color: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13.5px;
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

    <!-- 2. Main Page Content -->
    <main class="page-wrapper">
        <!-- Breadcrumb -->
        <div class="breadcrumb-box">
            <a href="{{ route('home') }}" class="breadcrumb-link">Trang chủ</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('trips.index') }}" class="breadcrumb-link">Chuyến đi của tôi</a>
            <span class="breadcrumb-sep">/</span>
            <a href="{{ route('itinerary.index', ['trip' => $trip->id, 'day_number' => $current_day]) }}" class="breadcrumb-link">{{ $trip->name }}</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">Thêm hoạt động mới</span>
        </div>

        @if($errors->any())
            <div class="alert-error-box">
                <div style="font-weight: 700; margin-bottom: 4px;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Không thể lưu hoạt động:</div>
                <ul style="padding-left: 20px; margin: 0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Form Màn hình Thêm Hoạt Động Mới (Hình 19) -->
        <div class="activity-form-card">
            <div class="form-card-header">
                <h1 class="form-card-title">Thêm Hoạt Động Mới</h1>
                <a href="{{ route('itinerary.index', ['trip' => $trip->id, 'day_number' => $current_day]) }}" class="btn-close-header" title="Đóng">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

            <form action="{{ route('itinerary.store', $trip->id) }}" method="POST">
                @csrf
                <div class="form-card-body">
                    <!-- Tên hoạt động -->
                    <div class="form-group-custom">
                        <label class="form-label" for="activity_name">Tên hoạt động <span style="color: var(--danger);">*</span></label>
                        <input type="text" 
                               name="activity_name" 
                               id="activity_name" 
                               class="form-control-custom @error('activity_name') is-invalid @enderror" 
                               placeholder="Ví dụ: Tham quan Bán đảo Sơn Trà & Chùa Linh Ứng"
                               value="{{ old('activity_name', $selected_destination ? $selected_destination->name : '') }}"
                               list="destinations-list"
                               required
                               autofocus>
                        <datalist id="destinations-list">
                            @foreach($destinations as $dest)
                                <option value="{{ $dest->name }}" data-id="{{ $dest->id }}">{{ $dest->city ? $dest->city->name : '' }}</option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="destination_id" id="destination_id" value="{{ old('destination_id', $selected_destination ? $selected_destination->id : '') }}">
                        @error('activity_name')
                            <span class="error-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Ngày thực hiện trong chuyến đi -->
                    <div class="form-group-custom">
                        <label class="form-label" for="day_number">Ngày thực hiện <span style="color: var(--danger);">*</span></label>
                        <select name="day_number" id="day_number" class="form-control-custom @error('day_number') is-invalid @enderror">
                            @foreach($days_list as $dayItem)
                                <option value="{{ $dayItem['day_number'] }}" {{ old('day_number', $current_day) == $dayItem['day_number'] ? 'selected' : '' }}>
                                    Ngày {{ $dayItem['day_number'] }}{{ $dayItem['date'] ? ' — ' . $dayItem['day_of_week'] . ' (' . $dayItem['date'] . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('day_number')
                            <span class="error-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Giờ bắt đầu & Giờ kết thúc -->
                    <div class="form-grid-2">
                        <div class="form-group-custom">
                            <label class="form-label" for="start_time">Giờ bắt đầu</label>
                            <input type="time" 
                                   name="start_time" 
                                   id="start_time" 
                                   class="form-control-custom @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time', '14:00') }}">
                            @error('start_time')
                                <span class="error-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label" for="end_time">Giờ kết thúc</label>
                            <input type="time" 
                                   name="end_time" 
                                   id="end_time" 
                                   class="form-control-custom @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time', '16:30') }}">
                            @error('end_time')
                                <span class="error-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Loại hoạt động & Chi phí dự kiến -->
                    <div class="form-grid-2">
                        <div class="form-group-custom">
                            <label class="form-label" for="activity_type">Loại hoạt động</label>
                            <select name="activity_type" id="activity_type" class="form-control-custom @error('activity_type') is-invalid @enderror">
                                @foreach($activity_types as $type)
                                    <option value="{{ $type }}" {{ old('activity_type', 'Tham quan / Check-in') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                            @error('activity_type')
                                <span class="error-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label" for="estimated_cost">Chi phí dự kiến (đ)</label>
                            <input type="number" 
                                   name="estimated_cost" 
                                   id="estimated_cost" 
                                   class="form-control-custom @error('estimated_cost') is-invalid @enderror" 
                                   placeholder="200000" 
                                   min="0" 
                                   step="1000"
                                   value="{{ old('estimated_cost', $selected_destination && $selected_destination->entrance_fee ? (int)$selected_destination->entrance_fee : '200000') }}">
                            @error('estimated_cost')
                                <span class="error-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Ghi chú thêm -->
                    <div class="form-group-custom">
                        <label class="form-label" for="note">Ghi chú thêm</label>
                        <textarea name="note" 
                                  id="note" 
                                  class="form-control-custom textarea-custom @error('note') is-invalid @enderror" 
                                  placeholder="Ghi chú chi tiết hoặc lưu ý đặc biệt...">{{ old('note') }}</textarea>
                        @error('note')
                            <span class="error-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-card-footer">
                    <a href="{{ route('itinerary.index', ['trip' => $trip->id, 'day_number' => $current_day]) }}" class="btn-cancel">Hủy</a>
                    <button type="submit" class="btn-submit">Lưu hoạt động</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Tự động liên kết destination_id khi người dùng gõ tên địa điểm trùng khớp
        const activityNameInput = document.getElementById('activity_name');
        const destinationIdInput = document.getElementById('destination_id');
        const destinationsData = @json($destinations->map(fn($d) => ['id' => $d->id, 'name' => $d->name]));

        activityNameInput.addEventListener('input', function() {
            const val = this.value.trim().toLowerCase();
            const found = destinationsData.find(d => d.name.toLowerCase() === val);
            if (found) {
                destinationIdInput.value = found.id;
            }
        });
    </script>
</body>
</html>
