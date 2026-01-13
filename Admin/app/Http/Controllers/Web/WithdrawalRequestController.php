<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Wallet;
use App\Models\WalletLog;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawalRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');

        $query = WithdrawalRequest::with('user.merchantProfile')
            ->orderBy('created_at', 'desc');

        // 1. Lọc theo trạng thái
        if ($status && $status != 'all') {
            $query->where('status', $status);
        }

        // 2. Tìm kiếm (Bổ sung tìm ID)
        if ($search) {
            $query->where(function ($q) use ($search) {
                // Kiểm tra nếu search là số thì ưu tiên tìm theo ID chính xác
                if (is_numeric($search)) {
                    $q->where('id', $search);
                }

                // Tìm kiếm theo các trường khác
                $q->orWhere('transaction_code', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $withdrawalRequests = $query->paginate(15)->withQueryString();

        return view('admin.withdraw.index', compact('withdrawalRequests', 'status', 'search'));
    }
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Kiểm tra thông tin ngân hàng trước khi cho rút
        if (!$user->merchantProfile) {
            return redirect()->back()->with('error', 'Vui lòng cập nhật thông tin ngân hàng trong hồ sơ trước khi rút tiền.');
        }

        $minReserve = 300000;
        $wallet = Wallet::where('user_id', $user->id)->first();

        // Tính số tiền tối đa có thể rút
        $maxWithdrawable = $wallet ? max(0, $wallet->balance - $minReserve) : 0;

        $request->validate([
            'amount' => "required|numeric|min:50000|max:{$maxWithdrawable}",
        ], [
            'amount.max' => 'Số dư khả dụng không đủ (cần duy trì tối thiểu 300.000đ).',
            'amount.min' => 'Số tiền rút tối thiểu là 50.000đ.'
        ]);

        try {
            DB::beginTransaction();

            // Khóa hàng ví để tránh race condition (nhấn 2 lần liên tiếp)
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            $amount = $request->amount;
            $beforeBalance = $wallet->balance;
            $afterBalance = $beforeBalance - $amount;

            // 2. Tạo yêu cầu rút tiền
            $withdraw = WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'fee' => 0,
                'actual_amount' => $amount,
                'bank_name' => $user->merchantProfile->bank_name,
                'bank_account_number' => $user->merchantProfile->bank_account_number,
                'bank_account_name' => $user->merchantProfile->bank_account_name,
                'status' => 'pending'
            ]);

            // 3. Trừ tiền ví
            $wallet->update(['balance' => $afterBalance]);

            // 4. Ghi log ví (Dùng Model::create thay vì instance cũ)
            WalletLog::create([
                'wallet_id'      => $wallet->id,
                'before_balance' => $beforeBalance,
                'after_balance'  => $afterBalance,
                'amount'         => - ($amount),
                'type'           => 'withdraw',
                'description'    => "Tạm trừ tiền cho yêu cầu rút tiền #{$withdraw->id}"
            ]);
            $notification = Notification::create([
                'user_id' => 1,
                'type' => 'info',
                'title' => 'yêu cầu rút tiền',
                'message' => 'Bạn có một yêu cầu rút tiền mới từ chủ sân: ' . $user->name . ' Số tiền: ' . number_format($amount) . 'đ',
                'data' => [
                    'withdraw' => $withdraw->id,
                    'link' => '/admin/withdrawal-requests?status=all&search=' . $withdraw->id,
                ],
                'read_at' => null,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Gửi yêu cầu rút tiền thành công. Vui lòng chờ Admin phê duyệt.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi rút tiền: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau.');
        }
    }

    public function update(Request $request, $id)
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);
        $user = $withdrawal->user;

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý hoặc hủy bỏ trước đó.');
        }

        $status = $request->input('status');

        // TRƯỜNG HỢP 1: PHÊ DUYỆT (Xác nhận đã chuyển tiền cho sân)
        if ($status === 'approved') {
            $request->validate([
                'transaction_code' => 'required|string|max:100',
            ], [
                'transaction_code.required' => 'Vui lòng nhập mã giao dịch ngân hàng để xác nhận.'
            ]);

            // Cập nhật trạng thái thành 'approved'
            // Sau khi update xong, Dashboard sẽ tự động tính số tiền này vào cột "CHI TRẢ SÂN"
            $withdrawal->update([
                'status' => 'approved',
                'transaction_code' => $request->transaction_code,
                'processed_at' => now(),
            ]);

            return back()->with('success', 'Đã phê duyệt. Tiền đã được ghi nhận chi ra từ ngân hàng.');
        }

        // TRƯỜNG HỢP 2: TỪ CHỐI (Hoàn trả tiền về ví như cũ của bạn)
        if ($status === 'rejected') {
            $request->validate([
                'admin_note' => 'required|string|max:255',
            ], [
                'admin_note.required' => 'Vui lòng nhập lý do từ chối.'
            ]);

            DB::transaction(function () use ($withdrawal, $user, $request) {
                $withdrawal->update([
                    'status' => 'rejected',
                    'admin_note' => $request->admin_note,
                    'processed_at' => now(),
                ]);

                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if ($wallet) {
                    $beforeBalance = $wallet->balance;
                    $wallet->increment('balance', $withdrawal->amount);

                    WalletLog::create([
                        'wallet_id'      => $wallet->id,
                        'before_balance' => $beforeBalance,
                        'after_balance'  => $beforeBalance + $withdrawal->amount,
                        'amount'         => $withdrawal->amount,
                        'type'           => 'refund',
                        'description'    => "Hoàn tiền cho yêu cầu rút tiền #{$withdrawal->id} bị từ chối."
                    ]);
                }
            });

            return back()->with('success', 'Đã từ chối và hoàn tiền lại ví chủ sân.');
        }

        return back()->with('error', 'Hành động không hợp lệ.');
    }
}