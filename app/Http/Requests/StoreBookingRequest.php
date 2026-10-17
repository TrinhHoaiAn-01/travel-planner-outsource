<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Xác định người dùng có quyền thực hiện request này hay không.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Các quy tắc validation khi tạo đặt phòng mới.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_type_id'   => ['required', 'exists:room_types,id'],
            'trip_id'        => ['nullable', 'exists:trips,id'],
            'check_in_date'  => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'room_count'     => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt theo quy chuẩn dự án.
     */
    public function messages(): array
    {
        return [
            'room_type_id.required'        => 'Vui lòng chọn loại phòng muốn đặt.',
            'room_type_id.exists'          => 'Loại phòng đã chọn không tồn tại trên hệ thống.',
            'trip_id.exists'               => 'Chuyến đi đã chọn không hợp lệ.',
            'check_in_date.required'       => 'Vui lòng chọn ngày nhận phòng.',
            'check_in_date.after_or_equal' => 'Ngày nhận phòng không được ở trong quá khứ.',
            'check_out_date.required'      => 'Vui lòng chọn ngày trả phòng.',
            'check_out_date.after'         => 'Ngày trả phòng phải sau ngày nhận phòng ít nhất 1 ngày.',
            'room_count.required'          => 'Vui lòng nhập số lượng phòng muốn đặt.',
            'room_count.integer'           => 'Số lượng phòng phải là số nguyên.',
            'room_count.min'               => 'Số lượng phòng đặt tối thiểu là 1 phòng.',
            'room_count.max'               => 'Số lượng phòng đặt tối đa trong một lần là 10 phòng.',
        ];
    }
}
