<?php

namespace App\Services;

use App\Models\Review;
use App\Services\Interfaces\ReviewModerationServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Lớp dịch vụ xử lý nghiệp vụ kiểm duyệt đánh giá khách hàng (Admin Review Moderation).
 */
class ReviewModerationService implements ReviewModerationServiceInterface
{
    /**
     * Lấy danh sách đánh giá kèm thông tin Người dùng và Địa điểm du lịch, hỗ trợ lọc theo trạng thái.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getReviews(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Review::query()->with(['user', 'destination']);

        // Lọc theo trạng thái kiểm duyệt (pending, approved, hidden)
        $status = $filters['status'] ?? 'all';
        if ($status !== 'all' && in_array($status, [Review::STATUS_PENDING, Review::STATUS_APPROVED, Review::STATUS_HIDDEN], true)) {
            $query->where('status', $status);
        }

        // Tìm kiếm theo từ khóa nếu có (tên người dùng, email, tên địa điểm hoặc nội dung nhận xét)
        if (! empty($filters['search'])) {
            $searchTerm = trim((string) $filters['search']);
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('comment', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                        $userQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('destination', function ($destQuery) use ($searchTerm) {
                        $destQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Sắp xếp các đánh giá mới nhất lên đầu danh sách
        return $query->latest('created_at')->paginate($perPage)->withQueryString();
    }

    /**
     * Đếm số lượng đánh giá theo từng trạng thái để hiển thị trên các thẻ tab bộ lọc và sidebar.
     *
     * @return array<string, int>
     */
    public function getReviewCounts(): array
    {
        return [
            'all' => Review::count(),
            'pending' => Review::where('status', Review::STATUS_PENDING)->count(),
            'approved' => Review::where('status', Review::STATUS_APPROVED)->count(),
            'hidden' => Review::where('status', Review::STATUS_HIDDEN)->count(),
        ];
    }

    /**
     * Lấy chi tiết một đánh giá kèm theo thông tin quan hệ người dùng và địa điểm.
     */
    public function getReviewDetails(Review $review): Review
    {
        return $review->loadMissing(['user', 'destination']);
    }

    /**
     * Duyệt đánh giá: Đổi trạng thái sang 'approved' để hiển thị công khai.
     */
    public function approveReview(Review $review): Review
    {
        $review->update([
            'status' => Review::STATUS_APPROVED,
        ]);

        return $review;
    }

    /**
     * Ẩn đánh giá: Đổi trạng thái sang 'hidden' để tạm ẩn khỏi phía người dùng.
     */
    public function hideReview(Review $review): Review
    {
        $review->update([
            'status' => Review::STATUS_HIDDEN,
        ]);

        return $review;
    }

    /**
     * Xóa vĩnh viễn bản ghi đánh giá khỏi cơ sở dữ liệu.
     */
    public function deleteReview(Review $review): bool
    {
        return (bool) $review->delete();
    }
}
