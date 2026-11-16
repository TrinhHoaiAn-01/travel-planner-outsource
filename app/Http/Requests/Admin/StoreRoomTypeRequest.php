<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRoomTypeRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng thực hiện yêu cầu.
     * Chỉ quản trị viên (Admin) mới có quyền tạo mới loại phòng lưu trú (BR-13, BR-20).
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi thêm loại phòng mới.
     */
    public function rules(): array
    {
        return [
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0', 'max:1000'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, chuẩn mực.
     */
    public function messages(): array
    {
        return [
            'destination_id.required' => 'Vui lòng chọn địa điểm du lịch cho loại phòng.',
            'destination_id.exists' => 'Địa điểm du lịch được chọn không tồn tại trên hệ thống.',
            'name.required' => 'Vui lòng nhập tên loại phòng.',
            'name.string' => 'Tên loại phòng phải là chuỗi ký tự hợp lệ.',
            'name.max' => 'Tên loại phòng không được vượt quá 255 ký tự.',
            'capacity.required' => 'Vui lòng nhập sức chứa người lớn.',
            'capacity.integer' => 'Sức chứa phải là số nguyên hợp lệ.',
            'capacity.min' => 'Sức chứa tối thiểu là 1 người.',
            'capacity.max' => 'Sức chứa tối đa là 50 người.',
            'price_per_night.required' => 'Vui lòng nhập giá phòng mỗi đêm.',
            'price_per_night.numeric' => 'Giá phòng mỗi đêm phải là số hợp lệ.',
            'price_per_night.min' => 'Giá phòng không được nhỏ hơn 0đ.',
            'quantity.required' => 'Vui lòng nhập số lượng phòng sẵn có.',
            'quantity.integer' => 'Số lượng phòng phải là số nguyên hợp lệ.',
            'quantity.min' => 'Số lượng phòng không được nhỏ hơn 0.',
            'description.max' => 'Mô tả không được vượt quá 2000 ký tự.',
        ];
    }
}

