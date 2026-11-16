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
    <script>
        // Thoát khỏi iframe nếu vô tình bị nhúng, đảm bảo đăng nhập luôn ở màn hình chính
        if (window.top !== window.self) {
            window.top.location.href = window.location.href;
        }
    </script>
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

        #captchaRefreshError:empty {
            margin-top: 0;
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

        .card-footer-text {
            text-align: center;
            font-size: 14px;
            color: #374151;
            margin-top: 22px;
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
                        value="{{ \App\Support\FormInput::oldText('email') }}"
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
                <span class="field-error" id="captchaRefreshError" role="alert"></span>
            </div>

            <!-- Ghi nhớ đăng nhập & Quên mật khẩu -->
            <div class="options-row">
                <label class="remember-checkbox" for="remember">
                    <input type="checkbox" id="remember" name="remember" value="1" {{ \App\Support\FormInput::oldText('remember') ? 'checked' : '' }}>
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <a href="{{ route('password.request') }}" class="forgot-password-link">Quên mật khẩu?</a>
            </div>

            <!-- Nút Đăng nhập -->
            <button type="submit" class="btn-submit" id="btnLogin">Đăng nhập</button>
        </form>

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
        const captchaRefreshError = document.getElementById('captchaRefreshError');

        if (refreshBtn && captchaBox && captchaInput && captchaRefreshError) {
            refreshBtn.addEventListener('click', function () {
                refreshBtn.disabled = true;
                refreshBtn.classList.add('spinning');
                captchaRefreshError.textContent = '';

                fetch('{{ route("captcha.refresh") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Không thể làm mới CAPTCHA.');
                    }

                    return response.json();
                })
                .then(data => {
                    if (typeof data?.captcha !== 'string' || !data.captcha) {
                        throw new Error('Phản hồi CAPTCHA không hợp lệ.');
                    }

                    captchaBox.textContent = data.captcha;
                    captchaInput.value = '';
                    captchaInput.focus();
                })
                .catch(() => {
                    captchaRefreshError.textContent = 'Không thể tạo mã CAPTCHA mới. Vui lòng thử lại.';
                })
                .finally(() => {
                    refreshBtn.disabled = false;
                    refreshBtn.classList.remove('spinning');
                });
            });
        }
    </script>
    @include('partials.popup-notification')
</body>
</html>
