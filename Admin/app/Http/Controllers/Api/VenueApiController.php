<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Availability;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VenueApiController extends Controller
{
    /**
     * Lấy danh sách venue với filter và sort.
     * Đã tối ưu hóa bằng cách bỏ eager loading không cần thiết.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'type_id' => 'nullable|integer|exists:venue_types,id',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'sort' => 'nullable|in:rating_desc,rating_asc',
        ]);

        // --- ĐÃ SỬA: Bỏ các quan hệ không cần thiết cho list view ---
        $query = Venue::with([
            'images:id,venue_id,url,is_primary',
            'courts:id,venue_id',
            'reviews:id,venue_id,rating',
            'venueTypes:id,name',
        ])->withAvg('reviews', 'rating');

        // Filter logic
        if ($request->filled('type_id')) {
            // --- ĐÃ SỬA: Sửa lại tên quan hệ cho đúng ---
            $query->whereHas('venueTypes', fn($q) => $q->where('venue_types.id', $request->type_id));
        }
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        // Sort logic
        if ($request->sort === 'rating_desc') {
            $query->orderByDesc('reviews_avg_rating');
        } elseif ($request->sort === 'rating_asc') {
            $query->orderBy('reviews_avg_rating');
        }

        $venues = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách địa điểm thành công',
            'data' => $venues,
        ]);
    }


    public function show(Request $request, $id)
    {
        $validated = $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $date = $validated['date'] ?? now()->toDateString();

        $venue = Venue::with([
            'images:id,venue_id,url,is_primary',
            'venueTypes:id,name',
            'courts:id,venue_id,name,surface,is_indoor',
            'courts.timeSlots:id,court_id,label,start_time,end_time',
            'owner:id,name,email',
            'province:id,name',
        ])->withAvg('reviews', 'rating')->find($id);

        if (!$venue) {
            return response()->json(['success' => false, 'message' => "Không tìm thấy địa điểm với ID = {$id}"], 404);
        }

        $courtIds = $venue->courts->pluck('id');

        $availabilities = Availability::whereIn('court_id', $courtIds)
            ->where('date', $date)
            ->get()
            ->groupBy('court_id')
            ->map(fn($items) => $items->keyBy('slot_id'));

        foreach ($venue->courts as $court) {
            $courtAvailabilities = $availabilities->get($court->id, collect());

            foreach ($court->timeSlots as $slot) {
                $availability = $courtAvailabilities->get($slot->id);

                if ($availability) {
                    $slot->status = $availability->status;
                    $slot->price = $availability->price;
                } else {
                    $slot->status = 'unavailable';
                    $slot->price = null;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết địa điểm thành công.',
            'data' => $venue,
        ]);
    }

}