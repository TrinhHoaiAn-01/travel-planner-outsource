<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng thực hiện yêu cầu.
     * Chỉ quản trị viên (Admin) mới có quyền cập nhật người dùng (BR-13, BR-20).
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Chuẩn hóa và làm sạch dữ liệu trước khi kiểm tra (chống XSS và chuẩn hóa email).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => \App\Support\SecuritySanitizer::clean((string) $this->input('name')),
            ]);
        }
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim((string) $this->input('email'))),
            ]);
        }
        if ($this->has('phone')) {
            $this->merge([
                'phone' => trim((string) $this->input('phone')),
            ]);
        }
        if ($this->has('city')) {
            $this->merge([
                'city' => \App\Support\SecuritySanitizer::clean((string) $this->input('city')),
            ]);
        }
        if ($this->has('bio')) {
            $this->merge([
                'bio' => \App\Support\SecuritySanitizer::clean((string) $this->input('bio')),
            ]);
        }
    }

    /**
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi cập nhật người dùng.
     */
    public function rules(): array
    {
        $userParam = $this->route('user');
        $userId = $userParam instanceof User ? $userParam->id : (int) $userParam;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,user'],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        $digits = preg_replace('/\D/', '', (string) $value);
                        if (! preg_match('/^(0[3|5|7|8|9])[0-9]{8}$/', $digits)) {
                            $fail('Số điện thoại không đúng định dạng di động Việt Nam (gồm 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09).');
                        }
                    }
                },
            ],
            'city' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, chuẩn mực.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên người dùng.',
            'name.string' => 'Họ và tên phải là chuỗi ký tự hợp lệ.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã tồn tại trên hệ thống.',
            'password.min' => 'Mật khẩu phải có độ dài tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không trùng khớp.',
            'role.required' => 'Vui lòng chọn vai trò cho người dùng.',
            'role.in' => 'Vai trò không hợp lệ.',
        ];
    }
}
