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

        // 1. Lấy Venue và các quan hệ cơ bản
        $venue = Venue::with([
            'images:id,imageable_id,imageable_type,url,is_primary,description',
            'venueTypes:id,name',
            'courts:id,venue_id,name,surface,is_indoor',
            'courts.timeSlots:id,court_id,label,start_time,end_time',
            'owner:id,name,email',
            'province:id,name',
            'reviews:id,user_id,venue_id,rating,comment,created_at,updated_at',
            'reviews.images:id,imageable_id,imageable_type,url',
            'reviews.user:id,name,avt',
            'reviews.user.images:id,imageable_id,imageable_type,url',
        ])
            ->withAvg('reviews', 'rating')
            ->where('id', $id)
            ->first();

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy địa điểm với ID = {$id}"
            ], 404);
        }

        $courtIds = $venue->courts->pluck('id');

        // 2. Lấy Availability và LỌC FLASH SALE THEO TRẠNG THÁI ACTIVE
        $availabilities = Availability::whereIn('court_id', $courtIds)
            ->where('date', $date)
            ->with(['flashSaleItem' => function ($query) {
                $query->whereHas('campaign', function ($q) {
                    $q->where('status', 'active');
                });
            }])
            ->get()
            ->groupBy('court_id')
            // Lưu ý: Kiểm tra kỹ tên cột trong DB là 'time_slot_id' hay 'slot_id'
            // Thường Laravel convention là 'time_slot_id'
            ->map(fn($items) => $items->keyBy('slot_id'));

        // 3. Map dữ liệu vào TimeSlots để trả về Frontend
        foreach ($venue->courts as $court) {
            $courtAvailabilities = $availabilities->get($court->id, collect());

            foreach ($court->timeSlots as $slot) {
                // Tìm availability tương ứng với slot này
                $availability = $courtAvailabilities->get($slot->id);

                // Gán dữ liệu cơ bản
                // Quan trọng: Phải trả về availability_id để frontend biết đường book
                $slot->availability_id = $availability ? $availability->id : null;
                $slot->status          = $availability ? $availability->status : 'unavailable'; // Hoặc logic mặc định của bạn
                $slot->price           = $availability ? $availability->price : null;

                // Xử lý Flash Sale (Chỉ hiện nếu availability load được flashSaleItem active)
                if ($availability && $availability->flashSaleItem) {
                    $slot->sale_price   = $availability->flashSaleItem->sale_price;
                    $slot->is_flash_sale = true;
                } else {
                    $slot->sale_price    = null;
                    $slot->is_flash_sale = false;
                }

                if ($availability) unset($availability->flashSaleItem);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết địa điểm thành công.',
            'data' => $venue,
        ]);
    }



    public function store(Request $request)
    {
        Log::info('Request to create venue', $request->all());
        $userId = $request->input('owner_id'); // owner_id là INT

        // Kiểm tra user đã có venue chưa
        $existingVenue = Venue::where('owner_id', $userId)->first();
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