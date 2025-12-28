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

        // 1. Lấy tham số
        $search  = $request->input('search');
        $status  = $request->input('status');
        $venueId = $request->input('venue');

        // 2. Khởi tạo Query
        $query = Ticket::with([
            'user',
            'items.booking.court.venue',
            'items.booking.timeSlot',
        ]);

        // 3. Lọc Booking: Chỉ lấy vé thuộc sân của owner này
        $query->whereHas('items.booking.court.venue', function ($q) use ($user, $venueId) {
            $q->where('owner_id', $user->id);

            if ($venueId) {
                $q->where('id', $venueId);
            }
        });

        // 4. XỬ LÝ TÌM KIẾM ĐA NĂNG (Tên, SĐT, Mã Booking)
        if ($search) {
            $query->where(function ($subQuery) use ($search) {
                // a. Tìm theo Mã Booking (giả sử cột tên là booking_code trong bảng tickets)
                $subQuery->where('booking_code', 'like', "%{$search}%")

                    // b. Hoặc tìm trong bảng User (Tên hoặc SĐT)
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                        // LƯU Ý: Kiểm tra lại tên cột SĐT trong DB của bạn là 'phone' hay 'phone_number'
                    });
            });
        }

        // Lọc theo trạng thái
        if ($status) {
            $query->where('status', $status);
        }

        // 5. Lấy dữ liệu
        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        // 6. Lọc Dropdown Venue
        $venues = Venue::where('owner_id', $user->id)->get();

        return view('venue_owner.bookings.index', compact('tickets', 'search', 'status', 'venues', 'venueId'));
    }
   
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        // Sử dụng transaction để đảm bảo cả 2 bảng cùng cập nhật thành công
        DB::transaction(function () use ($request, $id) {
            $ticket = Ticket::findOrFail($id);

            // Lưu trạng thái cũ
            $oldStatus = $ticket->status;

            // Cập nhật Ticket
            $ticket->update([
                'status' => $request->status,
                'payment_status' => $request->payment_status,
            ]);

            // Kiểm tra logic: Nếu trước đó chưa hoàn thành -> nay chuyển thành hoàn thành
            if ($oldStatus !== 'completed' && $request->status === 'completed') {

                // Vì bạn chắc chắn MoneyFlow đã có, ta chạy lệnh update thẳng vào DB
                // Cách này gọn hơn, không cần get() ra rồi mới update
                MoneyFlow::where('booking_id', $ticket->id)
                    ->update(['status' => 'completed']);

                Log::info("Đã cập nhật trạng thái MoneyFlow thành completed cho Ticket #{$id}");
            }
        });

        return redirect()->back()->with('success', 'Cập nhật đơn hàng thành công!');
    }

    public function create()
    {
        $user = Auth::user();

        $venues = Venue::where('owner_id', $user->id)
            ->where('is_active', 1)
            ->with('courts')
            ->get();

        $currentUserId = $user->id;
        $ownerName = $user->name;
        $now = Carbon::now();

        $promotions = Promotion::query()
            ->where('start_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->where('end_at', '>=', $now)
                    ->orWhereNull('end_at');
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhere('usage_limit', 0)
                    ->orWhereRaw('used_count < usage_limit');
            })
            ->orderBy('end_at', 'asc')
            ->get();

        $venuesJson = $venues->map(function ($v) {
            return [
                'id' => $v->id,
                'name' => $v->name,
                'courts' => $v->courts->map(function ($c) {
                    return ['id' => $c->id, 'name' => $c->name];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        return view(
            'venue_owner.bookings.create',
            compact('venues', 'promotions', 'venuesJson', 'currentUserId', 'ownerName')
        );
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'promotion_id' => 'nullable|exists:promotions,id',
                'discount_amount' => 'nullable|numeric|min:0',
                'subtotal' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'payment_status' => 'required|in:unpaid,paid',
                'bookings' => 'required|array|min:1',
                'bookings.*.court_id' => 'required|exists:courts,id',
                'bookings.*.time_slot_id' => 'required|exists:time_slots,id',
                'bookings.*.date' => 'required|date|after_or_equal:today',
                'bookings.*.unit_price' => 'required|numeric|min:0',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Validation failed khi tạo booking', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        try {
            $ticket = DB::transaction(function () use ($validated, $request) {

                // ✅ BƯỚC 1: Kiểm tra tất cả availability trước khi tạo
                foreach ($validated['bookings'] as $index => $bookingData) {
                    $availability = Availability::where('court_id', $bookingData['court_id'])
                        ->where('slot_id', $bookingData['time_slot_id'])
                        ->where('date', $bookingData['date'])
                        ->lockForUpdate()
                        ->first();

                    if (!$availability) {
                        throw new \Exception("Không tìm thấy availability cho sân ID {$bookingData['court_id']}, slot {$bookingData['time_slot_id']}, ngày {$bookingData['date']}");
                    }

                    if ($availability->status !== 'open') {
                        throw ValidationException::withMessages([
                            "bookings.{$index}.time_slot_id" => "Khung giờ này đã được đặt hoặc không khả dụng."
                        ]);
                    }
                }

                // ✅ BƯỚC 2: Tạo Ticket - Status luôn là 'confirmed'
                $subtotal = floatval($validated['subtotal']);
                $discount = floatval($validated['discount_amount'] ?? 0);
                $total = floatval($validated['total_amount']);

                $ticket = Ticket::create([
                    'user_id' => $validated['user_id'],
                    'promotion_id' => $validated['promotion_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'status' => 'confirmed', // ✅ Luôn là confirmed
                    'payment_status' => $validated['payment_status'],
                    'booking_code' => 'BK-' . now()->format('Ymd') . '-' . rand(1000, 9999)
                ]);

                Log::info("✅ Ticket #{$ticket->id} đã được tạo", [
                    'ticket_id' => $ticket->id,
                    'user_id' => $validated['user_id'],
                    'status' => 'confirmed',
                    'payment_status' => $validated['payment_status'],
                    'total' => $total
                ]);

                // ✅ BƯỚC 3: Giảm usage_limit của promotion (nếu có)
                if (!empty($validated['promotion_id'])) {
                    $promotion = Promotion::find($validated['promotion_id']);

                    if ($promotion && $promotion->usage_limit > 0) {
                        $promotion->decrement('usage_limit');
                        Log::info("✅ Promotion #{$promotion->id} usage giảm 1");
                    }
                }

                // ✅ BƯỚC 4: Tạo Booking + Item + Cập nhật Availability
                foreach ($validated['bookings'] as $bookingData) {

                    // Tạo Booking - Status luôn là 'confirmed'
                    $createdBooking = Booking::create([
                        'user_id' => $validated['user_id'],
                        'court_id' => $bookingData['court_id'],
                        'time_slot_id' => $bookingData['time_slot_id'],
                        'date' => $bookingData['date'],
                        'status' => 'confirmed', // ✅ Luôn là confirmed
                    ]);

                    Log::info("✅ Booking #{$createdBooking->id} đã tạo", [
                        'court_id' => $bookingData['court_id'],
                        'date' => $bookingData['date'],
                        'time_slot_id' => $bookingData['time_slot_id']
                    ]);

                    // Tạo Item
                    Item::create([
                        'ticket_id' => $ticket->id,
                        'booking_id' => $createdBooking->id,
                        'unit_price' => floatval($bookingData['unit_price']),
                        'discount_amount' => 0,
                    ]);

                    // Cập nhật Availability
                    $updated = Availability::where('court_id', $bookingData['court_id'])
                        ->where('slot_id', $bookingData['time_slot_id'])
                        ->where('date', $bookingData['date'])
                        ->update([
                            'status' => 'closed',
                            'note' => 'Đã đặt qua ticket #' . $ticket->id,
                        ]);

                    if ($updated === 0) {
                        throw new \Exception("Không thể cập nhật availability cho booking #{$createdBooking->id}");
                    }

                    Log::info("✅ Availability đã đóng", [
                        'court_id' => $bookingData['court_id'],
                        'date' => $bookingData['date'],
                        'slot_id' => $bookingData['time_slot_id']
                    ]);
                }

                return $ticket;
            });

            Log::info("🎉 Tạo ticket thành công", [
                'ticket_id' => $ticket->id,
                'payment_status' => $validated['payment_status']
            ]);

            $statusText = $validated['payment_status'] === 'paid'
                ? '✅ Đã thanh toán'
                : '⏳ Chưa thanh toán';

            return redirect()->route('owner.bookings.index')
                ->with('success', "Tạo đơn đặt sân #{$ticket->id} thành công! Trạng thái: {$statusText}");
        } catch (\Exception $e) {
            Log::error('❌ Lỗi khi tạo ticket', [
                'message' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Đã có lỗi hệ thống: ' . $e->getMessage())
                ->withInput();
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
}
