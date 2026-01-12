<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{FlashSaleItem, FlashSaleCampaign, Availability, Post};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class FlashSaleItemController extends Controller
{
    public function create_flash_sale_items(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'campaign_id' => 'required|exists:flash_sale_campaigns,id',
            'availability_ids' => 'required|array',
            'sale_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request, $user) {
            $campaign = FlashSaleCampaign::where('id', $request->campaign_id)
                ->where('owner_id', $user->id)
                ->firstOrFail();

            // 1. Sync Flash Sale Items
            FlashSaleItem::where('campaign_id', $campaign->id)
                ->whereNotIn('availability_id', $request->availability_ids)
                ->delete();

            $venueId = null;

            foreach ($request->availability_ids as $availabilityId) {
                $availability = Availability::with('court.venue')->find($availabilityId);

                // Kiểm tra quyền sở hữu và lấy Venue ID
                if ($availability && $availability->court->venue->owner_id == $user->id) {
                    $venueId = $availability->court->venue->id; // Lấy venue_id từ slot đầu tiên hợp lệ

                    FlashSaleItem::updateOrCreate(
                        ['campaign_id' => $campaign->id, 'availability_id' => $availabilityId],
                        ['sale_price' => $request->sale_price, 'status' => 'active']
                    );
                }
            }

            // 2. TỰ ĐỘNG TẠO/CẬP NHẬT BÀI ĐĂNG TRÊN BẢNG TIN
            if ($venueId) {
                Post::updateOrCreate(
                    [
                        'type' => 'sale',
                        'reference_id' => $campaign->id // Dùng ID chiến dịch để không tạo trùng bài
                    ],
                    [
                        'user_id' => $user->id,
                        'venue_id' => $venueId,
                        'content' => "🔥 SIÊU GIẢM GIÁ: Chiến dịch " . $campaign->name . " đang diễn ra với giá chỉ " . number_format($request->sale_price) . "đ. Đặt sân ngay để nhận ưu đãi!",
                        'status' => 'active',
                        'phone_contact' => $user->phone ?? ''
                    ]
                );
            }

            return redirect()->route('owner.flash_sale_campaigns.index')
                ->with('success', 'Đã cập nhật Flash Sale và bài đăng cộng đồng!');
        });
    }
}