<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Services\Interfaces\UserManagementServiceInterface;
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
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'role' => (string) $request->query('role', 'all'),
            'status' => (string) $request->query('status', 'all'),
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

            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Không tìm thấy thông tin chi tiết người dùng.');
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
        $this->userService->createUser($request->validated());

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

        if (! $userDetails) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Không tìm thấy thông tin người dùng.');
        }

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
        } catch (ValidationException $e) {
            $errorMessage = $e->validator->errors()->first('role')
                ?? $e->validator->errors()->first('is_active')
                ?? $e->validator->errors()->first()
                ?? $e->getMessage();

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
