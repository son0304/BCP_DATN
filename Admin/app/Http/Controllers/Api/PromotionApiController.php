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
        $user    = Auth::user(); // Có thể null nếu chưa login

        if (!$venueId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu venue_id.'
            ], 400);
        }

        $venue = Venue::find($venueId);
        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => 'Sân không tồn tại.'
            ], 404);
        }
        $ownerId = $venue->owner_id;

        // --- TRƯỜNG HỢP 1: TÌM MÃ CỤ THỂ ---
        if ($code) {
            $voucher = Promotion::where('code', $code)->first();

            if (!$voucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không tồn tại.'
                ], 404);
            }

            // Truyền orderTotal là null để lấy thông tin voucher dù đơn 0đ
            if (!$voucher->isEligible(null, $venueId, $ownerId, $user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã này không áp dụng cho cửa hàng hoặc tài khoản của bạn.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatVoucher($voucher)
            ]);
        }

        // --- TRƯỜNG HỢP 2: LẤY DANH SÁCH ---
        // Tối ưu: Chỉ lấy các voucher đang Active ở cấp DB trước
        $query = Promotion::where('process_status', 'active')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now());

        // Nếu muốn query kỹ hơn về venue_id tại DB thì thêm where vào đây
        // Tuy nhiên logic isValidForVenue của bạn khá phức tạp nên lọc PHP sau cũng tạm ổn
        // nhưng tốt nhất là filter owner/venue ngay trong query nếu được.

        $vouchers = $query->get()
            ->filter(function ($voucher) use ($venueId, $ownerId, $user) {
                // Truyền null để hiển thị cả voucher chưa đủ tiền mua
                return $voucher->isEligible(null, $venueId, $ownerId, $user);
            })
            ->values()
            ->map(function ($voucher) {
                return $this->formatVoucher($voucher);
            });

        return response()->json([
            'success' => true,
            'data' => $vouchers
        ]);
    }

    // Helper format response để đồng nhất dữ liệu
    private function formatVoucher($voucher)
    {
        return [
            'id'                  => $voucher->id,
            'code'                => $voucher->code,
            'value'               => (float) $voucher->value,
            'type'                => $voucher->type === 'percentage' ? '%' : 'VND',
            'start_at'            => $voucher->start_at,
            'end_date'            => $voucher->end_at, // React dùng field này
            // QUAN TRỌNG: React cần field này để validate
            'min_order_amount'    => (float) $voucher->min_order_value,
            'max_discount_amount' => $voucher->type === 'percentage'
                ? (float) $voucher->max_discount_amount
                : null,
        ];
    }
}
