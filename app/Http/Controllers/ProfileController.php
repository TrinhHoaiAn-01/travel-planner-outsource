<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Models\City;
use App\Services\Interfaces\ProfileServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Khởi tạo ProfileController với ràng buộc ProfileServiceInterface.
     */
    public function __construct(
        private ProfileServiceInterface $profileService
    ) {
    }

    /**
     * Hiển thị trang Hồ sơ cá nhân của người dùng.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        // Lấy thông tin hồ sơ và các số liệu thống kê qua Service
        $profileData = $this->profileService->getProfile($user);

        // Lấy danh sách thành phố để hiển thị trong dropdown chọn thành phố sinh sống
        $cities = City::orderBy('name')->pluck('name')->all();
        if (empty($cities)) {
            $cities = [
                'Đà Nẵng',
                'Hà Nội',
                'TP. Hồ Chí Minh',
                'Nha Trang',
                'Huế',
                'Đà Lạt',
                'Hội An',
                'Phú Quốc',
                'Cần Thơ',
                'Hải Phòng',
                'Vũng Tàu',
                'Sa Pa',
            ];
        }

        return view('profile.show', [
            'user' => $profileData['user'],
            'tripsCount' => $profileData['tripsCount'],
            'favoritesCount' => $profileData['favoritesCount'],
            'reviewsCount' => $profileData['reviewsCount'],
            'cities' => $cities,
        ]);
    }

    /**
     * Cập nhật thông tin hồ sơ cá nhân của người dùng.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Kiểm tra quyền hạn theo UserPolicy (Quy tắc BR-02)
        Gate::authorize('update', $user);

        // Gọi Service thực hiện cập nhật thông tin
        $this->profileService->updateProfile($user, $request->validated());

        return redirect()
            ->route('profile.show')
            ->with('success', 'Cập nhật thông tin cá nhân thành công.');
    }

    /**
     * Cập nhật ảnh đại diện của người dùng.
     */
    public function updateAvatar(UploadAvatarRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Kiểm tra quyền hạn theo UserPolicy (Quy tắc BR-02)
        Gate::authorize('update', $user);

        // Gọi Service lưu ảnh và cập nhật
        $avatarUrl = $this->profileService->updateAvatar($user, $request->file('avatar'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'avatar_url' => $avatarUrl,
                'message' => 'Cập nhật ảnh đại diện thành công.',
            ]);
        }

        return redirect()
            ->route('profile.show')
            ->with('success', 'Cập nhật ảnh đại diện thành công.');
    }

    /**
     * Hiển thị giao diện Đổi mật khẩu.
     */
    public function showChangePasswordForm(): View
    {
        return view('profile.change-password');
    }

    /**
     * Xử lý thay đổi mật khẩu người dùng.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Kiểm tra quyền hạn theo UserPolicy (Quy tắc BR-02)
        Gate::authorize('changePassword', $user);

        // Gọi Service thực hiện kiểm tra và đổi mật khẩu
        $this->profileService->changePassword($user, $request->validated());

        return redirect()
            ->route('profile.show')
            ->with('success', 'Đổi mật khẩu thành công. Mật khẩu mới của bạn đã có hiệu lực.');
    }
}
