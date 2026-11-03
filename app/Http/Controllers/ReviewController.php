<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Destination;
use App\Models\Review;
use App\Services\Interfaces\ReviewServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Controller xử lý các thao tác Thêm, Sửa, Xóa đánh giá và nhận xét của người dùng (User Review Controller).
 * Tuân thủ mô hình Controller mỏng (Thin Controller), ủy quyền toàn bộ nghiệp vụ cho ReviewServiceInterface.
 */
class ReviewController extends Controller
{
    /**
     * Tiêm phụ thuộc qua Service Interface (Dependency Injection).
     */
    public function __construct(
        protected ReviewServiceInterface $reviewService
    ) {}

    /**
     * Lưu nhận xét và đánh giá mới của người dùng cho một địa điểm cụ thể.
     * Quy tắc BR-01 & BR-08.
     */
    public function store(StoreReviewRequest $request, Destination $destination): JsonResponse|RedirectResponse
    {
        // Kiểm tra quyền tạo đánh giá qua Policy
        Gate::authorize('create', Review::class);

        $user = $request->user();

        // Kiểm tra xem người dùng đã từng đánh giá địa điểm này chưa để tránh gửi trùng
        if (! $this->reviewService->canCreateReview($user, $destination)) {
            $errorMessage = 'Bạn đã gửi đánh giá cho địa điểm này rồi. Bạn có thể chỉnh sửa đánh giá hiện có.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }

            return redirect()
                ->route('destinations.show', $destination->slug)
                ->with('error', $errorMessage)
                ->withErrors(['rating' => $errorMessage]);
        }

        // Gọi Service Interface thực thi lưu dữ liệu
        $review = $this->reviewService->createReview($user, $destination, $request->validated());

        $message = $user->isAdmin()
            ? 'Đánh giá của bạn đã được xuất bản công khai!'
            : 'Gửi đánh giá thành công! Đánh giá của bạn đang ở trạng thái chờ duyệt.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $review,
            ], 201);
        }

        return redirect()
            ->route('destinations.show', $destination->slug)
            ->with('success', $message);
    }

    /**
     * Hiển thị màn hình danh sách Quản lý trạng thái và quy tắc Review của User.
     * Áp dụng đúng quy chuẩn giao diện Section 4.3: Quản lý chuyến đi (Image 1 Style).
     */
    public function myReviews(Request $request): \Illuminate\View\View
    {
        $user = $request->user();

        // Thu thập các tiêu chí lọc: trạng thái, số sao và từ khóa tìm kiếm
        $filters = [
            'status' => $request->query('status', 'all'),
            'rating' => $request->query('rating'),
            'search' => $request->query('search'),
        ];

        // Lấy danh sách review có phân trang từ tầng dịch vụ
        $reviews = $this->reviewService->getUserReviews($user, $filters, 9);

        // Lấy dữ liệu thống kê tổng hợp số lượng bài đánh giá theo từng trạng thái
        $stats = $this->reviewService->getUserReviewStats($user);

        return view('reviews.my-reviews', compact('reviews', 'stats', 'filters'));
    }

    /**
     * Cập nhật số sao và nội dung nhận xét của đánh giá.
     * Quy tắc BR-06: Người dùng chỉ được sửa đánh giá do chính mình tạo (hoặc quyền Admin).
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse|RedirectResponse
    {
        // Kiểm tra quyền chỉnh sửa qua ReviewPolicy
        Gate::authorize('update', $review);

        $user = $request->user();
        $updatedReview = $this->reviewService->updateReview($review, $request->validated());

        $message = ($user && $user->isAdmin())
            ? 'Cập nhật đánh giá thành công!'
            : 'Cập nhật đánh giá thành công! Bài đánh giá đang ở trạng thái chờ duyệt lại nội dung.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $updatedReview,
            ]);
        }

        // Nếu thao tác từ trang Quản lý đánh giá của User thì quay lại đúng trang đó
        if ($request->input('redirect_to') === 'my-reviews' || str_contains((string) $request->headers->get('referer'), 'my-reviews')) {
            return redirect()
                ->route('reviews.my-reviews')
                ->with('success', $message);
        }

        $destinationSlug = $review->destination?->slug;

        return redirect()
            ->route('destinations.show', $destinationSlug ?? 'destinations.index')
            ->with('success', $message);
    }

    /**
     * Xóa đánh giá của người dùng và cập nhật lại điểm rating trung bình của địa điểm.
     * Quy tắc BR-06 & BR-14: User chỉ được xóa đánh giá của mình hoặc Admin có quyền xóa.
     */
    public function destroy(Request $request, Review $review): JsonResponse|RedirectResponse
    {
        // Kiểm tra quyền xóa qua ReviewPolicy
        Gate::authorize('delete', $review);

        $destination = $review->destination;
        $this->reviewService->deleteReview($review);

        $message = 'Đã xóa đánh giá thành công!';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        // Nếu thao tác từ trang Quản lý đánh giá của User thì quay lại đúng trang đó
        if ($request->input('redirect_to') === 'my-reviews' || str_contains((string) $request->headers->get('referer'), 'my-reviews')) {
            return redirect()
                ->route('reviews.my-reviews')
                ->with('success', $message);
        }

        return redirect()
            ->route('destinations.show', $destination?->slug ?? 'destinations.index')
            ->with('success', $message);
    }
}
