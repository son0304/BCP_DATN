<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagApiController extends Controller
{
    public function index()
    {
        // Lấy tất cả tag để đổ vào Select Box ở Frontend
        $tags = Tag::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'data' => $tags
        ]);
    }
}