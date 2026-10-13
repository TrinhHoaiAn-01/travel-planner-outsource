<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDestinationImageRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng thực hiện yêu cầu.
     * Chỉ quản trị viên (Admin) mới có quyền tải lên hình ảnh vào thư viện (BR-13, BR-20).
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Khai báo quy tắc kiểm tra dữ liệu đầu vào khi tải lên hình ảnh mới.
     */
    public function rules(): array
    {
        return [
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, chuẩn mực.
     */
    public function messages(): array
    {
        return [
            'destination_id.required' => 'Vui lòng chọn địa điểm du lịch cần gán hình ảnh.',
            'destination_id.exists' => 'Địa điểm du lịch được chọn không tồn tại trong hệ thống.',
            'image_file.image' => 'Tệp tải lên phải là định dạng hình ảnh hợp lệ.',
            'image_file.mimes' => 'Hình ảnh chỉ chấp nhận các định dạng: jpeg, png, jpg, webp, gif.',
            'image_file.max' => 'Kích thước tệp hình ảnh không được vượt quá 2MB.',
            'image_url.max' => 'Đường dẫn hình ảnh không được vượt quá 2048 ký tự.',
        ];
    }
}
