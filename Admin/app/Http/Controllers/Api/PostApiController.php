<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Post;
use Auth;
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

            $query->where(function ($q) {
                $q->where('is_active', 1);

                if (Auth::check()) {
                    $q->orWhere('author_id', Auth::id());
                }
            });


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
        if (!Auth::check()) {
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
                    'author_id'   => Auth::id(),
                    'tag_id'    => $request->tag_id,
                    'is_active' => 0,

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

                $post->load([
                    'author',
                    'tags',
                    'images'
                ]);

                broadcast(new \App\Events\DataCreated($post, 'post', 'post.created'))->toOthers();

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

    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Bạn chưa đăng nhập'], 401);
        }

        $request->validate([
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'image_ids'  => 'nullable|array',
            'image_ids.*' => 'integer|exists:images,id',
            'tag_id'     => 'required|exists:tags,id',
            'tags'       => 'nullable|array',
            'tags.*'     => 'integer',
        ]);

        try {
            $post = Post::findOrFail($id);
            
            if ($post->author_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền sửa bài này'], 403);
            }

            return DB::transaction(function () use ($request, $post) {
                // 1. Cập nhật thông tin cơ bản
                $post->update([
                    'title'   => $request->title,
                    'content' => $request->content,
                    'tag_id'  => $request->tag_id,
                ]);

                // 2. Cập nhật tags
                if ($request->has('tags')) {
                    $post->tags()->sync($request->tags);
                }

                // 3. Cập nhật hình ảnh (Morph) - FIX: Xóa ảnh cũ thay vì set null
                if ($request->has('image_ids')) {
                    // Lấy danh sách ảnh cũ không còn trong danh sách mới
                    $oldImageIds = Image::where('imageable_type', Post::class)
                        ->where('imageable_id', $post->id)
                        ->whereNotIn('id', $request->image_ids)
                        ->pluck('id');

                    // XÓA các ảnh cũ không còn dùng (hoặc có thể giữ lại tùy yêu cầu)
                    if ($oldImageIds->isNotEmpty()) {
                        Image::whereIn('id', $oldImageIds)->delete();
                        // Hoặc nếu muốn giữ ảnh, chỉ gỡ liên kết:
                        // Image::whereIn('id', $oldImageIds)->update([
                        //     'imageable_type' => 'temp',
                        //     'imageable_id' => null,
                        //     'is_primary' => false
                        // ]);
                    }

                    // Gắn các ảnh mới/giữ lại
                    Image::whereIn('id', $request->image_ids)
                        ->update([
                            'imageable_type' => Post::class,
                            'imageable_id'   => $post->id,
                        ]);

                    // Reset tất cả primary của post này
                    Image::where('imageable_type', Post::class)
                        ->where('imageable_id', $post->id)
                        ->update(['is_primary' => false]);

                    // Set ảnh đầu tiên trong list làm primary
                    if (!empty($request->image_ids)) {
                        Image::where('id', $request->image_ids[0])
                            ->update(['is_primary' => true]);
                    }
                }

                $post->load(['author', 'tags', 'images']);

                // Broadcast sự kiện cập nhật
                broadcast(new \App\Events\DataUpdated($post, 'post', 'post.updated'))->toOthers();

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật bài viết thành công!',
                    'data'    => $post
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật bài viết',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Bạn chưa đăng nhập'], 401);
        }

        try {
            $post = Post::findOrFail($id);
            
            if ($post->author_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa'], 403);
            }

            DB::transaction(function () use ($post) {
                // 1. Xóa hẳn ảnh (hoặc gỡ liên kết nếu muốn giữ)
                Image::where('imageable_type', Post::class)
                    ->where('imageable_id', $post->id)
                    ->delete();
                
                // Nếu muốn giữ ảnh trong hệ thống:
                // Image::where('imageable_type', Post::class)
                //     ->where('imageable_id', $post->id)
                //     ->update([
                //         'imageable_type' => 'temp',
                //         'imageable_id'   => null,
                //         'is_primary'     => false
                //     ]);

                // 2. Gỡ liên kết tags (Pivot table)
                $post->tags()->detach();

                // 3. Xóa bài viết
                $post->delete();
            });

            broadcast(new \App\Events\DataDeleted($id, 'post', 'post.deleted'))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa bài viết thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa bài viết',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}