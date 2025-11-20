<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
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

        $partnerCode  = $request->partnerCode;
        $orderId      = $request->orderId;
        $requestId    = $request->requestId;
        $amount       = $request->amount;
        $orderInfo    = $request->orderInfo;
        $orderType    = $request->orderType;
        $transId      = $request->transId;
        $resultCode   = $request->resultCode;
        $message      = $request->message;
        $payType      = $request->payType;
        $responseTime = $request->responseTime;
        $extraData    = $request->extraData;
        $signature    = $request->signature;

        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');

        $rawHash = "accessKey=" . $accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&message=" . $message .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&orderType=" . $orderType .
            "&partnerCode=" . $partnerCode .
            "&payType=" . $payType .
            "&requestId=" . $requestId .
            "&responseTime=" . $responseTime .
            "&resultCode=" . $resultCode .
            "&transId=" . $transId;

        $mySignature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($mySignature != $signature) {
            Log::error("Sai chữ ký MoMo");
            return response()->json([
                'success' => false,
                'resultCode' => 1,
                'message' => 'Thanh toán thất bại'
            ]);
        }

        if ($resultCode == 0) {
            $ticketIdParts = explode('_', $orderId);
            $ticketId = $ticketIdParts[0];

            $ticket = Ticket::find($ticketId);

            if ($ticket) {
                $ticket->status = 'confirmed';
                $ticket->payment_status = 'paid';
                $ticket->save();
            }

            return response()->json([
                'success' => true,
                'resultCode' => 0,
                'message' => 'Thanh toán thành công'
            ]);
        }

        return response()->json([
            'success' => false,
            'resultCode' => 1,
            'message' => 'Thanh toán thất bại'
        ]);
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
