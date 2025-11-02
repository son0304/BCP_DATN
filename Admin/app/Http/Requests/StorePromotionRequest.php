<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:promotions,code',
            'value' => 'required|numeric|min:0',
            'type' => 'required|in:%,VND',
            'start_at' => 'required|date|after_or_equal:today',
            'end_at' => 'required|date|after:start_at',
            'usage_limit' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Mã voucher là bắt buộc.',
            'code.max' => 'Mã voucher không được vượt quá 50 ký tự.',
            'code.unique' => 'Mã voucher này đã tồn tại.',
            'value.required' => 'Giá trị voucher là bắt buộc.',
            'value.numeric' => 'Giá trị voucher phải là số.',
            'value.min' => 'Giá trị voucher phải lớn hơn hoặc bằng 0.',
            'type.required' => 'Loại voucher là bắt buộc.',
            'type.in' => 'Loại voucher không hợp lệ.',
            'start_at.required' => 'Ngày bắt đầu là bắt buộc.',
            'start_at.date' => 'Ngày bắt đầu không đúng định dạng.',
            'start_at.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'end_at.required' => 'Ngày kết thúc là bắt buộc.',
            'end_at.date' => 'Ngày kết thúc không đúng định dạng.',
            'end_at.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'usage_limit.integer' => 'Giới hạn sử dụng phải là số nguyên.',
            'usage_limit.min' => 'Giới hạn sử dụng phải lớn hơn hoặc bằng 0.',
        ];
    }
}

