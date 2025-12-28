<?php

namespace App\Http\Controllers\Api;

use App\Events\DataCreated;
use App\Events\DataDeleted;
use App\Events\DataUpdated;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReviewApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min(100, $perPage));

        $reviewsQuery = Review::with(['user:id,name', 'venue:id,name']);

        if ($request->filled('venue_id')) {
            $reviewsQuery->where('venue_id', $request->input('venue_id'));
        }

        if ($request->filled('user_id')) {
            $reviewsQuery->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('rating')) {
            $reviewsQuery->where('rating', $request->input('rating'));
        }

        $reviews = $reviewsQuery->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách đánh giá thành công',
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
            ],
            'links' => [
                'next' => $reviews->nextPageUrl(),
                'prev' => $reviews->previousPageUrl(),
            ],
        ]);
    }

    public function store(Request $request)
    {

        Log::info($request->all());

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'venue_id' => 'required|exists:venues,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'user_id.required' => 'Người dùng là bắt buộc',
            'user_id.exists' => 'Người dùng không tồn tại',
            'venue_id.required' => 'Sân là bắt buộc',
            'venue_id.exists' => 'Sân không tồn tại',
            'rating.required' => 'Điểm đánh giá là bắt buộc',
            'rating.integer' => 'Điểm đánh giá phải là số nguyên',
            'rating.min' => 'Điểm đánh giá tối thiểu là 1',
            'rating.max' => 'Điểm đánh giá tối đa là 5',
            'comment.string' => 'Nội dung đánh giá phải là chuỗi ký tự',
            'comment.max' => 'Nội dung đánh giá tối đa 2000 ký tự',
            'image.image' => 'File tải lên phải là hình ảnh',
            'image.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, webp',
            'image.max' => 'Ảnh tối đa 5MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2. Sử dụng Transaction
        DB::beginTransaction();
        $path = null; // Khởi tạo biến path để dùng cho catch nếu lỗi

        try {
            // Tạo Review
            $review = Review::create([
                'user_id' => $request->user_id,
                'venue_id' => $request->venue_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // 3. Xử lý Upload ảnh (Giống ImageApiController)
            if ($request->hasFile('image')) {
                $file = $request->file('image');

                // Lưu vào thư mục: storage/app/public/uploads/reviews
                $path = $file->store('uploads/reviews', 'public');

                // Tạo bản ghi trong bảng images
                $review->images()->create([
                    'url' => 'storage/' . $path, // Format chuẩn: storage/uploads/reviews/ten_file.jpg
                    'description' => 'Review image for venue ' . $request->venue_id,
                    'is_primary' => true,
                ]);
            }

            // Commit transaction
            DB::commit();

            // Load lại quan hệ để trả về
            $review->load(['user:id,name,avt', 'images']);
            broadcast(new DataCreated($review, 'reviews', 'review.created'))->toOthers();
            return response()->json([
                'success' => true,
                'message' => 'Tạo đánh giá thành công',
                'data' => $review,
            ], 201);
        } catch (Exception $e) {
            // Rollback nếu có lỗi
            DB::rollBack();

            // Xóa file rác nếu đã lỡ upload nhưng DB lỗi
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Review $review)
    {
        $review->load('user:id,name', 'venue:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết đánh giá thành công',
            'data' => $review,
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Đánh giá không tồn tại'], 404);
        }

        // Validate (Bỏ user_id, venue_id vì không cho sửa chủ sở hữu/sân)
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'image.max' => 'Ảnh tối đa 5MB',
            'image.image' => 'File phải là hình ảnh',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        $newPath = null;

        try {
            // 1. Cập nhật thông tin text
            $review->update($request->only(['rating', 'comment']));

            // 2. Xử lý ảnh (Nếu có upload ảnh mới)
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $newPath = $file->store('uploads/reviews', 'public'); // Upload ảnh mới
                $fullNewUrl = 'storage/' . $newPath;

                // Lấy ảnh cũ (Giả sử 1 review có 1 ảnh chính)
                $existingImage = $review->images()->first();

                if ($existingImage) {
                    // Xóa file vật lý cũ
                    $oldPathRelative = str_replace('storage/', '', $existingImage->url);
                    if (Storage::disk('public')->exists($oldPathRelative)) {
                        Storage::disk('public')->delete($oldPathRelative);
                    }

                    // Cập nhật record DB
                    $existingImage->update(['url' => $fullNewUrl]);
                } else {
                    // Chưa có ảnh thì tạo mới
                    $review->images()->create([
                        'url' => $fullNewUrl,
                        'description' => 'Review image updated',
                        'is_primary' => true,
                    ]);
                }
            }

            DB::commit();
            $review->load(['user:id,name,avt', 'images']);
            broadcast(new DataUpdated($review, 'reviews', 'review.updated'))->toOthers();
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật đánh giá thành công',
                'data' => $review,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            // Xóa ảnh mới nếu lỗi DB
            if ($newPath && Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }
            return response()->json(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);


        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa đánh giá này',
            ], 403);
        }
        $reviewId = $review->id;

        $review->delete();
        broadcast(new DataDeleted($reviewId, 'reviews', 'review.deleted'))->toOthers();
        return response()->json([
            'success' => true,
            'message' => 'Xóa đánh giá thành công',
        ]);
    }
}