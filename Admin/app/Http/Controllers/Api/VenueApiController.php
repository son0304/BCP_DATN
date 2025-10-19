<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VenueApiController extends Controller
{
    /**
     * Lấy danh sách venue, có filter và sort
     */
    public function index(Request $request)
    {
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
            'venueTypes:id,name',
        ])->withAvg('reviews', 'rating');

        if ($request->filled('type_id')) {
            $query->whereHas('venueTypes', fn($q) => $q->where('venue_types.id', $request->type_id));
        }
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->sort === 'rating_desc') {
            $query->orderByDesc('reviews_avg_rating');
        } elseif ($request->sort === 'rating_asc') {
            $query->orderBy('reviews_avg_rating');
        }

        $venues = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sân thành công',
            'data' => $venues
        ]);
    }

    /**
     * Lấy chi tiết venue, cập nhật trạng thái slot
     */
    public function show($id, Request $request)
    {
        $validated = $request->validate(['date' => 'nullable|date']);
        $date = $validated['date'] ?? now()->toDateString();

        // 1️⃣ Lấy venue trước
        $venue = Venue::with([
            'images:id,venue_id,url,is_primary,description',
            'courts:id,venue_id,name,surface,price_per_hour,is_indoor',
            'courts.timeSlots',
            'reviews:id,venue_id,rating',
            'venueTypes:id,name',
            'province:id,name',
            'owner:id,name,email'
        ])->withAvg('reviews', 'rating')->find($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy sân với ID = {$id}",
                'data' => null
            ], 404);
        }

        // 2️⃣ Lấy tất cả booking cho venue trong ngày 1 lần
        $bookings = Booking::whereIn('court_id', $venue->courts->pluck('id'))
            ->where('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->groupBy('court_id');

        // 3️⃣ Cập nhật trạng thái slot dựa trên booking mới nhất
        foreach ($venue->courts as $court) {
            foreach ($court->timeSlots as $slot) {
                $latestBooking = $bookings[$court->id] ?? collect();
                $latestBooking = $latestBooking->where('time_slot_id', $slot->id)->sortByDesc('id')->first();
                $slot->is_booking = $latestBooking->status ?? 'canceled';
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết sân thành công',
            'data' => $venue
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
            'data' => $venue
        ]);
    }
}