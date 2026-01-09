<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Role;
use App\Models\Province;
use App\Models\District;
use App\Models\WalletLog;
use App\Models\WithdrawalRequest;
use Auth;
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
        $query = User::with(['role', 'province', 'district']);

        Log::info('message:' . $query->toSql());


        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
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
        $roles = Role::select('id', 'name')->get(); // Chỉ select những cột cần thiết

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::select('id', 'name')->get();
        $provinces = Province::select('id', 'name')->get();
        $districts = District::select('id', 'name')->get();

        return view('admin.users.create', compact('roles', 'provinces', 'districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
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
                'lat' => $request->lat,
                'lng' => $request->lng,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            DB::commit();
            return redirect()->route('admin.admin.users.index')
                ->with('success', 'Người dùng đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo người dùng: ' . $e->getMessage());
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


        // Paginate bookings
        $bookings = $user->bookings()
            ->with(['court.venue', 'timeSlot'])
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'bookings_page');

        // Paginate venues
        $venues = $user->venues()
            ->with(['province', 'district'])
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'venues_page');

        // Get roles for search form
        $roles = Role::select('id', 'name')->get();

        return view('admin.users.show', compact('user', 'bookings', 'venues', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::select('id', 'name')->get();
        $provinces = Province::select('id', 'name')->get();
        $districts = District::select('id', 'name')->get();

        return view('admin.users.edit', compact('user', 'roles', 'provinces', 'districts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        DB::beginTransaction();
        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'phone' => $request->phone,
                'province_id' => $request->province_id,
                'district_id' => $request->district_id,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'is_active' => $request->has('is_active') ? true : false,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            DB::commit();
            return redirect()->route('admin.admin.users.index')
                ->with('success', 'Thông tin người dùng đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật người dùng: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        DB::beginTransaction();
        try {
            // Check if user has bookings or venues
            if ($user->bookings()->exists() || $user->venues()->exists()) {
                return back()->with('error', 'Không thể xóa người dùng này vì họ có đặt sân hoặc sở hữu địa điểm!');
            }

            $user->delete();

            DB::commit();
            return redirect()->route('admin.admin.users.index')
                ->with('success', 'Người dùng đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Có lỗi xảy ra khi xóa người dùng: ' . $e->getMessage());
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
            return back()->with('error', 'Có lỗi xảy ra khi thay đổi trạng thái người dùng: ' . $e->getMessage());
        }
    }


    public function myAccout()
    {
        $user = Auth::user();
        $user->load([
            'role',
            'province',
            'district',
            'merchantProfile.images',
        ]);
        Log::info('message:' . $user->toJson());


        // Paginate venues
        $venues = $user->venues()
            ->with(['province', 'district'])
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'venues_page');

        $wallet = $user->wallet;
        $wallet_log = WalletLog::where('wallet_id', $wallet->id)->orderBy('created_at', 'desc')->get();
        $roles = Role::select('id', 'name')->get();

        $withdraw = WithdrawalRequest::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return view('venue_owner.user.index', compact('user', 'wallet_log', 'venues', 'roles', 'wallet', 'withdraw'));
    }
}