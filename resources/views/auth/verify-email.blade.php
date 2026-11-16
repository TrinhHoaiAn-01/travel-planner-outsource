<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực tài khoản email - Travel Planner</title>
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
            padding: 24px;
        }

        .verify-card {
            max-width: 480px;
            width: 100%;
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.04);
            padding: 40px 36px;
            text-align: center;
        }

        .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background-color: #EFF6FF;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .icon-circle svg {
            width: 32px;
            height: 32px;
            stroke: #0066FF;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .card-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
        }

        .card-text {
            font-size: 14.5px;
            line-height: 1.6;
            color: #4B5563;
            margin-bottom: 24px;
        }

        .alert-box {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            line-height: 1.45;
            margin-bottom: 20px;
            text-align: left;
        }

        .alert-success {
            background-color: #F0FDF4;
            color: #16A34A;
            border: 1px solid #BBF7D0;
        }

        .btn-resend {
            width: 100%;
            height: 46px;
            background-color: #0066FF;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 14.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .btn-resend:hover {
            background-color: #0052CC;
        }

        .actions-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
        }

        .btn-logout {
            background: none;
            border: none;
            color: #DC2626;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
        }

        .btn-home {
            color: #4B5563;
            font-size: 13.5px;
            text-decoration: none;
        }

        .btn-home:hover {
            color: #111827;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                <polyline points="22,6 12,13 2,6" />
            </svg>
        </div>

        <h1 class="card-title">Xác thực tài khoản email</h1>

        @if (session('status') == 'verification-link-sent')
            <div class="alert-box alert-success" role="alert">
                Liên kết xác thực mới đã được gửi đến địa chỉ email của bạn.
            </div>
        @endif

        @if (session('success'))
            <div class="alert-box alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <p class="card-text">
            Cảm ơn bạn đã đăng ký tài khoản Travel Planner! Trước khi bắt đầu, vui lòng kiểm tra hộp thư email và nhấp vào liên kết xác thực để kích hoạt tài khoản.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-resend">Gửi lại email xác thực</button>
        </form>

        <div class="actions-row">
            <a href="{{ route('home') }}" class="btn-home">Về trang chủ</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">Đăng xuất</button>
            </form>
        </div>
    </div>
</body>
</html>
