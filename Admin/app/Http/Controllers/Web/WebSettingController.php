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
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        // 1. Tạo Banner (không có image_url trong fillable)
        $banner = Banner::create($request->only([
            'title',
            'target_url',
            'position',
            'priority',
            'start_date',
            'end_date',
            'is_active'
        ]));

        // 2. Xử lý lưu ảnh vào bảng images thông qua relationship
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('uploads/banners', 'public');

            // Tạo bản ghi vào bảng images (morphMany)
            $banner->images()->create([
                'url' => 'storage/' . $path, // Format chuẩn: storage/uploads/reviews/ten_file.jpg
                'description' => null,
                'is_primary' => true,
            ]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Thêm banner thành công!');
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        // 1. Cập nhật thông tin cơ bản
        $banner->update($request->only([
            'title',
            'target_url',
            'position',
            'priority',
            'start_date',
            'end_date',
            'is_active'
        ]));

        // 2. Nếu có upload ảnh mới
        if ($request->hasFile('image')) {
            // Xóa các ảnh cũ trong storage và trong DB
            foreach ($banner->images as $oldImage) {
                Storage::disk('public')->delete($oldImage->url);
                $oldImage->delete();
            }

            // Lưu ảnh mới
            $path = $request->file('image')->store('banners', 'public');
            $banner->images()->create([
                'url' => $path,
            ]);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Cập nhật banner thành công!');
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