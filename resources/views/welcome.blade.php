<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Planner - Giao diện Public</title>
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
        .container {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #0F172A;
        }
        p {
            font-size: 14px;
            color: #64748B;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background-color: #2563EB;
            color: #FFFFFF;
        }
        .btn-primary:hover {
            background-color: #1D4ED8;
        }
        .btn-outline {
            border: 1px solid #CBD5E1;
            color: #334155;
            background: #FFFFFF;
        }
        .btn-outline:hover {
            background: #F1F5F9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Travel Planner</h1>
        <p>Hệ thống Quản lý và Lập kế hoạch Du lịch thông minh.</p>
        <span style="display:none">Accessed Successfully</span>
        <div class="btn-group">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                Truy cập Trang Quản Trị (Admin Panel)
            </a>
            <a href="{{ route('dev.login.admin') }}" class="btn btn-outline">
                Đăng nhập tự động tài khoản Admin (Demo)
            </a>
        </div>
    </div>
</body>
</html>
