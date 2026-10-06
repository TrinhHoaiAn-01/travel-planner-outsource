<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lên Kế Hoạch Chuyến Đi Mới - Travel Planner</title>
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
            background-color: #F8FAFC;
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
            max-width: 820px;
            margin: 36px auto;
            padding: 0 20px 48px;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #0066FF;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Main Form Card */
        .form-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 20px;
            padding: 36px 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            margin-bottom: 28px;
        }

        .card-title {
            font-size: 26px;
            font-weight: 700;
            color: #0F172A;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .card-subtitle {
            font-size: 14px;
            color: #64748B;
            line-height: 1.5;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 22px;
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
            .form-card {
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

        .input-wrapper.has-error {
            border-color: #EF4444;
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

        .form-textarea.has-error {
            border-color: #EF4444;
        }

        .error-message {
            color: #EF4444;
            font-size: 12.5px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Action Buttons */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #F1F5F9;
        }

        .btn-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 24px;
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
            padding: 11px 26px;
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
            transform: translateY(-1px);
        }

        /* Alert notifications */
        .alert-error {
            background-color: #FEF2F2;
            border: 1px solid #FCA5A5;
            color: #991B1B;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
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
            <div class="user-dropdown">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <span class="user-name">{{ Auth::user()->name ?? 'Người dùng' }}</span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('trips.index') }}">Chuyến đi của tôi</a>
            <span>/</span>
            <span>Tạo chuyến đi mới</span>
        </nav>

        <!-- Form Card Matching Mockup Exact Layout -->
        <div class="form-card">
            <div class="card-header">
                <h1 class="card-title">Lên Kế Hoạch Chuyến Đi Mới</h1>
                <p class="card-subtitle">Điền thông tin cơ bản để bắt đầu sắp xếp lịch trình chi tiết và quản lý ngân sách</p>
            </div>

            @if ($errors->any())
                <div class="alert-error">
                    <strong>Đã có lỗi xảy ra!</strong> Vui lòng kiểm tra lại thông tin bên dưới.
                </div>
            @endif

            <form action="{{ route('trips.store') }}" method="POST">
                @csrf

                <!-- Tên chuyến đi -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        Tên chuyến đi <span class="required">*</span>
                    </label>
                    <div class="input-wrapper {{ $errors->has('name') ? 'has-error' : '' }}">
                        <div class="input-icon-box">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-input"
                               placeholder="Hành trình Khám phá Đà Nẵng - Hội An"
                               value="{{ old('name') }}"
                               required>
                    </div>
                    @error('name')
                        <div class="error-message">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Ngày bắt đầu & Ngày kết thúc -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="start_date" class="form-label">
                            Ngày bắt đầu <span class="required">*</span>
                        </label>
                        <div class="input-wrapper {{ $errors->has('start_date') ? 'has-error' : '' }}">
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
                                   id="start_date"
                                   class="form-input"
                                   value="{{ old('start_date', '2026-10-10') }}"
                                   required>
                        </div>
                        @error('start_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="end_date" class="form-label">
                            Ngày kết thúc <span class="required">*</span>
                        </label>
                        <div class="input-wrapper {{ $errors->has('end_date') ? 'has-error' : '' }}">
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
                                   id="end_date"
                                   class="form-input"
                                   value="{{ old('end_date', '2026-10-15') }}"
                                   required>
                        </div>
                        @error('end_date')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Tổng ngân sách & Địa bàn trọng tâm -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="budget" class="form-label">
                            Tổng ngân sách dự kiến (VNĐ)
                        </label>
                        <div class="input-wrapper {{ $errors->has('budget') ? 'has-error' : '' }}">
                            <div class="input-icon-box">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="12" x="2" y="6" rx="2"></rect>
                                    <circle cx="12" cy="12" r="2"></circle>
                                    <path d="M6 12h.01M18 12h.01"></path>
                                </svg>
                            </div>
                            <input type="number"
                                   name="budget"
                                   id="budget"
                                   class="form-input"
                                   placeholder="15000000"
                                   value="{{ old('budget', '15000000') }}"
                                   min="0"
                                   step="100000">
                            <span class="input-suffix">đ</span>
                        </div>
                        @error('budget')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="destination_area" class="form-label">
                            Địa bàn trọng tâm
                        </label>
                        <div class="input-wrapper {{ $errors->has('destination_area') ? 'has-error' : '' }}">
                            <div class="input-icon-box">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <select name="destination_area" id="destination_area" class="form-select">
                                <option value="Đà Nẵng & Hội An" {{ old('destination_area', 'Đà Nẵng & Hội An') === 'Đà Nẵng & Hội An' ? 'selected' : '' }}>Đà Nẵng & Hội An</option>
                                <option value="Hà Nội & Miền Bắc" {{ old('destination_area') === 'Hà Nội & Miền Bắc' ? 'selected' : '' }}>Hà Nội & Miền Bắc</option>
                                <option value="Vịnh Hạ Long" {{ old('destination_area') === 'Vịnh Hạ Long' ? 'selected' : '' }}>Vịnh Hạ Long</option>
                                <option value="Sa Pa & Fansipan" {{ old('destination_area') === 'Sa Pa & Fansipan' ? 'selected' : '' }}>Sa Pa & Fansipan</option>
                                <option value="Huế - Cố Đô" {{ old('destination_area') === 'Huế - Cố Đô' ? 'selected' : '' }}>Huế - Cố Đô</option>
                                <option value="Nha Trang" {{ old('destination_area') === 'Nha Trang' ? 'selected' : '' }}>Nha Trang</option>
                                <option value="Đà Lạt" {{ old('destination_area') === 'Đà Lạt' ? 'selected' : '' }}>Đà Lạt</option>
                                <option value="TP. Hồ Chí Minh" {{ old('destination_area') === 'TP. Hồ Chí Minh' ? 'selected' : '' }}>TP. Hồ Chí Minh</option>
                                <option value="Phú Quốc" {{ old('destination_area') === 'Phú Quốc' ? 'selected' : '' }}>Phú Quốc</option>
                            </select>
                        </div>
                        @error('destination_area')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Ảnh bìa chuyến đi (Tùy chọn) -->
                <div class="form-group">
                    <label for="cover_image" class="form-label">
                        Ảnh bìa chuyến đi (Tùy chọn)
                    </label>
                    <div class="input-wrapper {{ $errors->has('cover_image') ? 'has-error' : '' }}">
                        <div class="input-icon-box">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                <circle cx="9" cy="9" r="2"></circle>
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                            </svg>
                        </div>
                        <input type="text"
                               name="cover_image"
                               id="cover_image"
                               class="form-input"
                               placeholder="https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&1"
                               value="{{ old('cover_image', 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&1') }}">
                    </div>
                    @error('cover_image')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Mô tả & Ghi chú mục tiêu -->
                <div class="form-group">
                    <label for="description" class="form-label">
                        Mô tả & Ghi chú mục tiêu chuyến đi
                    </label>
                    <textarea name="description"
                              id="description"
                              class="form-textarea {{ $errors->has('description') ? 'has-error' : '' }}"
                              placeholder="Chuyến đi 6 ngày 5 đêm khám phá các điểm nổi tiếng tại Đà Nẵng và Hội An. Dự kiến tham quan Bà Nà Hills, tắm biển Mỹ Khê, chèo SUP ngắm bình minh và thưởng thức đặc sản mì Quảng, bánh mì Phượng.">{{ old('description', 'Chuyến đi 6 ngày 5 đêm khám phá các điểm nổi tiếng tại Đà Nẵng và Hội An. Dự kiến tham quan Bà Nà Hills, tắm biển Mỹ Khê, chèo SUP ngắm bình minh và thưởng thức đặc sản mì Quảng, bánh mì Phượng.') }}</textarea>
                    @error('description')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nút thao tác -->
                <div class="form-actions">
                    <a href="{{ route('trips.index') }}" class="btn-cancel">Hủy bỏ</a>
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
    </main>

</body>
</html>
