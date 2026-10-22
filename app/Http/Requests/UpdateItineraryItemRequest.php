<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request xác thực dữ liệu khi chỉnh sửa hoạt động trong lịch trình.
 * Tuân thủ quy tắc nghiệp vụ Bảng 22, Bảng 26 và Hình 20 (Chức năng 7).
 */
class UpdateItineraryItemRequest extends FormRequest
{
    /**
     * Xác định xem người dùng hiện tại có quyền thực hiện yêu cầu này không.
     * Kiểm tra chống lỗ hổng bảo mật IDOR theo quy tắc BR-01.
     */
    public function authorize(): bool
    {
        $itinerary = $this->route('itinerary');

        if (!$itinerary || !$itinerary->trip) {
            return false;
        }

        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Chỉ chủ sở hữu chuyến đi (Owner) mới có quyền chỉnh sửa mục lịch trình
        return (int) $user->id === (int) $itinerary->trip->user_id;
    }

    /**
     * Quy tắc kiểm tra dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'activity_name' => ['required', 'string', 'max:255'],
            'day_number' => ['nullable', 'integer', 'min:1'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'note' => ['nullable', 'string', 'max:1000'],
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Tùy biến thông báo lỗi bằng tiếng Việt chuẩn theo tài liệu đồ án.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'activity_name.required' => 'Vui lòng nhập tên hoạt động hoặc chọn địa điểm.',
            'activity_name.max' => 'Tên hoạt động không được vượt quá 255 ký tự.',
            'day_number.min' => 'Ngày thực hiện không hợp lệ.',
            'start_time.required' => 'Vui lòng chọn giờ bắt đầu.',
            'start_time.date_format' => 'Định dạng giờ bắt đầu không hợp lệ (HH:mm).',
            'end_time.required' => 'Vui lòng chọn giờ kết thúc.',
            'end_time.date_format' => 'Định dạng giờ kết thúc không hợp lệ (HH:mm).',
            'end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
            'note.max' => 'Ghi chú vượt quá số ký tự cho phép (tối đa 1000 ký tự).',
            'destination_id.exists' => 'Địa điểm được chọn không tồn tại trong hệ thống.',
            'estimated_cost.numeric' => 'Chi phí dự kiến phải là số hợp lệ.',
            'estimated_cost.min' => 'Chi phí dự kiến không được là số âm.',
        ];
    }
}
