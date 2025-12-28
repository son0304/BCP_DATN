<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Booking_Status;
use App\Models\MoneyFlow;
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

            // Lấy config (Đã sửa lại tên biến khớp với .env chuẩn)
            $endpoint = env('MOMO_ENDPOINT');
            $partnerCode = env('MOMO_PARTNER_CODE');
            $accessKey = env('MOMO_ACCESS_KEY');
            $secretKey = env('MOMO_SECRET_KEY');
            $redirectUrl = env('MOMO_REDIRECT_URL_LOCAL'); // Đã bỏ _LOCAL
            $ipnUrl = env('MOMO_IPN_URL_LOCAL');           // Đã bỏ _LOCAL

            if (!$accessKey || !$secretKey || !$ipnUrl) {
                Log::error("Thiếu cấu hình MoMo in .env");
                return response()->json(['message' => 'Lỗi cấu hình Server Payment'], 500);
            }

            // Chuẩn bị dữ liệu
            $orderId = $ticket->id . '_' . time();
            $requestId = $orderId;
            $amount = (int)round($ticket->total_amount); // Lấy tiền từ DB
            $orderInfo = "Thanh toan ve #" . $ticket->id;
            $requestType = "captureWallet";
            $extraData = "";

            // Tạo chữ ký
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

        // A. Verify Signature
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

        // B. Tìm Ticket & Transaction Log
        $ticketId = explode('_', $request->orderId)[0];
        $ticket = Ticket::find($ticketId);

        if (!$ticket) return response()->json(['success' => false, 'message' => 'Ticket not found']);

        // Ghi log Transaction (Idempotency)
        $existingTrans = Transaction::where('booking_id', $ticketId)
            ->where('payment_source', 'momo')
            ->where('note', 'like', "%" . $request->transId . "%")
            ->first();

        if ($existingTrans && $existingTrans->status == 'success') {
            return response()->json(['success' => true, 'message' => 'Đã xử lý trước đó']);
        }

        if (!$existingTrans) {
            $transaction = Transaction::create([
                'booking_id' => $ticketId,
                'user_id' => $ticket->user_id ?? 0,
                'payment_source' => 'momo',
                'amount' => $request->amount,
                'note' => 'Momo IPN: ' . $request->transId,
                'status' => 'pending'
            ]);
        } else {
            $transaction = $existingTrans;
        }

        // C. Xử lý Logic chính (DB Transaction)
        DB::beginTransaction();
        try {
            // Lock để tránh race condition
            $ticketLocked = Ticket::where('id', $ticketId)->lockForUpdate()->first();

            // Check số tiền (Dùng total_amount)
            if ((float)$request->amount < (float)$ticketLocked->total_amount) {
                throw new \Exception("Tiền thanh toán không đủ. Nhận: {$request->amount}, Cần: {$ticketLocked->total_amount}");
            }

            // --- LOGIC MONEY FLOW (COPY TỪ WALLET SANG) ---
            $venue_id = DB::table('items')
                ->join('bookings', 'items.booking_id', '=', 'bookings.id')
                ->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('items.ticket_id', $ticketLocked->id)
                ->value('courts.venue_id');

            // Tính toán
            $actualPaid = (float)$request->amount;
            $discount = (float)($ticketLocked->discount_amount ?? 0);
            $grossAmount = $actualPaid + $discount; // Giá gốc
            $commissionRate = 0.20;
            $baseCommission = $grossAmount * $commissionRate;

            // Xác định nguồn Voucher
            $promotion = $ticketLocked->promotion_id ? Promotion::find($ticketLocked->promotion_id) : null;
            $isVenueVoucher = true; // Mặc định chủ sân chịu
            if ($promotion && is_null($promotion->venue_id)) {
                $isVenueVoucher = false; // Admin chịu
            }

            if ($isVenueVoucher) {
                $adminAmount = $baseCommission;
                $venueOwnerAmount = $actualPaid - $baseCommission;
            } else {
                $adminAmount = $baseCommission - $discount;
                $venueOwnerAmount = $grossAmount - $baseCommission;
            }
            if ($venueOwnerAmount < 0) $venueOwnerAmount = 0;

            // Lưu MoneyFlow
            MoneyFlow::create([
                'booking_id' => $ticketLocked->id,
                'total_amount' => $grossAmount, // Lưu giá gốc
                'promotion_id' => $ticketLocked->promotion_id,
                'promotion_amount' => $discount,
                'venue_id' => $venue_id,
                'admin_amount' => $adminAmount,
                'venue_owner_amount' => $venueOwnerAmount,
                'status' => 'pending'
            ]);

            // Update Status
            $ticketLocked->update(['status' => 'confirmed', 'payment_status' => 'paid']);
            $transaction->update(['status' => 'success']);

            DB::commit();

            // Gửi Mail & Broadcast
            $this->sendMailAndBroadcast($ticketLocked);

            return response()->json(['success' => true, 'message' => 'Thanh toán thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Momo IPN Logic Error: " . $e->getMessage());
            $transaction->update(['status' => 'failed_logic', 'note' => $e->getMessage()]);
            return response()->json(['success' => true, 'message' => 'Lỗi xử lý nội bộ']);
        }
    }

    // =========================================================================
    // 3. THANH TOÁN QUA VÍ (WALLET)
    // =========================================================================
    public function paymentWallet(Request $request)
    {
        Log::info("Wallet Payment Request:", $request->all());
        // Validate: CHỈ nhận ID, KHÔNG tin tưởng total_amount từ client
        $request->validate(['ticket_id' => 'required|exists:tickets,id']);

        $userId = Auth::id();
        Log::info("User $userId request thanh toán ví vé #" . $request->ticket_id);

        $ticket = Ticket::where('id', $request->ticket_id)->lockForUpdate()->first();
        $wallet = Wallet::where('user_id', $userId)->first();

        // Checks
        if (!$ticket) return response()->json(['message' => 'Không tìm thấy vé'], 404);
        if ($ticket->status !== 'pending') return response()->json(['message' => 'Vé không ở trạng thái chờ thanh toán.'], 400);
        if (!$wallet) return response()->json(['message' => 'Bạn chưa có ví.'], 404);

        // Lấy tiền từ DB
        $paymentAmount = (float)$ticket->total_amount;

        if ($wallet->balance < $paymentAmount) {
            return response()->json(['message' => 'Số dư ví không đủ.'], 400);
        }

        // Lấy Venue ID
        $venue_id = DB::table('items')
            ->join('bookings', 'items.booking_id', '=', 'bookings.id')
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->where('items.ticket_id', $ticket->id)
            ->value('courts.venue_id');

        if (!$venue_id) return response()->json(['message' => 'Không tìm thấy thông tin sân.'], 500);

        try {
            DB::transaction(function () use ($ticket, $wallet, $paymentAmount, $venue_id) {
                // --- LOGIC MONEY FLOW (GIỐNG IPN) ---
                $actualPaid = $paymentAmount;
                $discount = (float)($ticket->discount_amount ?? 0);
                $grossAmount = $actualPaid + $discount; // Giá gốc

                $commissionRate = 0.20;
                $baseCommission = $grossAmount * $commissionRate;

                // Check nguồn Voucher
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

                // A. Trừ ví
                $beforeBalance = $wallet->balance;
                $wallet->decrement('balance', $actualPaid);

                // B. Cập nhật vé
                $ticket->update(['status' => 'confirmed', 'payment_status' => 'paid']);

                // C. Log Ví
                WalletLog::create([
                    'wallet_id'   => $wallet->id,
                    'type'        => 'payment',
                    'amount'      => $actualPaid,
                    'before_balance' => $beforeBalance,
                    'after_balance'  => $beforeBalance - $actualPaid,
                    'description' => 'Thanh toán vé #' . $ticket->id,
                ]);

                // D. Tạo MoneyFlow
                MoneyFlow::create([
                    'booking_id' => $ticket->id,
                    'total_amount' => $grossAmount,
                    'promotion_id' => $ticket->promotion_id,
                    'promotion_amount' => $discount,
                    'venue_id' => $venue_id,
                    'admin_amount' => $adminAmount,
                    'venue_owner_amount' => $venueOwnerAmount,
                    'status' => 'pending'
                ]);
            });

            // Gửi Mail & Broadcast
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
            // Load quan hệ để gửi mail/broadcast
            $ticket->load([
                'user',
                'items.booking.court.venue',
                'items.booking.timeSlot',
                'items.venueService.service'
            ]);

            // Gửi Mail
            if ($ticket->user && $ticket->user->email) {
                Mail::to($ticket->user->email)->send(new Booking_Status($ticket));
            }

            // Broadcast (Sửa thành ticket.updated)
            broadcast(new \App\Events\DataUpdated($ticket, 'booking', 'ticket.updated'));
        } catch (\Exception $e) {
            Log::error("Lỗi gửi mail/broadcast vé #{$ticket->id}: " . $e->getMessage());
        }
    }

    // Check trạng thái đơn (Cho Polling ở Client)
    public function checkTransactionStatus($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) return response()->json(['status' => 'not_found'], 404);

        return response()->json([
            'id' => $ticket->id,
            'status' => $ticket->status,
            'payment_status' => $ticket->payment_status
        ]);
    }
}