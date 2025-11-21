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
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($validator) use ($request) {
            $venueStartTimeStr = $request->input('start_time');
            $venueEndTimeStr = $request->input('end_time');
            $courts = $request->input('courts', []);

            if (!$venueStartTimeStr || !$venueEndTimeStr || empty($courts)) {
                return;
            }

            // Tạo đối tượng Carbon cho giờ hoạt động của Venue.
            // Nếu Giờ đóng cửa <= Giờ mở cửa, thêm 1 ngày để mô phỏng qua đêm.
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

                    // Xử lý slot qua đêm (ví dụ: 23:00 -> 01:00)
                    if ($slot['end_time'] === '24:00' || $slotEnd->lte($slotStart)) {
                        $slotEnd->addDay();
                    }

                    // Điều chỉnh ngày của slot để so sánh với VenueStart/VenueEnd (VenueStart luôn là ngày 1, VenueEnd có thể là ngày 2)
                    if ($slotStart->lt($venueStart)) {
                        $slotStart->addDay();
                        $slotEnd->addDay();
                    }

                    $errorMessage = 'Khung giờ phải nằm trong giờ hoạt động của thương hiệu (' . $venueStartTimeStr . ' - ' . $venueEndTimeStr . ').';

                    // 1. Kiểm tra giờ bắt đầu/kết thúc slot có hợp lệ không (start < end)
                    if ($slotStart->gte($slotEnd)) {
                        $validator->errors()->add("courts.{$courtIndex}.time_slots.{$slotIndex}.end_time", 'Giờ kết thúc phải sau giờ bắt đầu.');
                        continue;
                    }

                    // 2. Kiểm tra slot nằm ngoài phạm vi Venue
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
                                ? '23:59:59' // Chuẩn hóa 24:00 cho DB
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
            Log::error('Venue store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi lưu: ' . $e->getMessage());
        }
    }

    //================ Edit / Update / Delete =================//
    public function edit(Venue $venue)
    {
        if (!PermissionHelper::ownsVenue($venue->id, Auth::user())) {
            abort(403, 'Bạn không có quyền sửa địa điểm này.');
        }

        $owners = User::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        $venue_types = VenueType::orderBy('name')->get();

        return view('venue_owner.venue.edit', compact('venue', 'owners', 'provinces', 'districts', 'venue_types'));
    }

    public function update(Request $request, Venue $venue)
    {
        if (!PermissionHelper::ownsVenue($venue->id, Auth::user())) {
            abort(403, 'Bạn không có quyền sửa địa điểm này.');
        }

        $user = Auth::user();
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
        ];

        if (PermissionHelper::isAdmin($user)) {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        $messages = [
            'name.required' => 'Tên thương hiệu không được bỏ trống.',
            'province_id.required' => 'Vui lòng chọn Tỉnh/Thành phố.',
            'district_id.required' => 'Vui lòng chọn Quận/Huyện.',
            'address_detail.required' => 'Địa chỉ chi tiết không được bỏ trống.',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
            'start_time.required' => 'Giờ mở cửa không được bỏ trống.',
            'end_time.required' => 'Giờ đóng cửa không được bỏ trống.',
            'venue_types.required' => 'Vui lòng chọn ít nhất một loại hình sân.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

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

        DB::beginTransaction();
        try {
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
