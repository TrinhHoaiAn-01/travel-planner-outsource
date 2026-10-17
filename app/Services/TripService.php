<?php

namespace App\Services;

use App\Models\Trip;
use App\Services\Interfaces\TripServiceInterface;
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

            if (isset($data['start_date']) && isset($data['end_date'])) {
                if (! $this->validateTripDates($data['start_date'], $data['end_date'])) {
                    throw new InvalidArgumentException('Ngày bắt đầu không được sau ngày kết thúc.');
                }

                // Xử lý thay đổi ngày và tự động bảo toàn các hoạt động lịch trình khi số ngày thay đổi
                $this->handleTripDateChange($tripId, $data['start_date'], $data['end_date']);
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

            return $trip->fresh();
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
     * Cập nhật trạng thái của chuyến đi.
     */
    public function updateTripStatus(int $tripId, string $status): Trip
    {
        $trip = Trip::findOrFail($tripId);
        $trip->status = $status;
        $trip->save();

        return $trip;
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
     * Xử lý khi ngày của chuyến đi bị thay đổi.
     * Kiểm tra tính hợp lệ và tự động bảo toàn các hoạt động lịch trình nếu số ngày bị rút ngắn.
     */
    public function handleTripDateChange(int $tripId, ?string $newStartDate, ?string $newEndDate): void
    {
        DB::transaction(function () use ($tripId, $newStartDate, $newEndDate) {
            $trip = Trip::with('itineraryItems')->findOrFail($tripId);

            if ($newStartDate !== null && $newEndDate !== null) {
                if (! $this->validateTripDates($newStartDate, $newEndDate)) {
                    throw new InvalidArgumentException('Ngày bắt đầu không được sau ngày kết thúc.');
                }

                $newTotalDays = (int) \Carbon\Carbon::parse($newStartDate)->diffInDays(\Carbon\Carbon::parse($newEndDate)) + 1;

                // Xử lý bảo toàn dữ liệu: Nếu chuyến đi bị rút ngắn ngày, chuyển các hoạt động ở ngày vượt quá về ngày cuối cùng mới
                if ($trip->itineraryItems->isNotEmpty()) {
                    foreach ($trip->itineraryItems as $item) {
                        if ($item->day_number > $newTotalDays) {
                            $item->day_number = $newTotalDays;
                            $item->save();
                        }
                    }
                }
            }

            $trip->start_date = $newStartDate;
            $trip->end_date = $newEndDate;
            $trip->save();
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
}


