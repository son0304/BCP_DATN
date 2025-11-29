<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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


class PaymentApiController extends Controller
{
    public function paymentMomo(Request $request)
    {
        try {
            Log::info("Dữ liệu nhận từ React:", $request->all());

            // 1. Validate
            $request->validate([
                'id' => 'required|exists:tickets,id'
            ]);

            // Dùng input('id') cho chắc chắn
            $ticketId = $request->input('id');
            $ticket = Ticket::find($ticketId);

            // 2. Kiểm tra ticket tồn tại (Tránh lỗi 500 "status on null")
            if (!$ticket) {
                return response()->json(['message' => 'Không tìm thấy vé trong Database'], 404);
            }

            if ($ticket->status === 'confirmed') {
                return response()->json(['message' => 'Vé này đã được thanh toán rồi.'], 400);
            }

            // 3. Lấy cấu hình (Nên check null để dễ debug)
            $endpoint = env('MOMO_ENDPOINT');
            $partnerCode = env('MOMO_PARTNER_CODE');
            $accessKey = env('MOMO_ACCESS_KEY');
            $secretKey = env('MOMO_SECRET_KEY');
            $redirectUrl = env('MOMO_REDIRECT_URL');
            $ipnUrl = env('MOMO_IPN_URL');

            if (!$accessKey || !$secretKey) {
                return response()->json(['message' => 'Chưa cấu hình MoMo trong .env hoặc chưa clear cache'], 500);
            }

            // 4. Chuẩn bị dữ liệu
            $orderId = $ticket->id . '_' . time();
            $requestId = $orderId;

            // --- SỬA LỖI SỐ TIỀN LẺ TẠI ĐÂY ---
            // Làm tròn 444819.24 -> 444819 rồi mới ép kiểu int
            $amount = (int)round($ticket->total_amount);

            $orderInfo = "Thanh toan ve #" . $ticket->id;
            $extraData = "";
            $requestType = "captureWallet"; // Hoặc "payWithATM"

            // 5. Tạo chữ ký
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

            // 6. Gửi sang MoMo
            $response = Http::post($endpoint, $data);
            $jsonResult = $response->json();

            // Log kết quả MoMo trả về để debug nếu lỗi
            Log::info("MoMo Response:", $jsonResult ?? []);

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
                    'message' => $jsonResult['message'] ?? 'Lỗi không xác định từ MoMo'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("Lỗi Payment Controller: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()], 500);
        }
    }

    public function ipn(Request $request)
    {
        Log::info("MoMo IPN Received:", $request->all());

        // 1. Validation & Signature Check (Giữ nguyên như code của bạn)
        $partnerCode = $request->partnerCode;
        $orderId = $request->orderId;
        $requestId = $request->requestId;
        $amount = $request->amount;
        $orderInfo = $request->orderInfo;
        $orderType = $request->orderType;
        $transId = $request->transId;
        $resultCode = $request->resultCode;
        $message = $request->message;
        $payType = $request->payType;
        $responseTime = $request->responseTime;
        $extraData = $request->extraData;
        $signature = $request->signature;

        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');

        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime . "&resultCode=" . $resultCode . "&transId=" . $transId;
        $mySignature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($mySignature != $signature) {
            return response()->json(['success' => false, 'message' => 'Sai chữ ký']);
        }

        // 2. Kiểm tra kết quả từ MoMo
        if ($resultCode != 0) {
            return response()->json(['success' => false, 'message' => 'Giao dịch thất bại từ phía MoMo']);
        }

        // ========================================================================
        // PHA 1: GHI NHẬN GIAO DỊCH (QUAN TRỌNG: LÀM TRƯỚC LOGIC)
        // ========================================================================
        $ticketIdParts = explode('_', $orderId);
        $ticketId = $ticketIdParts[0];

        // Tìm Ticket để lấy user_id (nếu cần), nhưng chưa update status vội
        $ticket = Ticket::find($ticketId);
        if (!$ticket) return response()->json(['success' => false, 'message' => 'Không tìm thấy vé']);

        // Kiểm tra xem transaction này đã lưu chưa để tránh trùng lặp
        $existingTrans = Transaction::where('booking_id', $ticketId)
            ->where('payment_source', 'momo')
            ->where('note', 'like', "%$transId%") // check kỹ hơn bằng transId
            ->first();

        if ($existingTrans && $existingTrans->status == 'success') {
            return response()->json(['success' => true, 'message' => 'Giao dịch đã xử lý rồi']);
        }

        // Nếu chưa có, tạo Transaction với trạng thái PENDING
        // Lưu ý: Không dùng DB::beginTransaction() ở đây, hoặc dùng transaction riêng
        if (!$existingTrans) {
            try {
                $transaction = Transaction::create([
                    'booking_id' => $ticketId,
                    'user_id' => $ticket->user_id ?? 0,
                    'payment_source' => 'momo',
                    'amount' => $amount,
                    'note' => 'Momo IPN Received. TransId: ' . $transId,
                    'status' => 'pending' // <--- TRẠNG THÁI CHỜ XỬ LÝ
                ]);
            } catch (\Exception $e) {
                Log::error("Không thể lưu Transaction ban đầu: " . $e->getMessage());
                // Nếu không lưu được transaction log thì nguy to, nhưng vẫn phải trả về success để MoMo không retry spam
                return response()->json(['success' => true, 'message' => 'Lỗi lưu log, nhưng đã nhận tiền']);
            }
        } else {
            $transaction = $existingTrans;
        }

        // ========================================================================
        // PHA 2: XỬ LÝ LOGIC PHỨC TẠP (MONEY FLOW, TICKET STATUS)
        // ========================================================================
        DB::beginTransaction(); // Bắt đầu transaction xử lý logic
        try {
            // Lock ticket để xử lý
            $ticketLocked = Ticket::where('id', $ticketId)->lockForUpdate()->first();

            // Check lại tiền lần nữa cho chắc
            if ((float)$amount < (float)$ticketLocked->total_price) {
                throw new \Exception("Số tiền không khớp: Nhận $amount, Cần {$ticketLocked->total_price}");
            }

            // --- Logic Tính toán Money Flow (Copy từ code cũ) ---
            $venue_id = \Illuminate\Support\Facades\DB::table('items')
                ->join('bookings', 'items.booking_id', '=', 'bookings.id')
                ->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('items.ticket_id', $ticketLocked->id)
                ->value('courts.venue_id');

            // Logic tính toán hoa hồng...
            $commission = 0.20;
            $paidAmount = (float)$amount;
            $discount = (float)($ticketLocked->discount_amount ?? 0);
            $originalPrice = $paidAmount + $discount;

            $adminAmount = 0;
            $venueOwnerAmount = 0;

            // ... (Giữ nguyên đoạn logic tính toán promotion của bạn ở đây) ...
            // Tôi rút gọn để dễ nhìn, bạn paste logic if/else promotion vào đây
            // GIẢ SỬ LOGIC TÍNH TOÁN CỦA BẠN NẰM ĐÂY
            // Ví dụ tạm:
            $fee = $paidAmount * $commission;
            $venueOwnerAmount = $paidAmount - $fee;
            $adminAmount = $fee;
            // ... Kết thúc logic tính toán ...

            // 1. Tạo MoneyFlow
            MoneyFlow::create([
                'booking_id' => $ticketLocked->id,
                'total_amount' => $amount,
                'promotion_id' => $ticketLocked->promotion_id ?? null,
                'promotion_amount' => $discount,
                'venue_id' => $venue_id,
                'admin_amount' => $adminAmount,
                'venue_owner_amount' => $venueOwnerAmount,
                'status' => 'pending' // Mặc định là xong
            ]);

            // 2. Update Ticket
            $ticketLocked->status = 'confirmed';
            $ticketLocked->payment_status = 'paid';
            $ticketLocked->save();

            // 3. Update Transaction thành SUCCESS
            $transaction->update([
                'status' => 'success',
                'note' => $transaction->note . ' | Xử lý thành công.'
            ]);

            DB::commit(); // Xong xuôi tất cả thì commit PHA 2

            return response()->json(['success' => true, 'message' => 'Thanh toán thành công']);
        } catch (\Exception $e) {
            // NẾU CÓ LỖI Ở PHA 2
            DB::rollBack(); // Hoàn tác việc update Ticket và MoneyFlow (vì tính sai hoặc lỗi code)

            Log::error("Lỗi xử lý IPN logic: " . $e->getMessage());
            $transaction->update([
                'status' => 'failed_logic', // Hoặc 'pending' tùy bạn
                'note' => $transaction->note . ' | Lỗi Logic: ' . $e->getMessage()
            ]);

            // Vẫn trả về True cho MoMo để nó không gửi lại request nữa (Vì tiền đã nhận rồi, lỗi là do server mình xử lý)
            // Nếu trả false, MoMo sẽ gửi lại IPN liên tục.
            return response()->json(['success' => true, 'message' => 'Đã nhận tiền, nhưng lỗi xử lý nội bộ.']);
        }
    }


    public function paymentWallet(Request $request)
    {
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'total_amount' => 'required|numeric|min:0'
        ]);

        $userId = Auth::id();
        Log::info("User $userId đang thanh toán vé #" . $validated['ticket_id'] . " qua ví với số tiền " . $validated['total_amount']);
        $ticket = Ticket::find($validated['ticket_id']);
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Không tìm thấy vé'], 404);
        }

        if ($ticket->status === 'confirmed') {
            return response()->json(['message' => 'Vé này đã được thanh toán rồi.'], 400);
        }
        if (!$wallet) {
            return response()->json(['message' => 'Không tìm thấy ví người dùng. Vui lòng tạo ví trước khi thanh toán.'], 404);
        }

        if (!$wallet || $wallet->balance < $validated['total_amount']) {
            return response()->json(['message' => 'Số dư ví không đủ.'], 400);
        }

        try {
            DB::transaction(function () use ($ticket, $wallet, $validated) {
                $wallet->decrement('balance', $validated['total_amount']);
                $ticket->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid'
                ]);

                WalletLog::create([
                    'wallet_id'   => $wallet->id,
                    'type'        => 'payment',
                    'amount'      => $validated['total_amount'],
                    'before_balance' => $wallet->balance,
                    'after_balance'  => $wallet->balance - $validated['total_amount'],
                    'description' => 'Thanh toán vé #' . $ticket->id,
                ]);
            });

            return response()->json(['message' => 'Thanh toán thành công!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi giao dịch: ' . $e->getMessage()], 500);
        }
    }

    public function checkTransactionStatus($id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'id' => $ticket->id,
            'status' => $ticket->status,           // confirmed, pending...
            'payment_status' => $ticket->payment_status // paid, unpaid...
        ]);
    }
}
