<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền tải lên ảnh đại diện hay không.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Quy tắc kiểm tra tính hợp lệ của tệp ảnh tải lên.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
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
            'avatar.required' => 'Vui lòng chọn tệp ảnh đại diện cần tải lên.',
            'avatar.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'avatar.mimes' => 'Ảnh đại diện chỉ chấp nhận định dạng jpeg, png, jpg hoặc webp.',
            'avatar.max' => 'Dung lượng ảnh đại diện không được vượt quá 2MB.',
        ];
    }
}
