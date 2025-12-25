<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Court;
use Illuminate\Http\Request;

class PromotionApiController extends Controller
{
    public function index(Request $request)
    {
        $code = $request->input('code');
        $venueId = $request->input('venue_id'); // Venue ID khi booking
        $courtId = $request->input('court_id'); // Court ID khi booking (để lấy venue_id)
        $now = now();

        // Nếu có court_id, lấy venue_id từ court
        if ($courtId && !$venueId) {
            $court = Court::find($courtId);
            if ($court) {
                $venueId = $court->venue_id;
            }
        }

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

            // *** KIỂM TRA VENUE: Voucher phải là toàn hệ thống (venue_id = null) hoặc match với venue đang đặt ***
            if ($venueId && $voucher->venue_id !== null && $voucher->venue_id != $venueId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá này chỉ áp dụng cho venue khác.'
                ], 400);
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
                'start_at' => $voucher->start_at,
                'expires_at' => $voucher->end_at,
                'max_discount_amount' => $voucher->type === '%' ? floatval($voucher->max_discount_amount) : null,
                'venue_id' => $voucher->venue_id, // Thêm venue_id để frontend biết
            ];

            return response()->json([
                'success' => true,
                'message' => 'Mã giảm giá hợp lệ.',
                'data' => $result
            ]);
        }

        // ==== TRƯỜNG HỢP 2: KHÔNG CÓ CODE - LẤY TẤT CẢ VOUCHER HỢP LỆ ====
        
        // *** LOGIC MỚI: Lấy tất cả voucher HỢP LỆ (đã bắt đầu, còn hạn, còn lượt) ***
        $query = Promotion::where(function ($query) use ($now) {
                // 1. Phải bắt đầu rồi
                $query->where('start_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                // 2. Phải chưa kết thúc (hoặc không có ngày kết thúc)
                $query->where('end_at', '>=', $now)
                      ->orWhereNull('end_at');
            })
            ->where(function ($query) {
                // 3. Phải còn lượt sử dụng (hoặc không giới hạn)
                $query->whereNull('usage_limit')
                      ->orWhereRaw('used_count < usage_limit');
            });

        // *** FILTER THEO VENUE: Nếu có venue_id, chỉ lấy voucher toàn hệ thống (null) hoặc voucher của venue đó ***
        if ($venueId) {
            $query->where(function ($q) use ($venueId) {
                $q->whereNull('venue_id') // Voucher toàn hệ thống
                  ->orWhere('venue_id', $venueId); // Hoặc voucher của venue này
            });
        }

        $vouchers = $query->get();

        // Chuẩn hoá mảng data
        $result = $vouchers->map(function ($voucher) {
            return [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'value' => floatval($voucher->value),
                'type' => $voucher->type === '%' ? '%' : 'VND',
                'start_at' => $voucher->start_at,
                'expires_at' => $voucher->end_at,
                'max_discount_amount' => $voucher->type === '%' ? floatval($voucher->max_discount_amount) : null,
                'venue_id' => $voucher->venue_id, // Thêm venue_id để frontend biết
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result // Trả về mảng các voucher
        ]);
    }
}