<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt phòng lưu trú - {{ $destination->name }} - Travel Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0066FF;
            --primary-dark: #0052CC;
            --primary-light: #EBF3FF;
            --secondary: #0F172A;
            --text-main: #334155;
            --text-muted: #64748B;
            --bg-body: #F8FAFC;
            --bg-card: #FFFFFF;
            --border-color: #E2E8F0;
            --success: #10B981;
            --warning: #F59E0B;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.5;
            min-height: 100vh;
        }

        /* Top Header Navbar */
        header {
            background: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-weight: 800;
            font-size: 18px;
            color: var(--secondary);
            letter-spacing: -0.5px;
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #0066FF, #00C6FF);
            color: #FFFFFF;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-back-detail {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            color: #475569;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back-detail:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        /* Main Container */
        .main-wrapper {
            max-width: 1200px;
            margin: 32px auto 60px auto;
            padding: 0 24px;
        }

        .booking-grid {
            display: grid;
            grid-template-columns: 1.65fr 1fr;
            gap: 32px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .booking-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Form Card */
        .form-card {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 32px;
            box-shadow: var(--shadow-sm);
        }

        .card-header-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 6px;
        }

        .card-header-subtitle {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .form-section-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--secondary);
            margin: 28px 0 16px 0;
            padding-top: 24px;
            border-top: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        @media (max-width: 640px) {
            .form-row-2, .form-row-3 {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .input-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-wrapper svg {
            position: absolute;
            left: 12px;
            color: #94A3B8;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            height: 44px;
            padding: 0 14px 0 38px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 14px;
            color: var(--secondary);
            background: #FFFFFF;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control.no-icon {
            padding-left: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.12);
        }

        .form-control[readonly] {
            background-color: #F8FAFC;
            cursor: default;
            color: #1E293B;
            font-weight: 500;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748B'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 36px;
        }

        textarea.form-control {
            height: auto;
            padding: 10px 14px;
            resize: vertical;
        }

        /* Payment Method Options */
        .payment-methods-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 12px;
        }

        .payment-option-card {
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            cursor: pointer;
            background: #FFFFFF;
            transition: all 0.2s ease;
        }

        .payment-option-card:hover {
            border-color: #93C5FD;
            background: #FAFCFF;
        }

        .payment-option-card.selected {
            border-color: var(--primary);
            background: #F0F7FF;
        }

        .payment-radio {
            margin-top: 3px;
            accent-color: var(--primary);
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .payment-info {
            flex: 1;
        }

        .payment-title {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 3px;
        }

        /* Form Buttons */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #F1F5F9;
        }

        .btn-cancel {
            padding: 12px 24px;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            color: var(--secondary);
            background: #F1F5F9;
        }

        .btn-submit-booking {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            border-radius: 24px;
            padding: 13px 28px;
            font-size: 14.5px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 102, 255, 0.35);
            transition: all 0.2s;
        }

        .btn-submit-booking:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 102, 255, 0.4);
        }

        /* Summary Sticky Card */
        .summary-card {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 96px;
        }

        .summary-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--secondary);
            padding-bottom: 16px;
            margin-bottom: 18px;
            border-bottom: 1px solid #F1F5F9;
        }

        .hotel-snippet {
            display: flex;
            gap: 14px;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 18px;
            border-bottom: 1px solid #F1F5F9;
        }

        .hotel-thumb {
            width: 76px;
            height: 60px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            background-color: #E2E8F0;
        }

        .hotel-meta h4 {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--secondary);
            line-height: 1.3;
        }

        .hotel-location {
            font-size: 12.5px;
            color: var(--text-muted);
            margin: 3px 0 6px 0;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .badge-star {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #FEF3C7;
            color: #B45309;
            font-size: 11.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 6px;
        }

        .summary-details-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            font-size: 13.5px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-row .label {
            color: var(--text-muted);
        }

        .summary-row .value {
            font-weight: 600;
            color: #1E293B;
        }

        .summary-row .value.highlight {
            color: var(--primary);
            font-weight: 700;
        }

        .summary-divider {
            height: 1px;
            background: #F1F5F9;
            margin: 16px 0;
        }

        .total-box {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 14px;
        }

        .total-label-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .badge-best-price {
            background: #DEF7EC;
            color: #03543F;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
        }

        .total-amount-display {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
            text-align: right;
            line-height: 1.2;
        }

        .policy-banner {
            margin-top: 20px;
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12.5px;
            color: #166534;
            font-weight: 500;
        }

        .policy-banner svg {
            flex-shrink: 0;
            color: #10B981;
        }

        .error-alert {
            background: #FEF2F2;
            border: 1px solid #F87171;
            color: #991B1B;
            padding: 14px 18px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            font-size: 13.5px;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="header-container">
            <a href="{{ route('home') }}" class="logo">
                <div class="logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                </div>
                <span>TRAVEL PLANNER</span>
            </a>
            <a href="{{ route('destinations.show', $destination->id) }}" class="btn-back-detail">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Quay lại Chi tiết
            </a>
        </div>
    </header>

    <!-- Main Content Area (Chuẩn 100% theo Hình 27 Báo cáo) -->
    <main class="main-wrapper">

        @if ($errors->any())
            <div class="error-alert">
                <strong>Vui lòng kiểm tra lại thông tin:</strong>
                <ul style="margin: 6px 0 0 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm" class="booking-grid">
            @csrf

            <!-- Cột Trái: Thông Tin Đặt Phòng Lưu Trú -->
            <div class="form-card">
                <h1 class="card-header-title">Thông Tin Đặt Phòng Lưu Trú</h1>
                <p class="card-header-subtitle">Vui lòng kiểm tra kỹ thông tin lưu trú và điền chi tiết liên hệ người nhận phòng</p>

                <!-- Khách sạn & Hạng phòng -->
                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label" for="hotelName">Địa điểm / Khách sạn lưu trú</label>
                        <div class="input-icon-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M9 8h1m4 0h1m-5 4h1m4 0h1m-5 4h1m4 0h1M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path></svg>
                            <input type="text" id="hotelName" class="form-control" value="{{ $destination->name }}" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="roomTypeSelect">Hạng phòng lựa chọn</label>
                        <div class="input-icon-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20M6 8v9"></path></svg>
                            <select name="room_type_id" id="roomTypeSelect" class="form-control" required onchange="calculateBookingTotal()">
                                @if($destination->roomTypes->isNotEmpty())
                                    @foreach($destination->roomTypes as $room)
                                        <option value="{{ $room->id }}" 
                                            data-price="{{ $room->price_per_night }}" 
                                            data-name="{{ $room->name }}"
                                            {{ ($selectedRoomTypeId == $room->id || $loop->first) ? 'selected' : '' }}>
                                            {{ $room->name }} - {{ number_format($room->price_per_night, 0, ',', '.') }}đ / đêm
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">Chưa có hạng phòng</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Ngày nhận, trả & Số lượng phòng -->
                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label" for="checkInInput">Ngày nhận phòng (Check-in) *</label>
                        <div class="input-icon-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <input type="date" name="check_in_date" id="checkInInput" class="form-control" value="{{ $checkInDate }}" min="{{ date('Y-m-d') }}" required onchange="calculateBookingTotal()">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="checkOutInput">Ngày trả phòng (Check-out) *</label>
                        <div class="input-icon-wrapper">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            <input type="date" name="check_out_date" id="checkOutInput" class="form-control" value="{{ $checkOutDate }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required onchange="calculateBookingTotal()">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="roomCountInput">Số lượng phòng *</label>
                        <input type="number" name="room_count" id="roomCountInput" class="form-control no-icon" value="{{ $roomCount }}" min="1" max="10" required onchange="calculateBookingTotal()" onkeyup="calculateBookingTotal()">
                    </div>
                </div>

                <!-- Chuyến đi liên kết (nếu có) -->
                @if(isset($userTrips) && $userTrips->isNotEmpty())
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" for="tripSelect">Gắn vào Chuyến đi (Tùy chọn)</label>
                        <select name="trip_id" id="tripSelect" class="form-control no-icon">
                            <option value="">-- Không gắn chuyến đi riêng --</option>
                            @foreach($userTrips as $t)
                                <option value="{{ $t->id }}" {{ $selectedTripId == $t->id ? 'selected' : '' }}>
                                    {{ $t->title }} ({{ \Carbon\Carbon::parse($t->start_date)->format('d/m') }} - {{ \Carbon\Carbon::parse($t->end_date)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Thông Tin Người Nhận Phòng -->
                <div class="form-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Thông Tin Người Nhận Phòng
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label" for="guestName">Họ và tên khách hàng *</label>
                        <input type="text" name="guest_name" id="guestName" class="form-control no-icon" value="{{ auth()->user()->name ?? 'Nguyễn Bảo Ngọc' }}" placeholder="Nguyễn Bảo Ngọc" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="guestPhone">Số điện thoại liên hệ *</label>
                        <input type="tel" name="guest_phone" id="guestPhone" class="form-control no-icon" value="{{ auth()->user()->phone ?? '0912 345 678' }}" placeholder="0912 345 678" required>
                    </div>
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label class="form-label" for="guestEmail">Địa chỉ Email xác nhận *</label>
                        <input type="email" name="guest_email" id="guestEmail" class="form-control no-icon" value="{{ auth()->user()->email ?? 'ngoc.travel@travelplanner.com' }}" placeholder="ngoc.travel@travelplanner.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="guestCount">Số lượng khách lưu trú</label>
                        <select name="guest_count" id="guestCount" class="form-control no-icon">
                            <option value="2 Người lớn" selected>2 Người lớn</option>
                            <option value="1 Người lớn">1 Người lớn</option>
                            <option value="2 Người lớn, 1 Trẻ em">2 Người lớn, 1 Trẻ em</option>
                            <option value="3 - 4 Người lớn">3 - 4 Người lớn</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 8px;">
                    <label class="form-label" for="specialRequests">Yêu cầu đặc biệt (Tùy chọn)</label>
                    <textarea name="special_requests" id="specialRequests" class="form-control" rows="3" placeholder="Ví dụ: Giường đôi lớn, phòng tầng cao yên tĩnh, check-in sớm..."></textarea>
                </div>

                <!-- Phương Thức Thanh Toán -->
                <div class="form-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    Phương Thức Thanh Toán
                </div>

                <div class="payment-methods-list">
                    <!-- Phương thức Online -->
                    <label class="payment-option-card selected" id="optCardOnline" onclick="selectPaymentOption('online')">
                        <input type="radio" name="payment_method" value="online" class="payment-radio" checked>
                        <div class="payment-info">
                            <div class="payment-title">
                                💳 Thẻ ATM / Visa / Mastercard / VNPAY QR
                            </div>
                            <div class="payment-desc">
                                Thanh toán an toàn, xác nhận đơn tức thì trong 30 giây
                            </div>
                        </div>
                    </label>

                    <!-- Phương thức COD / Tại chỗ -->
                    <label class="payment-option-card" id="optCardCod" onclick="selectPaymentOption('cod')">
                        <input type="radio" name="payment_method" value="cod" class="payment-radio">
                        <div class="payment-info">
                            <div class="payment-title">
                                💵 Thanh toán trực tiếp khi nhận phòng tại khách sạn
                            </div>
                            <div class="payment-desc">
                                Yêu cầu xác nhận qua số điện thoại trước 24h
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Nút thao tác -->
                <div class="form-actions">
                    <a href="{{ route('destinations.show', $destination->id) }}" class="btn-cancel">Hủy bỏ</a>
                    <button type="submit" class="btn-submit-booking">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Xác nhận & Hoàn tất Đặt phòng
                    </button>
                </div>
            </div>

            <!-- Cột Phải: Tóm Tắt Đơn Đặt Phòng (Sticky) -->
            <aside>
                <div class="summary-card">
                    <h2 class="summary-title">Tóm Tắt Đơn Đặt Phòng</h2>

                    <!-- Khách sạn Preview -->
                    <div class="hotel-snippet">
                        @php
                            $thumb = $destination->primaryImage?->image_path ?: ($destination->images->first()?->image_path ?? '/images/destinations/mercure-bana.jpg');
                            if (!empty($thumb) && !str_starts_with($thumb, 'http') && !str_starts_with($thumb, '/')) {
                                $thumb = '/' . $thumb;
                            }
                        @endphp
                        <img src="{{ $thumb }}" alt="{{ $destination->name }}" class="hotel-thumb" onerror="this.src='/images/default-hotel.jpg'">
                        <div class="hotel-meta">
                            <h4>{{ $destination->name }}</h4>
                            <div class="hotel-location">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                {{ $destination->city->name ?? 'Đà Nẵng' }}
                            </div>
                            <div class="badge-star">
                                ⭐ {{ number_format($destination->approvedReviews->avg('rating') ?: 4.9, 1) }}
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách thông số đặt phòng -->
                    <div class="summary-details-list">
                        <div class="summary-row">
                            <span class="label">Nhận phòng:</span>
                            <span class="value" id="summaryCheckInText">10/10/2026 (14:00)</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Trả phòng:</span>
                            <span class="value" id="summaryCheckOutText">12/10/2026 (12:00)</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Thời gian lưu trú:</span>
                            <span class="value highlight" id="summaryNightsText">2 đêm</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Số lượng phòng:</span>
                            <span class="value" id="summaryRoomCountText">1 phòng</span>
                        </div>
                    </div>

                    <div class="summary-divider"></div>

                    <!-- Chiết tính giá -->
                    <div class="summary-details-list">
                        <div class="summary-row">
                            <span class="label">Giá phòng:</span>
                            <span class="value" id="summaryRateDisplay">1.850.000đ x 2 đêm</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Thuế & Phí dịch vụ (8%):</span>
                            <span class="value" style="color: #10B981;">Đã bao gồm</span>
                        </div>
                    </div>

                    <div class="summary-divider"></div>

                    <!-- Tổng tiền thanh toán -->
                    <div class="total-box">
                        <div>
                            <div class="total-label-wrapper">
                                <span style="font-size: 13.5px; font-weight: 600; color: #475569;">Tổng tiền thanh toán</span>
                                <span class="badge-best-price">Giá tốt nhất</span>
                            </div>
                        </div>
                        <div class="total-amount-display" id="summaryTotalPriceDisplay">
                            3.700.000đ
                        </div>
                    </div>

                    <!-- Chính sách hủy phòng -->
                    <div class="policy-banner">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>Miễn phí hủy phòng trước 48h khi nhận phòng.</span>
                    </div>
                </div>
            </aside>

        </form>

    </main>

    <script>
        // Chọn phương thức thanh toán & đổi style
        function selectPaymentOption(method) {
            const cardOnline = document.getElementById('optCardOnline');
            const cardCod = document.getElementById('optCardCod');
            const radioOnline = cardOnline.querySelector('input[type="radio"]');
            const radioCod = cardCod.querySelector('input[type="radio"]');

            if (method === 'online') {
                radioOnline.checked = true;
                cardOnline.classList.add('selected');
                cardCod.classList.remove('selected');
            } else {
                radioCod.checked = true;
                cardCod.classList.add('selected');
                cardOnline.classList.remove('selected');
            }
        }

        // Định dạng số tiền kiểu Việt Nam (3.700.000đ)
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(amount)) + 'đ';
        }

        // Định dạng ngày theo chuẩn dd/mm/yyyy
        function formatDateVn(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }
            return dateStr;
        }

        // Tính toán tổng tiền realtime & cập nhật card Tóm tắt
        function calculateBookingTotal() {
            const roomSelect = document.getElementById('roomTypeSelect');
            const checkInInput = document.getElementById('checkInInput');
            const checkOutInput = document.getElementById('checkOutInput');
            const roomCountInput = document.getElementById('roomCountInput');

            let pricePerNight = 1850000;
            if (roomSelect && roomSelect.selectedOptions.length > 0) {
                const selectedOpt = roomSelect.selectedOptions[0];
                const rawPrice = parseFloat(selectedOpt.getAttribute('data-price'));
                if (!isNaN(rawPrice) && rawPrice > 0) {
                    pricePerNight = rawPrice;
                }
            }

            const checkInVal = checkInInput.value;
            const checkOutVal = checkOutInput.value;
            let roomCount = parseInt(roomCountInput.value, 10);
            if (isNaN(roomCount) || roomCount < 1) roomCount = 1;

            // Tính số đêm
            let nights = 1;
            if (checkInVal && checkOutVal) {
                const d1 = new Date(checkInVal);
                const d2 = new Date(checkOutVal);
                const diffTime = d2.getTime() - d1.getTime();
                nights = Math.round(diffTime / (1000 * 3600 * 24));
                if (nights < 1) nights = 1;
            }

            // Cập nhật text tóm tắt
            document.getElementById('summaryCheckInText').innerText = formatDateVn(checkInVal) + ' (14:00)';
            document.getElementById('summaryCheckOutText').innerText = formatDateVn(checkOutVal) + ' (12:00)';
            document.getElementById('summaryNightsText').innerText = nights + ' đêm';
            document.getElementById('summaryRoomCountText').innerText = roomCount + ' phòng';

            // Chiết tính
            const roomCost = pricePerNight * nights * roomCount;
            if (roomCount > 1) {
                document.getElementById('summaryRateDisplay').innerText = formatMoney(pricePerNight) + ' x ' + nights + ' đêm x ' + roomCount + ' phòng';
            } else {
                document.getElementById('summaryRateDisplay').innerText = formatMoney(pricePerNight) + ' x ' + nights + ' đêm';
            }

            document.getElementById('summaryTotalPriceDisplay').innerText = formatMoney(roomCost);
        }

        // Khởi chạy tính toán ban đầu khi tải trang
        document.addEventListener('DOMContentLoaded', function() {
            calculateBookingTotal();
        });
    </script>
</body>
</html>
