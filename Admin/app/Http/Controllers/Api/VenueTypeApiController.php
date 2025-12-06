<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VenueType;

class VenueTypeApiController extends Controller
{
    /**
     * Lấy danh sách tất cả venue types kèm số sân hiện có
     */
    public function index()
    {
        try {
            // Lấy tất cả loại sân kèm số lượng sân
            $types = VenueType::withCount('courts')->get();

            return response()->json([
                'success' => true,
                'message' => "Lấy danh sách loại hình sân thành công",
                'data' => $types
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải danh sách loại sân'
            ], 500);
        }
    }
}
