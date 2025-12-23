<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Venue;
use App\Models\VenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceApiController extends Controller
{
    public function getServiceByVenue(Request $request, $id)
    {
        // 1. Kiểm tra Venue có tồn tại không
        $venueExists = Venue::where('id', $id)->exists();
        if (!$venueExists) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy sân vận động (Venue ID: ' . $id . ')',
                'data' => []
            ], 404);
        }

        // 2. Lấy dữ liệu từ database (Eager Loading)
        $services = VenueService::where('venue_id', $id)
            ->with([
                'service.images',   // Load ảnh
                'service.category'  // Load danh mục
            ])
            ->get();

        // 3. Xử lý Sắp xếp (Sorting Logic)
        // Thứ tự: Miễn phí (0đ) -> Type (Tiện ích > Dịch vụ > Hàng hóa) -> Tên Danh mục
        $sortedServices = $services->sortBy(function ($item) {
            // --- Tiêu chí 1: Giá (Miễn phí lên đầu) ---
            // Giá trị: 0 (Ưu tiên) | 1 (Thường)
            $priorityFree = ($item->price == 0) ? 0 : 1;

            // --- Tiêu chí 2: Loại hình (Type) ---
            // Định nghĩa trọng số: amenities (1) < service (2) < consumable (3)
            $typeWeight = match ($item->service->type ?? '') {
                'amenities'  => 1, // Tiện ích (Wifi, WC...)
                'service'    => 2, // Dịch vụ (Trọng tài...)
                'consumable' => 3, // Hàng hóa (Nước...)
                default      => 4
            };

            // --- Tiêu chí 3: Tên danh mục (A-Z) ---
            $categoryName = $item->service->category->name ?? 'zzzz';

            // Kết hợp key để sort: "0-1-TenDanhMuc"
            return sprintf('%d-%d-%s', $priorityFree, $typeWeight, $categoryName);
        });

        // 4. Trả về kết quả
        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách dịch vụ thành công',
            // Quan trọng: dùng values() để reset index mảng về [0, 1, 2...] sau khi sort
            'data' => $sortedServices->values(),
        ], 200);
    }
}
