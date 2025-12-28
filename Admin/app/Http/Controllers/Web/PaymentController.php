<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Cache;
use DB;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

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
}