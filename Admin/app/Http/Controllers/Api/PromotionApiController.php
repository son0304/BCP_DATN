<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionApiController extends Controller
{
    public function index(Request $request)
    {
        // Validate input
        $request->validate([
            'code' => 'required|string'
        ]);

        $code = $request->input('code');

        $voucher = Promotion::where('code', $code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không hợp lệ.'
            ], 404);
        }

        if ($voucher->expires_at && now()->greaterThan($voucher->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá đã hết hạn.'
            ], 400);
        }

        // Nếu hợp lệ
        return response()->json([
            'success' => true,
            'message' => 'Mã giảm giá hợp lệ.',
            'discount' => $voucher->discount,
            'data' => $voucher
        ]);
    }
}
