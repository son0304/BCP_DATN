<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TicketApiController extends Controller
{
    /**
     * Lấy danh sách ticket kèm các booking + court.
     */
    public function index()
    {
        $tickets = Ticket::with('items.booking.court', 'items.booking.timeSlot')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách ticket thành công',
            'data' => $tickets
        ]);
    }

    /**
     * Lấy chi tiết 1 ticket theo ID.
     */
    public function show($id)
    {
        $ticket = Ticket::with('items.booking.court', 'items.booking.timeSlot')->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ticket'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết ticket thành công',
            'data' => $ticket
        ]);
    }

    /**
     * Tạo mới ticket + booking + items.
     */
    public function store(Request $request)
    {
        // 1️⃣ Auto-cancel các booking pending quá hạn
        try {
            $expiredBookings = Booking::where('status', 'pending')
                ->where('created_at', '<', now()->subMinutes(2))
                ->get();

            foreach ($expiredBookings as $booking) {
                $booking->update(['status' => 'cancelled']);

                // Hủy ticket liên quan nếu có
                $ticketIds = Item::where('booking_id', $booking->id)
                    ->pluck('ticket_id')
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($ticketIds)) {
                    Ticket::whereIn('id', $ticketIds)->update(['status' => 'cancelled']);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Không thể auto-cancel booking: ' . $e->getMessage());
        }

        // 2️ Validate dữ liệu đầu vào
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'promotion_id' => 'nullable|exists:promotions,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'bookings' => 'required|array|min:1',

            'bookings.*.court_id' => 'required|exists:courts,id',
            'bookings.*.time_slot_id' => 'required|exists:time_slots,id',
            'bookings.*.date' => 'required|date|after_or_equal:today',
            'bookings.*.unit_price' => 'required|numeric|min:0',
        ]);

        // 3️ Transaction tạo ticket + booking + item
        $ticket = DB::transaction(function () use ($validated) {

            // Kiểm tra trùng slot (đã được đặt rồi)
            foreach ($validated['bookings'] as $booking) {
                $exists = Booking::where('court_id', $booking['court_id'])
                    ->where('time_slot_id', $booking['time_slot_id'])
                    ->where('date', $booking['date'])
                    ->where('status', '!=', 'cancelled')
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'bookings' => [
                            "Sân ID {$booking['court_id']} - slot ID {$booking['time_slot_id']} đã được đặt vào ngày {$booking['date']}"
                        ]
                    ]);
                }
            }

            // Tính toán tiền
            $subtotal = array_sum(array_column($validated['bookings'], 'unit_price'));
            $discount = $validated['discount_amount'] ?? 0;
            $total = $subtotal - $discount;

            // Tạo ticket
            $ticket = Ticket::create([
                'user_id' => $validated['user_id'],
                'promotion_id' => $validated['promotion_id'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            // Tạo booking + item cho từng sân
            foreach ($validated['bookings'] as $booking) {
                $createdBooking = Booking::create([
                    'user_id' => $validated['user_id'],
                    'court_id' => $booking['court_id'],
                    'time_slot_id' => $booking['time_slot_id'],
                    'date' => $booking['date'],
                    'status' => 'pending',
                ]);

                Item::create([
                    'ticket_id' => $ticket->id,
                    'booking_id' => $createdBooking->id,
                    'unit_price' => $booking['unit_price'],
                    'discount_amount' => 0,
                ]);
            }

            return $ticket;
        });

        // 4️ Trả về kết quả
        return response()->json([
            'success' => true,
            'message' => 'Tạo ticket thành công',
            'data' => $ticket->id
        ]);
    }
}