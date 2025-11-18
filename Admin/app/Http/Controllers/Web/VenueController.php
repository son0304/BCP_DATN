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

class VenueController extends Controller
{
    //==============ADMIN=================//
    public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            // Admin xem tất cả venues
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
        // Load tất cả quan hệ cần thiết
        $venue->load([
            'owner',
            'province',
            'courts',
            'services',
            'images',
        ]);

        $user = Auth::user();

        // Kiểm tra quyền truy cập
        if ($user->role->name !== 'admin' && $user->id !== $venue->owner_id) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Điều hướng view theo role
        if ($user->role->name === 'admin') {
            return view('admin.venue.show', compact('venue'));
        } else {
            return view('venue_owner.venue.show', compact('venue'));
        }
    }



    public function updateStatus(Request $request, Venue $venue)
    {
        // Kiểm tra quyền admin
        if (!PermissionHelper::isAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // Validate dữ liệu
        $validatedData = $request->validate([
            'is_active' => 'required|in:0,1',
        ]);

        // Cập nhật trạng thái
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
        $rules = [
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'required|string',
            'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'venue_types' => 'required|array|min:1',
            'venue_types.*' => 'exists:venue_types,id',
            'courts' => 'nullable|array',
            'courts.*.name' => 'required|string|max:255',
            'courts.*.venue_type_id' => 'required|exists:venue_types,id',
            'courts.*.surface' => 'nullable|string|max:255',
            'courts.*.is_indoor' => 'nullable|in:0,1',
            'courts.*.time_slots' => 'nullable|array',
            'courts.*.time_slots.*.start_time' => 'required|date_format:H:i',
            'courts.*.time_slots.*.end_time' => 'required|date_format:H:i|after:courts.*.time_slots.*.start_time',
            'courts.*.time_slots.*.price' => 'required|numeric|min:0',
        ];
        if (PermissionHelper::isAdmin($user)) {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        $messages = [
            // Thông tin chung của Venue
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
            'end_time.date_format' => 'Giờ đóng cửa sai định dạng (HH:mm).',
            'end_time.after' => 'Giờ đóng cửa phải sau giờ mở cửa.',
            'venue_types.required' => 'Vui lòng chọn ít nhất một loại hình sân.',

            // Dành cho Admin chọn chủ sân
            'owner_id.required' => 'Vui lòng chọn chủ sân.',
            'owner_id.exists' => 'Chủ sân được chọn không tồn tại.',

            // Validate mảng Courts (Sân con)
            'courts.*.name.required' => 'Tên sân không được bỏ trống.',
            'courts.*.name.max' => 'Tên sân không được vượt quá 255 ký tự.',
            'courts.*.venue_type_id.required' => 'Vui lòng chọn loại sân.',
            'courts.*.venue_type_id.exists' => 'Loại sân không hợp lệ.',
            'courts.*.is_indoor.in' => 'Trạng thái sân (Trong nhà/Ngoài trời) không hợp lệ.',

            // Validate mảng Time Slots (Khung giờ)
            'courts.*.time_slots.*.start_time.required' => 'Giờ bắt đầu khung giờ không được bỏ trống.',
            'courts.*.time_slots.*.start_time.date_format' => 'Giờ bắt đầu khung giờ sai định dạng.',
            'courts.*.time_slots.*.end_time.required' => 'Giờ kết thúc khung giờ không được bỏ trống.',
            'courts.*.time_slots.*.end_time.date_format' => 'Giờ kết thúc khung giờ sai định dạng.',
            'courts.*.time_slots.*.end_time.after' => 'Giờ kết thúc khung giờ phải sau giờ bắt đầu.',
            'courts.*.time_slots.*.price.required' => 'Giá tiền không được bỏ trống.',
            'courts.*.time_slots.*.price.numeric' => 'Giá tiền phải là một số.',
            'courts.*.time_slots.*.price.min' => 'Giá tiền không được nhỏ hơn 0.',
        ];

        // validate khung giờ 
        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function ($validator) use ($request) {
            $venueStartTimeStr = $request->input('start_time');
            $venueEndTimeStr = $request->input('end_time');
            $courts = $request->input('courts', []);

            if (!$venueStartTimeStr || !$venueEndTimeStr || empty($courts) || $validator->errors()->has('start_time') || $validator->errors()->has('end_time')) {
                return;
            }

            $venueStart = Carbon::parse($venueStartTimeStr);
            $venueEnd = Carbon::parse($venueEndTimeStr);

            foreach ($courts as $courtIndex => $court) {
                if (empty($court['time_slots'])) {
                    continue;
                }

                foreach ($court['time_slots'] as $slotIndex => $slot) {
                    // Chỉ kiểm tra nếu các trường thời gian có tồn tại và đúng định dạng
                    if (empty($slot['start_time']) || empty($slot['end_time']) || $validator->errors()->has("courts.{$courtIndex}.time_slots.{$slotIndex}.*")) {
                        continue;
                    }

                    $slotStart = Carbon::parse($slot['start_time']);
                    $slotEnd = Carbon::parse($slot['end_time']);

                    $errorMessage = 'Khung giờ phải nằm trong giờ hoạt động của thương hiệu (' . $venueStart->format('H:i') . ' - ' . $venueEnd->format('H:i') . ').';

                    // Kiểm tra giờ bắt đầu của slot
                    if ($slotStart->lt($venueStart) || $slotStart->gte($venueEnd)) {
                        $validator->errors()->add("courts.{$courtIndex}.time_slots.{$slotIndex}.start_time", $errorMessage);
                    }

                    // Kiểm tra giờ kết thúc của slot
                    if ($slotEnd->lte($venueStart) || $slotEnd->gt($venueEnd)) {
                        $validator->errors()->add("courts.{$courtIndex}.time_slots.{$slotIndex}.end_time", $errorMessage);
                    }
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $validatedData = $validator->validated();



        if (PermissionHelper::isVenueOwner($user)) {
            $validatedData['owner_id'] = $user->id;
        }

        if (isset($validatedData['start_time']) && strlen($validatedData['start_time']) === 5) {
            $validatedData['start_time'] .= ':00';
        }
        if (isset($validatedData['end_time']) && strlen($validatedData['end_time']) === 5) {
            $validatedData['end_time'] .= ':00';
        }

        DB::beginTransaction();
        try {
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

            if (!empty($validatedData['venue_types'])) {
                $venue->venueTypes()->attach($validatedData['venue_types']);
            }

            if (!empty($validatedData['courts']) && is_array($validatedData['courts'])) {
                foreach ($validatedData['courts'] as $courtData) {
                    // if (empty($courtData['name']) || empty($courtData['venue_type_id'])) {
                    //     continue;
                    // }

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
                            $endTime = strlen($timeSlotData['end_time']) === 5 ? $timeSlotData['end_time'] . ':00' : $timeSlotData['end_time'];
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
            Log::error('Venue store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi lưu: ' . $e->getMessage());
        }
    }
    public function edit(Venue $venue)
    {
        if (!PermissionHelper::ownsVenue($venue->id, Auth::user())) {
            abort(403, 'Bạn không có quyền sửa địa điểm này.');
        }

        $owners = User::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();

        // ⚠️ QUAN TRỌNG: Phải lấy TOÀN BỘ District để JS lọc động.
        // Code cũ của bạn chỉ lấy district của tỉnh hiện tại, nên khi đổi tỉnh sẽ bị lỗi mất data.
        $districts = District::orderBy('name')->get();

        $venue_types = VenueType::orderBy('name')->get();

        return view('venue_owner.venue.edit', compact('venue', 'owners', 'provinces', 'districts', 'venue_types'));
    }

    public function update(Request $request, Venue $venue)
    {
        // 1. Check quyền
        if (!PermissionHelper::ownsVenue($venue->id, Auth::user())) {
            abort(403, 'Bạn không có quyền sửa địa điểm này.');
        }
        $user = Auth::user();

        // 2. Khai báo Rules
        $rules = [
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'required|string',
            'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',

            // 👇 Validate bắt buộc chọn checkbox Loại hình sân
            'venue_types' => 'required|array|min:1',
            'venue_types.*' => 'exists:venue_types,id',

            'is_active' => 'nullable|boolean',
        ];

        if (PermissionHelper::isAdmin($user)) {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        // 3. Khai báo Messages
        $messages = [
            'name.required' => 'Tên thương hiệu không được bỏ trống.',
            'province_id.required' => 'Vui lòng chọn Tỉnh/Thành phố.',
            'district_id.required' => 'Vui lòng chọn Quận/Huyện.',
            'address_detail.required' => 'Địa chỉ chi tiết không được bỏ trống.',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
            'start_time.required' => 'Giờ mở cửa không được bỏ trống.',
            'end_time.required' => 'Giờ đóng cửa không được bỏ trống.',
            'end_time.after' => 'Giờ đóng cửa phải sau giờ mở cửa.',

            // 👇 Thông báo lỗi cho Venue Types
            'venue_types.required' => 'Vui lòng chọn ít nhất một loại hình sân.',
            'venue_types.min' => 'Vui lòng chọn ít nhất một loại hình sân.',
        ];

        // 4. Xử lý Validate
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput(); // Trả lại dữ liệu cũ để form không bị trống
        }

        $validatedData = $validator->validated();

        // 5. Format dữ liệu
        if (isset($validatedData['start_time']) && strlen($validatedData['start_time']) === 5) {
            $validatedData['start_time'] .= ':00';
        }
        if (isset($validatedData['end_time']) && strlen($validatedData['end_time']) === 5) {
            $validatedData['end_time'] .= ':00';
        }

        // Nếu không phải admin, giữ nguyên owner cũ
        if (!PermissionHelper::isAdmin($user)) {
            $validatedData['owner_id'] = $venue->owner_id;
        }

        $validatedData['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : $venue->is_active;

        DB::beginTransaction();
        try {
            // 6. Update Venue
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

            // 7. Sync quan hệ nhiều-nhiều (Lưu loại hình sân)
            // Vì đã validate required nên mảng này luôn có dữ liệu
            $venue->venueTypes()->sync($validatedData['venue_types']);

            DB::commit();
            return redirect()->route('owner.venues.index')->with('success', 'Cập nhật sân thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Venue update error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật: ' . $e->getMessage());
        }
    }
    public function destroy(Venue $venue)
    {
        if (!PermissionHelper::ownsVenue($venue->id, Auth::user())) {
            abort(403, 'Bạn không có quyền xóa địa điểm này.');
        }

        $venue->delete();
        return redirect()->route('owner.venues.index')->with('success', 'Xóa sân thành công!');
    }
}
