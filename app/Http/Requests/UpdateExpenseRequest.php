<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateExpenseRequest extends FormRequest
{
    /**
     * Xác thực người dùng có quyền chỉnh sửa khoản chi hay không.
     * Người dùng bắt buộc phải đăng nhập theo quy tắc BR-01.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Khai báo các quy tắc kiểm tra dữ liệu đầu vào khi cập nhật khoản chi.
     * Tuân thủ yêu cầu chức năng FR24 và quy tắc nghiệp vụ BR-04, BR-19.
     */
    public function rules(): array
    {
        return [
            'title' => ['required_without:description', 'nullable', 'string', 'max:255'],
            'description' => ['required_without:title', 'nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'expense_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Thông báo lỗi xác thực bằng tiếng Việt rõ ràng, thân thiện.
     */
    public function messages(): array
    {
        return [
            'title.required_without' => 'Vui lòng nhập tên khoản chi.',
            'title.string' => 'Tên khoản chi phải là chuỗi ký tự hợp lệ.',
            'title.max' => 'Tên khoản chi không được vượt quá 255 ký tự.',
            'description.required_without' => 'Vui lòng nhập tên khoản chi hoặc mô tả.',
            'description.string' => 'Mô tả khoản chi phải là chuỗi ký tự hợp lệ.',
            'description.max' => 'Mô tả khoản chi không được vượt quá 255 ký tự.',
            'category.required' => 'Vui lòng chọn danh mục cho khoản chi.',
            'category.string' => 'Danh mục chi phí phải là chuỗi ký tự hợp lệ.',
            'category.max' => 'Danh mục chi phí không được vượt quá 50 ký tự.',
            'amount.required' => 'Vui lòng nhập số tiền chi tiêu.',
            'amount.numeric' => 'Số tiền chi tiêu phải là một số hợp lệ.',
            'amount.gt' => 'Số tiền chi tiêu bắt buộc phải lớn hơn 0 VNĐ.',
            'amount.max' => 'Số tiền chi tiêu không được vượt quá 9.999.999.999 VNĐ (tối đa 10 tỷ).',
            'expense_date.date' => 'Ngày chi không đúng định dạng ngày tháng.',
            'note.string' => 'Ghi chú chi phí phải là chuỗi ký tự hợp lệ.',
            'note.max' => 'Ghi chú chi phí không được vượt quá 1000 ký tự.',
        ];
    }
}
