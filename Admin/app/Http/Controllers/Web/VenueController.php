<?php

namespace App\Http\Controllers\Web;

use App\Events\DataCreated;
use App\Events\DataDeleted;
use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use App\Models\VenueType;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Models\TimeSlot;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PermissionHelper;
use App\Mail\BookingConfirmationMail;
use App\Mail\Confirm_Merchant;
use App\Mail\Reject_Merchant;
use App\Mail\Reject_Venue;
use App\Mail\VenueApprovedMail;
use App\Models\MerchantProfile;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VenueController extends Controller
{

    protected $nameChannel = 'venues';
    //==============ADMIN=================//
    public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            $venues = Venue::with('owner', 'province')->latest()->paginate(10);
            return view('admin.venue.index', compact('venues'));
        }

        if ($user->role->name === 'venue_owner') {
            $venues = Venue::with('owner', 'province')
                ->where('owner_id', $user->id)
                ->latest()
                ->paginate(10);
            return view('venue_owner.venue.index', compact('venues'));
        }

        abort(403, 'Bạn không có quyền truy cập trang này.');
    }

    public function showVenueDetail(Venue $venue)
    {
        // 1. Load các quan hệ
        $venue->load([
            'owner.merchantProfile', // Đảm bảo đã load cái này
            'province',
            'district',
            'courts',
            'images',
            'services.images'
        ]);

        $user = Auth::user();

        // 2. Kiểm tra quyền
        if ($user->role->name !== 'admin' && $user->id !== $venue->owner_id) {
            abort(403);
        }

        // 3. LẤY HỒ SƠ MERCHANT RA BIẾN RIÊNG
        $merchant_profile = $venue->owner->merchantProfile ?? null;

        // 4. TRUYỀN THÊM BIẾN VÀO COMPACT
        if ($user->role->name === 'admin') {
            return view('admin.venue.show', compact('venue', 'merchant_profile'));
        } else {
            return view('venue_owner.venue.show', compact('venue', 'merchant_profile'));
        }
    }


    public function updateStatus(Request $request, Venue $venue)
    {
        // Kiểm tra quyền admin
        if (!PermissionHelper::isAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $validatedData = $request->validate([
            'is_active'  => 'required|in:0,1',
            'admin_note' => 'nullable|string',
        ]);

        // Cập nhật trạng thái
        $venue->update(['is_active' => $validatedData['is_active']]);
        $user = $venue->owner;

        // Lấy link Frontend để đối tác truy cập quản lý
        $urlWebAdmin = config('app.url_web_admin');

        if ($validatedData['is_active'] == 1) {
            // --- KÍCH HOẠT ---
            $venue->update([
                'is_active'  => 1,
                'admin_note' => null
            ]);

            if ($user) {
                // Gửi link Client để họ vào quản lý sân
                Mail::to($user->email)->send(new VenueApprovedMail($user, $urlWebAdmin));
            }
        } else {
            // --- TỪ CHỐI / TẠM DỪNG ---
            $venue->update([
                'is_active'  => 0,
                'admin_note' => $validatedData['admin_note'] ?? null
            ]);

            if ($user) {
                Mail::to($user->email)->send(new Reject_Venue($user, $venue, $urlWebAdmin));
            }
        }

        return redirect()->back()->with('success', $validatedData['is_active'] == 1 ? 'Đã kích hoạt!' : 'Đã cập nhật trạng thái.');
    }

    public function updateMerchant(Request $request, $id)
    {
        if (!PermissionHelper::isAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }
        $venue = Venue::findOrFail($id);
        $merchant = $venue->owner->merchantProfile;
        $user = $venue->owner;

        // Lấy link Frontend
        $urlWebClient = config('app.url_web_client');

        $validatedData = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string',
        ]);

        if ($validatedData['status'] == 'approved') {
            $merchant->update(['status' => 'approved', 'admin_note' => null]);

            if ($user && $user->role_id != 1 && $user->role_id != 2) {
                $user->role_id = 2;
                $user->save();
            }
            if ($user) {
                $user->update(['role_id' => 2]);

                Mail::to($user->email)->send(new Confirm_Merchant($user, $merchant, $urlWebClient));
            }
        } elseif ($validatedData['status'] == 'rejected') {
            $merchant->update([
                'status' => 'rejected',
                'admin_note' => $validatedData['admin_note'] ?? null,
            ]);

            if ($user) {
                // Gửi mail Reject
                Mail::to($user->email)->send(new Reject_Merchant($user, $merchant, $urlWebClient));
            }
        }

        return back()->with('success', 'Đã cập nhật trạng thái hồ sơ.');
    }


    //==============Venue_Owner=================//
    public function create()
    {
        $user = Auth::user();
        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::isVenueOwner($user)) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }
        $owners = [];
        if (PermissionHelper::isAdmin($user)) {
            $owners = User::orderBy('name')->get();
        }
        $venue_types = VenueType::orderBy('name')->get();
        $timeSlots = TimeSlot::orderBy('start_time')->get();

        return view('venue_owner.venue.create', compact('owners', 'venue_types', 'timeSlots'));
    }

    public function store(Request $request)
    {
        Log::info('Venue Store Request', [
            'request' => $request->all()
        ]);
        $user = Auth::user();

        // --- 1. Định nghĩa Rules và Messages ---
        $rules = [
            'name' => 'required|string|max:255',
            'province_id' => 'nullable|numeric',
            'district_id' => 'nullable|numeric',
            'address_detail' => 'required|string',
            'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'start_time' => 'required|date_format:H:i',
            'end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],

            // 📍 Lat / Lng
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',

            'venue_types' => 'required|array|min:1',
            'venue_types.*' => 'exists:venue_types,id',

            // Images
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_links' => 'nullable|array',
            'image_links.*' => 'nullable|url|max:500',
            'primary_image_index' => 'required|integer|min:0',

            // Courts & Time slots
            'courts' => 'nullable|array',
            'courts.*.name' => 'required|string|max:255',
            'courts.*.venue_type_id' => 'required|exists:venue_types,id',
            'courts.*.surface' => 'nullable|string|max:255',
            'courts.*.is_indoor' => 'nullable|in:0,1',
            'courts.*.time_slots' => 'nullable|array',
            'courts.*.time_slots.*.start_time' => 'required|date_format:H:i',
            'courts.*.time_slots.*.end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
            'courts.*.time_slots.*.price' => 'required|numeric|min:0',
        ];


        if (PermissionHelper::isAdmin($user)) {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        $messages = [
            'name.required' => 'Tên thương hiệu không được bỏ trống.',
            'name.max' => 'Tên thương hiệu không được vượt quá 255 ký tự.',
            'province_id.required' => 'Vui lòng chọn Tỉnh/Thành phố.',
            'province_id.exists' => 'Tỉnh/Thành phố không hợp lệ.',
            'district_id.required' => 'Vui lòng chọn Quận/Huyện.',
            'district_id.exists' => 'Quận/Huyện không hợp lệ.',
            'address_detail.required' => 'Địa chỉ chi tiết không được bỏ trống.',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
            'start_time.required' => 'Giờ mở cửa không được bỏ trống.',
            'start_time.date_format' => 'Giờ mở cửa sai định dạng (HH:mm).',
            'end_time.required' => 'Giờ đóng cửa không được bỏ trống.',
            'venue_types.required' => 'Vui lòng chọn ít nhất một loại hình sân.',
            'owner_id.required' => 'Vui lòng chọn chủ sân.',
            'owner_id.exists' => 'Chủ sân được chọn không tồn tại.',
            'courts.*.name.required' => 'Tên sân không được bỏ trống.',
            'courts.*.name.max' => 'Tên sân không được vượt quá 255 ký tự.',
            'courts.*.venue_type_id.required' => 'Vui lòng chọn loại sân.',
            'courts.*.venue_type_id.exists' => 'Loại sân không hợp lệ.',
            'courts.*.is_indoor.in' => 'Trạng thái sân (Trong nhà/Ngoài trời) không hợp lệ.',
            'courts.*.time_slots.*.start_time.required' => 'Giờ bắt đầu khung giờ không được bỏ trống.',
            'courts.*.time_slots.*.start_time.date_format' => 'Giờ bắt đầu khung giờ sai định dạng.',
            'courts.*.time_slots.*.end_time.required' => 'Giờ kết thúc khung giờ không được bỏ trống.',
            'courts.*.time_slots.*.price.required' => 'Giá tiền không được bỏ trống.',
            'courts.*.time_slots.*.price.numeric' => 'Giá tiền phải là một số.',
            'courts.*.time_slots.*.price.min' => 'Giá tiền không được nhỏ hơn 0.',

            'images.*.image' => 'File tải lên phải là hình ảnh.',
            'images.*.mimes' => 'Định dạng ảnh không hợp lệ (chỉ chấp nhận: jpeg, png, jpg, gif, webp).',
            'images.*.max' => 'Kích thước mỗi ảnh không được vượt quá 2MB.',
            'image_links.*.url' => 'Link ảnh phải là một URL hợp lệ.',
            'image_links.*.max' => 'Đường dẫn link ảnh không được quá 500 ký tự.',
            'primary_image_index.required' => 'Vui lòng chọn ảnh đại diện.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // --- 2. After Hooks (Bao gồm check count ảnh và kiểm tra Time Slot) ---
        $validator->after(function ($validator) use ($request) {
            $venueStartTimeStr = $request->input('start_time');
            $venueEndTimeStr = $request->input('end_time');
            $courts = $request->input('courts', []);

            // Kiểm tra số lượng ảnh (Files + Links)
            $uploadedFiles = array_filter((array) $request->file('images'));
            $imageLinks = array_filter((array) $request->input('image_links'));
            $totalCount = count($uploadedFiles) + count($imageLinks);

            if ($totalCount < 1) {
                $validator->errors()->add('images', 'Vui lòng tải lên hoặc chèn link ít nhất một ảnh.');
            }
            if ($totalCount > 5) {
                $validator->errors()->add('images', 'Chỉ được phép tải lên/chèn link tối đa 5 ảnh.');
            }

            $primaryIndex = (int) $request->input('primary_image_index', -1);
            if ($totalCount > 0 && ($primaryIndex < 0 || $primaryIndex >= $totalCount)) {
                $validator->errors()->add('primary_image_index', 'Index ảnh chính không hợp lệ (phải nằm trong khoảng 0 đến ' . ($totalCount - 1) . ').');
            }

            // Logic kiểm tra Time Slot
            if (!$venueStartTimeStr || !$venueEndTimeStr || empty($courts)) {
                return;
            }

            $venueStart = Carbon::createFromFormat('H:i', $venueStartTimeStr);
            $venueEnd = Carbon::createFromFormat('H:i', $venueEndTimeStr === '24:00' ? '00:00' : $venueEndTimeStr);

            if ($venueEndTimeStr === '24:00' || $venueEnd->lte($venueStart)) {
                $venueEnd->addDay();
            }

            foreach ($courts as $courtIndex => $court) {
                if (empty($court['time_slots'])) continue;

                foreach ($court['time_slots'] as $slotIndex => $slot) {
                    if (empty($slot['start_time']) || empty($slot['end_time'])) continue;

                    $slotStart = Carbon::createFromFormat('H:i', $slot['start_time']);
                    $slotEnd = Carbon::createFromFormat('H:i', $slot['end_time'] === '24:00' ? '00:00' : $slot['end_time']);

                    if ($slot['end_time'] === '24:00' || $slotEnd->lte($slotStart)) {
                        $slotEnd->addDay();
                    }

                    if ($slotStart->lt($venueStart)) {
                        $slotStart->addDay();
                        $slotEnd->addDay();
                    }

                    $errorMessage = 'Khung giờ phải nằm trong giờ hoạt động của thương hiệu (' . $venueStartTimeStr . ' - ' . $venueEndTimeStr . ').';

                    if ($slotStart->gte($slotEnd)) {
                        $validator->errors()->add("courts.{$courtIndex}.time_slots.{$slotIndex}.end_time", 'Giờ kết thúc phải sau giờ bắt đầu.');
                        continue;
                    }

                    if ($slotStart->lt($venueStart) || $slotEnd->gt($venueEnd)) {
                        $validator->errors()->add("courts.{$courtIndex}.time_slots.{$slotIndex}.start_time", $errorMessage);
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        if (PermissionHelper::isVenueOwner($user)) {
            $validatedData['owner_id'] = $user->id;
        }

        // Chuẩn hóa giờ giấc
        if (isset($validatedData['start_time']) && strlen($validatedData['start_time']) === 5) {
            $validatedData['start_time'] .= ':00';
        }

        if (isset($validatedData['end_time'])) {
            if ($validatedData['end_time'] === '24:00') {
                $validatedData['end_time'] = '23:59:59';
            } elseif (strlen($validatedData['end_time']) === 5) {
                $validatedData['end_time'] .= ':00';
            }
        }

        // --- 3. Transaction và Lưu trữ ---
        DB::beginTransaction();

        $venue = null;
        $imagesToRollback = collect();

        try {
            // A. Create Venue
            $venue = Venue::create([
                'name' => $validatedData['name'],
                'owner_id' => $validatedData['owner_id'],
                'province_id' => $validatedData['province_id'],
                'district_id' => $validatedData['district_id'],
                'address_detail' => $validatedData['address_detail'],
                'lat' => $validatedData['lat'],
                'lng' => $validatedData['lng'],
                'phone' => $validatedData['phone'] ?? null,
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
                'is_active' => 0,
            ]);

            // B. Handle Images (Files and Links combined)
            $uploadedFiles = array_filter((array) $request->file('images'));
            $imageLinks = array_filter((array) $request->input('image_links'));
            $allImagesData = array_merge($uploadedFiles, $imageLinks);
            $mainImageIndex = (int) $validatedData['primary_image_index'];

            if (!empty($allImagesData)) {
                foreach ($allImagesData as $index => $imageData) {
                    $url = null;
                    // Logic xác định ảnh đại diện chuẩn (chỉ 1 ảnh đúng)
                    $isPrimary = $index === (int)$mainImageIndex;

                    if ($imageData instanceof \Illuminate\Http\UploadedFile) {
                        // 1. Lưu file vật lý vào disk public (vẫn trả về 'uploads/venues/name.jpg')
                        $path = $imageData->store('uploads/venues', 'public');

                        // 2. TRUYỀN CỨNG 'storage/' VÀO URL THEO Ý BẠN
                        $url = 'storage/' . $path;
                    } elseif (is_string($imageData)) {
                        // Nếu là link ảnh online thì giữ nguyên
                        $url = $imageData;
                    }

                    if ($url) {
                        $image = $venue->images()->create([
                            'url' => $url, // Kết quả trong DB: storage/uploads/venues/filename.jpg
                            'is_primary' => $isPrimary,
                        ]);

                        // Lưu lại model để rollback nếu transaction lỗi
                        $imagesToRollback->push($image);
                    }
                }
            }
            // C. Attach Venue Types
            if (!empty($validatedData['venue_types'])) {
                $venue->venueTypes()->attach($validatedData['venue_types']);
            }

            // D. Create Courts, TimeSlots, and Availabilities
            if (!empty($validatedData['courts']) && is_array($validatedData['courts'])) {
                foreach ($validatedData['courts'] as $courtData) {
                    $court = Court::create([
                        'name' => $courtData['name'],
                        'venue_id' => $venue->id,
                        'venue_type_id' => $courtData['venue_type_id'],
                        'surface' => $courtData['surface'] ?? null,
                        'is_indoor' => isset($courtData['is_indoor']) ? (bool)$courtData['is_indoor'] : false,
                    ]);

                    $availabilitiesToInsert = [];
                    $now = Carbon::now();

                    if (!empty($courtData['time_slots']) && is_array($courtData['time_slots'])) {
                        foreach ($courtData['time_slots'] as $timeSlotData) {
                            if (empty($timeSlotData['start_time']) || empty($timeSlotData['end_time']) || !isset($timeSlotData['price'])) {
                                continue;
                            }

                            $startTime = strlen($timeSlotData['start_time']) === 5 ? $timeSlotData['start_time'] . ':00' : $timeSlotData['start_time'];
                            $endTime = $timeSlotData['end_time'] === '24:00'
                                ? '23:59:59'
                                : (strlen($timeSlotData['end_time']) === 5 ? $timeSlotData['end_time'] . ':00' : $timeSlotData['end_time']);
                            $price = (float)$timeSlotData['price'];

                            $timeSlot = TimeSlot::create([
                                'court_id' => $court->id,
                                'start_time' => $startTime,
                                'end_time' => $endTime,
                                'label' => $timeSlotData['start_time'] . ' - ' . $timeSlotData['end_time'],
                            ]);

                            for ($i = 0; $i < 30; $i++) {
                                $date = Carbon::today()->addDays($i)->toDateString();
                                $availabilitiesToInsert[] = [
                                    'court_id' => $court->id,
                                    'date' => $date,
                                    'slot_id' => $timeSlot->id,
                                    'price' => $price,
                                    'status' => 'open',
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                        }
                    }

                    if (!empty($availabilitiesToInsert)) {
                        Availability::insert($availabilitiesToInsert);
                    }
                }
            }

            DB::commit();
            $venue->load(['owner', 'province']);
            Log::info('Đang bắn event cho Venue ID: ' . $venue->id);

            broadcast(new DataCreated($venue, $this->nameChannel, 'venue.created'));
            $redirectRoute = PermissionHelper::isAdmin($user) ? 'admin.venues.index' : 'owner.venues.index';
            return redirect()->route($redirectRoute)->with('success', 'Đăng ký thương hiệu và sân thành công!');
        } catch (\Exception $e) {
            DB::rollBack();

            // Rollback Images: Chỉ xóa file vật lý nếu cần
            if ($imagesToRollback->isNotEmpty()) {
                $imagesToRollback->each(function ($image) {
                    // Nếu url không bắt đầu bằng http, đây là file vật lý, cần xóa
                    if (strpos($image->url, 'http') !== 0) {
                        Storage::disk('public')->delete($image->url);
                    }
                });
            }

            Log::error('Venue store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi lưu: ' . $e->getMessage());
        }
    }

    public function edit(Venue $venue)
    {
        if (!PermissionHelper::ownsVenue($venue->id, Auth::user()) && !PermissionHelper::isAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền sửa địa điểm này.');
        }

        $owners = User::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $districts = District::where('province_id', $venue->province_id)->orderBy('name')->get();
        $venue_types = VenueType::orderBy('name')->get();
        $venue->load('images');
        return view('venue_owner.venue.edit', compact('venue', 'owners', 'provinces', 'districts', 'venue_types'));
    }

    public function update(Request $request, $id)
    {
        $venue = Venue::findOrFail($id);
        $user = Auth::user();

        // 1. KIỂM TRA QUYỀN HẠN
        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::ownsVenue($venue->id, $user)) {
            abort(403, 'Bạn không có quyền chỉnh sửa thương hiệu này.');
        }

        // 2. VALIDATION
        $rules = [
            'name' => 'required|string|max:255',
            'province_id' => 'required',
            'district_id' => 'required',
            'address_detail' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'start_time' => 'required|date_format:H:i',
            'end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
            'venue_types' => 'required|array|min:1',
            'venue_types.*' => 'exists:venue_types,id',

            // Image Rules
            'new_files.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Max 2MB
            'image_links.*' => 'url',
            'primary_image_index' => 'required', // String identifier (VD: existing_5, new_file_0)
        ];

        // Nếu là Admin thì validate owner_id, ngược lại bỏ qua
        if (PermissionHelper::isAdmin($user)) {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.required' => 'Tên thương hiệu không được để trống.',
            'lat.required' => 'Vui lòng chọn vị trí trên bản đồ.',
            'venue_types.required' => 'Chọn ít nhất 1 loại hình sân.',
            'primary_image_index.required' => 'Vui lòng chọn 1 ảnh làm ảnh đại diện.',
            'end_time.regex' => 'Giờ đóng cửa không hợp lệ.',
        ]);

        // Logic Validate nâng cao (After Hook)
        $validator->after(function ($validator) use ($request, $venue) {
            // A. Kiểm tra giờ
            $start = $request->input('start_time');
            $end = $request->input('end_time');
            // (Bạn có thể thêm logic check start < end ở đây nếu cần)

            // B. Kiểm tra tổng số lượng ảnh sau khi cập nhật
            $deletedIds = array_filter(explode(',', $request->input('deleted_image_ids', '')));

            // Số ảnh cũ còn lại
            $remainingExisting = $venue->images()->whereNotIn('id', $deletedIds)->count();

            // Số ảnh mới (File + Link)
            $newFilesCount = $request->file('new_files') ? count($request->file('new_files')) : 0;
            $newLinksCount = $request->input('image_links') ? count($request->input('image_links')) : 0;

            $totalImages = $remainingExisting + $newFilesCount + $newLinksCount;

            if ($totalImages === 0) {
                $validator->errors()->add('images', 'Phải có ít nhất 1 hình ảnh cho thương hiệu.');
            }
            if ($totalImages > 5) {
                $validator->errors()->add('images', 'Tổng số ảnh không được vượt quá 5.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // 3. XỬ LÝ DỮ LIỆU
        // Format lại giờ (thêm :00 giây)
        if (strlen($data['start_time']) === 5) $data['start_time'] .= ':00';
        if ($data['end_time'] === '24:00') {
            $data['end_time'] = '23:59:59';
        } elseif (strlen($data['end_time']) === 5) {
            $data['end_time'] .= ':00';
        }

        DB::beginTransaction();
        try {
            // 4. CẬP NHẬT VENUE (Thông tin cơ bản)
            $venue->update([
                'name' => $data['name'],
                'owner_id' => $data['owner_id'] ?? $venue->owner_id,
                'province_id' => $data['province_id'],
                'district_id' => $data['district_id'],
                'address_detail' => $data['address_detail'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'phone' => $data['phone'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            // Sync loại hình sân
            $venue->venueTypes()->sync($data['venue_types']);

            // 5. XỬ LÝ HÌNH ẢNH (Quan trọng)

            // A. Xóa ảnh cũ bị đánh dấu
            $deletedIds = array_filter(explode(',', $request->input('deleted_image_ids', '')));
            if (!empty($deletedIds)) {
                $imagesToDelete = $venue->images()->whereIn('id', $deletedIds)->get();
                foreach ($imagesToDelete as $img) {
                    // Nếu là file local (không chứa http) thì xóa file vật lý
                    if (!filter_var($img->url, FILTER_VALIDATE_URL)) {
                        $relativePath = str_replace('/storage/', '', $img->url);
                        Storage::disk('public')->delete($relativePath);
                    }
                    $img->delete();
                }
            }

            // Mảng tạm để mapping Key từ Frontend -> Model vừa tạo
            $newImagesMap = [];

            // B. Upload và Lưu File mới
            if ($request->hasFile('new_files')) {
                foreach ($request->file('new_files') as $idx => $file) {
                    $path = $file->store('uploads/venues', 'public');
                    $img = $venue->images()->create([
                        'url' => '/storage/' . $path,
                        'is_primary' => false
                    ]);
                    // Key này khớp với JS: new_file_0, new_file_1...
                    $newImagesMap["new_file_{$idx}"] = $img;
                }
            }

            // C. Lưu Link ảnh mới
            if ($request->has('image_links')) {
                foreach ($request->input('image_links') as $idx => $link) {
                    if (!empty($link)) {
                        $img = $venue->images()->create([
                            'url' => trim($link),
                            'is_primary' => false
                        ]);
                        // Key này khớp với JS: new_link_0, new_link_1...
                        $newImagesMap["new_link_{$idx}"] = $img;
                    }
                }
            }

            // D. Thiết lập Ảnh đại diện (Primary)
            $primaryKey = $request->input('primary_image_index'); // Ví dụ: 'existing_15' hoặc 'new_file_0'

            // Reset toàn bộ về false trước
            $venue->images()->update(['is_primary' => false]);

            if (str_starts_with($primaryKey, 'existing_')) {
                // Trường hợp chọn ảnh cũ
                $id = str_replace('existing_', '', $primaryKey);
                // Đảm bảo ảnh này thuộc về venue và chưa bị xóa
                $venue->images()->where('id', $id)->update(['is_primary' => true]);
            } elseif (isset($newImagesMap[$primaryKey])) {
                // Trường hợp chọn ảnh vừa mới thêm (File hoặc Link)
                $newImagesMap[$primaryKey]->update(['is_primary' => true]);
            } else {
                // Fallback: Nếu Key không hợp lệ (hiếm), lấy ảnh đầu tiên còn lại làm primary
                $firstImg = $venue->images()->first();
                if ($firstImg) {
                    $firstImg->update(['is_primary' => true]);
                }
            }

            DB::commit();
            return redirect()->route('owner.venues.index')->with('success', 'Cập nhật thương hiệu thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Venue Update Error: " . $e->getMessage());
            Log::error($e->getTraceAsString()); // Log chi tiết để debug
            return back()->with('error', 'Đã có lỗi xảy ra: ' . $e->getMessage())->withInput();
        }
    }
    public function destroy(Venue $venue)
    {
        if (!PermissionHelper::ownsVenue($venue->id, Auth::user())) {
            abort(403, 'Bạn không có quyền xóa địa điểm này.');
        }

        DB::beginTransaction();
        try {
            $venue->images->each(function ($image) {
                Storage::disk('public')->delete($image->url);
            });
            $venueId = $venue->id;

            $venue->delete();

            DB::commit();
            broadcast(new DataDeleted($venueId, $this->nameChannel, 'venue.deleted'));
            return redirect()->route('owner.venues.index')->with('success', 'Xóa sân thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Venue delete error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xóa: ' . $e->getMessage());
        }
    }
}