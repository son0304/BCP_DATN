<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Mail\EmailVerificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Hiển thị trang đăng ký
    public function showRegister()
    {
        return view('auth.register');
    }

    // Xử lý đăng ký
    public function register(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
        ], [
            'name.required' => 'Tên là bắt buộc',
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã được sử dụng',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'phone.required' => 'Số điện thoại là bắt buộc',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Tạo verification token
        $verificationToken = Str::random(60);

        // Tạo user mới với trạng thái chưa xác nhận email
        // Xác định role mặc định là 'User' (tạo nếu chưa tồn tại)
        $defaultRoleId = Role::where('name', 'user')->value('id');
        if (!$defaultRoleId) {
            $role = Role::firstOrCreate(
                ['name' => 'user'],
                ['description' => 'user role']
            );
            $defaultRoleId = $role->id;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role_id' => $defaultRoleId, // Mặc định role 'User'
            'is_active' => false, // Tạm thời không active
            'is_email_verified' => false,
            'email_verification_token' => $verificationToken,
        ]);

        // Gửi email xác nhận
        $verificationUrl = route('verify.email', ['token' => $verificationToken]); // chế biến URL xác nhận :)
        Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

        // KHÔNG đăng nhập tự động, yêu cầu xác nhận email trước
        return redirect()->route('login')
            ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để xác nhận tài khoản.');
    }

    // Hiển thị trang đăng nhập
    public function showLogin()
    {
        return view('auth.login');
    }

    // Xử lý đăng nhập
    public function login(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'password.required' => 'Mật khẩu là bắt buộc',
        ]);

        // 2. Thử đăng nhập
        if (Auth::attempt($credentials, $request->remember)) {

            $user = Auth::user();

            // 3. Kiểm tra xác thực email
            if (!$user->is_email_verified) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Vui lòng xác nhận email trước khi đăng nhập.',
                ])->withInput($request->except('password'));
            }

            // 4. Regenerate Session để bảo mật
            $request->session()->regenerate();

            // ====================================================
            // 5. LOGIC CHUYỂN HƯỚNG THEO QUYỀN (ROLE)
            // ====================================================

            // Mặc định là về trang chủ
            $redirectUrl = route('home.index');

            // Lấy tên Role (Giả sử User có quan hệ belongsTo 'role')
            // Lưu ý: Cần đảm bảo trong Model User có function role()
            $roleName = $user->role->name ?? '';

            if ($roleName === 'admin') {
                $redirectUrl = route('admin.statistics.index');
            } elseif ($roleName === 'venue_owner') {
                $redirectUrl = route('owner.statistics.index');
            }

            // Sử dụng intended:
            // Nếu user cố vào link A -> bị đẩy ra login -> login xong -> tự về lại A.
            // Nếu user chủ động bấm login -> login xong -> về $redirectUrl (Dashboard).
            return redirect()->intended($redirectUrl)
                ->with('success', 'Đăng nhập thành công!');
        }

        // 6. Đăng nhập thất bại
        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->withInput($request->except('password'));
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Đăng xuất thành công!');
    }

    // Xác nhận email
    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Link xác nhận không hợp lệ hoặc đã hết hạn.');
        }

        // Cập nhật trạng thái xác nhận
        $user->update([
            'is_email_verified' => true,
            'email_verified_at' => now(),
            'is_active' => true,
            'email_verification_token' => null,
        ]);

        // Đăng nhập tự động sau khi xác nhận
        Auth::login($user);

        return redirect()->route('home.index')
            ->with('success', 'Email đã được xác nhận thành công!');
    }

    // Gửi lại email xác nhận
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'email.exists' => 'Email không tồn tại trong hệ thống'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->is_email_verified) {
            return redirect()->route('login')
                ->with('info', 'Email đã được xác nhận rồi.');
        }

        // Tạo token mới
        $verificationToken = Str::random(60);
        $user->update(['email_verification_token' => $verificationToken]);

        // Gửi lại email
        $verificationUrl = route('verify.email', ['token' => $verificationToken]);
        Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));

        return redirect()->route('login')
            ->with('success', 'Email xác nhận đã được gửi lại.');
    }
}
