<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
// Thêm 2 dòng này để xử lý Exception của JWT
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthApiController extends Controller
{
    public function register(Request $request)
    {
        // --- ĐÃ SỬA: Lỗi validation 'require' và 'number' ---
        // --- ĐÃ SỬA: Đồng bộ tên cột 'province_id' và 'district_id' ---
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|numeric|exists:provinces,id',
            'district_id' => 'required|numeric|exists:districts,id',
        ], [
            'email.unique' => 'Email này đã được sử dụng.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $defaultRole = Role::firstOrCreate(
                ['name' => 'User'],
                ['description' => 'Standard user role']
            );

            $verificationToken = Str::random(60);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'province_id' => $request->province_id, // <-- Đã sửa
                'district_id' => $request->district_id, // <-- Đã sửa
                'role_id' => $defaultRole->id,
                'is_active' => false,
                'is_email_verified' => false,
                'email_verification_token' => $verificationToken,
            ]);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $verificationUrl = $frontendUrl . '/verify-email?token=' . $verificationToken;

            Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công! Vui lòng kiểm tra email để xác nhận tài khoản.',
                'data' => ['user_id' => $user->id, 'email' => $user->email]
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi đăng ký user: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Đã có lỗi xảy ra, vui lòng thử lại.'], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.', 'errors' => $validator->errors()], 422);
        }

        // 2. Tìm người dùng bằng email
        $user = User::where('email', $request->email)->with(['role', 'district', 'province'])->first();

        // 3. Kiểm tra mật khẩu
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác.'], 401);
        }

        // 4. Kiểm tra email đã xác nhận chưa
        if (!$user->is_email_verified) {
            return response()->json(['success' => false, 'message' => 'Vui lòng xác nhận email trước khi đăng nhập.', 'errors' => ['email' => 'Email chưa được xác nhận.']], 403);
        }

        // 5. Kiểm tra tài khoản có bị khóa không
        if (!$user->is_active) {
            return response()->json(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa hoặc vô hiệu hóa.', 'errors' => ['account' => 'Tài khoản bị vô hiệu hóa.']], 403);
        }

        // 6. Mọi thứ đều hợp lệ, tạo token
        $token = JWTAuth::fromUser($user);
        $expiresIn = JWTAuth::factory()->getTTL() * 60; // (Tính bằng giây)

        // 7. Trả về JSON
        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_id' => $user->role->id ?? 2,
                    'phone' => $user->phone,
                    'avt' => $user->avt ?? null,
                    // --- ĐÃ SỬA: Sửa tên quan hệ (singular) ---
                    'district' => $user->district->name ?? null,
                    'province' => $user->province->name ?? null,
                    'is_active' => $user->is_active,
                ]
            ]
        ]);
    }

    /**
     * Đăng xuất (API - JWTAuth)
     */
    public function logout(Request $request)
    {
        // --- ĐÃ SỬA: Viết lại hàm logout cho JWTAuth ---
        try {
            // Yêu cầu token hiện tại phải bị vô hiệu hóa (thêm vào blacklist)
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công!'
            ]);
        } catch (TokenInvalidException $e) {
            return response()->json(['success' => false, 'message' => 'Token không hợp lệ.'], 401);
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy token.'], 401);
        }
    }

    /**
     * Xử lý xác nhận email từ link (API)
     * Luồng này đã đúng (React GỌI POST, API xử lý POST)
     */
    public function verifyEmail(Request $request)
    {
        if (!$request->has('token') || empty($request->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu token xác nhận email!'
            ], 400);
        }

        try {
            $token = $request->token;
            $user = User::where('email_verification_token', $token)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token không hợp lệ hoặc đã hết hạn.'
                ], 400);
            }

            if ($user->is_email_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email này đã được xác nhận trước đó!'
                ], 400);
            }

            $user->update([
                'is_email_verified' => true,
                'is_active' => true,
                'email_verification_token' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email đã được xác thực thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi xác nhận email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi trong quá trình xác nhận.'
            ], 500);
        }
    }
}