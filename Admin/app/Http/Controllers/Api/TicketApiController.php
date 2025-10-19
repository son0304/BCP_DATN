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
        // --- LOGIC AUTO-CANCEL ĐÃ ĐƯỢC CHUYỂN RA MIDDLEWARE ĐỂ CHẠY ĐÁNG TIN CẬY HƠN ---

        // 1️ Validate dữ liệu đầu vào
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

        // 2️ Dùng Transaction để đảm bảo tính toàn vẹn dữ liệu
        try {
            $ticket = DB::transaction(function () use ($validated) {

                // Tối ưu hóa: Kiểm tra tất cả các slot xung đột trong 1 truy vấn
                $conflictExists = Booking::where(function ($query) use ($validated) {
                    foreach ($validated['bookings'] as $booking) {
                        $query->orWhere(function ($subQuery) use ($booking) {
                            $subQuery->where('court_id', $booking['court_id'])
                                ->where('time_slot_id', $booking['time_slot_id'])
                                ->where('date', $booking['date']);
                        });
                    }
                })
                ->where('status', '!=', 'cancelled')
                ->whereNull('deleted_at')
                ->lockForUpdate() // Khóa các hàng để tránh race condition
                ->exists();

                if ($conflictExists) {
                    throw ValidationException::withMessages([
                        'bookings' => 'Một hoặc nhiều slot bạn chọn đã được người khác đặt. Vui lòng thử lại.'
                    ]);
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
                    'status' => 'pending', // Trạng thái của vé
                    'payment_status' => 'unpaid', // Trạng thái thanh toán
                ]);

                // Tạo booking + item cho từng sân
                foreach ($validated['bookings'] as $bookingData) {
                    $createdBooking = Booking::create([
                        'user_id' => $validated['user_id'],
                        'court_id' => $bookingData['court_id'],
                        'time_slot_id' => $bookingData['time_slot_id'],
                        'date' => $bookingData['date'],
                        'status' => 'pending', // Trạng thái của từng slot
                    ]);

                    Item::create([
                        'ticket_id' => $ticket->id,
                        'booking_id' => $createdBooking->id,
                        'unit_price' => $bookingData['unit_price'],
                        'discount_amount' => 0,
                    ]);
                }

                return $ticket;
            });

             // 3️ Trả về kết quả
            return response()->json([
                'success' => true,
                'message' => 'Tạo ticket thành công, vui lòng thanh toán trong 2 phút.',
                'data' => $ticket->id
            ]);

        } catch (ValidationException $e) {
            // Bắt lỗi validation (slot đã được đặt)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            // Bắt các lỗi khác
            Log::error('Lỗi khi tạo ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra phía server.'
            ], 500);
        }
    }
}