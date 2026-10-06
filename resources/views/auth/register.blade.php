<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - Travel Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #E6E9EE;
            color: #111827;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }

        .auth-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 980px;
            width: 100%;
            gap: 56px;
        }

        /* Khối giới thiệu bên trái */
        .intro-section {
            flex: 1;
            max-width: 440px;
        }

        .intro-title {
            font-size: 38px;
            line-height: 1.18;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.02em;
        }

        .intro-title .highlight {
            color: #0066FF;
            display: block;
        }

        .intro-desc {
            font-size: 15.5px;
            line-height: 1.55;
            color: #4B5563;
            margin-top: 14px;
            margin-bottom: 34px;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .feature-icon-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }

        .feature-icon-circle svg {
            width: 22px;
            height: 22px;
            fill: none;
            stroke: #0066FF;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .feature-text {
            font-size: 15px;
            font-weight: 500;
            color: #1F2937;
        }

        /* Thẻ biểu mẫu đăng ký bên phải */
        .register-card {
            flex: 1;
            max-width: 450px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.04);
            padding: 42px 38px;
        }

        .card-title {
            text-align: center;
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 26px;
            letter-spacing: -0.01em;
        }

        .alert-box {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            line-height: 1.45;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: #FEF2F2;
            color: #DC2626;
            border: 1px solid #FCA5A5;
        }

        .alert-success {
            background-color: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 7px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-control {
            width: 100%;
            height: 46px;
            padding: 0 14px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 14px;
            color: #111827;
            background-color: #FFFFFF;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control::placeholder {
            color: #9CA3AF;
        }

        .form-control:focus {
            outline: none;
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.15);
        }

        .form-control.is-invalid {
            border-color: #EF4444;
            background-color: #FEF2F2;
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6B7280;
            transition: color 0.15s ease;
        }

        .toggle-password-btn:hover {
            color: #111827;
        }

        .toggle-password-btn svg {
            width: 19px;
            height: 19px;
        }

        .input-with-icon {
            padding-right: 42px;
        }

        .field-error {
            display: block;
            font-size: 12.5px;
            color: #DC2626;
            margin-top: 5px;
            font-weight: 500;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background-color: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 6px;
            transition: background-color 0.15s ease, transform 0.05s ease;
        }

        .btn-submit:hover {
            background-color: #0052CC;
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 22px 0;
            color: #9CA3AF;
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #E5E7EB;
        }

        .divider span {
            padding: 0 12px;
        }

        .card-footer-text {
            text-align: center;
            font-size: 14px;
            color: #374151;
        }

        .card-footer-text a {
            color: #0066FF;
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
        }

        .card-footer-text a:hover {
            text-decoration: underline;
        }

        /* Responsive trên thiết bị di động */
        @media (max-width: 820px) {
            .auth-container {
                flex-direction: column;
                gap: 36px;
            }

            .intro-section {
                text-align: center;
                max-width: 100%;
            }

            .intro-desc {
                margin-left: auto;
                margin-right: auto;
            }

            .feature-list {
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Cột thông tin giới thiệu bên trái -->
        <section class="intro-section" aria-label="Giới thiệu ứng dụng Travel Planner">
            <h1 class="intro-title">
                Lên kế hoạch
                <span class="highlight">cho hành trình của bạn</span>
            </h1>
            <p class="intro-desc">
                Khám phá điểm đến, quản lý lịch trình và lưu lại những khoảnh khắc đáng nhớ.
            </p>

            <div class="feature-list">
                <!-- Điểm đến -->
                <div class="feature-item">
                    <div class="feature-icon-circle">
                        <!-- Biểu tượng máy bay -->
                        <svg viewBox="0 0 24 24">
                            <path d="M22 2L11 13" />
                            <path d="M22 2L15 22L11 13L2 9L22 2Z" />
                        </svg>
                    </div>
                    <span class="feature-text">Tìm kiếm điểm đến dễ dàng</span>
                </div>

                <!-- Lập kế hoạch -->
                <div class="feature-item">
                    <div class="feature-icon-circle">
                        <!-- Biểu tượng lịch -->
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </div>
                    <span class="feature-text">Lập kế hoạch linh hoạt</span>
                </div>

                <!-- Yêu thích -->
                <div class="feature-item">
                    <div class="feature-icon-circle">
                        <!-- Biểu tượng trái tim -->
                        <svg viewBox="0 0 24 24">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                    </div>
                    <span class="feature-text">Lưu giữ trải nghiệm yêu thích</span>
                </div>
            </div>
        </section>

        <!-- Thẻ đăng ký tài khoản bên phải -->
        <main class="register-card" aria-label="Biểu mẫu đăng ký">
            <h2 class="card-title">Đăng ký tài khoản</h2>

            {{-- Thông báo phản hồi hệ thống nếu có --}}
            @if(session('error'))
                <div class="alert-box alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert-box alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" novalidate id="registerForm">
                @csrf

                <!-- Họ và tên -->
                <div class="form-group">
                    <label class="form-label" for="name">Họ và tên</label>
                    <div class="input-wrapper">
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Nhập họ và tên"
                            value="{{ old('name') }}"
                            required
                            maxlength="40"
                            autocomplete="name"
                            autofocus
                        >
                    </div>
                    @error('name')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrapper">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="Nhập email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                        >
                    </div>
                    @error('email')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Mật khẩu -->
                <div class="form-group">
                    <label class="form-label" for="password">Mật khẩu</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control input-with-icon @error('password') is-invalid @enderror"
                            placeholder="Nhập mật khẩu"
                            required
                            autocomplete="new-password"
                        >
                        <button
                            type="button"
                            class="toggle-password-btn"
                            id="togglePassword"
                            aria-label="Hiển thị hoặc ẩn mật khẩu"
                            title="Hiển thị hoặc ẩn mật khẩu"
                        >
                            <!-- Biểu tượng mắt mở -->
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <!-- Biểu tượng mắt đóng (ẩn mặc định) -->
                            <svg class="eye-closed" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Xác nhận mật khẩu -->
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Xác nhận mật khẩu</label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control input-with-icon @error('password_confirmation') is-invalid @enderror"
                            placeholder="Nhập lại mật khẩu"
                            required
                            autocomplete="new-password"
                        >
                        <button
                            type="button"
                            class="toggle-password-btn"
                            id="togglePasswordConfirm"
                            aria-label="Hiển thị hoặc ẩn xác nhận mật khẩu"
                            title="Hiển thị hoặc ẩn xác nhận mật khẩu"
                        >
                            <!-- Biểu tượng mắt mở -->
                            <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <!-- Biểu tượng mắt đóng (ẩn mặc định) -->
                            <svg class="eye-closed" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Nút Đăng ký -->
                <button type="submit" class="btn-submit" id="btnRegister">Đăng ký</button>
            </form>

            <div class="divider">
                <span>hoặc</span>
            </div>

            <div class="card-footer-text">
                Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
            </div>
        </main>
    </div>

    <script>
        // Hàm chuyển đổi ẩn/hiển thị mật khẩu mà không làm thay đổi giá trị đã nhập
        function setupPasswordToggle(buttonId, inputId) {
            const button = document.getElementById(buttonId);
            const input = document.getElementById(inputId);

            if (!button || !input) return;

            button.addEventListener('click', function () {
                const eyeOpen = button.querySelector('.eye-open');
                const eyeClosed = button.querySelector('.eye-closed');

                if (input.type === 'password') {
                    input.type = 'text';
                    eyeOpen.style.display = 'none';
                    eyeClosed.style.display = 'block';
                } else {
                    input.type = 'password';
                    eyeOpen.style.display = 'block';
                    eyeClosed.style.display = 'none';
                }
            });
        }

        // Kích hoạt cho cả Mật khẩu và Xác nhận mật khẩu
        setupPasswordToggle('togglePassword', 'password');
        setupPasswordToggle('togglePasswordConfirm', 'password_confirmation');
    </script>
</body>
</html>
