<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentStatusRequest extends FormRequest
{
    /**
     * Xác thực quyền quản trị viên cập nhật trạng thái thanh toán.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'admin';
    }

    /**
     * Quy tắc kiểm tra trạng thái cập nhật thanh toán.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,completed,failed,refunded'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái thanh toán.',
            'status.in' => 'Trạng thái thanh toán không hợp lệ.',
        ];
    }
}
