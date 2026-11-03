<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use App\Services\Interfaces\ReviewServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Lớp dịch vụ triển khai nghiệp vụ Đánh giá & Nhận xét địa điểm du lịch (Destination Review).
 * Đảm bảo các quy tắc nghiệp vụ BR-01, BR-06, BR-07, BR-08, toàn vẹn giao dịch và bảo mật.
 */
class ReviewService implements ReviewServiceInterface
{
    /**
     * Lấy danh sách nhận xét đã được duyệt để hiển thị công khai trên giao diện người dùng.
     */
    public function getApprovedReviews(Destination $destination): Collection
    {
        return $destination->approvedReviews()
            ->with('user')
            ->latest('created_at')
            ->get();
    }

    /**
     * Kiểm tra điều kiện tạo đánh giá mới của người dùng đối với địa điểm.
     * Người dùng phải đăng nhập, tài khoản hoạt động và chưa gửi đánh giá trước đó.
     */
    public function canCreateReview(User $user, Destination $destination): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return ! Review::where('user_id', $user->id)
            ->where('destination_id', $destination->id)
            ->exists();
    }

    /**
     * Tạo đánh giá mới và lưu vào cơ sở dữ liệu bên trong giao dịch an toàn (DB Transaction).
     *
     * @param array<string, mixed> $data Dữ liệu chứa rating (1-5) và comment (tối đa 500 ký tự)
     * @throws ValidationException Khi người dùng đã có đánh giá cho địa điểm này
     */
    public function createReview(User $user, Destination $destination, array $data): Review
    {
        return DB::transaction(function () use ($user, $destination, $data) {
            // Chặn trùng lặp đánh giá trên cùng một địa điểm (Quy tắc unique user_id + destination_id)
            $existingReview = Review::where('user_id', $user->id)
                ->where('destination_id', $destination->id)
                ->first();

            if ($existingReview) {
                throw ValidationException::withMessages([
                    'rating' => 'Bạn đã gửi đánh giá cho địa điểm này rồi. Vui lòng chỉnh sửa đánh giá hiện tại.',
                ]);
            }

            // Người dùng quản trị được duyệt ngay, tài khoản thành viên mặc định chuyển trạng thái chờ duyệt (Hình 26)
            $initialStatus = $user->isAdmin() ? Review::STATUS_APPROVED : Review::STATUS_PENDING;

            $review = Review::create([
                'user_id' => $user->id,
                'destination_id' => $destination->id,
                'rating' => (int) $data['rating'],
                'comment' => isset($data['comment']) ? trim((string) $data['comment']) : null,
                'status' => $initialStatus,
            ]);

            // Nếu được duyệt ngay thì tính toán lại điểm trung bình của địa điểm
            if ($initialStatus === Review::STATUS_APPROVED) {
                $this->recalculateDestinationRating($destination);
            }

            return $review;
        });
    }

    /**
     * Cập nhật thông tin số sao và nội dung nhận xét của đánh giá.
     *
     * @param array<string, mixed> $data
     */
    public function updateReview(Review $review, array $data): Review
    {
        return DB::transaction(function () use ($review, $data) {
            $user = auth()->user();

            // Nếu là người dùng thường chỉnh sửa, đưa về trạng thái chờ duyệt để kiểm duyệt lại nội dung
            $newStatus = ($user && $user->isAdmin()) ? $review->status : Review::STATUS_PENDING;

            $review->update([
                'rating' => (int) $data['rating'],
                'comment' => isset($data['comment']) ? trim((string) $data['comment']) : null,
                'status' => $newStatus,
            ]);

            // Cập nhật lại điểm trung bình của địa điểm tương ứng
            if ($review->destination) {
                $this->recalculateDestinationRating($review->destination);
            }

            return $review;
        });
    }

    /**
     * Xóa đánh giá và tính toán lại điểm rating trung bình của địa điểm.
     */
    public function deleteReview(Review $review): bool
    {
        return DB::transaction(function () use ($review) {
            $destination = $review->destination;
            $deleted = (bool) $review->delete();

            if ($destination) {
                $this->recalculateDestinationRating($destination);
            }

            return $deleted;
        });
    }

    /**
     * Tính toán lại điểm số đánh giá trung bình từ các nhận xét đã duyệt của địa điểm.
     */
    public function recalculateDestinationRating(Destination $destination): float
    {
        // Tính trung bình cộng cột rating của các đánh giá có trạng thái approved
        $averageRating = $destination->approvedReviews()->avg('rating');

        // Làm tròn 1 chữ số thập phân, mặc định 0.0 nếu chưa có đánh giá nào được duyệt
        $finalRating = $averageRating !== null ? round((float) $averageRating, 1) : 0.0;

        $destination->update([
            'rating' => $finalRating,
        ]);

        return $finalRating;
    }

    /**
     * Duyệt đánh giá và cập nhật lại điểm số trung bình của địa điểm.
     */
    public function approveReview(Review $review): Review
    {
        return DB::transaction(function () use ($review) {
            $review->update([
                'status' => Review::STATUS_APPROVED,
            ]);

            if ($review->destination) {
                $this->recalculateDestinationRating($review->destination);
            }

            return $review;
        });
    }

    /**
     * Ẩn đánh giá khỏi giao diện người dùng và cập nhật lại điểm trung bình.
     */
    public function hideReview(Review $review): Review
    {
        return DB::transaction(function () use ($review) {
            $review->update([
                'status' => Review::STATUS_HIDDEN,
            ]);

            if ($review->destination) {
                $this->recalculateDestinationRating($review->destination);
            }

            return $review;
        });
    }

    /**
     * Lấy đánh giá của chính người dùng hiện tại đối với địa điểm này.
     */
    public function getUserReviewForDestination(User $user, Destination $destination): ?Review
    {
        return Review::where('user_id', $user->id)
            ->where('destination_id', $destination->id)
            ->first();
    }

    /**
     * Thống kê phân bổ đánh giá theo từng mức 1, 2, 3, 4, 5 sao phục vụ hiển thị biểu đồ và thanh tỷ lệ.
     */
    public function getRatingDistribution(Destination $destination): array
    {
        $approvedReviews = $destination->approvedReviews()->get();
        $totalApproved = $approvedReviews->count();

        $distribution = [];

        // Khởi tạo và tính toán từ 5 sao xuống 1 sao
        for ($star = 5; $star >= 1; $star--) {
            $count = $approvedReviews->where('rating', $star)->count();
            $percentage = $totalApproved > 0 ? round(($count / $totalApproved) * 100, 1) : 0.0;

            $distribution[$star] = [
                'count' => $count,
                'percentage' => $percentage,
            ];
        }

        return $distribution;
    }

    /**
     * Lấy danh sách đánh giá của cá nhân người dùng có phân trang và hỗ trợ bộ lọc (trạng thái, số sao, từ khóa).
     *
     * @param array<string, mixed> $filters Mảng bộ lọc gồm 'status', 'rating', 'search'
     */
    public function getUserReviews(User $user, array $filters = [], int $perPage = 9): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Review::query()
            ->where('user_id', $user->id)
            ->with(['destination.city', 'destination.images']);

        // Bộ lọc theo trạng thái: pending, approved, hidden (nếu 'all' hoặc rỗng thì bỏ qua)
        if (! empty($filters['status']) && in_array($filters['status'], [Review::STATUS_PENDING, Review::STATUS_APPROVED, Review::STATUS_HIDDEN], true)) {
            $query->where('status', $filters['status']);
        }

        // Bộ lọc theo số sao: 1 đến 5
        if (! empty($filters['rating']) && is_numeric($filters['rating'])) {
            $rating = (int) $filters['rating'];
            if ($rating >= 1 && $rating <= 5) {
                $query->where('rating', $rating);
            }
        }

        // Bộ lọc tìm kiếm theo từ khóa (tìm trong tên địa điểm hoặc nội dung nhận xét)
        if (! empty($filters['search'])) {
            $keyword = trim((string) $filters['search']);
            $query->where(function ($q) use ($keyword) {
                $q->where('comment', 'like', "%{$keyword}%")
                    ->orWhereHas('destination', function ($destQuery) use ($keyword) {
                        $destQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Sắp xếp mới nhất trước
        return $query->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Thống kê tổng hợp số lượng đánh giá của người dùng theo từng trạng thái (tổng cộng, đã duyệt, chờ duyệt, đã ẩn).
     *
     * @return array{total: int, approved: int, pending: int, hidden: int, average_rating: float}
     */
    public function getUserReviewStats(User $user): array
    {
        $baseQuery = Review::where('user_id', $user->id);

        $total = (clone $baseQuery)->count();
        $approved = (clone $baseQuery)->where('status', Review::STATUS_APPROVED)->count();
        $pending = (clone $baseQuery)->where('status', Review::STATUS_PENDING)->count();
        $hidden = (clone $baseQuery)->where('status', Review::STATUS_HIDDEN)->count();

        $avg = (clone $baseQuery)->avg('rating');
        $averageRating = $avg !== null ? round((float) $avg, 1) : 0.0;

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'hidden' => $hidden,
            'average_rating' => $averageRating,
        ];
    }
}
