<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTripRequest extends FormRequest
{
    /**
     * Xác thực người dùng có quyền thực hiện yêu cầu này hay không.
     * Người dùng bắt buộc phải đăng nhập theo quy tắc BR-01.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi tạo chuyến đi mới.
     * Tuân thủ quy tắc bảo mật Chương 5 và quy tắc nghiệp vụ BR-01, BR-03.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'destination_area' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'description' => ['nullable', 'string', 'max:3000'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, thân thiện.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên chuyến đi.',
            'name.string' => 'Tên chuyến đi phải là chuỗi ký tự hợp lệ.',
            'name.max' => 'Tên chuyến đi không được vượt quá 255 ký tự.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu chuyến đi.',
            'start_date.date' => 'Ngày bắt đầu không đúng định dạng ngày tháng.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc chuyến đi.',
            'end_date.date' => 'Ngày kết thúc không đúng định dạng ngày tháng.',
            'end_date.after_or_equal' => 'Ngày kết thúc không được trước ngày bắt đầu.',
            'budget.numeric' => 'Tổng ngân sách dự kiến phải là một số hợp lệ.',
            'budget.min' => 'Tổng ngân sách dự kiến không được nhỏ hơn 0 VNĐ.',
            'destination_area.max' => 'Địa bàn trọng tâm không được vượt quá 255 ký tự.',
            'cover_image.max' => 'Đường dẫn ảnh bìa không được vượt quá 2048 ký tự.',
            'description.max' => 'Mô tả và ghi chú chuyến đi không được vượt quá 3000 ký tự.',
        ];
    }
}
