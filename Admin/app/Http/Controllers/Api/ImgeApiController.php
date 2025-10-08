<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ImgeApiController extends Controller
{
    public function index()
    {
        $images =   Image::with(['venue', 'court'])->get();
        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }
}