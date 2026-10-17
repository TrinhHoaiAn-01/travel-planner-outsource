<?php

namespace App\Http\Requests;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTripNotesRequest extends FormRequest
{
    /**
     * Xác thực người dùng có quyền cập nhật mô tả và ghi chú chuyến đi hay không.
     * Chống lỗi IDOR theo quy tắc BR-01 và BR-03.
     */
    public function authorize(): bool
    {
        $trip = $this->route('trip');

        if (is_numeric($trip)) {
            $trip = Trip::find($trip);
        }

        return $trip instanceof Trip && $this->user()?->can('update', $trip);
    }

    /**
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi cập nhật mô tả và ghi chú.
     */
    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:3000'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, thân thiện.
     */
    public function messages(): array
    {
        return [
            'description.string' => 'Nội dung mô tả và ghi chú phải là chuỗi ký tự hợp lệ.',
            'description.max' => 'Nội dung mô tả và ghi chú không được vượt quá 3000 ký tự.',
        ];
    }
}
