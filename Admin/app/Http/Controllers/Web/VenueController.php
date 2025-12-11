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
        $provinces = Province::orderBy('name')->get();
        $venue_types = VenueType::orderBy('name')->get();
        $timeSlots = TimeSlot::orderBy('start_time')->get();

        return view('venue_owner.venue.create', compact('owners', 'provinces', 'venue_types', 'timeSlots'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // --- 1. Định nghĩa Rules và Messages ---
        $rules = [
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
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

        // --- 1. Chuẩn bị dữ liệu và tính tổng số ảnh còn lại ---

        // Lấy ID ảnh cũ bị đánh dấu xóa
        $deletedImageIds = array_filter((array) $request->input('existing_images_to_delete'));

        // Load lại ảnh hiện tại (chỉ những ảnh không bị đánh dấu xóa)
        $currentImages = $venue->images()->whereNotIn('id', $deletedImageIds)->get();
        $currentImageCount = $currentImages->count();
        $validOldImageIds = $currentImages->pluck('id')->toArray(); // ID của các ảnh cũ còn lại

        // Lấy số lượng ảnh mới và link mới (đã loại bỏ các phần tử null/empty)
        $newFilesCount = count(array_filter((array) $request->file('new_files')));
        $newLinksCount = count(array_filter((array) $request->input('image_links')));
        $totalNewImages = $newFilesCount + $newLinksCount;

        // Tổng số ảnh cuối cùng sau khi cập nhật
        $finalImageCount = $currentImageCount + $totalNewImages;

        // --- 2. Định nghĩa Validation Rules ---
        $rules = [
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'required|string',
            'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'start_time' => 'required|date_format:H:i',
            'end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
            'venue_types' => 'required|array|min:1',
            'venue_types.*' => 'exists:venue_types,id',
            'is_active' => 'nullable|boolean',

            // Input files và links mới
            'new_files' => 'nullable|array',
            'new_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_links' => 'nullable|array',
            'image_links.*' => 'nullable|url|max:500',

            // Quản lý ảnh cũ
            'existing_images_to_delete' => 'nullable|array',
            'existing_images_to_delete.*' => 'exists:images,id',

            // Index ảnh chính
            'primary_image_index' => 'required|integer', // Đảm bảo luôn là số
        ];

        if (PermissionHelper::isAdmin($user)) {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        // Thêm rule custom để kiểm tra tổng số ảnh cuối cùng (Min: 1, Max: 5)
        if ($finalImageCount < 1 || $finalImageCount > 5) {
            $rules['images_check'] = 'required'; // Field ảo để fail validation
        }

        $messages = [
            'name.required' => 'Tên thương hiệu không được bỏ trống.',
            // ... (các thông báo khác giữ nguyên) ...
            'images_check.required' => 'Bạn phải có ít nhất 1 ảnh và tối đa 5 ảnh (bao gồm ảnh cũ, ảnh mới và link ảnh).',
            'new_files.*.image' => 'File phải là định dạng ảnh hợp lệ.',
            'image_links.*.url' => 'Link ảnh phải là một URL hợp lệ.',
            'image_links.*.max' => 'Đường dẫn link ảnh không được quá 500 ký tự.',
            'primary_image_index.required' => 'Vui lòng chọn ảnh đại diện.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // FIX LỖI QUAN TRỌNG: Kiểm tra xem ID/Index ảnh chính có tồn tại trong tập ảnh cuối cùng không.
        $validator->after(function ($validator) use ($request, $finalImageCount, $validOldImageIds, $totalNewImages) {
            $primaryImageValue = (int) $request->input('primary_image_index', -1);

            if ($finalImageCount > 0) {

                // Trường hợp không có giá trị nào được chọn
                if ($primaryImageValue == -1) {
                    $validator->errors()->add('primary_image_index', 'Vui lòng chọn ảnh đại diện.');
                    return;
                }

                $isValidPrimary = false;

                // A) Kiểm tra nếu là ID ảnh cũ (> 0)
                if ($primaryImageValue > 0) {
                    if (in_array($primaryImageValue, $validOldImageIds)) {
                        $isValidPrimary = true;
                    }
                }

                // B) Kiểm tra nếu là Index ảnh mới (>= 0)
                if (!$isValidPrimary && $primaryImageValue >= 0) {
                    // Index ảnh mới (0, 1, ...) phải nằm trong phạm vi của ảnh mới
                    if ($primaryImageValue < $totalNewImages) {
                        $isValidPrimary = true;
                    }
                }

                if (!$isValidPrimary) {
                    // Nếu giá trị là số (integer) nhưng không khớp với ID ảnh cũ còn lại và cũng không khớp Index ảnh mới
                    $validator->errors()->add('primary_image_index', 'Ảnh đại diện được chọn không hợp lệ hoặc đã bị xóa. Vui lòng chọn lại.');
                }
            } else {
                // Trường hợp $finalImageCount == 0 (không còn ảnh nào)
                if ($primaryImageValue != -1) { // <--- Logic này hoạt động nếu JS gửi -1
                    $validator->errors()->add('images_check', 'Bạn phải có ít nhất 1 ảnh. Lỗi đồng bộ ảnh chính.');
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        // Chuẩn hóa giờ giấc
        // ... (Giữ nguyên logic chuẩn hóa giờ giấc) ...
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

        if (!PermissionHelper::isAdmin($user)) {
            $validatedData['owner_id'] = $venue->owner_id;
        }

        $validatedData['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : $venue->is_active;

        // --- 3. BẮT ĐẦU TRANSACTION ---
        DB::beginTransaction();

        $newlyCreatedImages = collect(); // Lưu lại ảnh/link mới tạo

        try {
            // 3.1. Cập nhật thông tin cơ bản
            $venue->update([
                'name' => $validatedData['name'],
                'owner_id' => $validatedData['owner_id'],
                'province_id' => $validatedData['province_id'],
                'district_id' => $validatedData['district_id'],
                'address_detail' => $validatedData['address_detail'],
                'phone' => $validatedData['phone'] ?? null,
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
                'is_active' => $validatedData['is_active'],
            ]);

            // 3.2. Cập nhật loại sân (Venue Types)
            $venue->venueTypes()->sync($validatedData['venue_types']);

            // 3.3. XỬ LÝ ẢNH
            // A. Xóa ảnh cũ đã bị loại bỏ
            $deletedImageIds = array_filter((array) $request->input('existing_images_to_delete'));
            if (!empty($deletedImageIds)) {
                $imagesToDelete = $venue->images()->whereIn('id', $deletedImageIds)->get();
                foreach ($imagesToDelete as $image) {
                    // Chỉ xóa file vật lý nếu url không bắt đầu bằng http
                    if (strpos($image->url, 'http') !== 0) {
                        Storage::disk('public')->delete($image->url);
                    }
                    $image->delete();
                }
            }

            // B. Tải lên và lưu trữ các File ảnh mới
            if ($request->hasFile('new_files')) {
                foreach ($request->file('new_files') as $file) {
                    if ($file) {
                        $path = $file->store('uploads/venues', 'public');
                        $newImage = $venue->images()->create([
                            'url' => $path,
                            'is_primary' => false,
                        ]);
                        $newlyCreatedImages->push($newImage);
                    }
                }
            }

            // C. Lưu trữ các Link ảnh mới
            $imageLinks = array_filter((array) $request->input('image_links'));
            foreach ($imageLinks as $link) {
                $newImage = $venue->images()->create([
                    'url' => $link,
                    'is_primary' => false,
                ]);
                $newlyCreatedImages->push($newImage);
            }

            // D. Cập nhật ảnh chính (Primary Image) - ĐÃ FIX LOGIC
            $primaryImageValue = (int) $request->input('primary_image_index', -1);
            $primaryImage = null;

            // Lấy danh sách tất cả ảnh sau khi xóa (Ảnh cũ còn lại)
            $remainingOldImages = $venue->images()->whereNotIn('id', $deletedImageIds)->get();

            // Tạo danh sách tổng thể: Ảnh cũ còn lại + Ảnh/Link mới tạo
            $allImages = $remainingOldImages->merge($newlyCreatedImages);

            // Đặt tất cả ảnh về is_primary=false trước
            // Chỉ cập nhật những ảnh còn lại (hoặc ảnh mới)
            $venue->images()->whereIn('id', $allImages->pluck('id'))->update(['is_primary' => false]);


            // --- TÌM ẢNH CHÍNH DỰA TRÊN GIÁ TRỊ ID HOẶC INDEX ---
            if ($primaryImageValue > 0) {
                // Trường hợp 1: Giá trị là ID của ảnh cũ
                $primaryImage = $allImages->firstWhere('id', $primaryImageValue);
            } elseif ($primaryImageValue >= 0) {
                // Trường hợp 2: Giá trị là Index (0, 1, 2...) của ảnh mới
                // Index này là index tương đối trong mảng $newlyCreatedImages
                $primaryImage = $newlyCreatedImages->get($primaryImageValue);
            }

            // Cập nhật ảnh chính đã tìm được
            if ($primaryImage) {
                // Sử dụng Model để update is_primary
                $primaryImage->is_primary = true;
                $primaryImage->save();
            } elseif ($allImages->isNotEmpty()) {
                // Nếu không tìm thấy ảnh chính hợp lệ VÀ vẫn còn ảnh, chọn ảnh đầu tiên làm primary
                $firstImage = $allImages->first();
                $firstImage->is_primary = true;
                $firstImage->save();
            }

            DB::commit();

            $redirectRoute = PermissionHelper::isAdmin($user) ? 'admin.venues.index' : 'owner.venues.index';
            return redirect()->route($redirectRoute)->with('success', 'Cập nhật sân thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Venue update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật: Vui lòng kiểm tra Log Server.');
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
