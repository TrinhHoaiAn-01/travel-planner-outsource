<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh Sửa Hoạt Động - {{ $trip->name }} - Travel Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #0066FF;
            --primary-hover: #0052CC;
            --primary-light: #EBF3FF;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --bg-light: #F8FAFC;
            --danger: #EF4444;
            --success: #10B981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #F1F5F9;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .site-header {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            padding: 0 32px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--primary);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-link-custom {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.15s;
        }

        .nav-link-custom:hover,
        .nav-link-custom.active {
            color: var(--primary);
        }

        /* Breadcrumb Bar */
        .breadcrumb-bar {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 32px;
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .breadcrumb-bar a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.15s;
        }

        .breadcrumb-bar a:hover {
            color: var(--primary);
        }

        /* Main Container */
        .page-container {
            max-width: 680px;
            width: 100%;
            margin: 40px auto;
            padding: 0 20px;
            flex: 1;
        }

        /* Form Card */
        .form-card {
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .form-card-header {
            padding: 24px 28px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.3px;
        }

        .close-btn-link {
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

        .close-btn-link:hover {
            color: var(--text-dark);
            background-color: var(--bg-light);
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
            gap: 8px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-label .required-mark {
            color: var(--danger);
        }

        .form-control-custom {
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 11px 14px;
            font-size: 14px;
            color: var(--text-dark);
            background-color: #FFFFFF;
            transition: all 0.15s;
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
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .error-feedback {
            color: var(--danger);
            font-size: 12.5px;
            font-weight: 500;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <header class="site-header">
        <a href="{{ route('home') }}" class="header-logo">
            <i class="bi bi-compass"></i>
            <span>TRAVEL PLANNER</span>
        </a>

        <nav class="header-nav">
            <a href="{{ route('home') }}" class="nav-link-custom">
                <i class="bi bi-house"></i> Trang chủ
            </a>
            <a href="{{ route('destinations.index') }}" class="nav-link-custom">
                <i class="bi bi-compass"></i> Địa điểm
            </a>
            <a href="{{ route('trips.index') }}" class="nav-link-custom active">
                <i class="bi bi-geo-alt"></i> Chuyến đi
            </a>
            <a href="{{ route('favorites.index') }}" class="nav-link-custom">
                <i class="bi bi-heart"></i> Yêu thích
            </a>
        </nav>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <a href="{{ route('trips.index') }}">Chuyến đi của tôi</a>
        <i class="bi bi-chevron-right" style="font-size: 11px;"></i>
        <a href="{{ route('itinerary.index', ['trip' => $trip->id, 'day_number' => $item->day_number]) }}">{{ $trip->name }}</a>
        <i class="bi bi-chevron-right" style="font-size: 11px;"></i>
        <span>Chỉnh sửa hoạt động</span>
    </div>

    <!-- Main Container -->
    <main class="page-container">
        <div class="form-card">
            <!-- Header (Hình 20) -->
            <div class="form-card-header">
                <h1 class="form-card-title">Chỉnh Sửa Hoạt Động</h1>
                <a href="{{ route('itinerary.index', ['trip' => $trip->id, 'day_number' => $item->day_number]) }}" class="close-btn-link" title="Đóng">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>

            <!-- Form cập nhật (Bảng 22, PATCH /itinerary/{itinerary}) -->
            <form action="{{ route('itinerary.update', $item->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="form-card-body">
                    <!-- Tên hoạt động * -->
                    <div class="form-group-custom">
                        <label class="form-label" for="activity_name">Tên hoạt động <span class="required-mark">*</span></label>
                        <input type="text" 
                               name="activity_name" 
                               id="activity_name" 
                               class="form-control-custom @error('activity_name') is-invalid @enderror" 
                               placeholder="Ví dụ: Tham quan Bán đảo Sơn Trà & Chùa Linh Ứng"
                               value="{{ old('activity_name', $item->custom_title ?: ($item->destination ? $item->destination->name : '')) }}"
                               required>
                        <input type="hidden" name="destination_id" value="{{ old('destination_id', $item->destination_id) }}">
                        @error('activity_name')
                            <span class="error-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Ngày thực hiện trong chuyến đi -->
                    <div class="form-group-custom">
                        <label class="form-label" for="day_number">Ngày thực hiện <span class="required-mark">*</span></label>
                        <select name="day_number" id="day_number" class="form-control-custom @error('day_number') is-invalid @enderror">
                            @foreach($days_list as $dayItem)
                                <option value="{{ $dayItem['day_number'] }}" {{ old('day_number', $item->day_number) == $dayItem['day_number'] ? 'selected' : '' }}>
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
                            <label class="form-label" for="start_time">Giờ bắt đầu <span class="required-mark">*</span></label>
                            <input type="time" 
                                   name="start_time" 
                                   id="start_time" 
                                   class="form-control-custom @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time', $item->start_time ? substr($item->start_time, 0, 5) : '08:00') }}"
                                   required>
                            @error('start_time')
                                <span class="error-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label" for="end_time">Giờ kết thúc <span class="required-mark">*</span></label>
                            <input type="time" 
                                   name="end_time" 
                                   id="end_time" 
                                   class="form-control-custom @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time', $item->end_time ? substr($item->end_time, 0, 5) : '10:00') }}"
                                   required>
                            @error('end_time')
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
                                  placeholder="Ghi chú chi tiết hoặc lưu ý đặc biệt...">{{ old('note', $item->note) }}</textarea>
                        @error('note')
                            <span class="error-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Footer (Hình 20: Đóng & Cập nhật) -->
                <div class="form-card-footer">
                    <a href="{{ route('itinerary.index', ['trip' => $trip->id, 'day_number' => $item->day_number]) }}" class="btn-cancel">
                        Đóng
                    </a>
                    <button type="submit" class="btn-submit">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Xóa viền đỏ và thông báo lỗi khi bắt đầu chỉnh sửa lại input
        document.querySelectorAll('.form-control-custom').forEach(input => {
            const clearError = () => {
                input.classList.remove('is-invalid');
                const errorFeedback = input.closest('.form-group-custom')?.querySelector('.error-feedback');
                if (errorFeedback) errorFeedback.style.display = 'none';
            };
            input.addEventListener('input', clearError);
            input.addEventListener('change', clearError);
        });
    </script>
</body>
</html>
