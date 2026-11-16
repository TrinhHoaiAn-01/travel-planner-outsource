<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Services\Interfaces\UserManagementServiceInterface;
use App\Support\ListPage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
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
        $page = ListPage::from($request);
        $request->validate([
            'role' => ['sometimes', 'nullable', 'in:all,admin,user'],
            'status' => ['sometimes', 'nullable', 'in:all,active,disabled,trashed,deleted,0,1,true,false'],
        ]);
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'role' => (string) $request->query('role', 'all'),
            'status' => (string) $request->query('status', 'all'),
        ];

        $users = $this->userService->getUsers($filters, 10);
        abort_if($page > $users->lastPage(), 404);
        $counts = $this->userService->getUserCounts();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
            'counts' => $counts,
        ]);
    }

    /**
     * Xem thông tin chi tiết một tài khoản người dùng (hỗ trợ cả JSON cho modal và View cho truy cập trực tiếp).
     *
     * @param Request $request
     * @param mixed $user
     * @return View|JsonResponse|RedirectResponse
     */
    public function show(Request $request, mixed $user): View|JsonResponse|RedirectResponse
    {
        $userId = $user instanceof \App\Models\User ? $user->id : (int) $user;
        $userDetails = $this->userService->getUserDetails($userId);

        if (! $userDetails) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể tải thông tin chi tiết người dùng.',
                ], 404);
            }

            abort(404);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $userDetails->id,
                    'user_code' => $userDetails->user_code,
                    'name' => $userDetails->name,
                    'email' => $userDetails->email,
                    'phone' => $userDetails->phone ?? 'Chưa cập nhật',
                    'city' => $userDetails->city ?? 'Chưa cập nhật',
                    'bio' => $userDetails->bio ?? 'Chưa cập nhật tiểu sử.',
                    'role' => $userDetails->role,
                    'role_label' => $userDetails->role_label,
                    'is_active' => (bool) $userDetails->is_active,
                    'status_label' => $userDetails->status_label,
                    'avatar' => $userDetails->avatar,
                    'email_verified_at' => $userDetails->email_verified_at ? $userDetails->email_verified_at->format('d/m/Y H:i') : 'Chưa xác thực',
                    'created_at' => $userDetails->created_at ? $userDetails->created_at->format('d/m/Y H:i') : '15/01/2026',
                    'updated_at' => $userDetails->updated_at ? $userDetails->updated_at->format('d/m/Y H:i') : '15/01/2026',
                    'trips_count' => (int) ($userDetails->trips_count ?? 0),
                    'reviews_count' => (int) ($userDetails->reviews_count ?? 0),
                    'bookings_count' => (int) ($userDetails->bookings_count ?? 0),
                    'favorites_count' => (int) ($userDetails->favorites_count ?? 0),
                ],
            ]);
        }

        return view('admin.users.show', [
            'user' => $userDetails,
        ]);
    }

    /**
     * Mở biểu mẫu tạo người dùng mới.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Xử lý thêm mới người dùng từ bảng điều khiển quản trị.
     *
     * @param StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        try {
            $this->userService->createUser($request->validated());
        } catch (UniqueConstraintViolationException $exception) {
            $this->handleDuplicateEmail($request, $exception);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Thêm mới người dùng thành công.');
    }

    /**
     * Mở biểu mẫu chỉnh sửa thông tin người dùng.
     *
     * @param mixed $user
     * @return View|RedirectResponse
     */
    public function edit(mixed $user): View|RedirectResponse
    {
        $userId = $user instanceof \App\Models\User ? $user->id : (int) $user;
        $userDetails = $this->userService->getUserDetails($userId);

        abort_if(! $userDetails || $userDetails->trashed(), 404);

        return view('admin.users.edit', [
            'user' => $userDetails,
        ]);
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
        } catch (UniqueConstraintViolationException $exception) {
            $this->handleDuplicateEmail($request, $exception, $id);
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

    /**
     * Xóa mềm (Soft Delete) tài khoản người dùng khỏi hệ thống.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            if (! $this->userService->deleteUser($id)) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Xóa không hợp lệ. Tài khoản không tồn tại hoặc đã được xóa.');
            }

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Xóa mềm tài khoản người dùng thành công.');
        } catch (ValidationException $e) {
            $errorMessage = $e->validator->errors()->first('user') ?? $e->getMessage();

            return redirect()
                ->route('admin.users.index')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Khôi phục tài khoản người dùng đã bị xóa mềm.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function restore(int $id): RedirectResponse
    {
        try {
            $user = $this->userService->restoreUser($id);

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Khôi phục tài khoản '{$user->name}' thành công.");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Không thể khôi phục tài khoản người dùng.');
        }
    }

    private function handleDuplicateEmail(Request $request, UniqueConstraintViolationException $exception, ?int $id = null): never
    {
        $users = \App\Models\User::withTrashed()->where('email', $request->validated('email'));
        if ($id !== null) {
            $users->whereKeyNot($id);
        }

        if (! $users->exists()) {
            throw $exception;
        }

        throw ValidationException::withMessages([
            'email' => 'Địa chỉ email này đã tồn tại trên hệ thống.',
        ]);
    }
}
