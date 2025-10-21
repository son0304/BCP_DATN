<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Availability; // <-- THÊM DÒNG NÀY
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCancelTicketsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            Cache::remember('tickets_cleanup_lock', 60, function () {

                $expirationTime = Carbon::now()->subMinutes(2);


                $oldTickets = Ticket::where('status', 'pending')
                                    ->where('payment_status', 'unpaid')
                                    ->where('created_at', '<', $expirationTime)
                                    ->with('items.booking')
                                    ->get();

                if ($oldTickets->isEmpty()) {
                    return true; 
                }

                foreach ($oldTickets as $ticket) {
                    $bookingsToCancel = $ticket->items->pluck('booking')->filter();

                    foreach ($bookingsToCancel as $booking) {
                        $booking->update(['status' => 'cancelled']);
                        Availability::where('court_id', $booking->court_id)
                            ->where('slot_id', $booking->time_slot_id)
                            ->where('date', $booking->date)
                            ->where('status', 'closed')
                            ->update([
                                'status' => 'open',
                                'note' => 'Tự động mở lại do ticket #' . $ticket->id . ' bị hủy.'
                            ]);
                    }

                    $ticket->update([
                        'status' => 'cancelled',
                        'notes' => 'Tự động huỷ do quá hạn thanh toán.'
                    ]);
                }

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('Lỗi trong AutoCancelTicketsMiddleware: ' . $e->getMessage());
        }
        return $next($request);
    }
}
