<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\ItineraryItem;
use App\Models\Trip;
use App\Services\Interfaces\ItineraryServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Service triển khai nghiệp vụ Quản lý lịch trình chuyến đi theo từng ngày.
 * Thành viên phụ trách: Phạm Thị Ngọc Ái (MSSV: 24211TT3767).
 * Tuân thủ quy chuẩn MVC 3 lớp và Service Interface theo Báo cáo Đồ án.
 */
class ItineraryService implements ItineraryServiceInterface
{
    /**
     * Danh sách phân loại hoạt động theo chuẩn Hình 19 trong Báo cáo Đồ án.
     */
    public const ACTIVITY_TYPES = [
        'Tham quan / Check-in',
        'Ẩm thực / Ăn uống',
        'Nghỉ ngơi / Lưu trú',
        'Di chuyển',
        'Mua sắm / Giải trí',
        'Khác',
    ];

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
            ->orderByRaw('CASE WHEN start_time IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('start_time', 'asc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 5. Thống kê tổng số hoạt động của toàn bộ chuyến đi
        $totalActivitiesCount = $trip->itineraryItems()->count();

        // 6. Tính ngày tháng thực tế của ngày đang chọn
        $currentDate = $trip->start_date ? $trip->start_date->copy()->addDays($currentDay - 1) : null;
        $currentDayOfWeek = $currentDate ? $this->getVietnameseDayOfWeek($currentDate) : "Ngày {$currentDay}";

        // 7. Danh sách gợi ý địa điểm và loại hoạt động cho modal thêm nhanh
        $destinations = Destination::query()
            ->select('id', 'name', 'city_id', 'category_id', 'rating')
            ->with(['city:id,name', 'category:id,name'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('name', 'asc')
            ->limit(100)
            ->get();

        return [
            'trip' => $trip,
            'days_count' => $daysCount,
            'current_day' => $currentDay,
            'days_list' => $daysList,
            'items' => $items,
            'total_activities_count' => $totalActivitiesCount,
            'current_date' => $currentDate,
            'current_day_of_week' => $currentDayOfWeek,
            'destinations' => $destinations,
            'activity_types' => self::ACTIVITY_TYPES,
        ];
    }

    /**
     * Lấy dữ liệu khởi tạo cho màn hình / modal thêm hoạt động vào lịch trình.
     * Tuyến: GET /trips/{trip}/itinerary/create (Bảng 22 Báo cáo Đồ án).
     *
     * @param Trip $trip
     * @param int|null $dayNumber
     * @param int|null $destinationId
     * @return array<string, mixed>
     */
    public function getCreateActivityData(Trip $trip, ?int $dayNumber = null, ?int $destinationId = null): array
    {
        $baseData = $this->getTripItinerary($trip, $dayNumber);

        $selectedDestination = $destinationId ? Destination::with(['city', 'category', 'primaryImage'])->find($destinationId) : null;

        return array_merge($baseData, [
            'selected_destination' => $selectedDestination,
        ]);
    }

    /**
     * Thêm hoạt động mới vào lịch trình của chuyến đi.
     * Tuyến: POST /trips/{trip}/itinerary (Bảng 22 Báo cáo Đồ án - Chức năng 6).
     * Kiểm tra chặt chẽ quy tắc nghiệp vụ thời gian và xung đột khung giờ (FR19).
     *
     * @param Trip $trip
     * @param array $data
     * @return ItineraryItem
     * @throws ValidationException
     */
    public function storeActivity(Trip $trip, array $data): ItineraryItem
    {
        // 1. Tính tổng số ngày của chuyến đi
        $daysCount = 1;
        if ($trip->start_date && $trip->end_date) {
            $daysCount = max(1, $trip->start_date->diffInDays($trip->end_date) + 1);
        }

        // 2. Kiểm tra ngày nằm trong phạm vi chuyến đi
        $dayNumber = (int) ($data['day_number'] ?? 1);
        if ($dayNumber < 1 || $dayNumber > $daysCount) {
            throw ValidationException::withMessages([
                'day_number' => "Ngày được chọn phải nằm trong thời gian của chuyến đi (từ Ngày 1 đến Ngày {$daysCount}).",
            ]);
        }

        // 3. Chuẩn hóa khung giờ bắt đầu và kết thúc
        $startTime = !empty($data['start_time']) ? substr($data['start_time'], 0, 5) : null;
        $endTime = !empty($data['end_time']) ? substr($data['end_time'], 0, 5) : null;

        if ($startTime && $endTime) {
            // Giờ kết thúc phải lớn hơn giờ bắt đầu
            if ($endTime <= $startTime) {
                throw ValidationException::withMessages([
                    'end_time' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
                ]);
            }

            // Chặn nhập giờ chồng chéo / trùng lặp (Quy tắc FR19)
            $conflictItem = $trip->itineraryItems()
                ->where('day_number', $dayNumber)
                ->whereNotNull('start_time')
                ->whereNotNull('end_time')
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                })
                ->first();

            if ($conflictItem) {
                $conflictName = $conflictItem->custom_title ?: ($conflictItem->destination ? $conflictItem->destination->name : ($conflictItem->note ?: 'Hoạt động khác'));
                $conflictTime = substr($conflictItem->start_time, 0, 5) . ' - ' . substr($conflictItem->end_time, 0, 5);
                throw ValidationException::withMessages([
                    'start_time' => "Khung giờ này đã bị trùng lặp với '{$conflictName}' ({$conflictTime}) trong Ngày {$dayNumber}. Vui lòng chọn khung giờ khác.",
                ]);
            }
        }

        // 4. Xác định địa điểm liên kết (nếu có)
        $activityName = trim($data['activity_name'] ?? '');
        $destinationId = !empty($data['destination_id']) ? (int) $data['destination_id'] : null;

        if (!$destinationId && !empty($activityName)) {
            $matchingDest = Destination::where('name', $activityName)->first();
            if ($matchingDest) {
                $destinationId = $matchingDest->id;
            }
        }

        // 5. Tính toán số thứ tự sắp xếp tiếp theo cho ngày được chọn
        $maxSortOrder = $trip->itineraryItems()
            ->where('day_number', $dayNumber)
            ->max('sort_order') ?? 0;
        $sortOrder = $maxSortOrder + 1;

        // 6. Xử lý ghi chú và phân loại hoạt động
        $note = !empty($data['note']) ? trim($data['note']) : null;
        $activityType = !empty($data['activity_type']) ? trim($data['activity_type']) : null;

        // 7. Tạo bản ghi mục lịch trình
        $item = $trip->itineraryItems()->create([
            'destination_id' => $destinationId,
            'custom_title' => $activityName,
            'day_number' => $dayNumber,
            'start_time' => $startTime ? (strlen($startTime) === 5 ? $startTime . ':00' : $startTime) : null,
            'end_time' => $endTime ? (strlen($endTime) === 5 ? $endTime . ':00' : $endTime) : null,
            'note' => $note,
            'sort_order' => $sortOrder,
        ]);

        // 8. Tự động đồng bộ chi phí dự kiến vào ngân sách chuyến đi (Bảng 10, FR23)
        $estimatedCost = !empty($data['estimated_cost']) ? (float) $data['estimated_cost'] : 0;
        if ($estimatedCost > 0) {
            $expenseDate = $trip->start_date
                ? $trip->start_date->copy()->addDays($dayNumber - 1)
                : now();

            $trip->expenses()->create([
                'category' => $activityType ?: 'Tham quan / Check-in',
                'description' => 'Chi phí hoạt động: ' . $activityName,
                'amount' => $estimatedCost,
                'expense_date' => $expenseDate,
            ]);
        }

        // Tự động sắp xếp lại thứ tự theo thời gian
        $this->resequenceDayItems($trip, $dayNumber);

        return $item;
    }

    /**
     * Lấy dữ liệu khởi tạo cho màn hình / modal chỉnh sửa hoạt động trong lịch trình.
     * Tuyến: GET /itinerary/{itinerary}/edit (Bảng 22 Báo cáo Đồ án - Chức năng 7).
     *
     * @param ItineraryItem $itinerary
     * @return array<string, mixed>
     */
    public function getEditActivityData(ItineraryItem $itinerary): array
    {
        $itinerary->load(['trip', 'destination.city', 'destination.category', 'destination.primaryImage']);
        $baseData = $this->getTripItinerary($itinerary->trip, $itinerary->day_number);

        return array_merge($baseData, [
            'item' => $itinerary,
        ]);
    }

    /**
     * Cập nhật thông tin hoạt động trong lịch trình chuyến đi.
     * Tuyến: PATCH /itinerary/{itinerary} (Bảng 22 Báo cáo Đồ án - Chức năng 7 & 8).
     * Kiểm tra xung đột thời gian, loại trừ chính mục đang sửa (FR19, FR20).
     *
     * @param ItineraryItem $itinerary
     * @param array $data
     * @return ItineraryItem
     * @throws ValidationException
     */
    public function updateActivity(ItineraryItem $itinerary, array $data): ItineraryItem
    {
        $trip = $itinerary->trip;
        $oldDay = (int) $itinerary->day_number;

        // 1. Tính tổng số ngày của chuyến đi
        $daysCount = 1;
        if ($trip->start_date && $trip->end_date) {
            $daysCount = max(1, $trip->start_date->diffInDays($trip->end_date) + 1);
        }

        // 2. Kiểm tra ngày nằm trong phạm vi chuyến đi (Chức năng 8)
        $dayNumber = isset($data['day_number']) ? (int) $data['day_number'] : (int) $itinerary->day_number;
        if ($dayNumber < 1 || $dayNumber > $daysCount) {
            throw ValidationException::withMessages([
                'day_number' => "Ngày được chọn phải nằm trong thời gian của chuyến đi (từ Ngày 1 đến Ngày {$daysCount}).",
            ]);
        }

        // 3. Chuẩn hóa khung giờ bắt đầu và kết thúc (Chức năng 8)
        $startTime = !empty($data['start_time']) ? substr($data['start_time'], 0, 5) : ($itinerary->start_time ? substr($itinerary->start_time, 0, 5) : null);
        $endTime = !empty($data['end_time']) ? substr($data['end_time'], 0, 5) : ($itinerary->end_time ? substr($itinerary->end_time, 0, 5) : null);

        if ($startTime && $endTime) {
            // Giờ kết thúc phải sau giờ bắt đầu
            if ($endTime <= $startTime) {
                throw ValidationException::withMessages([
                    'end_time' => 'Giờ kết thúc phải sau giờ bắt đầu.',
                ]);
            }

            // Chặn nhập giờ trùng lặp / chồng chéo, loại trừ chính mục đang sửa
            $conflictItem = $trip->itineraryItems()
                ->where('id', '!=', $itinerary->id)
                ->where('day_number', $dayNumber)
                ->whereNotNull('start_time')
                ->whereNotNull('end_time')
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                })
                ->first();

            if ($conflictItem) {
                $conflictName = $conflictItem->custom_title ?: ($conflictItem->destination ? $conflictItem->destination->name : ($conflictItem->note ?: 'Hoạt động khác'));
                $conflictTime = substr($conflictItem->start_time, 0, 5) . ' - ' . substr($conflictItem->end_time, 0, 5);
                throw ValidationException::withMessages([
                    'start_time' => "Khung giờ này đã bị trùng lặp với '{$conflictName}' ({$conflictTime}) trong Ngày {$dayNumber}. Vui lòng chọn khung giờ khác.",
                ]);
            }
        }

        // 4. Xác định địa điểm liên kết nếu thay đổi tên
        $activityName = isset($data['activity_name']) ? trim($data['activity_name']) : null;
        $destinationId = isset($data['destination_id']) ? (!empty($data['destination_id']) ? (int) $data['destination_id'] : null) : $itinerary->destination_id;

        if (!empty($activityName) && !$destinationId) {
            $matchingDest = Destination::where('name', $activityName)->first();
            if ($matchingDest) {
                $destinationId = $matchingDest->id;
            }
        }

        // 5. Cập nhật dữ liệu mục lịch trình
        $itinerary->update([
            'custom_title' => $activityName ?? $itinerary->custom_title,
            'destination_id' => $destinationId,
            'day_number' => $dayNumber,
            'start_time' => $startTime ? $startTime . ':00' : null,
            'end_time' => $endTime ? $endTime . ':00' : null,
            'note' => array_key_exists('note', $data) ? $data['note'] : $itinerary->note,
        ]);

        // 6. Tự động chuẩn hóa lại thứ tự sắp xếp theo thời gian (Chức năng 8, FR21)
        $this->resequenceDayItems($trip, $dayNumber);
        if ($oldDay !== $dayNumber) {
            $this->resequenceDayItems($trip, $oldDay);
        }

        return $itinerary;
    }

    /**
     * Xóa mục hoạt động khỏi lịch trình của chuyến đi.
     * Tuyến: DELETE /itinerary/{itinerary} (Bảng 22 Báo cáo Đồ án - Chức năng 7).
     *
     * @param ItineraryItem $itinerary
     * @return bool
     */
    public function deleteActivity(ItineraryItem $itinerary): bool
    {
        $trip = $itinerary->trip;
        $dayNumber = (int) $itinerary->day_number;
        $deleted = (bool) $itinerary->delete();

        if ($trip) {
            $this->resequenceDayItems($trip, $dayNumber);
        }

        return $deleted;
    }

    /**
     * Thay đổi thứ tự hiển thị các hoạt động trong một ngày của chuyến đi (FR21).
     * Tuyến: PATCH /trips/{trip}/itinerary/reorder (Bảng 22 Báo cáo Đồ án - Chức năng 8).
     *
     * @param Trip $trip
     * @param int $dayNumber
     * @param array<int> $itemIds Danh sách ID hoạt động theo thứ tự mới
     * @return bool
     * @throws ValidationException
     */
    public function reorderActivities(Trip $trip, int $dayNumber, array $itemIds): bool
    {
        $existingItems = $trip->itineraryItems()
            ->where('day_number', $dayNumber)
            ->pluck('id')
            ->toArray();

        if (count($existingItems) !== count($itemIds)) {
            throw ValidationException::withMessages([
                'item_ids' => 'Danh sách sắp xếp không khớp với số lượng hoạt động trong ngày.',
            ]);
        }

        if (count($itemIds) !== count(array_unique($itemIds))) {
            throw ValidationException::withMessages([
                'item_ids' => 'Danh sách hoạt động chứa ID trùng lặp.',
            ]);
        }

        sort($existingItems);
        $checkIds = $itemIds;
        sort($checkIds);
        if ($existingItems !== $checkIds) {
            throw ValidationException::withMessages([
                'item_ids' => 'Danh sách chứa mục không thuộc về ngày hoặc chuyến đi này.',
            ]);
        }

        DB::transaction(function () use ($itemIds) {
            foreach ($itemIds as $index => $id) {
                ItineraryItem::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });

        return true;
    }

    /**
     * Cập nhật ghi chú riêng biệt cho một mục hoạt động trong lịch trình (Chức năng 7).
     * Tuyến: PATCH /itinerary/{itinerary}/note (Bảng 22 Báo cáo Đồ án).
     *
     * @param ItineraryItem $itinerary
     * @param string|null $note
     * @return ItineraryItem
     * @throws ValidationException
     */
    public function updateActivityNote(ItineraryItem $itinerary, ?string $note): ItineraryItem
    {
        $cleanNote = $note !== null ? trim($note) : null;
        if ($cleanNote !== null && mb_strlen($cleanNote) > 1000) {
            throw ValidationException::withMessages([
                'note' => 'Ghi chú vượt quá số ký tự cho phép (tối đa 1000 ký tự).',
            ]);
        }

        $itinerary->update([
            'note' => !empty($cleanNote) ? $cleanNote : null,
        ]);

        return $itinerary;
    }

    /**
     * Tự động chuẩn hóa thứ tự hoạt động (sort_order) liên tục theo thời gian từ sớm đến muộn.
     *
     * @param Trip $trip
     * @param int $dayNumber
     * @return void
     */
    private function resequenceDayItems(Trip $trip, int $dayNumber): void
    {
        $items = $trip->itineraryItems()
            ->where('day_number', $dayNumber)
            ->reorder()
            ->orderByRaw('CASE WHEN start_time IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('start_time', 'asc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($items as $index => $item) {
            $newOrder = $index + 1;
            if ($item->sort_order !== $newOrder) {
                $item->update(['sort_order' => $newOrder]);
            }
        }
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
