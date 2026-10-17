<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateBudgetRequest extends FormRequest
{
    /**
     * Xác thực người dùng có quyền cập nhật ngân sách hay không.
     * Người dùng bắt buộc phải đăng nhập theo quy tắc BR-01.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi thiết lập / cập nhật ngân sách Trip.
     * Tuân thủ yêu cầu chức năng FR22 và quy tắc nghiệp vụ BR-19.
     */
    public function rules(): array
    {
        return [
            'budget' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, thân thiện.
     */
    public function messages(): array
    {
        return [
            'budget.required' => 'Vui lòng nhập tổng ngân sách dự kiến cho chuyến đi.',
            'budget.numeric' => 'Tổng ngân sách dự kiến phải là một số hợp lệ.',
            'budget.min' => 'Tổng ngân sách dự kiến không được nhỏ hơn 0 VNĐ.',
            'budget.max' => 'Tổng ngân sách dự kiến không được vượt quá 9.999.999.999 VNĐ (tối đa 10 tỷ).',
        ];
    }
}
