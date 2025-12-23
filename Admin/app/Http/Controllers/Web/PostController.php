<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Danh sách bài viết
     */
    public function index()
    {
        $user = Auth::user();

        // ADMIN: xem tất cả
        if ($user->role->name === 'admin') {
            $posts = Post::with(['author', 'tags'])
                ->latest()
                ->paginate(10);

            return view('admin.posts.index', compact('posts'));
        }

        abort(403, 'Bạn không có quyền truy cập trang này.');
    }

    /**
     * Chi tiết bài viết
     */
    public function show(Post $post)
    {
        $post->load(['author', 'tags', 'images']);

        $user = Auth::user();

        if (
            $user->role->name !== 'admin'
        ) {
            abort(403, 'Bạn không có quyền truy cập bài viết này.');
        }

        if ($user->role->name === 'admin') {
            return view('admin.posts.show', compact('post'));
        }
    }

    /**
     * Cập nhật trạng thái bài viết
     */
    public function updateStatus(Request $request, Post $post)
    {
        // Chỉ admin được đổi trạng thái
        if ($request->user()->role->name !== 'admin') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $validated = $request->validate([
            'is_active' => 'required|in:0,1',
        ]);

        $post->update([
            'is_active' => $validated['is_active'],
        ]);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Cập nhật trạng thái bài viết thành công!');
    }

    public function rejectOrHide(Request $request, Post $post)
    {
        if ($request->user()->role->name !== 'admin') {
            abort(403);
        }

        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        if ($post->is_active) {
            $post->update([
                'is_active' => 0,
                'note' => '[CANCELLED] ' . $request->note,
            ]);
        }
        else {
            $post->update([
                'is_active' => 0,
                'note' => $request->note,
            ]);
        }

        return redirect()
            ->route('admin.posts.show', $post)
            ->with('success', 'Cập nhật trạng thái thành công');
    }
}
