<!DOCTYPE html>
<html lang="vi" data-signed-in="{{ auth()->check() ? 'true' : 'false' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base target="_top">
    <title>{{ $merchant ? 'Merchant Agent' : 'Trợ lý du lịch' }} · Travel Planner</title>
    <style>
        html, body {
            background-color: #ffffff !important;
            color: #0f172a !important;
            font-family: 'Times New Roman', Times, Georgia, serif !important;
            margin: 0;
            padding: 0;
            height: 100%;
        }
        *, ::before, ::after {
            font-family: 'Times New Roman', Times, Georgia, serif !important;
        }
    </style>
    @viteReactRefresh
    @vite($merchant ? 'resources/js/commerce/merchant.tsx' : 'resources/js/commerce/shopping.tsx')
</head>
<body style="background: #ffffff; color: #0f172a; font-family: 'Times New Roman', Times, Georgia, serif;">
    <div id="commerce-root"><p style="padding: 24px; font-family: 'Times New Roman', Times, Georgia, serif; color: #64748b;" role="status">Đang tải Commerce Agents…</p></div>
    <noscript>Bật JavaScript để sử dụng giao diện Commerce Agents. <a href="/" target="_top">Về Travel Planner</a></noscript>
    @include('partials.search-suggestions')
</body>
</html>
