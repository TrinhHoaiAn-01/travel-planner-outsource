<!DOCTYPE html>
<html lang="vi" data-signed-in="{{ auth()->check() ? 'true' : 'false' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base target="_top">
    <title>{{ $merchant ? 'Merchant Agent' : 'Trợ lý du lịch' }} · Travel Planner</title>
    
    <!-- Preload critical React bundles for instant load -->
    <link rel="modulepreload" href="{{ asset('build/assets/Boundary-BtyRJOZM.js') }}">
    @if(!$merchant)
        <link rel="modulepreload" href="{{ asset('build/assets/shopping-F7xK9m2Q.js') }}">
        <link rel="stylesheet" href="{{ asset('build/assets/shopping-DnmuUCsB.css') }}">
    @else
        <link rel="modulepreload" href="{{ asset('build/assets/merchant-B_LNYEpS.js') }}">
        <link rel="stylesheet" href="{{ asset('build/assets/merchant-SU6X7HtI.css') }}">
    @endif

    <style>
        html, body {
            background-color: #ffffff !important;
            color: #0f172a !important;
            font-family: 'Times New Roman', Times, Georgia, serif !important;
            margin: 0;
            padding: 0;
            height: 100%;
            overflow-x: hidden;
        }
        *, ::before, ::after {
            font-family: 'Times New Roman', Times, Georgia, serif !important;
        }

        @keyframes aiProgressShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        @keyframes aiPulseGlow {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        .ai-progress-track {
            position: relative;
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
        }

        .ai-progress-bar-fill {
            height: 100%;
            width: 15%;
            background: linear-gradient(90deg, #2563eb, #3b82f6, #60a5fa, #2563eb);
            background-size: 200% 100%;
            animation: aiProgressShimmer 2s linear infinite;
            border-radius: 9999px;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.45);
            transition: width 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ai-pulse-block {
            animation: aiPulseGlow 1.5s ease-in-out infinite;
        }
    </style>
    @viteReactRefresh
    @vite($merchant ? 'resources/js/commerce/merchant.tsx' : 'resources/js/commerce/shopping.tsx')
</head>
<body style="background: #ffffff; color: #0f172a; font-family: 'Times New Roman', Times, Georgia, serif;">
    <div id="commerce-root">
        <div class="ai-skeleton-screen" style="padding: 18px 24px; height: 100vh; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; background: #ffffff;">
            
            <!-- Top bar -->
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 28px; height: 28px; border-radius: 50%; background: #2563eb; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 13px;">✦</div>
                        <div style="font-weight: 700; font-size: 14px; color: #0f172a;">Trợ lý Du lịch AI</div>
                        <span style="font-size: 9px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: #2563eb; color: #ffffff;">BETA</span>
                    </div>
                    <span id="aiLoadingPercentText" style="font-size: 12px; font-weight: 700; color: #2563eb; font-family: monospace;">15%</span>
                </div>

                <!-- Thanh Progress trực quan xem tiến trình xử lý -->
                <div style="margin-top: 12px; margin-bottom: 16px;">
                    <div class="ai-progress-track">
                        <div id="aiLoadingBarFill" class="ai-progress-bar-fill"></div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px;">
                        <span id="aiLoadingStatusText" style="font-size: 11.5px; color: #64748b; font-weight: 600;">
                            Đang nạp mô hình & dữ liệu du lịch…
                        </span>
                        <span style="font-size: 11px; color: #94a3b8;">Vui lòng đợi giây lát</span>
                    </div>
                </div>
            </div>

            <!-- Body message placeholders -->
            <div style="flex: 1; padding: 16px 0; display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; gap: 10px; max-width: 90%;">
                    <div style="width: 26px; height: 26px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0;">✦</div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; width: 100%;">
                        <div class="ai-pulse-block" style="width: 85%; height: 12px; border-radius: 4px; background: #cbd5e1; margin-bottom: 8px;"></div>
                        <div class="ai-pulse-block" style="width: 65%; height: 12px; border-radius: 4px; background: #e2e8f0;"></div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 6px; padding-left: 36px;">
                    <div class="ai-pulse-block" style="height: 36px; border-radius: 8px; border: 1px dashed #cbd5e1; background: #f8fafc; width: 85%;"></div>
                    <div class="ai-pulse-block" style="height: 36px; border-radius: 8px; border: 1px dashed #cbd5e1; background: #f8fafc; width: 70%;"></div>
                </div>
            </div>

            <!-- Bottom input bar placeholder -->
            <div style="padding-top: 14px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px;">
                <div style="flex: 1; height: 40px; border-radius: 20px; border: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; padding: 0 16px; font-size: 13px; color: #64748b;">
                    <span>Đang kết nối Trợ lý AI Travel Planner…</span>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #2563eb; opacity: 0.8; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 14px;">↑</div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var bar = document.getElementById('aiLoadingBarFill');
            var text = document.getElementById('aiLoadingStatusText');
            var percent = document.getElementById('aiLoadingPercentText');
            if (!bar || !text || !percent) return;

            var steps = [
                { p: 30, t: 'Đang nạp mô hình & dữ liệu du lịch…' },
                { p: 58, t: 'Đang đồng bộ cấu hình điểm đến & khách sạn…' },
                { p: 82, t: 'Đang kết nối phiên làm việc bảo mật…' },
                { p: 96, t: 'Đang chuẩn bị giao diện hội thoại…' },
                { p: 100, t: 'Hoàn tất! Sẵn sàng hỗ trợ.' }
            ];
            var i = 0;
            var timer = setInterval(function() {
                if (i < steps.length) {
                    bar.style.width = steps[i].p + '%';
                    percent.textContent = steps[i].p + '%';
                    text.textContent = steps[i].t;
                    i++;
                } else {
                    clearInterval(timer);
                }
            }, 250);
        })();
    </script>

    <noscript>Bật JavaScript để sử dụng giao diện Commerce Agents. <a href="/" target="_top">Về Travel Planner</a></noscript>
    @include('partials.search-suggestions')
    @include('partials.popup-notification')
</body>
</html>
