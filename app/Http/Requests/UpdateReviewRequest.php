<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Form Request xác thực dữ liệu khi người dùng chỉnh sửa đánh giá & nhận xét của mình.
 */
class UpdateReviewRequest extends FormRequest
{
    /**
     * Xác thực quyền truy cập: Người dùng phải đăng nhập và có quyền sửa review (chính chủ hoặc Admin).
     */
    public function authorize(): bool
    {
        $review = $this->route('review');

        return Auth::check() && $review && $this->user()->can('update', $review);
    }

    /**
     * Quy tắc kiểm tra tính hợp lệ của dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Số sao đánh giá từ 1 đến 5 sao
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            // Nội dung nhận xét tối đa 500 ký tự
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Thông báo lỗi xác thực 100% bằng tiếng Việt thân thiện, rõ ràng.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá phải là số nguyên hợp lệ.',
            'rating.min' => 'Số sao đánh giá tối thiểu là 1 sao.',
            'rating.max' => 'Số sao đánh giá tối đa là 5 sao.',
            'comment.string' => 'Nội dung nhận xét phải là chuỗi văn bản hợp lệ.',
            'comment.max' => 'Nội dung đánh giá không được vượt quá 500 ký tự.',
        ];
    }
}
