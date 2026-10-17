<?php

namespace App\Services\Interfaces;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Giao diện định nghĩa các nghiệp vụ kiểm duyệt đánh giá khách hàng dành cho Quản trị viên.
 */
interface ReviewModerationServiceInterface
{
    /**
     * Lấy danh sách các đánh giá có phân trang và lọc theo trạng thái kiểm duyệt.
     *
     * @param  array<string, mixed>  $filters  Các tham số lọc (status: all|pending|approved|hidden, keyword...)
     * @param  int  $perPage  Số lượng bản ghi trên mỗi trang
     */
    public function getReviews(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Lấy số lượng thống kê theo từng trạng thái đánh giá (Tất cả, Chờ duyệt, Đã duyệt, Đã ẩn).
     *
     * @return array<string, int>
     */
    public function getReviewCounts(): array;

    /**
     * Lấy thông tin chi tiết của một đánh giá kèm theo người dùng và địa điểm.
     */
    public function getReviewDetails(Review $review): Review;

    /**
     * Phê duyệt đánh giá để cho phép hiển thị công khai trên giao diện người dùng.
     */
    public function approveReview(Review $review): Review;

    /**
     * Ẩn đánh giá khỏi giao diện người dùng mà không xóa dữ liệu.
     */
    public function hideReview(Review $review): Review;

    /**
     * Xóa vĩnh viễn đánh giá vi phạm khỏi hệ thống.
     */
    public function deleteReview(Review $review): bool;
}
