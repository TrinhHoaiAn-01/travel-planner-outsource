<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu? - Travel Planner</title>
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

        .auth-card {
            max-width: 480px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.04);
            padding: 42px 38px;
        }

        .card-header {
            text-align: center;
            margin-bottom: 22px;
        }

        .card-title {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.01em;
        }

        .info-notice {
            background-color: #D1FADF;
            color: #027A48;
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 13.5px;
            line-height: 1.45;
            text-align: center;
            margin-bottom: 22px;
            font-weight: 500;
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
            margin-bottom: 22px;
        }

        .input-wrapper {
            position: relative;
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

        .field-error {
            display: block;
            font-size: 12.5px;
            color: #DC2626;
            margin-top: 6px;
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
            transition: background-color 0.15s ease, transform 0.05s ease;
            margin-bottom: 24px;
        }

        .btn-submit:hover {
            background-color: #0052CC;
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .back-link-wrapper {
            text-align: center;
        }

        .back-link {
            color: #0066FF;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.15s ease;
        }

        .back-link:hover {
            text-decoration: underline;
            color: #0052CC;
        }
    </style>
</head>
<body>
    <main class="auth-card" aria-label="Biểu mẫu khôi phục mật khẩu">
        <div class="card-header">
            <h1 class="card-title">Quên mật khẩu?</h1>
        </div>

        <div class="info-notice">
            Nhập địa chỉ email và nhấn gửi để tiếp tục đặt lại mật khẩu mới.
        </div>

        {{-- Thông báo phản hồi hệ thống nếu có --}}
        @if(session('status'))
            <div class="alert-box alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" novalidate id="forgotPasswordForm">
            @csrf

            <!-- Email -->
            <div class="form-group">
                <div class="input-wrapper">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="Email đã đăng ký"
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

            <!-- Nút gửi liên kết -->
            <button type="submit" class="btn-submit" id="btnSubmitReset">
                Gửi liên kết đặt lại mật khẩu
            </button>
        </form>

        <div class="back-link-wrapper">
            <a href="{{ route('login') }}" class="back-link">
                &larr; Quay lại Đăng nhập
            </a>
        </div>
    </main>
</body>
</html>
