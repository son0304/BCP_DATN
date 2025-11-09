<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionApiController extends Controller
{
    public function index(Request $request)
    {
        $code = $request->input('code');

        // Nếu không có code → trả về data = null, tránh lỗi 422
        if (!$code) {
            return response()->json([
                'success' => true,
                'data' => null
            ]);
        }

        $voucher = Promotion::where('code', $code)->first();

        // Không tìm thấy
        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không hợp lệ.'
            ], 404);
        }

        // Hết hạn
        if ($voucher->end_at && now()->greaterThan($voucher->end_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá đã hết hạn.'
            ], 400);
        }

        // Chuẩn hoá data theo type Voucher trên FE
        $result = [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'value' => floatval($voucher->value),
            'type' => $voucher->type === '%' ? '%' : 'VND',
            'expires_at' => $voucher->end_at
        ];

        return response()->json([
            'success' => true,
            'message' => 'Mã giảm giá hợp lệ.',
            'data' => $result
        ]);
    }
}
