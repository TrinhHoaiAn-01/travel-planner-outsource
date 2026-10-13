@extends('layouts.admin')

@section('title', 'Quản Lý Thư Viện Hình Ảnh')
@section('breadcrumb', 'Thư viện ảnh')

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

    /* Thanh tiêu đề chính: Quản Lý Thư Viện Hình Ảnh */
    .gallery-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .gallery-page-title {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
        letter-spacing: -0.01em;
    }

    /* Nút "+ Tải Lên Ảnh Mới" dạng viên thuốc màu xanh */
    .btn-upload-photo {
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

    .btn-upload-photo:hover {
        background-color: #1D4ED8;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
        color: #FFFFFF;
    }

    /* Breadcrumb chuẩn ảnh: Admin / Thư viện ảnh */
    .gallery-breadcrumb {
        font-size: 13.5px;
        color: #64748B;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .gallery-breadcrumb a {
        color: #3B82F6;
        text-decoration: none;
        font-weight: 500;
    }

    .gallery-breadcrumb a:hover {
        text-decoration: underline;
    }

    .gallery-breadcrumb .sep {
        color: #94A3B8;
    }

    .gallery-breadcrumb .current {
        color: #64748B;
    }

    /* Card chứa bộ lọc và thư viện ảnh */
    .gallery-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #F1F5F9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        padding: 24px 28px;
    }

    /* Thanh bộ lọc theo địa điểm và badge tổng số ảnh */
    .gallery-toolbar-row {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .gallery-filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .gallery-filter-label {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
    }

    .gallery-select-wrap {
        position: relative;
        min-width: 320px;
    }

    .gallery-select {
        width: 100%;
        padding: 10px 36px 10px 16px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        font-size: 13.5px;
        color: #0F172A;
        outline: none;
        background-color: #FFFFFF;
        appearance: none;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-weight: 500;
    }

    .gallery-select:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .gallery-select-wrap svg {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        pointer-events: none;
    }

    /* Badge tổng cộng số hình ảnh */
    .gallery-count-badge {
        display: inline-flex;
        align-items: center;
        background-color: #EFF6FF;
        color: #2563EB;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid #DBEAFE;
    }

    /* Lưới hình ảnh Gallery (Grid 4 cột theo ảnh thiết kế) */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    @media (max-width: 1024px) {
        .gallery-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .gallery-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .gallery-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Thẻ ảnh (Photo Card) */
    .photo-card {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        background-color: #F1F5F9;
        aspect-ratio: 16 / 10;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .photo-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .photo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.3s ease;
    }

    .photo-card:hover .photo-img {
        transform: scale(1.03);
    }

    /* Badge "Ảnh đại diện" trên góc trái ảnh */
    .badge-primary-photo {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 10;
        background-color: #2563EB;
        color: #FFFFFF;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
        display: flex;
        align-items: center;
        gap: 4px;
        line-height: 1.2;
    }

    /* Lớp phủ hành động khi hover (Overlay actions) */
    .photo-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(1.5px);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        opacity: 0;
        transition: opacity 0.2s ease;
        z-index: 20;
        padding: 12px;
    }

    .photo-card:hover .photo-overlay {
        opacity: 1;
    }

    .btn-photo-action {
        background-color: #FFFFFF;
        color: #0F172A;
        font-size: 12px;
        font-weight: 600;
        padding: 7px 11px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }

    .btn-photo-action:hover {
        background-color: #2563EB;
        color: #FFFFFF;
    }

    .btn-photo-delete {
        background-color: #EF4444;
        color: #FFFFFF;
    }

    .btn-photo-delete:hover {
        background-color: #DC2626;
    }

    /* Toast thông báo */
    .gallery-toast {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 13.5px;
        animation: fadeIn 0.3s ease;
    }

    .gallery-toast-success {
        background-color: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .gallery-toast-error {
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

    /* Modal Styling */
    .gallery-modal-overlay {
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

    .gallery-modal-overlay.active {
        display: flex;
    }

    .gallery-modal-card {
        background-color: #FFFFFF;
        width: 100%;
        max-width: 520px;
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        animation: modalScaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalScaleUp {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }

    .gallery-modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .gallery-modal-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
    }

    .gallery-modal-close-btn {
        background: transparent;
        border: none;
        font-size: 18px;
        color: #94A3B8;
        cursor: pointer;
        transition: color 0.2s;
    }

    .gallery-modal-close-btn:hover {
        color: #0F172A;
    }

    .gallery-modal-body {
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

    .form-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
        color: #334155;
        cursor: pointer;
        user-select: none;
    }

    .gallery-modal-footer {
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
</style>
@endpush

@section('content')
<!-- Thanh Tiêu Đề và Nút Tải Lên -->
<div class="gallery-header-row">
    <h1 class="gallery-page-title">Quản Lý Thư Viện Hình Ảnh</h1>
    <button type="button" class="btn-upload-photo" onclick="openUploadModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
        </svg>
        <span>+ Tải Lên Ảnh Mới</span>
    </button>
</div>

<!-- Breadcrumb: Admin / Thư viện ảnh -->
<div class="gallery-breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Admin</a>
    <span class="sep">/</span>
    <span class="current">Thư viện ảnh</span>
</div>

<!-- Flash Toast thông báo -->
@if(session('success'))
<div class="gallery-toast gallery-toast-success" id="flashToast">
    <span>{{ session('success') }}</span>
    <button type="button" class="toast-close-btn" onclick="document.getElementById('flashToast').remove()">&times;</button>
</div>
@endif

@if(session('error'))
<div class="gallery-toast gallery-toast-error" id="flashToast">
    <span>{{ session('error') }}</span>
    <button type="button" class="toast-close-btn" onclick="document.getElementById('flashToast').remove()">&times;</button>
</div>
@endif

@if($errors->any())
<div class="gallery-toast gallery-toast-error" id="flashToast">
    <span>{{ $errors->first() }}</span>
    <button type="button" class="toast-close-btn" onclick="document.getElementById('flashToast').remove()">&times;</button>
</div>
@endif

<!-- Card Chính chứa Bộ Lọc và Lưới Ảnh -->
<div class="gallery-card">
    <!-- Thanh bộ lọc theo Địa điểm du lịch và Huy hiệu số lượng ảnh -->
    <div class="gallery-toolbar-row">
        <form action="{{ route('admin.destination-images.index') }}" method="GET" id="filterForm">
            <div class="gallery-filter-group">
                <label class="gallery-filter-label">Lọc theo Địa điểm du lịch:</label>
                <div class="gallery-select-wrap">
                    <select name="destination_id" class="gallery-select" onchange="document.getElementById('filterForm').submit()">
                        @foreach($destinations as $dest)
                            <option value="{{ $dest->id }}" {{ $selectedDestinationId === $dest->id ? 'selected' : '' }}>
                                {{ $dest->name }} ({{ $dest->city?->name ?? 'Việt Nam' }})
                            </option>
                        @endforeach
                    </select>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
            </div>
        </form>

        <!-- Badge Tổng cộng: X hình ảnh -->
        <div class="gallery-count-badge">
            Tổng cộng: {{ $images->count() }} hình ảnh
        </div>
    </div>

    <!-- Lưới Thư Viện Hình Ảnh -->
    @if($images->isNotEmpty())
    <div class="gallery-grid">
        @foreach($images as $image)
        <div class="photo-card">
            @if($image->is_primary)
                <div class="badge-primary-photo">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <span>Ảnh đại diện</span>
                </div>
            @endif

            <img src="{{ $image->image_path }}"
                 alt="Hình ảnh {{ $selectedDestination?->name }}"
                 class="photo-img"
                 onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=80'">

            <!-- Lớp phủ thao tác khi rê chuột -->
            <div class="photo-overlay">
                @if(! $image->is_primary)
                <form action="{{ route('admin.destination-images.set-primary', $image->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-photo-action" title="Đặt làm ảnh đại diện">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span>Đặt làm đại diện</span>
                    </button>
                </form>
                @endif

                <button type="button" class="btn-photo-action" onclick="viewFullImage('{{ $image->image_path }}')" title="Xem ảnh lớn">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        <line x1="11" y1="8" x2="11" y2="14"></line>
                        <line x1="8" y1="11" x2="14" y2="11"></line>
                    </svg>
                    <span>Xem lớn</span>
                </button>

                <form action="{{ route('admin.destination-images.destroy', $image->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hình ảnh này khỏi thư viện?')" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-photo-action btn-photo-delete" title="Xóa ảnh">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                        </svg>
                        <span>Xóa</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align: center; padding: 60px 20px; color: #94A3B8;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px; color: #CBD5E1;">
            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
            <circle cx="9" cy="9" r="2"></circle>
            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
        </svg>
        <p style="font-size: 14px; font-weight: 500;">Địa điểm này hiện chưa có hình ảnh nào trong thư viện.</p>
        <button type="button" class="btn-upload-photo" onclick="openUploadModal()" style="margin-top: 14px;">
            + Tải Lên Ảnh Đầu Tiên
        </button>
    </div>
    @endif
</div>

<!-- ================= MODAL: TẢI LÊN ẢNH MỚI ================= -->
<div class="gallery-modal-overlay" id="uploadModal">
    <div class="gallery-modal-card">
        <form action="{{ route('admin.destination-images.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="gallery-modal-header">
                <h3 class="gallery-modal-title">Tải Lên Hình Ảnh Mới</h3>
                <button type="button" class="gallery-modal-close-btn" onclick="closeModal('uploadModal')">&times;</button>
            </div>
            <div class="gallery-modal-body">
                <div class="form-group">
                    <label class="form-label">Địa điểm du lịch <span style="color: #EF4444;">*</span></label>
                    <select name="destination_id" class="form-control" required>
                        @foreach($destinations as $dest)
                            <option value="{{ $dest->id }}" {{ $selectedDestinationId === $dest->id ? 'selected' : '' }}>
                                {{ $dest->name }} ({{ $dest->city?->name ?? 'Việt Nam' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tải tệp hình ảnh từ máy tính (Tối đa 2MB)</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Hoặc nhập đường dẫn hình ảnh trực tuyến (URL)</label>
                    <input type="url" name="image_url" class="form-control" placeholder="https://images.unsplash.com/...">
                </div>

                <div class="form-group" style="margin-top: 14px;">
                    <label class="form-checkbox-label">
                        <input type="checkbox" name="is_primary" value="1">
                        <span>Đặt làm ảnh đại diện chính của địa điểm này</span>
                    </label>
                </div>
            </div>
            <div class="gallery-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('uploadModal')">Hủy bỏ</button>
                <button type="submit" class="btn-primary">Tải Lên Thư Viện</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL: XEM ẢNH LỚN ================= -->
<div class="gallery-modal-overlay" id="previewModal">
    <div class="gallery-modal-card" style="max-width: 800px; background-color: transparent; box-shadow: none;">
        <div style="position: relative; text-align: center;">
            <button type="button"
                    onclick="closeModal('previewModal')"
                    style="position: absolute; right: 10px; top: 10px; z-index: 50; background: rgba(15,23,42,0.7); border: none; color: #FFFFFF; font-size: 20px; width: 36px; height: 36px; border-radius: 50%; cursor: pointer;">
                &times;
            </button>
            <img id="previewImgElement" src="" alt="Preview" style="max-width: 100%; max-height: 80vh; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);">
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function openUploadModal() {
        openModal('uploadModal');
    }

    function viewFullImage(url) {
        document.getElementById('previewImgElement').src = url;
        openModal('previewModal');
    }

    window.addEventListener('click', function(e) {
        document.querySelectorAll('.gallery-modal-overlay').forEach(function(modal) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
</script>
@endsection
