<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Services\Interfaces\UserManagementServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Khởi tạo Controller với UserManagementServiceInterface thông qua Dependency Injection.
     *
     * @param UserManagementServiceInterface $userService
     */
    public function __construct(
        private readonly UserManagementServiceInterface $userService
    ) {
    }

    /**
     * Hiển thị danh sách người dùng hệ thống kèm bộ lọc và tìm kiếm.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search', ''),
            'role' => $request->query('role', 'all'),
        ];

        $users = $this->userService->getUsers($filters, 10);
        $counts = $this->userService->getUserCounts();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'counts' => $counts,
        ]);
    }

    /**
     * Xử lý thêm mới người dùng từ bảng điều khiển quản trị.
     *
     * @param StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Thêm mới người dùng thành công.');
    }

    /**
     * Xử lý cập nhật thông tin người dùng.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        try {
            $this->userService->updateUser($id, $request->validated());

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Cập nhật thông tin người dùng thành công.');
        } catch (ValidationException $e) {
            $errorMessage = $e->validator->errors()->first('is_active') ?? $e->getMessage();

            return redirect()
                ->route('admin.users.index')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Khóa hoặc mở khóa trạng thái tài khoản người dùng.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        try {
            $user = $this->userService->toggleUserStatus($id);
            $actionText = $user->is_active ? 'Mở khóa' : 'Khóa';

            return redirect()
                ->route('admin.users.index')
                ->with('success', "{$actionText} tài khoản người dùng '{$user->name}' thành công.");
        } catch (ValidationException $e) {
            $errorMessage = $e->validator->errors()->first('status') ?? $e->getMessage();

            return redirect()
                ->route('admin.users.index')
                ->with('error', $errorMessage);
        }
    }
}
