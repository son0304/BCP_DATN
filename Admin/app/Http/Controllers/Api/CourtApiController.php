<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;

class CourtApiController extends Controller
{
    /**
     * Lấy danh sách tất cả sân.
     */
    public function index()
    {
        $courts = Court::with('timeSlots:id,court_id,label,start_time,end_time')->get();

        return response()->json([
            'message' => 'Lấy danh sách sân thành công',
            'success' => true,
            'data' => $courts,
        ]);
    }

    /**
     * Lấy thông tin chi tiết 1 sân.
     */
    public function show($id)
    {
        $court = Court::with([
            // ✅ Chỉ giữ lại các cột thực tế có trong bảng images
            'images:id,imageable_id,url,is_primary,description',

            'venue:id,name,address_detail,phone,start_time,end_time,province_id,district_id,owner_id',
            'venue.province:id,name',
            'venue.district:id,name',
            'venue.owner:id,name,email,phone',
            'venue.types:id,name',
        ])->find($id);

        if (!$court) {
            return response()->json([
                'message' => 'Không tìm thấy sân',
                'success' => false,
            ], 404);
        }

        return response()->json([
            'message' => 'Lấy thông tin sân thành công',
            'success' => true,
            'data' => $court,
        ]);
    }
}