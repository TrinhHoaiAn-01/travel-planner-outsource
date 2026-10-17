<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Services\Interfaces\CategoryManagementServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Khởi tạo Controller với CategoryManagementServiceInterface thông qua Dependency Injection.
     *
     * @param CategoryManagementServiceInterface $categoryService
     */
    public function __construct(
        private readonly CategoryManagementServiceInterface $categoryService
    ) {
    }

    /**
     * Hiển thị danh sách các danh mục du lịch theo giao diện Admin Panel.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search', ''),
            'status' => $request->query('status', 'all'),
        ];

        $categories = $this->categoryService->getCategories($filters, 10);
        $statistics = $this->categoryService->getCategoryStatistics();

        return view('admin.categories.index', [
            'categories' => $categories,
            'filters' => $filters,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Xử lý thêm mới một danh mục từ Modal form.
     *
     * @param StoreCategoryRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->categoryService->createCategory($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Thêm mới danh mục du lịch thành công.');
    }

    /**
     * Xử lý cập nhật thông tin danh mục.
     *
     * @param UpdateCategoryRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateCategoryRequest $request, int $id): RedirectResponse
    {
        $this->categoryService->updateCategory($id, $request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục du lịch thành công.');
    }

    /**
     * Xử lý xóa một danh mục (Tuân thủ quy tắc bảo vệ toàn vẹn dữ liệu BR-17).
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->categoryService->deleteCategory($id);

            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Xóa danh mục du lịch thành công.');
        } catch (ValidationException $e) {
            $errorMessage = $e->validator->errors()->first('delete') ?? $e->getMessage();

            return redirect()
                ->route('admin.categories.index')
                ->with('error', $errorMessage);
        }
    }
}
