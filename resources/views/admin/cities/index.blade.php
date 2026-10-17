@extends('layouts.admin')

@section('title', 'Quản Lý Tỉnh / Thành Phố')
@section('breadcrumb', 'Thành phố')

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

    /* Thanh tiêu đề trang Quản Lý Tỉnh / Thành Phố */
    .city-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .city-page-title {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
        letter-spacing: -0.01em;
    }

    .btn-add-city {
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

    .btn-add-city:hover {
        background-color: #1D4ED8;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
    }

    /* Breadcrumb chuẩn ảnh: Admin / Thành phố */
    .city-breadcrumb {
        font-size: 13.5px;
        color: #64748B;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .city-breadcrumb a {
        color: #3B82F6;
        text-decoration: none;
        font-weight: 500;
    }

    .city-breadcrumb a:hover {
        text-decoration: underline;
    }

    .city-breadcrumb .sep {
        color: #94A3B8;
    }

    .city-breadcrumb .current {
        color: #64748B;
    }

    /* Thẻ Card chứa Bảng dữ liệu */
    .city-table-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #F1F5F9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    /* Bảng dữ liệu chính */
    .city-data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .city-data-table thead th {
        padding: 18px 24px;
        font-size: 13.5px;
        font-weight: 600;
        color: #0F172A;
        border-bottom: 1px solid #F1F5F9;
        background-color: #FFFFFF;
        letter-spacing: -0.01em;
        white-space: nowrap;
    }

    .city-data-table thead th.col-actions {
        text-align: right;
    }

    .city-data-table tbody tr {
        border-bottom: 1px solid #F8FAFC;
        transition: background-color 0.15s ease;
    }

    .city-data-table tbody tr:hover {
        background-color: #F8FAFC;
    }

    .city-data-table tbody tr:last-child {
        border-bottom: none;
    }

    .city-data-table tbody td {
        padding: 16px 24px;
        vertical-align: middle;
        font-size: 13.5px;
        color: #334155;
    }

    /* Cột 1: Hình ảnh Thumbnail */
    .city-thumb-wrap {
        width: 52px;
        height: 38px;
        border-radius: 8px;
        overflow: hidden;
        background-color: #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
    }

    .city-thumb-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .city-thumb-placeholder {
        color: #94A3B8;
        font-size: 10px;
        font-weight: 600;
    }

    /* Cột 2: Tên Thành Phố / Tỉnh */
    .city-name-text {
        font-size: 14px;
        font-weight: 700;
        color: #0F172A;
        line-height: 1.3;
    }

    .city-code-text {
        font-size: 12px;
        color: #94A3B8;
        margin-top: 2px;
    }

    /* Cột 3: Khu vực (Miền) Badges */
    .badge-region {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 500;
        line-height: 1.3;
    }

    .badge-central {
        background-color: #EFF6FF;
        color: #3B82F6;
    }

    .badge-south {
        background-color: #ECFDF5;
        color: #10B981;
    }

    .badge-north {
        background-color: #FEF3C7;
        color: #D97706;
    }

    /* Cột 4: Số điểm đến */
    .city-destinations-count {
        font-size: 13.5px;
        color: #0F172A;
        font-weight: 400;
    }

    .city-destinations-count strong {
        font-weight: 700;
        color: #0F172A;
    }

    /* Cột 5: Trạng thái */
    .badge-status {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 9999px;
        font-size: 11.5px;
        font-weight: 500;
        line-height: 1.3;
    }

    .badge-active {
        background-color: #DCFCE7;
        color: #16A34A;
    }

    .badge-inactive {
        background-color: #F1F5F9;
        color: #64748B;
    }

    /* Cột 6: Hành động (2 nút tròn) */
    .city-actions-wrap {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }

    .btn-action-circle {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #FFFFFF;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-action-edit {
        border: 1px solid #CBD5E1;
        color: #64748B;
    }

    .btn-action-edit:hover {
        border-color: #3B82F6;
        color: #2563EB;
        background-color: #EFF6FF;
    }

    .btn-action-delete {
        border: 1px solid #FCA5A5;
        color: #EF4444;
    }

    .btn-action-delete:hover {
        border-color: #EF4444;
        color: #DC2626;
        background-color: #FEF2F2;
    }

    /* Toast thông báo */
    .alert-toast {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 13.5px;
        animation: fadeIn 0.3s ease;
    }

    .alert-toast-success {
        background-color: #DCFCE7;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .alert-toast-danger {
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

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Modal Styling */
    .modal-overlay {
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

    .modal-overlay.active {
        display: flex;
    }

    .modal-card {
        background-color: #FFFFFF;
        width: 100%;
        max-width: 540px;
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        animation: modalScaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes modalScaleUp {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }

    .modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 700;
        color: #0F172A;
    }

    .modal-close-btn {
        background: transparent;
        border: none;
        font-size: 18px;
        color: #94A3B8;
        cursor: pointer;
        transition: color 0.2s;
    }

    .modal-close-btn:hover {
        color: #0F172A;
    }

    .modal-body {
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

    .modal-footer {
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

    .btn-danger {
        padding: 8px 18px;
        border-radius: 8px;
        border: none;
        background-color: #EF4444;
        color: #FFFFFF;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-danger:hover {
        background-color: #DC2626;
    }

    .pagination-wrap {
        padding: 16px 24px;
        border-top: 1px solid #F1F5F9;
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content')
<!-- Header Row: Tiêu đề + Nút Thêm Thành Phố Mới -->
<div class="city-header-row">
    <h1 class="city-page-title">Quản Lý Tỉnh / Thành Phố</h1>
    <button type="button" class="btn-add-city" onclick="openCreateModal()">
        <span>+ Thêm Thành Phố Mới</span>
    </button>
</div>

<!-- Breadcrumb chuẩn ảnh: Admin / Thành phố -->
<div class="city-breadcrumb">
    <a href="{{ route('admin.dashboard') }}">Admin</a>
    <span class="sep">/</span>
    <span class="current">Thành phố</span>
</div>

<!-- Thông báo Flash Messages -->
@if(session('success'))
<div class="alert-toast alert-toast-success">
    <span>{{ session('success') }}</span>
    <button type="button" class="toast-close-btn" onclick="this.parentElement.remove()">✕</button>
</div>
@endif

@if(session('error'))
<div class="alert-toast alert-toast-danger">
    <span>{{ session('error') }}</span>
    <button type="button" class="toast-close-btn" onclick="this.parentElement.remove()">✕</button>
</div>
@endif

@if($errors->any())
<div class="alert-toast alert-toast-danger">
    <div>
        <strong>Đã có lỗi xảy ra:</strong>
        <ul style="margin: 4px 0 0 18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <button type="button" class="toast-close-btn" onclick="this.parentElement.remove()">✕</button>
</div>
@endif

<!-- Bảng Danh sách Tỉnh / Thành phố -->
<div class="city-table-card">
    <table class="city-data-table">
        <thead>
            <tr>
                <th style="width: 80px;">Hình ảnh</th>
                <th style="width: 260px;">Tên Thành Phố / Tỉnh</th>
                <th style="width: 180px;">Khu vực (Miền)</th>
                <th style="width: 160px;">Số điểm đến</th>
                <th style="width: 140px;">Trạng thái</th>
                <th class="col-actions" style="width: 110px;">Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cities as $city)
            <tr>
                <!-- Cột 1: Hình ảnh -->
                <td>
                    <div class="city-thumb-wrap">
                        @if($city->image)
                            <img src="{{ $city->image }}" alt="{{ $city->name }}" class="city-thumb-img" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=300&q=80';">
                        @else
                            <div class="city-thumb-placeholder">N/A</div>
                        @endif
                    </div>
                </td>

                <!-- Cột 2: Tên Thành Phố / Tỉnh -->
                <td>
                    <div class="city-name-text">{{ $city->name }}</div>
                    <div class="city-code-text">Mã: {{ $city->code ?? 'CTY-' . strtoupper(substr($city->slug, 0, 3)) }}</div>
                </td>

                <!-- Cột 3: Khu vực (Miền) -->
                <td>
                    @if($city->region)
                        @if(str_contains($city->region, 'Trung'))
                            <span class="badge-region badge-central">{{ $city->region }}</span>
                        @elseif(str_contains($city->region, 'Nam'))
                            <span class="badge-region badge-south">{{ $city->region }}</span>
                        @elseif(str_contains($city->region, 'Bắc'))
                            <span class="badge-region badge-north">{{ $city->region }}</span>
                        @else
                            <span class="badge-region badge-central">{{ $city->region }}</span>
                        @endif
                    @endif
                </td>

                <!-- Cột 4: Số điểm đến -->
                <td>
                    <div class="city-destinations-count">
                        <strong>{{ $city->destinations_count ?? $city->destinations()->count() }}</strong> địa điểm
                    </div>
                </td>

                <!-- Cột 5: Trạng thái -->
                <td>
                    @if($city->is_active)
                        <span class="badge-status badge-active">Hiển thị</span>
                    @else
                        <span class="badge-status badge-inactive">Tạm ẩn</span>
                    @endif
                </td>

                <!-- Cột 6: Hành động -->
                <td>
                    <div class="city-actions-wrap">
                        <!-- Nút Sửa -->
                        <button type="button" class="btn-action-circle btn-action-edit" onclick="openEditModal({{ json_encode($city) }})" title="Chỉnh sửa">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                <path d="m15 5 4 4"/>
                            </svg>
                        </button>

                        <!-- Nút Xóa -->
                        <button type="button" class="btn-action-circle btn-action-delete" onclick="openDeleteModal({{ json_encode($city) }})" title="Xóa">
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
                <td colspan="6" style="text-align: center; padding: 40px; color: #94A3B8;">
                    Chưa có tỉnh / thành phố nào trong hệ thống.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($cities->hasPages())
    <div class="pagination-wrap">
        {{ $cities->links() }}
    </div>
    @endif
</div>

<!-- Modal Thêm Thành Phố Mới -->
<div class="modal-overlay" id="createCityModal">
    <div class="modal-card">
        <form method="POST" action="{{ route('admin.cities.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title">+ Thêm Thành Phố Mới</h3>
                <button type="button" class="modal-close-btn" onclick="closeCreateModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên Thành Phố / Tỉnh <span style="color: #EF4444;">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="VD: Đà Nẵng, Quảng Ninh..." required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Mã thành phố (Code)</label>
                        <input type="text" name="code" class="form-control" placeholder="VD: CTY-DAD (Tự sinh nếu trống)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Khu vực (Miền)</label>
                        <select name="region" class="form-control">
                            <option value="">-- Để trống --</option>
                            <option value="Miền Bắc">Miền Bắc</option>
                            <option value="Miền Trung">Miền Trung</option>
                            <option value="Miền Nam">Miền Nam</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái hiển thị</label>
                    <select name="is_active" class="form-control">
                        <option value="1" selected>Hiển thị</option>
                        <option value="0">Tạm ẩn</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Đường dẫn hình ảnh (Image URL)</label>
                    <input type="text" name="image" class="form-control" placeholder="https://images.unsplash.com/...">
                </div>

                <div class="form-group">
                    <label class="form-label">Hoặc tải tệp ảnh từ máy tính</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả giới thiệu</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Nhập mô tả tổng quan về tỉnh/thành phố..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeCreateModal()">Hủy</button>
                <button type="submit" class="btn-primary">+ Thêm Thành Phố</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Chỉnh Sửa Thành Phố -->
<div class="modal-overlay" id="editCityModal">
    <div class="modal-card">
        <form method="POST" id="editCityForm" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h3 class="modal-title">Chỉnh Sửa Tỉnh / Thành Phố</h3>
                <button type="button" class="modal-close-btn" onclick="closeEditModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên Thành Phố / Tỉnh <span style="color: #EF4444;">*</span></label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Mã thành phố (Code)</label>
                        <input type="text" name="code" id="edit_code" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Khu vực (Miền)</label>
                        <select name="region" id="edit_region" class="form-control">
                            <option value="">-- Để trống --</option>
                            <option value="Miền Bắc">Miền Bắc</option>
                            <option value="Miền Trung">Miền Trung</option>
                            <option value="Miền Nam">Miền Nam</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái hiển thị</label>
                    <select name="is_active" id="edit_is_active" class="form-control">
                        <option value="1">Hiển thị</option>
                        <option value="0">Tạm ẩn</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Đường dẫn hình ảnh (Image URL)</label>
                    <input type="text" name="image" id="edit_image" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Hoặc tải tệp ảnh mới thay thế</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Mô tả giới thiệu</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Hủy</button>
                <button type="submit" class="btn-primary">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Xác Nhận Xóa Thành Phố (Bảo vệ BR-17) -->
<div class="modal-overlay" id="deleteCityModal">
    <div class="modal-card">
        <form method="POST" id="deleteCityForm" action="">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h3 class="modal-title">Xác Nhận Xóa Thành Phố</h3>
                <button type="button" class="modal-close-btn" onclick="closeDeleteModal()">✕</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 14px; color: #334155; margin-bottom: 12px;">
                    Bạn có chắc chắn muốn xóa thành phố <strong id="delete_city_name"></strong> khỏi hệ thống?
                </p>
                <div style="background-color: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 12px; font-size: 12.5px; color: #991B1B; line-height: 1.4;">
                    <strong>Quy tắc bảo vệ dữ liệu BR-17:</strong> Nếu thành phố này đang chứa các địa điểm du lịch, thao tác xóa sẽ bị từ chối để bảo vệ tính toàn vẹn của hệ thống.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Hủy</button>
                <button type="submit" class="btn-danger">Xác Nhận Xóa</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createCityModal').classList.add('active');
    }

    function closeCreateModal() {
        document.getElementById('createCityModal').classList.remove('active');
    }

    function openEditModal(city) {
        document.getElementById('editCityForm').action = '/admin/cities/' + city.id;
        document.getElementById('edit_name').value = city.name || '';
        document.getElementById('edit_code').value = city.code || '';
        document.getElementById('edit_region').value = city.region || '';
        document.getElementById('edit_is_active').value = city.is_active ? '1' : '0';
        document.getElementById('edit_image').value = city.image || '';
        document.getElementById('edit_description').value = city.description || '';

        document.getElementById('editCityModal').classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('editCityModal').classList.remove('active');
    }

    function openDeleteModal(city) {
        document.getElementById('deleteCityForm').action = '/admin/cities/' + city.id;
        document.getElementById('delete_city_name').textContent = city.name;
        document.getElementById('deleteCityModal').classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('deleteCityModal').classList.remove('active');
    }

    // Đóng modal khi bấm ra ngoài lớp overlay
    window.addEventListener('click', function(e) {
        const createModal = document.getElementById('createCityModal');
        const editModal = document.getElementById('editCityModal');
        const deleteModal = document.getElementById('deleteCityModal');
        if (e.target === createModal) {
            closeCreateModal();
        }
        if (e.target === editModal) {
            closeEditModal();
        }
        if (e.target === deleteModal) {
            closeDeleteModal();
        }
    });
</script>
@endsection
