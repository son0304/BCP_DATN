<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Venue;
use Illuminate\Http\Request;

class VenueApiController extends Controller
{
    /**
     * Lấy danh sách venue với filter và sort
     */
    public function index(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'type_id' => 'nullable|integer|exists:venue_types,id',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'sort' => 'nullable|in:rating_desc,rating_asc',
        ]);

        $query = Venue::with([
            'images:id,venue_id,url,is_primary,description',
            'courts:id,venue_id,name,surface,price_per_hour,is_indoor',
            'courts.timeSlots:id,court_id,label,start_time,end_time',
            'reviews:id,venue_id,rating',
            'types:id,name',
        ])->withAvg('reviews', 'rating');

        if ($request->filled('type_id')) {
            $query->whereHas('types', fn($q) => $q->where('venue_types.id', $request->type_id));
        }

        // Filter theo province
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // Filter theo district
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

   

        $venues = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sân thành công',
            'data' => $venues,
        ]);
    }

    /**
     * Lấy chi tiết venue và cập nhật trạng thái booking cho từng time slot
     */
    public function show($id, Request $request)
    {
        $validated = $request->validate(['date' => 'nullable|date']);
        $date = $validated['date'] ?? now()->toDateString();

        // Lấy venue với các relation cần thiết
        $venue = Venue::with([
            'images:id,venue_id,url,is_primary,description',
            'courts:id,venue_id,name,surface,price_per_hour,is_indoor',
            'courts.timeSlots:id,court_id,label,start_time,end_time',
            'reviews:id,venue_id,rating',
            'types:id,name',
            'province:id,name',
            'owner:id,name,email',
        ])->withAvg('reviews', 'rating')->find($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy sân với ID = {$id}",
                'data' => null
            ], 404);
        }

        // Lấy tất cả booking cho venue trong ngày (1 lần)
        $bookings = Booking::whereIn('court_id', $venue->courts->pluck('id'))
            ->where('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->groupBy('court_id');

        // Gắn trạng thái booking vào từng time slot
        foreach ($venue->courts as $court) {
            foreach ($court->timeSlots as $slot) {
                $latestBooking = $bookings[$court->id] ?? collect();
                $latestBooking = $latestBooking->where('time_slot_id', $slot->id)
                                               ->sortByDesc('id')
                                               ->first();
                $slot->is_booking = $latestBooking->status ?? 'available';
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết sân thành công',
            'data' => $venue,
        ]);
    }

    /**
     * Tạo venue mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'address_detail' => 'required|string',
            'district_id' => 'required|integer',
            'province_id' => 'required|integer',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $venue = Venue::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tạo sân thành công',
            'data' => $venue,
        ]);
    }
}