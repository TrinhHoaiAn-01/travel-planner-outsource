<?php

namespace App\Http\Requests;

use App\Models\Trip;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreItineraryItemRequest extends FormRequest
{
    /**
     * Xác thực quyền thêm hoạt động vào chuyến đi.
     * Quy tắc BR-01, BR-03: Người dùng phải là chủ sở hữu chuyến đi (Owner).
     */
    public function authorize(): bool
    {
        $trip = $this->route('trip');
        if (!$trip instanceof Trip) {
            $trip = Trip::find($trip);
        }

        return Auth::check() && $trip && $trip->user_id === Auth::id();
    }

    /**
     * Quy tắc kiểm tra dữ liệu đầu vào theo Bảng 22, Bảng 26 & Hình 19 trong Báo cáo Đồ án.
     */
    public function rules(): array
    {
        return [
            'activity_name' => ['required', 'string', 'max:255'],
            'day_number' => ['required', 'integer', 'min:1'],
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'activity_type' => ['nullable', 'string', 'max:100'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt thân thiện, rõ ràng.
     */
    public function messages(): array
    {
        return [
            'activity_name.required' => 'Vui lòng nhập tên hoạt động.',
            'activity_name.max' => 'Tên hoạt động không được vượt quá 255 ký tự.',
            'day_number.required' => 'Vui lòng chọn ngày thực hiện hoạt động trong chuyến đi.',
            'day_number.min' => 'Ngày thực hiện hoạt động phải từ Ngày 1 trở lên.',
            'destination_id.exists' => 'Địa điểm được chọn không tồn tại trong hệ thống.',
            'start_time.date_format' => 'Giờ bắt đầu không đúng định dạng HH:mm.',
            'end_time.date_format' => 'Giờ kết thúc không đúng định dạng HH:mm.',
            'end_time.after' => 'Giờ kết thúc phải lớn hơn giờ bắt đầu.',
            'estimated_cost.numeric' => 'Chi phí dự kiến phải là một số hợp lệ.',
            'estimated_cost.min' => 'Chi phí dự kiến không được nhỏ hơn 0 đ.',
            'note.max' => 'Ghi chú thêm không được vượt quá 1000 ký tự.',
        ];
    }
}
