<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền đổi mật khẩu hay không.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Quy tắc kiểm tra tính hợp lệ của dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh bằng tiếng Việt 100%.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp với mật khẩu mới.',
            'password.different' => 'Mật khẩu mới phải khác với mật khẩu hiện tại.',
            'password_confirmation.required' => 'Vui lòng xác nhận lại mật khẩu mới.',
        ];
    }
}
