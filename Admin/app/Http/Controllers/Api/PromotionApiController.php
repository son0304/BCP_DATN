<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionApiController extends Controller
{
    public function index(Request $request)
    {
        $code    = $request->input('code');
        $venueId = $request->input('venue_id');
        $user    = Auth::guard('sanctum')->user(); // Lấy user nếu có login (API thường dùng guard sanctum/api)

        // 1. Validate Venue bắt buộc
        if (!$venueId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu venue_id.'
            ], 400);
        }

        // 2. Lấy Object Venue (BẮT BUỘC phải có để truyền vào Model mới)
        $venue = Venue::find($venueId);
        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Sân không tồn tại.'
            ], 404);
        }

        // --- TRƯỜNG HỢP 1: TÌM MÃ CỤ THỂ (Khi người dùng nhập code vào ô input) ---
        if ($code) {
            // Tìm mã chưa bị xóa (SoftDelete tự động lo)
            $voucher = Promotion::where('code', $code)->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không tồn tại.'
                ], 404);
            }

            // Gọi hàm isEligible của Model mới
            // Tham số: (Tổng tiền = null, Object Venue, Object User)
            if (!$voucher->isEligible(null, $venue, $user)) {

                // Trả về lỗi chi tiết hơn (Optional)
                $msg = 'Mã này không áp dụng cho sân này.';
                if (!$voucher->isActive()) $msg = 'Mã đã hết hạn hoặc hết lượt sử dụng.';
                if ($user && !$voucher->canUserUse($user)) $msg = 'Bạn không đủ điều kiện dùng mã này (ví dụ: chỉ dành cho khách mới).';

                return response()->json([
                    'success' => false,
                    'message' => $msg
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatVoucher($voucher)
            ]);
        }

        // --- TRƯỜNG HỢP 2: LẤY DANH SÁCH MÃ HỢP LỆ (Để hiển thị list cho user chọn) ---

        // Tối ưu Query: Chỉ lấy Active + Còn hạn + (Của sân này HOẶC Chung hệ thống)
        // Việc filter venue_id ngay ở SQL giúp giảm tải cho PHP phải loop quá nhiều record
        $query = Promotion::where('process_status', 'active')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->where(function ($q) use ($venueId) {
                $q->where('venue_id', $venueId)      // Mã riêng của sân
                    ->orWhereNull('venue_id');         // Hoặc mã chung (sẽ check owner/admin sau)
            });

        // Lấy danh sách về và lọc tiếp bằng logic PHP phức tạp (check role admin/owner, check user đã dùng chưa)
        $vouchers = $query->get()
            ->filter(function ($voucher) use ($venue, $user) {
                // Sử dụng hàm Model mới
                return $voucher->isEligible(null, $venue, $user);
            })
            ->values() // Reset array keys
            ->map(function ($voucher) {
                return $this->formatVoucher($voucher);
            });

        return response()->json([
            'success' => true,
            'data' => $vouchers
        ]);
    }

    // Helper format response chuẩn cho Frontend (React/Vue/Mobile)
    private function formatVoucher($voucher)
    {
        return [
            'id'                  => $voucher->id,
            'code'                => $voucher->code,
            'description'         => $voucher->description,
            'value'               => (float) $voucher->value,
            'type'                => $voucher->type === 'percentage' ? '%' : 'VND', // Frontend dễ hiển thị
            'type_raw'            => $voucher->type, // Frontend dùng để tính toán
            'start_at'            => $voucher->start_at,
            'end_date'            => $voucher->end_at, // React thường dùng field này
            'min_order_amount'    => (float) $voucher->min_order_value,
            'max_discount_amount' => $voucher->type === 'percentage'
                ? (float) $voucher->max_discount_amount
                : null,
            'usage_limit'         => $voucher->usage_limit, // -1 là vô hạn
            'is_unlimited'        => $voucher->usage_limit < 0, // Cờ tiện ích cho FE
        ];
    }
}