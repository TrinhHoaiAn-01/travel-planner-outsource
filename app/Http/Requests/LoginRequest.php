<?php

namespace App\Http\Requests;

use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện yêu cầu này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Chuẩn bị dữ liệu trước khi kiểm tra (cắt bỏ khoảng trắng ở đầu và cuối).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
            'captcha' => is_string($this->captcha) ? trim($this->captcha) : $this->captcha,
            'remember' => $this->boolean('remember'),
        ]);
    }

    /**
     * Các quy tắc xác thực dữ liệu đăng nhập theo tài liệu Hình 6 / Bảng 6.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'captcha' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt theo quy chuẩn đồ án Bảng 6.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Vui lòng nhập email hợp lệ.',
            'email.email' => 'Vui lòng nhập email hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'captcha.required' => 'Mã CAPTCHA không chính xác.',
        ];
    }

    /**
     * Kiểm tra tính chính xác của mã CAPTCHA sau khi validate các trường cơ bản.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('captcha')) {
                /** @var AuthServiceInterface $authService */
                $authService = app(AuthServiceInterface::class);

                if (! $authService->validateCaptcha((string) $this->captcha)) {
                    $validator->errors()->add('captcha', 'Mã CAPTCHA không chính xác.');
                }
            }
        });
    }
}
