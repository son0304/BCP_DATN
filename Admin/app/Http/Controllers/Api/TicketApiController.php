<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TicketApiController extends Controller
{
    
    protected $ticketRelations = [
        'items.booking.court.venue', // Tải: Item -> Booking -> Court -> Venue
        'items.booking.timeSlot'     // Tải: Item -> Booking -> TimeSlot (để lấy start/end_time)
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $tickets = Ticket::with($this->ticketRelations) 
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        // ==========================

        return response()->json([
            'success' => true,
            'message' => 'Danh sách vé của bạn',
            'data' => $tickets
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $ticket = Ticket::with($this->ticketRelations)
            ->where('user_id', Auth::id())
            ->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vé.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chi tiết vé.',
            'data' => $ticket
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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

        try {
            $ticket = DB::transaction(function () use ($validated) {

                // 1. Kiểm tra xung đột trên bảng AVAILABILITIES
                foreach ($validated['bookings'] as $bookingData) {
                    $availability = Availability::where('court_id', $bookingData['court_id'])
                        ->where('slot_id', $bookingData['time_slot_id'])
                        ->where('date', $bookingData['date'])
                        ->lockForUpdate()
                        ->first();

                    if (!$availability || $availability->status !== 'open') {
                        throw ValidationException::withMessages([
                            'bookings' => "Khung giờ bạn chọn cho sân ID {$bookingData['court_id']} vào ngày {$bookingData['date']} đã có người khác đặt hoặc không khả dụng."
                        ]);
                    }
                }

                // 2. Tính toán tiền
                $subtotal = array_sum(array_column($validated['bookings'], 'unit_price'));
                $discount = $validated['discount_amount'] ?? 0;
                $total = $subtotal - $discount;

                // 3. Tạo Ticket
                $ticket = Ticket::create([
                    'user_id' => $validated['user_id'],
                    'promotion_id' => $validated['promotion_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                ]);

                // 4. Tạo Booking, Item và Cập nhật Availability
                foreach ($validated['bookings'] as $bookingData) {
                    $createdBooking = Booking::create([
                        'user_id' => $validated['user_id'],
                        'court_id' => $bookingData['court_id'],
                        'time_slot_id' => $bookingData['time_slot_id'],
                        'date' => $bookingData['date'],
                        'status' => 'pending', // Sẽ cập nhật khi thanh toán
                    ]);

                    Item::create([
                        'ticket_id' => $ticket->id,
                        'booking_id' => $createdBooking->id,
                        'unit_price' => $bookingData['unit_price'],
                        'discount_amount' => 0, // Giảm giá được áp dụng ở Ticket, không phải Item
                    ]);

                    Availability::where('court_id', $bookingData['court_id'])
                        ->where('slot_id', $bookingData['time_slot_id'])
                        ->where('date', $bookingData['date'])
                        ->update([
                            'status' => 'closed', // Tạm đóng
                            'note' => 'Đã đặt qua ticket #' . $ticket->id,
                        ]);
                }

                return $ticket;
            });

            // 5. Trả về kết quả
            return response()->json([
                'success' => true,
                'message' => 'Tạo ticket thành công, vui lòng thanh toán trong 2 phút.',
                'data' => $ticket->id
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi tạo ticket: '->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra phía server.'
            ], 500);
        }
    }
}
