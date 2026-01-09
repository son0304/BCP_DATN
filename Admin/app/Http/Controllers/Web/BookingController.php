<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\AutoCompleteTicketJob;
use App\Jobs\NotifyOwnerJob;
use App\Models\Ticket;
use App\Models\Venue;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Availability;
use App\Models\MoneyFlow;
use App\Models\Promotion;
use App\Models\Wallet;
use App\Models\WalletLog;
use BaconQrCode\Common\ErrorCorrectionLevel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;

class BookingController extends Controller
{
    public function booking_admin(Request $request)
    {
        $search  = $request->input('search');
        $status  = $request->input('status');
        $venueId = $request->input('venue');

        // Khởi tạo query với các quan hệ cần thiết
        $query = Ticket::with([
            'user',
            'items.booking.court.venue',
            'items.booking.timeSlot',
        ]);

        // Filter: Lọc theo sân (nếu chọn)
        if ($venueId) {
            $query->whereHas('items.booking.court.venue', function ($q) use ($venueId) {
                $q->where('id', $venueId);
            });
        }

        // Filter: Trạng thái
        if ($status) {
            $query->where('status', $status);
        }

        // Filter: Tìm kiếm tên khách hàng
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Lấy dữ liệu và phân trang
        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        // Admin được lấy toàn bộ danh sách sân để lọc
        $venues = Venue::all();

        return view('admin.bookings.index', compact('tickets', 'search', 'status', 'venues', 'venueId'));
    }
    public function booking_venue(Request $request)
    {
        $user = Auth::user();

        // 1. Lấy tham số từ URL
        $search  = $request->input('search');
        $status  = $request->input('status');
        $venueId = $request->input('venue');

        // 2. Khởi tạo Query & Load quan hệ
        $query = Ticket::with([
            'user',
            'items.booking.court.venue',
            'items.booking.timeSlot',
        ]);

        // 3. BẮT BUỘC: Chỉ lấy vé thuộc sân của Owner đang đăng nhập
        $query->whereHas('items.booking.court.venue', function ($q) use ($user, $venueId) {
            $q->where('owner_id', $user->id);
            if ($venueId) {
                $q->where('id', $venueId);
            }
        });

        // 4. XỬ LÝ TÌM KIẾM (Đã thêm tìm theo ID)
        if ($search) {
            $query->where(function ($subQuery) use ($search) {
                // A. Tìm theo Mã Booking
                $subQuery->where('booking_code', 'like', "%{$search}%")

                    // B. Tìm theo ID (Mới thêm)
                    ->orWhere('id', $search)

                    // C. Hoặc tìm theo Tên hoặc SĐT
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // 5. Lọc theo trạng thái
        if ($status) {
            $query->where('status', $status);
        }

        // 6. Lấy dữ liệu & Phân trang
        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // 7. Lấy danh sách sân để lọc
        $venues = Venue::where('owner_id', $user->id)->get();

        // CHỖ QUAN TRỌNG: Nếu là AJAX, chỉ trả về phần HTML của các dòng bảng
        if ($request->ajax()) {
            return view('venue_owner.bookings.index', compact('tickets'))->fragment('table-rows');
        }

        return view('venue_owner.bookings.index', compact('tickets', 'venues', 'search', 'status', 'venueId'));
    }

    public function create()
    {
        $user = Auth::user();
        $venues = Venue::where('owner_id', $user->id)->where('is_active', 1)->with('courts')->get();

        // Lấy khách hàng, nếu không có cột role thì bỏ where('role', 'user')
        $customers = \App\Models\User::orderBy('name', 'asc')->get(['id', 'name', 'phone']);

        $promotions = \App\Models\Promotion::query()
            ->where('start_at', '<=', now())
            ->where(function ($query) {
                $query->where('end_at', '>=', now())->orWhereNull('end_at');
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')->orWhere('usage_limit', 0)->orWhereRaw('used_count < usage_limit');
            })
            ->get();

        $venuesJson = $venues->map(fn($v) => [
            'id' => $v->id,
            'name' => $v->name,
            'courts' => $v->courts->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
        ]);

        return view('venue_owner.bookings.create', [
            'venuesJson' => $venuesJson,
            'promotions' => $promotions,
            'customers' => $customers,
            'ownerName' => $user->name,
            'currentUserId' => $user->id
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'guest_name' => 'nullable|string|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'promotion_id' => 'nullable|exists:promotions,id',
            'discount_amount' => 'nullable|numeric',
            'subtotal' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'payment_status' => 'required|in:unpaid,paid',
            'payment_method' => 'required|in:cash,momo,vnpay',
            'temp_order_id' => 'nullable|string',
            'bookings' => 'required|array|min:1',
            'bookings.*.court_id' => 'required|exists:courts,id',
            'bookings.*.time_slot_id' => 'required|exists:time_slots,id',
            'bookings.*.date' => 'required|date',
            'bookings.*.unit_price' => 'required|numeric',
        ]);

        $timeSlotIds = collect($validated['bookings'])->pluck('time_slot_id')->unique();
        $timeSlotsMap = \App\Models\TimeSlot::whereIn('id', $timeSlotIds)->get()->keyBy('id');

        try {
            $transactionResult = DB::transaction(function () use ($validated, $timeSlotsMap) {
                // 1. Xác định trạng thái thanh toán
                $finalPaymentStatus = $validated['payment_status'];
                if ($finalPaymentStatus === 'paid' && !empty($validated['temp_order_id'])) {
                    if (Cache::get("momo_temp_paid_" . $validated['temp_order_id']) !== 'paid') {
                        $finalPaymentStatus = 'unpaid';
                    }
                }

                // 2. Xử lý thông tin người đặt
                $dbUserId = $validated['user_id'];
                $guestData = null;
                $note = "Đơn đặt bởi Chủ sân.";
                if ($dbUserId === 'guest') {
                    $dbUserId = Auth::id();
                    $guestData = ($validated['guest_name'] ?? 'Khách vãng lai') . ' - ' . ($validated['guest_phone'] ?? 'N/A');
                }

                // 3. Kiểm tra khả dụng sân
                foreach ($validated['bookings'] as $item) {
                    $avail = \App\Models\Availability::where([
                        'court_id' => $item['court_id'],
                        'slot_id' => $item['time_slot_id'],
                        'date' => $item['date']
                    ])->lockForUpdate()->first();

                    if (!$avail || $avail->status !== 'open') throw new \Exception("Sân đã có người đặt hoặc vừa bị đóng.");
                }

                // 4. Tạo Ticket
                $ticket = \App\Models\Ticket::create([
                    'user_id' => $dbUserId,
                    'promotion_id' => $validated['promotion_id'],
                    'subtotal' => $validated['subtotal'],
                    'discount_amount' => $validated['discount_amount'] ?? 0,
                    'total_amount' => $validated['total_amount'],
                    'status' => 'confirmed',
                    'payment_status' => $finalPaymentStatus,
                    'payment_method' => $validated['payment_method'],
                    'note' => $note,
                    'guest' => $guestData,
                    'booking_code' => 'BK-' . now()->format('Ymd') . '-' . rand(1000, 9999)
                ]);

                $schedulerData = [];
                $venue_id = null;

                // 5. Tạo các Booking Item
                foreach ($validated['bookings'] as $item) {
                    $booking = \App\Models\Booking::create([
                        'user_id' => $dbUserId,
                        'court_id' => $item['court_id'],
                        'time_slot_id' => $item['time_slot_id'],
                        'date' => $item['date'],
                        'status' => 'confirmed'
                    ]);

                    \App\Models\Item::create([
                        'ticket_id' => $ticket->id,
                        'booking_id' => $booking->id,
                        'unit_price' => $item['unit_price']
                    ]);

                    \App\Models\Availability::where([
                        'court_id' => $item['court_id'],
                        'slot_id' => $item['time_slot_id'],
                        'date' => $item['date']
                    ])->update(['status' => 'closed', 'note' => 'Đã đặt #' . $ticket->id]);

                    if (!$venue_id) {
                        $venue_id = \App\Models\Court::where('id', $item['court_id'])->value('venue_id');
                    }

                    if (isset($timeSlotsMap[$item['time_slot_id']])) {
                        $slot = $timeSlotsMap[$item['time_slot_id']];
                        $schedulerData[] = [
                            'court_id' => $item['court_id'],
                            'date' => $item['date'],
                            'start_time_str' => $item['date'] . ' ' . $slot->start_time,
                            'end_time_str' => $item['date'] . ' ' . $slot->end_time
                        ];
                    }
                }

                // 6. --- LOGIC MONEY FLOW (SỬA LẠI ĐA HÌNH) ---
                $actualPaid = (float)$validated['total_amount'];
                $discount = (float)($validated['discount_amount'] ?? 0);
                $grossAmount = (float)$validated['subtotal'];
                $commissionRate = 0.10;
                $baseCommission = $grossAmount * $commissionRate;

                $promotion = $validated['promotion_id'] ? \App\Models\Promotion::find($validated['promotion_id']) : null;
                $isVenueVoucher = true;
                if ($promotion && is_null($promotion->venue_id)) {
                    $isVenueVoucher = false;
                }

                if ($isVenueVoucher) {
                    $adminAmount = $baseCommission;
                    $venueOwnerAmount = $actualPaid - $baseCommission;
                } else {
                    $adminAmount = $baseCommission - $discount;
                    $venueOwnerAmount = $grossAmount - $baseCommission;
                }
                if ($venueOwnerAmount < 0) $venueOwnerAmount = 0;

                // SỬ DỤNG QUAN HỆ ĐA HÌNH: Laravel tự điền money_flowable_id và money_flowable_type
                $ticket->moneyFlows()->create([
                    'total_amount' => $grossAmount,
                    'promotion_id' => $validated['promotion_id'],
                    'promotion_amount' => $discount,
                    'venue_id' => $venue_id,
                    'admin_amount' => $adminAmount,
                    'venue_owner_amount' => $venueOwnerAmount,
                    'status' => 'completed', // Vì chủ sân tạo đơn trực tiếp thường được coi là xong luồng tiền
                    'process_status' => 'done',
                    'note' => $note
                ]);

                if (!empty($validated['temp_order_id'])) Cache::forget("momo_temp_paid_" . $validated['temp_order_id']);

                return [
                    'ticket' => $ticket,
                    'scheduler_data' => $schedulerData
                ];
            });

            // 7. --- XỬ LÝ JOB SCHEDULER ---
            $ticket = $transactionResult['ticket'];
            $rawSchedulerData = $transactionResult['scheduler_data'];

            try {
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
                        if ($currentGroup['court_id'] == $item['court_id'] && $currentGroup['end_time_str'] == $item['start_time_str']) {
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

                        $notifyAt = $finalEndTime->copy()->subMinutes(10);
                        if ($notifyAt->gt($now)) \App\Jobs\NotifyOwnerJob::dispatch($ticket)->delay($notifyAt);

                        $completeAt = $finalEndTime->copy()->addMinutes(2);
                        if ($completeAt->gt($now)) \App\Jobs\AutoCompleteTicketJob::dispatch($ticket->id)->delay($completeAt);
                    }
                }
            } catch (\Throwable $e) {
                Log::error("JOBS SCHEDULER FAILED: " . $e->getMessage());
            }

            return redirect()->route('owner.bookings.index')->with('success', "Đã tạo đơn #" . $ticket->id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
    public function checkin(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return redirect()->back()->with('error', 'Không tìm thấy vé này.');
        }

        // Nếu đã check-in rồi thì không cập nhật lại
        if ($ticket->status === 'checkin') {
            return redirect()->back()->with('warning', 'Vé này đã được check-in trước đó.');
        }

        // Cập nhật trạng thái check-in
        $ticket->update([
            'status' => 'checkin',
            // 'checkin_time' => now(), // mở nếu cần lưu giờ check-in
        ]);

        Log::info("✅ Ticket #{$ticket->id} đã được CHECK-IN");

        return redirect()->back()->with('success', 'Check-in thành công!');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['status' => 'required|string|in:pending,completed,cancelled']);

        try {
            $message = DB::transaction(function () use ($request, $id) {
                $ticket = Ticket::with(['items.booking.court.venue'])->lockForUpdate()->findOrFail($id);
                $oldStatus = $ticket->status;
                $newStatus = $request->status;

                if ($oldStatus === $newStatus) return 'Trạng thái không thay đổi.';
                if (in_array($oldStatus, ['completed', 'cancelled'])) throw new \Exception("Không thể cập nhật Ticket đã đóng.");

                $venue = $ticket->items->first()->booking->court->venue;
                $venueOwnerId = $venue->owner_id;

                $ticket->update(['status' => $newStatus]);
                $realBookingIds = $ticket->items->pluck('booking_id')->filter()->unique()->toArray();

                // XỬ LÝ KHI HOÀN THÀNH (COMPLETED)
                if ($newStatus === 'completed') {
                    if (!empty($realBookingIds)) {
                        Booking::whereIn('id', $realBookingIds)->update(['status' => 'completed']);
                    }

                    // Cập nhật MoneyFlow (Truy vấn đa hình)
                    $ticket->moneyFlows()->update(['status' => 'completed']);

                    $amount_payment = $ticket->moneyFlows()->sum('venue_owner_amount');
                    $amount_admin_fee = $ticket->moneyFlows()->sum('admin_amount');

                    $finalAmount = 0;
                    $logMessage = '';
                    $type = '';

                    if ($ticket->payment_method == 'cash') {
                        $finalAmount = -$amount_admin_fee;
                        $type = 'payment';
                        $logMessage = "Trừ phí sàn đơn #{$ticket->id} (Sân: {$venue->name}) - Tiền mặt";
                    } else {
                        $finalAmount = $amount_payment;
                        $type = 'deposit';
                        $logMessage = "Doanh thu online Ticket #{$ticket->id} (Sân: {$venue->name})";
                    }

                    if ($finalAmount != 0) {
                        $wallet = Wallet::firstOrCreate(['user_id' => $venueOwnerId], ['balance' => 0]);
                        $beforeBalance = $wallet->balance;
                        $wallet->increment('balance', $finalAmount);

                        WalletLog::create([
                            'wallet_id'      => $wallet->id,
                            'before_balance' => $beforeBalance,
                            'after_balance'  => $beforeBalance + $finalAmount,
                            'amount'         => $finalAmount,
                            'type'           => $type,
                            'description'    => $logMessage
                        ]);
                    }
                }

                // XỬ LÝ KHI HỦY (CANCELLED)
                if ($newStatus === 'cancelled') {
                    if (!empty($realBookingIds)) {
                        Booking::whereIn('id', $realBookingIds)->update(['status' => 'cancelled']);
                    }
                    $ticket->moneyFlows()->update(['status' => 'cancelled']);
                }

                return 'Cập nhật trạng thái thành công!';
            });

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error("Lỗi cập nhật Ticket #{$id}: " . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
