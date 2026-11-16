<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\Interfaces\AiItineraryServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller xử lý tính năng AI tự động sinh và đánh giá lịch trình cho chuyến đi.
 * Thành viên phụ trách: Trần Văn Trọng (22211TT0337) - Mục 11.
 * Tuân thủ nghiêm ngặt MVC 3 lớp, bảo vệ quyền sở hữu dữ liệu BR-03.
 */
class TripAiItineraryController extends Controller
{
    /**
     * Khởi tạo Controller tiêm phụ thuộc AiItineraryServiceInterface.
     *
     * @param AiItineraryServiceInterface $aiItineraryService
     */
    public function __construct(
        private readonly AiItineraryServiceInterface $aiItineraryService
    ) {
    }

    /**
     * Xử lý yêu cầu tự động tạo lịch trình bằng AI cho chuyến đi của người dùng.
     *
     * @param Request $request
     * @param Trip $trip
     * @return RedirectResponse
     */
    public function generate(Request $request, Trip $trip): RedirectResponse
    {
        // Kiểm tra quyền sở hữu chuyến đi (BR-03: User chỉ được thao tác trên Trip của mình)
        $user = Auth::user();
        if ($trip->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'Bạn không có quyền quản lý lịch trình của chuyến đi này.');
        }

        $validated = $request->validate([
            'style' => ['nullable', 'string', 'in:balanced,relaxed,active,culture,foodie'],
            'mode' => ['nullable', 'string', 'in:replace,fill_empty'],
        ]);

        $createdCount = $this->aiItineraryService->generateSmartItinerary($trip, $validated);

        return redirect()
            ->route('trips.show', $trip->id)
            ->with('success', "Trí tuệ nhân tạo (AI) đã tự động sinh thành công {$createdCount} hoạt động được phân bổ khoa học theo từng ngày cho chuyến đi của bạn!");
    }
}

