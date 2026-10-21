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
                        <!-- VNPay -->
                        <label class="payment-option-label active" onclick="selectPayment('vnpay', this)">
                            <div class="payment-option-info">
                                <input type="radio" name="payment_method" value="vnpay" class="payment-option-input" checked>
                                <div class="payment-icon-box icon-vnpay">VN</div>
                                <div>
                                    <div class="payment-method-name">Cổng thanh toán VNPay QR</div>
                                    <div class="payment-method-desc">Quét mã VNPAY-QR hoặc qua ứng dụng ngân hàng nội địa</div>
                                </div>
                            </div>
                        </label>

                        <!-- Ví MoMo -->
                        <label class="payment-option-label" onclick="selectPayment('momo', this)">
                            <div class="payment-option-info">
                                <input type="radio" name="payment_method" value="momo" class="payment-option-input">
                                <div class="payment-icon-box icon-momo">MM</div>
                                <div>
                                    <div class="payment-method-name">Ví Điện Tử MoMo</div>
                                    <div class="payment-method-desc">Thanh toán tức thì qua ví điện tử MoMo siêu tiện lợi</div>
                                </div>
                            </div>
                        </label>

                        <!-- Chuyển khoản ngân hàng -->
                        <label class="payment-option-label" onclick="selectPayment('bank_transfer', this)">
                            <div class="payment-option-info">
                                <input type="radio" name="payment_method" value="bank_transfer" class="payment-option-input">
                                <div class="payment-icon-box icon-bank">CK</div>
                                <div>
                                    <div class="payment-method-name">Chuyển Khoản Ngân Hàng (VietQR)</div>
                                    <div class="payment-method-desc">Chuyển khoản trực tiếp tới tài khoản ngân hàng của hệ thống</div>
                                </div>
                            </div>
                        </label>

                        <!-- Thẻ quốc tế Visa / MasterCard -->
                        <label class="payment-option-label" onclick="selectPayment('credit_card', this)">
                            <div class="payment-option-info">
                                <input type="radio" name="payment_method" value="credit_card" class="payment-option-input">
                                <div class="payment-icon-box icon-card">CC</div>
                                <div>
                                    <div class="payment-method-name">Thẻ Tín Dụng Quốc Tế (Visa / Mastercard)</div>
                                    <div class="payment-method-desc">Hỗ trợ thanh toán toàn cầu với thẻ tín dụng hoặc ghi nợ</div>
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
                                    <div class="payment-method-desc">Thanh toán trực tiếp tại quầy lễ tân khi làm thủ tục check-in</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Sandbox Developer Panels (Khu vực mô phỏng Cổng thanh toán cho Dev) -->
                    <div id="sandbox-container" style="margin-bottom: 24px;">
                        <!-- 1. VNPay Sandbox Panel -->
                        <div id="panel-vnpay" class="sandbox-panel" style="background: #F0F9FF; border: 1.5px solid #BAE6FD; border-radius: var(--radius-md); padding: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #0284C7; color: white; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 4px;">VNPAY SANDBOX</span>
                                    <strong style="color: #0369A1; font-size: 14px;">Mô phỏng Cổng Thanh Toán VNPay-QR</strong>
                                </div>
                                <span style="font-size: 12px; color: #0284C7; font-weight: 600;">Môi trường thử nghiệm</span>
                            </div>
                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                <div style="background: white; padding: 10px; border-radius: 8px; border: 1.5px solid #BAE6FD; text-align: center;">
                                    <img src="https://img.vietqr.io/image/vietinbank-108873456789-qr_only.png?amount={{ (int)$booking->total_price }}&addInfo={{ $booking->booking_code }}&accountName=CONG%20TY%20TRAVEL%20PLANNER" 
                                         alt="Mã VNPay-QR quét được thật" 
                                         style="width: 135px; height: 135px; display: block; border-radius: 6px; margin: 0 auto;">
                                    <div style="font-size: 11px; color: #0284C7; margin-top: 6px; font-weight: 700;">QUÉT QUA APP VNPAY / NGÂN HÀNG</div>
                                </div>
                                <div style="flex: 1; min-width: 200px; font-size: 13px; color: #334155;">
                                    <p style="margin-bottom: 6px;">🔹 Hỗ trợ thanh toán qua 40+ ứng dụng ngân hàng: <strong>Vietcombank, BIDV, VietinBank, Agribank, MB, Techcombank</strong>...</p>
                                    <p style="margin-bottom: 6px;">🔹 Bấm nút <strong>"Xác Nhận & Hoàn Tất Thanh Toán"</strong> bên dưới để hoàn tất giao dịch tự động.</p>
                                    <div style="background: white; border: 1px solid #BAE6FD; padding: 8px 12px; border-radius: 6px; font-size: 12px; color: #0284C7;">
                                        Mã kiểm thử: <strong>VNPAY_TEST_MERCHANT_01</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. MoMo Sandbox Panel -->
                        <div id="panel-momo" class="sandbox-panel" style="display: none; background: #FDF2F8; border: 1.5px solid #FBCFE8; border-radius: var(--radius-md); padding: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #A50064; color: white; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 4px;">MOMO DEVELOPER</span>
                                    <strong style="color: #A50064; font-size: 14px;">Cổng Thanh Toán MoMo Sandbox</strong>
                                </div>
                                <span style="font-size: 12px; color: #A50064; font-weight: 600;">developers.momo.vn</span>
                            </div>
                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                <div style="background: white; padding: 10px; border-radius: 8px; border: 1.5px solid #FCE7F3; text-align: center;">
                                    <img src="https://img.vietqr.io/image/vietinbank-108873456789-qr_only.png?amount={{ (int)$booking->total_price }}&addInfo={{ $booking->booking_code }}&accountName=CONG%20TY%20TRAVEL%20PLANNER" 
                                         alt="Mã MoMo QR quét được thật" 
                                         style="width: 135px; height: 135px; display: block; border-radius: 6px; margin: 0 auto;">
                                    <div style="font-size: 11px; color: #A50064; margin-top: 6px; font-weight: 700;">QUÉT BẰNG APP MOMO THẬT</div>
                                </div>
                                <div style="flex: 1; min-width: 200px; font-size: 13px; color: #334155;">
                                    <div style="margin-bottom: 8px;">
                                        <strong>Thông tin tài khoản ví MoMo Test (Sandbox):</strong>
                                    </div>
                                    <div style="background: white; border: 1px solid #FCE7F3; padding: 10px 14px; border-radius: 8px; line-height: 1.6; font-size: 12.5px;">
                                        <div>📱 Số điện thoại test: <strong style="color: #A50064;">0901 234 567</strong></div>
                                        <div>💰 Số dư ví thử nghiệm: <strong>50.000.000đ</strong></div>
                                        <div>🔑 Mã xác thực OTP test: <strong>123456</strong></div>
                                        <div>⚙️ Partner Code: <strong>MOMOBKUN20180529</strong></div>
                                    </div>
                                    <div style="margin-top: 8px; font-size: 12px; color: #64748B;">
                                        💡 Nhóm hỗ trợ cả 2 chế độ: Mở cổng MoMo API Live chính thức hoặc Giả lập Sandbox duyệt tức thì.
                                    </div>

                                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed #FBCFE8; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <button type="button" onclick="submitMoMoLive()" style="background: #A50064; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 700; font-size: 12.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(165,0,100,0.2);">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                            🚀 Mở Cổng MoMo Sandbox Trực Tiếp (API V2)
                                        </button>
                                        <span style="font-size: 11px; color: #9D174D;">
                                            (Chuyển sang test-payment.momo.vn hoặc dùng nút xanh bên dưới để test nội bộ)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. VietQR Real Dynamic Panel -->
                        <div id="panel-bank_transfer" class="sandbox-panel" style="display: none; background: #F0FDF4; border: 1.5px solid #BBF7D0; border-radius: var(--radius-md); padding: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #16A34A; color: white; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 4px;">VIETQR CHUẨN NAPAS 24/7</span>
                                    <strong style="color: #15803D; font-size: 14px;">Quét Mã Chuyển Khoản Ngân Hàng</strong>
                                </div>
                                <span style="font-size: 12px; color: #16A34A; font-weight: 600;">Tự động điền tiền & nội dung</span>
                            </div>
                            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                                <div style="background: white; padding: 8px; border-radius: 8px; border: 1px solid #DCFCE7; text-align: center;">
                                    <!-- Gọi trực tiếp VietQR API tạo ảnh QR thanh toán thật 100% -->
                                    <img src="https://img.vietqr.io/image/vietinbank-108873456789-compact2.png?amount={{ (int)$booking->total_price }}&addInfo={{ $booking->booking_code }}&accountName=CONG%20TY%20TRAVEL%20PLANNER" 
                                         alt="VietQR Chuyển Khoản" 
                                         style="width: 145px; height: 145px; object-fit: contain; border-radius: 4px; display: block;">
                                    <div style="font-size: 10.5px; color: #16A34A; margin-top: 4px; font-weight: 700;">QUÉT QUA MỌI APP NGÂN HÀNG</div>
                                </div>
                                <div style="flex: 1; min-width: 220px; font-size: 13px; color: #334155;">
                                    <div style="background: white; border: 1px solid #DCFCE7; padding: 12px 16px; border-radius: 8px; line-height: 1.7; font-size: 12.5px;">
                                        <div>🏦 Ngân hàng: <strong>VietinBank (ICB)</strong></div>
                                        <div>💳 Số tài khoản: <strong style="color: #15803D; font-size: 13.5px;">108873456789</strong></div>
                                        <div>👤 Tên chủ TK: <strong>CONG TY TRAVEL PLANNER</strong></div>
                                        <div>💵 Số tiền: <strong style="color: #059669; font-size: 14px;">{{ number_format($booking->total_price, 0, ',', '.') }}đ</strong></div>
                                        <div>📝 Cú pháp chuyển khoản: <strong style="color: #2563EB; font-family: monospace;">{{ $booking->booking_code }}</strong></div>
                                    </div>
                                    <div style="margin-top: 8px; font-size: 11.5px; color: #15803D;">
                                        ✨ <em>Bạn có thể mở app ngân hàng thật trên điện thoại quét mã QR bên cạnh để xem chức năng tự động nhận diện đúng số tiền và mã đơn!</em>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Thẻ Quốc Tế Sandbox Panel -->
                        <div id="panel-credit_card" class="sandbox-panel" style="display: none; background: #FFFBEB; border: 1.5px solid #FDE68A; border-radius: var(--radius-md); padding: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="background: #D97706; color: white; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 4px;">VISA / MASTERCARD SANDBOX</span>
                                    <strong style="color: #B45309; font-size: 14px;">Cổng Thẻ Quốc Tế Thử Nghiệm</strong>
                                </div>
                                <button type="button" onclick="fillTestCard()" style="background: white; border: 1px solid #D97706; color: #D97706; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 6px; cursor: pointer;">
                                    ⚡ Điền Thẻ Test Sandbox
                                </button>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                                <div style="grid-column: span 2;">
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #78350F; margin-bottom: 4px;">Số Thẻ Tín Dụng Test:</label>
                                    <input type="text" id="demo_card_number" value="4242 &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 4242" readonly style="width: 100%; padding: 8px 12px; border: 1px solid #FCD34D; border-radius: 6px; font-family: monospace; font-weight: 700; background: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #78350F; margin-bottom: 4px;">Ngày Hết Hạn:</label>
                                    <input type="text" id="demo_card_exp" value="12/28" readonly style="width: 100%; padding: 8px 12px; border: 1px solid #FCD34D; border-radius: 6px; font-family: monospace; background: white;">
                                </div>
                                <div>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #78350F; margin-bottom: 4px;">Mã CVV/CVC:</label>
                                    <input type="text" id="demo_card_cvv" value="888" readonly style="width: 100%; padding: 8px 12px; border: 1px solid #FCD34D; border-radius: 6px; font-family: monospace; background: white;">
                                </div>
                            </div>
                        </div>

                        <!-- 5. Tiền Mặt Panel -->
                        <div id="panel-cash" class="sandbox-panel" style="display: none; background: #FAF5FF; border: 1.5px solid #E9D5FF; border-radius: var(--radius-md); padding: 18px;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <span style="background: #9333EA; color: white; font-weight: 800; font-size: 11px; padding: 3px 8px; border-radius: 4px;">TIỀN MẶT</span>
                                <strong style="color: #7E22CE; font-size: 14px;">Thanh Toán Trực Tiếp Tại Khách Sạn</strong>
                            </div>
                            <p style="font-size: 13px; color: #4B5563; line-height: 1.6;">
                                Quý khách vui lòng thanh toán trực tiếp số tiền <strong>{{ number_format($booking->total_price, 0, ',', '.') }}đ</strong> bằng tiền mặt tại quầy lễ tân khi làm thủ tục nhận phòng (Check-in). Đơn đặt phòng sẽ được giữ chỗ ở trạng thái <em>Chờ xác nhận (Pending)</em>.
                            </p>
                        </div>
                    </div>

                    <!-- Realtime QR Payment Detection Box (Tự động nhận diện thanh toán từ App) -->
                    <div id="realtime-detection-box" style="margin-top: 20px; margin-bottom: 22px; padding: 18px 20px; background: #F8FAFC; border: 2px dashed #CBD5E1; border-radius: var(--radius-md); transition: all 0.3s;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div id="status-pulse-dot" class="pulse-indicator"></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 14px; color: var(--secondary);" id="polling-status-text">
                                        Đang chờ bạn quét mã & chuyển tiền từ App MoMo / Ngân Hàng...
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted);" id="polling-sub-text">
                                        Website tự động lắng nghe biến động số dư ngầm (mỗi 2.5 giây)
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" onclick="manualCheckPayment()" id="btn-manual-check" style="background: #0066FF; color: white; border: none; padding: 9px 18px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(0,102,255,0.2);">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                                Tôi đã chuyển tiền - Kiểm tra ngay
                            </button>
                        </div>

                        <!-- Hộp thông báo phản hồi khi bấm nút kiểm tra -->
                        <div id="check-feedback-alert" style="display: none; padding: 12px 16px; border-radius: 6px; font-size: 13px; line-height: 1.5; margin-top: 14px;"></div>

                        <!-- Khu vực kích hoạt Webhook giả lập cho Developer & Thầy cô khi bảo vệ đồ án -->
                        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <span style="font-size: 11.5px; color: #64748B;">
                                🛠️ <strong>Dành cho Developer:</strong> Sau khi quét mã bằng App, bạn có thể bấm nút bên cạnh để giả lập tín hiệu Webhook tiền đã vào tài khoản.
                            </span>
                            <button type="button" onclick="simulateMoneyReceived()" id="btn-simulate-receive" style="background: #10B981; color: white; border: none; padding: 7px 15px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                📡 Giả lập đã nhận tiền từ App (Ting Ting)
                            </button>
                        </div>
                    </div>

                    <div style="margin-top: 18px;">
                        <label style="display: block; font-weight: 700; font-size: 13.5px; color: var(--secondary); margin-bottom: 6px;">
                            Ghi chú thanh toán (tùy chọn)
                        </label>
                        <textarea name="note" rows="2" placeholder="Ví dụ: Giao dịch thanh toán đặt phòng qua app..." style="width: 100%; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); padding: 10px 14px; font-family: inherit; font-size: 14px; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" class="btn-submit-pay">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Xác Nhận & Hoàn Tất Thanh Toán
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

            // Ẩn tất cả các panel sandbox
            document.querySelectorAll('.sandbox-panel').forEach(panel => panel.style.display = 'none');

            // Hiển thị panel tương ứng với phương thức được chọn
            const targetPanel = document.getElementById('panel-' + method);
            if (targetPanel) {
                targetPanel.style.display = 'block';
            }
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
                btn.innerHTML = '<span>Đang xác nhận thanh toán...</span>';
                btn.style.opacity = '0.7';
                btn.style.cursor = 'not-allowed';
            }
            return true;
        }

        function fillTestCard() {
            document.getElementById('demo_card_number').value = '4242 4242 4242 4242';
            document.getElementById('demo_card_exp').value = '12/28';
            document.getElementById('demo_card_cvv').value = '888';
            alert('Đã nạp thẻ thử nghiệm Stripe/Visa Sandbox: 4242 4242 4242 4242');
        }

        function submitMoMoLive() {
            if (isSubmitting) return;
            isSubmitting = true;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('payments.momo.process', $booking->id) }}";
            
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

        // Người dùng chủ động bấm: "Tôi đã chuyển tiền - Kiểm tra ngay"
        function manualCheckPayment() {
            const btn = document.getElementById('btn-manual-check');
            const alertBox = document.getElementById('check-feedback-alert');
            
            btn.disabled = true;
            btn.innerHTML = '<span>⏳ Đang đối soát với ngân hàng...</span>';
            alertBox.style.display = 'none';

            fetch("{{ route('payments.status', $booking->id) }}")
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg> Tôi đã chuyển tiền - Kiểm tra ngay`;

                    if (data.is_paid) {
                        onPaymentSuccess(data);
                    } else {
                        // HIỆN THÔNG BÁO RÕ RÀNG NẾU CHƯA NHẬN ĐƯỢC TIỀN
                        alertBox.style.display = 'block';
                        alertBox.style.background = '#FFFBEB';
                        alertBox.style.border = '1.5px solid #FDE68A';
                        alertBox.style.color = '#92400E';
                        alertBox.innerHTML = `
                            <div style="display: flex; gap: 10px; align-items: flex-start;">
                                <span style="font-size: 18px;">⚠️</span>
                                <div>
                                    <strong style="color: #B45309; font-size: 13.5px;">Chưa nhận được khoản tiền:</strong> Hệ thống vẫn chưa ghi nhận biến động số dư cho đơn #<strong>{{ $booking->booking_code }}</strong>.
                                    <div style="margin-top: 5px; font-size: 12px; color: #78350F; line-height: 1.5;">
                                        👉 <strong>Lý do có thể:</strong> Bạn chưa bấm chuyển tiền trên App, nhập sai số tiền hoặc ngân hàng đang xử lý chậm.<br>
                                        💡 Vui lòng hoàn tất chuyển khoản trên <strong>App MoMo / Ngân Hàng</strong> và đợi 10-20 giây để hệ thống tự động xác nhận.
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = 'Tôi đã chuyển tiền - Kiểm tra ngay';
                    alert('Lỗi kiểm tra trạng thái giao dịch: ' + err.message);
                });
        }

        // Dành cho Dev / Demo: Giả lập tín hiệu Webhook từ App báo đã nhận tiền
        function simulateMoneyReceived() {
            const simBtn = document.getElementById('btn-simulate-receive');
            simBtn.disabled = true;
            simBtn.innerHTML = '<span>📡 Đang bắn Webhook...</span>';

            const activeRadio = document.querySelector('input[name="payment_method"]:checked');
            const method = activeRadio ? activeRadio.value : 'momo';

            fetch("{{ route('payments.simulate-receive', $booking->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ payment_method: method })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success || data.is_paid) {
                    onPaymentSuccess(data);
                }
            })
            .catch(err => {
                simBtn.disabled = false;
                simBtn.innerHTML = '📡 Giả lập đã nhận tiền từ App (Ting Ting)';
                alert('Lỗi kích hoạt webhook giả lập: ' + err.message);
            });
        }

        // Khi xác nhận đã nhận được tiền: Đổi màu, báo ting ting và tự động chuyển trang
        function onPaymentSuccess(data) {
            isPaid = true;
            if (pollingInterval) clearInterval(pollingInterval);

            const box = document.getElementById('realtime-detection-box');
            const pulseDot = document.getElementById('status-pulse-dot');
            const statusText = document.getElementById('polling-status-text');
            const subText = document.getElementById('polling-sub-text');
            const alertBox = document.getElementById('check-feedback-alert');

            pulseDot.style.background = '#10B981';
            pulseDot.style.animation = 'none';

            box.style.background = '#ECFDF5';
            box.style.borderColor = '#10B981';
            statusText.style.color = '#065F46';
            statusText.innerHTML = '🎉 TING TING! ĐÃ NHẬN THANH TOÁN THÀNH CÔNG TỪ APP!';
            subText.innerHTML = 'Hệ thống đã khớp đúng số tiền và mã đơn. Đang tự động chuyển tới Hóa Đơn trong giây lát...';

            alertBox.style.display = 'block';
            alertBox.style.background = '#D1FAE5';
            alertBox.style.border = '1.5px solid #6EE7B7';
            alertBox.style.color = '#065F46';
            alertBox.innerHTML = `<strong>✅ Giao dịch hoàn tất:</strong> Đơn đặt phòng #{{ $booking->booking_code }} đã được chuyển sang trạng thái <strong>ĐÃ XÁC NHẬN (CONFIRMED)</strong>.`;

            // Ẩn nút kiểm tra
            const checkBtn = document.getElementById('btn-manual-check');
            if (checkBtn) checkBtn.style.display = 'none';
            const simBtn = document.getElementById('btn-simulate-receive');
            if (simBtn) simBtn.style.display = 'none';

            // Tự động chuyển hướng sang trang Hóa Đơn sau 1.2 giây
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 1200);
        }

        // Khởi động lắng nghe biến động ngầm ngay khi tải trang
        document.addEventListener('DOMContentLoaded', function () {
            startPaymentPolling();
        });
    </script>
</body>
</html>
