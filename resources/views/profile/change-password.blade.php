<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - Travel Planner</title>
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

        .password-card {
            max-width: 480px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.04);
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

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 0 16px;
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
            right: 14px;
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
            width: 20px;
            height: 20px;
        }

        .input-with-icon {
            padding-right: 44px;
        }

        .field-error {
            display: block;
            font-size: 12.5px;
            color: #DC2626;
            margin-top: 6px;
            font-weight: 500;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 24px;
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

        .btn-cancel {
            width: 100%;
            height: 48px;
            background-color: #F3F4F6;
            color: #1F2937;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.15s ease;
        }

        .btn-cancel:hover {
            background-color: #E5E7EB;
        }
    </style>
</head>
<body>
    <main class="password-card" aria-label="Biểu mẫu đổi mật khẩu">
        <div class="card-header">
            <h1 class="card-title">Đổi mật khẩu</h1>
            <p class="card-subtitle">Cập nhật mật khẩu định kỳ để bảo vệ tài khoản của bạn</p>
        </div>

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

        <form action="{{ route('profile.password.update') }}" method="POST" novalidate id="changePasswordForm">
            @csrf

            <!-- Mật khẩu hiện tại -->
            <div class="form-group">
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        class="form-control input-with-icon @error('current_password') is-invalid @enderror"
                        placeholder="Mật khẩu hiện tại"
                        required
                        autocomplete="current-password"
                        autofocus
                    >
                    <button
                        type="button"
                        class="toggle-password-btn"
                        data-target="current_password"
                        aria-label="Hiển thị hoặc ẩn mật khẩu hiện tại"
                    >
                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-closed" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                @error('current_password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Mật khẩu mới -->
            <div class="form-group">
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control input-with-icon @error('password') is-invalid @enderror"
                        placeholder="Mật khẩu mới"
                        required
                        autocomplete="new-password"
                    >
                    <button
                        type="button"
                        class="toggle-password-btn"
                        data-target="password"
                        aria-label="Hiển thị hoặc ẩn mật khẩu mới"
                    >
                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
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

            <!-- Xác nhận mật khẩu mới -->
            <div class="form-group">
                <div class="input-wrapper">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control input-with-icon @error('password_confirmation') is-invalid @enderror"
                        placeholder="Xác nhận mật khẩu mới"
                        required
                        autocomplete="new-password"
                    >
                    <button
                        type="button"
                        class="toggle-password-btn"
                        data-target="password_confirmation"
                        aria-label="Hiển thị hoặc ẩn mật khẩu xác nhận"
                    >
                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
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

            <!-- Nhóm nút bấm -->
            <div class="button-group">
                <button type="submit" class="btn-submit" id="btnSavePassword">
                    Lưu mật khẩu mới
                </button>
                <a href="{{ route('profile.show') }}" class="btn-cancel" id="btnCancelPassword">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </main>

    <script>
        // Xử lý ẩn/hiện mật khẩu cho 3 trường
        document.querySelectorAll('.toggle-password-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const eyeOpen = this.querySelector('.eye-open');
                const eyeClosed = this.querySelector('.eye-closed');

                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    eyeOpen.style.display = 'none';
                    eyeClosed.style.display = 'block';
                } else {
                    targetInput.type = 'password';
                    eyeOpen.style.display = 'block';
                    eyeClosed.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
