<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Chuyến đi - Travel Planner</title>
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
            --danger: #DC2626;
            --surface: #FFFFFF;
            --bg-main: #F8FAFC;
            --border-color: #E2E8F0;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --radius-lg: 16px;
            --radius-md: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.08);
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

        .card-custom {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-group-text {
            border-color: var(--border-color);
            color: var(--text-muted);
        }

        .form-control, .form-select {
            border-color: var(--border-color);
            font-size: 14.5px;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.15);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-weight: 600;
            border-radius: 9999px;
            padding: 10px 24px;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.25);
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
            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ route('trips.show', $trip->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại chi tiết
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Form Content -->
    <main class="py-5">
        <div class="container" style="max-width: 800px;">
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('trips.index') }}" class="text-decoration-none text-muted">Chuyến đi</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('trips.show', $trip->id) }}" class="text-decoration-none text-muted">{{ Str::limit($trip->name, 25) }}</a></li>
                    <li class="breadcrumb-item active fw-medium text-dark" aria-current="page">Chỉnh sửa</li>
                </ol>
            </nav>

            <div class="card-custom p-4 p-md-5">
                <div class="mb-4 pb-2 border-bottom">
                    <h3 class="fw-bold mb-1 text-dark">Chỉnh Sửa Chuyến Đi</h3>
                    <p class="text-muted small mb-0">Cập nhật thông tin chi tiết, thời gian, ngân sách và ghi chú mục tiêu cho chuyến đi của bạn</p>
                </div>

                <!-- Validation Error Notification -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <strong class="fw-bold">Vui lòng kiểm tra lại thông tin:</strong>
                        </div>
                        <ul class="mb-0 ps-3 small">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Form Chỉnh sửa chuyến đi (Method PUT / PATCH) -->
                <form action="{{ route('trips.update', $trip->id) }}" method="POST" id="tripEditForm">
                    @csrf
                    @method('PUT')

                    <!-- Tên chuyến đi -->
                    <div class="mb-3">
                        <label for="tripName" class="form-label">Tên chuyến đi <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-pin-map-fill text-primary"></i></span>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="tripName" placeholder="Ví dụ: Nghỉ dưỡng Đà Nẵng - Hội An cùng gia đình" value="{{ old('name', $trip->name) }}" required>
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ngày bắt đầu & Ngày kết thúc -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" id="startDate" value="{{ old('start_date', $trip->start_date ? $trip->start_date->format('Y-m-d') : '') }}" required onchange="validateAndCalculateDates()">
                            </div>
                            @error('start_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="endDate" class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" id="endDate" value="{{ old('end_date', $trip->end_date ? $trip->end_date->format('Y-m-d') : '') }}" required onchange="validateAndCalculateDates()">
                            </div>
                            @error('end_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Hộp phản hồi thời lượng tính toán theo thời gian thực (Real-time Duration Feedback Box) -->
                    <div class="alert alert-light border shadow-sm p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" id="dateFeedbackBox">
                        <div class="d-flex align-items-center gap-2 text-primary fw-semibold small">
                            <i class="bi bi-clock-history fs-5"></i>
                            <span>Thời lượng chuyến đi: <strong id="calculatedDuration" class="text-dark">{{ $trip->duration_label ?? 'Chưa xác định' }}</strong></span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold" id="dateRangeBadge">
                            {{ $trip->date_range_formatted ?? '' }}
                        </span>
                    </div>

                    <!-- Cảnh báo lỗi ngày không hợp lệ chuẩn theo form.html và Phần 4.3 -->
                    <div id="dateAlert" class="alert alert-danger alert-dismissible fade show rounded-3 mb-3 d-none" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                            <span id="dateAlertText" class="fw-semibold">Ngày bắt đầu không được lớn hơn ngày kết thúc!</span>
                        </div>
                    </div>

                    <!-- Ngân sách dự kiến & Trạng thái -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="estimatedBudget" class="form-label">Tổng ngân sách dự kiến (VNĐ)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-cash-stack"></i></span>
                                <input type="number" name="budget" class="form-control @error('budget') is-invalid @enderror" id="estimatedBudget" placeholder="15000000" value="{{ old('budget', (int)$trip->budget) }}" min="0" step="100000">
                                <span class="input-group-text">₫</span>
                            </div>
                            @error('budget')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tripStatus" class="form-label">Trạng thái chuyến đi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-flag"></i></span>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" id="tripStatus">
                                    <option value="planned" {{ old('status', $trip->status) === 'planned' ? 'selected' : '' }}>Sắp tới (Đã lên kế hoạch)</option>
                                    <option value="ongoing" {{ old('status', $trip->status) === 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                                    <option value="completed" {{ old('status', $trip->status) === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                                    <option value="draft" {{ old('status', $trip->status) === 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                </select>
                            </div>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Địa bàn trọng tâm & Ảnh bìa -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="destinationLocation" class="form-label">Địa bàn trọng tâm</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                <select name="destination_area" class="form-select" id="destinationLocation">
                                    <option value="Đà Nẵng & Hội An" {{ str_contains($trip->description ?? '', 'Đà Nẵng') ? 'selected' : '' }}>Đà Nẵng & Phố Cổ Hội An</option>
                                    <option value="Quảng Ninh & Vịnh Hạ Long" {{ str_contains($trip->description ?? '', 'Hạ Long') ? 'selected' : '' }}>Quảng Ninh & Vịnh Hạ Long</option>
                                    <option value="Đảo Ngọc Phú Quốc" {{ str_contains($trip->description ?? '', 'Phú Quốc') ? 'selected' : '' }}>Đảo Ngọc Phú Quốc</option>
                                    <option value="Sa Pa - Lào Cai" {{ str_contains($trip->description ?? '', 'Sa Pa') ? 'selected' : '' }}>Sa Pa - Lào Cai</option>
                                    <option value="Đà Lạt - Lâm Đồng" {{ str_contains($trip->description ?? '', 'Đà Lạt') ? 'selected' : '' }}>Đà Lạt - Lâm Đồng</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="coverImage" class="form-label">Ảnh bìa chuyến đi (Tùy chọn URL)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-image"></i></span>
                                <input type="url" name="cover_image" class="form-control" id="coverImage" placeholder="https://..." value="{{ old('cover_image') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Mô tả & Ghi chú mục tiêu chuyến đi (Nghiệp vụ chức năng 4) -->
                    <div class="mb-4">
                        <label for="tripDescription" class="form-label d-flex justify-content-between">
                            <span>Mô tả & Ghi chú mục tiêu chuyến đi</span>
                            <span class="text-muted small fw-normal">Tối đa 3000 ký tự</span>
                        </label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="tripDescription" rows="4" placeholder="Mục tiêu chuyến đi, lưu ý về hành lý, trang phục, lịch trình tóm tắt hoặc sở thích của đoàn...">{{ old('description', $trip->description) }}</textarea>
                        <div class="form-text small text-muted">
                            <i class="bi bi-info-circle me-1"></i>Ghi chú này sẽ được hiển thị nổi bật trên màn hình chi tiết lịch trình của chuyến đi.
                        </div>
                        @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nút hành động -->
                    <div class="d-flex justify-content-end gap-2 border-top pt-4">
                        <a href="{{ route('trips.show', $trip->id) }}" class="btn btn-light rounded-pill px-4">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer Khớp 100% Giao diện chuẩn -->
    <footer class="py-4 mt-5 text-center text-white-50 small" style="background-color: #0F172A;">
        <div class="container">
            <p class="mb-0 text-white-50">&copy; 2026 Travel Planner Travel Platform. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hàm định dạng ngày hiển thị dd/mm/yyyy
        function formatDisplayDate(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            return dateStr;
        }

        // Xử lý kiểm tra và tính toán ngày bắt đầu / ngày kết thúc theo thời gian thực (Phần 4.3 Quản lý chuyến đi)
        function validateAndCalculateDates() {
            const startInput = document.getElementById('startDate');
            const endInput = document.getElementById('endDate');
            const dateAlert = document.getElementById('dateAlert');
            const dateFeedbackBox = document.getElementById('dateFeedbackBox');
            const durationEl = document.getElementById('calculatedDuration');
            const rangeEl = document.getElementById('dateRangeBadge');

            if (!startInput || !endInput) return true;

            const startVal = startInput.value;
            const endVal = endInput.value;

            // Tự động gán min cho ngày kết thúc để người dùng không chọn ngày trước ngày bắt đầu
            if (startVal) {
                endInput.min = startVal;
            }

            if (!startVal || !endVal) {
                if (dateFeedbackBox) dateFeedbackBox.classList.add('d-none');
                if (dateAlert) dateAlert.classList.add('d-none');
                endInput.setCustomValidity('');
                return true;
            }

            const startDate = new Date(startVal);
            const endDate = new Date(endVal);

            // Kiểm tra ràng buộc: Ngày bắt đầu không được lớn hơn ngày kết thúc
            if (startDate > endDate) {
                if (dateAlert) {
                    dateAlert.classList.remove('d-none');
                }
                if (dateFeedbackBox) {
                    dateFeedbackBox.classList.add('d-none');
                }
                endInput.setCustomValidity('Ngày kết thúc không được trước ngày bắt đầu.');
                endInput.classList.add('is-invalid');
                return false;
            }

            // Hợp lệ: Xóa cảnh báo lỗi và tính toán thời lượng
            if (dateAlert) {
                dateAlert.classList.add('d-none');
            }
            endInput.setCustomValidity('');
            endInput.classList.remove('is-invalid');

            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24)) + 1;
            const diffNights = Math.max(0, diffDays - 1);

            let durationLabel = `${diffDays} ngày`;
            if (diffNights > 0) {
                durationLabel = `${diffDays} ngày ${diffNights} đêm`;
            }

            if (durationEl) durationEl.textContent = durationLabel;
            if (rangeEl) rangeEl.textContent = `${formatDisplayDate(startVal)} — ${formatDisplayDate(endVal)}`;
            if (dateFeedbackBox) dateFeedbackBox.classList.remove('d-none');

            return true;
        }

        // Tự động chạy khi tài liệu tải xong
        document.addEventListener('DOMContentLoaded', function () {
            const startInput = document.getElementById('startDate');
            const endInput = document.getElementById('endDate');
            const form = document.getElementById('tripEditForm');

            if (startInput) {
                startInput.addEventListener('change', validateAndCalculateDates);
                startInput.addEventListener('input', validateAndCalculateDates);
            }

            if (endInput) {
                endInput.addEventListener('change', validateAndCalculateDates);
                endInput.addEventListener('input', validateAndCalculateDates);
            }

            // Tính toán ban đầu
            validateAndCalculateDates();

            if (form) {
                form.addEventListener('submit', function (e) {
                    if (!validateAndCalculateDates()) {
                        e.preventDefault();
                        const dateAlert = document.getElementById('dateAlert');
                        if (dateAlert) {
                            dateAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
