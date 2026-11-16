@php
    $merchant = $merchant ?? false;
    $targetUrl = route($merchant ? 'commerce.merchant' : 'commerce.shopping');
    $title = $merchant ? 'Merchant Agent' : 'Travel Agent';
@endphp

<!-- Resizable AI Assistant Split Screen Panel (Phong cách sang trọng, tông trắng tinh tế, font Times New Roman) -->
<div id="commerceSidePanel" class="commerce-side-panel" role="complementary" aria-label="AI Travel Assistant Panel">
    <!-- Thanh kéo kích thước chia đôi giữa 2 màn hình (Center Resizer Handle with Grip) -->
    <div id="commerceResizeHandle" class="commerce-resize-handle" title="Bấm giữ và kéo sang trái/phải để điều chỉnh tỷ lệ 2 màn hình">
        <div class="commerce-resize-grip" aria-hidden="true"></div>
    </div>

    <!-- Tấm chắn sự kiện chuột (Pointer Shield) ngăn Iframe nuốt chuột khi đang kéo rê -->
    <div id="commercePointerShield" class="commerce-pointer-shield" aria-hidden="true"></div>

    <!-- Thanh Header Trợ lý AI -->
    <div class="commerce-panel-header">
        <div class="commerce-header-left">
            <span class="commerce-agent-wordmark">Trợ lý AI</span>
            <span class="commerce-beta-badge">BETA</span>
        </div>
        <div class="commerce-header-right">
            <!-- Nút chuyển đổi chế độ: Đè lên (Overlay - màn hình chính full) vs Chia đôi (Split) -->
            <button type="button"
                    id="commerceDisplayModeBtn"
                    class="commerce-header-icon-btn"
                    onclick="toggleDisplayMode()"
                    aria-label="Chuyển chế độ hiển thị"
                    title="Chuyển sang chế độ Đè lên (Màn hình chính full)">
                <svg id="commerceIconModeOverlay" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="13" height="16" rx="2"></rect>
                    <rect x="9" y="8" width="13" height="12" rx="2" fill="currentColor" fill-opacity="0.18"></rect>
                </svg>
                <svg id="commerceIconModeSplit" style="display: none;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                    <line x1="14" y1="3" x2="14" y2="21"></line>
                </svg>
            </button>
            <!-- Mở toàn màn hình trong tab mới -->
            <a href="{{ $targetUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="commerce-header-icon-btn"
               title="Mở toàn màn hình trong tab mới">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
            </a>
            <!-- Nút đóng / Thu hồi màn hình chia đôi -->
            <button type="button"
                    class="commerce-header-icon-btn commerce-close-btn"
                    onclick="closeCommercePanel()"
                    aria-label="Đóng chia đôi màn hình"
                    title="Đóng chế độ chia đôi (ESC)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>

    <!-- Vùng thân chứa Iframe ứng dụng Commerce Agents -->
    <div class="commerce-panel-body">
        <div id="commercePanelLoading" class="commerce-panel-loading">
            <div class="commerce-panel-spinner"></div>
            <span>Đang tải trợ lý…</span>
        </div>
        <iframe id="commerceIframe"
                data-src="{{ $targetUrl }}"
                title="{{ $title }}"
                class="commerce-panel-iframe"
                onload="onCommerceIframeLoaded()">
        </iframe>
    </div>
</div>

<!-- Lớp phủ mờ nhẹ chỉ trên thiết bị di động hẹp (Mobile Backdrop) -->
<div id="commerceMobileBackdrop" class="commerce-mobile-backdrop" onclick="closeCommercePanel()" aria-hidden="true"></div>

<style>
    :root {
        --commerce-split-width: 36vw;
    }

    /* Khi kích hoạt AI Assistant (body.commerce-split-active): Ẩn hoàn toàn nút launcher Trợ lý AI Beta */
    body.commerce-split-active .commerce-floating-launcher {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        transform: scale(0.85) translateY(12px) !important;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
    }

    /* 1. Màn hình bên trái (Web chính): Tự động co lại khi ở chế độ chia 2 màn hình (Split Mode) */
    @media (min-width: 768px) {
        body.commerce-split-active:not(.commerce-mode-overlay) {
            width: calc(100vw - var(--commerce-split-width, 36vw)) !important;
            max-width: calc(100vw - var(--commerce-split-width, 36vw)) !important;
            margin-right: var(--commerce-split-width, 36vw) !important;
            transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1), margin-right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            overflow-x: hidden !important;
        }

        body.commerce-split-active:not(.commerce-mode-overlay).is-resizing {
            transition: none !important;
        }

        /* Chế độ Đè lên (Overlay Mode): Màn hình chính luôn full 100% không bị co lại */
        body.commerce-split-active.commerce-mode-overlay {
            width: 100% !important;
            max-width: 100% !important;
            margin-right: 0 !important;
            transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1), margin-right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
    }

    /* 2. Màn hình bên phải (Trợ lý AI Agent) */
    .commerce-side-panel {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        z-index: 99998;
        width: var(--commerce-split-width, 50vw);
        min-width: 320px;
        max-width: 75vw;
        background: #ffffff;
        border-left: 1px solid #cbd5e1;
        box-shadow: -4px 0 28px rgba(0, 0, 0, 0.08), -1px 0 0 rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
        font-family: 'Times New Roman', Times, Georgia, serif;
    }

    .commerce-side-panel * {
        font-family: 'Times New Roman', Times, Georgia, serif;
    }

    .commerce-side-panel.is-open {
        transform: translateX(0);
    }

    /* Chế độ Đè lên: Panel AI đè lên trên trang chính với z-index cao hơn và bóng đổ nổi bật */
    .commerce-side-panel.is-open.is-overlay-mode {
        z-index: 99999 !important;
        box-shadow: -8px 0 36px rgba(0, 0, 0, 0.18), -2px 0 8px rgba(0, 0, 0, 0.08) !important;
        border-left: 1px solid rgba(203, 213, 225, 0.9) !important;
    }

    /* Khi đang kéo chuột (resizing), bỏ transition để phản hồi zero-lag mượt mà 60fps */
    .commerce-side-panel.is-resizing {
        transition: none !important;
    }

    /* 3. Thanh kéo chia đôi giữa 2 màn hình (Center Resizer Handle) */
    .commerce-resize-handle {
        position: absolute;
        top: 0;
        bottom: 0;
        left: -8px;
        width: 16px;
        cursor: col-resize;
        z-index: 30;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        transition: background 0.15s ease;
    }

    .commerce-resize-handle::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 7px;
        width: 2px;
        background: transparent;
        transition: background 0.2s ease;
    }

    .commerce-resize-handle:hover::before,
    .commerce-side-panel.is-resizing .commerce-resize-handle::before {
        background: #2563eb;
    }

    /* Vạch gờ cầm (Grip bar) ở giữa thanh kéo */
    .commerce-resize-grip {
        width: 4.5px;
        height: 42px;
        border-radius: 9999px;
        background: #94a3b8;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.18);
        transition: all 0.2s ease;
        pointer-events: none;
    }

    .commerce-resize-handle:hover .commerce-resize-grip,
    .commerce-side-panel.is-resizing .commerce-resize-grip {
        background: #2563eb;
        height: 52px;
        width: 5.5px;
        box-shadow: 0 0 10px rgba(37, 99, 235, 0.5);
    }

    /* 4. Tấm chắn sự kiện chuột (Pointer Shield) trong khi kéo */
    .commerce-pointer-shield {
        position: absolute;
        inset: 0;
        z-index: 40;
        display: none;
        cursor: col-resize;
    }

    /* 5. Thanh Header */
    .commerce-panel-header {
        height: 50px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        flex-shrink: 0;
    }

    .commerce-header-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .commerce-agent-wordmark {
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #0f172a;
    }

    .commerce-beta-badge {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.05em;
        padding: 2.5px 6.5px;
        border-radius: 4px;
        background: #2563eb;
        color: #ffffff;
        text-transform: uppercase;
        display: inline-block;
        line-height: 1.2;
    }

    .commerce-header-right {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .commerce-header-icon-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        color: #64748b;
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .commerce-header-icon-btn:hover {
        color: #0f172a;
        background: #f1f5f9;
    }

    .commerce-close-btn:hover {
        color: #dc2626;
        background: #fee2e2;
    }

    /* 6. Vùng thân Iframe */
    .commerce-panel-body {
        flex: 1;
        position: relative;
        background: #ffffff;
        overflow: hidden;
    }

    .commerce-panel-iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
        background: #ffffff;
    }

    /* Màn hình loading */
    .commerce-panel-loading {
        position: absolute;
        inset: 0;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: #64748b;
        font-size: 14px;
        z-index: 5;
        transition: opacity 0.2s ease;
    }

    .commerce-panel-spinner {
        width: 28px;
        height: 28px;
        border: 2.5px solid #e2e8f0;
        border-top-color: #2563eb;
        border-radius: 50%;
        animation: commercePanelSpin 0.7s linear infinite;
    }

    @keyframes commercePanelSpin {
        to { transform: rotate(360deg); }
    }

    /* 7. Lớp phủ trên màn hình di động hẹp (< 768px) */
    .commerce-mobile-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(2px);
        z-index: 99997;
    }

    @media (max-width: 767px) {
        .commerce-side-panel {
            width: 100vw !important;
            max-width: 100vw;
        }

        .commerce-resize-handle {
            display: none !important;
        }

        .commerce-side-panel.is-open ~ .commerce-mobile-backdrop {
            display: block;
        }
    }
</style>

<script>
    (function () {
        const STORAGE_KEY = 'travel_planner_agent_split_width_v3';
        const MODE_STORAGE_KEY = 'tp_ai_display_mode'; // 'split' | 'overlay'
        const MIN_WIDTH = 340;
        const MIN_RATIO = 0.25;
        const MAX_RATIO = 0.75;
        const DEFAULT_RATIO = 0.36; // Mặc định mở ra chiếm ~36% (khớp chính xác giao diện Hình 1, chừa 64% cho 4 cột card bên trái)

        let isDragging = false;
        let startX = 0;
        let startWidth = 0;

        function getDisplayMode() {
            try {
                return localStorage.getItem(MODE_STORAGE_KEY) || 'split';
            } catch (e) {
                return 'split';
            }
        }

        function applyDisplayMode(mode) {
            const panel = document.getElementById('commerceSidePanel');
            const btn = document.getElementById('commerceDisplayModeBtn');
            const iconOverlay = document.getElementById('commerceIconModeOverlay');
            const iconSplit = document.getElementById('commerceIconModeSplit');

            if (mode === 'overlay') {
                if (panel && panel.classList.contains('is-open')) {
                    document.body.classList.add('commerce-mode-overlay');
                }
                if (panel) panel.classList.add('is-overlay-mode');
                if (btn) btn.setAttribute('title', 'Chuyển sang chế độ Chia đôi màn hình (Split Screen)');
                if (iconOverlay) iconOverlay.style.display = 'none';
                if (iconSplit) iconSplit.style.display = 'block';
            } else {
                document.body.classList.remove('commerce-mode-overlay');
                if (panel) panel.classList.remove('is-overlay-mode');
                if (btn) btn.setAttribute('title', 'Chuyển sang chế độ Đè lên (Màn hình chính full)');
                if (iconOverlay) iconOverlay.style.display = 'block';
                if (iconSplit) iconSplit.style.display = 'none';
            }
        }

        window.toggleDisplayMode = function () {
            const currentMode = getDisplayMode();
            const newMode = currentMode === 'overlay' ? 'split' : 'overlay';
            try {
                localStorage.setItem(MODE_STORAGE_KEY, newMode);
            } catch (e) {}
            applyDisplayMode(newMode);
            const panel = document.getElementById('commerceSidePanel');
            if (panel && panel.classList.contains('is-open')) {
                if (newMode === 'overlay') {
                    document.body.classList.add('commerce-mode-overlay');
                } else {
                    document.body.classList.remove('commerce-mode-overlay');
                }
            }
        };

        function getInitialWidth() {
            if (window.innerWidth < 768) {
                return window.innerWidth;
            }
            try {
                const saved = parseInt(localStorage.getItem(STORAGE_KEY), 10);
                const minW = Math.max(MIN_WIDTH, Math.floor(window.innerWidth * MIN_RATIO));
                const maxW = Math.floor(window.innerWidth * MAX_RATIO);
                if (saved && saved >= minW && saved <= maxW) {
                    return saved;
                }
            } catch (e) {}

            return Math.floor(window.innerWidth * DEFAULT_RATIO);
        }

        function applyPanelWidth(width, save = true) {
            const panel = document.getElementById('commerceSidePanel');
            if (!panel) return;
            let clamped = width;
            if (window.innerWidth >= 768) {
                const minW = Math.max(MIN_WIDTH, Math.floor(window.innerWidth * MIN_RATIO));
                const maxW = Math.floor(window.innerWidth * MAX_RATIO);
                clamped = Math.min(Math.max(width, minW), maxW);
            } else {
                clamped = window.innerWidth;
            }

            document.documentElement.style.setProperty('--commerce-split-width', clamped + 'px');
            panel.style.width = clamped + 'px';

            if (save && window.innerWidth >= 768) {
                try {
                    localStorage.setItem(STORAGE_KEY, clamped);
                } catch (e) {}
            }
        }

        window.setSplitRatio = function (ratio) {
            const targetWidth = Math.floor(window.innerWidth * ratio);
            applyPanelWidth(targetWidth, true);
        };

        document.addEventListener('DOMContentLoaded', function () {
            applyPanelWidth(getInitialWidth(), false);
            applyDisplayMode(getDisplayMode());
            setupResizeListeners();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) {
                applyPanelWidth(getInitialWidth(), false);
            }
        });

        function setupResizeListeners() {
            const handle = document.getElementById('commerceResizeHandle');
            const panel = document.getElementById('commerceSidePanel');
            const shield = document.getElementById('commercePointerShield');
            if (!handle || !panel) return;

            function onPointerDown(e) {
                if (window.innerWidth < 768) return;
                e.preventDefault();
                isDragging = true;
                startX = e.clientX || (e.touches && e.touches[0].clientX);
                startWidth = panel.getBoundingClientRect().width;

                panel.classList.add('is-resizing');
                document.body.classList.add('is-resizing');
                if (shield) shield.style.display = 'block';

                document.addEventListener('mousemove', onPointerMove);
                document.addEventListener('mouseup', onPointerUp);
                document.addEventListener('touchmove', onTouchMove, { passive: false });
                document.addEventListener('touchend', onPointerUp);
            }

            function onPointerMove(e) {
                if (!isDragging) return;
                const clientX = e.clientX;
                const delta = startX - clientX;
                applyPanelWidth(startWidth + delta, true);
            }

            function onTouchMove(e) {
                if (!isDragging) return;
                e.preventDefault();
                const clientX = e.touches[0].clientX;
                const delta = startX - clientX;
                applyPanelWidth(startWidth + delta, true);
            }

            function onPointerUp() {
                if (!isDragging) return;
                isDragging = false;
                panel.classList.remove('is-resizing');
                document.body.classList.remove('is-resizing');
                if (shield) shield.style.display = 'none';

                document.removeEventListener('mousemove', onPointerMove);
                document.removeEventListener('mouseup', onPointerUp);
                document.removeEventListener('touchmove', onTouchMove);
                document.removeEventListener('touchend', onPointerUp);
            }

            handle.addEventListener('mousedown', onPointerDown);
            handle.addEventListener('touchstart', onPointerDown, { passive: false });
        }

        function updateLauncherActiveState(isOpen) {
            const launcher = document.getElementById('commerceFloatingLauncher');
            if (!launcher) return;
            if (isOpen) {
                launcher.classList.add('is-active');
                launcher.style.opacity = '0';
                launcher.style.visibility = 'hidden';
                launcher.style.pointerEvents = 'none';
            } else {
                launcher.classList.remove('is-active');
                launcher.style.opacity = '';
                launcher.style.visibility = '';
                launcher.style.pointerEvents = '';
            }
        }

        window.openCommercePanel = function (event) {
            if (event) event.preventDefault();
            const panel = document.getElementById('commerceSidePanel');
            const iframe = document.getElementById('commerceIframe');
            if (!panel || !iframe) return;

            if (!iframe.src || iframe.src === 'about:blank' || iframe.src === window.location.href) {
                const targetSrc = iframe.getAttribute('data-src');
                if (targetSrc) {
                    iframe.src = targetSrc;
                }
            }

            applyPanelWidth(getInitialWidth(), false);
            panel.classList.add('is-open');
            document.body.classList.add('commerce-split-active');
            applyDisplayMode(getDisplayMode());
            updateLauncherActiveState(true);
        };

        window.closeCommercePanel = function () {
            const panel = document.getElementById('commerceSidePanel');
            if (panel) {
                panel.classList.remove('is-open');
            }
            document.body.classList.remove('commerce-split-active');
            document.body.classList.remove('commerce-mode-overlay');
            updateLauncherActiveState(false);
        };

        window.toggleCommercePanel = function (event) {
            if (event) event.preventDefault();
            const panel = document.getElementById('commerceSidePanel');
            if (panel && panel.classList.contains('is-open')) {
                closeCommercePanel();
            } else {
                openCommercePanel(event);
            }
        };

        window.openCommerceModal = window.openCommercePanel;
        window.closeCommerceModal = window.closeCommercePanel;
        window.toggleCommerceModal = window.toggleCommercePanel;

        window.onCommerceIframeLoaded = function () {
            const loading = document.getElementById('commercePanelLoading');
            if (loading) {
                loading.style.opacity = '0';
                setTimeout(function () {
                    loading.style.display = 'none';
                }, 200);
            }
        };

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                const panel = document.getElementById('commerceSidePanel');
                if (panel && panel.classList.contains('is-open')) {
                    closeCommercePanel();
                }
            }
        });
    })();
</script>
