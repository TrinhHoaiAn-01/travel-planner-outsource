<?php

namespace App\Services;

use App\Models\Trip;
use App\Services\Interfaces\ItineraryServiceInterface;
use Carbon\Carbon;

/**
 * Service triển khai nghiệp vụ Quản lý lịch trình chuyến đi theo từng ngày.
 * Thành viên phụ trách: Phạm Thị Ngọc Ái (MSSV: 24211TT3767).
 * Tuân thủ quy chuẩn MVC 3 lớp và Service Interface theo Báo cáo Đồ án.
 */
class ItineraryService implements ItineraryServiceInterface
{
    /**
     * Lấy dữ liệu chi tiết lịch trình theo từng ngày của chuyến đi.
     *
     * @param Trip $trip Chuyến đi cần hiển thị lịch trình
     * @param int|null $dayNumber Ngày cụ thể người dùng chọn (mặc định ngày 1)
     * @return array<string, mixed>
     */
    public function getTripItinerary(Trip $trip, ?int $dayNumber = null): array
    {
        // 1. Tính tổng số ngày của chuyến đi (tối thiểu 1 ngày)
        $daysCount = 1;
        if ($trip->start_date && $trip->end_date) {
            $daysCount = max(1, $trip->start_date->diffInDays($trip->end_date) + 1);
        }

        // 2. Xác định ngày được chọn hiện tại (ràng buộc chặt chẽ trong khoảng 1 -> $daysCount)
        $currentDay = $dayNumber !== null ? (int) $dayNumber : 1;
        if ($currentDay < 1) {
            $currentDay = 1;
        } elseif ($currentDay > $daysCount) {
            $currentDay = $daysCount;
        }

        // 3. Xây dựng danh sách các ngày với ngày tháng cụ thể và thứ trong tuần
        $daysList = [];
        for ($day = 1; $day <= $daysCount; $day++) {
            $date = $trip->start_date ? $trip->start_date->copy()->addDays($day - 1) : null;
            $daysList[] = [
                'day_number' => $day,
                'date' => $date ? $date->format('d/m/Y') : null,
                'date_short' => $date ? $date->format('d/m') : null,
                'day_of_week' => $date ? $this->getVietnameseDayOfWeek($date) : "Ngày {$day}",
                'is_active' => $day === $currentDay,
                'items_count' => $trip->itineraryItems()->where('day_number', $day)->count(),
            ];
        }

        // 4. Lấy danh sách các hoạt động / địa điểm thuộc ngày đang chọn
        // Eager loading thông tin địa điểm, thành phố, danh mục và ảnh đại diện
        $items = $trip->itineraryItems()
            ->where('day_number', $currentDay)
            ->with([
                'destination.city',
                'destination.category',
                'destination.primaryImage',
            ])
            ->orderByRaw('COALESCE(start_time, "23:59:59") ASC')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 5. Thống kê tổng số hoạt động của toàn bộ chuyến đi
        $totalActivitiesCount = $trip->itineraryItems()->count();

        // 6. Tính ngày tháng thực tế của ngày đang chọn
        $currentDate = $trip->start_date ? $trip->start_date->copy()->addDays($currentDay - 1) : null;
        $currentDayOfWeek = $currentDate ? $this->getVietnameseDayOfWeek($currentDate) : "Ngày {$currentDay}";

        return [
            'trip' => $trip,
            'days_count' => $daysCount,
            'current_day' => $currentDay,
            'days_list' => $daysList,
            'items' => $items,
            'total_activities_count' => $totalActivitiesCount,
            'current_date' => $currentDate,
            'current_day_of_week' => $currentDayOfWeek,
        ];
    }

    /**
     * Chuyển đổi thứ trong tuần từ Carbon sang Tiếng Việt chuẩn.
     *
     * @param Carbon $date
     * @return string
     */
    private function getVietnameseDayOfWeek(Carbon $date): string
    {
        $days = [
            0 => 'Chủ Nhật',
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy',
        ];

        return $days[$date->dayOfWeek] ?? '';
    }
}
