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
    public function store(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB
        ]);

        $saved = [];
        foreach ($request->file('files', []) as $file) {
            // Lưu vào disk 'public' -> storage/app/public/uploads
            $path = $file->store('uploads', 'public');
            $url = asset('storage/' . $path);

            // Lưu vào DB (Image model), liên kết với venue nếu cần
            $image = Image::create([
                'url' => $url,
                'venue_id' => $request->venue_id ?? null,
                'is_primary' => false
            ]);

            $saved[] = $image;
        }

        return response()->json(['success' => true, 'images' => $saved]);
    }
}