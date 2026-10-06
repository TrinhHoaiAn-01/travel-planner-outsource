<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'email' => is_string($this->email) ? trim($this->email) : $this->email,
        ]);
    }

    /**
     * Các quy tắc xác thực dữ liệu đăng ký theo tài liệu đặc tả Hình 5.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:40'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }

    /**
     * Các thông báo lỗi tiếng Việt chuẩn xác theo Bảng 5 tài liệu đồ án.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Vui lòng nhập họ và tên.',
            'name.max' => 'Họ và tên không được vượt quá 40 ký tự.',
            'email.required' => 'Vui lòng nhập email hợp lệ.',
            'email.email' => 'Vui lòng nhập email hợp lệ.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email này đã được sử dụng. Vui lòng sử dụng email khác.',
            'password.required' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password_confirmation.required' => 'Mật khẩu xác nhận không khớp.',
            'password_confirmation.same' => 'Mật khẩu xác nhận không khớp.',
        ];
    }
}
