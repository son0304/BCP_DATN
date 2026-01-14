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
    public function index(Request $request)
    {
        $user = Auth::user();

        // Khởi tạo query
        $query = FlashSaleCampaign::where('owner_id', $user->id);

        // 1. Tìm kiếm theo tên chiến dịch
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        // 2. Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Sắp xếp và phân trang
        $flashSaleCampaigns = $query->orderBy('start_datetime', 'desc')
            ->paginate(10)
            ->withQueryString(); // Giữ các tham số lọc khi chuyển trang

        return view('venue_owner.flash_sale_campaigns.index', compact('flashSaleCampaigns'));
    }

    // Xử lý lưu (Store) từ Modal
    public function store(Request $request)
    {
        // 1. Validate dữ liệu
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // Nới lỏng 10 phút để tránh lỗi "thời gian quá khứ" khi người dùng thao tác chậm
            'start_datetime' => 'required|date|after_or_equal:now',
            'end_datetime'   => 'required|date|after:start_datetime',
        ], [
            'name.required' => 'Vui lòng nhập tên chiến dịch.',
            'name.max' => 'Tên chiến dịch không quá 255 ký tự.',
            'start_datetime.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_datetime.date' => 'Định dạng thời gian không hợp lệ.',
            'start_datetime.after_or_equal' => 'Thời gian bắt đầu không được ở trong quá khứ.',
            'end_datetime.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_datetime.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
        ]);

        try {
            // 2. Tạo mới
            $campaign = FlashSaleCampaign::create([
                'owner_id' => Auth::id(),
                'name' => $request->name,
                'description' => $request->description,
                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,
                'status' => 'pending',
            ]);

            // Job kích hoạt
            CheckFlashSale::dispatch($campaign->id, 'active')->delay(\Carbon\Carbon::parse($campaign->start_datetime));
            // Job kết thúc
            CheckFlashSale::dispatch($campaign->id, 'inactive')->delay(\Carbon\Carbon::parse($campaign->end_datetime));

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

    // ... imports

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // 1. Validate dữ liệu mới
        $request->validate([
            'name' => 'required|string|max:255',
            'start_datetime' => 'required|date|after_or_equal:now',
            'end_datetime' => 'required|date|after:start_datetime',
        ], [
            'start_datetime.after_or_equal' => 'Thời gian bắt đầu không được ở trong quá khứ.',
            'end_datetime.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
        ]);

        $campaign = FlashSaleCampaign::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        // 2. Cập nhật thông tin Campaign
        $campaign->update([
            'name' => $request->name,
            'description' => $request->description,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
        ]);


        \App\Models\FlashSaleItem::where('campaign_id', $campaign->id)
            ->whereHas('availability', function ($query) use ($request) {
                // Sử dụng relationship 'timeSlot' đã khai báo trong Model Availability
                // Laravel sẽ tự biết cột nối là 'time_slot_id' hay 'timeslot_id'
                $query->whereHas('timeSlot', function ($qTime) use ($request) {
                    $qTime->where(function ($q) use ($request) {
                        // Logic: (Ngày + Giờ bắt đầu < Campaign Start) HOẶC (Ngày + Giờ kết thúc > Campaign End)
                        // Lưu ý: availabilities.date vẫn gọi được vì nó nằm trong query cha
                        $q->whereRaw("TIMESTAMP(availabilities.date, time_slots.start_time) < ?", [$request->start_datetime])
                            ->orWhereRaw("TIMESTAMP(availabilities.date, time_slots.end_time) > ?", [$request->end_datetime]);
                    });
                });
            })
            ->delete();

        // 4. Xử lý đồng bộ lại bài đăng (Post)
        // Sau khi xóa item lỗi, có thể có những Venue không còn item nào nữa -> Cần xóa bài Post của Venue đó

        // Lấy danh sách các Venue ID hiện còn item trong campaign này
        $remainingVenueIds = \App\Models\FlashSaleItem::where('campaign_id', $campaign->id)
            ->join('availabilities', 'flash_sale_items.availability_id', '=', 'availabilities.id')
            ->join('courts', 'availabilities.court_id', '=', 'courts.id')
            ->join('venues', 'courts.venue_id', '=', 'venues.id')
            ->distinct()
            ->pluck('venues.id')
            ->toArray();

        // Xóa các bài Post của Campaign này nếu Venue đó không còn trong danh sách còn lại
        \App\Models\Post::where('type', 'sale')
            ->where('reference_id', $campaign->id)
            ->whereNotIn('venue_id', $remainingVenueIds)
            ->delete();
        \App\Models\Post::where('type', 'sale')
            ->where('reference_id', $campaign->id)
            ->update([
                'content' => "🔥 SIÊU GIẢM GIÁ: Chiến dịch " . $request->name . " đang diễn ra..."
            ]);
        CheckFlashSale::dispatch($campaign->id, 'active')->delay($request->start_datetime);
        CheckFlashSale::dispatch($campaign->id, 'inactive')->delay($request->end_datetime);

        return redirect()->route('owner.flash_sale_campaigns.index')
            ->with('success', 'Cập nhật chiến dịch thành công. Các slot không hợp lệ đã bị loại bỏ.');
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // 1. Tìm chiến dịch và đảm bảo thuộc về chủ sân này
        $campaign = FlashSaleCampaign::where('id', $id)
            ->where('owner_id', $user->id)
            ->firstOrFail();

        try {
            // 2. Thực hiện xóa các dữ liệu liên quan trước (nếu database chưa cài đặt cascade delete)

            // Xóa các mặt hàng trong flash sale (Items)
            \App\Models\FlashSaleItem::where('campaign_id', $campaign->id)->delete();

            // Xóa các bài Post (bài đăng khuyến mãi) liên quan đến campaign này
            \App\Models\Post::where('type', 'sale')
                ->where('reference_id', $campaign->id)
                ->delete();

            // 3. Cuối cùng xóa chiến dịch
            $campaign->delete();

            return redirect()->route('owner.flash_sale_campaigns.index')
                ->with('success', 'Đã xóa chiến dịch Flash Sale thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa chiến dịch: ' . $e->getMessage());
        }
    }
}
