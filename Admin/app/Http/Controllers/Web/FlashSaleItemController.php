<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{FlashSaleItem, FlashSaleCampaign, Availability, Post, Venue}; // Nhớ import Venue
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Carbon\Carbon;

class FlashSaleItemController extends Controller
{
    public function create_flash_sale_items(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'campaign_id' => 'required|exists:flash_sale_campaigns,id',
            'availability_ids' => 'required|array',
            'sale_price' => 'required|numeric|min:0',
        ], [
            'sale_price.required' => 'Vui lòng nhập giá giảm.',
            'availability_ids.required' => 'Vui lòng chọn ít nhất một sân.'
        ]);

        // --- BỔ SUNG: KIỂM TRA GIÁ GỐC TRƯỚC KHI VÀO TRANSACTION ---
        $availabilities = Availability::with('court')
            ->whereIn('id', $request->availability_ids)
            ->get();

        foreach ($availabilities as $availability) {
            // So sánh sale_price với giá gốc (price) của availability
            if ($request->sale_price > $availability->price) {
                return back()->withInput()->with(
                    'error',
                    "Giá giảm (" . number_format($request->sale_price) . "đ) " .
                        "không được lớn hơn giá gốc (" . number_format($availability->price) . "đ) " .
                        "của sân {$availability->court->name}."
                );
            }
        }
        // --------------------------------------------------------

        return DB::transaction(function () use ($request, $user, $availabilities) {
            $campaign = FlashSaleCampaign::where('id', $request->campaign_id)
                ->where('owner_id', $user->id)
                ->firstOrFail();

            // 1. Sync Flash Sale Items (Xóa những item không còn được chọn)
            FlashSaleItem::where('campaign_id', $campaign->id)
                ->whereNotIn('availability_id', $request->availability_ids)
                ->delete();

            $currentVenueIds = [];

            // Lưu items và lấy danh sách Venue ID
            // Sử dụng danh sách $availabilities đã lấy ở trên để tối ưu query
            foreach ($availabilities as $availability) {
                // Kiểm tra quyền sở hữu sân
                if ($availability->court->venue->owner_id == $user->id) {
                    $venueId = $availability->court->venue->id;

                    FlashSaleItem::updateOrCreate(
                        ['campaign_id' => $campaign->id, 'availability_id' => $availability->id],
                        ['sale_price' => $request->sale_price, 'status' => 'active']
                    );
                    $currentVenueIds[] = $venueId;
                }
            }

            $uniqueVenueIds = array_unique($currentVenueIds);

            // 2. XỬ LÝ BÀI ĐĂNG (POST) + HÌNH ẢNH
            $startTime = Carbon::parse($campaign->start_datetime)->format('H:i d/m');
            $endTime   = Carbon::parse($campaign->end_datetime)->format('H:i d/m');
            $priceFormatted = number_format($request->sale_price);

            $newContent = "🔥 FLASH SALE CỰC CĂNG: {$campaign->name} 🔥\n" .
                "⏰ Khung giờ vàng: {$startTime} ➔ {$endTime}\n" .
                "💸 Giá huỷ diệt chỉ: {$priceFormatted}đ/slot\n" .
                "⚡️ Số lượng có hạn, chốt đơn ngay kẻo lỡ!";

            foreach ($uniqueVenueIds as $venueId) {
                $post = Post::updateOrCreate(
                    [
                        'type' => 'sale',
                        'reference_id' => $campaign->id,
                        'venue_id' => $venueId
                    ],
                    [
                        'user_id' => $user->id,
                        'content' => $newContent,
                        'status' => 'active',
                        'phone_contact' => $user->phone ?? ''
                    ]
                );

                // B. XỬ LÝ ẢNH
                $venue = Venue::with(['images' => function ($q) {
                    $q->where('is_primary', 1);
                }])->find($venueId);

                if ($venue && $venue->images->isNotEmpty()) {
                    $sourceImage = $venue->images->first();
                    $post->images()->delete();
                    $post->images()->create([
                        'url' => $sourceImage->url,
                        'is_primary' => 1,
                    ]);
                }
            }

            // C. Dọn dẹp bài đăng thừa
            if (!empty($uniqueVenueIds)) {
                Post::where('type', 'sale')
                    ->where('reference_id', $campaign->id)
                    ->whereNotIn('venue_id', $uniqueVenueIds)->delete();
            } else {
                Post::where('type', 'sale')
                    ->where('reference_id', $campaign->id)->delete();
            }

            return redirect()->route('owner.flash_sale_campaigns.index')
                ->with('success', 'Đã cập nhật Flash Sale và đồng bộ hình ảnh!');
        });
    }
}
