@extends('layouts.admin')

@section('title', 'Kiểm Duyệt Đánh Giá Khách Hàng')
@section('page-title', 'Kiểm Duyệt Đánh Giá Khách Hàng')

@section('breadcrumb')
    <div class="admin-breadcrumbs">
        <a href="{{ route('admin.dashboard') }}">Admin</a>
        <span>/</span>
        <span>Kiểm duyệt đánh giá</span>
    </div>
@endsection

@section('content')
<div class="review-moderation-container">

    {{-- Thông báo phản hồi --}}
    @if(session('success'))
        <div class="alert-success-banner">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif

    {{-- Nhóm Tabs Lọc Theo Trạng Thái Khớp 100% Ảnh Mockup --}}
    <div class="tabs-filter-bar">
        <a href="{{ route('admin.reviews.index', ['status' => 'all']) }}" 
           class="tab-pill-btn {{ $currentStatus === 'all' ? 'active' : '' }}">
            <span>Tất cả ({{ $reviewCounts['all'] ?? 0 }})</span>
        </a>

        <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}" 
           class="tab-pill-btn {{ $currentStatus === 'pending' ? 'active' : '' }}">
            <span class="tab-icon">⏳</span>
            <span>Chờ duyệt (Pending)</span>
        </a>

        <a href="{{ route('admin.reviews.index', ['status' => 'approved']) }}" 
           class="tab-pill-btn {{ $currentStatus === 'approved' ? 'active' : '' }}">
            <span class="tab-icon tab-icon-approved">✔</span>
            <span>Đã duyệt (Approved)</span>
        </a>

        <a href="{{ route('admin.reviews.index', ['status' => 'hidden']) }}" 
           class="tab-pill-btn {{ $currentStatus === 'hidden' ? 'active' : '' }}">
            <span class="tab-icon tab-icon-hidden">👁</span>
            <span>Đã ẩn (Hidden)</span>
        </a>
    </div>

    {{-- Bảng Danh Sách Đánh Giá Khách Hàng --}}
    <div class="reviews-table-card">
        <div class="table-responsive">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th style="width: 22%;">Người đánh giá</th>
                        <th style="width: 20%;">Địa điểm</th>
                        <th style="width: 10%;">Số sao</th>
                        <th style="width: 24%;">Nội dung nhận xét</th>
                        <th style="width: 11%;">Ngày tạo</th>
                        <th style="width: 13%;">Trạng thái</th>
                        <th style="width: 15%; text-align: right;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            {{-- Người đánh giá --}}
                            <td>
                                <div class="reviewer-info">
                                    <span class="reviewer-name">{{ $review->user->name ?? 'Người dùng ẩn danh' }}</span>
                                    <span class="reviewer-email">{{ $review->user->email ?? 'Không có email' }}</span>
                                </div>
                            </td>

                            {{-- Địa điểm du lịch --}}
                            <td>
                                <span class="destination-name-text">{{ $review->destination->name ?? 'Địa điểm đã xóa' }}</span>
                            </td>

                            {{-- Số sao đánh giá --}}
                            <td>
                                <div class="rating-display-badge">
                                    <span class="star-symbol">★</span>
                                    <span class="star-score">{{ number_format((float) $review->rating, 1) }}</span>
                                </div>
                            </td>

                            {{-- Nội dung nhận xét rút gọn --}}
                            <td>
                                <div class="review-comment-snippet" title="{{ $review->comment }}">
                                    {{ Str::limit($review->comment, 40, '...') }}
                                </div>
                            </td>

                            {{-- Ngày tạo --}}
                            <td>
                                <span class="created-date-text">{{ $review->created_at ? $review->created_at->format('d/m/Y') : 'N/A' }}</span>
                            </td>

                            {{-- Trạng thái đánh giá --}}
                            <td>
                                @if($review->status === \App\Models\Review::STATUS_PENDING)
                                    <span class="status-badge badge-pending">
                                        <span class="badge-icon">⏳</span>
                                        <span>Pending</span>
                                    </span>
                                @elseif($review->status === \App\Models\Review::STATUS_APPROVED)
                                    <span class="status-badge badge-approved">
                                        <span class="badge-icon">✔</span>
                                        <span>Approved</span>
                                    </span>
                                @elseif($review->status === \App\Models\Review::STATUS_HIDDEN)
                                    <span class="status-badge badge-hidden">
                                        <span class="badge-icon">👁</span>
                                        <span>Hidden</span>
                                    </span>
                                @else
                                    <span class="status-badge badge-default">{{ ucfirst($review->status) }}</span>
                                @endif
                            </td>

                            {{-- Cụm nút tròn hành động --}}
                            <td>
                                <div class="actions-group-row">
                                    {{-- Nút Xem Chi Tiết (Màu Xanh Dương Nhạt) --}}
                                    @if($review->status !== \App\Models\Review::STATUS_HIDDEN)
                                        <button type="button" 
                                                class="circle-action-btn btn-view" 
                                                title="Xem chi tiết nhận xét"
                                                onclick="openReviewDetailModal({{ $review->id }})">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- Nút Phê Duyệt (Màu Xanh Lá - Hiển thị khi Chờ duyệt hoặc Đã ẩn) --}}
                                    @if($review->status === \App\Models\Review::STATUS_PENDING || $review->status === \App\Models\Review::STATUS_HIDDEN)
                                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" 
                                                    class="circle-action-btn btn-approve" 
                                                    title="Phê duyệt đánh giá">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="20 6 9 17 4 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Nút Ẩn Đánh Giá (Màu Xám Tro - Hiển thị khi Chờ duyệt hoặc Đã duyệt) --}}
                                    @if($review->status === \App\Models\Review::STATUS_PENDING || $review->status === \App\Models\Review::STATUS_APPROVED)
                                        <form action="{{ route('admin.reviews.hide', $review) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" 
                                                    class="circle-action-btn btn-hide" 
                                                    title="Ẩn đánh giá">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                                    <line x1="2" x2="22" y1="2" y2="22"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Nút Xóa (Màu Đỏ) --}}
                                    <button type="button" 
                                            class="circle-action-btn btn-delete" 
                                            title="Xóa đánh giá này"
                                            onclick="openDeleteModal({{ $review->id }}, '{{ addslashes($review->user->name ?? 'Người dùng') }}', '{{ addslashes($review->destination->name ?? 'Địa điểm') }}')">
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
                            <td colspan="7" class="empty-state-cell">
                                <div class="empty-state-content">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                    </svg>
                                    <p>Không tìm thấy đánh giá nào phù hợp với bộ lọc hiện tại.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="table-pagination-footer">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

</div>

{{-- MODAL 1: Xem Chi Tiết Đánh Giá Khách Hàng --}}
<div id="modal-review-detail" class="custom-modal-backdrop" style="display: none;">
    <div class="custom-modal-card">
        <div class="custom-modal-header">
            <h3 class="custom-modal-title">Chi Tiết Đánh Giá Khách Hàng</h3>
            <button type="button" class="btn-close-modal" onclick="closeModal('modal-review-detail')">✕</button>
        </div>
        <div class="custom-modal-body" id="modal-detail-content">
            <div class="modal-loading-spinner" id="modal-detail-loading">
                <div class="spinner-dot"></div>
                <span>Đang tải thông tin...</span>
            </div>
            <div id="modal-detail-body" style="display: none;">
                <div class="detail-row-item">
                    <label>Người đánh giá:</label>
                    <div class="detail-user-box">
                        <strong id="detail-user-name">Nguyễn Bảo Ngọc</strong>
                        <span id="detail-user-email">ngoc.travel@travelplanner.com</span>
                    </div>
                </div>

                <div class="detail-row-item">
                    <label>Địa điểm du lịch:</label>
                    <span id="detail-destination-name" class="detail-bold-text">Sun World Bà Nà Hills</span>
                </div>

                <div class="detail-row-item">
                    <label>Mức độ đánh giá:</label>
                    <div class="detail-rating-wrapper">
                        <span class="star-symbol" style="color: #f59e0b; font-size: 16px;">★</span>
                        <strong id="detail-rating-score" style="color: #f59e0b; font-size: 16px;">5.0</strong>
                        <span style="color: #64748b; font-size: 13px;">(trên thang điểm 5 sao)</span>
                    </div>
                </div>

                <div class="detail-row-item">
                    <label>Ngày gửi đánh giá:</label>
                    <span id="detail-created-at" style="color: #334155; font-size: 14px;">28/09/2026 10:30</span>
                </div>

                <div class="detail-row-item">
                    <label>Trạng thái hiện tại:</label>
                    <span id="detail-status-badge"></span>
                </div>

                <div class="detail-row-item">
                    <label>Nội dung nhận xét chi tiết:</label>
                    <div id="detail-comment-text" class="detail-comment-box">
                        Cầu Vàng sáng sớm siêu đẹp và ít đông đúc...
                    </div>
                </div>
            </div>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal('modal-review-detail')">Đóng</button>
            <div id="modal-detail-actions" style="display: inline-flex; gap: 8px;">
                {{-- Các nút duyệt / ẩn sẽ được cập nhật động bằng javascript --}}
            </div>
        </div>
    </div>
</div>

{{-- MODAL 2: Xác Nhận Xóa Đánh Giá --}}
<div id="modal-delete-review" class="custom-modal-backdrop" style="display: none;">
    <div class="custom-modal-card modal-sm">
        <form id="form-delete-review" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="custom-modal-header">
                <h3 class="custom-modal-title" style="color: #EF4444;">Xác Nhận Xóa Đánh Giá</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('modal-delete-review')">✕</button>
            </div>
            <div class="custom-modal-body">
                <div class="delete-warning-box">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <p style="margin: 0; font-size: 14px; color: #1E293B;">
                        Bạn có chắc chắn muốn xóa đánh giá của <strong id="delete-user-name">Người dùng</strong> cho địa điểm <strong id="delete-destination-name">Địa điểm</strong>?
                    </p>
                    <p style="margin: 6px 0 0 0; font-size: 12.5px; color: #64748B;">
                        Hành động này sẽ xóa vĩnh viễn dữ liệu và không thể hoàn tác.
                    </p>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="modal-btn modal-btn-secondary" onclick="closeModal('modal-delete-review')">Hủy bỏ</button>
                <button type="submit" class="modal-btn modal-btn-danger">Xác nhận xóa</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ----------------------------------------------------
   GIAO DIỆN KIỂM DUYỆT ĐÁNH GIÁ (PIXEL-PERFECT MOCKUP)
---------------------------------------------------- */
.review-moderation-container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Alert Banner */
.alert-success-banner {
    background-color: #ECFDF5;
    border: 1px solid #A7F3D0;
    color: #065F46;
    padding: 12px 18px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    font-size: 14px;
}
.btn-close-alert {
    margin-left: auto;
    background: none;
    border: none;
    font-size: 16px;
    color: #065F46;
    cursor: pointer;
}

/* Tabs Filter Bar */
.tabs-filter-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}

.tab-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px;
    border-radius: 9999px;
    font-size: 13.5px;
    font-weight: 500;
    color: #1e293b;
    text-decoration: none;
    background-color: transparent;
    transition: all 0.2s ease;
}

.tab-pill-btn:hover {
    background-color: #e2e8f0;
    color: #0f172a;
}

.tab-pill-btn.active {
    background-color: #2563EB;
    color: #FFFFFF;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
}

.tab-icon {
    font-size: 14px;
    display: inline-flex;
    align-items: center;
}

.tab-icon-approved {
    color: #16A34A;
    font-weight: bold;
}

.tab-icon-hidden {
    color: #64748B;
}

.tab-pill-btn.active .tab-icon,
.tab-pill-btn.active .tab-icon-approved,
.tab-pill-btn.active .tab-icon-hidden {
    color: #FFFFFF;
}

/* Table Card */
.reviews-table-card {
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.admin-data-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.admin-data-table th {
    background-color: #FFFFFF;
    padding: 16px 20px;
    font-size: 13px;
    font-weight: 700;
    color: #0F172A;
    border-bottom: 1px solid #F1F5F9;
    white-space: nowrap;
}

.admin-data-table td {
    padding: 16px 20px;
    vertical-align: middle;
    border-bottom: 1px solid #F8FAFC;
    font-size: 13.5px;
}

.admin-data-table tbody tr:hover {
    background-color: #F8FAFC;
}

/* Reviewer Info */
.reviewer-info {
    display: flex;
    flex-direction: column;
}
.reviewer-name {
    font-size: 14px;
    font-weight: 700;
    color: #0F172A;
}
.reviewer-email {
    font-size: 12.5px;
    color: #64748B;
    margin-top: 3px;
}

/* Destination Name */
.destination-name-text {
    font-size: 14px;
    font-weight: 700;
    color: #0F172A;
}

/* Rating Badge */
.rating-display-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.star-symbol {
    color: #F59E0B;
    font-size: 15px;
}
.star-score {
    color: #F59E0B;
    font-weight: 700;
    font-size: 13.5px;
}

/* Comment snippet */
.review-comment-snippet {
    max-width: 320px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #334155;
    font-size: 13.5px;
}

/* Created Date */
.created-date-text {
    color: #0F172A;
    font-size: 13px;
    font-weight: 500;
}

/* Status Badges */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.badge-pending {
    background-color: #FEF3C7;
    border: 1px solid #FDE68A;
    color: #D97706;
}

.badge-approved {
    background-color: #DCFCE7;
    border: 1px solid #BBF7D0;
    color: #16A34A;
}

.badge-hidden {
    background-color: #F3F4F6;
    border: 1px solid #E5E7EB;
    color: #6B7280;
}

.badge-icon {
    font-size: 12px;
    display: inline-flex;
    align-items: center;
}

/* Circular Action Buttons */
.actions-group-row {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
}

.circle-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #FFFFFF;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
}

/* Nút Xem (Xanh dương nhạt) */
.circle-action-btn.btn-view {
    border: 1.5px solid #38BDF8;
    color: #0EA5E9;
}
.circle-action-btn.btn-view:hover {
    background-color: #F0F9FF;
    border-color: #0EA5E9;
    transform: translateY(-1px);
}

/* Nút Duyệt (Xanh lá) */
.circle-action-btn.btn-approve {
    border: 1.5px solid #34D399;
    color: #10B981;
}
.circle-action-btn.btn-approve:hover {
    background-color: #ECFDF5;
    border-color: #10B981;
    transform: translateY(-1px);
}

/* Nút Ẩn (Xám tro) */
.circle-action-btn.btn-hide {
    border: 1.5px solid #94A3B8;
    color: #64748B;
}
.circle-action-btn.btn-hide:hover {
    background-color: #F1F5F9;
    border-color: #64748B;
    transform: translateY(-1px);
}

/* Nút Xóa (Đỏ) */
.circle-action-btn.btn-delete {
    border: 1.5px solid #F87171;
    color: #EF4444;
}
.circle-action-btn.btn-delete:hover {
    background-color: #FEF2F2;
    border-color: #EF4444;
    transform: translateY(-1px);
}

/* Empty State */
.empty-state-cell {
    padding: 48px 20px;
    text-align: center;
}
.empty-state-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    color: #64748B;
    font-size: 14px;
}

/* Pagination Footer */
.table-pagination-footer {
    padding: 16px 20px;
    border-top: 1px solid #F1F5F9;
}

/* ----------------------------------------------------
   MODALS POPUP STYLES
---------------------------------------------------- */
.custom-modal-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(3px);
    z-index: 999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.custom-modal-card {
    background: #FFFFFF;
    border-radius: 16px;
    width: 100%;
    max-width: 580px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    animation: modalScaleIn 0.2s ease-out;
}

.custom-modal-card.modal-sm {
    max-width: 440px;
}

@keyframes modalScaleIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.custom-modal-header {
    padding: 18px 24px;
    border-bottom: 1px solid #F1F5F9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.custom-modal-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #0F172A;
}

.btn-close-modal {
    background: none;
    border: none;
    font-size: 16px;
    color: #94A3B8;
    cursor: pointer;
}
.btn-close-modal:hover {
    color: #0F172A;
}

.custom-modal-body {
    padding: 20px 24px;
    max-height: 75vh;
    overflow-y: auto;
}

.custom-modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #F1F5F9;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    background-color: #F8FAFC;
}

.modal-btn {
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
}

.modal-btn-secondary {
    background-color: #E2E8F0;
    color: #334155;
}
.modal-btn-secondary:hover {
    background-color: #CBD5E1;
}

.modal-btn-primary {
    background-color: #2563EB;
    color: #FFFFFF;
}
.modal-btn-primary:hover {
    background-color: #1D4ED8;
}

.modal-btn-success {
    background-color: #10B981;
    color: #FFFFFF;
}
.modal-btn-success:hover {
    background-color: #059669;
}

.modal-btn-warning {
    background-color: #64748B;
    color: #FFFFFF;
}
.modal-btn-warning:hover {
    background-color: #475569;
}

.modal-btn-danger {
    background-color: #EF4444;
    color: #FFFFFF;
}
.modal-btn-danger:hover {
    background-color: #DC2626;
}

/* Detail Modal Items */
.detail-row-item {
    margin-bottom: 16px;
}
.detail-row-item label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.detail-user-box {
    display: flex;
    flex-direction: column;
}
.detail-user-box strong {
    font-size: 15px;
    color: #0F172A;
}
.detail-user-box span {
    font-size: 13px;
    color: #64748B;
}
.detail-bold-text {
    font-size: 15px;
    font-weight: 700;
    color: #0F172A;
}
.detail-rating-wrapper {
    display: flex;
    align-items: center;
    gap: 6px;
}
.detail-comment-box {
    background-color: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    padding: 12px 14px;
    font-size: 14px;
    line-height: 1.6;
    color: #1E293B;
    white-space: pre-wrap;
}

.modal-loading-spinner {
    padding: 30px;
    text-align: center;
    color: #64748B;
    font-size: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}
.spinner-dot {
    width: 28px;
    height: 28px;
    border: 3px solid #E2E8F0;
    border-top-color: #2563EB;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}

.delete-warning-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    padding: 10px 0;
}
</style>

<script>
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Mở Modal Xem Chi Tiết và gọi Ajax lấy dữ liệu
function openReviewDetailModal(reviewId) {
    const modal = document.getElementById('modal-review-detail');
    const loading = document.getElementById('modal-detail-loading');
    const content = document.getElementById('modal-detail-body');
    const actionsContainer = document.getElementById('modal-detail-actions');

    if (!modal) return;

    modal.style.display = 'flex';
    loading.style.display = 'flex';
    content.style.display = 'none';
    actionsContainer.innerHTML = '';

    fetch(`/admin/reviews/${reviewId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(res => {
        if (res.success && res.data) {
            const data = res.data;
            document.getElementById('detail-user-name').textContent = data.user_name;
            document.getElementById('detail-user-email').textContent = data.user_email;
            document.getElementById('detail-destination-name').textContent = data.destination_name;
            document.getElementById('detail-rating-score').textContent = data.rating;
            document.getElementById('detail-created-at').textContent = data.created_at;
            document.getElementById('detail-comment-text').textContent = data.comment;

            // Render status badge
            const statusBadgeEl = document.getElementById('detail-status-badge');
            if (data.status === 'pending') {
                statusBadgeEl.innerHTML = '<span class="status-badge badge-pending"><span class="badge-icon">⏳</span> Pending</span>';
            } else if (data.status === 'approved') {
                statusBadgeEl.innerHTML = '<span class="status-badge badge-approved"><span class="badge-icon">✔</span> Approved</span>';
            } else {
                statusBadgeEl.innerHTML = '<span class="status-badge badge-hidden"><span class="badge-icon">👁</span> Hidden</span>';
            }

            // Render action buttons
            let actionsHtml = '';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            if (data.status === 'pending' || data.status === 'hidden') {
                actionsHtml += `
                    <form action="/admin/reviews/${data.id}/approve" method="POST" style="display:inline;">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <button type="submit" class="modal-btn modal-btn-success">✔ Duyệt Đánh Giá</button>
                    </form>
                `;
            }

            if (data.status === 'pending' || data.status === 'approved') {
                actionsHtml += `
                    <form action="/admin/reviews/${data.id}/hide" method="POST" style="display:inline;">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <button type="submit" class="modal-btn modal-btn-warning">👁 Ẩn Đánh Giá</button>
                    </form>
                `;
            }

            actionsContainer.innerHTML = actionsHtml;

            loading.style.display = 'none';
            content.style.display = 'block';
        }
    })
    .catch(err => {
        console.error(err);
        loading.innerHTML = '<p style="color:#EF4444;">Không thể tải dữ liệu chi tiết đánh giá.</p>';
    });
}

// Mở Modal Xác Nhận Xóa
function openDeleteModal(reviewId, userName, destinationName) {
    const modal = document.getElementById('modal-delete-review');
    const form = document.getElementById('form-delete-review');
    const userEl = document.getElementById('delete-user-name');
    const destEl = document.getElementById('delete-destination-name');

    if (modal && form) {
        form.action = `/admin/reviews/${reviewId}`;
        if (userEl) userEl.textContent = userName;
        if (destEl) destEl.textContent = destinationName;
        modal.style.display = 'flex';
    }
}

// Đóng modal khi click ra ngoài card
window.addEventListener('click', function(event) {
    const modalDetail = document.getElementById('modal-review-detail');
    const modalDelete = document.getElementById('modal-delete-review');

    if (event.target === modalDetail) {
        modalDetail.style.display = 'none';
    }
    if (event.target === modalDelete) {
        modalDelete.style.display = 'none';
    }
});
</script>
@endsection
