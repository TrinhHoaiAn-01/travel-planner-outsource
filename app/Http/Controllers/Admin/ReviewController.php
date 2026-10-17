<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\Interfaces\ReviewModerationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller kiểm duyệt đánh giá khách hàng dành cho Quản trị viên (Admin Review Moderation).
 */
class ReviewController extends Controller
{
    public function __construct(
        protected ReviewModerationServiceInterface $reviewModerationService
    ) {}

    /**
     * Hiển thị màn hình danh sách kiểm duyệt đánh giá với các tab lọc theo trạng thái.
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->query('status', 'all');
        $reviews = $this->reviewModerationService->getReviews(['status' => $currentStatus], 15);
        $reviewCounts = $this->reviewModerationService->getReviewCounts();

        return view('admin.reviews.index', compact('reviews', 'reviewCounts', 'currentStatus'));
    }

    /**
     * Lấy dữ liệu chi tiết một đánh giá, trả về JSON cho modal xem chi tiết.
     */
    public function show(Review $review): JsonResponse
    {
        $reviewDetail = $this->reviewModerationService->getReviewDetails($review);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $reviewDetail->id,
                'user_name' => $reviewDetail->user?->name ?? 'Người dùng ẩn danh',
                'user_email' => $reviewDetail->user?->email ?? 'Không có email',
                'user_avatar' => $reviewDetail->user?->avatar,
                'destination_name' => $reviewDetail->destination?->name ?? 'Địa điểm',
                'destination_address' => $reviewDetail->destination?->address ?? '',
                'rating' => number_format((float) $reviewDetail->rating, 1),
                'comment' => $reviewDetail->comment ?? 'Không có nội dung nhận xét.',
                'status' => $reviewDetail->status,
                'created_at' => $reviewDetail->created_at ? $reviewDetail->created_at->format('d/m/Y H:i') : 'N/A',
                'created_date' => $reviewDetail->created_at ? $reviewDetail->created_at->format('d/m/Y') : 'N/A',
            ],
        ]);
    }

    /**
     * Phê duyệt đánh giá để hiển thị công khai trên hệ thống.
     */
    public function approve(Review $review): RedirectResponse
    {
        $this->reviewModerationService->approveReview($review);

        return redirect()->back()->with('success', 'Đã phê duyệt đánh giá thành công.');
    }

    /**
     * Tạm ẩn đánh giá khỏi giao diện người dùng.
     */
    public function hide(Review $review): RedirectResponse
    {
        $this->reviewModerationService->hideReview($review);

        return redirect()->back()->with('success', 'Đã ẩn đánh giá thành công.');
    }

    /**
     * Xóa vĩnh viễn đánh giá khỏi cơ sở dữ liệu.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->reviewModerationService->deleteReview($review);

        return redirect()->back()->with('success', 'Đã xóa đánh giá thành công.');
    }
}
