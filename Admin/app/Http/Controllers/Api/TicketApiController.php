<?php

namespace App\Http\Controllers\Api;

use App\Events\DataCreated;
use App\Http\Controllers\Controller;
use App\Jobs\AutoCompleteTicketJob;
use App\Jobs\NotifyOwnerJob;
use App\Mail\Booking_Status;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Item;
use App\Models\MoneyFlow;
use App\Models\Notification;
use App\Models\Promotion;
use App\Models\Ticket;
use App\Models\TimeSlot;
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


    protected $namChannel = 'booking';

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

        // ---------------------------------------------------------
        // BƯỚC 1: VALIDATE DỮ LIỆU ĐẦU VÀO
        // ---------------------------------------------------------
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'promotion_id' => 'nullable|exists:promotions,id',
            'discount_amount' => 'nullable|numeric|min:0',

            // Validate Bookings
            'bookings' => 'required|array|min:1',
            'bookings.*.court_id' => 'required|exists:courts,id',
            'bookings.*.time_slot_id' => 'required|exists:time_slots,id',
            'bookings.*.date' => 'required|date|after_or_equal:today',
            'bookings.*.unit_price' => 'required|numeric|min:0',
            'bookings.*.sale_price' => 'required|numeric|min:0',

            // Validate Services
            'services' => 'nullable|array',
            'services.*.venue_service_id' => 'required|exists:venue_services,id',
            'services.*.quantity' => 'required|integer|min:1',
        ]);

        // Lấy thông tin TimeSlot trước để dùng trong logic
        $timeSlotIds = collect($validated['bookings'])->pluck('time_slot_id')->unique();
        $timeSlotsMap = TimeSlot::whereIn('id', $timeSlotIds)->get()->keyBy('id');

        // Biến lưu kết quả transaction để dùng bên ngoài
        $transactionResult = null;

        try {
            // ---------------------------------------------------------
            // BƯỚC 2: TRANSACTION - XỬ LÝ DỮ LIỆU QUAN TRỌNG (CRITICAL)
            // Nếu lỗi ở đây thì Rollback toàn bộ, không lưu gì cả.
            // ---------------------------------------------------------
            $transactionResult = DB::transaction(function () use ($validated, $timeSlotsMap) {

                $bookingTotal = 0;
                $serviceTotal = 0;
                $bookingItemsPayload = [];
                $serviceItemsPayload = [];
                $schedulerData = [];

                // --- 2.1: Xử lý & Khóa Booking ---
                foreach ($validated['bookings'] as $bookingData) {
                    // Lock dữ liệu để tránh trùng lịch (Race Condition)
                    $availability = Availability::where('court_id', $bookingData['court_id'])
                        ->where('slot_id', $bookingData['time_slot_id'])
                        ->where('date', $bookingData['date'])
                        ->lockForUpdate()
                        ->first();

                    if (!$availability || $availability->status !== 'open') {
                        throw ValidationException::withMessages([
                            'bookings' => "Sân ID {$bookingData['court_id']} ngày {$bookingData['date']} khung giờ này vừa có người đặt xong."
                        ]);
                    }

                    $finalPrice = ($bookingData['sale_price'] > 0) ? $bookingData['sale_price'] : $bookingData['unit_price'];
                    $bookingTotal += $finalPrice;

                    $bookingItemsPayload[] = [
                        'data' => $bookingData,
                        'final_price' => $finalPrice
                    ];

                    // Chuẩn bị dữ liệu cho Job Scheduler
                    if (isset($timeSlotsMap[$bookingData['time_slot_id']])) {
                        $slot = $timeSlotsMap[$bookingData['time_slot_id']];
                        $schedulerData[] = [
                            'court_id' => $bookingData['court_id'],
                            'date' => $bookingData['date'],
                            'start_time_str' => $bookingData['date'] . ' ' . $slot->start_time,
                            'end_time_str' => $bookingData['date'] . ' ' . $slot->end_time
                        ];
                    }
                }

                // --- 2.2: Xử lý Services ---
                if (!empty($validated['services'])) {
                    foreach ($validated['services'] as $srvItem) {
                        $venueService = VenueService::where('id', $srvItem['venue_service_id'])
                            ->lockForUpdate()
                            ->first();

                        if ($venueService->stock < $srvItem['quantity']) {
                            throw ValidationException::withMessages(['services' => "Dịch vụ {$venueService->name} không đủ tồn kho."]);
                        }

                        $venueService->decrement('stock', $srvItem['quantity']);

                        $itemTotal = $venueService->price * $srvItem['quantity'];
                        $serviceTotal += $itemTotal;

                        $serviceItemsPayload[] = [
                            'venue_service' => $venueService,
                            'quantity' => $srvItem['quantity'],
                            'unit_price' => $venueService->price,
                            'total_price' => $itemTotal
                        ];
                    }
                }

                // --- 2.3: Tính tổng tiền & Tạo Ticket ---
                $subtotal = $bookingTotal + $serviceTotal;
                $discount = $validated['discount_amount'] ?? 0;
                $totalAmount = max(0, $subtotal - $discount);

                // ====================================================
                // GIAI ĐOẠN 2: LƯU DATABASE
                // ====================================================
                if (!empty($validated['promotion_id'])) {
                    $promotion = Promotion::lockForUpdate()->find($validated['promotion_id']);

                    if (!$promotion) {
                        throw ValidationException::withMessages([
                            'promotion' => 'Voucher không tồn tại trong hệ thống.'
                        ]);
                    }

                    // Sử dụng method isActive() thay vì thuộc tính is_active
                    if (!$promotion->isActive()) {
                        throw ValidationException::withMessages([
                            'promotion' => 'Voucher không hợp lệ, đã hết hạn hoặc đã hết lượt sử dụng.'
                        ]);
                    }

                    // Kiểm tra giá trị đơn hàng tối thiểu (nếu có)
                    if (isset($promotion->min_order_value) && $subtotal < $promotion->min_order_value) {
                        throw ValidationException::withMessages([
                            'promotion' => "Đơn hàng tối thiểu " . number_format($promotion->min_order_value, 0, ',', '.') . "đ để sử dụng voucher này."
                        ]);
                    }
                }

                if ($promotion->used_count >= $promotion->usage_limit) {
                    throw ValidationException::withMessages([
                        'promotion' => 'Voucher đã hết lượt sử dụng.'
                    ]);
                }

                $now = Carbon::now();
                if ($promotion->start_date && $now->lt(Carbon::parse($promotion->start_date))) {
                    throw ValidationException::withMessages([
                        'promotion' => 'Voucher chưa đến thời gian sử dụng.'
                    ]);
                }

                if ($promotion->end_date && $now->gt(Carbon::parse($promotion->end_date))) {
                    throw ValidationException::withMessages([
                        'promotion' => 'Voucher đã hết hạn sử dụng.'
                    ]);
                }

                if ($promotion->min_order_value && $subtotal < $promotion->min_order_value) {
                    throw ValidationException::withMessages([
                        'promotion' => "Đơn hàng tối thiểu " . number_format($promotion->min_order_value, 0, ',', '.') . "đ để sử dụng voucher này."
                    ]);
                }

                // 2.1 Tạo Ticket
                $ticket = Ticket::create([
                    'user_id' => $validated['user_id'],
                    'promotion_id' => $validated['promotion_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'booking_code' => 'BK-' . now()->format('Ymd') . '-' . rand(1000, 9999)
                ]);

                if (!empty($validated['promotion_id'])) {
                    Promotion::where('id', $validated['promotion_id'])
                        ->increment('used_count');
                }

                // --- 2.4: Tạo các Item và Booking chi tiết ---
                foreach ($bookingItemsPayload as $payload) {
                    $bData = $payload['data'];
                    $booking = Booking::create([
                        'user_id' => $validated['user_id'],
                        'court_id' => $bData['court_id'],
                        'time_slot_id' => $bData['time_slot_id'],
                        'date' => $bData['date'],
                        'status' => 'pending',
                        'ticket_id' => $ticket->id // Nếu bảng bookings có ticket_id
                    ]);

                    // Cập nhật trạng thái sân thành đã đóng
                    Availability::where('court_id', $bData['court_id'])
                        ->where('slot_id', $bData['time_slot_id'])
                        ->where('date', $bData['date'])
                        ->update(['status' => 'closed', 'note' => 'Ticket #' . $ticket->id]);

                    Item::create([
                        'ticket_id'        => $ticket->id,
                        'item_type'        => 'booking',
                        'booking_id'       => $booking->id,
                        'unit_price'       => $bData['unit_price'],
                        'quantity'         => 1,
                        'discount_amount'  => $bData['unit_price'] - $payload['final_price'],
                        'total_price'      => $payload['final_price']
                    ]);
                }

                foreach ($serviceItemsPayload as $payload) {
                    Item::create([
                        'ticket_id'        => $ticket->id,
                        'item_type'        => 'service',
                        'venue_service_id' => $payload['venue_service']->id,
                        'unit_price'       => $payload['unit_price'],
                        'quantity'         => $payload['quantity'],
                        'discount_amount'  => 0,
                        'total_price'      => $payload['total_price']
                    ]);
                }

                return [
                    'ticket' => $ticket,
                    'scheduler_data' => $schedulerData
                ];
            }); // KẾT THÚC TRANSACTION

        } catch (ValidationException $e) {
            // Lỗi validate trả về ngay cho client
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            // Lỗi Database/Code nghiêm trọng
            Log::error('CRITICAL ERROR - TICKET CREATION: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi lưu đơn hàng. Vui lòng thử lại.',
            ], 500);
        }

        // =========================================================
        // BƯỚC 3: TÁC VỤ PHỤ (NON-CRITICAL)
        // Code chạy đến đây nghĩa là Đơn Hàng Đã Tạo Thành Công 100%.
        // Mọi lỗi ở dưới đây chỉ nên Log lại, KHÔNG ĐƯỢC return lỗi cho client.
        // =========================================================

        $ticket = $transactionResult['ticket'];
        $rawSchedulerData = $transactionResult['scheduler_data'];

        try {
            // --- 3.1: Xử lý Job Scheduler (Thông báo hết giờ) ---
            if (!empty($rawSchedulerData)) {
                $sortedData = collect($rawSchedulerData)->sort(function ($a, $b) {
                    if ($a['court_id'] != $b['court_id']) return $a['court_id'] <=> $b['court_id'];
                    return strcmp($a['start_time_str'], $b['start_time_str']);
                })->values();

                $groups = [];
                $currentGroup = null;

                foreach ($sortedData as $item) {
                    if (!$currentGroup) {
                        $currentGroup = $item;
                        continue;
                    }
                    $isSameCourt = ($currentGroup['court_id'] == $item['court_id']);
                    $isContinuous = ($currentGroup['end_time_str'] == $item['start_time_str']);

                    if ($isSameCourt && $isContinuous) {
                        $currentGroup['end_time_str'] = $item['end_time_str'];
                    } else {
                        $groups[] = $currentGroup;
                        $currentGroup = $item;
                    }
                }
                if ($currentGroup) $groups[] = $currentGroup;

                foreach ($groups as $group) {
                    $finalEndTime = Carbon::parse($group['end_time_str']);
                    $now = Carbon::now();

                    // Job báo trước 10p
                    $notifyAt = $finalEndTime->copy()->subMinutes(10);
                    if ($notifyAt->gt($now)) {
                        NotifyOwnerJob::dispatch($ticket)->delay($notifyAt);
                    }

                    // Job hoàn thành sau 2p
                    $completeAt = $finalEndTime->copy()->addMinutes(2);
                    if ($completeAt->gt($now)) {
                        AutoCompleteTicketJob::dispatch($ticket->id)->delay($completeAt);
                    }
                }
            }

            // --- 3.2: Load quan hệ để trả về hoặc broadcast ---
            $ticket->load([
                'user:id,name,phone',
                'items.booking.court',
                'items.booking.timeSlot',
                'items.venueService.service'
            ]);

            // --- 3.3: Broadcast (Thường xuyên gây lỗi nếu mạng lag) ---
            try {
                broadcast(new DataCreated($ticket, $this->namChannel, 'ticket.created'));
            } catch (\Throwable $bcEx) {
                Log::warning("Broadcast failed for Ticket #{$ticket->id}: " . $bcEx->getMessage());
            }
        } catch (\Throwable $secondaryError) {
            // Chỉ ghi log, không làm ảnh hưởng response
            Log::error("TICKET CREATED BUT SECONDARY TASKS FAILED (ID: {$ticket->id}): " . $secondaryError->getMessage());
        }

        // =========================================================
        // BƯỚC 4: TRẢ VỀ KẾT QUẢ THÀNH CÔNG
        // =========================================================
        return response()->json([
            'success' => true,
            'message' => 'Tạo đơn hàng thành công!',
            'data' =>  $ticket->id
        ]);
    }



    public function destroyItem($id)
    {
        // 1. Lấy thông tin Item và Ticket
        $item = Item::with(['booking.court', 'ticket.promotion', 'venueService.service'])->find($id);

        if (!$item) return response()->json(['success' => false, 'message' => 'Mục này không tồn tại.'], 404);
        if ($item->ticket->status === 'cancelled') return response()->json(['success' => false, 'message' => 'Vé này đã bị hủy toàn bộ.'], 400);
        if ($item->status === 'refund') return response()->json(['success' => false, 'message' => 'Đã hoàn tiền rồi.'], 400);

        $now = Carbon::now();
        $refundRate = 0;
        $logDescription = "";

        // --- BƯỚC 2: XÁC ĐỊNH CHÍNH SÁCH HOÀN TIỀN (POLICY) ---
        if ($item->venue_service_id) {
            $refundRate = 1.0;
            $name = $item->venueService->service->name ?? 'Dịch vụ';
            $logDescription = "Hủy: {$name} (SL: {$item->quantity})";
        } elseif ($item->booking) {
            $bookingTime = Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->start_time);

            if ($bookingTime->isPast()) return response()->json(['success' => false, 'message' => 'Sân đang/đã đá, không thể hủy.'], 400);

            $minutes = $now->diffInMinutes($bookingTime, false);
            if ($minutes < 120) return response()->json(['success' => false, 'message' => 'Phải hủy trước giờ đá ít nhất 2 tiếng.'], 400);

            $refundRate = ($minutes > 1440) ? 1.0 : 0.5;
            $name = $item->booking->court->name ?? 'Sân';
            $logDescription = "Hủy: {$name} (Hoàn " . ($refundRate * 100) . "%)";
        }

        // --- BƯỚC 3: THỰC HIỆN GIAO DỊCH (TRANSACTION) ---
        try {
            DB::transaction(function () use ($item, $refundRate, $logDescription) {
                // 3.1 Trả tài nguyên (Kho / Lịch sân)
                if ($item->venue_service_id) {
                    $item->venueService->increment('stock', $item->quantity);
                } elseif ($item->booking) {
                    Availability::where([
                        'court_id' => $item->booking->court_id,
                        'slot_id' => $item->booking->time_slot_id,
                        'date' => $item->booking->date
                    ])->update(['status' => 'open', 'note' => null]);
                    $item->booking->update(['status' => 'cancelled']);
                }

                // 3.2 Đổi trạng thái item
                $item->update(['status' => 'refund']);

                // 3.3 Tính toán lại Ticket (Số tiền và Voucher)
                $ticket = $item->ticket;
                $remainingItems = $ticket->items()->where('status', '!=', 'refund')->get();

                // Tính Subtotal mới
                $newSubtotal = 0;
                foreach ($remainingItems as $remItem) {
                    $newSubtotal += ($remItem->unit_price * $remItem->quantity);
                }

                // Tính Discount mới
                $newDiscountAmount = 0;
                if ($ticket->promotion) {
                    if ($ticket->promotion->type == '%') {
                        $newDiscountAmount = ($ticket->promotion->value / 100) * $newSubtotal;
                    } else {
                        $newDiscountAmount = min($ticket->promotion->value, $newSubtotal);
                    }
                }

                // Tính tiền hoàn Ví cho khách
                $originalItemValue = $item->unit_price * $item->quantity;
                $refundItemAmount = $originalItemValue * $refundRate;

                $oldDiscount = $ticket->discount_amount;
                $voucherClawback = $oldDiscount - $newDiscountAmount; // Voucher bị thu hồi

                $finalRefundToWallet = $refundItemAmount - $voucherClawback;
                $newTotalAmount = max(0, $newSubtotal - $newDiscountAmount); // Tổng tiền thanh toán mới

                // Cập nhật Ticket
                $ticket->update([
                    'subtotal' => $newSubtotal,
                    'discount_amount' => $newDiscountAmount,
                    'total_amount' => $newTotalAmount
                ]);

                // =========================================================
                // 3.4 [QUAN TRỌNG] CẬP NHẬT MONEY FLOW ĐẦY ĐỦ
                // =========================================================
                $moneyFlow = MoneyFlow::where('booking_id', $ticket->id)->first();

                if ($moneyFlow) {
                    // Tính tỷ lệ giảm để chia lại tiền Admin/Venue
                    // Nếu tổng cũ là 100k, tổng mới là 80k -> Tỷ lệ giữ lại là 0.8
                    $oldTotal = $moneyFlow->total_amount;

                    // Tránh chia cho 0
                    $ratio = ($oldTotal > 0) ? ($newTotalAmount / $oldTotal) : 0;

                    $newAdminAmount = $moneyFlow->admin_amount * $ratio;
                    $newVenueAmount = $moneyFlow->venue_owner_amount * $ratio;

                    // Update toàn bộ các trường tiền
                    $moneyFlow->update([
                        'total_amount'       => $newTotalAmount,
                        'promotion_amount'   => $newDiscountAmount,
                        'admin_amount'       => $newAdminAmount,
                        'venue_owner_amount' => $newVenueAmount,
                    ]);
                }
                // =========================================================

                // 3.5 Cộng tiền ví & Ghi log
                if ($finalRefundToWallet > 0 && $ticket->user_id) {
                    $wallet = Wallet::where('user_id', $ticket->user_id)->lockForUpdate()->first();
                    if ($wallet) {
                        $before = $wallet->balance;
                        $wallet->increment('balance', $finalRefundToWallet);

                        $desc = $logDescription;
                        if ($voucherClawback > 0) {
                            $desc .= " | Thu hồi voucher: -" . number_format($voucherClawback, 0, ',', '.') . "đ";
                        }

                        WalletLog::create([
                            'wallet_id' => $wallet->id,
                            'ticket_id' => $ticket->id,
                            'type' => 'refund',
                            'amount' => $finalRefundToWallet,
                            'before_balance' => $before,
                            'after_balance' => $before + $finalRefundToWallet,
                            'description' => $desc,
                        ]);
                    }
                }

                // 3.6 Nếu hết sạch món -> Hủy toàn bộ
                if ($remainingItems->isEmpty()) {
                    $ticket->update(['status' => 'cancelled']);

                    if ($ticket->promotion_id) {
                        Promotion::where('id', $ticket->promotion_id)
                            ->where('used_count', '>', 0)
                            ->decrement('used_count');
                    }

                    // Cập nhật MoneyFlow thành cancelled
                    if ($moneyFlow) {
                        $moneyFlow->update(['status' => 'cancelled']);
                    }
                }
            });

            // Broadcast update
            $item->ticket->load(['items.venueService', 'items.booking']);
            broadcast(new \App\Events\DataUpdated($item->ticket, $this->namChannel, 'ticket.updated'));

            return response()->json(['success' => true, 'message' => 'Hủy thành công.']);
        } catch (\Throwable $e) {
            Log::error("Refund Item Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống.'], 500);
        }
    }

    public function destroyTicket($id)

    {
        $ticket = Ticket::with(['items.booking.timeSlot', 'items.booking.court', 'items.venueService'])->findOrFail($id);

         if ($ticket->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Vé này đã bị hủy.'], 400);
         }

        $now = Carbon::now();

        // --- BƯỚC 1: CHECK ĐIỀU KIỆN (Chỉ cần 1 món vi phạm là chặn hủy cả vé) ---
        foreach ($ticket->items as $item) {
            if ($item->status === 'refund') continue;
            if ($item->booking) {
                $bookingTime = Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->start_time);
                if ($bookingTime->isPast()) return response()->json(['success' => false, 'message' => "Có sân đã diễn ra, không thể hủy toàn bộ vé."], 400);
                if ($now->diffInMinutes($bookingTime, false) < 120) return response()->json(['success' => false, 'message' => "Quá trễ để hủy vé (có sân < 2 tiếng)."], 400);
            }
        }

        // --- BƯỚC 2: XỬ LÝ HỦY TOÀN BỘ ---
        DB::transaction(function () use ($ticket, $now) {
            $totalRawRefund = 0; // Tổng tiền hoàn từ các món (chưa trừ voucher)
            $refundDetails = [];

            foreach ($ticket->items as $item) {
                if ($item->status === 'refund') continue;

                // Xử lý hoàn trả tài nguyên & tính tiền
                if ($item->booking) {
                    // Mở lịch
                    Availability::where([
                        'court_id' => $item->booking->court_id,
                        'slot_id' => $item->booking->time_slot_id,
                        'date' => $item->booking->date
                    ])->update(['status' => 'open', 'note' => null]);

                    $item->booking->update(['status' => 'cancelled']);

                    // Tính tiền
                    $bookingTime = Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->start_time);
                    $rate = ($now->diffInMinutes($bookingTime, false) > 1440) ? 1.0 : 0.5;

                    $totalRawRefund += ($item->unit_price * $item->quantity) * $rate;
                    $refundDetails[] = "Sân (" . ($rate * 100) . "%)";
                } elseif ($item->venue_service_id) {
                    // Trả kho
                    $item->venueService->increment('stock', $item->quantity);

                    $totalRawRefund += ($item->unit_price * $item->quantity); // Service hoàn 100%
                    $refundDetails[] = "Dịch vụ (100%)";
                }

                $item->update(['status' => 'refund']);
            }

            // Tính toán hoàn tiền ví
            // Khi hủy cả vé: Thu hồi TOÀN BỘ voucher đã giảm
            // Tiền hoàn = Tổng tiền các món (sau khi phạt) - Voucher đã dùng
            $voucherAmount = $ticket->discount_amount ?? 0;
            $finalRefundToWallet = max(0, $totalRawRefund - $voucherAmount);

            // Cộng ví
            if ($ticket->user_id && $finalRefundToWallet > 0) {
                $wallet = Wallet::where('user_id', $ticket->user_id)->lockForUpdate()->first();
                if ($wallet) {
                    $before = $wallet->balance;
                    $wallet->increment('balance', $finalRefundToWallet);

                    // Log mô tả
                    $desc = "Hủy vé #{$ticket->id}. Chi tiết: " . implode(', ', array_unique($refundDetails));
                    if ($voucherAmount > 0) {
                        $desc .= " | Thu hồi voucher: -" . number_format($voucherAmount, 0, ',', '.') . "đ";
                    }

                    WalletLog::create([
                        'wallet_id' => $wallet->id,
                        'ticket_id' => $ticket->id,
                        'type' => 'refund',
                        'amount' => $finalRefundToWallet,
                        'before_balance' => $before,
                        'after_balance' => $before + $finalRefundToWallet,
                        'description' => $desc,
                    ]);
                }
            }

            // Cập nhật Ticket: ĐƯA TẤT CẢ VỀ 0 & CANCELLED
            $ticket->update([
                'status' => 'cancelled',
                'subtotal' => 0,
                'discount_amount' => 0,
                'total_amount' => 0
            ]);

            if ($ticket->promotion_id) {
                Promotion::where('id', $ticket->promotion_id)
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');
            }

            // Cập nhật MoneyFlow
            MoneyFlow::where('booking_id', $ticket->id)->update(['status' => 'cancelled']);
        });

        broadcast(new \App\Events\DataUpdated($ticket, $this->namChannel, 'ticket.updated'));
        return response()->json(['success' => true, 'message' => 'Hủy vé thành công.']);
    }
}
