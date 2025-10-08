<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueApiController extends Controller
{
    public function index(){
      $venues = Venue::with('images')->get(); 
      return response()->json([
        'message' => "Lấy dữ liệu thành công",
        'success' => true,
        'data' => $venues
    ]);  
    }
}