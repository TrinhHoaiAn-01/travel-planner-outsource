<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingStatusRequest extends FormRequest
{
    /**
     * Xác thực quyền quản trị viên cập nhật trạng thái đơn đặt phòng.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Quy tắc kiểm tra trạng thái cập nhật.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,confirmed,completed,cancelled'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái đơn đặt phòng.',
            'status.in' => 'Trạng thái đơn đặt phòng không hợp lệ.',
        ];
    }
}
