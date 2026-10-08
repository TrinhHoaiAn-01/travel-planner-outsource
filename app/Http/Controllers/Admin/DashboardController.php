<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\DashboardServiceInterface;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Khởi tạo DashboardController với DashboardServiceInterface.
     */
    public function __construct(
        private readonly DashboardServiceInterface $dashboardService
    ) {
    }

    /**
     * Hiển thị bảng điều khiển quản trị (Dashboard).
     *
     * @return View
     */
    public function index(): View
    {
        // Thu thập các thông số thống kê tổng hợp từ Service
        $summary = $this->dashboardService->getSummaryStatistics();
        $monthlyStats = $this->dashboardService->getMonthlyStatistics(2026);
        $bookingCategories = $this->dashboardService->getBookingCategories(4);
        $topDestinations = $this->dashboardService->getTopDestinations(4);
        $recentBookings = $this->dashboardService->getRecentBookings(5);

        // Trả về view admin.dashboard với các dữ liệu đã chuẩn bị
        return view('admin.dashboard', compact(
            'summary',
            'monthlyStats',
            'bookingCategories',
            'topDestinations',
            'recentBookings'
        ));
    }
}
