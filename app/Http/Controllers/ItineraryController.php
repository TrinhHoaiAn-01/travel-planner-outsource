<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\Interfaces\ItineraryServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Controller quản lý hiển thị lịch trình chuyến đi theo từng ngày.
 * Thành viên phụ trách: Phạm Thị Ngọc Ái (MSSV: 24211TT3767 - Chức năng 5).
 * Tuân thủ quy chuẩn MVC mỏng (Thin Controller) và gọi qua Service Interface.
 */
class ItineraryController extends Controller
{
    /**
     * Khởi tạo ItineraryController với ràng buộc ItineraryServiceInterface.
     */
    public function __construct(
        private ItineraryServiceInterface $itineraryService
    ) {
    }

    /**
     * Hiển thị chi tiết lịch trình chuyến đi theo từng ngày.
     * Tuyến: GET /trips/{trip}/itinerary (Bảng 22 trong báo cáo).
     *
     * @param Request $request
     * @param Trip $trip Chuyến đi cần xem lịch trình
     * @return View
     */
    public function index(Request $request, Trip $trip): View
    {
        // 1. Kiểm tra quyền sở hữu chuyến đi (Quy tắc BR-01, BR-03 chống lỗi bảo mật IDOR)
        Gate::authorize('view', $trip);

        // 2. Nhận tham số day_number từ query string (nếu có)
        $dayNumber = $request->query('day_number') ? (int) $request->query('day_number') : null;

        // 3. Gọi Service Interface xử lý toàn bộ logic nghiệp vụ
        $data = $this->itineraryService->getTripItinerary($trip, $dayNumber);

        // 4. Trả về giao diện Blade hiển thị lịch trình theo ngày
        return view('itinerary.index', $data);
    }
}
