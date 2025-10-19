<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use App\Models\Ticket;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCancelTicketsMiddleware
{
    /**
     * Xử lý request đến.
     * Sẽ chạy logic dọn dẹp vé quá hạn 1 lần mỗi phút.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Dùng Cache để tạo "khóa", đảm bảo logic chỉ chạy 1 lần/phút
        // ngay cả khi có hàng ngàn request.
        Cache::remember('tickets:cleanup_lock', 60, function () {
            try {
                // 1. Lấy thời gian hết hạn (2 phút trước)
                $expirationTime = Carbon::now()->subMinutes(2);

                // 2. Tìm tất cả các vé chưa thanh toán VÀ đã quá hạn
                $expiredTickets = Ticket::where('payment_status', 'unpaid')
                                        ->where('status', 'pending')
                                        ->where('created_at', '<', $expirationTime)
                                        ->with('items') // Tải sẵn các item liên quan
                                        ->get();

                if ($expiredTickets->isNotEmpty()) {

                    // 3. Lấy tất cả booking_id từ các vé quá hạn
                    $bookingIdsToCancel = $expiredTickets->flatMap(function ($ticket) {
                        return $ticket->items->pluck('booking_id');
                    })->unique()->values();

                    // 4. Cập nhật trạng thái của tất cả vé và booking liên quan
                    if ($bookingIdsToCancel->isNotEmpty()) {
                        // Hủy các booking
                        Booking::whereIn('id', $bookingIdsToCancel)
                               ->where('status', 'pending')
                               ->update(['status' => 'cancelled']);

                        // Hủy các vé
                        Ticket::whereIn('id', $expiredTickets->pluck('id'))
                              ->update([
                                  'status' => 'cancelled',
                                  'notes' => 'Tự động huỷ do quá hạn thanh toán.'
                              ]);
                    }
                }

                return true; // Phải return gì đó để Cache lưu khóa

            } catch (\Throwable $e) {
                // Ghi log nếu có lỗi để debug, nhưng không làm sập trang
                Log::warning('Auto-cancel middleware failed: ' . $e->getMessage());
                return true; // Vẫn return để không thử lại ngay lập tức
            }
        });

        // Cho phép request của người dùng đi tiếp
        return $next($request);
    }
}