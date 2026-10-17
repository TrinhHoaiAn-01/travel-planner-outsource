<?php

namespace App\Services\Interfaces;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseServiceInterface
{
    /**
     * Lấy danh sách các khoản chi tiêu của chuyến đi, có hỗ trợ lọc theo danh mục.
     *
     * @param int $tripId Mã định danh của chuyến đi
     * @param string|null $category Tên danh mục cần lọc (nếu có)
     * @return Collection Danh sách các khoản chi tiêu
     */
    public function getExpenses(int $tripId, ?string $category = null): Collection;

    /**
     * Tạo mới một khoản chi tiêu cho chuyến đi.
     *
     * @param array $data Dữ liệu khoản chi bao gồm: trip_id, category, description, amount, expense_date
     * @return Expense Bản ghi khoản chi tiêu vừa tạo
     */
    public function createExpense(array $data): Expense;

    /**
     * Cập nhật thông tin của một khoản chi tiêu.
     *
     * @param int $expenseId Mã định danh của khoản chi tiêu
     * @param array $data Dữ liệu cần cập nhật
     * @return Expense Bản ghi khoản chi tiêu sau khi cập nhật
     */
    public function updateExpense(int $expenseId, array $data): Expense;

    /**
     * Xóa một khoản chi tiêu khỏi chuyến đi.
     *
     * @param int $expenseId Mã định danh của khoản chi tiêu
     * @return bool True nếu xóa thành công
     */
    public function deleteExpense(int $expenseId): bool;
}
