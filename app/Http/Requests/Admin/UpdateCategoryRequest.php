<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng thực hiện yêu cầu.
     * Chỉ quản trị viên (Admin) mới có quyền cập nhật danh mục du lịch (BR-13, BR-20).
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi chỉnh sửa danh mục.
     */
    public function rules(): array
    {
        $categoryParam = $this->route('category');
        $categoryId = null;

        if ($categoryParam instanceof Category) {
            $categoryId = $categoryParam->id;
        } elseif (is_numeric($categoryParam)) {
            $categoryId = (int) $categoryParam;
        } elseif (is_string($categoryParam)) {
            $found = Category::where('slug', $categoryParam)->first();
            $categoryId = $found ? $found->id : null;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                $categoryId ? Rule::unique('categories', 'slug')->ignore($categoryId) : 'unique:categories,slug',
            ],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, chuẩn mực.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.string' => 'Tên danh mục phải là chuỗi ký tự hợp lệ.',
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự.',
            'slug.string' => 'Đường dẫn tĩnh (Slug) phải là chuỗi ký tự hợp lệ.',
            'slug.max' => 'Đường dẫn tĩnh không được vượt quá 255 ký tự.',
            'slug.unique' => 'Đường dẫn tĩnh (Slug) này đã tồn tại trên hệ thống.',
            'icon.max' => 'Tên biểu tượng không được vượt quá 50 ký tự.',
            'description.max' => 'Mô tả không được vượt quá 2000 ký tự.',
        ];
    }
}
