@extends('layouts.admin')

@section('title', 'Quản Lý Danh Mục Du Lịch')
@section('breadcrumb', 'Danh mục')

@push('styles')
<style>
    /* Ẩn header và breadcrumb mặc định của layout để khớp 100% bố cục ảnh chụp */
    .admin-header-bar,
    .admin-breadcrumbs {
        display: none !important;
    }

    .admin-content-body {
        padding: 24px 32px 48px 32px;
    }

    /* Thanh tiêu đề trang Quản Lý Danh Mục Du Lịch */
    .category-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .category-page-title {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
        letter-spacing: -0.01em;
    }

    .btn-add-category {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: #2563EB;
        color: #FFFFFF;
        font-size: 13.5px;
        font-weight: 600;
        padding: 9px 18px;
        border-radius: 9999px;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
        transition: all 0.2s ease;
    }

    .btn-add-category:hover {
        background-color: #1D4ED8;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
    }

    /* Breadcrumb chuẩn ảnh: Admin / Danh mục */
    .category-breadcrumb {
        font-size: 13.5px;
        color: #64748B;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .category-breadcrumb a {
        color: #3B82F6;
        text-decoration: none;
        font-weight: 500;
    }

    .category-breadcrumb a:hover {
        text-decoration: underline;
    }

    .category-breadcrumb .sep {
        color: #94A3B8;
    }

    .category-breadcrumb .current {
        color: #64748B;
    }

    /* Thẻ Card chứa Bảng dữ liệu */
    .category-table-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #F1F5F9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    /* Bảng dữ liệu chính */
    .category-data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .category-data-table thead th {
        padding: 18px 24px;
        font-size: 13.5px;
        font-weight: 600;
        color: #0F172A;
        border-bottom: 1px solid #F1F5F9;
        background-color: #FFFFFF;
        letter-spacing: -0.01em;
        white-space: nowrap;
    }

    .category-data-table thead th.col-actions {
        text-align: right;
    }

    .category-data-table tbody tr {
        border-bottom: 1px solid #F8FAFC;
        transition: background-color 0.15s ease;
    }

    .category-data-table tbody tr:hover {
        background-color: #F8FAFC;
    }

    .category-data-table tbody tr:last-child {
        border-bottom: none;
    }

    .category-data-table tbody td {
        padding: 16px 24px;
        vertical-align: middle;
        font-size: 13.5px;
        color: #334155;
    }

    /* Cột 1: Biểu tượng Icon Container */
    .category-icon-box {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .category-icon-box:hover {
        transform: scale(1.05);
    }

    /* Cột 2: Tên Danh Mục */
    .category-name-text {
        font-size: 14px;
        font-weight: 700;
        color: #0F172A;
        line-height: 1.3;
    }

    /* Cột 3: Slug URL (định dạng mono màu hồng/đỏ tím chuẩn ảnh) */
    .category-slug-text {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 13px;
        color: #DB2777; /* Hồng tươi chuẩn màu thiết kế */
        font-weight: 500;
    }

    /* Cột 4: Số địa điểm */
    .category-destinations-count {
        font-size: 13.5px;
        font-weight: 700;
        color: #0F172A;
    }

    /* Cột 5: Trạng thái (Huy hiệu xanh lá chuẩn ảnh) */
    .badge-status-active {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        background-color: #ECFDF5;
        color: #10B981;
    }

    .badge-status-inactive {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        background-color: #F1F5F9;
        color: #64748B;
    }

    /* Cột 6: Hành động (Nút tròn Sửa & Xóa chuẩn ảnh) */
    .action-buttons-wrap {
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
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        color: #64748B;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-circle-action:hover {
        border-color: #3B82F6;
        color: #3B82F6;
        background-color: #EFF6FF;
        transform: translateY(-1px);
    }

    .btn-circle-action.btn-delete {
        border-color: #FECDD3;
        color: #EF4444;
    }

    .btn-circle-action.btn-delete:hover {
        border-color: #EF4444;
        background-color: #FEF2F2;
        color: #DC2626;
        transform: translateY(-1px);
    }

    /* Thông báo Flash Alert */
    .flash-alert-banner {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13.5px;
    }

    .flash-alert-success {
        background-color: #ECFDF5;
        border: 1px solid #A7F3D0;
        color: #065F46;
    }

    .flash-alert-error {
        background-color: #FEF2F2;
        border: 1px solid #FECACA;
        color: #991B1B;
    }

    /* Modal Styling */
    .custom-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background-color: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
        z-index: 100;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .custom-modal-backdrop.is-active {
        display: flex;
    }

    .custom-modal-box {
        background-color: #FFFFFF;
        border-radius: 16px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        animation: modalScaleUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalScaleUp {
        from {
            opacity: 0;
            transform: scale(0.96);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .custom-modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .custom-modal-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
    }

    .custom-modal-close {
        background: none;
        border: none;
        color: #94A3B8;
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        transition: all 0.15s;
    }

    .custom-modal-close:hover {
        color: #0F172A;
        background-color: #F1F5F9;
    }

    .custom-modal-body {
        padding: 24px;
    }

    .form-group-item {
        margin-bottom: 18px;
    }

    .form-group-item label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .form-control-input,
    .form-control-select,
    .form-control-textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        font-size: 13.5px;
        color: #0F172A;
        font-family: inherit;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-control-input:focus,
    .form-control-select:focus,
    .form-control-textarea:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .custom-modal-footer {
        padding: 16px 24px;
        background-color: #F8FAFC;
        border-top: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary-cancel {
        padding: 9px 18px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        color: #475569;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-secondary-cancel:hover {
        background-color: #F1F5F9;
        color: #0F172A;
    }

    .btn-primary-submit {
        padding: 9px 20px;
        border-radius: 8px;
        border: none;
        background-color: #2563EB;
        color: #FFFFFF;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        transition: all 0.2s;
    }

    .btn-primary-submit:hover {
        background-color: #1D4ED8;
    }

    .btn-danger-submit {
        padding: 9px 20px;
        border-radius: 8px;
        border: none;
        background-color: #EF4444;
        color: #FFFFFF;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
        transition: all 0.2s;
    }

    .btn-danger-submit:hover {
        background-color: #DC2626;
    }

    /* Icon Picker Grid */
    .icon-radio-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-top: 6px;
    }

    .icon-radio-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 10px 6px;
        border: 1.5px solid #E2E8F0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }

    .icon-radio-option input {
        display: none;
    }

    .icon-radio-option:hover {
        border-color: #93C5FD;
        background-color: #F8FAFC;
    }

    .icon-radio-option.selected {
        border-color: #2563EB;
        background-color: #EFF6FF;
    }

    .icon-radio-option .opt-label {
        font-size: 11px;
        font-weight: 600;
        color: #475569;
    }

    .icon-radio-option.selected .opt-label {
        color: #1D4ED8;
    }
</style>
@endpush

@section('content')
<div class="category-management-container">

    {{-- Thông báo phản hồi --}}
    @if(session('success'))
        <div class="flash-alert-banner flash-alert-success">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:inherit; cursor:pointer;">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-alert-banner flash-alert-error">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:inherit; cursor:pointer;">&times;</button>
        </div>
    @endif

    {{-- Tiêu đề trang & Nút thêm mới --}}
    <div class="category-header-row">
        <h1 class="category-page-title">Quản Lý Danh Mục Du Lịch</h1>
        <button type="button" class="btn-add-category" onclick="openCreateModal()">
            <span>+ Thêm Danh Mục Mới</span>
        </button>
    </div>

    {{-- Breadcrumb: Admin / Danh mục --}}
    <div class="category-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="sep">/</span>
        <span class="current">Danh mục</span>
    </div>

    {{-- Card Bảng Danh Sách --}}
    <div class="category-table-card">
        <table class="category-data-table">
            <thead>
                <tr>
                    <th style="width: 140px;">Biểu tượng</th>
                    <th style="width: 260px;">Tên Danh Mục</th>
                    <th style="width: 220px;">Slug URL</th>
                    <th style="width: 180px;">Số địa điểm</th>
                    <th style="width: 150px;">Trạng thái</th>
                    <th class="col-actions" style="width: 120px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    @php
                        $iconMeta = $category->icon_meta;
                    @endphp
                    <tr>
                        {{-- Cột 1: Biểu tượng --}}
                        <td>
                            <div class="category-icon-box" style="background-color: {{ $iconMeta['bg_color'] }}; color: {{ $iconMeta['icon_color'] }};">
                                @if($iconMeta['type'] === 'hotel')
                                    {{-- Biểu tượng Tòa nhà / Resort & Khách sạn --}}
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
                                        <path d="M9 22v-4h6v4"/>
                                        <path d="M8 6h.01"/>
                                        <path d="M16 6h.01"/>
                                        <path d="M12 6h.01"/>
                                        <path d="M12 10h.01"/>
                                        <path d="M12 14h.01"/>
                                        <path d="M16 10h.01"/>
                                        <path d="M16 14h.01"/>
                                        <path d="M8 10h.01"/>
                                        <path d="M8 14h.01"/>
                                    </svg>
                                @elseif($iconMeta['type'] === 'waves')
                                    {{-- Biểu tượng Sóng biển / Biển & Đảo --}}
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                        <path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                        <path d="M2 18c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                    </svg>
                                @elseif($iconMeta['type'] === 'tree')
                                    {{-- Biểu tượng Cây thông / Núi Rừng & Trekking --}}
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="m12 2 4 7h-2.5l3.5 6h-3.5L16 20H8l2.5-5H7l3.5-6H8z"/>
                                        <path d="M12 20v2"/>
                                    </svg>
                                @elseif($iconMeta['type'] === 'landmark')
                                    {{-- Biểu tượng Đền đài / Di Sản Văn Hóa --}}
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="3" y1="22" x2="21" y2="22"/>
                                        <line x1="6" y1="18" x2="6" y2="11"/>
                                        <line x1="10" y1="18" x2="10" y2="11"/>
                                        <line x1="14" y1="18" x2="14" y2="11"/>
                                        <line x1="18" y1="18" x2="18" y2="11"/>
                                        <polygon points="12 2 20 7 4 7"/>
                                    </svg>
                                @else
                                    {{-- Biểu tượng Mặc định --}}
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="7" height="7" x="3" y="3" rx="1"/>
                                        <rect width="7" height="7" x="14" y="3" rx="1"/>
                                        <rect width="7" height="7" x="14" y="14" rx="1"/>
                                        <rect width="7" height="7" x="3" y="14" rx="1"/>
                                    </svg>
                                @endif
                            </div>
                        </td>

                        {{-- Cột 2: Tên Danh Mục --}}
                        <td>
                            <span class="category-name-text">{{ $category->name }}</span>
                        </td>

                        {{-- Cột 3: Slug URL --}}
                        <td>
                            <span class="category-slug-text">{{ $category->slug }}</span>
                        </td>

                        {{-- Cột 4: Số địa điểm --}}
                        <td>
                            <span class="category-destinations-count">{{ $category->destinations_count ?? 0 }} địa điểm</span>
                        </td>

                        {{-- Cột 5: Trạng thái --}}
                        <td>
                            @if($category->is_active)
                                <span class="badge-status-active">Hiển thị</span>
                            @else
                                <span class="badge-status-inactive">Tạm ẩn</span>
                            @endif
                        </td>

                        {{-- Cột 6: Hành động --}}
                        <td>
                            <div class="action-buttons-wrap">
                                {{-- Nút Chỉnh Sửa --}}
                                <button type="button"
                                        class="btn-circle-action"
                                        title="Chỉnh sửa danh mục"
                                        onclick="openEditModal({{ json_encode([
                                            'id' => $category->id,
                                            'name' => $category->name,
                                            'slug' => $category->slug,
                                            'icon' => $category->icon,
                                            'description' => $category->description,
                                            'is_active' => (bool) $category->is_active,
                                            'update_url' => route('admin.categories.update', $category->id)
                                        ]) }})">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                    </svg>
                                </button>

                                {{-- Nút Xóa (Tuân thủ BR-17) --}}
                                <button type="button"
                                        class="btn-circle-action btn-delete"
                                        title="Xóa danh mục"
                                        onclick="openDeleteModal({{ json_encode([
                                            'id' => $category->id,
                                            'name' => $category->name,
                                            'destinations_count' => $category->destinations_count ?? 0,
                                            'delete_url' => route('admin.categories.destroy', $category->id)
                                        ]) }})">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"/>
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 48px; color: #94A3B8;">
                            <div style="margin-bottom: 8px;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5">
                                    <rect width="7" height="7" x="3" y="3" rx="1"/>
                                    <rect width="7" height="7" x="14" y="3" rx="1"/>
                                    <rect width="7" height="7" x="14" y="14" rx="1"/>
                                    <rect width="7" height="7" x="3" y="14" rx="1"/>
                                </svg>
                            </div>
                            <p style="font-size: 14px; font-weight: 500;">Chưa có danh mục du lịch nào trong hệ thống.</p>
                            <button type="button" class="btn-add-category" onclick="openCreateModal()" style="margin-top: 12px;">+ Thêm Danh Mục Đầu Tiên</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phân trang nếu có --}}
    @if($categories->hasPages())
        <div style="margin-top: 20px; display: flex; justify-content: center;">
            {{ $categories->links() }}
        </div>
    @endif
</div>

{{-- MODAL 1: THÊM MỚI DANH MỤC DU LỊCH --}}
<div id="createCategoryModal" class="custom-modal-backdrop">
    <div class="custom-modal-box">
        <div class="custom-modal-header">
            <h3 class="custom-modal-title">Thêm Danh Mục Mới</h3>
            <button type="button" class="custom-modal-close" onclick="closeModal('createCategoryModal')">&times;</button>
        </div>
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="custom-modal-body">
                <div class="form-group-item">
                    <label for="create_name">Tên Danh Mục <span style="color: #EF4444;">*</span></label>
                    <input type="text"
                           id="create_name"
                           name="name"
                           class="form-control-input"
                           placeholder="Ví dụ: Nghỉ dưỡng & Resort, Biển & Đảo..."
                           required
                           oninput="autoGenerateSlug(this.value, 'create_slug')">
                </div>

                <div class="form-group-item">
                    <label for="create_slug">Slug URL</label>
                    <input type="text"
                           id="create_slug"
                           name="slug"
                           class="form-control-input"
                           placeholder="resort-spa (để trống sẽ tự động tạo từ tên)">
                </div>

                <div class="form-group-item">
                    <label>Biểu tượng đại diện</label>
                    <div class="icon-radio-grid">
                        <label class="icon-radio-option selected" onclick="selectIconRadio(this, 'create_icon', 'hotel')">
                            <input type="radio" name="icon" value="hotel" checked>
                            <div class="category-icon-box" style="background-color: #EFF6FF; color: #3B82F6;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
                                    <path d="M9 22v-4h6v4"/>
                                </svg>
                            </div>
                            <span class="opt-label">Resort</span>
                        </label>

                        <label class="icon-radio-option" onclick="selectIconRadio(this, 'create_icon', 'waves')">
                            <input type="radio" name="icon" value="waves">
                            <div class="category-icon-box" style="background-color: #ECFEFF; color: #06B6D4;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                    <path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                </svg>
                            </div>
                            <span class="opt-label">Biển & Đảo</span>
                        </label>

                        <label class="icon-radio-option" onclick="selectIconRadio(this, 'create_icon', 'tree')">
                            <input type="radio" name="icon" value="tree">
                            <div class="category-icon-box" style="background-color: #ECFDF5; color: #10B981;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m12 2 4 7h-2.5l3.5 6h-3.5L16 20H8l2.5-5H7l3.5-6H8z"/>
                                </svg>
                            </div>
                            <span class="opt-label">Núi Rừng</span>
                        </label>

                        <label class="icon-radio-option" onclick="selectIconRadio(this, 'create_icon', 'landmark')">
                            <input type="radio" name="icon" value="landmark">
                            <div class="category-icon-box" style="background-color: #FEF2F2; color: #EF4444;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="3" y1="22" x2="21" y2="22"/>
                                    <polygon points="12 2 20 7 4 7"/>
                                </svg>
                            </div>
                            <span class="opt-label">Di Sản</span>
                        </label>
                    </div>
                </div>

                <div class="form-group-item">
                    <label for="create_description">Mô tả tóm tắt</label>
                    <textarea id="create_description"
                              name="description"
                              class="form-control-textarea"
                              rows="3"
                              placeholder="Mô tả đặc trưng danh mục du lịch này..."></textarea>
                </div>

                <div class="form-group-item">
                    <label for="create_is_active">Trạng thái hiển thị</label>
                    <select id="create_is_active" name="is_active" class="form-control-select">
                        <option value="1" selected>Hiển thị công khai</option>
                        <option value="0">Tạm ẩn trên website</option>
                    </select>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn-secondary-cancel" onclick="closeModal('createCategoryModal')">Hủy bỏ</button>
                <button type="submit" class="btn-primary-submit">Lưu Danh Mục</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: CHỈNH SỬA DANH MỤC DU LỊCH --}}
<div id="editCategoryModal" class="custom-modal-backdrop">
    <div class="custom-modal-box">
        <div class="custom-modal-header">
            <h3 class="custom-modal-title">Chỉnh Sửa Danh Mục</h3>
            <button type="button" class="custom-modal-close" onclick="closeModal('editCategoryModal')">&times;</button>
        </div>
        <form id="editCategoryForm" method="POST">
            @csrf
            @method('PUT')
            <div class="custom-modal-body">
                <div class="form-group-item">
                    <label for="edit_name">Tên Danh Mục <span style="color: #EF4444;">*</span></label>
                    <input type="text"
                           id="edit_name"
                           name="name"
                           class="form-control-input"
                           required
                           oninput="autoGenerateSlug(this.value, 'edit_slug')">
                </div>

                <div class="form-group-item">
                    <label for="edit_slug">Slug URL</label>
                    <input type="text"
                           id="edit_slug"
                           name="slug"
                           class="form-control-input">
                </div>

                <div class="form-group-item">
                    <label>Biểu tượng đại diện</label>
                    <div class="icon-radio-grid" id="edit_icon_grid">
                        <label class="icon-radio-option" onclick="selectEditIconRadio(this, 'hotel')">
                            <input type="radio" name="icon" value="hotel">
                            <div class="category-icon-box" style="background-color: #EFF6FF; color: #3B82F6;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
                                    <path d="M9 22v-4h6v4"/>
                                </svg>
                            </div>
                            <span class="opt-label">Resort</span>
                        </label>

                        <label class="icon-radio-option" onclick="selectEditIconRadio(this, 'waves')">
                            <input type="radio" name="icon" value="waves">
                            <div class="category-icon-box" style="background-color: #ECFEFF; color: #06B6D4;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 6c.6.5 1.2 1 2.5 1C7 7 7 5 9.5 5c2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                    <path d="M2 12c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 2.6 0 2.4 2 5 2 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                                </svg>
                            </div>
                            <span class="opt-label">Biển & Đảo</span>
                        </label>

                        <label class="icon-radio-option" onclick="selectEditIconRadio(this, 'tree')">
                            <input type="radio" name="icon" value="tree">
                            <div class="category-icon-box" style="background-color: #ECFDF5; color: #10B981;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m12 2 4 7h-2.5l3.5 6h-3.5L16 20H8l2.5-5H7l3.5-6H8z"/>
                                </svg>
                            </div>
                            <span class="opt-label">Núi Rừng</span>
                        </label>

                        <label class="icon-radio-option" onclick="selectEditIconRadio(this, 'landmark')">
                            <input type="radio" name="icon" value="landmark">
                            <div class="category-icon-box" style="background-color: #FEF2F2; color: #EF4444;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="3" y1="22" x2="21" y2="22"/>
                                    <polygon points="12 2 20 7 4 7"/>
                                </svg>
                            </div>
                            <span class="opt-label">Di Sản</span>
                        </label>
                    </div>
                </div>

                <div class="form-group-item">
                    <label for="edit_description">Mô tả tóm tắt</label>
                    <textarea id="edit_description"
                              name="description"
                              class="form-control-textarea"
                              rows="3"></textarea>
                </div>

                <div class="form-group-item">
                    <label for="edit_is_active">Trạng thái hiển thị</label>
                    <select id="edit_is_active" name="is_active" class="form-control-select">
                        <option value="1">Hiển thị công khai</option>
                        <option value="0">Tạm ẩn trên website</option>
                    </select>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn-secondary-cancel" onclick="closeModal('editCategoryModal')">Hủy bỏ</button>
                <button type="submit" class="btn-primary-submit">Cập Nhật Danh Mục</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: XÁC NHẬN XÓA DANH MỤC (TUÂN THỦ BR-17) --}}
<div id="deleteCategoryModal" class="custom-modal-backdrop">
    <div class="custom-modal-box">
        <div class="custom-modal-header">
            <h3 class="custom-modal-title" style="color: #EF4444; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Xác Nhận Xóa Danh Mục
            </h3>
            <button type="button" class="custom-modal-close" onclick="closeModal('deleteCategoryModal')">&times;</button>
        </div>
        <form id="deleteCategoryForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="custom-modal-body">
                <p style="font-size: 14px; color: #334155; line-height: 1.5; margin-bottom: 12px;">
                    Bạn có chắc chắn muốn xóa danh mục <strong id="delete_category_name" style="color: #0F172A;"></strong> khỏi hệ thống?
                </p>
                <div id="delete_warning_box" style="padding: 12px; background-color: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; font-size: 12.5px; color: #991B1B; line-height: 1.4;">
                    <strong>Lưu ý bảo vệ dữ liệu (BR-17):</strong> Nếu danh mục đang có các địa điểm du lịch liên kết, hệ thống sẽ ngăn chặn thao tác xóa để đảm bảo tính toàn vẹn dữ liệu.
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn-secondary-cancel" onclick="closeModal('deleteCategoryModal')">Hủy bỏ</button>
                <button type="submit" class="btn-danger-submit">Xác Nhận Xóa</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Hàm sinh Slug tiếng Việt tự động
    function autoGenerateSlug(text, targetId) {
        if (!text) return;
        const targetInput = document.getElementById(targetId);
        if (!targetInput) return;

        let str = text.toLowerCase();
        str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g, "a");
        str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g, "e");
        str = str.replace(/ì|í|ị|ỉ|ĩ/g, "i");
        str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g, "o");
        str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g, "u");
        str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g, "y");
        str = str.replace(/đ/g, "d");
        str = str.replace(/[^a-z0-9 -]/g, '');
        str = str.replace(/\s+/g, '-');
        str = str.replace(/-+/g, '-');
        str = str.replace(/^-+|-+$/g, '');

        targetInput.value = str;
    }

    // Quản lý Modal
    function openCreateModal() {
        document.getElementById('createCategoryModal').classList.add('is-active');
    }

    function openEditModal(category) {
        document.getElementById('edit_name').value = category.name;
        document.getElementById('edit_slug').value = category.slug;
        document.getElementById('edit_description').value = category.description || '';
        document.getElementById('edit_is_active').value = category.is_active ? '1' : '0';
        document.getElementById('editCategoryForm').action = category.update_url;

        // Chọn icon radio tương ứng
        const grid = document.getElementById('edit_icon_grid');
        const options = grid.querySelectorAll('.icon-radio-option');
        options.forEach(opt => {
            const radio = opt.querySelector('input[type="radio"]');
            if (radio && radio.value === (category.icon || 'hotel')) {
                radio.checked = true;
                opt.classList.add('selected');
            } else {
                opt.classList.remove('selected');
            }
        });

        document.getElementById('editCategoryModal').classList.add('is-active');
    }

    function openDeleteModal(category) {
        document.getElementById('delete_category_name').textContent = category.name;
        document.getElementById('deleteCategoryForm').action = category.delete_url;

        const warningBox = document.getElementById('delete_warning_box');
        if (category.destinations_count > 0) {
            warningBox.innerHTML = `<strong>Cảnh báo BR-17:</strong> Danh mục này hiện đang có <strong>${category.destinations_count} địa điểm</strong> liên kết. Thao tác xóa sẽ bị từ chối theo quy tắc bảo vệ toàn vẹn dữ liệu.`;
            warningBox.style.display = 'block';
        } else {
            warningBox.innerHTML = `Danh mục này chưa có địa điểm nào liên kết, bạn có thể xóa an toàn.`;
            warningBox.style.display = 'block';
        }

        document.getElementById('deleteCategoryModal').classList.add('is-active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('is-active');
    }

    function selectIconRadio(element, groupName, iconVal) {
        const grid = element.parentElement;
        grid.querySelectorAll('.icon-radio-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        const radio = element.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    function selectEditIconRadio(element, iconVal) {
        const grid = document.getElementById('edit_icon_grid');
        grid.querySelectorAll('.icon-radio-option').forEach(opt => opt.classList.remove('selected'));
        element.classList.add('selected');
        const radio = element.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    // Đóng Modal khi bấm phím ESC hoặc click ngoài backdrop
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.custom-modal-backdrop.is-active').forEach(m => m.classList.remove('is-active'));
        }
    });

    document.querySelectorAll('.custom-modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('is-active');
            }
        });
    });
</script>
@endpush
@endsection
