<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    /**
     * Xác thực quyền gửi yêu cầu thanh toán.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Quy tắc kiểm tra dữ liệu thanh toán.
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', 'in:vnpay,momo,bank_transfer,credit_card,cash'],
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Thông báo lỗi bằng tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in' => 'Phương thức thanh toán được chọn không hợp lệ.',
            'amount.required' => 'Số tiền thanh toán là bắt buộc.',
            'amount.numeric' => 'Số tiền thanh toán phải là định dạng số.',
            'amount.min' => 'Số tiền thanh toán không được nhỏ hơn 0.',
            'note.max' => 'Ghi chú thanh toán không được vượt quá 500 ký tự.',
        ];
    }
}
