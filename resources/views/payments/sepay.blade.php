<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chuyển khoản Ngân hàng VietQR #{{ $booking->booking_code }} - Travel Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #0A58CA, #0D6EFD);
            color: white;
            padding: 24px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .card-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .card-header-icon {
            width: 46px;
            height: 46px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-header-title {
            font-size: 20px;
            font-weight: 800;
        }

        .card-header-desc {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 2px;
        }

        .booking-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 32px 30px;
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 32px;
        }

        @media (max-width: 768px) {
            .card-body {
                grid-template-columns: 1fr;
            }
        }

        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            background: #F8FAFC;
            border: 1.5px dashed #CBD5E1;
            border-radius: var(--radius-md);
            padding: 24px;
        }

        .qr-image-wrapper {
            background: white;
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border: 1px solid #E2E8F0;
            margin-bottom: 16px;
        }

        .qr-image {
            width: 220px;
            height: 220px;
            display: block;
            border-radius: 6px;
            object-fit: contain;
        }

        .qr-guide {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .qr-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #E0F2FE;
            color: #0369A1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 10px;
        }

        .info-section {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .info-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .info-item {
            background: #F8FAFC;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .info-item-highlight {
            background: #FEF3C7;
            border-color: #FCD34D;
        }

        .info-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 700;
            color: var(--secondary);
        }

        .info-value-amount {
            font-size: 20px;
            color: #DC2626;
            font-weight: 800;
        }

        .info-value-content {
            font-family: monospace;
            font-size: 17px;
            color: #B45309;
            background: #FFFBEB;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px dashed #F59E0B;
        }

        .copy-btn {
            background: white;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .copy-btn:hover {
            background: #F1F5F9;
            border-color: #CBD5E1;
        }

        .status-box {
            margin-top: 24px;
            background: #F0FDF4;
            border: 1.5px solid #BBF7D0;
            border-radius: var(--radius-md);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .pulse-spinner {
            width: 22px;
            height: 22px;
            border: 3px solid #86EFAC;
            border-top-color: #16A34A;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .status-text {
            font-size: 13.5px;
            color: #166534;
            font-weight: 600;
        }

        .status-subtext {
            font-size: 12px;
            color: #15803D;
            margin-top: 2px;
        }

        .footer-actions {
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-top: 18px;
            border-top: 1px solid var(--border-color);
        }

        .back-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .back-link:hover {
            color: var(--secondary);
        }

        .test-sim-btn {
            background: #0284C7;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .test-sim-btn:hover {
            background: #0369A1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="card-header-left">
                    <div class="card-header-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                    </div>
                    <div>
                        <div class="card-header-title">Thanh Toán Chuyển Khoản Ngân Hàng (VietQR)</div>
                        <div class="card-header-desc">Hệ thống xử lý tự động qua SePay • Khớp lệnh tức thì 24/7</div>
                    </div>
                </div>
                <div class="booking-badge">ĐƠN #{{ $booking->booking_code }}</div>
            </div>

            <!-- Body -->
            <div class="card-body">
                <!-- QR Code Column -->
                <div class="qr-section">
                    <div class="qr-image-wrapper">
                        <img src="{{ $sepayDetails['qr_code_url'] }}" alt="Mã VietQR Chuyển Khoản Tự Động" class="qr-image" id="vietqr-image">
                    </div>
                    <div class="qr-guide">
                        Mở App <strong>Ngân hàng</strong> hoặc <strong>Ví điện tử</strong> bất kỳ quét mã QR phía trên để chuyển tiền tự động.
                    </div>
                    <div class="qr-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Tự điền Số tiền & Nội dung
                    </div>
                </div>

                <!-- Info Column -->
                <div class="info-section">
                    <div class="info-list">
                        <!-- Ngân hàng thụ hưởng -->
                        <div class="info-item">
                            <div>
                                <div class="info-label">Ngân Hàng Thụ Hưởng</div>
                                <div class="info-value">{{ $sepayDetails['bank_name'] }}</div>
                            </div>
                            <span style="font-size: 11px; background: #E2E8F0; padding: 3px 8px; border-radius: 4px; font-weight: 700;">NAPAS 247</span>
                        </div>

                        <!-- Số tài khoản -->
                        <div class="info-item">
                            <div>
                                <div class="info-label">Số Tài Khoản</div>
                                <div class="info-value" id="val-account-number">{{ $sepayDetails['account_number'] }}</div>
                            </div>
                            <button type="button" class="copy-btn" onclick="copyText('{{ $sepayDetails['account_number'] }}', this)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg>
                                Sao chép
                            </button>
                        </div>

                        <!-- Chủ tài khoản -->
                        <div class="info-item">
                            <div>
                                <div class="info-label">Chủ Tài Khoản</div>
                                <div class="info-value">{{ $sepayDetails['account_holder'] }}</div>
                            </div>
                        </div>

                        <!-- Số tiền -->
                        <div class="info-item">
                            <div>
                                <div class="info-label">Số Tiền Thanh Toán</div>
                                <div class="info-value info-value-amount" id="val-amount">{{ $sepayDetails['formatted_amount'] }}</div>
                            </div>
                            <button type="button" class="copy-btn" onclick="copyText('{{ (int)$sepayDetails['amount'] }}', this)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg>
                                Sao chép
                            </button>
                        </div>

                        <!-- Cú pháp chuyển khoản -->
                        <div class="info-item info-item-highlight">
                            <div>
                                <div class="info-label" style="color: #92400E;">Nội Dung Chuyển Khoản (Bắt buộc giữ nguyên)</div>
                                <div class="info-value info-value-content" id="val-content">{{ $sepayDetails['transfer_content'] }}</div>
                            </div>
                            <button type="button" class="copy-btn" style="border-color: #FCD34D; background: #FEF3C7;" onclick="copyText('{{ $sepayDetails['transfer_content'] }}', this)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path></svg>
                                Sao chép
                            </button>
                        </div>
                    </div>

                    <!-- Live status checking box -->
                    <div class="status-box" id="status-box">
                        <div class="pulse-spinner" id="status-spinner"></div>
                        <div>
                            <div class="status-text" id="status-title">Đang chờ nhận tiền từ SePay & Ngân hàng...</div>
                            <div class="status-subtext" id="status-desc">Hệ thống đang tự động kiểm tra biến động số dư theo thời gian thực (3s/lần).</div>
                        </div>
                    </div>

                    <!-- Footer actions -->
                    <div class="footer-actions">
                        <a href="{{ route('payments.create', $booking->id) }}" class="back-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Chọn phương thức khác
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hàm sao chép văn bản
        function copyText(text, btnElement) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btnElement.innerHTML;
                btnElement.innerHTML = '✓ Đã chép';
                btnElement.style.color = '#10B981';
                setTimeout(() => {
                    btnElement.innerHTML = originalText;
                    btnElement.style.color = '';
                }, 2000);
            });
        }

        // Tự động Polling kiểm tra trạng thái thanh toán cứ mỗi 3 giây
        const checkStatusUrl = "{{ route('payments.status', $booking->id) }}";
        const bookingId = {{ $booking->id }};
        let pollInterval = setInterval(checkPaymentStatus, 3000);

        function checkPaymentStatus() {
            fetch(checkStatusUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.is_paid && data.redirect_url) {
                    clearInterval(pollInterval);
                    document.getElementById('status-spinner').style.display = 'none';
                    document.getElementById('status-title').innerText = '🎉 Thanh toán thành công!';
                    document.getElementById('status-desc').innerText = 'Đã nhận được tiền. Đang chuyển hướng sang biên lai thanh toán...';
                    document.getElementById('status-box').style.background = '#ECFDF5';
                    document.getElementById('status-box').style.borderColor = '#34D399';

                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1200);
                }
            })
            .catch(err => {
                console.log('Đang kiểm tra kết nối...', err);
            });
        }

        // Hàm giả lập nhận tiền thành công tức thì phục vụ demo và kiểm thử
        function simulatePaymentSuccess() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerText = 'Đang xử lý...';

            fetch("{{ route('payments.simulate-receive', $booking->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    payment_method: 'banking'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    clearInterval(pollInterval);
                    document.getElementById('status-spinner').style.display = 'none';
                    document.getElementById('status-title').innerText = '🎉 Xác nhận thành công!';
                    document.getElementById('status-desc').innerText = 'Hệ thống đã nhận diện biến động số dư. Đang chuyển hướng...';
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 800);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerText = '⚡ Giả lập Nhận Tiền Ngay (Dev Test)';
                alert('Lỗi giả lập: ' + err.message);
            });
        }
    </script>
</body>
</html>

