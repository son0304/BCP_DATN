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
        // Đảm bảo đã import Log ở đầu file: use Illuminate\Support\Facades\Log;
        Log::info("MoMo IPN Received:", $request->all());

        // 1. Validation & Signature Check
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
        // PHA 1: GHI NHẬN GIAO DỊCH
        // ========================================================================
        $ticketIdParts = explode('_', $orderId);
        $ticketId = $ticketIdParts[0];

        $ticket = Ticket::find($ticketId);
        if (!$ticket) return response()->json(['success' => false, 'message' => 'Không tìm thấy vé']);

        $existingTrans = Transaction::where('booking_id', $ticketId)
            ->where('payment_source', 'momo')
            ->where('note', 'like', "%$transId%")
            ->first();

        if ($existingTrans && $existingTrans->status == 'success') {
            return response()->json(['success' => true, 'message' => 'Giao dịch đã xử lý rồi']);
        }

        if (!$existingTrans) {
            try {
                $transaction = Transaction::create([
                    'booking_id' => $ticketId,
                    'user_id' => $ticket->user_id ?? 0,
                    'payment_source' => 'momo',
                    'amount' => $amount,
                    'note' => 'Momo IPN Received. TransId: ' . $transId,
                    'status' => 'pending'
                ]);
            } catch (\Exception $e) {
                Log::error("Không thể lưu Transaction ban đầu: " . $e->getMessage());
                return response()->json(['success' => true, 'message' => 'Lỗi lưu log, nhưng đã nhận tiền']);
            }
        } else {
            $transaction = $existingTrans;
        }

        // ========================================================================
        // PHA 2: XỬ LÝ LOGIC
        // ========================================================================
        DB::beginTransaction();
        try {
            $ticketLocked = Ticket::where('id', $ticketId)->lockForUpdate()->first();

            if ((float)$amount < (float)$ticketLocked->total_price) {
                throw new \Exception("Số tiền không khớp: Nhận $amount, Cần {$ticketLocked->total_price}");
            }

            // --- Logic MoneyFlow ---
            $venue_id = \Illuminate\Support\Facades\DB::table('items')
                ->join('bookings', 'items.booking_id', '=', 'bookings.id')
                ->join('courts', 'bookings.court_id', '=', 'courts.id')
                ->where('items.ticket_id', $ticketLocked->id)
                ->value('courts.venue_id');

            $commission = 0.20;
            $paidAmount = (float)$amount;
            $discount = (float)($ticketLocked->discount_amount ?? 0);

            $fee = $paidAmount * $commission;
            $venueOwnerAmount = $paidAmount - $fee;
            $adminAmount = $fee;

            MoneyFlow::create([
                'booking_id' => $ticketLocked->id,
                'total_amount' => $amount,
                'promotion_id' => $ticketLocked->promotion_id ?? null,
                'promotion_amount' => $discount,
                'venue_id' => $venue_id,
                'admin_amount' => $adminAmount,
                'venue_owner_amount' => $venueOwnerAmount,
                'status' => 'pending'
            ]);

            $ticketLocked->status = 'confirmed';
            $ticketLocked->payment_status = 'paid';
            $ticketLocked->save();

            $transaction->update([
                'status' => 'success',
                'note' => $transaction->note . ' | Xử lý thành công.'
            ]);

            DB::commit(); // <--- Quan trọng: Commit xong mới gửi mail

            // ========================================================================
            // GỬI MAIL (Đã sửa lỗi)
            // ========================================================================
            try {
                $checkTicket = Ticket::with([
                    'user', // Lấy user để có email
                    'items.booking.court.venue', // Lấy tên Venue
                    'items.booking.timeSlot', // Lấy giờ
                ])->find($ticketLocked->id);

                Log::info("Chuẩn bị gửi mail vé ID: " . $checkTicket->id);

                if ($checkTicket->user && $checkTicket->user->email) {
                    // Đảm bảo namespace của Mailable đúng
                    \Illuminate\Support\Facades\Mail::to($checkTicket->user->email)
                        ->send(new \App\Mail\Booking_Status($checkTicket));

                    Log::info("Đã gửi mail thành công đến: " . $checkTicket->user->email);
                } else {
                    Log::warning("Không tìm thấy email user cho vé #" . $checkTicket->id);
                }
            } catch (\Exception $mailException) {
                // SỬA LỖI TẠI ĐÂY: Phải truyền chuỗi vào Log::error()
                Log::error("Thanh toán MoMo thành công nhưng gửi mail lỗi: " . $mailException->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Thanh toán thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi xử lý IPN logic: " . $e->getMessage());

            $transaction->update([
                'status' => 'failed_logic',
                'note' => $transaction->note . ' | Lỗi Logic: ' . $e->getMessage()
            ]);

            return response()->json(['success' => true, 'message' => 'Đã nhận tiền, nhưng lỗi xử lý nội bộ.']);
        }
    }


    public function paymentWallet(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validated = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'total_amount' => 'required|numeric|min:0'
        ]);

        $userId = Auth::id();
        Log::info("User $userId đang thanh toán vé #" . $validated['ticket_id'] . " qua ví với số tiền " . $validated['total_amount']);

        // 2. Load dữ liệu cần thiết
        // Dùng lockForUpdate để tránh việc 2 request thanh toán cùng lúc cho 1 vé
        $ticket = Ticket::where('id', $validated['ticket_id'])->lockForUpdate()->first();
        $wallet = Wallet::where('user_id', $userId)->first();

        // 3. Các bước kiểm tra điều kiện (Guard clauses)
        if (!$ticket) {
            return response()->json(['message' => 'Không tìm thấy vé'], 404);
        }

        if ($ticket->status === 'confirmed') {
            return response()->json(['message' => 'Vé này đã được thanh toán rồi.'], 400);
        }

        if (!$wallet) {
            return response()->json(['message' => 'Không tìm thấy ví người dùng. Vui lòng tạo ví trước khi thanh toán.'], 404);
        }

        // Kiểm tra số dư ví
        $paymentAmount = (float)$validated['total_amount'];
        if ($wallet->balance < $paymentAmount) {
            return response()->json(['message' => 'Số dư ví không đủ.'], 400);
        }

        // Kiểm tra số tiền thanh toán có khớp với giá vé không
        if ($paymentAmount < (float)$ticket->total_price) {
            return response()->json(['message' => 'Số tiền thanh toán không đủ so với giá vé.'], 400);
        }

        // 4. Lấy Venue ID
        $venue_id = \Illuminate\Support\Facades\DB::table('items')
            ->join('bookings', 'items.booking_id', '=', 'bookings.id')
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->where('items.ticket_id', $ticket->id)
            ->value('courts.venue_id');

        if (!$venue_id) {
            return response()->json(['message' => 'Lỗi dữ liệu: Không tìm thấy thông tin sân đấu.'], 500);
        }

        // 5. Tính toán Money Flow
        $commission = 0.20; // 20%
        $discount = (float)($ticket->discount_amount ?? 0);
        $adminAmount = $paymentAmount * $commission;
        $venueOwnerAmount = $paymentAmount - $adminAmount;

        try {
            // THỰC HIỆN GIAO DỊCH TRỪ TIỀN & LƯU DB
            DB::transaction(function () use ($ticket, $wallet, $paymentAmount, $venue_id, $discount, $adminAmount, $venueOwnerAmount) {
                // A. Trừ tiền ví
                $beforeBalance = $wallet->balance;
                $wallet->decrement('balance', $paymentAmount);
                $afterBalance = $beforeBalance - $paymentAmount;

                // B. Update trạng thái Vé
                $ticket->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid'
                ]);

                // C. Lưu Log Ví (WalletLog)
                WalletLog::create([
                    'wallet_id'   => $wallet->id,
                    'type'        => 'payment',
                    'amount'      => $paymentAmount,
                    'before_balance' => $beforeBalance,
                    'after_balance'  => $afterBalance,
                    'description' => 'Thanh toán vé #' . $ticket->id,
                ]);

                // D. Tạo MoneyFlow
                MoneyFlow::create([
                    'booking_id' => $ticket->id,
                    'total_amount' => $paymentAmount,
                    'promotion_id' => $ticket->promotion_id ?? null,
                    'promotion_amount' => $discount,
                    'venue_id' => $venue_id,
                    'admin_amount' => $adminAmount,
                    'venue_owner_amount' => $venueOwnerAmount,
                    'status' => 'pending'
                ]);
            });

            // === [BẮT ĐẦU ĐOẠN GỬI MAIL] ===
            // Đặt ở đây để nếu gửi mail lỗi thì tiền vẫn đã trừ thành công (không rollback DB)
            try {
                // Load lại ticket kèm các quan hệ cần thiết cho file view email
                $ticketForMail = Ticket::with([
                    'user:id,name,email,role_id,phone,avt',
                    'items:id,ticket_id,booking_id,unit_price,discount_amount,status',
                    'items.booking:id,court_id,date,status,time_slot_id',
                    'items.booking.court:id,name,venue_id',
                    'items.booking.court.venue:id,name', // Cần thiết để hiện tên sân
                    'items.booking.timeSlot:id,start_time,end_time', // Cần thiết để hiện giờ
                ])->find($ticket->id);

                $user = Auth::user(); // Lấy user hiện tại đang đăng nhập

                // Nếu user có email thì gửi
                if ($user && $user->email) {
                    \Illuminate\Support\Facades\Mail::to($user->email)
                        ->send(new \App\Mail\Booking_Status($ticketForMail));

                    Log::info("Đã gửi mail vé ví thành công cho: " . $user->email);
                }
            } catch (\Exception $mailEx) {
                // Chỉ log lỗi mail, KHÔNG return lỗi ra ngoài client
                Log::error("Thanh toán Ví thành công nhưng gửi mail thất bại: " . $mailEx->getMessage());
            }
            // === [KẾT THÚC ĐOẠN GỬI MAIL] ===

            return response()->json(['message' => 'Thanh toán thành công!'], 200);
        } catch (\Exception $e) {
            Log::error("Lỗi thanh toán ví Ticket #{$ticket->id}: " . $e->getMessage());
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
