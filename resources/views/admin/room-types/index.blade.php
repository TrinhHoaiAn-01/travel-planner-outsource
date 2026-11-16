@extends('layouts.admin')

@section('title', 'Quản Lý Loại Phòng Lưu Trú')
@section('breadcrumb', 'Loại phòng')

@push('styles')
<style>
    .admin-header-bar,
    .admin-breadcrumbs {
        display: none !important;
    }

    .admin-content-body {
        padding: 24px 32px 48px 32px;
    }

    .page-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .page-main-title {
        font-size: 20px;
        font-weight: 700;
        color: #0F172A;
    }

    .btn-add-primary {
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

    .btn-add-primary:hover {
        background-color: #1D4ED8;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.35);
    }

    .page-breadcrumb {
        font-size: 13.5px;
        color: #64748B;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .page-breadcrumb a {
        color: #3B82F6;
        text-decoration: none;
        font-weight: 500;
    }

    .page-breadcrumb .sep {
        color: #94A3B8;
    }

    /* Thẻ thống kê */
    .stats-cards-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1280px) {
        .stats-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
    }

    @media (max-width: 640px) {
        .stats-cards-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }

    .stat-card-item {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        min-width: 0;
    }

    .stat-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-wrap svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .stat-icon-blue { background: #EFF6FF; color: #2563EB; }
    .stat-icon-emerald { background: #ECFDF5; color: #059669; }
    .stat-icon-amber { background: #FFFBEB; color: #D97706; }
    .stat-icon-purple { background: #FAF5FF; color: #7C3AED; }

    .stat-card-info {
        min-width: 0;
        flex: 1;
        overflow: hidden;
    }

    .stat-card-info h4 {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748B;
        margin: 0 0 3px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .stat-card-info .stat-number {
        font-size: 20px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Khối bộ lọc & Bảng */
    .filter-card-container {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .filter-form-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .filter-input-search {
        flex: 1;
        min-width: 240px;
        height: 40px;
        padding: 0 14px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13.5px;
        outline: none;
        transition: border-color 0.2s;
    }

    .filter-input-search:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .filter-select-box {
        height: 40px;
        padding: 0 32px 0 12px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13.5px;
        background: #FFFFFF;
        color: #0F172A;
        outline: none;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }

    .btn-filter-action {
        height: 40px;
        padding: 0 18px;
        background-color: #0F172A;
        color: #FFFFFF;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background-color 0.2s;
    }

    .btn-filter-action:hover {
        background-color: #1E293B;
    }

    .btn-filter-reset {
        height: 40px;
        padding: 0 14px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: #FFFFFF;
        color: #64748B;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-filter-reset:hover {
        background: #F8FAFC;
        color: #0F172A;
    }

    /* Bảng dữ liệu */
    .table-responsive-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        overflow-x: auto;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .data-table-main {
        width: 100%;
        min-width: 960px;
        border-collapse: collapse;
        text-align: left;
    }

    .data-table-main th {
        background: #F8FAFC;
        padding: 14px 18px;
        font-size: 12.5px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #E2E8F0;
        white-space: nowrap;
    }

    .data-table-main td {
        padding: 16px 18px;
        font-size: 13.5px;
        color: #334155;
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
        white-space: nowrap;
    }

    .data-table-main tr:hover td {
        background-color: #F8FAFC;
    }

    .room-title-strong {
        font-weight: 700;
        color: #0F172A;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .dest-link-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        background: #EFF6FF;
        color: #1D4ED8;
        border-radius: 9999px;
        font-size: 12.5px;
        font-weight: 600;
        text-decoration: none;
    }

    .dest-link-badge:hover {
        background: #DBEAFE;
    }

    .badge-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.25;
        white-space: nowrap;
    }

    .badge-in-stock { background: #ECFDF5; color: #059669; }
    .badge-out-of-stock { background: #FEF2F2; color: #DC2626; }

    .action-buttons-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-circle-action {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #CBD5E1;
        background: #FFFFFF;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-circle-action:hover {
        border-color: #2563EB;
        color: #2563EB;
        background: #EFF6FF;
    }

    .btn-circle-action.btn-del:hover {
        border-color: #EF4444;
        color: #EF4444;
        background: #FEF2F2;
    }

    /* Modal Styling */
    .custom-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
        z-index: 999;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .custom-modal-backdrop.is-active {
        display: flex;
    }

    .custom-modal-box {
        background: #FFFFFF;
        border-radius: 16px;
        width: 100%;
        max-width: 560px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        border-bottom: 1px solid #E2E8F0;
    }

    .modal-head h3 {
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        color: #0F172A;
    }

    .btn-modal-close {
        border: none;
        background: transparent;
        font-size: 20px;
        color: #94A3B8;
        cursor: pointer;
    }

    .btn-modal-close:hover {
        color: #0F172A;
    }

    .modal-body {
        padding: 20px 24px;
    }

    .form-group-field {
        margin-bottom: 16px;
    }

    .form-group-field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .form-control-input, .form-control-textarea, .form-control-select {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13.5px;
        color: #0F172A;
        outline: none;
        box-sizing: border-box;
    }

    .form-control-input:focus, .form-control-textarea:focus, .form-control-select:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .modal-foot {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 24px;
        background: #F8FAFC;
        border-top: 1px solid #E2E8F0;
    }

    .flash-alert-banner {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13.5px;
    }
    .flash-alert-success { background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; }
    .flash-alert-error { background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; }
</style>
@endpush

@section('content')
<div class="admin-content-body">
    <!-- Breadcrumb & Tiêu đề trang -->
    <div class="page-breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span class="sep">/</span>
        <span class="current">Loại phòng</span>
    </div>

    <div class="page-header-row">
        <h1 class="page-main-title">Quản Lý Loại Phòng Lưu Trú (Room Types)</h1>
        <button type="button" class="btn-add-primary" onclick="openCreateModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Thêm loại phòng mới
        </button>
    </div>

    <!-- 4 Thẻ thống kê tổng quan -->
    <div class="stats-cards-grid">
        <div class="stat-card-item">
            <div class="stat-icon-wrap stat-icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20M6 8v9"/></svg>
            </div>
            <div class="stat-card-info">
                <h4>Tổng số loại phòng</h4>
                <div class="stat-number">{{ number_format($statistics['total_room_types']) }}</div>
            </div>
        </div>

        <div class="stat-card-item">
            <div class="stat-icon-wrap stat-icon-emerald">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
            </div>
            <div class="stat-card-info">
                <h4>Tổng số phòng sẵn có</h4>
                <div class="stat-number">{{ number_format($statistics['total_rooms']) }}</div>
            </div>
        </div>

        <div class="stat-card-item">
            <div class="stat-icon-wrap stat-icon-amber">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v12M8 10h8"></path></svg>
            </div>
            <div class="stat-card-info">
                <h4>Giá thuê trung bình</h4>
                <div class="stat-number">{{ number_format($statistics['average_price'], 0, ',', '.') }}đ</div>
            </div>
        </div>

        <div class="stat-card-item">
            <div class="stat-icon-wrap stat-icon-purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div class="stat-card-info">
                <h4>Địa điểm có chỗ lưu trú</h4>
                <div class="stat-number">{{ number_format($statistics['destinations_with_rooms']) }}</div>
            </div>
        </div>
    </div>

    <!-- Khối Bộ Lọc & Tìm Kiếm -->
    <div class="filter-card-container">
        <form action="{{ route('admin.room-types.index') }}" method="GET" class="filter-form-row">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tìm theo tên hạng phòng, mô tả hoặc địa điểm..." class="filter-input-search">
            
            <select name="destination_id" class="filter-select-box">
                <option value="">Tất cả địa điểm du lịch</option>
                @foreach($destinations as $dest)
                    <option value="{{ $dest->id }}" {{ (string)($filters['destination_id'] ?? '') === (string)$dest->id ? 'selected' : '' }}>
                        {{ $dest->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn-filter-action">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Lọc dữ liệu
            </button>

            @if(!empty($filters['search']) || !empty($filters['destination_id']))
                <a href="{{ route('admin.room-types.index') }}" class="btn-filter-reset">✕ Đặt lại</a>
            @endif
        </form>
    </div>

    <!-- Bảng Dữ Liệu Loại Phòng -->
    <div class="table-responsive-card">
        <table class="data-table-main">
            <thead>
                <tr>
                    <th style="width: 60px; white-space: nowrap;">ID</th>
                    <th style="min-width: 200px; white-space: nowrap;">Tên Loại Phòng</th>
                    <th style="min-width: 160px; white-space: nowrap;">Địa Điểm Du Lịch</th>
                    <th style="min-width: 140px; white-space: nowrap;">Sức Chứa</th>
                    <th style="min-width: 150px; white-space: nowrap;">Giá Mỗi Đêm</th>
                    <th style="min-width: 140px; white-space: nowrap;">Số Lượng Phòng</th>
                    <th style="min-width: 120px; white-space: nowrap;">Tình Trạng</th>
                    <th style="text-align: right; width: 110px; white-space: nowrap;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roomTypes as $room)
                    <tr>
                        <td style="color: #94A3B8; font-weight: 600; white-space: nowrap;">#{{ $room->id }}</td>
                        <td>
                            <div class="room-title-strong">{{ $room->name }}</div>
                            @if($room->description)
                                <div style="font-size: 12.5px; color: #64748B; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $room->description }}
                                </div>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            @if($room->destination)
                                <a href="{{ route('destinations.show', $room->destination->slug) }}" target="_blank" class="dest-link-badge" title="Xem trang khách hàng">
                                    📍 {{ $room->destination->name }}
                                </a>
                            @else
                                <span style="color: #94A3B8;">(Chưa gắn địa điểm)</span>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; color: #1E293B; white-space: nowrap;">
                                <span style="color: #2563EB;">👥</span> {{ $room->capacity }} người lớn
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <span style="white-space: nowrap;">
                                <span style="font-weight: 700; color: #0066FF; font-size: 14px;">{{ number_format($room->price_per_night, 0, ',', '.') }}đ</span><span style="color: #64748B; font-size: 12.5px;"> / đêm</span>
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <span style="white-space: nowrap;">
                                <span style="font-weight: 700; color: #1E293B;">{{ $room->quantity }}</span> <span style="color: #64748B; font-size: 12.5px;">phòng</span>
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            @if($room->quantity > 0)
                                <span class="badge-status-pill badge-in-stock">Còn phòng</span>
                            @else
                                <span class="badge-status-pill badge-out-of-stock">Hết phòng</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div class="action-buttons-wrap" style="justify-content: flex-end;">
                                <button type="button" class="btn-circle-action" title="Chỉnh sửa loại phòng"
                                        onclick="openEditModal({{ $room->id }}, {{ $room->destination_id }}, '{{ addslashes($room->name) }}', {{ $room->capacity }}, {{ $room->price_per_night }}, {{ $room->quantity }}, '{{ addslashes($room->description ?? '') }}')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                </button>
                                <button type="button" class="btn-circle-action btn-del" title="Xóa loại phòng"
                                        onclick="openDeleteModal({{ $room->id }}, '{{ addslashes($room->name) }}')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 48px 16px; color: #64748B;">
                            <div style="font-size: 32px; margin-bottom: 8px;">🏨</div>
                            <div style="font-weight: 600; font-size: 15px; color: #0F172A; margin-bottom: 4px;">Chưa có loại phòng nào phù hợp</div>
                            <p style="margin: 0; font-size: 13.5px;">Bấm nút "Thêm loại phòng mới" ở trên để tạo hạng phòng lưu trú đầu tiên.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    @if($roomTypes->hasPages())
        <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
            {{ $roomTypes->links() }}
        </div>
    @endif
</div>

<!-- Modal 1: Thêm mới loại phòng -->
<div class="custom-modal-backdrop" id="createRoomTypeModal">
    <div class="custom-modal-box">
        <form action="{{ route('admin.room-types.store') }}" method="POST">
            @csrf
            <div class="modal-head">
                <h3>Thêm Mới Loại Phòng Lưu Trú</h3>
                <button type="button" class="btn-modal-close" onclick="closeModal('createRoomTypeModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group-field">
                    <label>Địa điểm du lịch / Khách sạn <span style="color:#EF4444;">*</span></label>
                    <select name="destination_id" class="form-control-select" required>
                        <option value="">-- Chọn địa điểm liên kết --</option>
                        @foreach($destinations as $dest)
                            <option value="{{ $dest->id }}">{{ $dest->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group-field">
                    <label>Tên loại phòng <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="name" class="form-control-input" placeholder="Ví dụ: Deluxe King Hướng Biển" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group-field">
                        <label>Sức chứa (người lớn) <span style="color:#EF4444;">*</span></label>
                        <input type="number" name="capacity" class="form-control-input" value="2" min="1" max="50" required>
                    </div>
                    <div class="form-group-field">
                        <label>Số lượng phòng trống <span style="color:#EF4444;">*</span></label>
                        <input type="number" name="quantity" class="form-control-input" value="10" min="0" max="1000" required>
                    </div>
                </div>

                <div class="form-group-field">
                    <label>Giá mỗi đêm (VNĐ) <span style="color:#EF4444;">*</span></label>
                    <input type="number" name="price_per_night" class="form-control-input" placeholder="Ví dụ: 1500000" min="0" step="1000" required>
                </div>

                <div class="form-group-field">
                    <label>Mô tả & Tiện nghi phòng</label>
                    <textarea name="description" rows="3" class="form-control-textarea" placeholder="Mô tả không gian phòng, loại giường, tiện ích đi kèm..."></textarea>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-filter-reset" onclick="closeModal('createRoomTypeModal')">Hủy bỏ</button>
                <button type="submit" class="btn-filter-action" style="background:#2563EB;">Lưu loại phòng</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Chỉnh sửa loại phòng -->
<div class="custom-modal-backdrop" id="editRoomTypeModal">
    <div class="custom-modal-box">
        <form action="" method="POST" id="editRoomTypeForm">
            @csrf
            @method('PUT')
            <div class="modal-head">
                <h3>Chỉnh Sửa Loại Phòng Lưu Trú</h3>
                <button type="button" class="btn-modal-close" onclick="closeModal('editRoomTypeModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group-field">
                    <label>Địa điểm du lịch / Khách sạn <span style="color:#EF4444;">*</span></label>
                    <select name="destination_id" id="editDestinationId" class="form-control-select" required>
                        <option value="">-- Chọn địa điểm liên kết --</option>
                        @foreach($destinations as $dest)
                            <option value="{{ $dest->id }}">{{ $dest->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group-field">
                    <label>Tên loại phòng <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="name" id="editName" class="form-control-input" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group-field">
                        <label>Sức chứa (người lớn) <span style="color:#EF4444;">*</span></label>
                        <input type="number" name="capacity" id="editCapacity" class="form-control-input" min="1" max="50" required>
                    </div>
                    <div class="form-group-field">
                        <label>Số lượng phòng trống <span style="color:#EF4444;">*</span></label>
                        <input type="number" name="quantity" id="editQuantity" class="form-control-input" min="0" max="1000" required>
                    </div>
                </div>

                <div class="form-group-field">
                    <label>Giá mỗi đêm (VNĐ) <span style="color:#EF4444;">*</span></label>
                    <input type="number" name="price_per_night" id="editPrice" class="form-control-input" min="0" step="1000" required>
                </div>

                <div class="form-group-field">
                    <label>Mô tả & Tiện nghi phòng</label>
                    <textarea name="description" id="editDescription" rows="3" class="form-control-textarea"></textarea>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-filter-reset" onclick="closeModal('editRoomTypeModal')">Hủy bỏ</button>
                <button type="submit" class="btn-filter-action" style="background:#2563EB;">Cập nhật thay đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Xác nhận xóa -->
<div class="custom-modal-backdrop" id="deleteRoomTypeModal">
    <div class="custom-modal-box" style="max-width: 440px;">
        <form action="" method="POST" id="deleteRoomTypeForm">
            @csrf
            @method('DELETE')
            <div class="modal-head" style="border-bottom:none;">
                <h3 style="color:#DC2626; display:flex; align-items:center; gap:8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Xác Nhận Xóa Loại Phòng
                </h3>
                <button type="button" class="btn-modal-close" onclick="closeModal('deleteRoomTypeModal')">&times;</button>
            </div>
            <div class="modal-body" style="padding-top:0;">
                <p style="font-size:14px; color:#475569; line-height:1.6; margin:0;">
                    Bạn có chắc chắn muốn xóa loại phòng <strong id="deleteRoomName" style="color:#0F172A;"></strong> không?
                    Thao tác này sẽ xóa vĩnh viễn dữ liệu nếu loại phòng chưa phát sinh đơn đặt phòng.
                </p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-filter-reset" onclick="closeModal('deleteRoomTypeModal')">Hủy bỏ</button>
                <button type="submit" class="btn-filter-action" style="background:#DC2626;">Đồng ý xóa</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createRoomTypeModal').classList.add('is-active');
    }

    function openEditModal(id, destinationId, name, capacity, price, quantity, description) {
        const form = document.getElementById('editRoomTypeForm');
        form.action = `/admin/room-types/${id}`;
        document.getElementById('editDestinationId').value = destinationId;
        document.getElementById('editName').value = name;
        document.getElementById('editCapacity').value = capacity;
        document.getElementById('editPrice').value = price;
        document.getElementById('editQuantity').value = quantity;
        document.getElementById('editDescription').value = description;
        document.getElementById('editRoomTypeModal').classList.add('is-active');
    }

    function openDeleteModal(id, name) {
        const form = document.getElementById('deleteRoomTypeForm');
        form.action = `/admin/room-types/${id}`;
        document.getElementById('deleteRoomName').textContent = `"${name}"`;
        document.getElementById('deleteRoomTypeModal').classList.add('is-active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('is-active');
    }
</script>
@endsection
