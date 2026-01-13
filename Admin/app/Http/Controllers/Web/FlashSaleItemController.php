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
        ]);

        return DB::transaction(function () use ($request, $user) {
            $campaign = FlashSaleCampaign::where('id', $request->campaign_id)
                ->where('owner_id', $user->id)
                ->firstOrFail();

            // 1. Sync Flash Sale Items
            FlashSaleItem::where('campaign_id', $campaign->id)
                ->whereNotIn('availability_id', $request->availability_ids)
                ->delete();

            $currentVenueIds = [];

            // Lưu items và lấy danh sách Venue ID
            foreach ($request->availability_ids as $availabilityId) {
                $availability = Availability::with('court.venue')->find($availabilityId);
                if ($availability && $availability->court->venue->owner_id == $user->id) {
                    $venueId = $availability->court->venue->id;
                    FlashSaleItem::updateOrCreate(
                        ['campaign_id' => $campaign->id, 'availability_id' => $availabilityId],
                        ['sale_price' => $request->sale_price, 'status' => 'active']
                    );
                    $currentVenueIds[] = $venueId;
                }
            }

            $uniqueVenueIds = array_unique($currentVenueIds);

            // 2. XỬ LÝ BÀI ĐĂNG (POST) + HÌNH ẢNH

            // Format nội dung
            $startTime = Carbon::parse($campaign->start_datetime)->format('H:i d/m');
            $endTime   = Carbon::parse($campaign->end_datetime)->format('H:i d/m');
            $priceFormatted = number_format($request->sale_price);

            $newContent = "🔥 FLASH SALE CỰC CĂNG: {$campaign->name} 🔥\n" .
                "⏰ Khung giờ vàng: {$startTime} ➔ {$endTime}\n" .
                "💸 Giá huỷ diệt chỉ: {$priceFormatted}đ/slot\n" .
                "⚡️ Số lượng có hạn, chốt đơn ngay kẻo lỡ!";

            foreach ($uniqueVenueIds as $venueId) {
                // A. Tạo hoặc cập nhật Post
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

                // B. XỬ LÝ ẢNH (Mới thêm)
                // Lấy thông tin Venue và ảnh chính (primary) của nó
                $venue = Venue::with(['images' => function ($q) {
                    $q->where('is_primary', 1);
                }])->find($venueId);

                // Nếu Venue có ảnh chính
                if ($venue && $venue->images->isNotEmpty()) {
                    $sourceImage = $venue->images->first();

                    // Logic: Xóa ảnh cũ của Post này (nếu có) để cập nhật ảnh mới nhất từ Venue
                    // Hoặc bạn có thể check if(!$post->images()->exists()) nếu không muốn update ảnh
                    $post->images()->delete();

                    // Tạo ảnh mới cho Post (Copy URL từ Venue sang)
                    $post->images()->create([
                        'url' => $sourceImage->url,
                        'is_primary' => 1, // Set ảnh này là ảnh chính của bài Post
                        // Các trường khác nếu bảng images của bạn yêu cầu
                    ]);
                }
            }

            // C. Dọn dẹp bài đăng thừa (Logic cũ)
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