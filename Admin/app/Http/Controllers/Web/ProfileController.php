<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{WalletLog, WithdrawalRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};

class ProfileController extends Controller
{
    public function myAccount()
    {
        $user = Auth::user();
        Log::info('User data: ', $user->toArray());
        // Load tất cả quan hệ để tránh N+1 query
        $user->load([
            'role',
            'province',
            'district',
            'merchantProfile.images',
            'images', // Ảnh đại diện qua morphMany
            'wallet'
        ]);

        // Phân trang cơ sở kinh doanh
        $venues = $user->venues()
            ->with(['province', 'district'])
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'venues_page');

        $wallet = $user->wallet;

        // Lấy lịch sử ví (giới hạn 20 giao dịch gần nhất)
        $wallet_log = $wallet
            ? WalletLog::where('wallet_id', $wallet->id)->orderBy('created_at', 'desc')->limit(20)->get()
            : collect([]);

        // Lấy lịch sử rút tiền
        $withdraw = WithdrawalRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('venue_owner.user.index', compact('user', 'wallet_log', 'venues', 'wallet', 'withdraw'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'avt'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'avt.image'     => 'File tải lên phải là hình ảnh.',
            'avt.max'       => 'Ảnh đại diện tối đa 2MB.'
        ]);

        // 1. Cập nhật thông tin cơ bản
        $user->update([
            'name'  => $request->name,
            'phone' => $request->phone,
        ]);

        // 2. Xử lý ảnh đại diện (Lưu vào bảng images qua morphMany)
        if ($request->hasFile('avt')) {
            try {
                // Xóa ảnh cũ nếu có (cả file và record)
                $oldImage = $user->images()->first();
                if ($oldImage) {
                    $oldPath = public_path($oldImage->url);
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        unlink($oldPath);
                    }
                    $oldImage->delete();
                }

                // Lưu file mới
                $file = $request->file('avt');
                $filename = time() . '_avatar_' . $user->id . '.' . $file->getClientOriginalExtension();

                if (!file_exists(public_path('uploads/avatars'))) {
                    mkdir(public_path('uploads/avatars'), 0777, true);
                }

                $file->move(public_path('uploads/avatars'), $filename);
                $path = 'uploads/avatars/' . $filename;

                // Tạo record mới trong bảng images
                $user->images()->create([
                    'url' => $path
                ]);
            } catch (\Exception $e) {
                Log::error('Lỗi upload avatar: ' . $e->getMessage());
                return back()->with('error', 'Không thể tải ảnh lên.');
            }
        }

        return back()->with('success', 'Thông tin cá nhân đã được cập nhật thành công!');
    }
}