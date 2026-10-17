<?php

namespace App\Services;

use App\Models\Expense;
use App\Services\Interfaces\ExpenseServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseService implements ExpenseServiceInterface
{
    /**
     * Lấy danh sách các khoản chi tiêu của chuyến đi, có hỗ trợ lọc theo danh mục.
     *
     * @param int $tripId Mã định danh chuyến đi
     * @param string|null $category Tên danh mục cần lọc (hoặc 'all' / null để lấy tất cả)
     * @return Collection
     */
    public function getExpenses(int $tripId, ?string $category = null): Collection
    {
        $query = Expense::where('trip_id', $tripId)
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc');

        if (! empty($category) && $category !== 'all') {
            if (in_array($category, ['lodging', 'accommodation'], true)) {
                $query->whereIn('category', ['lodging', 'accommodation']);
            } else {
                $query->where('category', $category);
            }
        }

        return $query->get();
    }

    /**
     * Tạo mới một khoản chi tiêu cho chuyến đi theo đúng cấu trúc bảng expenses.
     *
     * @param array $data Dữ liệu khoản chi bao gồm: trip_id, category, description (hoặc title), amount, expense_date
     * @return Expense
     */
    public function createExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            $description = $data['title'] ?? ($data['description'] ?? null);

            // Nếu người dùng nhập thêm ghi chú thì lưu kèm vào mô tả chi phí
            if (! empty($data['note']) && $data['note'] !== $description) {
                $description = $description ? "{$description} - {$data['note']}" : $data['note'];
            }

            $payload = [
                'trip_id' => $data['trip_id'],
                'category' => $data['category'],
                'description' => $description,
                'amount' => $data['amount'],
                'expense_date' => $data['expense_date'] ?? null,
            ];

            return Expense::create($payload);
        });
    }

    /**
     * Cập nhật thông tin của một khoản chi tiêu đã tồn tại.
     *
     * @param int $expenseId Mã định danh khoản chi tiêu
     * @param array $data Dữ liệu cập nhật
     * @return Expense
     */
    public function updateExpense(int $expenseId, array $data): Expense
    {
        return DB::transaction(function () use ($expenseId, $data) {
            $expense = Expense::findOrFail($expenseId);

            $payload = [];
            if (isset($data['category'])) {
                $payload['category'] = $data['category'];
            }
            if (isset($data['title']) || isset($data['description'])) {
                $description = $data['title'] ?? $data['description'];
                if (! empty($data['note']) && $data['note'] !== $description) {
                    $description = "{$description} - {$data['note']}";
                }
                $payload['description'] = $description;
            }
            if (isset($data['amount'])) {
                $payload['amount'] = $data['amount'];
            }
            if (array_key_exists('expense_date', $data)) {
                $payload['expense_date'] = $data['expense_date'];
            }

            $expense->update($payload);

            return $expense->fresh();
        });
    }

    /**
     * Xóa một khoản chi tiêu khỏi chuyến đi.
     *
     * @param int $expenseId Mã định danh khoản chi tiêu
     * @return bool
     */
    public function deleteExpense(int $expenseId): bool
    {
        return DB::transaction(function () use ($expenseId) {
            $expense = Expense::findOrFail($expenseId);

            return (bool) $expense->delete();
        });
    }
}
