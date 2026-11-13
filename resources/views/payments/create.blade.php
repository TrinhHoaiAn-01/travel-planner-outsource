<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán đơn đặt phòng #{{ $booking->booking_code }} - Travel Planner</title>
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
            --success-light: #ECFDF5;
            --warning: #F59E0B;
            --danger: #EF4444;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08);
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
            display: flex;
            flex-direction: column;
        }

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
            height: 72px;
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
        }

        .logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14.5px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary);
            background: var(--primary-light);
        }

        .main-container {
            max-width: 1100px;
            margin: 36px auto;
            padding: 0 24px;
            flex: 1;
            width: 100%;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .breadcrumb a {
            color: var(--text-muted);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 32px;
            align-items: start;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-sm);
        }

        .section-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Payment Methods */
        .payment-methods-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin-bottom: 24px;
        }

        .payment-option-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.2s;
            background: #FFFFFF;
        }

        .payment-option-label:hover {
            border-color: #CBD5E1;
            background: #FAFAFA;
        }

        .payment-option-input {
            margin-right: 14px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .payment-option-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .payment-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            flex-shrink: 0;
        }

        .icon-vnpay { background: #E0F2FE; color: #0284C7; }
        .icon-momo { background: #FCE7F3; color: #DB2777; }
        .icon-zalopay { background: #E0F7FF; color: #008FE5; }
        .icon-bank { background: #DCFCE7; color: #16A34A; }
        .icon-card { background: #FEF3C7; color: #D97706; }
        .icon-cash { background: #F3E8FF; color: #9333EA; }

        .payment-method-name {
            font-weight: 700;
            color: var(--secondary);
            font-size: 15px;
            margin-bottom: 2px;
        }

        .payment-method-desc {
            font-size: 12.5px;
            color: var(--text-muted);
        }

        .payment-option-label.active {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        /* Order Summary */
        .summary-card {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            position: sticky;
            top: 96px;
        }

        .summary-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .booking-badge {
            display: inline-block;
            background: #EFF6FF;
            color: #1D4ED8;
            font-weight: 700;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 9999px;
            margin-bottom: 8px;
        }

        .summary-hotel-name {
            font-size: 16px;
            font-weight: 800;
            color: var(--secondary);
        }

        .summary-room-type {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 8px 0;
            color: var(--text-main);
        }

        .info-row.total-row {
            border-top: 2px dashed var(--border-color);
            margin-top: 16px;
            padding-top: 16px;
            font-size: 18px;
            font-weight: 800;
            color: var(--secondary);
        }

        .total-price-text {
            color: #059669;
            font-size: 22px;
        }

        .btn-submit-pay {
            width: 100%;
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            border: none;
            padding: 15px 24px;
            border-radius: var(--radius-md);
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        }

        .btn-submit-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
        }

        .btn-back {
            display: block;
            text-align: center;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            margin-top: 14px;
        }

        .btn-back:hover {
            color: var(--secondary);
        }

        .security-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #F1F5F9;
            padding: 10px 14px;
            border-radius: var(--radius-md);
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 20px;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 102, 255, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(0, 102, 255, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(0, 102, 255, 0); }
        }

        .pulse-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #0066FF;
            animation: pulse-dot 1.8s infinite;
            display: inline-block;
            flex-shrink: 0;
        }

        @media (max-width: 850px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header>
        <div class="header-container">
            <a href="{{ route('home') }}" class="logo">
                <div class="logo-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                </div>
                <span>TRAVEL PLANNER</span>
            </a>

            <ul class="nav-links">
                <li><a href="{{ route('home') }}" class="nav-link">Trang chủ</a></li>
                <li><a href="{{ route('destinations.index') }}" class="nav-link">Địa điểm</a></li>
                <li><a href="{{ route('trips.index') }}" class="nav-link">Chuyến đi</a></li>
                <li><a href="{{ route('bookings.index') }}" class="nav-link active">Booking</a></li>
            </ul>

            <div class="user-info" style="font-weight: 700; font-size: 14px;">
                {{ Auth::user()->name }}
            </div>
        </div>
    </header>

    <main class="main-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="{{ route('bookings.index') }}">Danh sách Booking</a>
            <span>/</span>
            <a href="{{ route('bookings.show', $booking->id) }}">{{ $booking->booking_code }}</a>
            <span>/</span>
            <span style="color: var(--secondary); font-weight: 600;">Thanh toán</span>
        </div>

        @if(session('warning'))
            <div style="background: #FFFBEB; border: 1.5px solid #FDE68A; color: #92400E; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 24px; font-size: 13.5px; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">⚠️</span>
                <div>
                    <strong>Thông báo cổng thanh toán:</strong> {{ session('warning') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div style="background: #FEF2F2; border: 1.5px solid #FECACA; color: #B91C1C; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 24px; font-size: 13.5px; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">❌</span>
                <div>
                    <strong>Lỗi giao dịch:</strong> {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; padding: 14px 20px; border-radius: var(--radius-md); margin-bottom: 24px;">
                <strong>Đã xảy ra lỗi:</strong>
                <ul style="margin-left: 20px; margin-top: 4px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="checkout-grid">
            <!-- Cột trái: Chọn phương thức thanh toán -->
            <div class="card">
                <h1 class="section-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                    Chọn Phương Thức Thanh Toán
                </h1>

                <form action="{{ route('payments.store', $booking->id) }}" method="POST" id="paymentForm" onsubmit="return handlePaymentSubmit(this);">
                    @csrf
                    <!-- Ràng buộc số tiền khớp chính xác với database theo quy tắc chống gian lận BR-21 -->
                    <input type="hidden" name="amount" value="{{ $booking->total_price }}">

                    <div class="payment-methods-grid">
                        <!-- Chuyển khoản ngân hàng (VietQR) - Mặc định -->
                        <label class="payment-option-label active" onclick="selectPayment('bank_transfer', this)">
                            <div class="payment-option-info">
                                <input type="radio" name="payment_method" value="bank_transfer" class="payment-option-input" checked>
                                <div class="payment-icon-box icon-bank">CK</div>
                                <div>
                                    <div class="payment-method-name">Chuyển Khoản Ngân Hàng (VietQR Chuẩn NAPAS 24/7)</div>
                                    <div class="payment-method-desc">Quét mã QR tự động qua ứng dụng ngân hàng, xác nhận tức thì</div>
                                </div>
                            </div>
                        </label>

                        <!-- Thanh toán khi nhận phòng -->
                        <label class="payment-option-label" onclick="selectPayment('cash', this)">
                            <div class="payment-option-info">
                                <input type="radio" name="payment_method" value="cash" class="payment-option-input">
                                <div class="payment-icon-box icon-cash">TM</div>
                                <div>
                                    <div class="payment-method-name">Thanh Toán Tiền Mặt Khi Nhận Phòng</div>
                                    <div class="payment-method-desc">Giữ chỗ trước, thanh toán trực tiếp tại quầy lễ tân khi làm thủ tục check-in</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Sandbox / Instruction Panels -->
                    <div id="sandbox-container" style="margin-bottom: 24px;">
                        <!-- 1. VietQR Real Dynamic Panel (Mặc định hiển thị) -->
                        <div id="panel-bank_transfer" class="sandbox-panel" style="background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: var(--radius-md); padding: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #16A34A; color: white; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 4px;">VIETQR CHUẨN NAPAS 24/7</span>
                                    <strong style="color: #15803D; font-size: 14px;">Quét Mã Chuyển Khoản Ngân Hàng Tự Động</strong>
                                </div>
                                <span style="font-size: 12px; color: #16A34A; font-weight: 600;">Tự động điền tiền & nội dung</span>
                            </div>
                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                <div style="background: white; padding: 10px; border-radius: 8px; border: 1px solid #DCFCE7; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                    <!-- Gọi trực tiếp VietQR API tạo ảnh QR thanh toán khớp với tài khoản SePay Test Mode -->
                                    <img src="https://img.vietqr.io/image/mbbank-0000000002-compact2.png?amount={{ (int)$booking->total_price }}&addInfo=TPB%20{{ $booking->booking_code }}&accountName=TRAVEL%20PLANNER%20TEST" 
                                         alt="VietQR Chuyển Khoản" 
                                         style="width: 150px; height: 150px; object-fit: contain; border-radius: 4px; display: block; margin: 0 auto;">
                                    <div style="font-size: 10.5px; color: #16A34A; margin-top: 6px; font-weight: 700;">QUÉT QUA MỌI APP NGÂN HÀNG</div>
                                </div>
                                <div style="flex: 1; min-width: 240px; font-size: 13px; color: #334155;">
                                    <div style="background: white; border: 1px solid #DCFCE7; padding: 12px 16px; border-radius: 8px; line-height: 1.7; font-size: 12.5px;">
                                        <div>🏦 Ngân hàng: <strong>MBBank (Ngân Hàng Quân Đội)</strong></div>
                                        <div style="display: flex; align-items: center; justify-content: space-between;">
                                            <span>💳 Số tài khoản: <strong style="color: #15803D; font-size: 13.5px;">0000000002</strong></span>
                                            <button type="button" onclick="copyPaymentText('0000000002', this)" style="border: 1px solid #BBF7D0; background: #F0FDF4; color: #16A34A; padding: 2px 8px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: 600;">Sao chép</button>
                                        </div>
                                        <div>👤 Tên chủ TK: <strong>TRAVEL PLANNER TEST</strong></div>
                                        <div>💵 Số tiền: <strong style="color: #059669; font-size: 14px;">{{ number_format($booking->total_price, 0, ',', '.') }}đ</strong></div>
                                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 2px;">
                                            <span>📝 Cú pháp: <strong style="color: #2563EB; font-family: monospace; font-size: 13px;">TPB {{ $booking->booking_code }}</strong></span>
                                            <button type="button" onclick="copyPaymentText('TPB {{ $booking->booking_code }}', this)" style="border: 1px solid #BBF7D0; background: #F0FDF4; color: #16A34A; padding: 2px 8px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: 600;">Sao chép</button>
                                        </div>
                                    </div>
                                    <div style="margin-top: 8px; font-size: 11.5px; color: #15803D;">
                                        ✨ <em>Mở app ngân hàng quét mã QR để thanh toán tự động, hoặc bấm nút bên dưới để mở màn hình SePay chuyên dụng với đồng hồ đếm ngược.</em>
                                    </div>

                                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed #BBF7D0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <button type="button" onclick="submitSePayTransfer()" style="background: #16A34A; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 700; font-size: 12.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(22,163,74,0.2);">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                            🚀 Mở Màn Hình Chuyển Khoản VietQR Tự Động (SePay Banking)
                                        </button>
                                        <span style="font-size: 11px; color: #15803D;">
                                            (Trang đếm ngược thời gian thực 24/7)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Tiền Mặt Panel (Hiển thị khi chọn Tiền mặt) -->
                        <div id="panel-cash" class="sandbox-panel" style="display: none; background: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: var(--radius-md); padding: 20px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                <span style="background: #9333EA; color: white; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 4px;">TIỀN MẶT KHI NHẬN PHÒNG</span>
                                <strong style="color: #7E22CE; font-size: 14px;">Thanh Toán Trực Tiếp Tại Quầy Lễ Tân Khách Sạn</strong>
                            </div>
                            <div style="background: white; border: 1px solid #E9D5FF; padding: 14px 18px; border-radius: 8px; line-height: 1.7; font-size: 13px; color: #4B5563;">
                                <p style="margin-bottom: 8px;">
                                    Quý khách vui lòng chuẩn bị số tiền <strong>{{ number_format($booking->total_price, 0, ',', '.') }}đ</strong> để thanh toán trực tiếp tại quầy lễ tân khi làm thủ tục check-in.
                                </p>
                                <div style="display: flex; gap: 12px; margin-top: 10px; font-size: 12.5px; flex-wrap: wrap;">
                                    <div style="flex: 1; min-width: 200px; background: #FDF4FF; padding: 10px 12px; border-radius: 6px; border: 1px dashed #D8B4FE;">
                                        <strong style="color: #9333EA;">📌 Quy trình giữ chỗ:</strong>
                                        <div style="color: #6B7280; margin-top: 4px;">Đơn đặt phòng sẽ được giữ ở trạng thái <em>Chờ xác nhận (Pending)</em> ngay khi quý khách bấm xác nhận.</div>
                                    </div>
                                    <div style="flex: 1; min-width: 200px; background: #FDF4FF; padding: 10px 12px; border-radius: 6px; border: 1px dashed #D8B4FE;">
                                        <strong style="color: #9333EA;">🏨 Khi đến nơi:</strong>
                                        <div style="color: #6B7280; margin-top: 4px;">Xuất trình mã đơn <strong>#{{ $booking->booking_code }}</strong> cho lễ tân để nhận phòng và nhận biên lai thanh toán.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><div style="margin-top: 18px;">
                        <label style="display: block; font-weight: 700; font-size: 13.5px; color: var(--secondary); margin-bottom: 6px;">
                            Ghi chú thanh toán (tùy chọn)
                        </label>
                        <textarea name="note" rows="2" placeholder="Ví dụ: Giao dịch thanh toán đặt phòng qua app..." style="width: 100%; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); padding: 10px 14px; font-family: inherit; font-size: 14px; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" class="btn-submit-pay" id="btnSubmitPay">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span id="btnSubmitText">Xác Nhận Đã Chuyển Khoản VietQR</span>
                    </button>

                    <a href="{{ route('bookings.show', $booking->id) }}" class="btn-back">
                        &larr; Quay lại thông tin đơn đặt phòng
                    </a>
                </form>

                <div class="security-badge">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span>Giao dịch được bảo mật tuyệt đối với mã hóa SSL 256-bit và cơ chế kiểm duyệt BR-21.</span>
                </div>
            </div>

            <!-- Cột phải: Tóm tắt chi phí đơn đặt phòng -->
            <div class="summary-card">
                <div class="summary-header">
                    <span class="booking-badge">Đơn đặt #{{ $booking->booking_code }}</span>
                    <h2 class="summary-hotel-name">{{ $booking->roomType->destination->name ?? 'Điểm lưu trú' }}</h2>
                    <p class="summary-room-type">{{ $booking->roomType->name ?? 'Hạng phòng' }} &bull; {{ $booking->room_count }} phòng</p>
                </div>

                <div class="info-row">
                    <span>Ngày nhận phòng:</span>
                    <strong>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') }}</strong>
                </div>

                <div class="info-row">
                    <span>Ngày trả phòng:</span>
                    <strong>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') }}</strong>
                </div>

                @php
                    $checkIn = \Carbon\Carbon::parse($booking->check_in_date);
                    $checkOut = \Carbon\Carbon::parse($booking->check_out_date);
                    $nights = max(1, $checkIn->diffInDays($checkOut));
                @endphp

                <div class="info-row">
                    <span>Thời gian lưu trú:</span>
                    <strong>{{ $nights }} đêm</strong>
                </div>

                <div class="info-row">
                    <span>Giá mỗi đêm:</span>
                    <span>{{ number_format($booking->roomType->price_per_night ?? 0, 0, ',', '.') }}đ</span>
                </div>

                <div class="info-row">
                    <span>Phí dịch vụ & VAT:</span>
                    <span style="color: var(--success); font-weight: 600;">Miễn phí</span>
                </div>

                <div class="info-row total-row">
                    <span>Tổng thanh toán:</span>
                    <span class="total-price-text">{{ number_format($booking->total_price, 0, ',', '.') }}đ</span>
                </div>
            </div>
        </div>
    </main>

    <footer style="background: white; border-top: 1px solid var(--border-color); padding: 20px; text-align: center; font-size: 13px; color: var(--text-muted); margin-top: 40px;">
        © 2026 Travel Planner - Nhóm B (Nguyễn Văn Thắng - Booking & Payment Module).
    </footer>

    <script>
        function selectPayment(method, element) {
            document.querySelectorAll('.payment-option-label').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            element.querySelector('input[type="radio"]').checked = true;

            // Ẩn tất cả các panel
            document.querySelectorAll('.sandbox-panel').forEach(panel => panel.style.display = 'none');

            // Hiển thị panel tương ứng với phương thức được chọn
            const targetPanel = document.getElementById('panel-' + method);
            if (targetPanel) {
                targetPanel.style.display = 'block';
            }

            // Cập nhật text nút submit cho phù hợp
            const submitText = document.getElementById('btnSubmitText');
            if (submitText) {
                if (method === 'cash') {
                    submitText.innerText = 'Xác Nhận Giữ Chỗ (Thanh Toán Khi Nhận Phòng)';
                } else {
                    submitText.innerText = 'Xác Nhận Đã Chuyển Khoản VietQR';
                }
            }
        }

        function copyPaymentText(text, btnElement) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btnElement.innerText;
                btnElement.innerText = '✓ Đã chép';
                btnElement.style.color = '#10B981';
                setTimeout(() => {
                    btnElement.innerText = originalText;
                    btnElement.style.color = '';
                }, 2000);
            });
        }

        let isSubmitting = false;

        function handlePaymentSubmit(form) {
            if (isSubmitting) {
                return false;
            }
            isSubmitting = true;

            const btn = form.querySelector('.btn-submit-pay');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span>Đang xử lý đơn đặt phòng...</span>';
                btn.style.opacity = '0.7';
                btn.style.cursor = 'not-allowed';
            }
            return true;
        }

        function submitSePayTransfer() {
            if (isSubmitting) return;
            isSubmitting = true;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('payments.sepay.process', $booking->id) }}";

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);

            document.body.appendChild(form);
            form.submit();
        }

        // ==========================================
        // REALTIME PAYMENT POLLING & NOTIFICATION
        // ==========================================
        let isPaid = false;
        let pollingInterval = null;

        function startPaymentPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            // Tự động kiểm tra trạng thái mỗi 2.5 giây
            pollingInterval = setInterval(checkPaymentStatusSilently, 2500);
        }

        function checkPaymentStatusSilently() {
            if (isPaid) return;

            fetch("{{ route('payments.status', $booking->id) }}")
                .then(res => res.json())
                .then(data => {
                    if (data.is_paid) {
                        onPaymentSuccess(data);
                    }
                })
                .catch(err => {
                    // Im lặng bỏ qua lỗi mạng khi polling ngầm
                });
        }

        // Khi xác nhận đã nhận được tiền: Tự động chuyển hướng sang trang Hóa Đơn
        function onPaymentSuccess(data) {
            isPaid = true;
            if (pollingInterval) clearInterval(pollingInterval);

            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            }
        }

        // Khởi động lắng nghe biến động ngầm ngay khi tải trang
        document.addEventListener('DOMContentLoaded', function () {
            startPaymentPolling();
        });
    </script>
</body>
</html>
