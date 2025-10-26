<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới
     *
     * @param RegisterRequest $request - Dữ liệu đăng ký từ client (đã được validate)
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        try {
            // Dữ liệu đã được validate bởi RegisterRequest

            // Tìm role mặc định cho user (thường là role_id = 2 cho customer)
            $defaultRole = Role::where('name', 'customer')->first();
            if (!$defaultRole) {
                // Nếu không có role customer, lấy role đầu tiên
                $defaultRole = Role::first();
            }

            if (!$defaultRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy role mặc định trong hệ thống'
                ], 500);
            }

            // Tạo user mới
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Mã hóa mật khẩu
                'role_id' => $defaultRole->id, // Gán role mặc định
                'phone' => $request->phone,
                'district_id' => $request->district_id,
                'province_id' => $request->province_id,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'is_active' => true // Mặc định active
            ]);

            // Load thông tin role và địa chỉ
            $user->load(['role', 'district', 'province']);

            // Trả về thông tin user đã tạo (không bao gồm password)
            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tài khoản thành công',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => $user->role,
                        'district' => $user->district,
                        'province' => $user->province,
                        'lat' => $user->lat,
                        'lng' => $user->lng,
                        'is_active' => $user->is_active,
                        'created_at' => $user->created_at
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            // Xử lý lỗi server
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình đăng ký',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
