<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\CheckFlashSale;
use App\Models\FlashSaleCampaign;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FlashSaleCampaignController extends Controller
{
    // Trang danh sách (Index)
    public function index()
    {
        $user = Auth::user();
        $now = now();

        // Chỉ lấy các chiến dịch của tôi và chưa kết thúc
        $flashSaleCampaigns = FlashSaleCampaign::where('owner_id', $user->id)
            ->where('end_datetime', '>', $now)
            ->orderBy('start_datetime', 'asc')
            ->get();

        return view('venue_owner.flash_sale_campaigns.index', compact('flashSaleCampaigns'));
    }

    // Xử lý lưu (Store) từ Modal
    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // Sử dụng sau 'now' trừ đi 1 phút để tránh lỗi lệch giây khi submit
            'start_datetime' => 'required|date|after:' . now()->subMinute(),
            'end_datetime' => 'required|date|after:start_datetime',
        ], [
            'name.required' => 'Vui lòng nhập tên chiến dịch.',
            'start_datetime.after' => 'Thời gian bắt đầu không được ở trong quá khứ.',
            'end_datetime.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
        ]);

        try {
            // 2. Gán owner_id và tạo mới
            $campaign = FlashSaleCampaign::create([
                'owner_id' => Auth::id(),
                'name' => $request->name,
                'description' => $request->description,
                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,
                'status' => 'pending', // Mặc định là chờ
            ]);

            CheckFlashSale::dispatch($campaign->id, 'active')
                ->delay($campaign->start_datetime);

            // Job 2: Chuyển trạng thái sang 'ended' (kết thúc) khi đến giờ KẾT THÚC
            CheckFlashSale::dispatch($campaign->id, 'inactive')
                ->delay($campaign->end_datetime);

            // 3. Chuyển hướng sang Bước 2 (Thiết lập giá sân)
            return redirect()->route('owner.flash_sale_campaigns.show', $campaign->id)
                ->with('success', 'Đã tạo khung chiến dịch. Bây giờ hãy chọn sân giảm giá!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Trang chi tiết để chọn Item (Show)
    public function show($id)
    {
        // 1. Lấy thông tin Campaign
        $campaign = FlashSaleCampaign::where('id', $id)
            ->where('owner_id', \Illuminate\Support\Facades\Auth::id())
            ->with('items')
            ->firstOrFail();

        $joinedIds = $campaign->items->pluck('availability_id')->toArray();
        $oldPrice = $campaign->items->first() ? (int)$campaign->items->first()->sale_price : '';

        // 2. Thời gian hiện tại để lọc các khung giờ đã qua
        $now = now();

        // 3. Truy vấn danh sách khung giờ (Availabilities)
        $rawAvailabilities = \App\Models\Availability::query()
            ->whereHas('court.venue', function ($q) {
                $q->where('owner_id', \Illuminate\Support\Facades\Auth::id());
            })
            ->where('status', 'open') // CHỈ LẤY NHỮNG SÂN ĐANG TRỐNG (Chưa bị đặt)
            ->whereHas('timeSlot', function ($q) use ($campaign, $now) {
                // Lọc theo khung giờ của Campaign
                $q->whereRaw("TIMESTAMP(availabilities.date, time_slots.start_time) >= ?", [$campaign->start_datetime])
                    ->whereRaw("TIMESTAMP(availabilities.date, time_slots.end_time) <= ?", [$campaign->end_datetime])

                    // THÊM: Chỉ lấy những khung giờ CÓ THỜI GIAN BẮT ĐẦU LỚN HƠN HIỆN TẠI
                    // (Để ẩn các giờ đã trôi qua trong ngày)
                    ->whereRaw("TIMESTAMP(availabilities.date, time_slots.start_time) > ?", [$now]);
            })
            ->with(['court.venue', 'timeSlot'])
            ->get();

        // 4. Group dữ liệu để hiển thị theo Venue và Court
        $groupedAvailabilities = $rawAvailabilities->groupBy([
            fn($item) => $item->court->venue->name,
            fn($item) => $item->court->name
        ]);

        return view('venue_owner.flash_sale_campaigns.show', compact(
            'campaign',
            'groupedAvailabilities',
            'joinedIds',
            'oldPrice'
        ));
    }
}