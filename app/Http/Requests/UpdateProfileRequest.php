<?php

namespace App\Http\Requests;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền cập nhật hồ sơ hay không.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi kiểm tra tính hợp lệ.
     */
    protected function prepareForValidation(): void
    {
        // Loại bỏ các trường nhạy cảm để chống Parameter Tampering và leo thang đặc quyền
        $this->request->remove('role');
        $this->request->remove('is_active');
        $this->request->remove('id');
        $this->request->remove('deleted_at');
        $this->request->remove('email_verified_at');
        $this->request->remove('password');

        if ($this->has('name')) {
            $this->merge([
                'name' => \App\Support\SecuritySanitizer::formText($this->input('name'), 255),
            ]);
        }
        if ($this->has('email')) {
            $this->merge([
                'email' => \App\Support\FormInput::email($this->input('email')),
            ]);
        }
        if ($this->has('phone')) {
            $this->merge([
                'phone' => \App\Support\SecuritySanitizer::phone($this->input('phone')),
            ]);
        }
        if ($this->has('city')) {
            $this->merge([
                'city' => \App\Support\SecuritySanitizer::formText($this->input('city'), 100),
            ]);
        }
        if ($this->has('bio')) {
            $this->merge([
                'bio' => \App\Support\SecuritySanitizer::formText($this->input('bio'), 1000),
            ]);
        }
    }

    /**
     * Quy tắc kiểm tra tính hợp lệ của dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'edit_version' => ['required', 'string', 'max:36'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => [
                'bail',
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if (! empty($value)) {
                        if (! \App\Support\SecuritySanitizer::vietnameseMobile($value)) {
                            $fail('Số điện thoại không đúng định dạng di động Việt Nam (gồm 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09).');
                        }
                    }
                },
            ],
            'city' => ['bail', 'nullable', 'string', 'max:100', Rule::in(City::profileOptions($this->user()?->city))],
            'avatar_url' => ['bail', 'nullable', 'string', 'max:1000', function ($attribute, $value, $fail) {
                if ($value !== $this->user()?->avatar && ! validator(['url' => $value], ['url' => 'url:http,https'])->passes()) {
                    $fail('Đường dẫn ảnh đại diện phải là URL HTTP hoặc HTTPS hợp lệ.');
                }
            }],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:1000'],
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
            'name.required' => 'Họ và tên là trường bắt buộc.',
            'edit_version.required' => 'Vui lòng tải lại trang trước khi cập nhật.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'email.required' => 'Địa chỉ email là trường bắt buộc.',
            'email.email' => 'Địa chỉ email không đúng định dạng hợp lệ.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng bởi một tài khoản khác.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'city.max' => 'Tên thành phố không được vượt quá 100 ký tự.',
            'city.in' => 'Vui lòng chọn một thành phố trong danh sách.',
            'avatar_url.max' => 'Đường dẫn ảnh đại diện không được vượt quá 1000 ký tự.',
            'avatar.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận định dạng jpeg, png, jpg hoặc webp.',
            'avatar.max' => 'Dung lượng ảnh đại diện không được vượt quá 2MB.',
            'bio.max' => 'Giới thiệu bản thân không được vượt quá 1000 ký tự.',
        ];
    }
}
