<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class PostApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Post::query()
                ->with([
                    'author:id,name',
                    'tags:id,name',
                    'images:id,imageable_id,url,is_primary'
                ]);

            $query->where('is_active', 1);

            if (!$request->has('sort')) {
                $query->latest();
            }

            $posts = $query->paginate($request->input('per_page', 10));

            return response()->json([
                'success' => true,
                'data'    => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn chưa đăng nhập'
            ], 401);
        }

        $request->validate([
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'image_ids'  => 'nullable|array',
            'image_ids.*' => 'integer|exists:images,id',
            'tag_id'  => 'required|exists:tags,id',
            'tags'       => 'nullable|array',
            'tags.*'     => 'integer',
        ]);

        try {
            return DB::transaction(function () use ($request) {

                // 1️⃣ Tạo bài viết
                $post = Post::create([
                    'title'     => $request->title,
                    'content'   => $request->content,
                    'author_id'   => auth()->id(),
                    'tag_id'    => $request->tag_id,
                    'is_active' => 0,
                    'note'      => $request->note ?? '',
                ]);

                // 2️⃣ Gắn tags (nếu có)
                if ($request->filled('tags')) {
                    $post->tags()->sync($request->tags);
                }

                // 3️⃣ Gắn ảnh đã upload trước đó vào post (MORPH)
                if ($request->filled('image_ids')) {
                    Image::whereIn('id', $request->image_ids)
                        ->update([
                            'imageable_type' => Post::class,
                            'imageable_id'   => $post->id,
                        ]);

                    // set ảnh đầu tiên làm primary
                    Image::whereIn('id', $request->image_ids)
                        ->orderBy('id')
                        ->limit(1)
                        ->update(['is_primary' => true]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Đã đăng bài viết mới!',
                    'data'    => $post->load([
                        'author',
                        'tags',
                        'images'
                    ]),
                ], 201);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo bài viết',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
