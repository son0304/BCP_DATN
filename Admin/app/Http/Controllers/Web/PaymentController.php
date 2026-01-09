<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    /**
     * TẠO MÃ QR TẠM THỜI
     * Khách quét mã này, tiền sẽ được "treo" trong hệ thống MoMo kèm theo temp_order_id
     */
    public function generateTempQr(Request $request)
    {
        try {
            Log::info("Momo Temp QR Request:", $request->all());

            $amount = (int)round($request->input('total_amount'));
            $tempOrderId = $request->input('temp_order_id');

            if ($amount < 1000) {
                return response()->json(['success' => false, 'message' => 'Số tiền tối thiểu 1.000đ']);
            }

            $endpoint    = env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
            $partnerCode = env('MOMO_PARTNER_CODE');
            $accessKey   = env('MOMO_ACCESS_KEY');
            $secretKey   = env('MOMO_SECRET_KEY');

            // URL để MoMo gọi về (Sử dụng Ngrok nếu chạy local)
            $ipnUrl      = env('MOMO_IPN_URL_LOCAL') ?: route('payment.momo.ipn-temp');
            $redirectUrl = route('owner.bookings.index');

            $orderInfo   = "Thanh toan don tam " . $tempOrderId;
            $requestId   = $tempOrderId;
            $requestType = "captureWallet";
            $extraData   = "";

            $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$tempOrderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$requestId&requestType=$requestType";
            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            $data = [
                'partnerCode' => $partnerCode,
                'partnerName' => "BCP Sports",
                'storeId'     => "Owner_Store",
                'requestId'   => $requestId,
                'amount'      => $amount,
                'orderId'     => $tempOrderId,
                'orderInfo'   => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl'      => $ipnUrl,
                'lang'        => 'vi',
                'extraData'   => $extraData,
                'requestType' => $requestType,
                'signature'   => $signature
            ];

            $response = Http::withoutVerifying()->timeout(15)->post($endpoint, $data);
            $jsonResult = $response->json();

            if (isset($jsonResult['payUrl'])) {
                // Khởi tạo trạng thái chờ trong Cache
                Cache::put("momo_temp_paid_$tempOrderId", ['status' => 'pending'], 600);

                return response()->json([
                    'success' => true,
                    'qr_code_url' => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($jsonResult['payUrl']),
                    'pay_url' => $jsonResult['payUrl']
                ]);
            }

            return response()->json(['success' => false, 'message' => $jsonResult['message'] ?? 'MoMo lỗi']);
        } catch (\Exception $e) {
            Log::error("Momo Temp QR Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi Server: ' . $e->getMessage()]);
        }
    }

    /**
     * MOMO IPN (CALLBACK)
     * Lưu thông tin giao dịch vào Cache để hàm store lấy ra tạo Transaction
     */
    public function momoIpnTemp(Request $request)
    {
        Log::info("IPN MoMo Temp Received:", $request->all());

        if ($request->resultCode == 0) {
            // Lưu mảng thông tin thay vì chỉ lưu chuỗi 'paid'
            Cache::put("momo_temp_paid_" . $request->orderId, [
                'status' => 'paid',
                'amount' => $request->amount,
                'transId' => $request->transId,
                'payment_source' => 'momo',
                'payType' => $request->payType
            ], 600);
        }

        return response()->json(['message' => 'IPN Received']);
    }

    /**
     * POLLING & QUERY STATUS
     */
    public function checkTempPayment(Request $request)
    {
        $tempOrderId = $request->temp_order_id;

        // 1. Kiểm tra Cache trước
        $cacheData = Cache::get("momo_temp_paid_$tempOrderId");
        if (is_array($cacheData) && $cacheData['status'] === 'paid') {
            return response()->json(['success' => true, 'paid' => true]);
        }

        // 2. Nếu Cache chưa có, chủ động gọi MoMo Query API
        try {
            $endpoint = "https://test-payment.momo.vn/v2/gateway/api/query";
            $partnerCode = env('MOMO_PARTNER_CODE');
            $accessKey = env('MOMO_ACCESS_KEY');
            $secretKey = env('MOMO_SECRET_KEY');
            $requestId = time() . "";

            $rawHash = "accessKey=$accessKey&orderId=$tempOrderId&partnerCode=$partnerCode&requestId=$requestId";
            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            $data = [
                'partnerCode' => $partnerCode,
                'requestId'   => $requestId,
                'orderId'     => $tempOrderId,
                'signature'   => $signature,
                'lang'        => 'vi'
            ];

            $response = Http::withoutVerifying()->post($endpoint, $data);
            $res = $response->json();

            if (isset($res['resultCode']) && $res['resultCode'] == 0) {
                $paymentInfo = [
                    'status' => 'paid',
                    'amount' => $res['amount'],
                    'transId' => $res['transId'] ?? 'N/A',
                    'payment_source' => 'momo'
                ];
                Cache::put("momo_temp_paid_$tempOrderId", $paymentInfo, 600);

                return response()->json(['success' => true, 'paid' => true]);
            }
        } catch (\Exception $e) {
            Log::error("Query MoMo Status Error: " . $e->getMessage());
        }

        return response()->json(['success' => true, 'paid' => false]);
    }
}
