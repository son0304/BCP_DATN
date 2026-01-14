<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\SponsoredVenue;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebSettingController extends Controller
{
    /**
     * TRANG CHỦ QUẢN LÝ SETTING (Hiển thị tất cả)
     */
    public function index()
    {
        // 1. Lấy danh sách Banner kèm theo các ảnh của chúng
        $banners = Banner::with('images')->orderBy('priority', 'asc')->get();


        // 3. Lấy danh sách tất cả sân (để hiện trong dropdown chọn sân tài trợ)
        $venues = Venue::where('is_active', true)->get();

        Log::info('Banners loaded', [
            'banners' => $banners->toArray()
        ]);
        // Trả về view
        return view('admin.setting.index', compact('banners',  'venues'));
    }

    // ==========================================
    // LOGIC BANNER
    // ==========================================

    public function storeBanner(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'image'      => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'position'   => 'required',
            'priority'   => 'required|integer|min:0',
            'start_date' => 'required|date|after_or_equal:now',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ], [
            'title.required' => 'Tiêu đề không được để trống.',
            'image.required' => 'Vui lòng chọn ảnh banner.',
            'priority.required' => 'Vui lòng nhập thứ tự ưu tiên.',
            'start_date.after_or_equal' => 'Thời gian bắt đầu không được ở trong quá khứ.',
            'end_date.after'    => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
        ]);

        $banner = Banner::create([
            'title'      => $request->title,
            'target_url' => $request->target_url,
            'position'   => $request->position,
            'priority'   => $request->priority,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->input('is_active', 1),
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('uploads/banners', 'public');

            $banner->images()->create([
                'url' => 'storage/' . $path, // Format chuẩn: storage/uploads/reviews/ten_file.jpg
                'description' => null,
                'is_primary' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Thêm banner thành công!');
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title'      => 'required|string|max:255',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'position'   => 'required',
            'priority'   => 'required|integer|min:0',
            'start_date' => 'required|date|after_or_equal:now',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ], [
            'title.required' => 'Tiêu đề không được để trống.',
            'position.required' => 'Vui lòng chọn vị trí hiển thị.',
            'priority.required' => 'Vui lòng nhập thứ tự ưu tiên.',
            'start_date.after_or_equal' => 'Thời gian bắt đầu không được ở trong quá khứ.',
            'end_date.after'    => 'Thời gian kết thúc phải sau thời gian bắt đầu.',

        ]);

        // 1. Cập nhật thông tin text
        $banner->update([
            'title'      => $request->title,
            'target_url' => $request->target_url,
            'position'   => $request->position,
            'priority'   => $request->priority,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->input('is_active', 0),
        ]);

        // 2. Xử lý ảnh nếu có upload mới
        if ($request->hasFile('image')) {
            // Xóa các ảnh cũ
            foreach ($banner->images as $oldImage) {
                // Vì DB lưu là 'storage/uploads/banners/abc.jpg'
                // Ta cần xóa chữ 'storage/' để Storage::disk('public') hiểu được đường dẫn vật lý
                $physicalPath = str_replace('storage/', '', $oldImage->url);

                if (Storage::disk('public')->exists($physicalPath)) {
                    Storage::disk('public')->delete($physicalPath);
                }
                $oldImage->delete();
            }

            // Lưu ảnh mới THEO FORMAT CỦA storeBanner
            $file = $request->file('image');
            $path = $file->store('uploads/banners', 'public');

            $banner->images()->create([
                'url'         => 'storage/' . $path, // Lưu giống hệt storeBanner
                'description' => null,
                'is_primary'  => true,
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật banner thành công!');
    }
    public function destroyBanner($id)
    {
        $banner = Banner::findOrFail($id);

        // Xóa tất cả ảnh liên quan trong storage và DB trước khi xóa Banner
        foreach ($banner->images as $image) {
            Storage::disk('public')->delete($image->url);
            $image->delete();
        }

        $banner->delete();
        return redirect()->route('admin.settings.index')->with('success', 'Xóa banner thành công!');
    }
}
