<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Trip;
use App\Services\Interfaces\BudgetServiceInterface;
use Illuminate\Support\Facades\DB;

class BudgetService implements BudgetServiceInterface
{
    /**
     * Lấy toàn bộ thông số tóm tắt ngân sách của chuyến đi.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return array
     */
    public function getBudgetSummary(int $tripId): array
    {
        $trip = Trip::with('expenses')->findOrFail($tripId);

        $budget = (float) ($trip->budget ?? 0);
        $totalExpenses = (float) $trip->expenses->sum('amount');
        $remainingBudget = $budget - $totalExpenses;
        $isOverBudget = $totalExpenses > $budget;
        $overBudgetAmount = $isOverBudget ? ($totalExpenses - $budget) : 0.0;

        // Tính tỷ lệ phần trăm ngân sách đã sử dụng
        if ($budget > 0) {
            $percentageUsed = round(($totalExpenses / $budget) * 100, 1);
        } else {
            $percentageUsed = $totalExpenses > 0 ? 100.0 : 0.0;
        }

        return [
            'trip' => $trip,
            'budget' => $budget,
            'total_expenses' => $totalExpenses,
            'remaining_budget' => $remainingBudget,
            'is_over_budget' => $isOverBudget,
            'over_budget_amount' => $overBudgetAmount,
            'percentage_used' => $percentageUsed,
        ];
    }

    /**
     * Cập nhật hạn mức ngân sách dự kiến của chuyến đi.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @param float $budget Hạn mức ngân sách mới (không âm)
     * @return Trip
     */
    public function updateBudget(int $tripId, float $budget): Trip
    {
        return DB::transaction(function () use ($tripId, $budget) {
            $trip = Trip::findOrFail($tripId);
            $trip->update([
                'budget' => max(0, $budget),
            ]);

            return $trip->fresh();
        });
    }

    /**
     * Tính tổng tất cả các khoản chi tiêu thực tế của chuyến đi.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return float
     */
    public function calculateTotalExpenses(int $tripId): float
    {
        return (float) Expense::where('trip_id', $tripId)->sum('amount');
    }

    /**
     * Tính số dư ngân sách còn lại (ngân sách dự kiến - tổng chi tiêu).
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return float
     */
    public function calculateRemainingBudget(int $tripId): float
    {
        $trip = Trip::findOrFail($tripId);
        $budget = (float) ($trip->budget ?? 0);

        return $budget - $this->calculateTotalExpenses($tripId);
    }

    /**
     * Tính số tiền chi tiêu vượt mức so với ngân sách dự kiến.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return float
     */
    public function calculateOverBudgetAmount(int $tripId): float
    {
        $remaining = $this->calculateRemainingBudget($tripId);

        return $remaining < 0 ? abs($remaining) : 0.0;
    }

    /**
     * Kiểm tra xem chuyến đi có đang ở trạng thái vượt ngân sách hay không.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @return bool
     */
    public function isOverBudget(int $tripId): bool
    {
        $trip = Trip::findOrFail($tripId);
        $budget = (float) ($trip->budget ?? 0);

        return $this->calculateTotalExpenses($tripId) > $budget;
    }
}
