<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\Interfaces\FavoriteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller quản lý trang Yêu thích và thao tác Thả tim
 */
class FavoriteController extends Controller
{
    protected FavoriteServiceInterface $favoriteService;

    public function __construct(FavoriteServiceInterface $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    /**
     * Hiển thị trang danh sách địa điểm yêu thích của người dùng
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $favorites = $this->favoriteService->getUserFavorites($user, 8);
        
        // Lấy danh sách chuyến đi của user để phục vụ Modal "Thêm vào chuyến đi"
        $userTrips = Trip::where('user_id', $user->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('favorites.index', compact('favorites', 'userTrips'));
    }

    /**
     * Xử lý Thêm / Xóa yêu thích (Hỗ trợ cả AJAX và Form thường)
     */
    public function toggle(Request $request, int $destinationId): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $result = $this->favoriteService->toggleFavorite($user, $destinationId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }
}
