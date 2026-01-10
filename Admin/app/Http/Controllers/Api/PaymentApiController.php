<?php

namespace App\Http\Controllers\Api;

use App\Events\DataCreated;
use App\Http\Controllers\Controller;
use App\Mail\Booking_Status;
use App\Models\MoneyFlow;
use App\Models\Notification;
use App\Models\Promotion;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentApiController extends Controller
{
    // =========================================================================
    // 1. THANH TOÁN MOMO (TẠO REQUEST)
    // =========================================================================
    public function paymentMomo(Request $request)
    {
        try {
            Log::info("Momo Init Request:", $request->all());

            $request->validate(['id' => 'required|exists:tickets,id']);

            $ticket = Ticket::find($request->input('id'));

            if (!$ticket) {
                return response()->json(['message' => 'Không tìm thấy vé'], 404);
            }
            if ($ticket->status === 'confirmed') {
                return response()->json(['message' => 'Vé này đã được thanh toán rồi.'], 400);
            }

            $endpoint = env('MOMO_ENDPOINT');
            $partnerCode = env('MOMO_PARTNER_CODE');
            $accessKey = env('MOMO_ACCESS_KEY');
            $secretKey = env('MOMO_SECRET_KEY');
            $redirectUrl = env('MOMO_REDIRECT_URL_LOCAL');
            $ipnUrl = env('MOMO_IPN_URL_LOCAL');

            if (!$accessKey || !$secretKey || !$ipnUrl) {
                Log::error("Thiếu cấu hình MoMo in .env");
                return response()->json(['message' => 'Lỗi cấu hình Server Payment'], 500);
            }

            $orderId = $ticket->id . '_' . time();
            $requestId = $orderId;
            $amount = (int)round($ticket->total_amount);
            $orderInfo = "Thanh toan ve #" . $ticket->id;
            $requestType = "captureWallet";
            $extraData = "";

            $rawHash = "accessKey=" . $accessKey .
                "&amount=" . $amount .
                "&extraData=" . $extraData .
                "&ipnUrl=" . $ipnUrl .
                "&orderId=" . $orderId .
                "&orderInfo=" . $orderInfo .
                "&partnerCode=" . $partnerCode .
                "&redirectUrl=" . $redirectUrl .
                "&requestId=" . $requestId .
                "&requestType=" . $requestType;

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

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

            $response = Http::post($endpoint, $data);
            $jsonResult = $response->json();

            Log::info("MoMo Create Response:", $jsonResult ?? []);

            if (isset($jsonResult['payUrl'])) {
                return response()->json([
                    'success' => true,
                    'payUrl' => $jsonResult['payUrl'],
                    'qrCodeUrl' => $jsonResult['qrCodeUrl'] ?? null,
                    'deeplink' => $jsonResult['deeplink'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $jsonResult['message'] ?? 'Lỗi tạo giao dịch MoMo'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("Momo Payment Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 2. XỬ LÝ IPN MOMO (CALLBACK)
    // =========================================================================
    public function ipn(Request $request)
    {
        Log::info("MoMo IPN Received:", $request->all());

        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');

        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $request->amount .
            "&extraData=" . $request->extraData .
            "&message=" . $request->message .
            "&orderId=" . $request->orderId .
            "&orderInfo=" . $request->orderInfo .
            "&orderType=" . $request->orderType .
            "&partnerCode=" . $request->partnerCode .
            "&payType=" . $request->payType .
            "&requestId=" . $request->requestId .
            "&responseTime=" . $request->responseTime .
            "&resultCode=" . $request->resultCode .
            "&transId=" . $request->transId;

        $mySignature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($mySignature != $request->signature) {
            return response()->json(['success' => false, 'message' => 'Sai chữ ký']);
        }

        if ($request->resultCode != 0) {
            return response()->json(['success' => false, 'message' => 'Giao dịch thất bại']);
        }

        $ticketId = explode('_', $request->orderId)[0];
        $ticket = Ticket::find($ticketId);

        if (!$ticket) return response()->json(['success' => false, 'message' => 'Ticket not found']);

        // Ghi log Transaction (Dùng quan hệ để tự động điền polymorphic fields)
        $existingTrans = $ticket->transactions()
            ->where('payment_source', 'momo')
            ->where('note', 'like', "%" . $request->transId . "%")
            ->first();

        if ($existingTrans && $existingTrans->status == 'success') {
            return response()->json(['success' => true, 'message' => 'Đã xử lý trước đó']);
        }

        if (!$existingTrans) {
            // TỰ ĐỘNG ĐIỀN transactionable_id và transactionable_type
            $transaction = $ticket->transactions()->create([
                'user_id' => $ticket->user_id ?? null,
                'payment_source' => 'momo',
                'amount' => $request->amount,
                'note' => 'Momo IPN: ' . $request->transId,
                'status' => 'pending'
            ]);
        } else {
            $transaction = $existingTrans;
        }

        DB::beginTransaction();
        try {
            $ticketLocked = Ticket::where('id', $ticketId)->lockForUpdate()->first();

            if ((float)$request->amount < (float)$ticketLocked->total_amount) {
                throw new \Exception("Tiền thanh toán không đủ. Nhận: {$request->amount}, Cần: {$ticketLocked->total_amount}");
            }

            $venue_id = DB::table('items')
                ->join('bookings', 'items.booking_id', '=', 'bookings.id')
                ->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('items.ticket_id', $ticketLocked->id)
                ->value('courts.venue_id');

            $actualPaid = (float)$request->amount;
            $discount = (float)($ticketLocked->discount_amount ?? 0);
            $grossAmount = $actualPaid + $discount;
            $commissionRate = 0.10;
            $baseCommission = $grossAmount * $commissionRate;

            $promotion = $ticketLocked->promotion_id ? Promotion::find($ticketLocked->promotion_id) : null;
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

            foreach ($ticket->items as $item) {
                $booking = $item->booking;
                if ($booking) {
                    $booking->update(['status' => 'confirmed']);
                }
            }

            // TỰ ĐỘNG ĐIỀN money_flowable_id và money_flowable_type
            $ticketLocked->moneyFlows()->create([
                'total_amount' => $grossAmount,
                'promotion_id' => $ticketLocked->promotion_id,
                'promotion_amount' => $discount,
                'venue_id' => $venue_id,
                'admin_amount' => $adminAmount,
                'venue_owner_amount' => $venueOwnerAmount,
                'status' => 'pending'
            ]);

            $ticketLocked->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'momo'
            ]);

            $transaction->update(['status' => 'success']);

            $owner_id = $ticket->getOwnerId();
            if ($owner_id) {
                $notification = Notification::create([
                    'user_id' => $owner_id,
                    'type' => 'info',
                    'title' => 'Đơn đặt sân mới',
                    'message' => 'Bạn có đơn đặt sân mới #' . ($ticket->id) . ' từ ' . ($ticket->user->name ?? 'Unknown'),
                    'data' => [
                        'booking_id' => $ticket->id,
                        'link' => '/owner/bookings?search=' . $ticket->booking_code,
                    ],
                    'read_at' => null,
                ]);

                broadcast(new DataCreated($notification, 'notification', 'notification.created'));
            }
            DB::commit();

            $this->sendMailAndBroadcast($ticketLocked);

            return response()->json(['success' => true, 'message' => 'Thanh toán thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Momo IPN Logic Error: " . $e->getMessage());
            $transaction->update(['status' => 'failed_logic', 'note' => $e->getMessage()]);
            return response()->json(['success' => true, 'message' => 'Lỗi xử lý nội bộ']);
        }
    }


    public function paymentVNPay(Request $request)
    {
        $ticket = Ticket::findOrFail($request->id);

        Log::info("env: " . env('VNPAY_HASH_SECRET'));
        $vnp_TmnCode = env('VNPAY_TMN_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $vnp_Url = env('VNPAY_URL');
        $vnp_Returnurl = env('VNPAY_RETURN_URL') . $ticket->id;

        $vnp_TxnRef = $ticket->id . '_' . time();
        $vnp_OrderInfo = "Thanh toan ve " . $ticket->id;
        $vnp_OrderType = "billpayment";
        $vnp_Amount = (int)($ticket->total_amount * 100);
        $vnp_Locale = "vn";
        $vnp_IpAddr = $request->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        // 1. Sắp xếp dữ liệu theo key
        ksort($inputData);

        // 2. Tạo chuỗi query và hash data
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;

        // 3. Tính Secure Hash
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

        return response()->json([
            'success' => true,
            'payUrl' => $vnp_Url
        ]);
    }
    public function vnpayCallback(Request $request)
    {
        Log::info("VNPay Callback Received:", $request->all());

        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = array();

        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);

        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash !== $vnp_SecureHash) {
            Log::error("VNPay: Sai chữ ký");
            return response()->json(['success' => false, 'message' => 'Sai chữ ký']);
        }

        $vnp_TxnRef = $request->vnp_TxnRef;
        $ticketId = explode('_', $vnp_TxnRef)[0];
        $ticket = Ticket::find($ticketId);

        if (!$ticket || $request->vnp_ResponseCode !== '00') {
            return response()->json(['success' => false, 'message' => 'Giao dịch thất bại hoặc không tìm thấy đơn']);
        }

        // Kiểm tra xem đơn đã confirmed chưa để tránh xử lý trùng (VNPay hay gọi lại)
        if ($ticket->status === 'confirmed') {
            return response()->json(['success' => true, 'message' => 'Đã xử lý trước đó']);
        }

        $vnp_Amount = $request->vnp_Amount / 100;
        $vnp_TransactionNo = $request->vnp_TransactionNo;

        DB::beginTransaction();
        try {
            $ticketLocked = Ticket::where('id', $ticketId)->lockForUpdate()->first();

            // 1. Cập nhật Transaction
            $transaction = $ticketLocked->transactions()->updateOrCreate(
                ['note' => 'VNPay: ' . $vnp_TransactionNo],
                [
                    'user_id' => $ticketLocked->user_id,
                    'payment_source' => 'vnpay',
                    'amount' => $vnp_Amount,
                    'status' => 'success'
                ]
            );

            // 2. Tìm venue_id nhanh
            $venue_id = DB::table('items')
                ->join('bookings', 'items.booking_id', '=', 'bookings.id')
                ->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('items.ticket_id', $ticketLocked->id)
                ->value('courts.venue_id');

            // 3. Tính toán tiền nong (Logic giữ nguyên)
            $actualPaid = (float)$vnp_Amount;
            $discount = (float)($ticketLocked->discount_amount ?? 0);
            $grossAmount = $actualPaid + $discount;
            $commissionRate = 0.10;
            $baseCommission = $grossAmount * $commissionRate;

            $promotion = $ticketLocked->promotion_id ? Promotion::find($ticketLocked->promotion_id) : null;
            $isVenueVoucher = !($promotion && is_null($promotion->venue_id));

            if ($isVenueVoucher) {
                $adminAmount = $baseCommission;
                $venueOwnerAmount = $actualPaid - $baseCommission;
            } else {
                $adminAmount = $baseCommission - $discount;
                $venueOwnerAmount = $grossAmount - $baseCommission;
            }
            if ($venueOwnerAmount < 0) $venueOwnerAmount = 0;

            // 4. Cập nhật các Booking
            foreach ($ticketLocked->items as $item) {
                if ($item->booking) {
                    $item->booking->update(['status' => 'confirmed']);
                }
            }

            // 5. Tạo Money Flow
            $ticketLocked->moneyFlows()->create([
                'total_amount' => $grossAmount,
                'promotion_id' => $ticketLocked->promotion_id,
                'promotion_amount' => $discount,
                'venue_id' => $venue_id,
                'admin_amount' => $adminAmount,
                'venue_owner_amount' => $venueOwnerAmount,
                'status' => 'pending'
            ]);

            // 6. Cập nhật Ticket
            $ticketLocked->update([
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'payment_method' => 'vnpay'
            ]);

            DB::commit();

            // =====================================================================
            // ĐÂY LÀ PHẦN LÀM CHO NÓ NHANH: Đẩy các tác vụ chờ đợi vào afterResponse
            // =====================================================================
            dispatch(function () use ($ticketLocked) {
                try {
                    // 1. Gửi thông báo cho chủ sân (Tốn thời gian kết nối Database/Pusher)
                    $owner_id = $ticketLocked->getOwnerId();
                    if ($owner_id) {
                        $notification = Notification::create([
                            'user_id' => $owner_id,
                            'type' => 'info',
                            'title' => 'Đơn đặt sân mới (VNPay)',
                            'message' => 'Bạn có đơn đặt sân mới #' . $ticketLocked->id . ' từ ' . ($ticketLocked->user->name ?? 'Khách'),
                            'data' => [
                                'booking_id' => $ticketLocked->id,
                                'link' => '/owner/bookings?search=' . $ticketLocked->booking_code,
                            ],
                        ]);

                        if (class_exists(\App\Events\DataCreated::class)) {
                            broadcast(new \App\Events\DataCreated($notification, 'notification', 'notification.created'));
                        }
                    }

                    // 2. Gửi Mail (Đây là bước chậm nhất - tốn 2-5 giây)
                    if (method_exists($this, 'sendMailAndBroadcast')) {
                        $this->sendMailAndBroadcast($ticketLocked);
                    }
                } catch (\Exception $e) {
                    Log::error("Tác vụ ngầm VNPay lỗi: " . $e->getMessage());
                }
            })->afterResponse();

            // Trả về JSON ngay lập tức cho Frontend
            return response()->json(['success' => true, 'message' => 'Thanh toán thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("VNPay Callback Logic Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi xử lý hệ thống'], 500);
        }
    }


    // =========================================================================
    // 3. THANH TOÁN QUA VÍ (WALLET)
    // =========================================================================
    public function paymentWallet(Request $request)
    {
        Log::info("Wallet Payment Request:", $request->all());
        $request->validate(['ticket_id' => 'required|exists:tickets,id']);

        $userId = Auth::id();
        $ticket = Ticket::where('id', $request->ticket_id)->lockForUpdate()->first();
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$ticket) return response()->json(['message' => 'Không tìm thấy vé'], 404);
        if ($ticket->status !== 'pending') return response()->json(['message' => 'Vé không ở trạng thái chờ thanh toán.'], 400);
        if (!$wallet) return response()->json(['message' => 'Bạn chưa có ví.'], 404);

        $paymentAmount = (float)$ticket->total_amount;

        if ($wallet->balance < $paymentAmount) {
            return response()->json(['message' => 'Số dư ví không đủ.'], 400);
        }

        $venue_id = DB::table('items')
            ->join('bookings', 'items.booking_id', '=', 'bookings.id')
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->where('items.ticket_id', $ticket->id)
            ->value('courts.venue_id');

        if (!$venue_id) return response()->json(['message' => 'Không tìm thấy thông tin sân.'], 500);

        try {
            DB::transaction(function () use ($ticket, $wallet, $paymentAmount, $venue_id) {
                $actualPaid = $paymentAmount;
                $discount = (float)($ticket->discount_amount ?? 0);
                $grossAmount = $actualPaid + $discount;

                $commissionRate = 0.10;
                $baseCommission = $grossAmount * $commissionRate;

                $promotion = $ticket->promotion_id ? Promotion::find($ticket->promotion_id) : null;
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

                $beforeBalance = $wallet->balance;
                $wallet->decrement('balance', $actualPaid);

                $ticket->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                    'payment_method' => 'wallet'
                ]);

                foreach ($ticket->items as $item) {
                    $booking = $item->booking;
                    if ($booking) {
                        $booking->update(['status' => 'confirmed']);
                    }
                }

                WalletLog::create([
                    'wallet_id'   => $wallet->id,
                    'type'        => 'payment',
                    'amount'      => $actualPaid,
                    'before_balance' => $beforeBalance,
                    'after_balance'  => $beforeBalance - $actualPaid,
                    'description' => 'Thanh toán vé #' . $ticket->id,
                ]);

                // TỰ ĐỘNG ĐIỀN money_flowable_id và money_flowable_type
                $ticket->moneyFlows()->create([
                    'total_amount' => $grossAmount,
                    'promotion_id' => $ticket->promotion_id,
                    'promotion_amount' => $discount,
                    'venue_id' => $venue_id,
                    'admin_amount' => $adminAmount,
                    'venue_owner_amount' => $venueOwnerAmount,
                    'status' => 'pending'
                ]);

                $owner_id = $ticket->getOwnerId();
                if ($owner_id) {
                    $notification = Notification::create([
                        'user_id' => $owner_id,
                        'type' => 'info',
                        'title' => 'Đơn đặt sân mới',
                        'message' => 'Bạn có đơn đặt sân mới #' . ($ticket->id) . ' từ ' . ($ticket->user->name ?? 'Unknown'),
                        'data' => [
                            'booking_id' => $ticket->id,
                            'link' => '/owner/bookings?search=' . $ticket->booking_code,
                        ],
                        'read_at' => null,
                    ]);
                    broadcast(new DataCreated($notification, 'notification', 'notification.created'));
                }
            });

            $this->sendMailAndBroadcast($ticket);

            return response()->json(['message' => 'Thanh toán thành công!'], 200);
        } catch (\Exception $e) {
            Log::error("Lỗi thanh toán ví Ticket #{$ticket->id}: " . $e->getMessage());
            return response()->json(['message' => 'Giao dịch thất bại.'], 500);
        }
    }

    // =========================================================================
    // 4. HELPER: GỬI MAIL & BROADCAST
    // =========================================================================
    private function sendMailAndBroadcast($ticket)
    {
        try {
            $ticket->load([
                'user',
                'items.booking.court.venue',
                'items.booking.timeSlot',
                'items.venueService.service'
            ]);

            if ($ticket->user && $ticket->user->email) {
                Mail::to($ticket->user->email)->send(new Booking_Status($ticket));
            }

            broadcast(new \App\Events\DataUpdated($ticket, 'booking', 'ticket.updated'));
        } catch (\Exception $e) {
            Log::error("Lỗi gửi mail/broadcast vé #{$ticket->id}: " . $e->getMessage());
        }
    }

    public function checkTransactionStatus($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) return response()->json(['status' => 'not_found'], 404);

        return response()->json([
            'id' => $ticket->id,
            'status' => $ticket->status,
            'payment_status' => $ticket->payment_status,
        ]);
    }
}