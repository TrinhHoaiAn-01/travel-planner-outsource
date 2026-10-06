<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Khởi tạo AuthController với ràng buộc AuthServiceInterface.
     */
    public function __construct(
        private AuthServiceInterface $authService
    ) {
    }

    /**
     * Hiển thị giao diện đăng ký tài khoản người dùng.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký tài khoản người dùng mới.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        // Gọi Service thực hiện nghiệp vụ đăng ký và tạo tài khoản
        $user = $this->authService->registerUser($request->validated());

        // Tự động đăng nhập cho người dùng vừa đăng ký
        Auth::login($user);

        // Chuyển hướng sang màn hình thông báo xác thực email theo đặc tả luồng ứng dụng
        return redirect()
            ->route('verification.notice')
            ->with('success', 'Đăng ký tài khoản thành công! Vui lòng kiểm tra email để xác thực tài khoản.');
    }

    /**
     * Hiển thị màn hình hướng dẫn xác thực email cho người dùng đã đăng nhập.
     */
    public function showVerificationNotice(Request $request): View|RedirectResponse
    {
        // Nếu email đã được xác thực, chuyển hướng về trang chủ
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('auth.verify-email');
    }

    /**
     * Xử lý yêu cầu xác thực email từ liên kết gửi về hòm thư người dùng.
     */
    public function verifyEmail(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = $request->user();

        // Kiểm tra tính hợp lệ của id và chuỗi băm email
        if (! hash_equals((string) $id, (string) $user->getKey())) {
            abort(403, 'Liên kết xác thực không thuộc về tài khoản hiện tại.');
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Chuỗi xác thực email không hợp lệ.');
        }

        // Gọi Service cập nhật trạng thái xác thực email
        $this->authService->verifyEmail($user);

        return redirect()
            ->route('home')
            ->with('success', 'Xác thực email thành công! Bạn có thể sử dụng đầy đủ chức năng hệ thống.');
    }

    /**
     * Gửi lại liên kết xác thực email cho người dùng.
     */
    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        // Nếu người dùng đã xác thực email thì chuyển về trang chủ
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        // Gọi Service gửi lại thư xác thực
        $this->authService->resendVerificationEmail($request->user());

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Hiển thị giao diện đăng nhập cho người dùng.
     */
    public function showLoginForm(): View
    {
        // Khởi tạo mã CAPTCHA nếu chưa có trong phiên làm việc
        $captchaCode = session('auth_captcha') ?: $this->authService->refreshCaptcha();

        return view('auth.login', [
            'captchaCode' => $captchaCode,
        ]);
    }

    /**
     * Làm mới mã CAPTCHA cho phiên đăng nhập.
     */
    public function refreshCaptcha(Request $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $newCaptcha = $this->authService->refreshCaptcha();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'captcha' => $newCaptcha,
            ]);
        }

        return back();
    }

    /**
     * Xử lý đăng nhập tài khoản người dùng vào hệ thống.
     */
    public function login(\App\Http\Requests\LoginRequest $request): RedirectResponse
    {
        // Xác thực thông tin qua Service Interface
        $this->authService->authenticate(
            $request->only('email', 'password'),
            $request->boolean('remember')
        );

        // Điều hướng theo vai trò người dùng
        if (Auth::user()->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Đăng xuất người dùng khỏi hệ thống.
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logoutUser();

        return redirect()->route('login');
    }
}
