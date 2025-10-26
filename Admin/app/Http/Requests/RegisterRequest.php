<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Xác định xem user có quyền thực hiện request này không
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Cho phép tất cả user đăng ký
    }

    /**
     * Định nghĩa các rules validation cho đăng ký
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone' => 'nullable|string|max:20',
            'district_id' => 'nullable|exists:districts,id',
            'province_id' => 'nullable|exists:provinces,id',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180'
        ];
    }

    /**
     * Định nghĩa các message lỗi tùy chỉnh
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên là bắt buộc',
            'name.string' => 'Tên phải là chuỗi ký tự',
            'name.max' => 'Tên không được quá 255 ký tự',

            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email đã tồn tại trong hệ thống',
            'email.max' => 'Email không được quá 255 ký tự',

            'password.required' => 'Mật khẩu là bắt buộc',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',

            'phone.string' => 'Số điện thoại phải là chuỗi ký tự',
            'phone.max' => 'Số điện thoại không được quá 20 ký tự',

            'district_id.exists' => 'Quận/huyện không tồn tại',
            'province_id.exists' => 'Tỉnh/thành phố không tồn tại',

            'lat.numeric' => 'Vĩ độ phải là số',
            'lat.between' => 'Vĩ độ phải trong khoảng -90 đến 90',

            'lng.numeric' => 'Kinh độ phải là số',
            'lng.between' => 'Kinh độ phải trong khoảng -180 đến 180'
        ];
    }

    /**
     * Định nghĩa các attributes tùy chỉnh cho validation
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'tên',
            'email' => 'email',
            'password' => 'mật khẩu',
            'phone' => 'số điện thoại',
            'district_id' => 'quận/huyện',
            'province_id' => 'tỉnh/thành phố',
            'lat' => 'vĩ độ',
            'lng' => 'kinh độ'
        ];
    }
}
