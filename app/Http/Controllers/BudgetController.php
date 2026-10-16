<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBudgetRequest;
use App\Models\Trip;
use App\Services\Interfaces\BudgetServiceInterface;
use App\Services\Interfaces\ExpenseServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BudgetController extends Controller
{
    /**
     * Khởi tạo BudgetController phụ thuộc vào các Service Interfaces.
     */
    public function __construct(
        private BudgetServiceInterface $budgetService,
        private ExpenseServiceInterface $expenseService
    ) {
    }

    /**
     * Hiển thị màn hình theo dõi và quản lý ngân sách chuyến đi (Hình 21, Hình 22).
     */
    public function show(Request $request, Trip $trip): View
    {
        // Kiểm tra quyền sở hữu chuyến đi theo quy tắc BR-01 & BR-03
        Gate::authorize('view', $trip);

        $selectedCategory = $request->query('category', 'all');

        // Lấy số liệu tóm tắt ngân sách qua BudgetServiceInterface
        $summary = $this->budgetService->getBudgetSummary($trip->id);

        // Lấy danh sách các khoản chi tiêu có lọc theo danh mục
        $expenses = $this->expenseService->getExpenses($trip->id, $selectedCategory);

        return view('trips.budget', [
            'trip' => $trip,
            'summary' => $summary,
            'expenses' => $expenses,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    /**
     * Cập nhật hạn mức ngân sách dự kiến của chuyến đi (FR22).
     */
    public function update(UpdateBudgetRequest $request, Trip $trip): RedirectResponse|JsonResponse
    {
        // Kiểm tra quyền sở hữu chuyến đi trước khi cập nhật
        Gate::authorize('update', $trip);

        $validatedData = $request->validated();
        $updatedTrip = $this->budgetService->updateBudget($trip->id, (float) $validatedData['budget']);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Cập nhật hạn mức ngân sách thành công!',
                'budget' => (float) $updatedTrip->budget,
            ]);
        }

        return redirect()
            ->route('trips.budget.show', $trip)
            ->with('success', 'Cập nhật hạn mức ngân sách thành công!');
    }
}
