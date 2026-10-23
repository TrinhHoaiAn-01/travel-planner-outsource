<?php

namespace App\Services;

use App\Models\Trip;
use App\Services\Interfaces\TripServiceInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TripService implements TripServiceInterface
{
    /**
     * Lấy danh sách chuyến đi của người dùng kèm bộ lọc và tìm kiếm.
     */
    public function getUserTrips(int $userId, array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        $query = Trip::query()
            ->where('user_id', $userId)
            ->with(['expenses', 'itineraryItems.destination']);

        // Tìm kiếm theo từ khóa (tên hoặc mô tả chuyến đi)
        if (! empty($filters['search'])) {
            $searchKeyword = trim((string) $filters['search']);
            $query->where(function ($subQuery) use ($searchKeyword) {
                $subQuery->where('name', 'like', "%{$searchKeyword}%")
                    ->orWhere('description', 'like', "%{$searchKeyword}%");
            });
        }

        // Lọc theo trạng thái chuyến đi
        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $status = (string) $filters['status'];

            if ($status === 'planned' || $status === 'upcoming') {
                $query->where('status', Trip::STATUS_PLANNED);
            } elseif ($status === 'ongoing') {
                $query->where(function ($subQuery) {
                    $subQuery->where('status', 'ongoing')
                        ->orWhere(function ($dateQuery) {
                            $dateQuery->where('status', '!=', Trip::STATUS_COMPLETED)
                                ->whereDate('start_date', '<=', now())
                                ->whereDate('end_date', '>=', now());
                        });
                });
            } elseif ($status === 'completed') {
                $query->where('status', Trip::STATUS_COMPLETED);
            } elseif ($status === 'draft') {
                $query->where('status', Trip::STATUS_DRAFT);
            }
        }

        // Lọc theo khoảng ngày bắt đầu
        if (! empty($filters['start_date'])) {
            $query->whereDate('start_date', '>=', $filters['start_date']);
        }

        // Lọc theo khoảng ngày kết thúc
        if (! empty($filters['end_date'])) {
            $query->whereDate('end_date', '<=', $filters['end_date']);
        }

        // Sắp xếp theo ngày bắt đầu giảm dần, sau đó theo ID giảm dần
        return $query->orderBy('start_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Đếm số lượng chuyến đi theo từng trạng thái để phục vụ các tab điều hướng.
     */
    public function getTripCountsByStatus(int $userId): array
    {
        $baseQuery = Trip::query()->where('user_id', $userId);

        $totalCount = (clone $baseQuery)->count();

        $plannedCount = (clone $baseQuery)
            ->where('status', Trip::STATUS_PLANNED)
            ->count();

        $ongoingCount = (clone $baseQuery)
            ->where(function ($query) {
                $query->where('status', 'ongoing')
                    ->orWhere(function ($dateQuery) {
                        $dateQuery->where('status', '!=', Trip::STATUS_COMPLETED)
                            ->whereDate('start_date', '<=', now())
                            ->whereDate('end_date', '>=', now());
                    });
            })
            ->count();

        $completedCount = (clone $baseQuery)
            ->where('status', Trip::STATUS_COMPLETED)
            ->count();

        $draftCount = (clone $baseQuery)
            ->where('status', Trip::STATUS_DRAFT)
            ->count();

        return [
            'all' => $totalCount,
            'planned' => $plannedCount,
            'ongoing' => $ongoingCount,
            'completed' => $completedCount,
            'draft' => $draftCount,
        ];
    }

    /**
     * Tạo mới một chuyến đi cho người dùng.
     */
    public function createTrip(array $data): Trip
    {
        if (isset($data['start_date']) && isset($data['end_date'])) {
            if (! $this->validateTripDates($data['start_date'], $data['end_date'])) {
                throw new InvalidArgumentException('Ngày bắt đầu không được sau ngày kết thúc.');
            }
        }

        // Chuẩn hóa và lọc các thuộc tính tương thích với bảng trips
        $description = $data['description'] ?? null;
        if (! empty($data['destination_area']) && empty($description)) {
            $description = 'Địa bàn: ' . $data['destination_area'];
        }

        $tripAttributes = [
            'user_id' => $data['user_id'] ?? null,
            'name' => $data['name'],
            'description' => $description,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'budget' => $data['budget'] ?? 0,
            'status' => $data['status'] ?? Trip::STATUS_PLANNED,
        ];

        return Trip::create($tripAttributes);
    }

    /**
     * Lấy thông tin chi tiết của một chuyến đi.
     */
    public function getTripDetails(int $tripId): Trip
    {
        return Trip::query()
            ->with(['user', 'expenses', 'itineraryItems.destination'])
            ->findOrFail($tripId);
    }

    /**
     * Cập nhật thông tin chuyến đi.
     */
    public function updateTrip(int $tripId, array $data): Trip
    {
        return DB::transaction(function () use ($tripId, $data) {
            $trip = Trip::findOrFail($tripId);

            // Kiểm tra phân tích toàn diện ảnh hưởng đến Booking, Chi phí và Lịch trình trước khi cập nhật
            $changeImpact = $this->checkTripChangeImpact($tripId, $data);

            $dateChangeResult = null;
            if (isset($data['start_date']) && isset($data['end_date'])) {
                if (! $this->validateTripDates($data['start_date'], $data['end_date'])) {
                    throw new InvalidArgumentException('Ngày bắt đầu không được sau ngày kết thúc.');
                }

                // Xử lý thay đổi ngày và tự động bảo toàn các hoạt động lịch trình khi số ngày thay đổi
                $dateChangeResult = $this->handleTripDateChange($tripId, $data['start_date'], $data['end_date']);
                $trip->refresh();
            }

            // Chuẩn hóa mô tả và địa bàn trọng tâm
            $description = $data['description'] ?? $trip->description;
            if (! empty($data['destination_area']) && empty($data['description'])) {
                $description = 'Địa bàn: ' . $data['destination_area'];
            }

            $updateAttributes = [
                'name' => $data['name'] ?? $trip->name,
                'description' => $description,
                'budget' => $data['budget'] ?? $trip->budget,
            ];

            if (isset($data['status'])) {
                $updateAttributes['status'] = $data['status'];
            }

            $trip->update($updateAttributes);

            $freshTrip = $trip->fresh();
            if ($dateChangeResult !== null) {
                $freshTrip->dateChangeResult = $dateChangeResult;
            }
            $freshTrip->changeImpact = $changeImpact;

            return $freshTrip;
        });
    }

    /**
     * Xóa chuyến đi khỏi hệ thống.
     */
    public function deleteTrip(int $tripId): bool
    {
        return DB::transaction(function () use ($tripId) {
            $trip = Trip::findOrFail($tripId);

            return (bool) $trip->delete();
        });
    }

    /**
     * Cập nhật trạng thái của chuyến đi (draft, planned, ongoing, completed, cancelled).
     * Bọc DB::transaction để đảm bảo tính toàn vẹn dữ liệu.
     */
    public function updateTripStatus(int $tripId, string $status): Trip
    {
        return DB::transaction(function () use ($tripId, $status) {
            // Danh sách các trạng thái chuyến đi được phép
            $validStatuses = [
                Trip::STATUS_DRAFT,
                Trip::STATUS_PLANNED,
                Trip::STATUS_ONGOING,
                Trip::STATUS_COMPLETED,
                Trip::STATUS_CANCELLED,
            ];

            // Kiểm tra trạng thái có nằm trong danh sách cho phép không
            if (! in_array($status, $validStatuses, true)) {
                throw new InvalidArgumentException('Trạng thái chuyến đi không hợp lệ.');
            }

            $trip = Trip::findOrFail($tripId);
            $trip->status = $status;
            $trip->save();

            return $trip->fresh();
        });
    }

    /**
     * Xác thực khoảng thời gian ngày bắt đầu và kết thúc của chuyến đi.
     */
    public function validateTripDates(?string $startDate, ?string $endDate): bool
    {
        if ($startDate === null || $endDate === null) {
            return false;
        }

        return strtotime($startDate) <= strtotime($endDate);
    }

    /**
     * Lấy dữ liệu tóm tắt của chuyến đi.
     */
    public function getTripSummary(int $tripId): array
    {
        $trip = Trip::query()->with(['expenses', 'itineraryItems'])->findOrFail($tripId);

        $totalExpenses = $trip->expenses->sum('amount');
        $remainingBudget = max(0, (float) $trip->budget - (float) $totalExpenses);

        $daysCount = 0;
        if ($trip->start_date && $trip->end_date) {
            $daysCount = $trip->start_date->diffInDays($trip->end_date) + 1;
        }

        $destinationsCount = $trip->itineraryItems
            ->pluck('destination_id')
            ->filter()
            ->unique()
            ->count();

        return [
            'days_count' => $daysCount,
            'destinations_count' => $destinationsCount,
            'budget' => (float) $trip->budget,
            'total_expenses' => (float) $totalExpenses,
            'remaining_budget' => (float) $remainingBudget,
            'is_over_budget' => $totalExpenses > $trip->budget,
        ];
    }

    /**
     * Nhân bản chuyến đi đã có thành một chuyến đi mới.
     */
    public function cloneTrip(int $tripId): Trip
    {
        return DB::transaction(function () use ($tripId) {
            $originalTrip = Trip::query()->with(['itineraryItems'])->findOrFail($tripId);

            $clonedTrip = $originalTrip->replicate(['created_at', 'updated_at']);
            $clonedTrip->name = 'Bản sao - ' . $originalTrip->name;
            $clonedTrip->status = Trip::STATUS_DRAFT;
            $clonedTrip->save();

            foreach ($originalTrip->itineraryItems as $item) {
                $clonedItem = $item->replicate(['created_at', 'updated_at', 'trip_id']);
                $clonedItem->trip_id = $clonedTrip->id;
                $clonedItem->save();
            }

            return $clonedTrip;
        });
    }

    /**
     * Mở lại chuyến đi đã hoàn thành về trạng thái lập kế hoạch.
     */
    public function reopenTrip(int $tripId): Trip
    {
        $trip = Trip::findOrFail($tripId);
        $trip->status = Trip::STATUS_PLANNED;
        $trip->save();

        return $trip;
    }

    /**
     * Lấy thông tin chi tiết các hoạt động lịch trình phụ thuộc bị ảnh hưởng khi thay đổi hoặc rút ngắn ngày chuyến đi.
     */
    public function getAffectedItineraryItems(int $tripId, ?string $newStartDate, ?string $newEndDate): array
    {
        $trip = Trip::with(['itineraryItems.destination'])->findOrFail($tripId);
        $oldTotalDays = $trip->total_days;

        if (empty($newStartDate) || empty($newEndDate) || ! $this->validateTripDates($newStartDate, $newEndDate)) {
            return [
                'is_shortened' => false,
                'old_total_days' => $oldTotalDays,
                'new_total_days' => 0,
                'target_day' => 0,
                'affected_count' => 0,
                'affected_items' => [],
            ];
        }

        $newTotalDays = (int) \Carbon\Carbon::parse($newStartDate)->diffInDays(\Carbon\Carbon::parse($newEndDate)) + 1;
        $isShortened = ($oldTotalDays > 0) && ($newTotalDays < $oldTotalDays);

        $affectedItems = [];
        if ($isShortened && $trip->itineraryItems->isNotEmpty()) {
            $filtered = $trip->itineraryItems
                ->filter(fn ($item) => $item->day_number > $newTotalDays)
                ->sortBy(fn ($item) => $item->day_number * 1000 + $item->sort_order);

            foreach ($filtered as $item) {
                $affectedItems[] = [
                    'id' => $item->id,
                    'original_day_number' => $item->day_number,
                    'target_day_number' => $newTotalDays,
                    'destination_id' => $item->destination_id,
                    'destination_name' => $item->destination ? $item->destination->name : 'Điểm tham quan #' . $item->destination_id,
                    'start_time' => $item->start_time ? substr($item->start_time, 0, 5) : null,
                    'end_time' => $item->end_time ? substr($item->end_time, 0, 5) : null,
                    'note' => $item->note,
                    'sort_order' => $item->sort_order,
                ];
            }
        }

        return [
            'is_shortened' => $isShortened,
            'old_total_days' => $oldTotalDays,
            'new_total_days' => $newTotalDays,
            'target_day' => $newTotalDays,
            'affected_count' => count($affectedItems),
            'affected_items' => $affectedItems,
        ];
    }

    /**
     * Xử lý khi ngày của chuyến đi bị thay đổi hoặc rút ngắn khi có lịch trình phụ thuộc.
     * Tự động bảo toàn dữ liệu bằng cách dời an toàn các hoạt động vượt quá số ngày về ngày cuối cùng mới và tái cấu trúc thứ tự hiển thị.
     */
    public function handleTripDateChange(int $tripId, ?string $newStartDate, ?string $newEndDate): array
    {
        return DB::transaction(function () use ($tripId, $newStartDate, $newEndDate) {
            $trip = Trip::with(['itineraryItems.destination'])->findOrFail($tripId);

            if ($newStartDate !== null && $newEndDate !== null) {
                if (! $this->validateTripDates($newStartDate, $newEndDate)) {
                    throw new InvalidArgumentException('Ngày bắt đầu không được sau ngày kết thúc.');
                }
            }

            $affectedInfo = $this->getAffectedItineraryItems($tripId, $newStartDate, $newEndDate);
            $reassignedCount = 0;
            $reassignedItems = [];

            if ($affectedInfo['is_shortened'] && $affectedInfo['affected_count'] > 0) {
                $targetDay = $affectedInfo['target_day'];

                // Tìm thứ tự sắp xếp lớn nhất hiện có ở ngày đích để xếp các hoạt động chuyển về tiếp nối phía sau
                $currentMaxSortOrder = (int) ($trip->itineraryItems
                    ->where('day_number', $targetDay)
                    ->max('sort_order') ?? 0);

                // Lấy các mục cần di dời, sắp xếp theo ngày cũ và thứ tự cũ để bảo toàn logic thời gian
                $itemsToReassign = $trip->itineraryItems
                    ->filter(fn ($item) => $item->day_number > $targetDay)
                    ->sortBy(fn ($item) => $item->day_number * 1000 + $item->sort_order);

                foreach ($itemsToReassign as $item) {
                    $item->day_number = $targetDay;
                    $item->sort_order = ++$currentMaxSortOrder;
                    $item->save();

                    $reassignedCount++;
                    $reassignedItems[] = [
                        'id' => $item->id,
                        'destination_name' => $item->destination ? $item->destination->name : 'Điểm tham quan #' . $item->destination_id,
                        'day_number' => $targetDay,
                        'sort_order' => $item->sort_order,
                    ];
                }
            }

            // Cập nhật khoảng ngày mới cho chuyến đi
            $trip->start_date = $newStartDate;
            $trip->end_date = $newEndDate;
            $trip->save();

            $message = 'Thời gian chuyến đi đã được cập nhật thành công.';
            if ($reassignedCount > 0) {
                $message = "Chuyến đi đã được rút ngắn từ {$affectedInfo['old_total_days']} ngày xuống {$affectedInfo['new_total_days']} ngày. Đã tự động bảo toàn {$reassignedCount} hoạt động lịch trình sang Ngày {$affectedInfo['target_day']}.";
            }

            return [
                'date_changed' => true,
                'is_shortened' => $affectedInfo['is_shortened'],
                'old_total_days' => $affectedInfo['old_total_days'],
                'new_total_days' => $affectedInfo['new_total_days'],
                'reassigned_count' => $reassignedCount,
                'reassigned_items' => $reassignedItems,
                'target_day' => $affectedInfo['target_day'],
                'message' => $message,
            ];
        });
    }

    /**
     * Tính toán thời lượng chuyến đi (số ngày, số đêm, nhãn hiển thị và tính hợp lệ) từ ngày bắt đầu và kết thúc.
     */
    public function calculateTripDuration(?string $startDate, ?string $endDate): array
    {
        if (empty($startDate) || empty($endDate)) {
            return [
                'days' => 0,
                'nights' => 0,
                'label' => 'Chưa xác định thời lượng',
                'is_valid' => false,
            ];
        }
        if (! $this->validateTripDates($startDate, $endDate)) {
            return [
                'days' => 0,
                'nights' => 0,
                'label' => 'Ngày kết thúc không được trước ngày bắt đầu',
                'is_valid' => false,
            ];
        }

        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $days = (int) $start->diffInDays($end) + 1;
        $nights = max(0, $days - 1);

        $label = $nights > 0 ? "{$days} ngày {$nights} đêm" : "{$days} ngày";

        return [
            'days' => $days,
            'nights' => $nights,
            'label' => $label,
            'is_valid' => true,
        ];
    }

    /**
     * Lấy danh sách chi tiết các ngày của chuyến đi (từ Ngày 1 đến Ngày N kèm ngày trong tuần tiếng Việt).
     */
    public function getTripDaysList(int $tripId): array
    {
        $trip = Trip::findOrFail($tripId);

        if (! $trip->start_date || ! $trip->end_date) {
            return [];
        }

        $days = [];
        $totalDays = (int) $trip->start_date->diffInDays($trip->end_date) + 1;

        $vietnameseDays = [
            'Monday' => 'Thứ Hai',
            'Tuesday' => 'Thứ Ba',
            'Wednesday' => 'Thứ Tư',
            'Thursday' => 'Thứ Năm',
            'Friday' => 'Thứ Sáu',
            'Saturday' => 'Thứ Bảy',
            'Sunday' => 'Chủ Nhật',
        ];

        for ($i = 1; $i <= $totalDays; $i++) {
            $currentDate = (clone $trip->start_date)->addDays($i - 1);
            $dayNameEnglish = $currentDate->format('l');
            $dayNameVietnamese = $vietnameseDays[$dayNameEnglish] ?? $dayNameEnglish;

            $days[] = [
                'day_number' => $i,
                'date' => $currentDate->format('Y-m-d'),
                'formatted_date' => $currentDate->format('d/m/Y'),
                'short_date' => $currentDate->format('d/m'),
                'full_date' => $currentDate->format('d/m/Y'),
                'day_name' => $dayNameVietnamese,
                'label' => "Ngày {$i} ({$currentDate->format('d/m')} - {$dayNameVietnamese})",
            ];
        }

        return $days;
    }

    /**
     * Cập nhật mô tả và ghi chú tổng quát của chuyến đi.
     */
    public function updateTripNotes(int $tripId, ?string $notes): Trip
    {
        return DB::transaction(function () use ($tripId, $notes) {
            $trip = Trip::findOrFail($tripId);
            $trip->description = $notes;
            $trip->save();

            return $trip->fresh();
        });
    }

    /**
     * Lấy thông tin chi tiết các đơn đặt phòng (Booking) liên quan bị ảnh hưởng khi thay đổi ngày hoặc trạng thái chuyến đi.
     * Kiểm tra các đơn đặt phòng có check_in trước start_date mới, hoặc check_out sau end_date mới, hoặc khi hủy chuyến đi.
     */
    public function getAffectedBookings(int $tripId, ?string $newStartDate, ?string $newEndDate, ?string $newStatus = null): array
    {
        $trip = Trip::with(['bookings.roomType.destination'])->findOrFail($tripId);
        $allBookings = $trip->bookings;
        $totalCount = $allBookings->count();
        $totalBookingPrice = (float) $allBookings->sum('total_price');

        $affectedItems = [];
        $hasDates = !empty($newStartDate) && !empty($newEndDate) && $this->validateTripDates($newStartDate, $newEndDate);
        $newStart = $hasDates ? Carbon::parse($newStartDate)->toDateString() : null;
        $newEnd = $hasDates ? Carbon::parse($newEndDate)->toDateString() : null;

        foreach ($allBookings as $booking) {
            $issueType = null;
            $reason = '';

            // Bỏ qua các đơn đặt phòng đã bị hủy từ trước vì không còn ảnh hưởng nghiệp vụ
            if ($booking->status === 'cancelled') {
                continue;
            }

            // 1. Kiểm tra ảnh hưởng khi thay đổi ngày chuyến đi
            if ($hasDates && $booking->check_in_date && $booking->check_out_date) {
                $checkIn = $booking->check_in_date->toDateString();
                $checkOut = $booking->check_out_date->toDateString();

                if ($checkIn < $newStart && $checkOut > $newEnd) {
                    $issueType = 'both_outside';
                    $reason = "Khoảng thời gian lưu trú ({$booking->check_in_date->format('d/m/Y')} - {$booking->check_out_date->format('d/m/Y')}) vượt ra ngoài toàn bộ thời gian mới của chuyến đi (" . Carbon::parse($newStartDate)->format('d/m/Y') . ' - ' . Carbon::parse($newEndDate)->format('d/m/Y') . ').';
                } elseif ($checkIn < $newStart) {
                    $issueType = 'before_start';
                    $reason = "Ngày nhận phòng ({$booking->check_in_date->format('d/m/Y')}) diễn ra trước ngày bắt đầu mới của chuyến đi (" . Carbon::parse($newStartDate)->format('d/m/Y') . ').';
                } elseif ($checkOut > $newEnd) {
                    $issueType = 'after_end';
                    $reason = "Ngày trả phòng ({$booking->check_out_date->format('d/m/Y')}) diễn ra sau ngày kết thúc mới của chuyến đi (" . Carbon::parse($newEndDate)->format('d/m/Y') . ').';
                }
            }

            // 2. Kiểm tra ảnh hưởng khi trạng thái chuyến đi chuyển sang Đã hủy
            if ($newStatus === Trip::STATUS_CANCELLED && $issueType === null) {
                $issueType = 'trip_cancelled';
                $reason = 'Chuyến đi chuyển sang trạng thái Đã hủy nhưng đơn đặt phòng vẫn đang hoạt động (cần liên hệ hủy phòng hoặc bảo lưu).';
            }

            if ($issueType !== null) {
                $affectedItems[] = [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code ?? '#BK-' . $booking->id,
                    'room_type_id' => $booking->room_type_id,
                    'room_type_name' => $booking->roomType ? $booking->roomType->name : 'Phòng #' . $booking->room_type_id,
                    'destination_name' => $booking->roomType && $booking->roomType->destination ? $booking->roomType->destination->name : 'Điểm lưu trú',
                    'check_in_date' => $booking->check_in_date ? $booking->check_in_date->format('Y-m-d') : null,
                    'check_in_formatted' => $booking->check_in_date ? $booking->check_in_date->format('d/m/Y') : '',
                    'check_out_date' => $booking->check_out_date ? $booking->check_out_date->format('Y-m-d') : null,
                    'check_out_formatted' => $booking->check_out_date ? $booking->check_out_date->format('d/m/Y') : '',
                    'room_count' => (int) $booking->room_count,
                    'total_price' => (float) $booking->total_price,
                    'status' => $booking->status,
                    'status_label' => match ($booking->status) {
                        'pending' => 'Chờ xử lý',
                        'confirmed' => 'Đã xác nhận',
                        'cancelled' => 'Đã hủy',
                        default => $booking->status,
                    },
                    'issue_type' => $issueType,
                    'reason' => $reason,
                ];
            }
        }

        $affectedBookingPrice = (float) array_sum(array_column($affectedItems, 'total_price'));

        return [
            'has_affected' => count($affectedItems) > 0,
            'total_count' => $totalCount,
            'affected_count' => count($affectedItems),
            'total_booking_price' => $totalBookingPrice,
            'affected_booking_price' => $affectedBookingPrice,
            'items' => $affectedItems,
        ];
    }

    /**
     * Lấy thông tin chi tiết các khoản chi phí và ngân sách bị ảnh hưởng khi thay đổi hạn mức ngân sách hoặc ngày chuyến đi.
     */
    public function getAffectedExpenses(int $tripId, ?string $newStartDate, ?string $newEndDate, ?float $newBudget = null): array
    {
        $trip = Trip::with(['expenses'])->findOrFail($tripId);
        $allExpenses = $trip->expenses;
        $totalExpensesAmount = (float) $allExpenses->sum('amount');
        $totalExpensesCount = $allExpenses->count();
        $oldBudget = (float) $trip->budget;
        $budgetToCheck = $newBudget !== null ? (float) $newBudget : $oldBudget;

        // So sánh tổng chi tiêu thực tế với ngân sách mới
        $isOverBudget = false;
        $overBudgetAmount = 0.0;
        $remainingBudget = 0.0;
        $budgetUsagePercent = 0.0;

        if ($budgetToCheck > 0) {
            $budgetUsagePercent = round(($totalExpensesAmount / $budgetToCheck) * 100, 1);
            if ($totalExpensesAmount > $budgetToCheck) {
                $isOverBudget = true;
                $overBudgetAmount = $totalExpensesAmount - $budgetToCheck;
            } else {
                $remainingBudget = $budgetToCheck - $totalExpensesAmount;
            }
        } elseif ($totalExpensesAmount > 0) {
            $isOverBudget = true;
            $overBudgetAmount = $totalExpensesAmount;
            $budgetUsagePercent = 100.0;
        }

        // Kiểm tra ngày chi tiêu có nằm ngoài khoảng thời gian mới của chuyến đi hay không
        $affectedDateItems = [];
        $hasDates = !empty($newStartDate) && !empty($newEndDate) && $this->validateTripDates($newStartDate, $newEndDate);

        if ($hasDates) {
            $newStart = Carbon::parse($newStartDate)->toDateString();
            $newEnd = Carbon::parse($newEndDate)->toDateString();

            foreach ($allExpenses as $expense) {
                if ($expense->expense_date) {
                    $expDate = $expense->expense_date->toDateString();
                    $issueType = null;
                    $reason = '';

                    if ($expDate < $newStart) {
                        $issueType = 'before_start';
                        $reason = "Khoản chi '{$expense->description}' ({$expense->expense_date->format('d/m/Y')}) diễn ra trước ngày bắt đầu mới của chuyến đi (" . Carbon::parse($newStartDate)->format('d/m/Y') . ').';
                    } elseif ($expDate > $newEnd) {
                        $issueType = 'after_end';
                        $reason = "Khoản chi '{$expense->description}' ({$expense->expense_date->format('d/m/Y')}) diễn ra sau ngày kết thúc mới của chuyến đi (" . Carbon::parse($newEndDate)->format('d/m/Y') . ').';
                    }

                    if ($issueType !== null) {
                        $affectedDateItems[] = [
                            'id' => $expense->id,
                            'category' => $expense->category,
                            'description' => $expense->description,
                            'amount' => (float) $expense->amount,
                            'expense_date' => $expense->expense_date->format('Y-m-d'),
                            'expense_date_formatted' => $expense->expense_date->format('d/m/Y'),
                            'issue_type' => $issueType,
                            'reason' => $reason,
                        ];
                    }
                }
            }
        }

        return [
            'has_affected_dates' => count($affectedDateItems) > 0,
            'affected_date_count' => count($affectedDateItems),
            'is_over_budget' => $isOverBudget,
            'over_budget_amount' => $overBudgetAmount,
            'total_expenses_amount' => $totalExpensesAmount,
            'total_expenses_count' => $totalExpensesCount,
            'old_budget' => $oldBudget,
            'new_budget' => $budgetToCheck,
            'remaining_budget' => $remainingBudget,
            'budget_usage_percent' => $budgetUsagePercent,
            'affected_date_items' => $affectedDateItems,
        ];
    }

    /**
     * Phân tích tổng thể ảnh hưởng của việc thay đổi chuyến đi đến Chi phí và Booking liên quan (kèm lịch trình phụ thuộc).
     */
    public function checkTripChangeImpact(int $tripId, array $newData = []): array
    {
        $newStartDate = $newData['start_date'] ?? null;
        $newEndDate = $newData['end_date'] ?? null;
        $newBudget = isset($newData['budget']) ? (float) $newData['budget'] : null;
        $newStatus = $newData['status'] ?? null;

        $bookingsImpact = $this->getAffectedBookings($tripId, $newStartDate, $newEndDate, $newStatus);
        $expensesImpact = $this->getAffectedExpenses($tripId, $newStartDate, $newEndDate, $newBudget);
        $itineraryImpact = $this->getAffectedItineraryItems($tripId, $newStartDate, $newEndDate);

        $summaryMessages = [];

        // Thông điệp cảnh báo về Booking
        if ($bookingsImpact['has_affected']) {
            $summaryMessages[] = "Có {$bookingsImpact['affected_count']} đơn đặt phòng bị ảnh hưởng bởi thay đổi thời gian hoặc trạng thái chuyến đi.";
        }

        // Thông điệp cảnh báo về Ngân sách
        if ($expensesImpact['is_over_budget']) {
            $summaryMessages[] = 'Ngân sách mới (' . number_format($expensesImpact['new_budget'], 0, ',', '.') . '₫) thấp hơn tổng chi tiêu thực tế (' . number_format($expensesImpact['total_expenses_amount'], 0, ',', '.') . '₫), vượt quá ' . number_format($expensesImpact['over_budget_amount'], 0, ',', '.') . '₫.';
        }

        // Thông điệp cảnh báo về ngày chi phí
        if ($expensesImpact['has_affected_dates']) {
            $summaryMessages[] = "Có {$expensesImpact['affected_date_count']} khoản chi phí phát sinh nằm ngoài khoảng thời gian mới của chuyến đi.";
        }

        // Thông điệp cảnh báo về Lịch trình
        if ($itineraryImpact['is_shortened'] && $itineraryImpact['affected_count'] > 0) {
            $summaryMessages[] = "Có {$itineraryImpact['affected_count']} hoạt động lịch trình sẽ được tự động bảo toàn dời về Ngày {$itineraryImpact['target_day']} do rút ngắn chuyến đi.";
        }

        $hasImpact = $bookingsImpact['has_affected']
            || $expensesImpact['is_over_budget']
            || $expensesImpact['has_affected_dates']
            || ($itineraryImpact['is_shortened'] && $itineraryImpact['affected_count'] > 0);

        return [
            'has_impact' => $hasImpact,
            'has_booking_impact' => $bookingsImpact['has_affected'],
            'has_expense_impact' => $expensesImpact['is_over_budget'] || $expensesImpact['has_affected_dates'],
            'has_itinerary_impact' => $itineraryImpact['is_shortened'] && $itineraryImpact['affected_count'] > 0,
            'bookings' => $bookingsImpact,
            'expenses' => $expensesImpact,
            'itinerary' => $itineraryImpact,
            'summary_messages' => $summaryMessages,
        ];
    }
}


