<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Travel Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

        .login-card {
            max-width: 440px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.04);
            padding: 42px 38px;
        }

        .card-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.01em;
        }

        .card-subtitle {
            font-size: 14px;
            color: #4B5563;
            margin-top: 6px;
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

        /* Khối CAPTCHA */
        .captcha-row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .captcha-display {
            flex: 1;
            height: 46px;
            background: #F3F4F6;
            background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(0, 0, 0, 0.02) 10px, rgba(0, 0, 0, 0.02) 20px);
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 14px;
            color: #1F2937;
            user-select: none;
            padding-left: 14px;
        }

        .btn-refresh-captcha {
            width: 46px;
            height: 46px;
            background-color: #FFFFFF;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #4B5563;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        .btn-refresh-captcha:hover {
            background-color: #F9FAFB;
            color: #0066FF;
            border-color: #0066FF;
        }

        .btn-refresh-captcha svg {
            width: 20px;
            height: 20px;
            transition: transform 0.3s ease;
        }

        .btn-refresh-captcha.spinning svg {
            transform: rotate(360deg);
        }

        .field-error {
            display: block;
            font-size: 12.5px;
            color: #DC2626;
            margin-top: 5px;
            font-weight: 500;
        }

        /* Hàng ghi nhớ đăng nhập và quên mật khẩu */
        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 4px;
            margin-bottom: 18px;
            font-size: 13.5px;
        }

        .remember-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #374151;
            cursor: pointer;
            user-select: none;
        }

        .remember-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            accent-color: #0066FF;
            cursor: pointer;
        }

        .forgot-password-link {
            color: #0066FF;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-password-link:hover {
            text-decoration: underline;
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

        /* Các nút mạng xã hội */
        .social-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 22px;
        }

        .btn-social {
            width: 100%;
            height: 44px;
            background-color: #FFFFFF;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #1F2937;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .btn-social:hover {
            background-color: #F9FAFB;
            border-color: #9CA3AF;
        }

        .btn-social svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
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
    </style>
</head>
<body>
    <main class="login-card" aria-label="Biểu mẫu đăng nhập">
        <div class="card-header">
            <h1 class="card-title">Đăng nhập</h1>
            <p class="card-subtitle">Chào mừng bạn quay trở lại</p>
        </div>

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

        <form action="{{ route('login') }}" method="POST" novalidate id="loginForm">
            @csrf

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
                        autofocus
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
                        autocomplete="current-password"
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

            <!-- CAPTCHA -->
            <div class="form-group">
                <label class="form-label" for="captcha">CAPTCHA</label>
                <div class="captcha-row">
                    <div class="captcha-display" id="captchaBox" aria-label="Mã CAPTCHA">{{ $captchaCode }}</div>
                    <button
                        type="button"
                        class="btn-refresh-captcha"
                        id="btnRefreshCaptcha"
                        aria-label="Làm mới mã CAPTCHA"
                        title="Làm mới mã CAPTCHA"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                    </button>
                </div>
                <div class="input-wrapper">
                    <input
                        type="text"
                        id="captcha"
                        name="captcha"
                        class="form-control @error('captcha') is-invalid @enderror"
                        placeholder="Nhập mã CAPTCHA"
                        required
                        maxlength="10"
                        autocomplete="off"
                    >
                </div>
                @error('captcha')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Ghi nhớ đăng nhập & Quên mật khẩu -->
            <div class="options-row">
                <label class="remember-checkbox" for="remember">
                    <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <a href="#" class="forgot-password-link">Quên mật khẩu?</a>
            </div>

            <!-- Nút Đăng nhập -->
            <button type="submit" class="btn-submit" id="btnLogin">Đăng nhập</button>
        </form>

        <div class="divider">
            <span>Hoặc</span>
        </div>

        <!-- Đăng nhập mạng xã hội -->
        <div class="social-buttons">
            <button type="button" class="btn-social">
                <!-- Icon Facebook -->
                <svg viewBox="0 0 24 24">
                    <path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span>Đăng nhập với Facebook</span>
            </button>

            <button type="button" class="btn-social">
                <!-- Icon Google -->
                <svg viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.15z"/>
                    <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24z"/>
                    <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15z"/>
                    <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                </svg>
                <span>Đăng nhập với Google</span>
            </button>
        </div>

        <div class="card-footer-text">
            Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
        </div>
    </main>

    <script>
        // Xử lý ẩn/hiển thị mật khẩu
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const eyeOpen = toggleBtn.querySelector('.eye-open');
                const eyeClosed = toggleBtn.querySelector('.eye-closed');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeOpen.style.display = 'none';
                    eyeClosed.style.display = 'block';
                } else {
                    passwordInput.type = 'password';
                    eyeOpen.style.display = 'block';
                    eyeClosed.style.display = 'none';
                }
            });
        }

        // Xử lý làm mới CAPTCHA qua AJAX
        const refreshBtn = document.getElementById('btnRefreshCaptcha');
        const captchaBox = document.getElementById('captchaBox');
        const captchaInput = document.getElementById('captcha');

        if (refreshBtn && captchaBox) {
            refreshBtn.addEventListener('click', function () {
                refreshBtn.classList.add('spinning');

                fetch('{{ route("captcha.refresh") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.captcha) {
                        captchaBox.textContent = data.captcha;
                        // Xóa mã cũ người dùng đã nhập theo yêu cầu của đặc tả
                        if (captchaInput) {
                            captchaInput.value = '';
                            captchaInput.focus();
                        }
                    }
                })
                .catch(error => {
                    console.error('Lỗi làm mới CAPTCHA:', error);
                })
                .finally(() => {
                    setTimeout(() => {
                        refreshBtn.classList.remove('spinning');
                    }, 300);
                });
            });
        }
    </script>
</body>
</html>
