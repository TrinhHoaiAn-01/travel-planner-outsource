<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\Trip;
use App\Services\Interfaces\ExpenseServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    /**
     * Khởi tạo ExpenseController phụ thuộc vào ExpenseServiceInterface.
     */
    public function __construct(
        private ExpenseServiceInterface $expenseService
    ) {
    }

    /**
     * Thêm mới một khoản chi tiêu vào chuyến đi (FR23).
     */
    public function store(StoreExpenseRequest $request, Trip $trip): RedirectResponse|JsonResponse
    {
        // Kiểm tra quyền sở hữu chuyến đi trước khi thêm chi phí
        Gate::authorize('update', $trip);

        $validatedData = $request->validated();
        $validatedData['trip_id'] = $trip->id;

        $expense = $this->expenseService->createExpense($validatedData);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Thêm khoản chi mới thành công!',
                'expense' => $expense,
            ], 201);
        }

        return redirect()
            ->route('trips.budget.show', $trip)
            ->with('success', 'Thêm khoản chi mới thành công!');
    }

    /**
     * Cập nhật thông tin khoản chi tiêu (FR24).
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse|JsonResponse
    {
        // Kiểm tra quyền sở hữu khoản chi thông qua ExpensePolicy
        Gate::authorize('update', $expense);

        $validatedData = $request->validated();
        $updatedExpense = $this->expenseService->updateExpense($expense->id, $validatedData);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Cập nhật khoản chi thành công!',
                'expense' => $updatedExpense,
            ]);
        }

        return redirect()
            ->route('trips.budget.show', $expense->trip_id)
            ->with('success', 'Cập nhật khoản chi thành công!');
    }

    /**
     * Xóa một khoản chi tiêu khỏi chuyến đi (FR24).
     */
    public function destroy(Expense $expense): RedirectResponse|JsonResponse
    {
        // Kiểm tra quyền sở hữu khoản chi thông qua ExpensePolicy
        Gate::authorize('delete', $expense);

        $tripId = $expense->trip_id;
        $this->expenseService->deleteExpense($expense->id);

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Đã xóa khoản chi thành công!',
            ]);
        }

        return redirect()
            ->route('trips.budget.show', $tripId)
            ->with('success', 'Đã xóa khoản chi thành công!');
    }
}
