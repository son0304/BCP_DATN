<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Province;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Validate dữ liệu tìm kiếm trên URL
        $request->validate([
            'search' => 'nullable|string|max:255',
            'role_id' => 'nullable|integer|exists:roles,id',
            'is_active' => 'nullable|boolean',
        ]);

        $query = User::with(['role', 'province', 'district']);

        Log::info('message:' . $query->toSql());


        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $roles = Role::select('id', 'name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::select('id', 'name')->get();
        $provinces = Province::select('id', 'name')->get();
        $districts = District::select('id', 'name', 'province_id')->get();

        return view('admin.users.create', compact('roles', 'provinces', 'districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 2. Validate Thêm mới trực tiếp tại đây
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed', // Cần input password_confirmation ở form
            'role_id' => 'required|integer|exists:roles,id',
            'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'province_id' => 'nullable|integer|exists:provinces,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'is_active' => 'nullable|in:0,1,on', // 'on' là giá trị checkbox gửi lên
        ], [
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role_id.required' => 'Vui lòng chọn vai trò.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ]);

        DB::beginTransaction();
        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'phone' => $request->phone,
                'province_id' => $request->province_id,
                'district_id' => $request->district_id,
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();
            return redirect()->route('admin.users.index') // Sửa lại route cho đúng tên route của bạn
                ->with('success', 'Người dùng đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load([
            'role',
            'province',
            'district',
            'merchantProfile.images'
        ]);
        Log::info('message:' . $user->toJson());


        $tickets = $user->tickets()
            ->with([
                'items.booking.court.venue',
                'items.booking.timeSlot'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'tickets_page');

        $venues = $user->venues()
            ->with(['province', 'district'])
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'venues_page');

        $roles = Role::select('id', 'name')->get();

        return view('admin.users.show', compact('user', 'tickets', 'venues', 'roles'));
    }

    public function destroy(User $user)
    {
        DB::beginTransaction();
        try {
            if ($user->tickets()->exists() || $user->venues()->exists()) {
                return back()->with('error', 'Không thể xóa người dùng này vì họ có dữ liệu liên quan (đơn đặt sân hoặc sở hữu địa điểm)!');
            }

            $user->delete();

            DB::commit();
            return redirect()->route('admin.users.index')
                ->with('success', 'Người dùng đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi khi xóa: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::select('id', 'name')->get();
        $provinces = Province::select('id', 'name')->get();
        $districts = District::select('id', 'name', 'province_id')->get();

        return view('admin.users.edit', compact('user', 'roles', 'provinces', 'districts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // 3. Validate Cập nhật trực tiếp tại đây
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|integer|exists:roles,id',
            'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'province_id' => 'nullable|integer|exists:provinces,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'is_active' => 'nullable|in:0,1,on',
        ], [
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.unique' => 'Email này đã được người khác sử dụng.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role_id.required' => 'Vui lòng chọn vai trò.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'phone' => $request->phone,
                'province_id' => $request->province_id,
                'district_id' => $request->district_id,
                'is_active' => $request->has('is_active'),
            ];

            // Chỉ update password nếu người dùng nhập vào
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            DB::commit();
            return redirect()->route('admin.users.index')
                ->with('success', 'Cập nhật thông tin thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        DB::beginTransaction();
        try {
            $user->update(['is_active' => !$user->is_active]);

            DB::commit();
            $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
            return back()->with('success', "Người dùng đã được {$status} thành công!");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}
