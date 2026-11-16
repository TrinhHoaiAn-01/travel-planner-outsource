@php
    if (defined('APP_POPUP_NOTIFICATION_RENDERED')) {
        return;
    }
    define('APP_POPUP_NOTIFICATION_RENDERED', true);

    $flashType = null;
    $flashTitle = null;
    $flashMessage = null;

    if (session('success')) {
        $flashType = 'success';
        $flashTitle = 'Thành công';
        $flashMessage = session('success');
    } elseif (session('error')) {
        $flashType = 'error';
        $flashTitle = 'Đã xảy ra lỗi';
        $flashMessage = session('error');
    } elseif (session('warning')) {
        $flashType = 'warning';
        $flashTitle = 'Lưu ý quan trọng';
        $flashMessage = session('warning');
    } elseif (session('status')) {
        $flashType = 'info';
        $flashTitle = 'Thông báo hệ thống';
        $flashMessage = session('status') === 'verification-link-sent'
            ? 'Liên kết xác thực mới đã được gửi đến địa chỉ email của bạn.'
            : session('status');
    } elseif (session('info')) {
        $flashType = 'info';
        $flashTitle = 'Thông báo';
        $flashMessage = session('info');
    } elseif (isset($errors) && $errors->any()) {
        $flashType = 'error';
        $flashTitle = 'Vui lòng kiểm tra lại thông tin';
        $flashMessage = '<ul style="margin: 0; padding-left: 20px; text-align: left;">' . 
            implode('', array_map(fn($e) => '<li>' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '</li>', $errors->all())) . 
            '</ul>';
    }
@endphp

<!-- Popup Thông Báo Ở Giữa Màn Hình (Unified Centered App Popup Modal) -->
<div id="appGlobalPopupBackdrop" class="app-popup-backdrop" style="display: none;" onclick="handleAppPopupBackdropClick(event)">
    <div class="app-popup-card" role="dialog" aria-modal="true" aria-labelledby="appPopupTitle" aria-describedby="appPopupMessage" onclick="event.stopPropagation()">
        <button type="button" class="app-popup-close-btn" onclick="handleAppPopupCancel()" title="Đóng (Esc)" aria-label="Đóng popup">&times;</button>
        
        <div class="app-popup-icon-wrap success" id="appPopupIconWrap">
            <!-- Render icon SVG dynamically -->
            <svg id="appPopupSvgSuccess" class="app-popup-svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <svg id="appPopupSvgError" class="app-popup-svg" style="display: none;" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            <svg id="appPopupSvgWarning" class="app-popup-svg" style="display: none;" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <svg id="appPopupSvgInfo" class="app-popup-svg" style="display: none;" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
        </div>

        <h3 class="app-popup-title" id="appPopupTitle">Thành công</h3>
        
        <div class="app-popup-message" id="appPopupMessage">
            <!-- Nội dung thông báo -->
        </div>

        <div class="app-popup-actions" id="appPopupActions">
            <button type="button" class="app-popup-cancel-btn" id="appPopupCancelBtn" onclick="handleAppPopupCancel()" style="display: none;">
                Hủy bỏ
            </button>
            <button type="button" class="app-popup-confirm-btn" id="appPopupConfirmBtn" onclick="handleAppPopupConfirm()">
                Đồng ý
            </button>
        </div>
    </div>
</div>

<style>
    .app-popup-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.68);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        z-index: 9999999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.22s ease, visibility 0.22s ease;
        box-sizing: border-box;
    }

    .app-popup-backdrop.active {
        opacity: 1;
        visibility: visible;
    }

    .app-popup-card {
        background: #FFFFFF;
        width: 100%;
        max-width: 440px;
        border-radius: 22px;
        box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.35), 0 0 0 1px rgba(0, 0, 0, 0.05);
        padding: 32px 28px 26px;
        text-align: center;
        position: relative;
        transform: scale(0.9) translateY(12px);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        box-sizing: border-box;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .app-popup-backdrop.active .app-popup-card {
        transform: scale(1) translateY(0);
    }

    .app-popup-close-btn {
        position: absolute;
        top: 14px;
        right: 14px;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #F1F5F9;
        border: none;
        color: #64748B;
        font-size: 22px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .app-popup-close-btn:hover {
        background: #E2E8F0;
        color: #0F172A;
        transform: rotate(90deg);
    }

    .app-popup-icon-wrap {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        margin: 0 auto 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .app-popup-icon-wrap.success {
        background: #ECFDF5;
        color: #10B981;
        border: 3px solid #D1FAE5;
    }

    .app-popup-icon-wrap.error {
        background: #FEF2F2;
        color: #EF4444;
        border: 3px solid #FEE2E2;
    }

    .app-popup-icon-wrap.warning {
        background: #FFFBEB;
        color: #F59E0B;
        border: 3px solid #FEF3C7;
    }

    .app-popup-icon-wrap.info {
        background: #EFF6FF;
        color: #0066FF;
        border: 3px solid #DBEAFE;
    }

    .app-popup-title {
        font-size: 19px;
        font-weight: 700;
        color: #0F172A;
        margin: 0 0 10px 0;
        line-height: 1.35;
        letter-spacing: -0.01em;
    }

    .app-popup-message {
        font-size: 14.5px;
        line-height: 1.6;
        color: #475569;
        margin-bottom: 24px;
        word-break: break-word;
        max-height: 48vh;
        overflow-y: auto;
        padding: 0 4px;
    }

    .app-popup-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .app-popup-cancel-btn {
        flex: 1;
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #CBD5E1;
        border-radius: 12px;
        padding: 12px 20px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        outline: none;
    }

    .app-popup-cancel-btn:hover {
        background: #E2E8F0;
        color: #0F172A;
    }

    .app-popup-confirm-btn {
        flex: 1;
        width: 100%;
        background: #0066FF;
        color: #FFFFFF;
        border: none;
        border-radius: 12px;
        padding: 12px 20px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(0, 102, 255, 0.25);
        transition: all 0.2s ease;
        outline: none;
    }

    .app-popup-confirm-btn:hover {
        background: #0052CC;
        box-shadow: 0 6px 18px rgba(0, 102, 255, 0.35);
        transform: translateY(-1px);
    }

    .app-popup-confirm-btn:active {
        transform: translateY(0);
    }

    .app-popup-confirm-btn.danger {
        background: #EF4444;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.25);
    }

    .app-popup-confirm-btn.danger:hover {
        background: #DC2626;
        box-shadow: 0 6px 18px rgba(239, 68, 68, 0.35);
    }
</style>

<script>
(function() {
    let appPopupOnConfirm = null;
    let appPopupOnCancel = null;
    let appPopupTimer = null;

    window.showAppPopup = function(options) {
        if (!options) return;
        if (typeof options === 'string') {
            options = { message: options, type: 'success' };
        }

        const type = options.type || 'info';
        const isConfirm = !!options.isConfirm;
        const title = options.title || (isConfirm ? 'Xác nhận thao tác' : (type === 'success' ? 'Thành công' : (type === 'error' ? 'Đã xảy ra lỗi' : (type === 'warning' ? 'Lưu ý' : 'Thông báo'))));
        const message = options.message || '';
        const confirmText = options.confirmText || (isConfirm ? 'Xác nhận' : 'Đồng ý');
        const cancelText = options.cancelText || 'Hủy bỏ';
        const autoCloseMs = options.autoCloseMs || null;
        
        appPopupOnConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : null;
        appPopupOnCancel = typeof options.onCancel === 'function' ? options.onCancel : null;

        const backdrop = document.getElementById('appGlobalPopupBackdrop');
        const iconWrap = document.getElementById('appPopupIconWrap');
        const titleEl = document.getElementById('appPopupTitle');
        const messageEl = document.getElementById('appPopupMessage');
        const confirmBtn = document.getElementById('appPopupConfirmBtn');
        const cancelBtn = document.getElementById('appPopupCancelBtn');

        if (!backdrop || !iconWrap || !titleEl || !messageEl || !confirmBtn) return;

        // Reset classes
        iconWrap.className = 'app-popup-icon-wrap ' + type;

        // Toggle svgs
        const svgSuccess = document.getElementById('appPopupSvgSuccess');
        const svgError = document.getElementById('appPopupSvgError');
        const svgWarning = document.getElementById('appPopupSvgWarning');
        const svgInfo = document.getElementById('appPopupSvgInfo');

        if (svgSuccess) svgSuccess.style.display = type === 'success' ? 'block' : 'none';
        if (svgError) svgError.style.display = type === 'error' ? 'block' : 'none';
        if (svgWarning) svgWarning.style.display = type === 'warning' ? 'block' : 'none';
        if (svgInfo) svgInfo.style.display = type === 'info' ? 'block' : 'none';

        titleEl.textContent = title;
        if (options.isHtml || (typeof message === 'string' && message.includes('<'))) {
            messageEl.innerHTML = message;
        } else {
            messageEl.textContent = message;
        }

        confirmBtn.textContent = confirmText;
        if (isConfirm && (type === 'error' || options.danger)) {
            confirmBtn.className = 'app-popup-confirm-btn danger';
        } else {
            confirmBtn.className = 'app-popup-confirm-btn';
        }

        if (cancelBtn) {
            cancelBtn.style.display = isConfirm ? 'block' : 'none';
            cancelBtn.textContent = cancelText;
        }

        // Clear existing timer if any
        if (appPopupTimer) {
            clearTimeout(appPopupTimer);
            appPopupTimer = null;
        }

        backdrop.style.display = 'flex';
        // Trigger animation
        requestAnimationFrame(() => {
            backdrop.classList.add('active');
            confirmBtn.focus();
        });

        // Auto close timer if requested
        if (autoCloseMs && autoCloseMs > 0 && !isConfirm) {
            appPopupTimer = setTimeout(() => {
                closeAppPopup();
            }, autoCloseMs);
        }
    };

    window.closeAppPopup = function() {
        const backdrop = document.getElementById('appGlobalPopupBackdrop');
        if (appPopupTimer) {
            clearTimeout(appPopupTimer);
            appPopupTimer = null;
        }
        if (backdrop) {
            backdrop.classList.remove('active');
            setTimeout(() => {
                backdrop.style.display = 'none';
            }, 220);
        }
    };

    window.handleAppPopupConfirm = function() {
        const cb = appPopupOnConfirm;
        appPopupOnConfirm = null;
        appPopupOnCancel = null;
        closeAppPopup();
        if (typeof cb === 'function') {
            cb();
        }
    };

    window.handleAppPopupCancel = function() {
        const cb = appPopupOnCancel;
        appPopupOnConfirm = null;
        appPopupOnCancel = null;
        closeAppPopup();
        if (typeof cb === 'function') {
            cb();
        }
    };

    window.handleAppPopupBackdropClick = function(e) {
        if (e.target && e.target.id === 'appGlobalPopupBackdrop') {
            handleAppPopupCancel();
        }
    };

    window.showAppConfirm = function(options) {
        if (!options) return;
        if (typeof options === 'string') {
            options = { message: options };
        }
        options.isConfirm = true;
        options.type = options.type || 'warning';
        options.title = options.title || 'Xác nhận thao tác';
        options.confirmText = options.confirmText || 'Xác nhận';
        options.cancelText = options.cancelText || 'Hủy bỏ';
        window.showAppPopup(options);
    };

    // Helper cho các form cần xác nhận
    window.handleFormConfirm = function(form, event, message, confirmText = 'Xác nhận', danger = true) {
        if (form.dataset.confirmed === 'true') {
            return true;
        }
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        window.showAppConfirm({
            title: 'Xác nhận thao tác',
            message: message,
            confirmText: confirmText,
            danger: danger,
            type: danger ? 'warning' : 'info',
            onConfirm: function() {
                form.dataset.confirmed = 'true';
                form.submit();
            }
        });
        return false;
    };

    // Helper functions
    window.showSuccessPopup = function(msg, title = 'Thành công') {
        window.showAppPopup({ type: 'success', title, message: msg });
    };

    window.showErrorPopup = function(msg, title = 'Đã xảy ra lỗi') {
        window.showAppPopup({ type: 'error', title, message: msg });
    };

    window.showWarningPopup = function(msg, title = 'Lưu ý') {
        window.showAppPopup({ type: 'warning', title, message: msg });
    };

    window.showInfoPopup = function(msg, title = 'Thông báo') {
        window.showAppPopup({ type: 'info', title, message: msg });
    };

    // Global override cho các hàm toast và alert cũ khắp app
    window.showToast = function(msg, type = 'success') {
        window.showAppPopup({ type: type === 'danger' ? 'error' : type, message: msg });
    };

    window.showHomeToast = function(msg, type = 'success') {
        window.showAppPopup({ type: type === 'danger' ? 'error' : type, message: msg });
    };

    // Thay thế hoàn toàn alert mặc định của trình duyệt ("localhost:8000 says")
    window.alert = function(msg) {
        window.showAppPopup({ type: 'info', message: msg });
    };

    // Phím tắt Esc / Enter
    document.addEventListener('keydown', function(e) {
        const backdrop = document.getElementById('appGlobalPopupBackdrop');
        if (backdrop && backdrop.classList.contains('active')) {
            if (e.key === 'Escape') {
                e.preventDefault();
                handleAppPopupCancel();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                handleAppPopupConfirm();
            }
        }
    });

    // Tự động kích hoạt khi có Flash message từ Backend Laravel
    @if($flashMessage)
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.showAppPopup({
                    type: @json($flashType),
                    title: @json($flashTitle),
                    message: {!! json_encode($flashMessage) !!},
                    isHtml: true
                });
            }, 100);
        });
    @endif
})();
</script>
