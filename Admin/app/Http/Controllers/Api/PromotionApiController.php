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
        $now = now();

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

            // *** LOGIC MỚI: Chưa bắt đầu ***
            if ($voucher->start_at && $now->lessThan($voucher->start_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá chưa đến ngày bắt đầu.'
                ], 400);
            }

            // Hết hạn (Logic cũ)
            if ($voucher->end_at && $now->greaterThan($voucher->end_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã hết hạn.'
                ], 400);
            }

            // *** LOGIC MỚI: Hết lượt sử dụng ***
            if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá đã hết lượt sử dụng.'
                ], 400);
            }

            // Chuẩn hoá data theo type Voucher trên FE
            $result = [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'value' => floatval($voucher->value),
                'type' => $voucher->type === '%' ? '%' : 'VND',
                'start_at' => $voucher->start_at, // *** MỚI: Thêm start_at ***
                'expires_at' => $voucher->end_at,
                'max_discount_amount' => $voucher->type === '%' ? floatval($voucher->max_discount_amount) : null, // *** MỚI: Thêm max_discount_amount ***
            ];

            return response()->json([
                'success' => true,
                'message' => 'Mã giảm giá hợp lệ.',
                'data' => $result
            ]);
        }

        // ==== TRƯỜNG HỢP 2: KHÔNG CÓ CODE - LẤY TẤT CẢ VOUCHER HỢP LỆ ====
        
        // *** LOGIC MỚI: Lấy tất cả voucher HỢP LỆ (đã bắt đầu, còn hạn, còn lượt) ***
        $vouchers = Promotion::where(function ($query) use ($now) {
                // 1. Phải bắt đầu rồi
                $query->where('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                // 2. Phải chưa kết thúc (hoặc không có ngày kết thúc - logic cũ)
                $query->where('end_at', '>=', $now)
                      ->orWhereNull('end_at');
            })
            ->where(function ($query) {
                // 3. Phải còn lượt sử dụng (hoặc không giới hạn)
                $query->whereNull('usage_limit')
                      ->orWhereRaw('used_count < usage_limit');
            })
            ->get();

        // Chuẩn hoá mảng data
        $result = $vouchers->map(function ($voucher) {
            return [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'value' => floatval($voucher->value),
                'type' => $voucher->type === '%' ? '%' : 'VND',
                'start_at' => $voucher->start_at, // *** MỚI: Thêm start_at ***
                'expires_at' => $voucher->end_at,
                'max_discount_amount' => $voucher->type === '%' ? floatval($voucher->max_discount_amount) : null, // *** MỚI: Thêm max_discount_amount ***
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result // Trả về mảng các voucher
        ]);
    }
}