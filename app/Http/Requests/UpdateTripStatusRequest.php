<?php

namespace App\Http\Requests;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTripStatusRequest extends FormRequest
{
    /**
     * Xác thực người dùng có quyền cập nhật trạng thái chuyến đi này hay không.
     * Kiểm tra quyền sở hữu chuyến đi chống lỗi IDOR theo quy tắc BR-01 và BR-03.
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
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi cập nhật trạng thái chuyến đi.
     * Hỗ trợ các trạng thái: draft (nháp), planned (đã lập kế hoạch), ongoing (đang diễn ra), completed (hoàn thành), cancelled (đã hủy).
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:draft,planned,ongoing,completed,cancelled',
            ],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt ngắn gọn, dễ hiểu.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái chuyến đi cần cập nhật.',
            'status.string' => 'Trạng thái chuyến đi phải là chuỗi ký tự hợp lệ.',
            'status.in' => 'Trạng thái chuyến đi không hợp lệ. Chỉ chấp nhận: draft, planned, ongoing, completed hoặc cancelled.',
        ];
    }
}
