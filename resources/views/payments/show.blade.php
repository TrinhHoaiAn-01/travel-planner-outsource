<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn thanh toán {{ $payment->transaction_id }} - Travel Planner</title>
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

        .main-container {
            max-width: 760px;
            margin: 40px auto;
            padding: 0 20px;
            flex: 1;
            width: 100%;
        }

        .receipt-card {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 40px;
            box-shadow: var(--shadow-lg);
            position: relative;
        }

        .receipt-status-banner {
            text-align: center;
            margin-bottom: 30px;
        }

        .status-icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #DCFCE7;
            color: #16A34A;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .status-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 6px;
        }

        .status-desc {
            font-size: 14px;
            color: var(--text-muted);
        }

        .receipt-divider {
            border-top: 2px dashed var(--border-color);
            margin: 28px 0;
        }

        .receipt-details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .receipt-details-table td {
            padding: 10px 0;
            font-size: 14.5px;
        }

        .receipt-label {
            color: var(--text-muted);
            width: 40%;
        }

        .receipt-val {
            text-align: right;
            font-weight: 600;
            color: var(--secondary);
        }

        .total-highlight {
            font-size: 22px;
            font-weight: 800;
            color: #059669;
        }

        .badge-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-completed { background: #DCFCE7; color: #15803D; }
        .badge-pending { background: #FEF3C7; color: #B45309; }
        .badge-failed { background: #FEE2E2; color: #B91C1C; }

        .receipt-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-print {
            background: #FFFFFF;
            border: 1.5px solid var(--border-color);
            color: var(--secondary);
            font-weight: 700;
            padding: 11px 22px;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-print:hover {
            border-color: var(--secondary);
            background: #F8FAFC;
        }

        .btn-booking {
            background: var(--primary);
            color: white;
            text-decoration: none;
            font-weight: 700;
            padding: 11px 22px;
            border-radius: var(--radius-md);
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-booking:hover {
            background: var(--primary-dark);
        }

        @media print {
            header, footer, .receipt-actions, .breadcrumb {
                display: none !important;
            }
            body {
                background: white;
            }
            .receipt-card {
                box-shadow: none;
                border: 1px solid #ccc;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="header-container">
            <a href="{{ route('home') }}" class="logo">
                <div class="logo-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                </div>
                <span>TRAVEL PLANNER</span>
            </a>
            <div style="font-size: 14px; font-weight: 700;">
                <a href="{{ route('bookings.index') }}" style="color: var(--primary); text-decoration: none;">&larr; Quản lý Booking</a>
            </div>
        </div>
    </header>

    <main class="main-container">
        <div class="receipt-card">
            <!-- Banner Trạng Thái -->
            <div class="receipt-status-banner">
                <div class="status-icon-circle">
                    @if($payment->status === 'completed')
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    @else
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="3"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                    @endif
                </div>
                <h1 class="status-title">
                    {{ $payment->status === 'completed' ? 'Thanh Toán Thành Công!' : 'Giao Dịch Đang Chờ Xử Lý' }}
                </h1>
                <p class="status-desc">Cảm ơn quý khách đã hoàn tất thanh toán cho đơn đặt phòng trên hệ thống Travel Planner.</p>
            </div>

            <!-- Bảng Chi Tiết Giao Dịch -->
            <table class="receipt-details-table">
                <tr>
                    <td class="receipt-label">Mã giao dịch (Txn ID):</td>
                    <td class="receipt-val" style="font-family: monospace; font-size: 15px;">{{ $payment->transaction_id }}</td>
                </tr>
                <tr>
                    <td class="receipt-label">Mã đơn đặt phòng:</td>
                    <td class="receipt-val">
                        <a href="{{ route('bookings.show', $payment->booking->id) }}" style="color: var(--primary); text-decoration: none;">
                            #{{ $payment->booking->booking_code }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="receipt-label">Thời gian giao dịch:</td>
                    <td class="receipt-val">{{ \Carbon\Carbon::parse($payment->payment_date ?? $payment->created_at)->format('H:i d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="receipt-label">Cơ sở lưu trú:</td>
                    <td class="receipt-val">{{ $payment->booking->roomType->destination->name ?? 'Điểm lưu trú' }}</td>
                </tr>
                <tr>
                    <td class="receipt-label">Hạng phòng & số lượng:</td>
                    <td class="receipt-val">{{ $payment->booking->roomType->name ?? 'Phòng' }} ({{ $payment->booking->room_count }} phòng)</td>
                </tr>
                <tr>
                    <td class="receipt-label">Khách hàng nhận phòng:</td>
                    <td class="receipt-val">{{ $payment->booking->user->name ?? 'Khách hàng' }}</td>
                </tr>
                <tr>
                    <td class="receipt-label">Phương thức thanh toán:</td>
                    <td class="receipt-val">
                        @switch($payment->payment_method)
                            @case('vnpay') Cổng thanh toán VNPay QR @break
                            @case('momo') Ví điện tử MoMo @break
                            @case('bank_transfer') Chuyển khoản VietQR @break
                            @case('credit_card') Thẻ tín dụng quốc tế @break
                            @case('cash') Tiền mặt tại quầy nhận phòng @break
                            @default {{ ucfirst($payment->payment_method) }}
                        @endswitch
                    </td>
                </tr>
                <tr>
                    <td class="receipt-label">Trạng thái giao dịch:</td>
                    <td class="receipt-val">
                        @if($payment->status === 'completed')
                            <span class="badge-status badge-completed">Đã thanh toán thành công</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge-status badge-pending">Chờ xác nhận</span>
                        @else
                            <span class="badge-status badge-failed">{{ ucfirst($payment->status) }}</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="receipt-divider"></div>

            <table class="receipt-details-table">
                <tr>
                    <td class="receipt-label" style="font-weight: 700; color: var(--secondary);">Số tiền đã thanh toán:</td>
                    <td class="receipt-val total-highlight">{{ number_format($payment->amount, 0, ',', '.') }}đ</td>
                </tr>
            </table>

            <!-- Các nút hành động -->
            <div class="receipt-actions">
                <button onclick="window.print()" class="btn-print">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    In Hóa Đơn Điện Tử
                </button>

                <a href="{{ route('bookings.show', $payment->booking->id) }}" class="btn-booking">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Xem Chi Tiết Đơn Đặt Phòng
                </a>
            </div>
        </div>
    </main>

    <footer style="background: white; border-top: 1px solid var(--border-color); padding: 20px; text-align: center; font-size: 13px; color: var(--text-muted);">
        © 2026 Travel Planner - Nhóm B (Nguyễn Văn Thắng - Booking & Payment Module).
    </footer>

</body>
</html>
