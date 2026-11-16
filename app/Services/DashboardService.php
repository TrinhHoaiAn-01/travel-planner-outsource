<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use App\Services\Interfaces\DashboardServiceInterface;
use Carbon\Carbon;

class DashboardService implements DashboardServiceInterface
{
    /**
     * Tính toán chuỗi tỷ lệ tăng trưởng so với kỳ trước.
     *
     * @param int|float $current
     * @param int|float $previous
     * @return string
     */
    private function calculateGrowth(int|float $current, int|float $previous): string
    {
        if ($previous <= 0) {
            return $current > 0 ? '+100.0%' : '+0.0%';
        }

        $rate = round((($current - $previous) / $previous) * 100, 1);
        $prefix = $rate >= 0 ? '+' : '';

        return $prefix . number_format($rate, 1, '.', '') . '%';
    }

    /**
     * Lấy các chỉ số thống kê tổng quan trên Dashboard từ dữ liệu thực tế trong CSDL.
     *
     * @return array<string, mixed>
     */
    public function getSummaryStatistics(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Tổng số người dùng
        $totalUsers = User::count();
        $usersThisMonth = User::where('created_at', '>=', $startOfMonth)->count();
        $usersLastMonth = User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $userGrowth = $this->calculateGrowth($usersThisMonth, $usersLastMonth);

        // 2. Tổng số điểm đến
        $totalDestinations = Destination::count();
        $destThisMonth = Destination::where('created_at', '>=', $startOfMonth)->count();
        $destLastMonth = Destination::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $destGrowth = $this->calculateGrowth($destThisMonth, $destLastMonth);

        // 3. Tổng số đơn đặt phòng
        $totalBookings = Booking::count();
        $bookingsThisMonth = Booking::where('created_at', '>=', $startOfMonth)->count();
        $bookingsLastMonth = Booking::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $bookingGrowth = $this->calculateGrowth($bookingsThisMonth, $bookingsLastMonth);

        // 4. Tổng doanh thu thực tế
        $totalRevenue = (float) Payment::where('status', 'completed')->sum('amount');
        if ($totalRevenue <= 0) {
            $totalRevenue = (float) Booking::whereIn('status', ['completed', 'confirmed'])->sum('total_price');
        }

        $revenueThisMonth = (float) Payment::where('status', 'completed')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('amount');
        if ($revenueThisMonth <= 0) {
            $revenueThisMonth = (float) Booking::whereIn('status', ['completed', 'confirmed'])
                ->where('created_at', '>=', $startOfMonth)
                ->sum('total_price');
        }

        $revenueLastMonth = (float) Payment::where('status', 'completed')
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');
        if ($revenueLastMonth <= 0) {
            $revenueLastMonth = (float) Booking::whereIn('status', ['completed', 'confirmed'])
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->sum('total_price');
        }
        $revenueGrowth = $this->calculateGrowth($revenueThisMonth, $revenueLastMonth);

        // Các chỉ số bổ sung hỗ trợ báo cáo và tương thích ngược
        $totalTrips = Trip::count();
        $tripsThisMonth = Trip::where('created_at', '>=', $startOfMonth)->count();
        $tripsLastMonth = Trip::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $tripGrowth = $this->calculateGrowth($tripsThisMonth, $tripsLastMonth);

        $pendingReviews = Review::where('status', 'pending')->count();
        $citiesCount = City::count();

        return [
            'total_users' => number_format($totalUsers, 0, ',', '.'),
            'user_growth' => $userGrowth,
            'total_destinations' => number_format($totalDestinations, 0, ',', '.'),
            'destination_growth' => $destGrowth,
            'total_bookings' => number_format($totalBookings, 0, ',', '.'),
            'booking_growth' => $bookingGrowth,
            'total_revenue' => number_format($totalRevenue, 0, ',', '.') . ' đ',
            'revenue_growth' => $revenueGrowth,
            'total_trips' => number_format($totalTrips, 0, ',', '.'),
            'trip_growth' => $tripGrowth,
            'destination_note' => 'Tại ' . $citiesCount . ' tỉnh thành',
            'pending_reviews' => $pendingReviews,
            'system_status' => 'Hoạt động (Realtime)',
        ];
    }

    /**
     * Lấy dữ liệu biểu đồ đặt booking theo từng tháng trong năm thực tế từ CSDL.
     *
     * @param int $year
     * @return array<string, mixed>
     */
    public function getMonthlyStatistics(int $year = 2026): array
    {
        $months = [];
        $monthCounts = [];

        for ($m = 1; $m <= 12; $m++) {
            $count = Booking::whereYear('created_at', $year)
                ->whereMonth('created_at', $m)
                ->count();

            $monthCounts[$m] = $count;
        }

        $maxValue = ! empty($monthCounts) ? max($monthCounts) : 0;
        $peakMonth = 1;
        $maxCount = 0;

        foreach ($monthCounts as $m => $count) {
            if ($count > $maxCount) {
                $maxCount = $count;
                $peakMonth = $m;
            }

            // Tính tỷ lệ % chiều cao của thanh biểu đồ
            if ($maxValue > 0 && $count > 0) {
                $height = min(95, max(12, (int) round(($count / $maxValue) * 85) + 10)) . '%';
            } else {
                $height = '8%';
            }

            $months[] = [
                'label' => (string) $m,
                'month_name' => 'Tháng ' . $m,
                'value' => $count,
                'height' => $height,
            ];
        }

        // Tăng trưởng số lượng booking so với năm trước
        $thisYearTotal = Booking::whereYear('created_at', $year)->count();
        $lastYearTotal = Booking::whereYear('created_at', $year - 1)->count();
        $yearGrowth = $this->calculateGrowth($thisYearTotal, $lastYearTotal);
        $growthPrefix = str_starts_with($yearGrowth, '-') ? 'Giảm ' : 'Tăng ';

        return [
            'year' => $year,
            'title' => 'Thống Kê Lượt Đặt Phòng Theo Tháng',
            'subtitle' => 'Số lượt đặt phòng trong năm ' . $year,
            'growth_info' => $growthPrefix . ltrim($yearGrowth, '+-') . ' so với cùng kỳ năm trước',
            'peak_info' => 'Tháng ' . $peakMonth . ' (' . $maxCount . ' lượt)',
            'months' => $months,
        ];
    }

    /**
     * Lấy tỷ lệ đặt phòng theo loại hình lưu trú và danh mục thực tế từ CSDL.
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getBookingCategories(int $limit = 4): array
    {
        $totalBookings = Booking::count();

        // Lấy danh mục và đếm số lượng đơn đặt phòng thực tế
        $categories = Category::withCount(['destinations as bookings_count' => function ($query) {
            $query->join('room_types', 'destinations.id', '=', 'room_types.destination_id')
                  ->join('bookings', 'room_types.id', '=', 'bookings.room_type_id');
        }])
        ->orderByDesc('bookings_count')
        ->take($limit)
        ->get();

        $palette = ['#2563EB', '#06B6D4', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'];
        $result = [];
        $rank = 1;

        foreach ($categories as $index => $cat) {
            $count = (int) $cat->bookings_count;
            $percentage = $totalBookings > 0 ? (int) round(($count / $totalBookings) * 100) : 0;

            $result[] = [
                'rank' => $rank++,
                'name' => $cat->name,
                'percentage' => $percentage,
                'color' => $palette[$index % count($palette)],
            ];
        }

        // Nếu danh mục trong DB ít hơn limit, bổ sung các danh mục mặc định để giữ layout cân đối
        if (count($result) < $limit) {
            $defaultNames = ['Khách Sạn', 'Resort & Nghỉ Dưỡng', 'Homestay', 'Căn Hộ & Villa'];
            foreach ($defaultNames as $name) {
                if (count($result) >= $limit) {
                    break;
                }
                $existingNames = array_column($result, 'name');
                if (! in_array($name, $existingNames, true)) {
                    $result[] = [
                        'rank' => count($result) + 1,
                        'name' => $name,
                        'percentage' => 0,
                        'color' => $palette[count($result) % count($palette)],
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Lấy danh sách điểm đến phổ biến nhất kèm tỷ lệ phần trăm (hỗ trợ tương thích ngược).
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getTopDestinations(int $limit = 4): array
    {
        $destinations = Destination::withCount('roomTypes')
            ->orderByDesc('rating')
            ->take($limit)
            ->get();

        $palette = ['#2563EB', '#06B6D4', '#10B981', '#F59E0B'];
        $result = [];
        $rank = 1;

        foreach ($destinations as $index => $dest) {
            $result[] = [
                'rank' => $rank++,
                'name' => $dest->name,
                'percentage' => (int) round(($dest->rating / 5.0) * 100),
                'color' => $palette[$index % count($palette)],
            ];
        }

        if (empty($result)) {
            return [
                ['rank' => 1, 'name' => 'Đà Nẵng', 'percentage' => 38, 'color' => '#2563EB'],
                ['rank' => 2, 'name' => 'Vịnh Hạ Long', 'percentage' => 26, 'color' => '#06B6D4'],
                ['rank' => 3, 'name' => 'Phú Quốc', 'percentage' => 20, 'color' => '#10B981'],
                ['rank' => 4, 'name' => 'Hội An', 'percentage' => 16, 'color' => '#F59E0B'],
            ];
        }

        return $result;
    }

    /**
     * Lấy danh sách giao dịch / đơn đặt phòng mới nhất từ CSDL thực tế.
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public function getRecentBookings(int $limit = 5): array
    {
        $bookings = Booking::with(['user', 'roomType.destination'])
            ->latest('id')
            ->take($limit)
            ->get();

        return $bookings->map(function ($booking) {
            $status = strtolower((string) $booking->status);
            $statusLabel = match ($status) {
                'completed' => 'Hoàn thành',
                'confirmed' => 'Đã xác nhận',
                'processing', 'upcoming' => 'Đang xử lý',
                'cancelled' => 'Đã hủy',
                default => 'Chờ duyệt',
            };

            $statusBadge = match ($status) {
                'completed', 'confirmed' => 'success',
                'processing', 'upcoming' => 'info',
                'cancelled' => 'danger',
                default => 'warning',
            };

            return [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code ?: ('#BK-' . $booking->id),
                'customer_name' => $booking->user?->name ?? 'Khách vãng lai',
                'destination_name' => $booking->roomType?->destination?->name ?? 'Điểm đến',
                'booking_date' => $booking->created_at ? $booking->created_at->format('d/m/Y') : date('d/m/Y'),
                'total_price' => (float) $booking->total_price,
                'formatted_price' => number_format((float) $booking->total_price, 0, ',', '.') . ' đ',
                'status' => $booking->status,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
            ];
        })->toArray();
    }

    /**
     * Xuất dữ liệu báo cáo thống kê Dashboard ra định dạng tệp CSV hỗ trợ Excel (UTF-8 BOM).
     *
     * @param int $year
     * @return string
     */
    public function exportReportData(int $year = 2026): string
    {
        $summary = $this->getSummaryStatistics();
        $monthlyStats = $this->getMonthlyStatistics($year);
        $bookingCategories = $this->getBookingCategories(4);
        $recentBookings = $this->getRecentBookings(10);

        $fileHandle = fopen('php://temp', 'r+');

        // Ghi UTF-8 BOM để Excel hiển thị đúng tiếng Việt có dấu không bị lỗi font
        fwrite($fileHandle, "\xEF\xBB\xBF");

        // Tiêu đề báo cáo
        fputcsv($fileHandle, ['BÁO CÁO THỐNG KÊ VẬN HÀNH HỆ THỐNG TRAVEL PLANNER']);
        fputcsv($fileHandle, ['Thời gian xuất báo cáo', date('d/m/Y H:i:s')]);
        fputcsv($fileHandle, ['Năm thống kê', $year]);
        fputcsv($fileHandle, []);

        // Phần I: Các chỉ số KPI tổng quan
        fputcsv($fileHandle, ['I. CHỈ SỐ THỐNG KÊ TỔNG QUAN (KPIs)']);
        fputcsv($fileHandle, ['Tên chỉ số', 'Giá trị', 'Tăng trưởng', 'Ghi chú']);
        fputcsv($fileHandle, ['Tổng số người dùng', $summary['total_users'] ?? '0', $summary['user_growth'] ?? '+0.0%', 'Toàn hệ thống']);
        fputcsv($fileHandle, ['Tổng số điểm đến', $summary['total_destinations'] ?? '0', $summary['destination_growth'] ?? '+0.0%', $summary['destination_note'] ?? 'Toàn hệ thống']);
        fputcsv($fileHandle, ['Tổng đặt phòng', $summary['total_bookings'] ?? '0', $summary['booking_growth'] ?? '+0.0%', 'Năm ' . $year]);
        fputcsv($fileHandle, ['Tổng doanh thu', $summary['total_revenue'] ?? '0 đ', $summary['revenue_growth'] ?? '+0.0%', 'Doanh thu ghi nhận']);
        fputcsv($fileHandle, []);

        // Phần II: Thống kê lượt đặt phòng theo 12 tháng
        fputcsv($fileHandle, ['II. THỐNG KÊ LƯỢT ĐẶT PHÒNG THEO THÁNG NĂM ' . $year]);
        fputcsv($fileHandle, ['Tháng', 'Tên tháng', 'Số lượt đặt phòng']);
        foreach ($monthlyStats['months'] as $monthItem) {
            fputcsv($fileHandle, [
                $monthItem['label'],
                $monthItem['month_name'] ?? ('Tháng ' . $monthItem['label']),
                $monthItem['value'],
            ]);
        }
        fputcsv($fileHandle, []);

        // Phần III: Tỷ lệ đặt phòng theo loại hình lưu trú
        fputcsv($fileHandle, ['III. TỶ LỆ ĐẶT PHÒNG THEO LOẠI HÌNH LƯU TRÚ']);
        fputcsv($fileHandle, ['Thứ hạng', 'Loại hình lưu trú', 'Tỷ lệ (%)']);
        foreach ($bookingCategories as $categoryItem) {
            fputcsv($fileHandle, [
                $categoryItem['rank'] ?? '-',
                $categoryItem['name'] ?? '',
                ($categoryItem['percentage'] ?? 0) . '%',
            ]);
        }
        fputcsv($fileHandle, []);

        // Phần IV: Danh sách giao dịch đặt phòng gần đây
        fputcsv($fileHandle, ['IV. DANH SÁCH GIAO DỊCH ĐẶT PHÒNG GẦN ĐÂY']);
        fputcsv($fileHandle, ['Mã Booking', 'Khách hàng', 'Điểm đến / Dịch vụ', 'Ngày đặt', 'Tổng tiền', 'Trạng thái']);
        foreach ($recentBookings as $bookingItem) {
            fputcsv($fileHandle, [
                $bookingItem['booking_code'] ?? '',
                $bookingItem['customer_name'] ?? '',
                $bookingItem['destination_name'] ?? '',
                $bookingItem['booking_date'] ?? '',
                $bookingItem['formatted_price'] ?? (number_format($bookingItem['total_price'] ?? 0, 0, ',', '.') . ' đ'),
                $bookingItem['status_label'] ?? ($bookingItem['status'] ?? ''),
            ]);
        }

        rewind($fileHandle);
        $csvContent = stream_get_contents($fileHandle);
        fclose($fileHandle);

        return $csvContent;
    }

    /**
     * Làm sạch dữ liệu ô CSV chống formula injection.
     */
    public function sanitizeCsvCell(mixed $value): mixed
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        $str = (string) $value;
        if ($str === '') {
            return '';
        }
        $dangerous = ['=', '+', '-', '@', "\t", "\r"];
        if (in_array($str[0], $dangerous, true)) {
            return "'" . $str;
        }
        return $value;
    }
}
