<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Lớp Form Request xác thực dữ liệu tìm kiếm Trang chủ (Home Search Request).
 * Đóng vai trò kiểm tra tính hợp lệ của tất cả các tham số do người dùng gửi lên từ form tìm kiếm
 * trước khi chuyển dữ liệu vào Controller và Service xử lý.
 * Giúp bảo vệ hệ thống khỏi các dữ liệu không hợp lệ hoặc tấn công tiêm nhiễm (Injection).
 */
class HomeSearchRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền thực hiện yêu cầu này hay không.
     * Trang chủ là trang công khai nên luôn cho phép tất cả mọi người (true).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Định nghĩa các quy tắc kiểm tra (Validation Rules) cho từng tham số tìm kiếm.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Từ khóa tìm kiếm: chuỗi ký tự, tối đa 200 ký tự
            'keyword' => ['nullable', 'string', 'max:200'],

            // Danh mục địa điểm: số nguyên dương, phải tồn tại trong bảng categories
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],

            // Thành phố: số nguyên dương, phải tồn tại trong bảng cities
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],

            // Mức ngân sách: chỉ chấp nhận 1 (<500k), 2 (500k-1.5tr), hoặc 3 (>1.5tr)
            'budget' => ['nullable', 'integer', 'in:1,2,3'],

            // Công cụ tìm kiếm: chỉ chấp nhận 'solr' hoặc 'sql'
            'engine' => ['nullable', 'string', 'in:solr,sql'],

            // Lọc nơi lưu trú (khách sạn, homestay, resort có phòng)
            'is_lodging' => ['nullable', 'boolean'],

            // Định dạng phản hồi khi kiểm thử qua Postman
            'format' => ['nullable', 'string', 'in:json,html'],
        ];
    }

    /**
     * Tùy biến thông báo lỗi bằng Tiếng Việt thân thiện với người dùng.
     */
    public function messages(): array
    {
        return [
            'keyword.max' => 'Từ khóa tìm kiếm không được vượt quá 200 ký tự.',
            'category_id.exists' => 'Danh mục du lịch được chọn không tồn tại trong hệ thống.',
            'city_id.exists' => 'Thành phố được chọn không tồn tại trong hệ thống.',
            'budget.in' => 'Mức ngân sách lựa chọn không hợp lệ (chỉ chấp nhận mức 1, 2 hoặc 3).',
            'engine.in' => 'Công cụ tìm kiếm không hợp lệ (chỉ chấp nhận solr hoặc sql).',
        ];
    }
}

