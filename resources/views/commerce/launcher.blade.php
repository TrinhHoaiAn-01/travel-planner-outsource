@php
    // Không bao giờ render launcher bên trong trang con của chính Commerce Agent
    if (request()->routeIs('commerce.*')) {
        return;
    }
    $merchant = $merchant ?? false;
@endphp

<!-- Nút kích hoạt nhanh Trợ lý AI (Góc dưới bên phải, tông trắng chuyên nghiệp, font Times New Roman) -->
<button type="button"
        id="commerceFloatingLauncher"
        onclick="toggleCommercePanel(event)"
        aria-label="Mở Trợ lý AI"
        class="commerce-floating-launcher"
        title="Mở Trợ lý AI (Chia đôi 2 màn hình)">
    <span class="commerce-launcher-icon">✦</span>
    <span class="commerce-launcher-text">Trợ lý AI</span>
    <span class="commerce-launcher-beta">BETA</span>
</button>

<style>
    .commerce-floating-launcher {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 99990;
        background: #ffffff;
        color: #0f172a;
        padding: 10px 18px;
        border-radius: 9999px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05);
        font-family: 'Times New Roman', Times, Georgia, serif;
        font-size: 14.5px;
        font-weight: 700;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
    }

    .commerce-floating-launcher * {
        font-family: 'Times New Roman', Times, Georgia, serif;
    }

    .commerce-floating-launcher:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 8px 26px rgba(0, 0, 0, 0.12);
    }

    /* Trạng thái khi chế độ chia đôi 2 màn hình đang mở */
    .commerce-floating-launcher.is-active {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #1d4ed8;
        box-shadow: 0 6px 22px rgba(37, 99, 235, 0.2);
    }

    .commerce-floating-launcher.is-active:hover {
        background: #dbeafe;
    }

    .commerce-launcher-icon {
        color: #2563eb;
        font-size: 14px;
        line-height: 1;
        transition: transform 0.25s ease;
    }

    .commerce-floating-launcher:hover .commerce-launcher-icon {
        transform: scale(1.2) rotate(15deg);
    }

    .commerce-launcher-text {
        color: #0f172a;
        font-weight: 700;
    }

    .commerce-floating-launcher.is-active .commerce-launcher-text {
        color: #1d4ed8;
    }

    .commerce-launcher-beta {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        background: #2563eb;
        color: #ffffff;
        letter-spacing: 0.05em;
        line-height: 1.2;
    }

    @media (max-width: 640px) {
        .commerce-floating-launcher {
            right: 16px;
            bottom: 16px;
            padding: 9px 14px;
            font-size: 13.5px;
        }
    }
</style>

@include('commerce.modal', ['merchant' => $merchant])
