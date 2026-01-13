<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Post, Image};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Log, Storage};

class PostApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $posts = Post::query()
                ->where('status', 'active')
                ->where('type', 'sale') // <--- THÊM DÒNG NÀY: Chỉ lấy bài Sale
                ->with(['author:id,name,avt', 'images', 'venue:id,name'])
                ->latest()
                ->paginate($request->input('per_page', 10));

            return response()->json(['success' => true, 'data' => $posts]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Hàm store giữ nguyên để Admin/Chủ sân có thể gọi API này tạo bài Sale (từ trang quản trị)
    // Hoặc bạn có thể phân quyền middleware để chặn user thường gọi vào đây.
    public function store(Request $request)
    {
        // ... Code cũ giữ nguyên hoặc thêm logic check admin ...
        // Tạm thời giữ nguyên để tránh lỗi nếu bạn dùng nó cho admin panel
        $validated = $request->validate([
            'content'       => 'required|string|min:5',
            'type'          => 'required|in:sale,user_post',
            'venue_id'      => 'nullable|integer',
            'phone_contact' => 'nullable|string|max:20',
            'images'        => 'nullable|array',
            'images.*'      => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            return DB::transaction(function () use ($request, $validated) {
                $user = Auth::user();
                $post = Post::create([
                    'user_id'       => $user->id,
                    'type'          => $validated['type'],
                    'venue_id'      => $request->venue_id,
                    'content'       => $validated['content'],
                    'phone_contact' => $request->phone_contact ?? $user->phone,
                    'status'        => 'active',
                ]);

                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $index => $file) {
                        $path = $file->store('posts', 'public');
                        Image::create([
                            'imageable_type' => Post::class,
                            'imageable_id'   => $post->id,
                            'url'            => asset('storage/' . $path),
                            'is_primary'     => $index === 0,
                            'user_id'        => $user->id
                        ]);
                    }
                }
                return response()->json(['success' => true, 'data' => $post], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}