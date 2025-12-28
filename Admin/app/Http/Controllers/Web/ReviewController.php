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
        $reviews = collect();

        if ($user->role->name === 'admin') {
            $reviews = Review::with(['user', 'venue'])
                ->latest()
                ->get();
            return view('admin.reviews.index', compact('reviews'));
        } elseif ($user->role->name === 'venue_owner') {
            $venueIds = $user->venues->pluck('id');
            $reviews = Review::with(['user', 'venue'])
                ->whereIn('venue_id', $venueIds)
                ->latest()
                ->get();
            return view('venue_owner.reviews.index', compact('reviews'));
        } else {
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập trang này.');
        }
    }
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Thực hiện xóa
        $review->delete();

        return redirect()->back()->with('success', 'Đã xóa đánh giá thành công.');
    }
}