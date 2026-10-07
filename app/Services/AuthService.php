<?php

namespace App\Services;

use App\Models\User;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    /**
     * Đăng ký tài khoản người dùng mới vào hệ thống.
     *
     * @param array<string, mixed> $data Dữ liệu đã được kiểm thực qua RegisterRequest
     * @return User
     */
    public function registerUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Chuẩn hóa và làm sạch dữ liệu đầu vào (cắt bỏ khoảng trắng thừa)
            $cleanedName = trim((string) $data['name']);
            $cleanedEmail = strtolower(trim((string) $data['email']));

            // Tạo người dùng mới với vai trò mặc định là user và trạng thái hoạt động
            // Tuyệt đối không cho phép người dùng tự cấp quyền admin khi đăng ký
            $user = User::create([
                'name' => $cleanedName,
                'email' => $cleanedEmail,
                'password' => Hash::make($data['password']),
                'role' => 'user',
                'is_active' => true,
            ]);

            // Kích hoạt sự kiện Registered để gửi email thông báo xác thực cho tài khoản
            event(new Registered($user));

            return $user;
        });
    }

    /**
     * Xác thực email cho người dùng nếu chưa được xác thực.
     *
     * @param User $user
     * @return bool
     */
    public function verifyEmail(User $user): bool
    {
        // Nếu tài khoản đã xác thực email trước đó thì không cần xử lý lại
        if ($user->hasVerifiedEmail()) {
            return true;
        }

        // Đánh dấu email đã được xác thực và kích hoạt sự kiện Verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return true;
        }

        return false;
    }

    /**
     * Gửi lại liên kết xác thực email cho người dùng.
     *
     * @param User $user
     * @return void
     */
    public function resendVerificationEmail(User $user): void
    {
        // Chỉ gửi lại email nếu tài khoản chưa được xác thực
        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }

    /**
     * Xác thực thông tin đăng nhập và tạo phiên làm việc cho người dùng.
     *
     * @param array<string, mixed> $credentials
     * @param bool $remember
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(array $credentials, bool $remember = false): bool
    {
        $normalizedEmail = strtolower(trim((string) $credentials['email']));

        // Tìm người dùng trong hệ thống để kiểm tra trạng thái hoạt động
        $user = User::where('email', $normalizedEmail)->first();

        // Kiểm tra tài khoản có bị khóa không
        if ($user && ! $user->is_active) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        // Thực hiện đăng nhập với thông tin đã nhập
        if (! \Illuminate\Support\Facades\Auth::attempt([
            'email' => $normalizedEmail,
            'password' => (string) $credentials['password'],
        ], $remember)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ]);
        }

        // Tạo mới session ID để chống tấn công Session Fixation
        request()->session()->regenerate();

        return true;
    }

    /**
     * Đăng xuất người dùng hiện tại và xóa phiên làm việc an toàn.
     */
    public function logoutUser(): void
    {
        \Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Tạo mã CAPTCHA ngẫu nhiên gồm 5 ký tự và lưu vào session.
     */
    public function refreshCaptcha(): string
    {
        // Tập ký tự rõ ràng, tránh nhầm lẫn giữa chữ O và số 0, chữ I và số 1
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $captchaLength = 5;
        $captchaCode = '';

        for ($index = 0; $index < $captchaLength; $index++) {
            $randomIndex = random_int(0, strlen($characters) - 1);
            $captchaCode .= $characters[$randomIndex];
        }

        session(['auth_captcha' => $captchaCode]);

        return $captchaCode;
    }

    /**
     * Kiểm tra tính chính xác của mã CAPTCHA người dùng nhập.
     */
    public function validateCaptcha(string $inputCaptcha): bool
    {
        $storedCaptcha = session('auth_captcha');

        if (empty($storedCaptcha)) {
            return false;
        }

        // So sánh không phân biệt hoa thường
        $isValid = strtoupper(trim($inputCaptcha)) === strtoupper((string) $storedCaptcha);

        // Sau khi kiểm tra, tạo mã CAPTCHA mới để CAPTCHA cũ hết hiệu lực
        $this->refreshCaptcha();

        return $isValid;
    }

    /**
     * Gửi liên kết đặt lại mật khẩu cho tài khoản người dùng qua email.
     * Tuân thủ quy chuẩn bảo mật: dùng thông báo chung để tránh rò rỉ sự tồn tại của email.
     *
     * @param array<string, mixed> $data
     * @return string
     */
    public function sendPasswordResetLink(array $data): string
    {
        $normalizedEmail = strtolower(trim((string) $data['email']));

        // Gửi liên kết đặt lại mật khẩu qua Password Broker của Laravel
        Password::broker()->sendResetLink(['email' => $normalizedEmail]);

        // Luôn trả về thông báo chung theo yêu cầu bảo mật trong tài liệu đặc tả
        return 'Nếu email được đăng ký, hướng dẫn đặt lại mật khẩu sẽ được gửi đến email của bạn.';
    }

    /**
     * Đặt lại mật khẩu mới cho tài khoản người dùng bằng mã token xác thực.
     *
     * @param array<string, mixed> $data
     * @return string
     * @throws ValidationException
     */
    public function resetPassword(array $data): string
    {
        $credentials = [
            'email' => strtolower(trim((string) $data['email'])),
            'password' => (string) $data['password'],
            'password_confirmation' => (string) $data['password_confirmation'],
            'token' => (string) $data['token'],
        ];

        // Thực hiện đặt lại mật khẩu và cập nhật vào cơ sở dữ liệu
        $status = Password::broker()->reset($credentials, function (User $user, string $password) {
            $user->password = Hash::make($password);
            $user->save();

            event(new PasswordReset($user));
        });

        if ($status === Password::PASSWORD_RESET) {
            return 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập với mật khẩu mới.';
        }

        if ($status === Password::INVALID_TOKEN) {
            throw ValidationException::withMessages([
                'token' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => 'Địa chỉ email không hợp lệ hoặc không tìm thấy tài khoản.',
        ]);
    }
}
