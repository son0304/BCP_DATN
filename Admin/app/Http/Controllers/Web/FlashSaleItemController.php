<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FlashSaleItem;
use App\Models\FlashSaleCampaign;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlashSaleItemController extends Controller
{
    public function create_flash_sale_items(Request $request)
    {
        $user = Auth::user();

        // 1. Validate dữ liệu
        $validatedData = $request->validate([
            'campaign_id' => 'required|exists:flash_sale_campaigns,id',
            'availability_ids' => 'required|array',
            'availability_ids.*' => 'exists:availabilities,id',
            'sale_price' => 'required|numeric|min:0',
        ]);

        $campaignId = $request->campaign_id;
        $selectedIds = $request->availability_ids;
        $salePrice = $request->sale_price;

        // 2. Bảo mật: Kiểm tra xem Campaign này có đúng là của chủ sân này không
        $campaign = FlashSaleCampaign::where('id', $campaignId)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        // 3. Logic Sync (Đồng bộ):
        // Xóa những Item cũ của Campaign này mà KHÔNG nằm trong danh sách vừa gửi lên
        FlashSaleItem::where('campaign_id', $campaignId)
            ->whereNotIn('availability_id', $selectedIds)
            ->delete();

        // 4. Tạo mới hoặc Cập nhật giá cho các Item được chọn
        foreach ($selectedIds as $availabilityId) {
            // Kiểm tra thêm: Slot này phải thuộc về sân của chủ sân này
            $isOwnAvailability = Availability::where('id', $availabilityId)
                ->whereHas('court.venue', function ($q) use ($user) {
                    $q->where('owner_id', $user->id);
                })->exists();

            if ($isOwnAvailability) {
                FlashSaleItem::updateOrCreate(
                    [
                        'campaign_id' => $campaignId,
                        'availability_id' => $availabilityId
                    ],
                    [
                        'sale_price' => $salePrice,
                        'status' => 'active'
                    ]
                );
            }
        }

        return redirect()->route('owner.flash_sale_campaigns.index')
            ->with('success', 'Đã thiết lập giảm giá Flash Sale thành công!');
    }
}
