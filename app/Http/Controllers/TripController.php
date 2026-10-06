<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\TripServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TripController extends Controller
{
    /**
     * Khởi tạo TripController với ràng buộc TripServiceInterface.
     */
    public function __construct(
        private TripServiceInterface $tripService
    ) {
    }

    /**
     * Hiển thị danh sách chuyến đi của người dùng kèm tìm kiếm và lọc.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();

        // Thu thập các tiêu chí lọc từ truy vấn HTTP
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status', 'all'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        // Lấy danh sách chuyến đi có phân trang qua Service Interface
        $trips = $this->tripService->getUserTrips($userId, $filters, 9);

        // Lấy thống kê số lượng theo từng trạng thái để hiển thị badge số lượng trên các tab
        $counts = $this->tripService->getTripCountsByStatus($userId);

        return view('trips.index', [
            'trips' => $trips,
            'counts' => $counts,
            'currentStatus' => $filters['status'],
            'searchKeyword' => $filters['search'],
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
        ]);
    }
}
