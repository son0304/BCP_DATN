<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->role->name === 'venue_owner';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = Auth::user();
        
        return [
            'venue_id' => [
                'nullable',
                'exists:venues,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        $venue = \App\Models\Venue::where('id', $value)
                            ->where('owner_id', $user->id)
                            ->first();
                        if (!$venue) {
                            $fail('Bạn không có quyền thêm sản phẩm cho thương hiệu này.');
                        }
                    }
                }
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'category_id' => [
                'nullable',
                'exists:product_categories,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value) {
                        $category = \App\Models\ProductCategory::where('id', $value)
                            ->where('owner_id', $user->id)
                            ->first();
                        if (!$category) {
                            $fail('Danh mục không tồn tại hoặc không thuộc về bạn.');
                        }
                    }
                }
            ],
            'image_url' => ['nullable', 'url', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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
            'name.required' => 'Tên sản phẩm không được bỏ trống.',
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'sku.string' => 'Mã SKU phải là chuỗi ký tự.',
            'sku.max' => 'Mã SKU không được vượt quá 100 ký tự.',
            'sku.unique' => 'Mã SKU này đã tồn tại.',
            'price.required' => 'Giá sản phẩm không được bỏ trống.',
            'price.numeric' => 'Giá sản phẩm phải là số.',
            'price.min' => 'Giá sản phẩm phải lớn hơn hoặc bằng 0.',
            'cost_price.numeric' => 'Giá vốn phải là số.',
            'cost_price.min' => 'Giá vốn phải lớn hơn hoặc bằng 0.',
            'stock_quantity.required' => 'Số lượng tồn kho không được bỏ trống.',
            'stock_quantity.integer' => 'Số lượng tồn kho phải là số nguyên.',
            'stock_quantity.min' => 'Số lượng tồn kho phải lớn hơn hoặc bằng 0.',
            'min_stock_level.integer' => 'Mức tồn kho tối thiểu phải là số nguyên.',
            'min_stock_level.min' => 'Mức tồn kho tối thiểu phải lớn hơn hoặc bằng 0.',
            'unit.string' => 'Đơn vị phải là chuỗi ký tự.',
            'unit.max' => 'Đơn vị không được vượt quá 50 ký tự.',
            'venue_id.exists' => 'Thương hiệu không tồn tại.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'image_url.url' => 'URL hình ảnh không hợp lệ.',
            'image_url.max' => 'URL hình ảnh không được vượt quá 500 ký tự.',
            'is_active.boolean' => 'Trạng thái hoạt động không hợp lệ.',
            'is_featured.boolean' => 'Trạng thái nổi bật không hợp lệ.',
            'sort_order.integer' => 'Thứ tự sắp xếp phải là số nguyên.',
            'sort_order.min' => 'Thứ tự sắp xếp phải lớn hơn hoặc bằng 0.',
        ];
    }
}
