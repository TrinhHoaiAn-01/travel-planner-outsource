<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Travel Planner</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 36px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 700;
            color: #0F172A;
        }
        .brand-subtitle {
            font-size: 13.5px;
            color: #64748B;
            margin-top: 4px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #CBD5E1;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .btn-submit {
            width: 100%;
            padding: 11px;
            background-color: #2563EB;
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-submit:hover {
            background-color: #1D4ED8;
        }
        .error-box {
            background-color: #FEF2F2;
            color: #DC2626;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .dev-login-link {
            display: block;
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #2563EB;
            text-decoration: none;
        }
        .dev-login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-header">
            <h1 class="brand-title">Đăng nhập Quản Trị</h1>
            <p class="brand-subtitle">Hệ thống Travel Planner</p>
        </div>

        @if($errors->any())
            <div class="error-box">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="admin@travelplanner.test" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" value="password" required>
            </div>

            <button type="submit" class="btn-submit">Đăng nhập</button>
        </form>

        <a href="{{ route('dev.login.admin') }}" class="dev-login-link">
            ⚡ Đăng nhập nhanh Admin (Môi trường Dev)
        </a>
    </div>
</body>
</html>
