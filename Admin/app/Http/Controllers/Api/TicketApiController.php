<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Promotion;
use App\Models\Ticket;
use App\Models\Wallet;
use App\Models\WalletLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TicketApiController extends Controller
{



    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $tickets = Ticket::with([
            'user:id,name,email,role_id,phone,avt',
            'items:id,ticket_id,booking_id,unit_price,discount_amount',
            'items.booking:id,court_id,date,status,time_slot_id',
            'items.booking.court:id,name',
            'items.booking.timeSlot:id,end_time,start_time'
        ])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

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
        $ticket = Ticket::with([
            'user:id,name,email,role_id,phone,avt',
            'user.wallet:id,user_id,balance', // select đúng cột 'user_id', không phải 'owner_id'
            'items:id,ticket_id,booking_id,unit_price,discount_amount,status',
            'items.booking:id,court_id,date,status,time_slot_id',
            'items.booking.court:id,name',
            'items.booking.timeSlot:id,start_time,end_time',
        ])->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket không tồn tại',

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
                if (!empty($validated['promotion_id'])) {
                    Promotion::where('id', $validated['promotion_id'])
                        ->update([
                            'usage_limit' => DB::raw('usage_limit - 1')
                        ]);
                }

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
            Log::error('Lỗi khi tạo ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra phía server.'
            ], 500);
        }
    }

    public function destroyTicket($id)
    {
        $ticket = Ticket::with(['items.booking.timeSlot', 'promotion'])->findOrFail($id);

        if ($ticket->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Vé này đã bị hủy trước đó.'], 400);
        }

        $now = Carbon::now();

        foreach ($ticket->items as $item) {
            if ($item->status === 'refund') continue; // Bỏ qua item đã hủy lẻ

            if ($item->booking) {
                $bookingDateTime = Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->start_time);
                if ($bookingDateTime->isPast()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sân {$item->booking->court->name} ({$item->booking->date}) đã diễn ra. Không thể hủy vé.",
                    ], 400);
                }

                $diffMinutes = $now->diffInMinutes($bookingDateTime, false);
                if ($diffMinutes < 720) { // 720 phút = 12 tiếng
                    return response()->json([
                        'success' => false,
                        'message' => "Sân {$item->booking->court->name} sắp diễn ra trong vòng 12 tiếng. Không thể hủy vé.",
                    ], 400);
                }
            }
        }

        DB::transaction(function () use ($ticket, $now) {
            $totalRefundRaw = 0;

            // 1. Đổi tên biến này để dễ hiểu hơn, chứa danh sách chi tiết
            $refundDetails = [];

            $amount = 0; // Số tiền phạt (giữ lại)
            // $commission = 0.20; // Nếu cần tính hoa hồng

            foreach ($ticket->items as $item) {
                if ($item->status === 'refund') continue;

                $refundRate = 0;
                $rateLabel = '0%'; // Biến tạm để lưu text %

                if ($item->booking) {
                    $bookingDateTime = Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->start_time);
                    $diffMinutes = $now->diffInMinutes($bookingDateTime, false);

                    // Tạo tên hiển thị ngắn gọn: "Sân 1 (10:00 - 20/10)"
                    $courtName = $item->booking->court->name ?? 'Sân';
                    $timeSlot = substr($item->booking->timeSlot->start_time ?? '', 0, 5); // Lấy HH:mm
                    $dateBooking = Carbon::parse($item->booking->date)->format('d/m');

                    $nameItem = "{$courtName} ({$timeSlot} {$dateBooking})";

                    // --- LOGIC TÍNH TỶ LỆ ---
                    if ($diffMinutes > 1440) {
                        $refundRate = 1.0;
                        $rateLabel = '100%';
                    } elseif ($diffMinutes >= 720) {
                        $refundRate = 0.5;
                        $rateLabel = '50%';
                    } else {
                        $rateLabel = '0%';
                    }

                    // 2. Lưu chi tiết vào mảng: "Sân A (10:00): 50%"
                    $refundDetails[] = "{$nameItem}: {$rateLabel}";

                    $totalRefundRaw += ($item->unit_price * $refundRate);

                    // Cập nhật trạng thái booking để nhả sân
                    $item->booking->update(['status' => 'cancelled']);
                }
                $item->update(['status' => 'refund']);
            }

            // --- XỬ LÝ TEXT GHI CHÚ ---
            // 3. Nối chuỗi chi tiết bằng dấu chấm phẩy hoặc xuống dòng
            // Kết quả sẽ là: "Sân A (10:00 20/10): 100%; Sân B (11:00 20/10): 50%"
            $detailNote = implode('; ', $refundDetails);

            // Tính tiền thực tế (trừ voucher)...
            $discountUsed = $ticket->discount_amount ?? 0;
            $finalRefund = max(0, $totalRefundRaw - $discountUsed);
            $finalRefund = min($finalRefund, $ticket->total_amount);

            // Tính tiền giữ lại (doanh thu phạt)
            $penaltyAmount = $ticket->total_amount - $finalRefund;

            // --- GHI LOG ---
            if ($ticket->user_id && $finalRefund > 0) {
                $wallet = Wallet::where('user_id', $ticket->user_id)->lockForUpdate()->first();
                if ($wallet) {
                    $beforeBalance = $wallet->balance;
                    $afterBalance = $beforeBalance + $finalRefund;

                    $wallet->update(['balance' => $afterBalance]);

                    WalletLog::create([
                        'wallet_id'      => $wallet->id,
                        'ticket_id'      => $ticket->id,
                        'type'           => 'refund',
                        'amount'         => $finalRefund,
                        'before_balance' => $beforeBalance,
                        'after_balance'  => $afterBalance,

                        // 4. Ghi description chi tiết
                        // VD: "Hoàn hủy vé #123. CT: Sân A (10:00): 100%; Sân B (11:00): 50%"
                        'description'    => "Hoàn hủy vé #{$ticket->id}. CT: {$detailNote}",
                    ]);
                }
            }

            // ... Logic chia tiền cho Admin/Owner (như đã bàn ở câu trước) ...

            $ticket->update(['status' => 'cancelled']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Hủy vé thành công. Tiền đã được hoàn lại vào ví.'
        ]);
    }
    public function destroyItem($id)
    {
        $item = Item::with(['booking.court', 'ticket.promotion'])->find($id);
        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item không tồn tại.'], 404);
        }
        if ($item->status === 'refund') {
            return response()->json(['success' => false, 'message' => 'Lịch này đã hủy và hoàn tiền rồi.'], 400);
        }
        if (!$item->booking) {
            return response()->json(['success' => false, 'message' => 'Lỗi dữ liệu: Item này không liên kết với sân nào.'], 500);
        }

        // --- 2. CHECK THỜI GIAN & TỶ LỆ ---
        $now = Carbon::now();
        $bookingDateTime = Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->start_time);

        // Check quá khứ
        if ($bookingDateTime->isPast()) {
            return response()->json(['success' => false, 'message' => 'Không thể hủy lịch đã diễn ra.'], 400);
        };

        $diffMinutes = $now->diffInMinutes($bookingDateTime, false);

        if ($diffMinutes < 120) {
            return response()->json(['success' => false, 'message' => 'Chỉ được hủy trước giờ chơi tối thiểu 2 tiếng.'], 400);
        };
        $refundRate = ($diffMinutes > 1440) ? 1.0 : 0.5;

        // --- 3. GIAO DỊCH DATABASE ---
        DB::transaction(
            function () use ($item, $refundRate) {
                // A. Trả lại sân trống
                Availability::where('court_id', $item->booking->court_id)
                    ->where('slot_id', $item->booking->time_slot_id)
                    ->where('date', $item->booking->date)
                    ->update(['status' => 'open', 'note' => null]);

                // B. Cập nhật trạng thái item/booking
                $item->booking->update(['status' => 'cancelled']);
                $item->update(['status' => 'refund']);

                // C. Xử lý Tiền & Ticket
                if ($item->ticket) {
                    $ticket = $item->ticket;

                    // $amount = (int)round($ticket->total_amount);
                    // $commission = 0.20;
                    // $paidAmount = (float)$amount;
                    // $discount = (float)($ticketLocked->discount_amount ?? 0);
                    // $originalPrice = $paidAmount + $discount;

                    // $adminAmount = 0;
                    // $venueOwnerAmount = 0;

                    // $fee = $paidAmount * $commission;
                    // $venueOwnerAmount = $paidAmount - $fee;
                    // $adminAmount = $fee;
                    // --- XỬ LÝ VÍ & GHI LOG (QUAN TRỌNG) ---
                    if ($ticket->user_id) {
                        $refundAmount = $item->unit_price * $refundRate;

                        if ($refundAmount > 0) {
                            // 1. Lấy ví
                            $wallet = Wallet::where('user_id', $ticket->user_id)->first();

                            if ($wallet) {
                                // 2. Tính toán số dư
                                $beforeBalance = $wallet->balance;
                                $afterBalance = $beforeBalance + $refundAmount;

                                // 3. Update Ví
                                $wallet->update(['balance' => $afterBalance]);

                                // 4. Format nội dung log (VD: "100%", "50%")
                                $ratePercent = $refundRate * 100;
                                $courtName = $item->booking->court->name ?? 'Sân';

                                // 5. Ghi Log
                                WalletLog::create([
                                    'wallet_id'      => $wallet->id,
                                    'ticket_id'      => $ticket->id,
                                    'booking_id'     => $item->booking_id,
                                    'type'           => 'refund',
                                    'amount'         => $refundAmount,
                                    'before_balance' => $beforeBalance,
                                    'after_balance'  => $afterBalance,
                                    'description'    => "Hoàn tiền hủy {$courtName} (Tỷ lệ: {$ratePercent}%)",
                                ]);
                            }
                        }
                    }

                    // --- TÍNH LẠI TỔNG TIỀN TICKET ---
                    // Chỉ tính tổng các item còn active
                    $activeItems = $ticket->items()->where('status', 'active');
                    $newSubtotal = $activeItems->sum('unit_price');
                    $activeCount = $activeItems->count();

                    // Tính lại voucher
                    $discountAmount = 0;
                    $promotion = $ticket->promotion;

                    if ($promotion) {
                        if ($promotion->type === 'VND') {
                            $discountAmount = min($promotion->value, $newSubtotal);
                        } elseif ($promotion->type === '%') {
                            $discountAmount = ($promotion->value / 100) * $newSubtotal;
                        }
                    }

                    // Update Ticket
                    $ticket->update([
                        'subtotal' => $newSubtotal,
                        'discount_amount' => $discountAmount,
                        'total_amount' => max(0, $newSubtotal - $discountAmount),
                    ]);

                    // Nếu hủy hết sạch item thì hủy luôn vé
                    if ($activeCount === 0) {
                        $ticket->update([
                            'status' => 'cancelled', // Hoặc 'canceled' tùy enum của bạn
                            'payment_status' => 'refunded',
                        ]);
                    }
                };
            },
        );

        return response()->json([
            'success' => true,
            'message' => 'Hủy sân thành công. Tiền đã được hoàn về ví.'
        ]);
    }
}
