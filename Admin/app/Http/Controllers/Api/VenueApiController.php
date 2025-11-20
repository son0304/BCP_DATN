<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Models\Availability;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
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
            'images:id,imageable_id,imageable_type,url,is_primary,description',
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
            'images:id,imageable_id,imageable_type,url,is_primary,description',
            'venueTypes:id,name',
            'courts:id,venue_id,name,surface,is_indoor',
            'courts.timeSlots:id,court_id,label,start_time,end_time',
            'owner:id,name,email',
            'province:id,name',
            'reviews:id,user_id,venue_id,rating,comment,created_at,updated_at',
            'reviews.user:id,name,avt'

        ])
            ->withAvg('reviews', 'rating')
            ->where('id', $id)
            ->first(); // thay vì find($id)

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

        $now = Carbon::now(); // Lấy thời gian thực tế hiện tại của server
        $isToday = $now->toDateString() === $date; // Kiểm tra ngày khách chọn có phải hôm nay không

        foreach ($venue->courts as $court) {
            $courtAvailabilities = $availabilities->get($court->id, collect());

            foreach ($court->timeSlots as $slot) {
                $availability = $courtAvailabilities->get($slot->id);

                // 1. Lấy trạng thái gốc từ DB
                $status = $availability->status ?? 'unavailable';
                $price = $availability->price ?? null;

                // 2. Logic kiểm tra giờ quá khứ
                if ($isToday) {
                    try {
                        // Parse giờ của slot
                        $slotTime = Carbon::createFromFormat('H:i:s', $slot->start_time);

                        // Gán ngày hiện tại vào để so sánh đầy đủ ngày + giờ
                        $slotDateTime = $slotTime->setDate($now->year, $now->month, $now->day);

                        // Nếu giờ slot nhỏ hơn giờ hiện tại -> Đánh dấu là đã qua
                        if ($slotDateTime->lt($now)) {
                            $status = 'passed'; // Trạng thái mới: Đã qua
                        }
                    } catch (\Exception $e) {
                    }
                }

                $slot->status = $status;
                $slot->price = $price;
            }
        }
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
    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'user_id' => 'required|integer|exists:users,id',
    //         'name' => 'required|string|max:255',
    //         'phone' => 'required|string|max:20',
    //         'provinceId' => 'required|integer|exists:provinces,id',
    //         'districtId' => 'required|integer|exists:districts,id',
    //         'address' => 'required|string',
    //         'start_time' => 'required|date_format:H:i',
    //         'end_time' => 'required|date_format:H:i|after:start_time',
    //         'description' => 'nullable|string',
    //         'images' => 'required|array|min:1',
    //         'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
    //         'mainImageIndex' => 'required|integer|min:0',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $venue = Venue::create([
    //             'owner_id' => $validatedData['user_id'],
    //             'name' => $validatedData['name'],
    //             'phone' => $validatedData['phone'],
    //             'province_id' => $validatedData['provinceId'],
    //             'district_id' => $validatedData['districtId'],
    //             'address_detail' => $validatedData['address'],
    //             'start_time' => $validatedData['start_time'],
    //             'end_time' => $validatedData['end_time'],
    //             'is_active' => false,
    //         ]);

    //         $files = $request->file('images');

    //         foreach ($files as $index => $file) {
    //             $path = $file->store('uploads/venues', 'public');
    //             $url = asset('storage/' . $path);

    //             // ✅ Dùng quan hệ morphMany
    //             $venue->images()->create([
    //                 'url' => $url,
    //                 'is_primary' => $index == $validatedData['mainImageIndex'],
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'data' => $venue->load('images')
    //         ], 201);
    //     } catch (Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Lỗi tạo venue: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function store(Request $request)
    {
        $user = $request->input('owner_id'); // Hoặc $request->input('owner_id') 

        // Kiểm tra user đã có venue chưa
        $existingVenue = Venue::where('owner_id', $user->id)->first();
        if ($existingVenue) {
            return response()->json([
                'success' => false,
                'alreadyRegistered' => true,
                'message' => 'Bạn đã đăng ký sân trước đó.'
            ], 409); // 409 Conflict
        }

        $rules = [
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'courts' => 'nullable|array',
            'courts.*.name' => 'required_with:courts|string|max:255',
            'courts.*.venue_type_id' => 'required_with:courts|exists:venue_types,id',
            'courts.*.surface' => 'nullable|string|max:255',
            'courts.*.is_indoor' => 'nullable|boolean',
            'courts.*.time_slots' => 'nullable|array',
            'courts.*.time_slots.*.start_time' => 'required_with:courts.*.time_slots|date_format:H:i',
            'courts.*.time_slots.*.end_time' => 'required_with:courts.*.time_slots|date_format:H:i|after:courts.*.time_slots.*.start_time',
            'courts.*.time_slots.*.price' => 'required_with:courts.*.time_slots|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $venueStart = Carbon::parse($request->start_time);
            $venueEnd = Carbon::parse($request->end_time);

            foreach ($request->input('courts', []) as $courtIndex => $court) {
                foreach ($court['time_slots'] ?? [] as $slotIndex => $slot) {
                    $slotStart = Carbon::parse($slot['start_time']);
                    $slotEnd = Carbon::parse($slot['end_time']);
                    if ($slotStart->lt($venueStart) || $slotEnd->gt($venueEnd)) {
                        $validator->errors()->add("courts.{$courtIndex}.time_slots.{$slotIndex}", "Khung giờ phải nằm trong giờ hoạt động của venue ({$venueStart->format('H:i')} - {$venueEnd->format('H:i')})");
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $venue = Venue::create($request->only([
                'name',
                'owner_id',
                'province_id',
                'district_id',
                'address_detail',
                'phone',
                'start_time',
                'end_time'
            ]) + ['is_active' => 0]);

            foreach ($request->input('courts', []) as $courtData) {
                $court = Court::create([
                    'venue_id' => $venue->id,
                    'name' => $courtData['name'],
                    'venue_type_id' => $courtData['venue_type_id'],
                    'surface' => $courtData['surface'] ?? null,
                    'is_indoor' => $courtData['is_indoor'] ?? false,
                ]);

                foreach ($courtData['time_slots'] ?? [] as $slotData) {
                    $timeSlot = TimeSlot::create([
                        'court_id' => $court->id,
                        'start_time' => $slotData['start_time'],
                        'end_time' => $slotData['end_time'],
                        'label' => $slotData['start_time'] . ' - ' . $slotData['end_time'],
                    ]);

                    // tạo availability 30 ngày
                    $now = Carbon::now();
                    $availabilities = [];
                    for ($i = 0; $i < 30; $i++) {
                        $date = Carbon::today()->addDays($i)->toDateString();
                        $availabilities[] = [
                            'court_id' => $court->id,
                            'slot_id' => $timeSlot->id,
                            'date' => $date,
                            'price' => $slotData['price'],
                            'status' => 'open',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    Availability::insert($availabilities);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo venue thành công',
                'data' => $venue->load('courts.timeSlots')
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
