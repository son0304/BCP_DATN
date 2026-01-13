<?php

namespace App\Http\Controllers\Api;

use App\Events\DataCreated;
use App\Http\Controllers\Controller;
use App\Models\Venue;
use App\Models\Availability;
use App\Models\MerchantProfile;
use App\Models\Promotion;
use App\Models\SponsoredVenue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VenueApiController extends Controller
{
    /**
     * Lấy danh sách venue với filter và sort.
     */
    public function index(Request $request)
    {
        $now = now();

        // 1. Khởi tạo Query với Select và xác định các Flag (Featured, Promoted, Sale)
        $query = Venue::query()
            ->select('venues.*')
            // Lấy dấu hiệu quảng cáo
            ->addSelect(DB::raw('MAX(ad_featured_venues.id) as featured_id'))
            ->addSelect(DB::raw('MAX(ad_top_searches.priority_point) as top_search_point'))

            // --- LOGIC CHECK SALE (THÊM MỚI) ---
            // Nếu tìm thấy ID của flash_sale_items thỏa mãn điều kiện, sân này sẽ có is_on_sale = true
            ->addSelect(DB::raw('MAX(flash_sale_items.id) as sale_item_id'))

            // --- JOIN QUẢNG CÁO (GIỮ NGUYÊN) ---
            ->leftJoin('ad_featured_venues', function ($join) use ($now) {
                $join->on('venues.id', '=', 'ad_featured_venues.venue_id')
                    ->where('ad_featured_venues.end_at', '>', $now);
            })
            ->leftJoin('ad_top_searches', function ($join) use ($now) {
                $join->on('venues.id', '=', 'ad_top_searches.venue_id')
                    ->where('ad_top_searches.end_at', '>', $now);
            })

            // --- JOIN CHUỖI FLASH SALE (THÊM MỚI) ---
            // Venue -> Courts -> Availabilities -> FlashSaleItems -> FlashSaleCampaigns
            ->leftJoin('courts', 'venues.id', '=', 'courts.venue_id')
            ->leftJoin('availabilities', 'courts.id', '=', 'availabilities.court_id')
            ->leftJoin('flash_sale_items', function ($join) {
                $join->on('availabilities.id', '=', 'flash_sale_items.availability_id')
                    ->where('flash_sale_items.status', '=', 'active') // Item đang mở bán
                    ->whereColumn('flash_sale_items.quantity', '>', 'flash_sale_items.sold_count'); // Còn hàng
            })
            ->leftJoin('flash_sale_campaigns', function ($join) use ($now) {
                $join->on('flash_sale_items.campaign_id', '=', 'flash_sale_campaigns.id')
                    ->where('flash_sale_campaigns.status', '=', 'active') // Chiến dịch đang chạy
                    ->where('flash_sale_campaigns.start_datetime', '<=', $now) // Đã bắt đầu
                    ->where('flash_sale_campaigns.end_datetime', '>=', $now);  // Chưa kết thúc
            })

            ->where('venues.is_active', 1)
            ->groupBy('venues.id'); // Đảm bảo không bị lặp sân khi join nhiều bảng con

        // 2. Các quan hệ bổ trợ
        $query->with([
            'images:id,imageable_id,imageable_type,url,is_primary',
            'venueTypes:id,name',
        ])->withAvg('reviews', 'rating');

        // 3. Bộ lọc tìm kiếm
        if ($request->filled('name')) {
            $query->where('venues.name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('type_id')) {
            $query->whereHas('venueTypes', fn($q) => $q->where('venue_types.id', $request->type_id));
        }
        if ($request->filled('province_id')) {
            $query->where('venues.province_id', $request->province_id);
        }
        if ($request->filled('district_id')) {
            $query->where('venues.district_id', $request->district_id);
        }

        $query->orderByRaw('CASE WHEN MAX(ad_featured_venues.id) IS NOT NULL THEN 1 ELSE 2 END ASC')
            ->orderByRaw('CASE WHEN MAX(flash_sale_items.id) IS NOT NULL THEN 1 ELSE 2 END ASC')
            ->orderByRaw('MAX(ad_top_searches.priority_point) DESC')
            ->orderByDesc('venues.created_at');

        $venues = $query->paginate(12);

        // 5. Transform dữ liệu trả về cho JSON
        $venues->getCollection()->transform(function ($venue) {
            $venue->is_featured = !is_null($venue->featured_id);
            $venue->is_promoted = !is_null($venue->top_search_point);

            // TẠO TRƯỜNG is_on_sale (Dùng cái này để hiện Badge SALE ở React)
            $venue->is_on_sale = !is_null($venue->sale_item_id);

            return $venue;
        });

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }


    public function show(Request $request, $id)
    {
        // 1. Validate ngày (mặc định là ngày hôm nay nếu không truyền)
        $validated = $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $date = $validated['date'] ?? now()->toDateString();

        // 2. Truy vấn Venue với đầy đủ các quan hệ để tối ưu hiệu năng (Eager Loading)
        $venue = Venue::with([
            'images:id,imageable_id,imageable_type,url,is_primary,type,description',
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
            ->find($id);

        if (!$venue) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy địa điểm với ID = {$id}"
            ], 404);
        }

        // 3. Xử lý trạng thái Sân (Availability) và Flash Sale
        $courtIds = $venue->courts->pluck('id');

        // Lấy danh sách khung giờ trống kèm Flash Sale đang hoạt động
        $availabilities = Availability::whereIn('court_id', $courtIds)
            ->where('date', $date)
            ->with(['flashSaleItem' => function ($query) {
                $query->whereHas('campaign', function ($q) {
                    $q->where('status', 'active');
                });
            }])
            ->get()
            ->groupBy('court_id')
            ->map(fn($items) => $items->keyBy('time_slot_id')); // Đảm bảo cột đúng là time_slot_id

        // Map dữ liệu Availability vào từng TimeSlot của mỗi Court
        foreach ($venue->courts as $court) {
            $courtAvailabilities = $availabilities->get($court->id, collect());

            foreach ($court->timeSlots as $slot) {
                $availability = $courtAvailabilities->get($slot->id);

                $slot->availability_id = $availability ? $availability->id : null;
                $slot->status          = $availability ? $availability->status : 'unavailable';
                $slot->price           = $availability ? $availability->price : null;

                if ($availability && $availability->flashSaleItem) {
                    $slot->sale_price    = $availability->flashSaleItem->sale_price;
                    $slot->is_flash_sale = true;
                } else {
                    $slot->sale_price    = null;
                    $slot->is_flash_sale = false;
                }
            }
        }

        // 4. Kiểm tra User hiện tại (Sử dụng guard API)
        $currentUser = Auth::guard('api')->user();

        // 5. Xử lý PROMOTION (VOUCHER)
        // CHỈ XỬ LÝ NẾU USER ĐÃ ĐĂNG NHẬP
        if ($currentUser) {
            $promotions = Promotion::query()
                ->with(['creator.role'])
                ->where('process_status', 'active')
                ->where('start_at', '<=', now())
                ->where('end_at', '>=', now())
                // Kiểm tra giới hạn lượt dùng: (Vô hạn -1) HOẶC (Còn lượt: used < limit)
                ->where(function ($query) {
                    $query->where('usage_limit', '<', 0)
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('usage_limit', '>', 0)
                                ->whereColumn('used_count', '<', 'usage_limit');
                        });
                })
                ->get()
                // Lọc logic nghiệp vụ thông qua hàm isEligible trong Model
                ->filter(function ($promotion) use ($venue, $currentUser) {
                    return $promotion->isEligible(
                        null,           // Chưa có tổng đơn nên truyền null
                        $venue,         // Đối tượng Venue hiện tại
                        $currentUser    // Đối tượng User đã đăng nhập
                    );
                })
                ->values(); // Reset key của mảng sau khi filter
        } else {
            // Nếu chưa đăng nhập, trả về danh sách rỗng
            $promotions = collect();
        }

        // Đính kèm voucher vào dữ liệu trả về
        $venue->promotions = $promotions;

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết địa điểm thành công.',
            'data' => $venue,
        ]);
    }


    public function store(Request $request)
    {
        // Log để kiểm tra dữ liệu từ frontend gửi lên
        Log::info('Dữ liệu đăng ký Venue:', $request->all());

        try {
            $validated = $request->validate([
                // --- 1. THÔNG TIN CHỦ SỞ HỮU ---
                'business_name'       => 'required|string|max:255',
                'business_address'    => 'required|string|max:255',
                'bank_name'           => 'required|string|max:100',
                'bank_account_number' => 'required|string|max:50',
                'bank_account_name'   => 'required|string|max:100',
                'user_profiles'       => 'required|array|min:1',
                'user_profiles.*'     => 'image|mimes:jpeg,png,jpg|max:5120',

                // --- 2. THÔNG TIN SÂN (VENUE) ---
                'venue_name'          => 'required|string|max:255',
                'venue_phone'         => 'required|string|max:20',
                'province_id'         => 'required|integer',
                'district_id'         => 'required|integer',
                'address_detail'      => 'required|string|max:255',
                'lat'                 => 'required|numeric',
                'lng'                 => 'required|numeric',
                'open_time'           => 'required',
                'close_time'          => 'required',

                // --- 3. HÌNH ẢNH SÂN & PHÁP LÝ ---
                'venue_profiles'      => 'required|array|min:1',
                'venue_profiles.*'    => 'image|mimes:jpeg,png,jpg|max:5120',
                'document_images'     => 'required|array|min:1',
                'document_images.*'   => 'image|mimes:jpeg,png,jpg|max:5120',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $e->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // 1. Cập nhật hoặc tạo thông tin Merchant
            $merchant = MerchantProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name'       => $validated['business_name'],
                    'business_address'    => $validated['business_address'],
                    'bank_name'           => $validated['bank_name'],
                    'bank_account_number' => $validated['bank_account_number'],
                    'bank_account_name'   => $validated['bank_account_name'],
                ]
            );

            // 2. Tạo Venue (Sân)
            $venue = Venue::create([
                'owner_id'       => $user->id,
                'name'           => $validated['venue_name'],
                'phone'          => $validated['venue_phone'],
                'province_id'    => $validated['province_id'],
                'district_id'    => $validated['district_id'],
                'address_detail' => $validated['address_detail'],
                'lat'            => $validated['lat'],
                'lng'            => $validated['lng'],
                'start_time'     => $validated['open_time'], // Map lại khớp DB
                'end_time'       => $validated['close_time'], // Map lại khớp DB
                'is_active'      => 0, // Chờ duyệt
            ]);

            // 3. Lưu ảnh CCCD/GPKD (Merchant Images)
            foreach ($request->file('user_profiles') as $file) {
                $path = $file->store('uploads/merchant_profiles', 'public');
                $merchant->images()->create([
                    'url'         => 'storage/' . $path,
                    'description' => 'Merchant Document',
                    'is_primary'  => false
                ]);
            }

            // 4. Lưu ảnh thực tế của Sân (Venue Profiles)
            foreach ($request->file('venue_profiles') as $file) {
                $path = $file->store('uploads/venues', 'public');
                $venue->images()->create([
                    'url'         => 'storage/' . $path,
                    'description' => 'Venue Profile Image',
                    'is_primary'  => true,
                    'type'        => 'venue',
                ]);
            }

            // 5. Lưu ảnh pháp lý của Sân (Document Images)
            foreach ($request->file('document_images') as $file) {
                $path = $file->store('uploads/documents', 'public');
                $venue->images()->create([
                    'url'         => 'storage/' . $path,
                    'description' => 'Venue Legal Document',
                    'is_primary'  => false,
                    'type'        => 'document',
                ]);
            }

            DB::commit();

            // Phát sự kiện (nếu có)
            broadcast(new DataCreated($venue, 'venues', 'venue.created'))->toOthers();

            return response()->json([
                'status'  => 'success',
                'message' => 'Hồ sơ đã được gửi thành công. Vui lòng chờ quản trị viên phê duyệt!',
                'data'    => $venue
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi Store Venue: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }
}