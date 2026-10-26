@extends('layouts.admin')

@section('title', 'Quản Lý Địa Điểm Du Lịch')
@section('breadcrumb', 'Địa điểm (Destinations)')

@push('styles')
<style>
    /* Ẩn header và breadcrumb mặc định của layout để khớp 100% giao diện ảnh chụp */
    .admin-header-bar,
    .admin-breadcrumbs {
        display: none !important;
    }

    .admin-content-body {
        padding: 24px 32px 48px 32px;
    }

    /* Thanh tiêu đề chính: Quản Lý Địa Điểm Du Lịch */
    .dest-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .dest-page-title {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
        letter-spacing: -0.01em;
    }

    /* Nút "+ Thêm Địa Điểm Mới" dạng viên thuốc màu xanh */
    .btn-add-dest {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: #2563EB;
        color: #FFFFFF;
        font-size: 13.5px;
        font-weight: 600;
        padding: 9px 20px;
        border-radius: 9999px;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-add-dest:hover {
        background-color: #1D4ED8;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
        color: #FFFFFF;
    }

    /* Breadcrumb chuẩn ảnh: Admin / Địa điểm (Destinations) */
    .dest-breadcrumb {
        font-size: 13.5px;
        color: #64748B;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .dest-breadcrumb a {
        color: #3B82F6;
        text-decoration: none;
        font-weight: 500;
    }

    .dest-breadcrumb a:hover {
        text-decoration: underline;
    }

    .dest-breadcrumb .sep {
        color: #94A3B8;
    }

    .dest-breadcrumb .current {
        color: #64748B;
    }

    /* Card chứa bộ lọc và bảng dữ liệu */
    .dest-table-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #F1F5F9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    /* Thanh bộ lọc (Filter Toolbar) */
    .dest-filter-toolbar {
        padding: 18px 24px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        border-bottom: 1px solid #F8FAFC;
    }

    .dest-search-box {
        position: relative;
        flex: 1;
        min-width: 260px;
        max-width: 380px;
    }

    .dest-search-box svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        pointer-events: none;
    }

    .dest-search-input {
        width: 100%;
        padding: 9px 14px 9px 38px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        font-size: 13px;
        color: #0F172A;
        outline: none;
        background-color: #FFFFFF;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .dest-search-input:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .dest-select-wrap {
        position: relative;
        min-width: 170px;
    }

    .dest-select {
        width: 100%;
        padding: 9px 32px 9px 14px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        font-size: 13px;
        color: #334155;
        outline: none;
        background-color: #FFFFFF;
        appearance: none;
        cursor: pointer;
        transition: border-color 0.2s;
    }

    .dest-select:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .dest-select-wrap svg {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        pointer-events: none;
    }

    /* Bảng dữ liệu địa điểm du lịch */
    .dest-data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .dest-data-table thead th {
        padding: 16px 24px;
        font-size: 13.5px;
        font-weight: 700;
        color: #0F172A;
        border-bottom: 1px solid #F1F5F9;
        background-color: #FFFFFF;
        letter-spacing: -0.01em;
        white-space: nowrap;
    }

    .dest-data-table thead th.col-actions {
        text-align: right;
    }

    .dest-data-table tbody tr {
        border-bottom: 1px solid #F8FAFC;
        transition: background-color 0.15s ease;
    }

    .dest-data-table tbody tr:hover {
        background-color: #F8FAFC;
    }

    .dest-data-table tbody tr:last-child {
        border-bottom: none;
    }

    .dest-data-table tbody td {
        padding: 16px 24px;
        vertical-align: middle;
        font-size: 13.5px;
        color: #334155;
    }

    /* Cột 1: Thông tin Địa điểm (Hình thumbnail + Tên + ID) */
    .dest-info-cell {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .dest-thumb-wrap {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        overflow: hidden;
        background-color: #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    .dest-thumb-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .dest-thumb-placeholder {
        color: #94A3B8;
        font-size: 11px;
        font-weight: 600;
    }

    .dest-name-title {
        font-size: 14px;
        font-weight: 700;
        color: #0F172A;
        line-height: 1.3;
    }

    .dest-code-subtitle {
        font-size: 12px;
        color: #94A3B8;
        margin-top: 2px;
        font-weight: 500;
    }

    /* Cột 2: Thành phố */
    .dest-city-text {
        font-size: 13.5px;
        color: #334155;
        font-weight: 500;
    }

    /* Cột 3: Danh mục Badges */
    .badge-dest-category {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 500;
        line-height: 1.4;
    }

    .badge-cat-blue {
        background-color: #EFF6FF;
        color: #2563EB;
    }

    .badge-cat-teal {
        background-color: #F0FDF4;
        color: #0D9488;
    }

    .badge-cat-orange {
        background-color: #FFFBEB;
        color: #D97706;
    }

    .badge-cat-rose {
        background-color: #FEF2F2;
        color: #EF4444;
    }

    /* Cột 4: Đánh giá Rating (Badge màu cam/vàng có ngôi sao) */
    .badge-rating-amber {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background-color: #EAB308;
        color: #FFFFFF;
        font-size: 12px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 5px;
        line-height: 1.2;
    }

    /* Cột 5: Giá vé / Phòng */
    .dest-price-text {
        font-size: 14px;
        font-weight: 700;
        color: #0F172A;
    }

    /* Cột 6: Nơi lưu trú */
    .badge-hotel-yes {
        display: inline-block;
        background-color: #DCFCE7;
        color: #16A34A;
        font-size: 11.5px;
        font-weight: 500;
        padding: 3px 10px;
        border-radius: 5px;
    }

    .text-hotel-no {
        color: #64748B;
        font-size: 13px;
        font-weight: 400;
    }

    /* Cột 7: Hành động (3 nút icon tròn: Xem, Sửa, Xóa) */
    .dest-actions-wrap {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .btn-circle-action {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #FFFFFF;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1.5px solid;
    }

    /* Nút Xem (Màu Cyan / Light Blue) */
    .btn-action-view {
        border-color: #38BDF8;
        color: #0284C7;
    }

    .btn-action-view:hover {
        background-color: #F0F9FF;
        border-color: #0284C7;
        color: #0369A1;
    }

    /* Nút Chỉnh sửa (Màu Slate / Xám) */
    .btn-action-edit {
        border-color: #94A3B8;
        color: #64748B;
    }

    .btn-action-edit:hover {
        background-color: #F1F5F9;
        border-color: #475569;
        color: #334155;
    }

    /* Nút Xóa (Màu Đỏ nhạt) */
    .btn-action-delete {
        border-color: #FCA5A5;
        color: #EF4444;
    }

    .btn-action-delete:hover {
        background-color: #FEF2F2;
        border-color: #EF4444;
        color: #DC2626;
    }

    /* Toast thông báo */
    .dest-toast {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 13.5px;
        animation: fadeIn 0.3s ease;
    }

    .dest-toast-success {
        background-color: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .dest-toast-error {
        background-color: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FECACA;
    }

    .toast-close-btn {
        background: transparent;
        border: none;
        color: currentColor;
        font-size: 16px;
        cursor: pointer;
        margin-left: 12px;
    }

    /* Phân trang */
    .dest-pagination-wrap {
        padding: 16px 24px;
        border-top: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #FFFFFF;
        flex-wrap: wrap;
        gap: 12px;
    }

    .dest-pagination-info {
        font-size: 13.5px;
        color: #64748B;
    }

    .dest-pagination-info strong {
        color: #0F172A;
        font-weight: 600;
    }

    /* Modal Styling */
    .dest-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(3px);
        z-index: 100;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .dest-modal-overlay.active {
        display: flex;
    }

    .dest-modal-card {
        background-color: #FFFFFF;
        width: 100%;
        max-width: 600px;
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        animation: modalScaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalScaleUp {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }

    .dest-modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .dest-modal-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
    }

    .dest-modal-close-btn {
        background: transparent;
        border: none;
        font-size: 18px;
        color: #94A3B8;
        cursor: pointer;
        transition: color 0.2s;
    }

    .dest-modal-close-btn:hover {
        color: #0F172A;
    }

    .dest-modal-body {
        padding: 20px 24px;
        max-height: 75vh;
        overflow-y: auto;
    }

    .form-group {
        margin-bottom: 16px;
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
        padding: 9px 13px;
        border-radius: 8px;
        border: 1px solid #CBD5E1;
        font-size: 13.5px;
        color: #0F172A;
        font-family: inherit;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .form-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
        color: #334155;
        cursor: pointer;
        user-select: none;
    }

    .dest-modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #F1F5F9;
        background-color: #F8FAFC;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary {
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #CBD5E1;
        background-color: #FFFFFF;
        color: #475569;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-secondary:hover {
        background-color: #F1F5F9;
        border-color: #94A3B8;
    }

    .btn-primary {
        padding: 8px 18px;
        border-radius: 8px;
        border: none;
        background-color: #2563EB;
        color: #FFFFFF;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary:hover {
        background-color: #1D4ED8;
    }

    .btn-danger-confirm {
        padding: 8px 18px;
        border-radius: 8px;
        border: none;
        background-color: #DC2626;
        color: #FFFFFF;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-danger-confirm:hover {
        background-color: #B91C1C;
    }
</style>
@endpush

@section('content')
<!-- Thanh Tiêu Đề và Nút Thêm Mới -->
<div class="dest-header-row">
    <h1 class="dest-page-title">Quản Lý Địa Điểm Du Lịch</h1>
    <button type="button" class="btn-add-dest" onclick="openCreateModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>+ Thêm Địa Điểm Mới</span>
    </button>
</div>

<!-- Breadcrumb: Admin / Địa điểm (Destinations) -->
<div class="dest-breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Admin</a>
    <span class="sep">/</span>
    <span class="current">Địa điểm (Destinations)</span>
</div>

<!-- Thông báo Flash Toast -->
@if(session('success'))
<div class="dest-toast dest-toast-success" id="flashToast">
    <span>{{ session('success') }}</span>
    <button type="button" class="toast-close-btn" onclick="document.getElementById('flashToast').remove()">&times;</button>
</div>
@endif

@if(session('error'))
<div class="dest-toast dest-toast-error" id="flashToast">
    <span>{{ session('error') }}</span>
    <button type="button" class="toast-close-btn" onclick="document.getElementById('flashToast').remove()">&times;</button>
</div>
@endif

@if($errors->any())
<div class="dest-toast dest-toast-error" id="flashToast">
    <span>{{ $errors->first() }}</span>
    <button type="button" class="toast-close-btn" onclick="document.getElementById('flashToast').remove()">&times;</button>
</div>
@endif

<!-- Bảng Danh Sách và Bộ Lọc -->
<div class="dest-table-card">
    <!-- Bộ lọc (Tìm kiếm, Thành phố, Danh mục) -->
    <form action="{{ route('admin.destinations.index') }}" method="GET" class="dest-filter-toolbar" id="filterForm">
        <!-- Ô tìm kiếm tên địa điểm -->
        <div class="dest-search-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text"
                   name="search"
                   class="dest-search-input"
                   placeholder="Tìm theo tên địa điểm..."
                   value="{{ $filters['search'] ?? '' }}"
                   onkeydown="if(event.key === 'Enter') document.getElementById('filterForm').submit()">
        </div>

        <!-- Bộ lọc Thành phố -->
        <div class="dest-select-wrap">
            <select name="city_id" class="dest-select" onchange="document.getElementById('filterForm').submit()">
                <option value="all" {{ ($filters['city_id'] ?? 'all') === 'all' ? 'selected' : '' }}>Tất cả thành phố</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ ($filters['city_id'] ?? '') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>

        <!-- Bộ lọc Danh mục -->
        <div class="dest-select-wrap">
            <select name="category_id" class="dest-select" onchange="document.getElementById('filterForm').submit()">
                <option value="all" {{ ($filters['category_id'] ?? 'all') === 'all' ? 'selected' : '' }}>Tất cả danh mục</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>
    </form>

    <!-- Bảng Dữ Liệu -->
    <div style="overflow-x: auto;">
        <table class="dest-data-table">
            <thead>
                <tr>
                    <th style="min-width: 260px;">Địa điểm</th>
                    <th style="min-width: 140px;">Thành phố</th>
                    <th style="min-width: 130px;">Danh mục</th>
                    <th style="min-width: 100px;">Đánh giá</th>
                    <th style="min-width: 140px;">Giá vé / Phòng</th>
                    <th style="min-width: 120px;">Nơi lưu trú</th>
                    <th class="col-actions" style="min-width: 130px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($destinations as $destination)
                @php
                    $primaryImg = $destination->primaryImage?->image_path ?? $destination->images->first()?->image_path;
                    $destCode = sprintf('#DST-%03d', $destination->id);
                    $categoryName = $destination->category?->name ?? 'Khác';

                    // Chọn kiểu màu cho Badge danh mục
                    $badgeCatClass = 'badge-cat-blue';
                    if (str_contains(mb_strtolower($categoryName), 'biển') || str_contains(mb_strtolower($categoryName), 'đảo')) {
                        $badgeCatClass = 'badge-cat-teal';
                    } elseif (str_contains(mb_strtolower($categoryName), 'di sản') || str_contains(mb_strtolower($categoryName), 'văn hóa')) {
                        $badgeCatClass = 'badge-cat-rose';
                    } elseif (str_contains(mb_strtolower($categoryName), 'núi') || str_contains(mb_strtolower($categoryName), 'rừng')) {
                        $badgeCatClass = 'badge-cat-orange';
                    }
                @endphp
                <tr>
                    <!-- Cột 1: Địa điểm (Thumbnail + Tên + ID) -->
                    <td>
                        <div class="dest-info-cell">
                            <div class="dest-thumb-wrap">
                                @if(!empty($primaryImg))
                                    <img src="{{ $primaryImg }}" alt="{{ $destination->name }}" class="dest-thumb-img" onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=150&q=80'">
                                @else
                                    <span class="dest-thumb-placeholder">NO IMG</span>
                                @endif
                            </div>
                            <div>
                                <div class="dest-name-title">{{ $destination->name }}</div>
                                <div class="dest-code-subtitle">ID: {{ $destCode }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- Cột 2: Thành phố -->
                    <td>
                        <span class="dest-city-text">{{ $destination->city?->name ?? 'Chưa xác định' }}</span>
                    </td>

                    <!-- Cột 3: Danh mục -->
                    <td>
                        <span class="badge-dest-category {{ $badgeCatClass }}">
                            {{ $categoryName }}
                        </span>
                    </td>

                    <!-- Cột 4: Đánh giá -->
                    <td>
                        <span class="badge-rating-amber">
                            ★ {{ number_format((float) $destination->rating, 1) }}
                        </span>
                    </td>

                    <!-- Cột 5: Giá vé / Phòng -->
                    <td>
                        <span class="dest-price-text">
                            {{ $destination->entrance_fee > 0 ? number_format((float) $destination->entrance_fee, 0, ',', '.') . 'đ' : 'Miễn phí' }}
                        </span>
                    </td>

                    <!-- Cột 6: Nơi lưu trú -->
                    <td>
                        @if($destination->has_hotel)
                            <span class="badge-hotel-yes">Có khách sạn</span>
                        @else
                            <span class="text-hotel-no">Không</span>
                        @endif
                    </td>

                    <!-- Cột 7: Hành động (3 nút icon: Xem, Sửa, Xóa) -->
                    <td>
                        <div class="dest-actions-wrap">
                            <!-- Nút Xem Chi Tiết (Eye - Cyan) -->
                            <button type="button"
                                    class="btn-circle-action btn-action-view"
                                    title="Xem chi tiết"
                                    onclick="openViewModal({{ $destination->id }})">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>

                            <!-- Nút Chỉnh Sửa (Pencil - Slate) -->
                            <button type="button"
                                    class="btn-circle-action btn-action-edit"
                                    title="Chỉnh sửa địa điểm"
                                    onclick="openEditModal({{ $destination->id }})">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path>
                                    <path d="m15 5 4 4"></path>
                                </svg>
                            </button>

                            <!-- Nút Xóa (Trash - Red) -->
                            <button type="button"
                                    class="btn-circle-action btn-action-delete"
                                    title="Xóa địa điểm"
                                    onclick="openDeleteModal({{ $destination->id }}, '{{ addslashes($destination->name) }}')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 48px; color: #94A3B8;">
                        Không tìm thấy địa điểm du lịch nào phù hợp với bộ lọc.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Phân trang -->
        @if($destinations->hasPages())
        <div class="dest-pagination-wrap">
            <div class="dest-pagination-info">
                Hiển thị <strong>{{ $destinations->firstItem() }}–{{ $destinations->lastItem() }}</strong> trong tổng số <strong>{{ $destinations->total() }}</strong> địa điểm
            </div>
            <div class="dest-pagination-controls">
                {{ $destinations->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- ================= MODAL 1: THÊM ĐỊA ĐIỂM MỚI ================= -->
<div class="dest-modal-overlay" id="createModal">
    <div class="dest-modal-card">
        <form action="{{ route('admin.destinations.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="dest-modal-header">
                <h3 class="dest-modal-title">Thêm Địa Điểm Du Lịch Mới</h3>
                <button type="button" class="dest-modal-close-btn" onclick="closeModal('createModal')">&times;</button>
            </div>
            <div class="dest-modal-body">
                <div class="form-group">
                    <label class="form-label">Tên địa điểm du lịch <span style="color: #EF4444;">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Ví dụ: Tên khu du lịch, di tích, bãi biển..." required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Tỉnh / Thành phố <span style="color: #EF4444;">*</span></label>
                        <select name="city_id" class="form-control" required>
                            <option value="">-- Chọn thành phố --</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Danh mục trải nghiệm <span style="color: #EF4444;">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Giá vé / Giá phòng (VNĐ)</label>
                        <input type="number" name="entrance_fee" class="form-control" placeholder="0" min="0" step="1000">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Đánh giá ban đầu (0.0 - 5.0)</label>
                        <input type="number" name="rating" class="form-control" placeholder="5.0" min="0" max="5" step="0.1" value="5.0">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Giờ mở cửa (HH:mm)</label>
                        <input type="time" name="opening_time" class="form-control" value="08:00">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Giờ đóng cửa (HH:mm)</label>
                        <input type="time" name="closing_time" class="form-control" value="22:00">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Địa chỉ chi tiết</label>
                    <input type="text" name="address" class="form-control" placeholder="Ví dụ: Thôn An Sơn, Xã Hòa Ninh, Huyện Hòa Vang, Đà Nẵng">
                </div>

                <div class="form-group">
                    <label class="form-label">Đường dẫn hình ảnh (URL)</label>
                    <input type="url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/...">
                </div>

                <div class="form-group">
                    <label class="form-label">Hoặc tải lên hình ảnh từ máy tính (Tối đa 2MB)</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả giới thiệu</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả về nét độc đáo của địa điểm..."></textarea>
                </div>

                <div class="form-grid-2" style="margin-top: 10px;">
                    <div>
                        <label class="form-checkbox-label">
                            <input type="checkbox" name="has_hotel" value="1" checked>
                            <span>Có khách sạn / nơi lưu trú</span>
                        </label>
                    </div>
                    <div>
                        <label class="form-checkbox-label">
                            <input type="checkbox" name="is_featured" value="1">
                            <span>Đánh dấu là điểm đến nổi bật</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="dest-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('createModal')">Hủy bỏ</button>
                <button type="submit" class="btn-primary">Lưu Địa Điểm</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 2: CHỈNH SỬA ĐỊA ĐIỂM ================= -->
<div class="dest-modal-overlay" id="editModal">
    <div class="dest-modal-card">
        <form id="editDestForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="dest-modal-header">
                <h3 class="dest-modal-title">Chỉnh Sửa Địa Điểm Du Lịch</h3>
                <button type="button" class="dest-modal-close-btn" onclick="closeModal('editModal')">&times;</button>
            </div>
            <div class="dest-modal-body">
                <div class="form-group">
                    <label class="form-label">Tên địa điểm du lịch <span style="color: #EF4444;">*</span></label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Tỉnh / Thành phố <span style="color: #EF4444;">*</span></label>
                        <select name="city_id" id="edit_city_id" class="form-control" required>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Danh mục trải nghiệm <span style="color: #EF4444;">*</span></label>
                        <select name="category_id" id="edit_category_id" class="form-control" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Giá vé / Giá phòng (VNĐ)</label>
                        <input type="number" name="entrance_fee" id="edit_entrance_fee" class="form-control" min="0" step="1000">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Đánh giá (0.0 - 5.0)</label>
                        <input type="number" name="rating" id="edit_rating" class="form-control" min="0" max="5" step="0.1">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Giờ mở cửa (HH:mm)</label>
                        <input type="time" name="opening_time" id="edit_opening_time" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Giờ đóng cửa (HH:mm)</label>
                        <input type="time" name="closing_time" id="edit_closing_time" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Địa chỉ chi tiết</label>
                    <input type="text" name="address" id="edit_address" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Đường dẫn hình ảnh (URL)</label>
                    <input type="url" name="image_url" id="edit_image_url" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Thay đổi ảnh bằng tệp tải lên (Tối đa 2MB)</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả giới thiệu</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-grid-2" style="margin-top: 10px;">
                    <div>
                        <label class="form-checkbox-label">
                            <input type="checkbox" name="has_hotel" id="edit_has_hotel" value="1">
                            <span>Có khách sạn / nơi lưu trú</span>
                        </label>
                    </div>
                    <div>
                        <label class="form-checkbox-label">
                            <input type="checkbox" name="is_featured" id="edit_is_featured" value="1">
                            <span>Đánh dấu là điểm đến nổi bật</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="dest-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Hủy bỏ</button>
                <button type="submit" class="btn-primary">Cập Nhật Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL 3: XEM CHI TIẾT ĐỊA ĐIỂM ================= -->
<div class="dest-modal-overlay" id="viewModal">
    <div class="dest-modal-card">
        <div class="dest-modal-header">
            <h3 class="dest-modal-title" id="view_title">Chi Tiết Địa Điểm Du Lịch</h3>
            <button type="button" class="dest-modal-close-btn" onclick="closeModal('viewModal')">&times;</button>
        </div>
        <div class="dest-modal-body" id="view_content_body">
            <!-- Được tải động qua JS -->
            <div style="text-align: center; padding: 24px; color: #94A3B8;">Đang tải thông tin...</div>
        </div>
        <div class="dest-modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('viewModal')">Đóng</button>
        </div>
    </div>
</div>

<!-- ================= MODAL 4: XÁC NHẬN XÓA ================= -->
<div class="dest-modal-overlay" id="deleteModal">
    <div class="dest-modal-card" style="max-width: 440px;">
        <form id="deleteDestForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="dest-modal-header">
                <h3 class="dest-modal-title" style="color: #DC2626;">Xác Nhận Xóa Địa Điểm</h3>
                <button type="button" class="dest-modal-close-btn" onclick="closeModal('deleteModal')">&times;</button>
            </div>
            <div class="dest-modal-body">
                <p style="font-size: 14px; color: #334155; line-height: 1.5;">
                    Bạn có chắc chắn muốn xóa địa điểm <strong id="delete_dest_name"></strong> khỏi hệ thống?
                </p>
                <div style="background-color: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 10px 14px; margin-top: 14px; font-size: 12.5px; color: #991B1B;">
                    <strong>Lưu ý BR-17:</strong> Địa điểm sẽ không thể xóa nếu đang có hoạt động lịch trình du lịch hoặc đơn đặt phòng lưu trú liên kết.
                </div>
            </div>
            <div class="dest-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteModal')">Hủy bỏ</button>
                <button type="submit" class="btn-danger-confirm">Xác Nhận Xóa</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function openCreateModal() {
        openModal('createModal');
    }

    function openDeleteModal(id, name) {
        document.getElementById('deleteDestForm').action = "{{ url('admin/destinations') }}/" + id;
        document.getElementById('delete_dest_name').innerText = '"' + name + '"';
        openModal('deleteModal');
    }

    function openEditModal(id) {
        fetch("{{ url('admin/destinations') }}/" + id, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                const data = response.data;
                document.getElementById('editDestForm').action = "{{ url('admin/destinations') }}/" + data.id;
                document.getElementById('edit_name').value = data.name;
                document.getElementById('edit_city_id').value = data.city_id;
                document.getElementById('edit_category_id').value = data.category_id;
                document.getElementById('edit_entrance_fee').value = data.entrance_fee;
                document.getElementById('edit_rating').value = data.rating;
                document.getElementById('edit_opening_time').value = data.opening_time;
                document.getElementById('edit_closing_time').value = data.closing_time;
                document.getElementById('edit_address').value = data.address || '';
                document.getElementById('edit_image_url').value = data.primary_image || '';
                document.getElementById('edit_description').value = data.description || '';
                document.getElementById('edit_has_hotel').checked = !!data.has_hotel;
                document.getElementById('edit_is_featured').checked = !!data.is_featured;

                openModal('editModal');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Không thể tải thông tin địa điểm.');
        });
    }

    function openViewModal(id) {
        const body = document.getElementById('view_content_body');
        body.innerHTML = '<div style="text-align: center; padding: 24px; color: #94A3B8;">Đang tải thông tin...</div>';
        openModal('viewModal');

        fetch("{{ url('admin/destinations') }}/" + id, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === 'success') {
                const d = response.data;
                document.getElementById('view_title').innerText = d.name + ' (' + d.code + ')';

                let imgHtml = d.primary_image
                    ? `<img src="${d.primary_image}" style="width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; margin-bottom: 16px;" onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80'">`
                    : '';

                let hotelBadge = d.has_hotel
                    ? '<span class="badge-hotel-yes">Có khách sạn</span>'
                    : '<span class="text-hotel-no">Không</span>';

                body.innerHTML = `
                    ${imgHtml}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; font-size: 13.5px;">
                        <div><strong>Thành phố:</strong> ${d.city_name || 'N/A'}</div>
                        <div><strong>Danh mục:</strong> ${d.category_name || 'N/A'}</div>
                        <div><strong>Đánh giá:</strong> <span class="badge-rating-amber">★ ${d.rating.toFixed(1)}</span></div>
                        <div><strong>Giá vé / Phòng:</strong> <span style="font-weight: 700; color: #0F172A;">${d.entrance_fee_formatted}</span></div>
                        <div><strong>Giờ mở cửa:</strong> ${d.opening_time || 'Cả ngày'} - ${d.closing_time || '24/24'}</div>
                        <div><strong>Nơi lưu trú:</strong> ${hotelBadge}</div>
                    </div>
                    <div style="font-size: 13.5px; margin-bottom: 10px;">
                        <strong>Địa chỉ:</strong> ${d.address || 'Chưa cập nhật'}
                    </div>
                    <div style="font-size: 13.5px; color: #475569; line-height: 1.5; background: #F8FAFC; padding: 12px; border-radius: 8px;">
                        <strong>Mô tả:</strong> ${d.description || 'Không có mô tả chi tiết.'}
                    </div>
                `;
            }
        })
        .catch(err => {
            body.innerHTML = '<div style="color: #DC2626; padding: 16px;">Không thể tải dữ liệu chi tiết.</div>';
        });
    }

    // Đóng modal khi bấm ra ngoài nền xám
    window.addEventListener('click', function(e) {
        document.querySelectorAll('.dest-modal-overlay').forEach(function(modal) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
</script>
@endsection
