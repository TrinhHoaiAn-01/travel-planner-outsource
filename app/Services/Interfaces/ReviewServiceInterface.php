<?php

namespace App\Services\Interfaces;

use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Giao diện định nghĩa hợp đồng dịch vụ Đánh giá & Nhận xét địa điểm (Destination Review).
 * Tuân thủ quy chuẩn MVC 3 lớp và danh mục Service Interface của dự án Travel Planner.
 */
interface ReviewServiceInterface
{
    /**
     * Lấy danh sách các nhận xét đã được phê duyệt công khai của địa điểm du lịch.
     */
    public function getApprovedReviews(Destination $destination): Collection;

    /**
     * Kiểm tra người dùng có đủ điều kiện tạo đánh giá mới cho địa điểm này hay không.
     * Quy tắc BR-01 & BR-08: Đã đăng nhập, tài khoản hoạt động và chưa đánh giá địa điểm này.
     */
    public function canCreateReview(User $user, Destination $destination): bool;

    /**
     * Tạo đánh giá và nhận xét mới cho địa điểm du lịch.
     *
     * @param array<string, mixed> $data Dữ liệu đầu vào gồm rating (1-5) và comment (tối đa 500 ký tự)
     */
    public function createReview(User $user, Destination $destination, array $data): Review;

    /**
     * Cập nhật nội dung hoặc số sao đánh giá hiện có.
     *
     * @param array<string, mixed> $data Dữ liệu cập nhật gồm rating và comment
     */
    public function updateReview(Review $review, array $data): Review;

    /**
     * Xóa vĩnh viễn đánh giá và tự động tính toán lại điểm rating trung bình của địa điểm.
     */
    public function deleteReview(Review $review): bool;

    /**
     * Tính toán lại điểm số đánh giá trung bình của địa điểm dựa trên các đánh giá đã duyệt.
     */
    public function recalculateDestinationRating(Destination $destination): float;

    /**
     * Phê duyệt đánh giá để hiển thị công khai trên hệ thống.
     */
    public function approveReview(Review $review): Review;

    /**
     * Tạm ẩn đánh giá khỏi giao diện người dùng.
     */
    public function hideReview(Review $review): Review;

    /**
     * Lấy đánh giá của chính người dùng hiện tại đối với địa điểm (nếu có).
     */
    public function getUserReviewForDestination(User $user, Destination $destination): ?Review;

    /**
     * Lấy thống kê phân bổ số lượng và tỷ lệ % theo từng mức 1 đến 5 sao của địa điểm.
     *
     * @return array<int, array{count: int, percentage: float}>
     */
    public function getRatingDistribution(Destination $destination): array;
}
