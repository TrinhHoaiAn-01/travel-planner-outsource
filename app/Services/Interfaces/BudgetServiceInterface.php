<?php

namespace App\Services\Interfaces;

use App\Models\Trip;

interface BudgetServiceInterface
{
    /**
     * Lấy tóm tắt ngân sách của chuyến đi bao gồm: tổng ngân sách dự kiến,
     * tổng chi tiêu thực tế, số dư khả dụng, tỷ lệ phần trăm đã dùng,
     * trạng thái vượt ngân sách và số tiền vượt.
     *
     * @param int $tripId Mã định danh của chuyến đi
     * @return array Mảng chứa thông tin tóm tắt ngân sách
     */
    public function getBudgetSummary(int $tripId): array;

    /**
     * Cập nhật hạn mức tổng ngân sách dự kiến của chuyến đi.
     *
     * @param int $tripId Mã định danh của chuyến đi
     * @param float $budget Hạn mức ngân sách mới (không âm)
     * @return Trip Chuyến đi sau khi đã được cập nhật ngân sách
     */
    public function updateBudget(int $tripId, float $budget): Trip;

    /**
     * Tính tổng tất cả các khoản chi tiêu thực tế của chuyến đi.
     *
     * @param int $tripId Mã định danh của chuyến đi
     * @return float Tổng số tiền chi tiêu
     */
    public function calculateTotalExpenses(int $tripId): float;

    /**
     * Tính số dư ngân sách còn lại (ngân sách dự kiến trừ tổng chi tiêu).
     *
     * @param int $tripId Mã định danh của chuyến đi
     * @return float Số dư khả dụng (có thể âm nếu chi tiêu vượt mức)
     */
    public function calculateRemainingBudget(int $tripId): float;

    /**
     * Tính số tiền chi tiêu vượt mức so với ngân sách dự kiến.
     * Trả về 0 nếu chi tiêu chưa vượt ngân sách.
     *
     * @param int $tripId Mã định danh của chuyến đi
     * @return float Số tiền vượt mức
     */
    public function calculateOverBudgetAmount(int $tripId): float;

    /**
     * Kiểm tra xem chuyến đi có đang ở trạng thái chi tiêu vượt ngân sách hay không.
     *
     * @param int $tripId Mã định danh của chuyến đi
     * @return bool True nếu tổng chi tiêu lớn hơn ngân sách dự kiến
     */
    public function isOverBudget(int $tripId): bool;
}
