<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Venue;
use App\Models\Booking;
use App\Models\Item;
use App\Models\Availability;
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
        $user = Auth::user(); // Lấy người dùng hiện tại

        // 1. Lấy tham số từ URL
        $search  = $request->input('search');
        $status  = $request->input('status');
        $venueId = $request->input('venue');

        // 2. Khởi tạo Query Booking
        $query = Ticket::with([
            'user',
            'items.booking.court.venue',
            'items.booking.timeSlot',
        ]);

        // 3. Lọc Booking: Chỉ lấy vé có chứa sân của owner_id này
        $query->whereHas('items.booking.court.venue', function ($q) use ($user, $venueId) {
            $q->where('owner_id', $user->id); // <--- QUAN TRỌNG: Chỉ lấy sân của tôi

            if ($venueId) {
                $q->where('id', $venueId); // Lọc thêm theo sân cụ thể nếu chọn
            }
        });

        // 4. Các bộ lọc khác (Trạng thái, Tìm kiếm)
        if ($status) {
            $query->where('status', $status);
        }
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // 5. Lấy dữ liệu Booking
        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        // 6. Lọc Dropdown Venue: Chỉ lấy danh sách sân của owner này
        $venues = Venue::where('owner_id', $user->id)->get();
        // dd($tickets);// <--- PHẦN BẠN YÊU CẦU

        return view('venue_owner.bookings.index', compact('tickets', 'search', 'status', 'venues', 'venueId'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status,
        ]);
        if ($request->status == 3 && $oldStatus != 3) {
        
        // 1. Lấy khung giờ kết thúc muộn nhất của đơn này
        // (Sắp xếp theo ngày và giờ kết thúc của slot cuối cùng)
        $lastItem = $ticket->items->map(function($item) {
            return [
                'full_end_time' => Carbon::parse($item->booking->date . ' ' . $item->booking->timeSlot->end_time)
            ];
        })->sortByDesc('full_end_time')->first();

        $finalEndTime = $lastItem['full_end_time'];

        // 2. Thiết lập thời gian thực thi
        $now = now();
        
        // Hẹn giờ thông báo: End Time - 10 phút
        $notifyAt = $finalEndTime->copy()->subMinutes(10);
        
        if ($notifyAt->gt($now)) {
            // Gửi ticket vào hàng đợi, delay đến lúc cần thông báo
            NotifyOwnerJob::dispatch($ticket)->delay($notifyAt);
        }

        // Hẹn giờ tự động hoàn thành: Đúng lúc End Time
        if ($finalEndTime->gt($now)) {
            AutoCompleteTicketJob::dispatch($ticket)->delay($finalEndTime);
        }
    }


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
                    'payment_status' => $validated['payment_status'], // ✅ Lấy từ form
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

    private function generateMomoPaymentRedirect(int $ticketId, Request $request)
    {
        try {
            $ticket = Ticket::find($ticketId);

            if (!$ticket) {
                return redirect()->route('owner.bookings.index')
                    ->with('error', "Không tìm thấy vé cần thanh toán.");
            }

            if ($ticket->payment_status === 'paid') {
                return redirect()->route('owner.bookings.index')
                    ->with('success', "Vé #{$ticketId} đã được thanh toán.");
            }

            $host = $request->getHost();
            $isLocalhost = in_array($host, ['localhost', '127.0.0.1'])
                || strpos($host, 'localhost') !== false;

            if ($isLocalhost) {
                $redirectUrl = env('MOMO_REDIRECT_URL');

                if (!$redirectUrl) {
                    throw new \Exception('❌ Chưa cấu hình MOMO_REDIRECT_URL_LOCAL trong .env');
                }

                Log::info("🔵 Môi trường: LOCAL | redirectUrl = {$redirectUrl}");
            }

            // ✅ Config MoMo
            $endpoint = env('MOMO_ENDPOINT');
            $partnerCode = env('MOMO_PARTNER_CODE');
            $accessKey = env('MOMO_ACCESS_KEY');
            $secretKey = env('MOMO_SECRET_KEY');
            $ipnUrl = env('MOMO_IPN_URL');

            if (!$accessKey || !$secretKey || !$ipnUrl) {
                throw new \Exception('❌ Chưa đầy đủ config MoMo (ACCESS_KEY, SECRET_KEY, IPN_URL)');
            }

            // ✅ Tạo orderId và requestId
            $orderId = $ticket->id . '_' . time();
            $requestId = $orderId;
            $amount = (int) round($ticket->total_amount);
            $orderInfo = "Thanh toán vé #{$ticket->id}";
            $extraData = "";
            $requestType = "payWithQR";

            // ✅ Tạo Signature
            $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}"
                . "&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}"
                . "&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            // ✅ Payload gửi MoMo
            $data = [
                'partnerCode' => $partnerCode,
                'partnerName' => "BCP Sports",
                'storeId' => "BCP_Store",
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature
            ];

            Log::info("📤 Gửi MoMo (Temp QR với payWithQR)", ['orderId' => $tempOrderId, 'amount' => $amount, 'requestType' => $requestType]);

            // ✅ Gọi API MoMo
            $response = Http::timeout(10)->post($endpoint, $data);

            if (!$response->successful()) {
                throw new \Exception("MoMo API trả lỗi: HTTP {$response->status()}");
            }

            $responseData = $response->json();

            Log::info("📥 Response từ MoMo", $responseData);

            // ✅ Kiểm tra payUrl
            if (isset($responseData['payUrl']) && !empty($responseData['payUrl'])) {
                Log::info("✅ Chuyển hướng MoMo thành công", [
                    'payUrl' => $responseData['payUrl']
                ]);

                return redirect($responseData['payUrl']);
            }

            // ❌ Lỗi từ MoMo
            $errorMsg = $responseData['message'] ?? $responseData['localMessage'] ?? 'Lỗi không xác định từ MoMo';

            Log::error("❌ MoMo không trả payUrl", [
                'response' => $responseData,
                'resultCode' => $responseData['resultCode'] ?? null
            ]);

            return redirect()->route('owner.bookings.index')
                ->with('error', "Lỗi MoMo: {$errorMsg}");
        } catch (\Exception $e) {
            Log::error("❌ Exception trong generateMomoPaymentRedirect", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('owner.bookings.index')
                ->with('error', "Lỗi hệ thống: {$e->getMessage()}");
        }
    }

    public function generateTempQR(Request $request)
    {
        try {
            $validated = $request->validate([
                'total_amount' => 'required|numeric|min:1000',
                'temp_order_id' => 'required|string'
            ]);

            $tempOrderId = $validated['temp_order_id'];
            $amount = (int) round($validated['total_amount']);

            if ($amount < 1000) {
                throw new \Exception('Số tiền phải >= 1,000 VNĐ');
            }
            if ($amount > 50000000) {
                throw new \Exception('Số tiền vượt quá giới hạn MoMo (50 triệu VNĐ)');
            }

            $redirectUrl = env('MOMO_REDIRECT_URL');
            $ipnUrl = env('MOMO_IPN_URL');

            if (!$redirectUrl || !$ipnUrl) {
                throw new \Exception('Thiếu MOMO_REDIRECT_URL hoặc MOMO_IPN_URL');
            }

            // ✅ Config MoMo
            $endpoint = env('MOMO_ENDPOINT');
            $partnerCode = env('MOMO_PARTNER_CODE');
            $accessKey = env('MOMO_ACCESS_KEY');
            $secretKey = env('MOMO_SECRET_KEY');
            $ipnUrl = env('MOMO_IPN_URL');

            if (!$endpoint || !$partnerCode || !$accessKey || !$secretKey || !$ipnUrl) {
                throw new \Exception('Thiếu config MoMo trong .env');
            }

            $requestId = $tempOrderId;
            $orderInfo = "Thanh toán đặt sân (Tạm thời)";
            $extraData = base64_encode(json_encode([
                'temp_order_id' => $tempOrderId,
                'created_at' => now()->toIso8601String()
            ]));

            // ✅ Sử dụng captureWallet cho MoMo Test
            $requestType = "captureWallet";

            // ✅ Tạo Signature
            $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}"
                . "&orderId={$tempOrderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}"
                . "&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            // ✅ Payload gửi MoMo
            $data = [
                'partnerCode' => $partnerCode,
                'partnerName' => "BCP Sports",
                'storeId' => "BCP_Store",
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $tempOrderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature
            ];

            Log::info("📤 Request gửi MoMo", [
                'endpoint' => $endpoint,
                'orderId' => $tempOrderId,
                'amount' => $amount,
                'requestType' => $requestType
            ]);

            // ✅ Call MoMo API
            $response = Http::timeout(10)->post($endpoint, $data);

            if (!$response->successful()) {
                Log::error("❌ MoMo trả lỗi", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'json' => $response->json()
                ]);

                $errorMsg = $response->json()['message'] ?? $response->json()['localMessage'] ?? 'Lỗi không xác định';
                throw new \Exception("MoMo API error: {$response->status()} - {$errorMsg}");
            }

            $responseData = $response->json();
            Log::info("📥 Response từ MoMo", $responseData);

            if (!isset($responseData['payUrl'])) {
                Log::error("❌ MoMo không trả payUrl", ['response' => $responseData]);
                throw new \Exception($responseData['message'] ?? 'MoMo không trả về Deep Link');
            }

            $deepLinkMomo = $responseData['payUrl'];

            // ✅ TẠO QR CODE 
            $qrCode = \Endroid\QrCode\QrCode::create($deepLinkMomo)
                ->setEncoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
                ->setSize(300)
                ->setMargin(10);

            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);

            // ✅ Chuyển sang Base64 Data URI
            $qrCodeBase64 = $result->getDataUri();

            Log::info("✅ Tạo QR tạm thời thành công", [
                'orderId' => $tempOrderId,
                'amount' => $amount
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'qr_code_url' => $qrCodeBase64,
                    'pay_url' => $deepLinkMomo,
                    'order_id' => $tempOrderId,
                    'amount' => $amount,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Lỗi generateTempQR: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkTempPayment(Request $request)
    {
        try {
            $tempOrderId = $request->input('temp_order_id');

            if (!$tempOrderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing temp_order_id'
                ], 400);
            }

            $cacheKey = "momo_temp_paid_{$tempOrderId}";
            $isPaid = Cache::get($cacheKey, false);

            Log::info("🔍 Check temp payment", [
                'temp_order_id' => $tempOrderId,
                'cache_key' => $cacheKey,
                'is_paid' => $isPaid,
                'cache_has' => Cache::has($cacheKey)
            ]);

            return response()->json([
                'success' => true,
                'paid' => $isPaid === true, // ✅ Đảm bảo boolean
                'temp_order_id' => $tempOrderId
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Lỗi checkTempPayment", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function paymentMomo(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:tickets,id'
            ]);

            $ticketId = $request->input('id');

            return $this->generateMomoPaymentRedirect($ticketId, $request);
        } catch (\Exception $e) {
            Log::error("Lỗi tạo payment MoMo: " . $e->getMessage());
            return back()->with('error', "Lỗi hệ thống: " . $e->getMessage());
        }
    }

    public function ipn(Request $request)
    {
        Log::info("📥 MoMo IPN nhận được", $request->all());

        try {
            // ===== 1. LẤY DỮ LIỆU =====
            $partnerCode  = $request->input('partnerCode');
            $orderId      = $request->input('orderId');
            $requestId    = $request->input('requestId');
            $amount       = $request->input('amount');
            $orderInfo    = $request->input('orderInfo');
            $orderType    = $request->input('orderType');
            $transId      = $request->input('transId');
            $resultCode   = $request->input('resultCode');
            $message      = $request->input('message');
            $payType      = $request->input('payType');
            $responseTime = $request->input('responseTime');
            $extraData    = $request->input('extraData');
            $signature    = $request->input('signature');

            $accessKey = env('MOMO_ACCESS_KEY');
            $secretKey = env('MOMO_SECRET_KEY');

            // ===== 2. VERIFY SIGNATURE =====
            $rawHash = "accessKey={$accessKey}"
                . "&amount={$amount}"
                . "&extraData={$extraData}"
                . "&message={$message}"
                . "&orderId={$orderId}"
                . "&orderInfo={$orderInfo}"
                . "&orderType={$orderType}"
                . "&partnerCode={$partnerCode}"
                . "&payType={$payType}"
                . "&requestId={$requestId}"
                . "&responseTime={$responseTime}"
                . "&resultCode={$resultCode}"
                . "&transId={$transId}";

            $mySignature = hash_hmac("sha256", $rawHash, $secretKey);

            if ($mySignature !== $signature) {
                Log::error("❌ MoMo IPN: Signature không hợp lệ", [
                    'received' => $signature,
                    'calculated' => $mySignature,
                    'orderId' => $orderId
                ]);

                // ⚠️ VẪN TRẢ 200 để MoMo không retry vô hạn
                return response()->json(['message' => 'Invalid signature'], 200);
            }

            Log::info("✅ MoMo IPN: Signature hợp lệ", [
                'orderId' => $orderId,
                'resultCode' => $resultCode
            ]);

            // ===== 3. XỬ LÝ TEMP ORDER =====
            if (str_starts_with($orderId, 'temp_')) {
                if ((int)$resultCode === 0) {
                    Cache::put("momo_temp_paid_{$orderId}", true, now()->addMinutes(30));
                    Cache::put("momo_temp_trans_{$orderId}", [
                        'transId' => $transId,
                        'amount' => $amount,
                        'responseTime' => $responseTime,
                        'message' => $message,
                    ], now()->addMinutes(30));

                    Log::info("🟢 Temp order thanh toán thành công", [
                        'orderId' => $orderId,
                        'transId' => $transId
                    ]);

                    return response()->json(['message' => 'Temp order confirmed'], 200);
                }

                Cache::put("momo_temp_paid_{$orderId}", false, now()->addMinutes(5));

                Log::warning("🔴 Temp order thanh toán thất bại", [
                    'orderId' => $orderId,
                    'resultCode' => $resultCode,
                    'message' => $message
                ]);

                return response()->json(['message' => 'Temp order failed'], 200);
            }

            // ===== 4. XỬ LÝ TICKET ORDER =====
            $ticketId = explode('_', $orderId)[0];
            $ticket = Ticket::where('id', $ticketId)->lockForUpdate()->first();

            if (!$ticket) {
                Log::error("❌ Không tìm thấy ticket", ['ticketId' => $ticketId]);
                return response()->json(['message' => 'Ticket not found'], 200);
            }

            // ✅ ĐÃ XỬ LÝ TRƯỚC ĐÓ
            if ($ticket->payment_status === 'paid') {
                Log::info("ℹ️ Ticket đã được thanh toán trước đó", [
                    'ticketId' => $ticketId
                ]);

                return response()->json(['message' => 'Already processed'], 200);
            }

            // ===== 5. THANH TOÁN THÀNH CÔNG =====
            if ((int)$resultCode === 0) {
                DB::beginTransaction();

                try {
                    // Kiểm tra số tiền
                    if ((float)$amount < (float)$ticket->total_amount) {
                        throw new \Exception('Amount mismatch');
                    }

                    $ticket->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed'
                    ]);

                    DB::commit();

                    Log::info("✅ Ticket thanh toán thành công", [
                        'ticketId' => $ticketId,
                        'transId' => $transId,
                        'amount' => $amount
                    ]);

                    return response()->json(['message' => 'Payment confirmed'], 200);
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            // ===== 6. THANH TOÁN THẤT BẠI =====
            Log::warning("🔴 Ticket thanh toán thất bại", [
                'ticketId' => $ticketId,
                'resultCode' => $resultCode,
                'message' => $message
            ]);

            return response()->json(['message' => 'Transaction failed'], 200);
        } catch (\Exception $e) {
            Log::error("❌ Exception trong MoMo IPN", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // ⚠️ Luôn trả 200 cho MoMo
            return response()->json(['message' => 'Server error'], 200);
        }
    }



    public function checkStatus($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'id' => $ticket->id,
            'status' => $ticket->status,
            'payment_status' => $ticket->payment_status
        ]);
    }

    public function paymentResult(Request $request)
    {
        $resultCode = $request->input('resultCode');
        $orderId = $request->input('orderId');
        $message = $request->input('message');

        $ticketId = explode("_", $orderId)[0] ?? null;

        Log::info("📥 Payment Result", [
            'resultCode' => $resultCode,
            'orderId' => $orderId,
            'ticketId' => $ticketId
        ]);

        if ($resultCode == 0) {
            return redirect()->route('owner.bookings.index')
                ->with('success', "✅ Thanh toán thành công cho đơn hàng #{$ticketId}");
        }

        return redirect()->route('owner.bookings.index')
            ->with('error', "❌ Thanh toán thất bại: {$message}");
    }
}
