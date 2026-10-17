<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCityRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng thực hiện yêu cầu.
     * Chỉ quản trị viên (Admin) mới có quyền tạo mới tỉnh / thành phố (BR-13, BR-20).
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi thêm tỉnh / thành phố mới.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:cities,code'],
            'region' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, chuẩn mực.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên tỉnh hoặc thành phố.',
            'name.string' => 'Tên tỉnh hoặc thành phố phải là chuỗi ký tự hợp lệ.',
            'name.max' => 'Tên tỉnh hoặc thành phố không được vượt quá 255 ký tự.',
            'code.string' => 'Mã định danh thành phố phải là chuỗi ký tự hợp lệ.',
            'code.max' => 'Mã thành phố không được vượt quá 50 ký tự.',
            'code.unique' => 'Mã thành phố này đã tồn tại trên hệ thống.',
            'region.max' => 'Tên khu vực không được vượt quá 50 ký tự.',
            'description.max' => 'Mô tả không được vượt quá 2000 ký tự.',
            'image.max' => 'Đường dẫn hình ảnh không được vượt quá 2048 ký tự.',
            'image_file.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'image_file.mimes' => 'Hình ảnh chỉ chấp nhận các định dạng: jpeg, png, jpg, webp, gif.',
            'image_file.max' => 'Kích thước tệp hình ảnh không được vượt quá 2MB.',
        ];
    }
}
