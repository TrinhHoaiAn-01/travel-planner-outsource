<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreDestinationRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng thực hiện yêu cầu.
     * Chỉ quản trị viên (Admin) mới có quyền tạo mới địa điểm du lịch (BR-13, BR-20).
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi thêm địa điểm du lịch mới.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:destinations,slug'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'address' => ['nullable', 'string', 'max:500'],
            'entrance_fee' => ['nullable', 'numeric', 'min:0'],
            'rating' => ['nullable', 'numeric', 'between:0,5'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'has_hotel' => ['nullable', 'boolean'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, chuẩn mực theo BR-04 và Chương 5.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên địa điểm du lịch.',
            'name.string' => 'Tên địa điểm du lịch phải là chuỗi ký tự hợp lệ.',
            'name.max' => 'Tên địa điểm không được vượt quá 255 ký tự.',
            'slug.unique' => 'Đường dẫn định danh (slug) này đã tồn tại trên hệ thống.',
            'city_id.required' => 'Vui lòng chọn tỉnh hoặc thành phố trực thuộc.',
            'city_id.exists' => 'Tỉnh hoặc thành phố đã chọn không tồn tại trong hệ thống (BR-15).',
            'category_id.required' => 'Vui lòng chọn danh mục du lịch phù hợp.',
            'category_id.exists' => 'Danh mục du lịch đã chọn không tồn tại trong hệ thống (BR-15).',
            'description.max' => 'Mô tả địa điểm không được vượt quá 5000 ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',
            'entrance_fee.numeric' => 'Giá vé / giá phòng phải là số hợp lệ.',
            'entrance_fee.min' => 'Giá vé / giá phòng không được nhỏ hơn 0đ.',
            'rating.numeric' => 'Điểm đánh giá phải là chữ số.',
            'rating.between' => 'Điểm đánh giá phải nằm trong khoảng từ 0.0 đến 5.0 sao (BR-08).',
            'opening_time.date_format' => 'Giờ mở cửa phải đúng định dạng Giờ:Phút (HH:mm).',
            'closing_time.date_format' => 'Giờ đóng cửa phải đúng định dạng Giờ:Phút (HH:mm).',
            'duration.integer' => 'Thời lượng tham quan phải là số phút nguyên dương.',
            'duration.min' => 'Thời lượng tham quan không được nhỏ hơn 0 phút.',
            'image_file.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'image_file.mimes' => 'Hình ảnh chỉ chấp nhận các định dạng: jpeg, png, jpg, webp, gif.',
            'image_file.max' => 'Kích thước tệp hình ảnh không được vượt quá 2MB.',
            'image_url.max' => 'Đường dẫn hình ảnh không được vượt quá 2048 ký tự.',
        ];
    }
}
