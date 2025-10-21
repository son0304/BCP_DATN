<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;

class ProvinceApiController extends Controller
{
    public function index ()  {
       $provinces = Province::all();
       return response()->json([
        'succes' => true,
        'message'=> "Lấy danh sách tỉnh/thành thành công",
        'data'=> $provinces
       ]);
    }
}
