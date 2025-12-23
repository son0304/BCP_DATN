<?php

namespace App\Http\Controllers\Web;

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
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VenueController extends Controller
{
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
        $venue->load([
            'owner',
            'province',
            'courts',
            'services',
            'images',
        ]);

        $user = Auth::user();

        if ($user->role->name !== 'admin' && $user->id !== $venue->owner_id) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        if ($user->role->name === 'admin') {
            return view('admin.venue.show', compact('venue'));
        } else {
            return view('venue_owner.venue.show', compact('venue'));
        }
    }

    public function updateStatus(Request $request, Venue $venue)
    {
        if (!PermissionHelper::isAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $validatedData = $request->validate([
            'is_active' => 'required|in:0,1',
        ]);

        $venue->update(['is_active' => $validatedData['is_active']]);
        $user = $venue->owner;
        $urlWebAdmin = env('BACKEND_URL', 'http://127.0.0.1:8000');
        if ($user->role->name != 'admin' && $user->role->name != 'venue_owner') {
            $user->update(['role_id' => 2]);
        }
        if ($validatedData['is_active'] == 1) {
            Mail::to($user->email)->send(new BookingConfirmationMail($user, $urlWebAdmin));
        }

        return redirect()->route('admin.venues.index')->with('success', 'Cập nhật trạng thái thành công!');
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
            'venue_types' => 'required|array|min:1',
            'venue_types.*' => 'exists:venue_types,id',

            // Rules cho files và links ảnh
            'images' => 'nullable|array', // Files tải lên
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_links' => 'nullable|array', // Links ảnh chèn vào
            'image_links.*' => 'nullable|url|max:500',
            'primary_image_index' => 'required|integer|min:0', // Index trong mảng combined

            // Rules cho Courts và Time Slots (Giữ nguyên)
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
                    $isPrimary = $index === $mainImageIndex;

                    if ($imageData instanceof \Illuminate\Http\UploadedFile) {
                        // File tải lên -> Lưu vào storage
                        $path = $imageData->store('uploads/venues', 'public');
                        $url = $path;
                    } elseif (is_string($imageData)) {
                        // Link ảnh -> Dùng URL
                        $url = $imageData;
                    }

                    if ($url) {
                        $image = $venue->images()->create([
                            'url' => $url,
                            'is_primary' => $isPrimary,
                        ]);
                        // Lưu lại model để rollback file vật lý nếu cần
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
        $venue->load('images'); // Load ảnh

        return view('venue_owner.venue.edit', compact('venue', 'owners', 'provinces', 'districts', 'venue_types'));
    }

    public function update(Request $request, Venue $venue)
{
    $user = Auth::user();

    if (!PermissionHelper::ownsVenue($venue->id, $user) && !PermissionHelper::isAdmin($user)) {
        abort(403, 'Bạn không có quyền sửa địa điểm này.');
    }

    // --- Validation Rules (Thêm rules cho courts) ---
    $rules = [
        'name' => 'required|string|max:255',
        'province_id' => 'required',
        'district_id' => 'required',
        'address_detail' => 'required|string',
        'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required',
        'venue_types' => 'required|array|min:1',
        
        // Courts & Slots
        'courts' => 'nullable|array',
        'courts.*.name' => 'required|string|max:255',
        'courts.*.venue_type_id' => 'required|exists:venue_types,id',
        'courts.*.time_slots' => 'nullable|array',
        
        // Images (Giữ nguyên logic cũ)
        'primary_image_index' => 'required',
    ];
    
    // Nếu là admin thì check owner
    if (PermissionHelper::isAdmin($user)) {
        $rules['owner_id'] = 'required|exists:users,id';
    }

    $validator = Validator::make($request->all(), $rules);
    
    // ... (Giữ validator->after logic kiểm tra giờ giấc như create) ...

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $data = $validator->validated();
    
    // Format giờ
    if (strlen($data['start_time']) === 5) $data['start_time'] .= ':00';
    if ($data['end_time'] === '24:00') $data['end_time'] = '23:59:59';
    elseif (strlen($data['end_time']) === 5) $data['end_time'] .= ':00';

    DB::beginTransaction();
    try {
        // 1. Update Venue
        $venue->update([
            'name' => $data['name'],
            'owner_id' => isset($data['owner_id']) ? $data['owner_id'] : $venue->owner_id,
            'province_id' => $data['province_id'],
            'district_id' => $data['district_id'],
            'address_detail' => $data['address_detail'],
            'phone' => $data['phone'] ?? null,
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);
        
        $venue->venueTypes()->sync($data['venue_types']);

        // 2. XỬ LÝ COURTS & SLOTS (PHẦN MỚI)
        $submittedCourts = $request->input('courts', []);
        
        // Lấy danh sách ID các sân ĐÃ SUBMIT có chứa ID (tức là sân cũ)
        $submittedCourtIds = [];
        foreach($submittedCourts as $c) {
            if(isset($c['id'])) $submittedCourtIds[] = $c['id'];
        }
        
        // Xóa các sân không còn trong danh sách submit (User đã nhấn xóa trên UI)
        // Lưu ý: Cần xóa ràng buộc (TimeSlots, Availability, Booking...) trước nếu ko cascade
        $venue->courts()->whereNotIn('id', $submittedCourtIds)->delete();

        foreach ($submittedCourts as $courtData) {
            // Update hoặc Create Court
            $court = Court::updateOrCreate(
                ['id' => $courtData['id'] ?? null, 'venue_id' => $venue->id], // Điều kiện tìm (nếu có id)
                [
                    'venue_id' => $venue->id,
                    'name' => $courtData['name'],
                    'venue_type_id' => $courtData['venue_type_id'],
                    'surface' => $courtData['surface'] ?? null,
                    'is_indoor' => $courtData['is_indoor'] ?? 0,
                ]
            );

            // Xử lý TimeSlots cho sân này
            $submittedSlots = $courtData['time_slots'] ?? [];
            $submittedSlotIds = [];
            foreach($submittedSlots as $s) {
                if(isset($s['id'])) $submittedSlotIds[] = $s['id'];
            }
            
            // Xóa slot thừa
            $court->timeSlots()->whereNotIn('id', $submittedSlotIds)->delete();
            
            foreach ($submittedSlots as $slotData) {
                // Format time
                $sStart = $slotData['start_time'];
                $sEnd = $slotData['end_time'];
                if(strlen($sStart)===5) $sStart .= ':00';
                if($sEnd === '24:00') $sEnd = '23:59:59';
                elseif(strlen($sEnd)===5) $sEnd .= ':00';
                
                $slot = TimeSlot::updateOrCreate(
                    ['id' => $slotData['id'] ?? null, 'court_id' => $court->id],
                    [
                        'court_id' => $court->id,
                        'start_time' => $sStart,
                        'end_time' => $sEnd,
                        // Update giá tiền vào bảng Availability luôn nếu cần thiết kế đơn giản
                        // Hoặc chỉ update TimeSlot, giá tiền thường nằm ở Availability
                    ]
                );
                
                // Cập nhật giá cho các availability trong tương lai
                // (Logic đơn giản: update giá cho 30 ngày tới chưa book)
                Availability::where('slot_id', $slot->id)
                    ->where('date', '>=', now()->toDateString())
                    ->where('status', 'open')
                    ->update(['price' => $slotData['price']]);
                    
                // Nếu slot mới tạo, cần insert Availability
                if(!isset($slotData['id'])) {
                   // Logic insert availability giống Create (Loop 30 ngày)
                   // ... copy logic từ hàm store ...
                   $availabilitiesToInsert = [];
                   for ($i = 0; $i < 30; $i++) {
                        $date = Carbon::today()->addDays($i)->toDateString();
                        $availabilitiesToInsert[] = [
                            'court_id' => $court->id,
                            'date' => $date,
                            'slot_id' => $slot->id,
                            'price' => $slotData['price'],
                            'status' => 'open',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    Availability::insert($availabilitiesToInsert);
                }
            }
        }

        // 3. XỬ LÝ ẢNH (Logic giữ nguyên từ controller cũ của bạn)
        // ... (Code xóa ảnh cũ, thêm ảnh mới, set primary) ...
        // Lưu ý: Logic ảnh của controller cũ của bạn khá tốt, hãy giữ lại phần xử lý Images.
        // Chỉ cần đảm bảo phần input names từ View khớp ('existing_images_to_delete', 'new_files', 'image_links')
        
        // === PASTE PHẦN XỬ LÝ ẢNH CŨ VÀO ĐÂY ===
        // A. Xóa ảnh cũ
        $deletedImageIds = array_filter(explode(',', $request->input('existing_images_to_delete', '')));
        if (!empty($deletedImageIds)) {
             $imgs = $venue->images()->whereIn('id', $deletedImageIds)->get();
             foreach($imgs as $img) {
                 if (strpos($img->url, 'http') !== 0) Storage::disk('public')->delete($img->url);
                 $img->delete();
             }
        }
        
        $newlyCreatedImages = collect();
        // B. Files mới
        if ($request->hasFile('new_files')) {
            foreach ($request->file('new_files') as $file) {
                $path = $file->store('uploads/venues', 'public');
                $newlyCreatedImages->push($venue->images()->create(['url' => $path, 'is_primary' => false]));
            }
        }
        // C. Links mới
        if ($request->has('image_links')) {
            foreach ($request->input('image_links') as $link) {
                if($link) $newlyCreatedImages->push($venue->images()->create(['url' => $link, 'is_primary' => false]));
            }
        }
        
        // D. Set Primary
        // Reset all
        $venue->images()->update(['is_primary' => false]);
        
        $primaryVal = $request->input('primary_image_index');
        // Logic: Nếu primaryVal trùng ID ảnh cũ -> Set.
        // Nếu không -> xem như index ảnh mới (nếu cần thiết, hoặc user chọn ảnh mới thì input value nên xử lý khác)
        // Để đơn giản: Nếu tìm thấy ID trong DB thì set, nếu ko thì lấy từ mảng mới tạo.
        
        $img = $venue->images()->find($primaryVal);
        if($img) {
            $img->update(['is_primary' => true]);
        } elseif ($newlyCreatedImages->isNotEmpty()) {
            // Fallback hoặc logic index (0,1,2)
            if(is_numeric($primaryVal) && $primaryVal < $newlyCreatedImages->count()) {
                 $newlyCreatedImages[$primaryVal]->update(['is_primary' => true]);
            } else {
                 $newlyCreatedImages->first()->update(['is_primary' => true]);
            }
        } elseif ($venue->images()->exists()) {
             $venue->images()->first()->update(['is_primary' => true]);
        }
        // ==========================================

        DB::commit();
        return redirect()->route('owner.venues.index')->with('success', 'Cập nhật thành công!');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error($e);
        return back()->with('error', 'Lỗi: ' . $e->getMessage());
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

            $venue->delete();

            DB::commit();
            return redirect()->route('owner.venues.index')->with('success', 'Xóa sân thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Venue delete error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xóa: ' . $e->getMessage());
        }
    }
}
