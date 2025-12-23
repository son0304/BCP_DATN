<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Booking_Status;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Promotion;
use App\Models\Ticket;
use App\Models\VenueService;
use App\Models\Wallet;
use App\Models\WalletLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
            'items.booking.court:id,name,venue_id',
            'items.booking.court.venue:id,name',
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


    public function show($id)
    {
        $ticket = Ticket::with([
            'user:id,name,email,role_id,phone,avt',
            'user.wallet:id,user_id,balance', // select đúng cột 'user_id', không phải 'owner_id'
            'items:id,ticket_id,booking_id,venue_service_id,unit_price,quantity,discount_amount,status',
            'items.booking:id,court_id,date,status,time_slot_id',
            'items.booking.court:id,name,venue_id',
            'items.booking.court.venue:id,name',
            'items.booking.timeSlot:id,start_time,end_time',
            'items.venueService:id,service_id,venue_id,price',
            'items.venueService.service:id,name,unit,type',
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
        Log::info('Yêu cầu tạo ticket', ['request' => $request->all()]);

        // 1. Validate dữ liệu
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'promotion_id' => 'nullable|exists:promotions,id',
            'discount_amount' => 'nullable|numeric|min:0',

            // --- Validate Bookings ---
            'bookings' => 'required|array|min:1',
            'bookings.*.court_id' => 'required|exists:courts,id',
            'bookings.*.time_slot_id' => 'required|exists:time_slots,id',
            'bookings.*.date' => 'required|date|after_or_equal:today',
            'bookings.*.unit_price' => 'required|numeric|min:0',
            'bookings.*.sale_price' => 'required|numeric|min:0',

            // --- Validate Services ---
            'services' => 'nullable|array',
            // CHÚ Ý: Frontend phải gửi key là 'venue_service_id' hoặc bạn map lại dữ liệu trước khi validate
            'services.*.venue_service_id' => 'required|exists:venue_services,id',
            'services.*.quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        try {
            $ticket = DB::transaction(function () use ($validated) {

                // ====================================================
                // GIAI ĐOẠN 1: TÍNH TOÁN & KHÓA DỮ LIỆU (LOCKING)
                // ====================================================

                $bookingTotal = 0;
                $serviceTotal = 0;
                $bookingItemsPayload = [];
                $serviceItemsPayload = [];

                // --- 1.1 Xử lý Booking ---
                foreach ($validated['bookings'] as $bookingData) {
                    // Lock availability để tránh trùng lịch
                    $availability = Availability::where('court_id', $bookingData['court_id'])
                        ->where('slot_id', $bookingData['time_slot_id'])
                        ->where('date', $bookingData['date'])
                        ->lockForUpdate()
                        ->first();

                    if (!$availability || $availability->status !== 'open') {
                        throw ValidationException::withMessages([
                            'bookings' => "Sân ID {$bookingData['court_id']} khung giờ này đã có người đặt."
                        ]);
                    }

                    $finalPrice = ($bookingData['sale_price'] > 0) ? $bookingData['sale_price'] : $bookingData['unit_price'];
                    $bookingTotal += $finalPrice;

                    $bookingItemsPayload[] = [
                        'data' => $bookingData,
                        'final_price' => $finalPrice
                    ];
                }

                // --- 1.2 Xử lý Services ---
                if (!empty($validated['services'])) {
                    foreach ($validated['services'] as $srvItem) {
                        // Tìm trong bảng venue_services
                        $venueService = VenueService::with('service')
                            ->where('id', $srvItem['venue_service_id'])
                            ->lockForUpdate()
                            ->first();

                        $qty = $srvItem['quantity'];
                        $venueService->decrement('stock', $qty);


                        // Check tồn kho
                        // if ($venueService->service->type === 'consumable') {
                        //     if ($venueService->stock < $qty) {
                        //         throw ValidationException::withMessages([
                        //             'services' => "Sản phẩm {$venueService->service->name} chỉ còn {$venueService->stock}."
                        //         ]);
                        //     }
                        //     $venueService->decrement('stock', $qty);
                        // }

                        $itemTotal = $venueService->price * $qty;
                        $serviceTotal += $itemTotal;

                        $serviceItemsPayload[] = [
                            'venue_service' => $venueService,
                            'quantity' => $qty,
                            'unit_price' => $venueService->price,
                            'total_price' => $itemTotal
                        ];
                    }
                }

                // --- 1.3 Tổng kết tiền ---
                $subtotal = $bookingTotal + $serviceTotal;
                $discount = $validated['discount_amount'] ?? 0;
                $totalAmount = max(0, $subtotal - $discount);

                // ====================================================
                // GIAI ĐOẠN 2: LƯU DATABASE
                // ====================================================

                // 2.1 Tạo Ticket
                $ticket = Ticket::create([
                    'user_id' => $validated['user_id'],
                    'promotion_id' => $validated['promotion_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                ]);

                // Trừ lượt khuyến mãi
                if (!empty($validated['promotion_id'])) {
                    Promotion::where('id', $validated['promotion_id'])->decrement('usage_limit');
                }

                // 2.2 Lưu Item Booking
                foreach ($bookingItemsPayload as $payload) {
                    $bData = $payload['data'];

                    $booking = Booking::create([
                        'user_id' => $validated['user_id'],
                        'court_id' => $bData['court_id'],
                        'time_slot_id' => $bData['time_slot_id'],
                        'date' => $bData['date'],
                        'status' => 'pending',
                    ]);

                    // Đóng lịch
                    Availability::where('court_id', $bData['court_id'])
                        ->where('slot_id', $bData['time_slot_id'])
                        ->where('date', $bData['date'])
                        ->update(['status' => 'closed', 'note' => 'Ticket #' . $ticket->id]);

                    // Tạo Item Booking
                    Item::create([
                        'ticket_id'        => $ticket->id,
                        'item_type'        => 'booking', // Quan trọng để phân loại
                        'booking_id'       => $booking->id,
                        'venue_service_id' => null, // Booking thì không có service
                        'unit_price'       => $bData['unit_price'],
                        'quantity'         => 1,
                        'discount_amount'  => $bData['unit_price'] - $payload['final_price'],
                        'total_price'      => $payload['final_price']
                    ]);
                }

                // 2.3 Lưu Item Services (ĐÃ SỬA CHỖ NÀY)
                foreach ($serviceItemsPayload as $payload) {
                    Item::create([
                        'ticket_id'        => $ticket->id,
                        'item_type'        => 'service', // Quan trọng để phân loại
                        'booking_id'       => null,      // Service thì không có booking

                        // LƯU Ý: Đây là cột bạn vừa sửa trong DB
                        'venue_service_id' => $payload['venue_service']->id,

                        'unit_price'       => $payload['unit_price'],
                        'quantity'         => $payload['quantity'],
                        'discount_amount'  => 0,
                        'total_price'      => $payload['total_price']
                    ]);
                }

                return $ticket;
            });

            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng thành công!',
                'data' =>  $ticket->id
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Lỗi tạo ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống.'
            ], 500);
        }
    }

    public function destroyTicket($id)
    {
        // 1. Thêm 'items.venue_service.service' để lấy tên dịch vụ và 'items.venue_service' để trả stock
        $ticket = Ticket::with([
            'items.booking.timeSlot',
            'items.booking.court',
            'items.venueService.service',
            'promotion'
        ])->findOrFail($id);

        if ($ticket->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Vé này đã bị hủy trước đó.'], 400);
        }

        $now = Carbon::now();

        // --- BƯỚC 1: KIỂM TRA ĐIỀU KIỆN THỜI GIAN (CHỈ ÁP DỤNG CHO BOOKING SÂN) ---
        foreach ($ticket->items as $item) {
            if ($item->status === 'refund') continue;

            // Chỉ kiểm tra thời gian nếu item là đặt sân
            if ($item->booking) {
                $bookingDateTime = Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->start_time);

                // Nếu trận đấu đã qua -> Không cho hủy vé (kể cả dịch vụ đi kèm)
                if ($bookingDateTime->isPast()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sân {$item->booking->court->name} ({$item->booking->date}) đã diễn ra. Không thể hủy vé.",
                    ], 400);
                }

                // Nếu còn dưới 12 tiếng -> Không cho hủy vé
                $diffMinutes = $now->diffInMinutes($bookingDateTime, false);
                if ($diffMinutes < 720) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sân {$item->booking->court->name} sắp diễn ra trong vòng 12 tiếng. Không thể hủy vé.",
                    ], 400);
                }
            }
        }

        DB::transaction(function () use ($ticket, $now) {
            $totalRefundRaw = 0;
            $refundDetails = [];

            foreach ($ticket->items as $item) {
                if ($item->status === 'refund') continue;

                $refundRate = 0;
                $rateLabel = '0%';
                $itemTotal = $item->unit_price * $item->quantity; // Tính tổng tiền item (đơn giá * số lượng)

                // --- TRƯỜNG HỢP 1: ĐẶT SÂN (BOOKING) ---
                if ($item->booking) {
                    // Mở lại lịch đặt sân
                    Availability::where('court_id', $item->booking->court_id)
                        ->where('slot_id', $item->booking->time_slot_id)
                        ->where('date', $item->booking->date)
                        ->update(['status' => 'open', 'note' => null]);

                    $item->booking->update(['status' => 'cancelled']);

                    // Tính thời gian để xác định % hoàn tiền
                    $bookingDateTime = Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->start_time);
                    $diffMinutes = $now->diffInMinutes($bookingDateTime, false);

                    if ($diffMinutes > 1440) { // > 24h
                        $refundRate = 1.0;
                        $rateLabel = '100%';
                    } elseif ($diffMinutes >= 720) { // 12h - 24h
                        $refundRate = 0.5;
                        $rateLabel = '50%';
                    } else {
                        $rateLabel = '0%';
                    }

                    // Tạo ghi chú: "Sân 1 (10:00 20/12): 50%"
                    $courtName = $item->booking->court->name ?? 'Sân';
                    $timeSlot = substr($item->booking->timeSlot->start_time ?? '', 0, 5);
                    $dateBooking = Carbon::parse($item->booking->date)->format('d/m');

                    $refundDetails[] = "{$courtName} ({$timeSlot} {$dateBooking}): {$rateLabel}";
                }

                // --- TRƯỜNG HỢP 2: DỊCH VỤ (VENUE SERVICE) ---
                elseif ($item->venue_service_id) {
                    // Logic: Dịch vụ luôn hoàn 100% nếu vé được phép hủy
                    $refundRate = 1.0;
                    $rateLabel = '100%';

                    // Hoàn trả tồn kho (Stock)
                    if ($item->venueService) {
                        $item->venueService->increment('stock', $item->quantity);
                    }

                    // Lấy tên dịch vụ để ghi log
                    $serviceName = $item->venueService->service->name ?? 'Dịch vụ';
                    $refundDetails[] = "{$serviceName} (x{$item->quantity}): {$rateLabel}";
                }

                // --- TÍNH TOÁN ---
                $totalRefundRaw += ($itemTotal * $refundRate);

                // Cập nhật trạng thái item là đã hoàn hủy
                $item->update(['status' => 'refund']);
            }

            // --- TỔNG KẾT VÀ HOÀN TIỀN VÀO VÍ ---
            $detailNote = implode('; ', $refundDetails);

            $discountUsed = $ticket->discount_amount ?? 0;
            // Trừ voucher (nếu có) khỏi số tiền hoàn, không âm
            $finalRefund = max(0, $totalRefundRaw - $discountUsed);
            // Không hoàn quá số tiền thực thu
            $finalRefund = min($finalRefund, $ticket->total_amount);

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
                        'description'    => "Hoàn hủy vé #{$ticket->id}. CT: {$detailNote}",
                    ]);
                }
            }

            $ticket->update(['status' => 'cancelled']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Hủy vé thành công. Tiền đã được hoàn lại vào ví.'
        ]);
    }
    public function destroyItem($id)
    {
        $item = Item::with(['booking.court', 'ticket.promotion', 'venueService.service'])->find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item không tồn tại.'], 404);
        }
        if ($item->status === 'refund') {
            return response()->json(['success' => false, 'message' => 'Mục này đã hủy và hoàn tiền rồi.'], 400);
        }

        // --- 2. XÁC ĐỊNH LOẠI ITEM & TỶ LỆ HOÀN TIỀN ---
        $refundRate = 0;
        $now = Carbon::now();

        // TRƯỜNG HỢP A: LÀ ĐẶT SÂN (BOOKING)
        if ($item->booking) {
            $bookingDateTime = Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->start_time);

            // Check quá khứ
            if ($bookingDateTime->isPast()) {
                return response()->json(['success' => false, 'message' => 'Không thể hủy lịch đã diễn ra.'], 400);
            };

            $diffMinutes = $now->diffInMinutes($bookingDateTime, false);

            if ($diffMinutes < 120) {
                return response()->json(['success' => false, 'message' => 'Chỉ được hủy trước giờ chơi tối thiểu 2 tiếng.'], 400);
            };

            // > 24h (1440p) hoàn 100%, ngược lại 50%
            $refundRate = ($diffMinutes > 1440) ? 1.0 : 0.5;
        }
        // TRƯỜNG HỢP B: LÀ DỊCH VỤ (VENUE SERVICE)
        elseif ($item->venue_service_id) {
            // Dịch vụ luôn hoàn 100% nếu chưa sử dụng (logic đơn giản hóa)
            $refundRate = 1.0;
        } else {
            return response()->json(['success' => false, 'message' => 'Lỗi dữ liệu: Item không hợp lệ.'], 500);
        }

        // --- 3. GIAO DỊCH DATABASE ---
        DB::transaction(
            function () use ($item, $refundRate) {
                $descriptionLog = '';

                // --- XỬ LÝ RIÊNG TỪNG LOẠI ---

                if ($item->booking) {
                    // A1. Trả lại sân trống
                    Availability::where('court_id', $item->booking->court_id)
                        ->where('slot_id', $item->booking->time_slot_id)
                        ->where('date', $item->booking->date)
                        ->update(['status' => 'open', 'note' => null]);

                    // A2. Cập nhật booking
                    $item->booking->update(['status' => 'cancelled']);

                    // A3. Tạo nội dung log
                    $courtName = $item->booking->court->name ?? 'Sân';
                    $ratePercent = $refundRate * 100;
                    $descriptionLog = "Hoàn tiền hủy {$courtName} (Tỷ lệ: {$ratePercent}%)";
                } elseif ($item->venueService) {
                    // B1. Hoàn trả tồn kho (Restock)
                    $item->venueService->increment('stock', $item->quantity);

                    // B2. Tạo nội dung log
                    $serviceName = $item->venueService->service->name ?? 'Dịch vụ';
                    $descriptionLog = "Hoàn tiền hủy dịch vụ: {$serviceName} (SL: {$item->quantity})";
                }

                // Cập nhật trạng thái Item
                $item->update(['status' => 'refund']);

                // --- XỬ LÝ VÍ (HOÀN TIỀN) ---
                if ($item->ticket && $item->ticket->user_id) {
                    $ticket = $item->ticket;

                    // TÍNH TIỀN: Đơn giá * Số lượng * Tỷ lệ
                    $refundAmount = ($item->unit_price * $item->quantity) * $refundRate;

                    if ($refundAmount > 0) {
                        $wallet = Wallet::where('user_id', $ticket->user_id)->lockForUpdate()->first();

                        if ($wallet) {
                            $beforeBalance = $wallet->balance;
                            $afterBalance = $beforeBalance + $refundAmount;

                            $wallet->update(['balance' => $afterBalance]);

                            WalletLog::create([
                                'wallet_id'      => $wallet->id,
                                'ticket_id'      => $ticket->id,
                                'booking_id'     => $item->booking_id, // Null nếu là service
                                'type'           => 'refund',
                                'amount'         => $refundAmount,
                                'before_balance' => $beforeBalance,
                                'after_balance'  => $afterBalance,
                                'description'    => $descriptionLog,
                            ]);
                        }
                    }

                    // --- TÍNH LẠI TỔNG TIỀN TICKET (RE-CALCULATE) ---
                    // Lấy các item còn active (chưa hủy)
                    $activeItems = $ticket->items()->where('status', 'active')->get();

                    // Tính tổng tiền mới: Sum(đơn giá * số lượng)
                    $newSubtotal = $activeItems->sum(function ($i) {
                        return $i->unit_price * $i->quantity;
                    });

                    $activeCount = $activeItems->count();

                    // Tính lại voucher dựa trên subtotal mới
                    $discountAmount = 0;
                    $promotion = $ticket->promotion;

                    if ($promotion) {
                        // Kiểm tra điều kiện tối thiểu của Voucher (nếu có logic min_order_amount)
                        // Ví dụ: if ($newSubtotal >= $promotion->min_order_amount) { ... }

                        if ($promotion->type === 'VND') {
                            $discountAmount = min($promotion->value, $newSubtotal);
                        } elseif ($promotion->type === '%') {
                            $discountAmount = ($promotion->value / 100) * $newSubtotal;
                            // Nếu có max_discount: $discountAmount = min($discountAmount, $promotion->max_discount);
                        }
                    }

                    // Cập nhật lại Ticket
                    $ticket->update([
                        'subtotal'        => $newSubtotal,
                        'discount_amount' => $discountAmount,
                        'total_amount'    => max(0, $newSubtotal - $discountAmount),
                    ]);

                    // Nếu hủy hết sạch item thì hủy luôn vé
                    if ($activeCount === 0) {
                        $ticket->update([
                            'status'         => 'cancelled',
                        ]);
                    }
                }
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Hủy thành công. Tiền đã được hoàn về ví (nếu có).'
        ]);
    }
}