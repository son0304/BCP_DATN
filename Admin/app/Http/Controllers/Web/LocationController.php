<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;

class LocationController extends Controller
{
    public function getDistrictsByProvince(Province $province)
    {
        $districts = $province->districts()->orderBy('name')->get();
        return response()->json($districts);
    }
}
