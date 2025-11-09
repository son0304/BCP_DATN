<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictApiController extends Controller
{
    public function index()
    {
        $district = District::all();
        return response()->json([
            'succes' => true,
            'message' => "Lấy danh sách tỉnh/thành thành công",
            'data' => $district
        ]);
    }

    public function show($id)
    {
        $district = District::all()->find($id);
        return response()->json([
            'success' => true,
            'message' => "Lấy danh sách quận/huyện thành công",
            'data' => $district
        ]);
    }
}