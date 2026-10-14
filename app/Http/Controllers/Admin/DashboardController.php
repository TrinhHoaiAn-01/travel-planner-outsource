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

    /**
     * Xuất báo cáo thống kê Dashboard ra tệp tin CSV tải về cho Quản trị viên.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportReport(): \Symfony\Component\HttpFoundation\Response
    {
        $csvContent = $this->dashboardService->exportReportData(2026);
        $fileName = 'bao-cao-thong-ke-travel-planner-2026.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
