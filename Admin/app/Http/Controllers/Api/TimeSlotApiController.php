<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class TimeSlotApiController extends Controller
{
    public function index()
    {
        $time_slot = TimeSlot::all();
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thành công',
            'data' => $time_slot
        ]);
    }
}