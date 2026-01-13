<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Nạp sẵn user, venue và images để tránh lỗi N+1
        $query = Review::with(['user', 'venue', 'images'])->latest();

        if ($user->role->name === 'admin') {
            $reviews = $query->get();
            return view('admin.reviews.index', compact('reviews'));
        } elseif ($user->role->name === 'venue_owner') {
            $venueIds = $user->venues->pluck('id');
            $reviews = $query->whereIn('venue_id', $venueIds)->get();
            return view('venue_owner.reviews.index', compact('reviews'));
        }

        return redirect()->back()->with('error', 'Bạn không có quyền truy cập.');
    }
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Thực hiện xóa
        $review->delete();

        return redirect()->back()->with('success', 'Đã xóa đánh giá thành công.');
    }
}