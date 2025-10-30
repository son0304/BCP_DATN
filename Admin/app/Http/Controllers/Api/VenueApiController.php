<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class VenueApiController extends Controller
{
    /**
     * Lấy danh sách venue với filter và sort.
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
            // ✅ Sửa lại vì giờ dùng morphMany → không có venue_id
            'images:id,imageable_id,url,is_primary,description',
            'courts:id,venue_id',
            'reviews:id,venue_id,rating',
            'venueTypes:id,name',
        ])->withAvg('reviews', 'rating');

        $query->where('is_active', 1);

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
            'message' => 'Lấy danh sách địa điểm thành công',
            'data' => $venues,
        ]);
    }

    /**
     * Lấy chi tiết venue
     */
    public function show(Request $request, $id)
    {
        $validated = $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $date = $validated['date'] ?? now()->toDateString();

        $venue = Venue::with([
            // ✅ morphMany: không có venue_id
            'images:id,imageable_id,url,is_primary,description',
            'venueTypes:id,name',
            'courts:id,venue_id,name,surface,is_indoor',
            'courts.timeSlots:id,court_id,label,start_time,end_time',
            'owner:id,name,email',
            'province:id,name',
        ])->withAvg('reviews', 'rating')->find($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy địa điểm với ID = {$id}"
            ], 404);
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
                $slot->status = $availability->status ?? 'unavailable';
                $slot->price = $availability->price ?? null;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết địa điểm thành công.',
            'data' => $venue,
        ]);
    }

    /**
     * Tạo mới venue kèm ảnh upload
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'provinceId' => 'required|integer|exists:provinces,id',
            'districtId' => 'required|integer|exists:districts,id',
            'address' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'mainImageIndex' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $venue = Venue::create([
                'owner_id' => $validatedData['user_id'],
                'name' => $validatedData['name'],
                'phone' => $validatedData['phone'],
                'province_id' => $validatedData['provinceId'],
                'district_id' => $validatedData['districtId'],
                'address_detail' => $validatedData['address'],
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
                'is_active' => false,
            ]);

            $files = $request->file('images');

            foreach ($files as $index => $file) {
                $path = $file->store('uploads/venues', 'public');
                $url = asset('storage/' . $path);

                // ✅ Dùng quan hệ morphMany
                $venue->images()->create([
                    'url' => $url,
                    'is_primary' => $index == $validatedData['mainImageIndex'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $venue->load('images')
            ], 201);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi tạo venue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}