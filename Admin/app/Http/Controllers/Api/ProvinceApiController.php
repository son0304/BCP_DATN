<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;

class ProvinceApiController extends Controller
{
    public function index()
    {
        $provinces = Province::with('districts:id,province_id,name,code')->get();
        return response()->json([
            'success' => true,
            'message' => "Lấy danh sách tỉnh/thành thành công",
            'data' => $provinces
        ]);
    }
    public function show($id)
    {
        $province = Province::with('districts:id,province_id,name,code')->find($id);
        return response()->json([
            'success' => true,
            'message' => "Lấy danh sách tỉnh/thành thành công",
            'data' => $province
        ]);
    }
}