<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromotionRequest extends FormRequest
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
        $promotionId = $this->route('promotion')->id ?? null;
        $promotion = $this->route('promotion');
        $usedCount = $promotion ? $promotion->used_count : 0;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('promotions', 'code')->ignore($promotionId),
            ],
            'value' => 'required|numeric|min:0',
            'type' => 'required|in:%,VND',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'usage_limit' => [
                'required',
                'integer',
                'min:1',
            ],
            'max_discount_amount' => 'nullable|numeric|min:0',
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
            'end_at.required' => 'Ngày kết thúc là bắt buộc.',
            'end_at.date' => 'Ngày kết thúc không đúng định dạng.',
            'end_at.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'usage_limit.required' => 'Giới hạn sử dụng là bắt buộc.',
            'usage_limit.integer' => 'Giới hạn sử dụng phải là số nguyên.',
            'usage_limit.min' => 'Giới hạn sử dụng phải lớn hơn 0 và không được nhỏ hơn số lần đã sử dụng.',
            'max_discount_amount.numeric' => 'Số tiền giảm tối đa phải là số.',
            'max_discount_amount.min' => 'Số tiền giảm tối đa phải lớn hơn hoặc bằng 0.',
        ];
    }

    /**
     * Chuẩn hóa dữ liệu trước khi validate
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->input('code'))),
            ]);
        }
    }

    /**
     * Bổ sung ràng buộc phụ thuộc và kiểm tra không giảm usage_limit dưới used_count
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $type = $this->input('type');
            $value = (float) $this->input('value');
            $cap = $this->input('max_discount_amount');

            if ($type === '%') {
                if ($value <= 0 || $value > 100) {
                    $v->errors()->add('value', 'Giá trị phần trăm phải trong khoảng 1 đến 100.');
                }
                if ($cap === null || $cap === '' || (float) $cap <= 0) {
                    $v->errors()->add('max_discount_amount', 'Vui lòng nhập số tiền giảm tối đa khi giảm theo phần trăm.');
                }
            }

            if ($type === 'VND') {
                if ($value < 1000) {
                    $v->errors()->add('value', 'Giá trị tiền mặt tối thiểu là 1.000₫.');
                }
            }

            $promotion = $this->route('promotion');
            if ($promotion) {
                $usageLimit = (int) $this->input('usage_limit');
                if ($usageLimit < (int) $promotion->used_count) {
                    $v->errors()->add('usage_limit', 'Giới hạn sử dụng không thể nhỏ hơn số lần đã sử dụng (' . (int) $promotion->used_count . ').');
                }
            }
        });
    }
}

