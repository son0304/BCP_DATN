<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\AdBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BannerApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Lấy tất cả, không lọc điều kiện để kiểm tra dữ liệu
            $adminBanners = Banner::with('images')->where('is_active', 1)->get();
            $adBanners = AdBanner::with(['images', 'venue'])->get();

            Log::info('Ad Banners:', $adBanners->toArray());


            $merged = collect();

            // 1. Xử lý dữ liệu bảng Banner (Admin)
            foreach ($adminBanners as $b) {
                $merged->push([
                    'id' => $b->id,
                    'type' => 'system',
                    'title' => $b->title,
                    'image' => $this->getFirstImageUrl($b),
                    'target_url' => $b->target_url,
                    'priority' => $b->priority,
                    'position' => $b->position // Thêm để bạn check
                ]);
            }

            // 2. Xử lý dữ liệu bảng AdBanner (Venue)
            foreach ($adBanners as $ad) {
                $merged->push([
                    'id' => $ad->id,
                    'type' => 'sponsored',
                    'title' => $ad->venue->name ?? $ad->title ?? 'Quảng cáo',
                    'image' => $ad->venue->images->where('is_primary', 1)->value('url'),
                    'target_url' => $ad->target_url,
                    'priority' => $ad->priority ?? 999,
                    'position' => $ad->position // Thêm để bạn check
                ]);
            }

            // Trả về toàn bộ (5 bản ghi)
            return response()->json([
                'success' => true,
                'total_count' => $merged->count(), // Sẽ ra 5 nếu DB đủ
                'data' => $merged->values()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getFirstImageUrl($model)
    {
        $firstImage = $model->images->first();

        // Nếu không có bản ghi trong bảng images
        if (!$firstImage) {
            return asset('images/default-banner.jpg');
        }

        // LẤY TỪ CỘT 'url' CỦA BẢNG ẢNH (Theo ý bạn)
        $path = $firstImage->url;

        if (empty($path)) {
            return asset('images/default-banner.jpg');
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Nối URL: http://domain.com/storage/ + tên_file.jpg
        return asset(Storage::url(ltrim($path, '/')));
    }
}