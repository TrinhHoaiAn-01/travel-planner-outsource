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
                <a href="{{ route('itinerary.index', $trip->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
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
                    <li class="breadcrumb-item"><a href="{{ route('itinerary.index', $trip->id) }}" class="text-decoration-none text-muted">{{ Str::limit($trip->name, 25) }}</a></li>
                    <li class="breadcrumb-item active fw-medium text-dark" aria-current="page">Chỉnh sửa</li>
                </ol>
            </nav>

            <div class="card-custom p-4 p-md-5">
                <div class="mb-4 pb-2 border-bottom">
                    <h3 class="fw-bold mb-1 text-dark">Chỉnh Sửa Chuyến Đi</h3>
                    <p class="text-muted small mb-0">Cập nhật thông tin chi tiết, thời gian, ngân sách và ghi chú mục tiêu cho chuyến đi của bạn</p>
                </div>

                <!-- Thông báo lỗi xác thực từ máy chủ (Server-side validation) kèm nút OK -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 shadow-sm border border-danger-subtle" role="alert" id="serverValidationAlert">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                            <strong class="fw-bold text-danger">Thông tin nhập không hợp lệ, vui lòng kiểm tra lại:</strong>
                        </div>
                        <ul class="mb-3 ps-3 small text-danger-emphasis">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <!-- Nút OK nằm ngay trong thông báo để người dùng xác nhận và đóng thông báo -->
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-danger btn-sm px-4 fw-semibold rounded-pill shadow-sm" data-bs-dismiss="alert">
                                OK
                            </button>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Thông báo lỗi kiểm tra phía người dùng (Client-side validation) kèm nút OK (Chỉ hiển thị khi có trường nhập không hợp lệ) -->
                <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 shadow-sm border border-danger-subtle d-none" role="alert" id="clientValidationErrorAlert">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-exclamation-octagon-fill fs-5 text-danger"></i>
                        <strong class="fw-bold text-danger">Dữ liệu nhập không hợp lệ, vui lòng kiểm tra lại:</strong>
                    </div>
                    <ul class="mb-3 ps-3 small text-danger-emphasis" id="clientValidationErrorsList">
                        <!-- Danh sách lỗi do JavaScript chèn vào khi người dùng nhập sai -->
                    </ul>
                    <!-- Nút OK đóng thông báo và chuyển con trỏ chuột đến trường nhập sai đầu tiên -->
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-danger btn-sm px-4 fw-semibold rounded-pill shadow-sm" id="btnDismissClientAlert">
                            OK
                        </button>
                    </div>
                </div>

                <!-- Form Chỉnh sửa chuyến đi (Method PUT / PATCH) -->
                <form action="{{ route('trips.update', $trip->id) }}" method="POST" id="tripEditForm" novalidate>
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
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" id="startDate" value="{{ old('start_date', $trip->start_date ? $trip->start_date->format('Y-m-d') : '') }}" required onchange="validateDates()">
                            </div>
                            @error('start_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="endDate" class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" id="endDate" value="{{ old('end_date', $trip->end_date ? $trip->end_date->format('Y-m-d') : '') }}" required onchange="validateDates()">
                            </div>
                            @error('end_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Hộp thông báo thời lượng tính toán theo thời gian thực (Real-time Duration Feedback Box) -->
                    <div class="alert alert-light border rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2" id="dateFeedbackBox">
                        <div class="d-flex align-items-center gap-2 text-primary fw-semibold fs-6">
                            <i class="bi bi-clock-history"></i>
                            <span>Thời lượng chuyến đi: <strong class="text-dark" id="calculatedDuration">{{ $trip->duration_label }}</strong></span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold" id="dateRangeBadge">
                            {{ $trip->date_range_formatted }}
                        </span>
                    </div>

                    <!-- Cảnh báo lỗi ngày không hợp lệ chuẩn theo form.html và Phần 4.3 -->
                    <div id="dateAlert" class="alert alert-danger alert-dismissible fade d-none mb-4" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                            <span id="dateAlertText" class="fw-semibold">Ngày bắt đầu không được lớn hơn ngày kết thúc!</span>
                        </div>
                    </div>

                    <!-- Cảnh báo và xử lý lịch trình phụ thuộc khi rút ngắn thời lượng chuyến đi (Phần 4.3 Quản lý chuyến đi) -->
                    <div id="itineraryDependencyWarningBox" class="alert alert-warning border border-warning-subtle rounded-4 p-3 mb-4 d-none shadow-sm" role="alert">
                        <div class="d-flex align-items-start gap-3">
                            <div class="text-warning-emphasis fs-4 mt-1">
                                <i class="bi bi-exclamation-diamond-fill"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="fw-bold text-dark mb-0">Lưu ý: Có hoạt động lịch trình phụ thuộc bị ảnh hưởng do rút ngắn ngày</h6>
                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-semibold" id="affectedBadge">
                                        <span id="affectedCountText">0</span> hoạt động
                                    </span>
                                </div>
                                <p class="small text-secondary mb-2" id="affectedNoticeText">
                                    Thời lượng chuyến đi mới bị rút ngắn so với lịch trình hiện tại. Hệ thống sẽ <strong>tự động bảo toàn an toàn 100%</strong> toàn bộ các hoạt động ở các ngày vượt quá bằng cách chuyển chúng về <strong>Ngày <span id="targetDayNotice">cuối cùng</span></strong> ngay khi bạn lưu chuyến đi.
                                </p>
                                <div class="bg-white rounded-3 p-2 border border-warning-subtle small shadow-sm" id="affectedItemsListContainer">
                                    <div class="fw-semibold text-muted mb-1 small text-uppercase"><i class="bi bi-arrow-repeat me-1 text-primary"></i> Danh sách hoạt động sẽ được tự động dời ngày:</div>
                                    <ul class="mb-0 ps-3 text-dark small" id="affectedItemsList">
                                        <!-- Danh sách hoạt động do JavaScript điền vào -->
                                    </ul>
                                </div>
                            </div>
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
                        <a href="{{ route('itinerary.index', $trip->id) }}" class="btn btn-light rounded-pill px-4">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Modal Thông Báo Lỗi Nhập Liệu Hiện Ra Trên Màn Hình Kèm Nút OK (Chỉ hiển thị khi trường nhập không hợp lệ) -->
    <div class="modal fade" id="clientValidationModal" tabindex="-1" aria-labelledby="clientValidationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-danger text-white border-0 py-3">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="clientValidationModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Thông Báo Lỗi Nhập Liệu</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">Vui lòng kiểm tra và sửa lại các trường thông tin chưa hợp lệ sau:</p>
                    <ul class="mb-0 ps-3 small text-danger fw-medium" id="clientValidationModalList">
                        <!-- Danh sách lỗi đưa vào động -->
                    </ul>
                </div>
                <!-- Nút OK ngay trong thông báo lỗi để người dùng đóng thông báo và sửa lỗi -->
                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-end">
                    <button type="button" class="btn btn-danger px-4 rounded-pill fw-semibold shadow-sm" data-bs-dismiss="modal" id="btnModalOk">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Khớp 100% Giao diện chuẩn -->
    <footer class="py-4 mt-5 text-center text-white-50 small" style="background-color: #0F172A;">
        <div class="container">
            <p class="mb-0 text-white-50">&copy; 2026 Travel Planner Travel Platform. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hàm định dạng ngày hiển thị dd/mm/yyyy từ chuỗi yyyy-mm-dd
        function formatDisplayDate(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            return dateStr;
        }

        // Kiểm tra tính hợp lệ và tự động tính toán thời lượng số ngày, số đêm theo thời gian thực (Phần 4.3 Quản lý chuyến đi)
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

            // Ràng buộc min ngày kết thúc phải từ ngày bắt đầu
            if (startVal) {
                endInput.min = startVal;
            }

            if (!startVal || !endVal) {
                if (dateFeedbackBox) dateFeedbackBox.classList.add('d-none');
                if (dateAlert) dateAlert.classList.add('d-none');
                return true;
            }

            const startDate = new Date(startVal);
            const endDate = new Date(endVal);

            // Kiểm tra: ngày bắt đầu không được lớn hơn ngày kết thúc
            if (startDate > endDate) {
                if (dateAlert) {
                    dateAlert.classList.remove('d-none');
                    dateAlert.classList.add('show');
                }
                if (dateFeedbackBox) {
                    dateFeedbackBox.classList.add('d-none');
                }
                endInput.setCustomValidity('Ngày kết thúc không được trước ngày bắt đầu.');
                checkItineraryDependencies(0);
                return false;
            } else {
                if (dateAlert) {
                    dateAlert.classList.add('d-none');
                    dateAlert.classList.remove('show');
                }
                endInput.setCustomValidity('');
            }

            // Tính toán chính xác số ngày và số đêm
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

            // Kiểm tra các hoạt động lịch trình phụ thuộc khi số ngày chuyến đi thay đổi
            checkItineraryDependencies(diffDays);

            return true;
        }

        // Dữ liệu các hoạt động lịch trình phụ thuộc hiện có của chuyến đi
        const tripItineraryItems = {!! json_encode($itineraryItemsList ?? [], JSON_UNESCAPED_UNICODE) !!};
        const originalTotalDays = {{ (int) $trip->total_days }};

        // Hàm kiểm tra các hoạt động lịch trình bị ảnh hưởng khi rút ngắn chuyến đi
        function checkItineraryDependencies(newDays) {
            const warningBox = document.getElementById('itineraryDependencyWarningBox');
            const affectedCountText = document.getElementById('affectedCountText');
            const targetDayNotice = document.getElementById('targetDayNotice');
            const affectedItemsList = document.getElementById('affectedItemsList');

            if (!warningBox) return;

            // Chỉ kích hoạt khi chuyến đi thực sự bị rút ngắn số ngày và có hoạt động ở các ngày thừa
            if (newDays > 0 && originalTotalDays > 0 && newDays < originalTotalDays && tripItineraryItems.length > 0) {
                const affected = tripItineraryItems.filter(item => item.day_number > newDays);
                if (affected.length > 0) {
                    warningBox.classList.remove('d-none');
                    if (affectedCountText) affectedCountText.textContent = affected.length;
                    if (targetDayNotice) targetDayNotice.textContent = newDays;
                    if (affectedItemsList) {
                        affectedItemsList.innerHTML = affected.map(item => {
                            const timeStr = item.start_time ? ` (${item.start_time}${item.end_time ? ' - ' + item.end_time : ''})` : '';
                            return `<li class="mb-1"><span class="badge bg-secondary me-1">Ngày ${item.day_number}</span> <strong>${item.destination_name}</strong>${timeStr} <i class="bi bi-arrow-right text-warning-emphasis mx-1"></i> Sẽ dời về <strong class="text-primary">Ngày ${newDays}</strong></li>`;
                        }).join('');
                    }
                    return;
                }
            }

            // Nếu không bị rút ngắn hoặc không có hoạt động phụ thuộc nào bị thừa ngày
            warningBox.classList.add('d-none');
        }
        // Tương thích với onchange trên input
        function validateDates() {
            return validateAndCalculateDates();
        }

        // Xử lý kiểm tra các trường nhập liệu không hợp lệ phía Client
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('tripEditForm');
            const clientAlert = document.getElementById('clientValidationErrorAlert');
            const clientErrorsList = document.getElementById('clientValidationErrorsList');
            const btnDismissClientAlert = document.getElementById('btnDismissClientAlert');
            const validationModalElement = document.getElementById('clientValidationModal');
            const validationModalList = document.getElementById('clientValidationModalList');
            const btnModalOk = document.getElementById('btnModalOk');
            const startInput = document.getElementById('startDate');
            const endInput = document.getElementById('endDate');

            // Lắng nghe sự kiện thay đổi ngày
            if (startInput) {
                startInput.addEventListener('input', validateAndCalculateDates);
                startInput.addEventListener('change', validateAndCalculateDates);
            }
            if (endInput) {
                endInput.addEventListener('input', validateAndCalculateDates);
                endInput.addEventListener('change', validateAndCalculateDates);
            }

            // Tính toán thời lượng ban đầu
            validateAndCalculateDates();

            let validationModal = null;
            if (validationModalElement && typeof bootstrap !== 'undefined') {
                validationModal = new bootstrap.Modal(validationModalElement);
            }

            let firstInvalidElement = null;

            // Xử lý đóng thông báo lỗi và đưa con trỏ đến ô nhập không hợp lệ đầu tiên
            function handleDismissErrorAlert() {
                if (clientAlert) {
                    clientAlert.classList.add('d-none');
                }
                if (firstInvalidElement) {
                    firstInvalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalidElement.focus();
                }
            }

            if (btnDismissClientAlert) {
                btnDismissClientAlert.addEventListener('click', handleDismissErrorAlert);
            }

            if (btnModalOk) {
                btnModalOk.addEventListener('click', function () {
                    if (validationModal) {
                        validationModal.hide();
                    }
                    if (firstInvalidElement) {
                        firstInvalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalidElement.focus();
                    }
                });
            }

            // Xóa viền đỏ cảnh báo khi người dùng bắt đầu chỉnh sửa lại dữ liệu
            const inputsToTrack = ['tripName', 'startDate', 'endDate', 'estimatedBudget', 'tripDescription'];
            inputsToTrack.forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', function () {
                        el.classList.remove('is-invalid');
                    });
                }
            });

            if (form) {
                form.addEventListener('submit', function (e) {
                    const errors = [];
                    firstInvalidElement = null;

                    // 1. Kiểm tra trường Tên chuyến đi (bắt buộc, không để trống, tối đa 255 ký tự)
                    const nameInput = document.getElementById('tripName');
                    const nameVal = nameInput ? nameInput.value.trim() : '';
                    if (!nameVal) {
                        errors.push({ element: nameInput, message: 'Vui lòng nhập tên chuyến đi.' });
                    } else if (nameVal.length > 255) {
                        errors.push({ element: nameInput, message: 'Tên chuyến đi không được vượt quá 255 ký tự.' });
                    }

                    // 2. Kiểm tra trường Ngày bắt đầu (bắt buộc)
                    const startDateInput = document.getElementById('startDate');
                    const startVal = startDateInput ? startDateInput.value : '';
                    if (!startVal) {
                        errors.push({ element: startDateInput, message: 'Vui lòng chọn ngày bắt đầu chuyến đi.' });
                    }

                    // 3. Kiểm tra trường Ngày kết thúc (bắt buộc)
                    const endDateInput = document.getElementById('endDate');
                    const endVal = endDateInput ? endDateInput.value : '';
                    if (!endVal) {
                        errors.push({ element: endDateInput, message: 'Vui lòng chọn ngày kết thúc chuyến đi.' });
                    }

                    // 4. Kiểm tra logic ngày: Ngày kết thúc không được trước ngày bắt đầu
                    if (startVal && endVal) {
                        const startDate = new Date(startVal);
                        const endDate = new Date(endVal);
                        if (startDate > endDate) {
                            errors.push({ element: endDateInput, message: 'Ngày kết thúc không được trước ngày bắt đầu.' });
                        }
                    }

                    // 5. Kiểm tra trường Tổng ngân sách dự kiến (không được âm nếu có nhập)
                    const budgetInput = document.getElementById('estimatedBudget');
                    if (budgetInput && budgetInput.value !== '') {
                        const budgetVal = Number(budgetInput.value);
                        if (isNaN(budgetVal) || budgetVal < 0) {
                            errors.push({ element: budgetInput, message: 'Tổng ngân sách dự kiến không được nhỏ hơn 0 VNĐ.' });
                        }
                    }

                    // 6. Kiểm tra trường Mô tả và ghi chú mục tiêu (tối đa 3000 ký tự)
                    const descInput = document.getElementById('tripDescription');
                    if (descInput && descInput.value.length > 3000) {
                        errors.push({ element: descInput, message: 'Mô tả và ghi chú chuyến đi không được vượt quá 3000 ký tự.' });
                    }

                    // LƯU Ý QUAN TRỌNG: Chỉ thêm / hiển thị thông báo lỗi nếu có trường nhập không hợp lệ
                    if (errors.length > 0) {
                        e.preventDefault();

                        // Xóa cờ lỗi cũ và đánh dấu viền đỏ các trường không hợp lệ
                        inputsToTrack.forEach(function (id) {
                            const el = document.getElementById(id);
                            if (el) el.classList.remove('is-invalid');
                        });

                        errors.forEach(function (err) {
                            if (err.element) {
                                err.element.classList.add('is-invalid');
                            }
                        });

                        firstInvalidElement = errors[0].element;

                        // Hiển thị khung thông báo lỗi kèm nút OK ở đầu form trên màn hình
                        if (clientErrorsList) {
                            clientErrorsList.innerHTML = errors.map(function (err) {
                                return '<li>' + err.message + '</li>';
                            }).join('');
                        }
                        if (clientAlert) {
                            clientAlert.classList.remove('d-none');
                            clientAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }

                        // Hiển thị modal thông báo lỗi hiện ra ngay trên màn hình kèm nút OK
                        if (validationModalList) {
                            validationModalList.innerHTML = errors.map(function (err) {
                                return '<li class="mb-1">' + err.message + '</li>';
                            }).join('');
                        }
                        if (validationModal) {
                            validationModal.show();
                        }
                    } else {
                        // Nếu tất cả các trường nhập đều hợp lệ: Ẩn thông báo lỗi hoàn toàn, cho phép gửi form bình thường
                        if (clientAlert) {
                            clientAlert.classList.add('d-none');
                        }
                        if (validationModal) {
                            validationModal.hide();
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
