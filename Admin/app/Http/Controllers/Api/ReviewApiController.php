<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'venue_id' => 'required|exists:venues,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $review = Review::create($validator->validated());
        $review->load('user:id,name', 'venue:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Tạo đánh giá thành công',
            'data' => $review,
        ], 201);
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

    public function update(Request $request, Review $review)
    {
        if (! $request->hasAny(['user_id', 'venue_id', 'rating', 'comment'])) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp ít nhất một trường cần cập nhật',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|required|exists:users,id',
            'venue_id' => 'sometimes|required|exists:venues,id',
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ], [
            'user_id.exists' => 'Người dùng không tồn tại',
            'venue_id.exists' => 'Sân không tồn tại',
            'rating.integer' => 'Điểm đánh giá phải là số nguyên',
            'rating.min' => 'Điểm đánh giá tối thiểu là 1',
            'rating.max' => 'Điểm đánh giá tối đa là 5',
            'comment.string' => 'Nội dung đánh giá phải là chuỗi ký tự',
            'comment.max' => 'Nội dung đánh giá tối đa 2000 ký tự',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors(),
            ], 422);
        }

        $review->update($validator->validated());
        $review->load('user:id,name', 'venue:id,name');

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đánh giá thành công',
            'data' => $review,
        ]);
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa đánh giá thành công',
        ]);
    }
}
