<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    /**
     * Get districts by province
     */
    public function index(Request $request)
    {
        $query = District::query();
        
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }
        
        $districts = $query->orderBy('name')->get();
        
        return response()->json($districts);
    }
}

