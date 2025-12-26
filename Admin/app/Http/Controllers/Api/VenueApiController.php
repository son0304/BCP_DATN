<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Court;
use App\Models\TimeSlot;
use App\Models\Availability;
use App\Models\MerchantProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

        $validated = $request->validate([
            // --- 1. THÔNG TIN DOANH NGHIỆP ---
            'business_name' => 'required|string|max:255',
            'business_address' => 'required|string|max:255',
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_name' => 'required|string|max:100',

            // --- 2. THÔNG TIN ĐỊA ĐIỂM (VENUE) ---
            'venue_name' => 'required|string|max:255',
            'venue_phone' => 'required|string|max:20',
            'start_time' => 'required|date_format:H:i',
            // Lưu ý: Nếu sân hoạt động qua đêm (VD: 23h -> 1h sáng), rule 'after' sẽ gây lỗi.
            'end_time' => 'required|date_format:H:i|after:start_time',

            // Nên bỏ comment exists để đảm bảo tính toàn vẹn dữ liệu
            'province_id' => 'required|integer|exists:provinces,id',
            'district_id' => 'required|integer|exists:districts,id',
            'address_detail' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',

            // --- 3. VALIDATE MẢNG SÂN CON (COURTS) ---
            'courts' => 'required|array|min:1',
            'courts.*.name' => 'required|string|max:100',
            'courts.*.venue_type_id' => 'required|integer', // Nên thêm exists:venue_types,id
            'courts.*.surface' => 'required|string|max:50',
            'courts.*.price_per_hour' => 'required|numeric|min:0',

            // --- 4. VALIDATE FILE ẢNH ---
            'user_profiles' => 'required|array',
            'user_profiles.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'venue_profiles' => 'required|array',
            'venue_profiles.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // 1. Tạo hoặc Cập nhật Merchant Profile (FIX LỖI A)
            // Dùng updateOrCreate để tránh lỗi duplicate entry
            $merchant_profile = MerchantProfile::updateOrCreate(
                ['user_id' => $user->id], // Điều kiện tìm kiếm
                [
                    'business_name' => $validated['business_name'],
                    'business_address' => $validated['business_address'],
                    'bank_name' => $validated['bank_name'],
                    'bank_account_number' => $validated['bank_account_number'],
                    'bank_account_name' => $validated['bank_account_name'],
                ]
            );

            // 2. Tạo Venue
            $venue = Venue::create([
                'name' => $validated['venue_name'],
                'phone' => $validated['venue_phone'],
                'address_detail' => $validated['address_detail'],
                'province_id' => $validated['province_id'],
                'district_id' => $validated['district_id'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'owner_id' => $user->id,
                'is_active' => 0
            ]);

            // 3. Xử lý ảnh Venue
            if ($request->hasFile('venue_profiles')) {
                foreach ($request->file('venue_profiles') as $file) {
                    $path = $file->store('uploads/venues', 'public');
                    $venue->images()->create([
                        'url' => 'storage/' . $path, // Nên lưu path gốc, dùng Accessor để lấy full URL
                        // FIX LỖI B: Dùng $venue->id thay vì $request->venue_id
                        'description' => 'Review image for venue ' . $venue->id,
                        'is_primary' => true,
                    ]);
                }
            }


            if ($request->hasFile('user_profiles')) {
                foreach ($request->file('user_profiles') as $file) {
                    $path = $file->store('uploads/user_docs', 'public');
                    $merchant_profile->images()->create([
                        'url' => 'storage/' . $path,
                        'description' => 'Document image for user ' . $merchant_profile->id,
                        'is_primary' => false,
                    ]);
                }
            }

            // 5. Tạo Courts
            foreach ($validated['courts'] as $courtData) {
                $venue->courts()->create([
                    'name' => $courtData['name'],
                    'venue_type_id' => $courtData['venue_type_id'],
                    'surface' => $courtData['surface'],
                    'price_per_hour' => $courtData['price_per_hour'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Gửi đăng kí thành công', 'data' => $venue], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Venue Store Error: ' . $e->getMessage()); // Log lỗi để debug

            // Không nên trả về $e->getMessage() trực tiếp cho client ở môi trường Production (bảo mật)
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi tạo sân.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }
}
