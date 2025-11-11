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

        // ==== TRƯỜNG HỢP 1: CÓ CODE - XÁC THỰC 1 VOUCHER ====
        if ($code) {
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

        // ==== TRƯỜNG HỢP 2: KHÔNG CÓ CODE - LẤY TẤT CẢ VOUCHER HỢP LỆ ====
        
        // Lấy tất cả voucher CÒN HẠN (chưa hết hạn hoặc không có ngày hết hạn)
        $vouchers = Promotion::where(function ($query) {
                $query->whereNull('end_at') // Không có ngày hết hạn
                      ->orWhere('end_at', '>', now()); // Hoặc ngày hết hạn còn trong tương lai
            })
            ->get();

        // Chuẩn hoá mảng data
        $result = $vouchers->map(function ($voucher) {
            return [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'value' => floatval($voucher->value),
                'type' => $voucher->type === '%' ? '%' : 'VND',
                'expires_at' => $voucher->end_at
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result // Trả về mảng các voucher
        ]);
    }
}