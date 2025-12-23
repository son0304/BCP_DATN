<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CommentApiController extends Controller
{
    /**
     * Lấy comment theo post
     * GET /api/posts/{postId}/comments
     */
    public function index($postId)
    {
        $comments = Comment::with([
                'author:id,name',
                'replies.author:id,name'
            ])
            ->where('post_id', $postId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $comments
        ]);
    }

    /**
     * Tạo comment / reply
     * POST /api/comments
     */
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $request->validate([
            'post_id'   => 'required|exists:posts,id',
            'content'   => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'post_id'   => $request->post_id,
            'user_id'   => $user->id,
            'content'   => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Comment created successfully',
            'data'    => $comment->load('author:id,name')
        ], 201);
    }

    /**
     * Xóa comment (soft delete)
     * DELETE /api/comments/{id}
     */
    public function destroy($id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $comment = Comment::findOrFail($id);

        // Chỉ chủ comment hoặc admin được xóa
        if ($comment->user_id !== $user->id && !$user->is_admin) {
            return response()->json([
                'status'  => false,
                'message' => 'Bạn không có quyền xóa comment này'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
}
