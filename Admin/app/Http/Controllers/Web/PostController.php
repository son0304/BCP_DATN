<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Khởi tạo middleware để đảm bảo chỉ Admin mới vào được các hàm này.
     * (Hoặc bạn có thể cài đặt ở file web.php)
     */
    public function __construct()
    {
        // $this->middleware(['auth', 'admin']);
    }

    /**
     * Danh sách bài viết (Cả Sale và User Post)
     */
    public function index(Request $request)
    {
        $query = Post::with(['author', 'venue'])
            ->latest();

        // Lọc theo loại bài viết nếu cần (sale/user_post)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Lọc theo trạng thái (pending/active/rejected)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->paginate(15)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Xem chi tiết bài viết
     */
    public function show(Post $post)
    {
        // Load đầy đủ thông tin: Tác giả, Sân, và các hình ảnh liên quan
        $post->load(['author', 'venue', 'images']);

        return view('admin.posts.show', compact('post'));
    }

    /**
     * Duyệt bài viết (Chuyển trạng thái sang ACTIVE)
     */
    public function updateStatus(Request $request, Post $post)
    {
        // Validate input status để đảm bảo an toàn dữ liệu
        $request->validate([
            'status' => 'required|in:active,pending,rejected',
        ]);

        try {
            DB::beginTransaction();

            $post->update([
                'status' => $request->status,
                'note'   => null, // Reset ghi chú khi đã duyệt
            ]);

            DB::commit();

            return redirect()
                ->route('admin.posts.index')
                ->with('success', 'Bài viết đã được chuyển sang trạng thái: ' . strtoupper($request->status));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Từ chối bài viết hoặc Ẩn bài viết (Kèm lý do)
     */
    public function rejectOrHide(Request $request, Post $post)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        try {
            // Cập nhật trạng thái thành rejected và lưu lý do
            $post->update([
                'status' => 'rejected',
                'note'   => $request->note,
            ]);

            return redirect()
                ->route('admin.posts.show', $post)
                ->with('success', 'Đã từ chối bài viết và gửi phản hồi cho người dùng.');
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể thực hiện thao tác này.');
        }
    }

    /**
     * (Tùy chọn) Xóa bài viết nếu cần thiết
     */
    public function destroy(Post $post)
    {
        try {
            // Xóa ảnh liên quan trước khi xóa post (nếu dùng morphMany)
            $post->images()->delete();
            $post->delete();

            return redirect()
                ->route('admin.posts.index')
                ->with('success', 'Đã xóa bài viết vĩnh viễn.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi xóa bài viết.');
        }
    }
}