<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SponsorshipPackage; // Model menu gói
use App\Models\AdTopSearch;        // Model thực thi 1
use App\Models\AdFeaturedVenue;    // Model thực thi 2
use App\Models\AdBanner;           // Model thực thi 3
use App\Models\MoneyFlow;
use App\Models\SponsorshipPackageItem;
use App\Models\Transaction;
use App\Models\Venue;
use App\Models\WalletLog;
use Auth;
use Cache;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SponsorshipController extends Controller
{
    // =========================================================
    // PHẦN 1: QUẢN TRỊ (ADMIN) & MUA GÓI
    // =========================================================

    /**
     * Trang Admin: Hiển thị danh sách Gói + Danh sách Sân đang chạy quảng cáo
     */
    public function listAdmin()
    {
        // Eager load 'items' để hiển thị chi tiết quyền lợi trong view
        $packages = SponsorshipPackage::with('items')->orderBy('created_at', 'desc')->get();

        // Lấy danh sách quảng cáo đang chạy để Admin theo dõi (nếu cần)
        $activeAds = [
            'top' => AdTopSearch::with('venue')->where('end_at', '>', now())->get(),
            'featured' => AdFeaturedVenue::with('venue')->where('end_at', '>', now())->get(),
            'banner' => AdBanner::with('venue')->where('end_at', '>', now())->get(),
        ];

        return view('admin.packages.index', compact('packages', 'activeAds'));
    }

    public function storeAd(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'description'   => 'nullable|string',
            'types'         => 'required|array|min:1', // Bắt buộc chọn ít nhất 1 loại quyền lợi
            'types.*'       => 'in:top_search,featured,banner', // Chỉ chấp nhận các giá trị hợp lệ
        ], [
            'name.required'     => 'Vui lòng nhập tên gói.',
            'price.required'    => 'Vui lòng nhập giá.',
            'types.required'    => 'Vui lòng chọn ít nhất một quyền lợi (Combo).',
        ]);

        // Sử dụng Transaction để đảm bảo: Có Package thì phải có Items, lỗi 1 cái là rollback hết
        DB::beginTransaction();

        try {
            // 2. Tạo Gói cha (Package)
            $package = SponsorshipPackage::create([
                'name'          => $request->name,
                'price'         => $request->price,
                'duration_days' => $request->duration_days,
                'description'   => $request->description,
                // Checkbox trong HTML: nếu check thì gửi 'on', không check thì không gửi gì.
                // Dùng $request->has() hoặc logic dưới để set true/false
                'is_active'     => $request->has('is_active') ? true : false,
            ]);

            // 3. Tạo các quyền lợi con (Items) dựa trên mảng types[] gửi lên
            if ($request->has('types')) {
                foreach ($request->types as $type) {
                    $settings = [];

                    // Tùy theo loại type mà lấy dữ liệu setting tương ứng
                    if ($type === 'top_search') {
                        $settings['point'] = $request->top_search_point ?? 0;
                    } elseif ($type === 'featured') {
                        $settings['section'] = $request->featured_section ?? 'home_featured';
                    } elseif ($type === 'banner') {
                        $settings['position'] = $request->banner_position ?? 'home_slider';
                    }

                    // Lưu vào bảng items
                    // Cách 1: Dùng quan hệ create (nếu đã khai báo hasMany items trong Package Model)
                    $package->items()->create([
                        'type'     => $type,
                        'settings' => $settings, // Model sẽ tự cast sang JSON nhờ $casts = ['settings' => 'array']
                    ]);
                }
            }

            DB::commit(); // Lưu tất cả vào DB

            return redirect()->route('admin.packages.index')
                ->with('success', 'Đã tạo gói quảng cáo combo thành công!');
        } catch (\Exception $e) {
            DB::rollBack(); // Có lỗi thì hủy toàn bộ thao tác
            Log::error("Lỗi tạo gói quảng cáo: " . $e->getMessage());

            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
    public function listOwner()
    {
        $user = Auth::user();
        $myVenueIds = $user->venues->pluck('id')->toArray();

        // 1. Lấy các gói đang Active
        $packages = SponsorshipPackage::with('items')
            ->where('is_active', true)
            ->get();

        // 2. Kiểm tra nhanh xem User có đang sở hữu gói này không (cho bất kỳ sân nào)
        foreach ($packages as $package) {
            // Lấy giao dịch mới nhất (trong thời hạn cho phép)
            // Logic: Tìm xem có giao dịch nào mà (created_at + duration) > now() không
            $hasActive = MoneyFlow::where('money_flowable_type', SponsorshipPackage::class)
                ->where('money_flowable_id', $package->id)
                ->whereIn('venue_id', $myVenueIds)
                ->get()
                ->filter(function ($flow) use ($package) {
                    $expiry = $flow->created_at->copy()->addDays($package->duration_days);
                    return $expiry->isFuture(); // Chỉ lấy cái còn hạn
                })
                ->isNotEmpty();

            $package->is_purchased = $hasActive;
        }

        return view('venue_owner.packages.index', compact('packages'));
    }

    // TRANG CHI TIẾT GÓI (Hiển thị form mua & Danh sách sân đang dùng)
    public function showOwner($id)
    {
        $user = Auth::user();
        $package = SponsorshipPackage::with('items')->findOrFail($id);

        // 1. Xác định gói này bao gồm những quyền lợi gì
        $hasBanner = $package->items->contains('type', 'banner');
        $hasTop    = $package->items->contains('type', 'top_search');
        $hasFeatured = $package->items->contains('type', 'featured');

        // 2. Lấy danh sách sân của chủ sân
        // Eager load các bảng quảng cáo để tối ưu query
        $myVenues = $user->venues()
            ->with(['adBanner', 'adTopSearch', 'adFeatured'])
            ->get();

        $activeVenues = $myVenues->map(function ($venue) use ($hasBanner, $hasTop, $hasFeatured) {
            $expiryDates = [];

            // Kiểm tra xem sân có đang chạy dịch vụ tương ứng với gói này không
            // Và lấy ngày hết hạn xa nhất (nếu đang chạy)

            if ($hasBanner && $venue->adBanner && $venue->adBanner->end_at > now()) {
                $expiryDates[] = \Carbon\Carbon::parse($venue->adBanner->end_at);
            }

            if ($hasTop && $venue->adTopSearch && $venue->adTopSearch->end_at > now()) {
                $expiryDates[] = \Carbon\Carbon::parse($venue->adTopSearch->end_at);
            }

            if ($hasFeatured) {
                // Featured là quan hệ 1-N (có thể nhiều section), lấy cái xa nhất
                $featEnd = $venue->adFeatured->where('end_at', '>', now())->max('end_at');
                if ($featEnd) $expiryDates[] = \Carbon\Carbon::parse($featEnd);
            }

            // Nếu không tìm thấy ngày hết hạn nào -> Sân này không dùng gói này
            if (empty($expiryDates)) {
                return null;
            }

            // Lấy ngày hết hạn chung (thường các dịch vụ trong 1 gói sẽ hết hạn cùng lúc hoặc lấy cái xa nhất)
            $venue->real_expiry = max($expiryDates);

            // Tính số ngày còn lại (Làm tròn lên)
            $venue->days_remaining = ceil(now()->diffInDays($venue->real_expiry));

            return $venue;
        })->filter(); // Loại bỏ các giá trị null (các sân không dùng gói)

        return view('venue_owner.packages.viewPackage', compact('package', 'activeVenues'));
    }


    /**
     * Trang chi tiết gói & Form thanh toán
     */
    public function showPackage($id)
    {
        $package = SponsorshipPackage::with('items')->where('is_active', true)->findOrFail($id);

        // Lấy danh sách sân của chủ sân đang đăng nhập để họ chọn áp dụng cho sân nào
        // Giả sử quan hệ User -> Venues là hasMany
        $venues = Auth::user()->venues;

        // Kiểm tra xem gói này có cần upload Banner không
        $hasBanner = $package->items->contains('type', 'banner');

        return view('venue_owner.packages.show', compact('package', 'venues', 'hasBanner'));
    }

    public function update(Request $request, $id)
    {
        $package = SponsorshipPackage::findOrFail($id);

        DB::transaction(function () use ($request, $package) {
            // Update Gói Cha
            $package->update([
                'name'          => $request->name,
                'price'         => $request->price,
                'duration_days' => $request->duration_days,
                'description'   => $request->description,
                'is_active'     => $request->has('is_active'),
            ]);

            // Update Items: Xóa hết cũ -> Tạo lại mới (đơn giản nhất)
            $package->items()->delete();
            $this->createPackageItems($package, $request);
        });

        return redirect()->route('admin.packages.index')->with('success', 'Cập nhật gói thành công.');
    }


    public function destroy($id)
    {
        // Model setup onDelete cascade sẽ tự xóa items
        SponsorshipPackage::destroy($id);
        return redirect()->route('admin.packages.index')->with('success', 'Đã xóa gói.');
    }


    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validate dữ liệu
        $request->validate([
            'package_id'     => 'required|exists:sponsorship_packages,id',
            'venue_ids'      => 'required|array|min:1',
            // Kiểm tra venue tồn tại VÀ phải thuộc về chủ sân đang đăng nhập
            'venue_ids.*'    => [
                'required',
                'exists:venues,id',
                function ($attribute, $value, $fail) use ($user) {
                    $exists = \App\Models\Venue::where('id', $value)
                        ->where('owner_id', $user->id) // Quan trọng: Check chủ sở hữu
                        ->exists();
                    if (!$exists) {
                        $fail("Sân với ID $value không hợp lệ hoặc không thuộc quyền quản lý của bạn.");
                    }
                }
            ],
            'payment_method' => 'required|in:wallet,momo',
            'momo_trans_id'  => 'nullable|string|required_if:payment_method,momo',
        ]);

        try {
            DB::beginTransaction();

            $package = \App\Models\SponsorshipPackage::with('items')->findOrFail($request->package_id);

            // Tổng tiền
            $totalAmount = $package->price * count($request->venue_ids);

            // =========================================================================
            // BƯỚC 1: XỬ LÝ THANH TOÁN
            // =========================================================================

            if ($request->payment_method === 'wallet') {
                // Lấy ví (Lock for update để tránh race condition nếu cần thiết)
                $wallet = $user->wallet;

                // --- FIX LOGIC: Kiểm tra số dư ---
                if ($wallet->balance < $totalAmount) {
                    throw new \Exception("Số dư ví không đủ (Hiện tại: " . number_format($wallet->balance) . "đ).");
                }

                $beforeBalance = $wallet->balance; // Lưu số dư trước khi trừ
                $afterBalance  = $beforeBalance - $totalAmount;

                // Trừ tiền
                $wallet->decrement('balance', $totalAmount);

                // --- FIX TYPO: blace -> balance ---
                \App\Models\WalletLog::create([
                    'wallet_id'      => $wallet->id,
                    'before_balance' => $beforeBalance,
                    'after_balance'  => $afterBalance, // FIX: Logic tính đúng
                    'amount'         => -$totalAmount,
                    'type'           => 'payment', // Hoặc 'expense' tùy quy ước
                    'description'    => 'Thanh toán gói ' . $package->name
                ]);
            } elseif ($request->payment_method === 'momo') {
                $tempId = $request->momo_trans_id;
                $paymentData = \Illuminate\Support\Facades\Cache::get("momo_temp_paid_$tempId");

                if (!$paymentData || $paymentData['status'] !== 'paid') {
                    throw new \Exception("Giao dịch Momo không hợp lệ hoặc đã hết hạn.");
                }
                if ((int)$paymentData['amount'] < (int)$totalAmount) {
                    throw new \Exception("Số tiền thanh toán không khớp.");
                }

                // --- FIX: Xóa key trùng lặp ---
                \App\Models\Transaction::create([
                    'transactionable_type' => \App\Models\SponsorshipPackage::class,
                    'transactionable_id'   => $package->id,
                    'user_id'              => $user->id,
                    'payment_source'       => 'momo',
                    'amount'               => $totalAmount,
                    'note'                 => "Thanh toán Momo gói: " . $package->name,
                    'status'               => 'success',
                    'process_status'       => 'new'
                ]);

                \Illuminate\Support\Facades\Cache::forget("momo_temp_paid_$tempId");
            }

            // =========================================================================
            // BƯỚC 2: GHI NHẬN MONEY FLOW & KÍCH HOẠT
            // =========================================================================

            foreach ($request->venue_ids as $venueId) {
                $moneyFlow = \App\Models\MoneyFlow::create([
                    'money_flowable_type' => \App\Models\SponsorshipPackage::class,
                    'money_flowable_id'   => $package->id,
                    'venue_id'            => $venueId,
                    'total_amount'        => $package->price,
                    'admin_amount'        => $package->price,
                    'venue_owner_amount'  => 0,
                    'status'              => 'completed',
                    'process_status'      => 'new',
                    'note'                => "Mua gói '{$package->name}' qua {$request->payment_method}",
                ]);

                foreach ($package->items as $item) {
                    $settings = $item->settings ?? [];

                    // Giả định các hàm này đã được định nghĩa trong Controller
                    switch ($item->type) {
                        case 'top_search':
                            $this->handleTopSearch($venueId, $package, $settings, $moneyFlow->id);
                            break;
                        case 'featured':
                            $this->handleFeatured($venueId, $package, $settings, $moneyFlow->id);
                            break;
                        case 'banner':
                            $this->handleBanner($venueId, $package, $settings, $moneyFlow->id);
                            break;
                    }
                }
            }

            DB::commit();
            return redirect()->route('owner.packages.index')->with('success', 'Đăng ký gói quảng cáo thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Sponsorship Purchase Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Giao dịch thất bại: ' . $e->getMessage());
        }
    }

    /**
     * Logic Top Search: Cộng dồn điểm và Nối tiếp ngày
     */
    /**
     * Logic Top Search: Cộng dồn điểm & Gia hạn thời gian
     */
    protected function handleTopSearch($venueId, $package, $settings, $moneyFlowId)
    {
        $pointsToAdd = (int) ($settings['point'] ?? 0);
        $daysToAdd   = (int) $package->duration_days;

        // Tìm bản ghi Top Search hiện có của sân này
        $existingAd = \App\Models\AdTopSearch::where('venue_id', $venueId)->first();

        if ($existingAd) {
            // Logic gia hạn:
            // Nếu còn hạn: Cộng thêm ngày vào ngày hết hạn cũ.
            // Nếu đã hết hạn: Tính từ thời điểm hiện tại.
            $currentEndAt = Carbon::parse($existingAd->end_at);
            $baseDate     = $currentEndAt->isFuture() ? $currentEndAt : Carbon::now();

            $existingAd->update([
                // Cộng dồn điểm (Hoặc bạn có thể đổi logic thành lấy điểm cao nhất: max($existingAd->priority_point, $pointsToAdd))
                'priority_point' => $existingAd->priority_point + $pointsToAdd,
                'end_at'         => $baseDate->addDays($daysToAdd),
                'purchase_id'    => $moneyFlowId, // Cập nhật ID giao dịch mới nhất để tracking
                'is_active'      => true // Đảm bảo bật lại nếu nó đang tắt
            ]);
        } else {
            // Tạo mới hoàn toàn
            \App\Models\AdTopSearch::create([
                'venue_id'       => $venueId,
                'purchase_id'    => $moneyFlowId,
                'priority_point' => $pointsToAdd,
                'start_at'       => Carbon::now(), // Thêm start_at nếu có cột này
                'end_at'         => Carbon::now()->addDays($daysToAdd),
                'is_active'      => true
            ]);
        }
    }

    /**
     * Logic Featured: Tránh trùng lặp cùng Section
     */
    protected function handleFeatured($venueId, $package, $settings, $moneyFlowId)
    {
        $sectionName = $settings['section'] ?? 'home_featured';
        $daysToAdd   = (int) $package->duration_days;

        // Kiểm tra xem sân này ĐÃ CÓ ở section này chưa (kể cả hết hạn hay chưa)
        $existingAd = \App\Models\AdFeaturedVenue::where('venue_id', $venueId)
            ->where('section_name', $sectionName)
            ->first();

        if ($existingAd) {
            // Gia hạn
            $currentEndAt = Carbon::parse($existingAd->end_at);
            $baseDate     = $currentEndAt->isFuture() ? $currentEndAt : Carbon::now();

            $existingAd->update([
                'end_at'      => $baseDate->addDays($daysToAdd),
                'purchase_id' => $moneyFlowId,
                'is_active'   => true
            ]);
        } else {
            // Tạo mới
            \App\Models\AdFeaturedVenue::create([
                'venue_id'     => $venueId,
                'purchase_id'  => $moneyFlowId,
                'section_name' => $sectionName,
                'start_at'     => Carbon::now(),
                'end_at'       => Carbon::now()->addDays($daysToAdd),
                'is_active'    => true
            ]);
        }
    }

    /**
     * Logic Banner: Tránh trùng lặp cùng Position
     */
    protected function handleBanner($venueId, $package, $settings, $moneyFlowId)
    {
        $venue    = \App\Models\Venue::find($venueId);
        $position = $settings['position'] ?? 'home_hero';
        $daysToAdd = (int) ($package->duration_days ?? 30);

        // Kiểm tra Banner ở vị trí này của sân này đã tồn tại chưa
        $existingAd = \App\Models\AdBanner::where('venue_id', $venueId)
            ->where('position', $position)
            ->first();

        if ($existingAd) {
            // Gia hạn
            $currentEndAt = Carbon::parse($existingAd->end_at);
            $baseDate     = $currentEndAt->isFuture() ? $currentEndAt : Carbon::now();

            $existingAd->update([
                'end_at'      => $baseDate->addDays($daysToAdd),
                'purchase_id' => $moneyFlowId,
                'is_active'   => true,
                // Có thể cập nhật lại title/url nếu thông tin sân thay đổi
                'title'       => "Quảng cáo: " . ($venue->name ?? 'Sân cầu lông'),
                'target_url'  => "/venues/" . $venueId,
            ]);
        } else {
            // Tạo mới
            \App\Models\AdBanner::create([
                'venue_id'    => $venueId,
                'purchase_id' => $moneyFlowId,
                'title'       => "Quảng cáo: " . ($venue->name ?? 'Sân cầu lông'),
                'position'    => $position,
                'target_url'  => "/venues/" . $venueId,
                'image_url'   => $venue->image ?? null, // Lấy ảnh sân làm banner mặc định
                'priority'    => 10, // Priority mặc định
                'start_at'    => Carbon::now(),
                'end_at'      => Carbon::now()->addDays($daysToAdd),
                'is_active'   => true,
            ]);
        }
    }

    // Hàm phụ trợ để tạo Items (tránh lặp code)
    private function createPackageItems($package, $request)
    {
        if (in_array('top_search', $request->types)) {
            $package->items()->create([
                'type' => 'top_search',
                'settings' => ['point' => (int)$request->top_search_point]
            ]);
        }
        if (in_array('featured', $request->types)) {
            $package->items()->create([
                'type' => 'featured',
                'settings' => ['section' => $request->featured_section]
            ]);
        }
        if (in_array('banner', $request->types)) {
            $package->items()->create([
                'type' => 'banner',
                'settings' => ['position' => $request->banner_position]
            ]);
        }
    }


    // =========================================================
    // PHẦN 2: PUBLIC API (LOGIC HIỂN THỊ RA WEBSITE)
    // =========================================================

    /**
     * 1. Logic Hiển thị TOP TÌM KIẾM
     * Giải pháp: Điểm cao nhất lên đầu -> Rating cao -> Random
     */
    public function searchVenues(Request $request)
    {
        $query = Venue::query()->where('is_active', true);

        // JOIN bảng top_search lấy điểm
        $query->leftJoin('ad_top_searches', function ($join) {
            $join->on('venues.id', '=', 'ad_top_searches.venue_id')
                ->where('ad_top_searches.end_at', '>', now());
        });

        // 0 điểm nếu không mua gói
        $query->selectRaw('venues.*, COALESCE(ad_top_searches.priority_point, 0) as ranking_point');

        // Sắp xếp
        $query->orderByDesc('ranking_point') // Ai nhiều tiền lên trước
            ->orderByDesc('rating')        // Cùng tiền thì ai tốt lên trước
            ->inRandomOrder();             // Cùng tốt thì random

        return response()->json($query->paginate(20));
    }

    /**
     * 2. Logic Hiển thị SÂN NỔI BẬT (Featured)
     * Giải pháp: Random rotation để chia đều cơ hội hiển thị
     */
    public function getFeaturedVenues()
    {
        $limit = 4;
        $venues = AdFeaturedVenue::with('venue')
            ->where('section_name', 'home_featured')
            ->where('end_at', '>', now())
            ->inRandomOrder()
            ->take($limit)
            ->get()
            ->pluck('venue');

        return response()->json($venues);
    }

    /**
     * 3. Logic Hiển thị BANNER
     * Giải pháp: Random rotation
     */
    public function getBanners()
    {
        $banners = AdBanner::where('position', 'home_slider')
            ->where('end_at', '>', now())
            ->inRandomOrder()
            ->take(5)
            ->get();

        return response()->json($banners);
    }
}
