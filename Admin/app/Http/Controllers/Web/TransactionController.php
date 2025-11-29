<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Khởi tạo query và eager load 'user' để tối ưu hiệu năng
        $query = Transaction::with('user');

        // 1. Tìm kiếm từ khóa (Keyword)
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('id', $keyword) // Tìm theo ID giao dịch
                    ->orWhere('booking_id', 'like', "%{$keyword}%") // Tìm theo mã booking
                    ->orWhere('note', 'like', "%{$keyword}%") // Tìm trong ghi chú
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        // Tìm theo tên hoặc sđt user
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    });
            });
        }

        // 2. Lọc theo Trạng thái (Status)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Lọc theo Nguồn tiền (Payment Source)
        if ($request->filled('source')) {
            $query->where('payment_source', $request->source);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sắp xếp mới nhất trước và phân trang (10 dòng/trang)
        // appends($request->all()) giúp giữ lại các tham số lọc khi chuyển trang
        $transactions = $query->latest()->paginate(10)->appends($request->all());

        return view('admin.transactions.index', compact('transactions'));
    }
}