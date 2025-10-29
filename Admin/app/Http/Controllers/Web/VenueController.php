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
use App\Models\Role;

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

        return redirect()->route('admin.venues.index')->with('success', 'Cập nhật trạng thái thành công!');
    }

    //==============Venue_Owner=================//




    public function create()
    {
        if (!PermissionHelper::isAdmin(Auth::user()) && !PermissionHelper::isVenueOwner(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $owners = User::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        $venue_types = VenueType::orderBy('name')->get();
        $timeSlots = TimeSlot::orderBy('start_time')->get();

        return view('venue_owner.venue.create', compact('owners', 'provinces', 'districts', 'venue_types', 'timeSlots'));
    }
    public function store(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:users,id',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'required|string',
            'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'courts' => 'nullable|array',
            'courts.*.name' => 'required_with:courts|string|max:255',
            'courts.*.venue_type_id' => 'required_with:courts|exists:venue_types,id',
            'courts.*.surface' => 'nullable|string|max:255',
            'courts.*.is_indoor' => 'nullable|in:0,1',
            'courts.*.time_slots' => 'nullable|array',
            'courts.*.time_slots.*.start_time' => 'required_with:courts.*.time_slots|date_format:H:i',
            'courts.*.time_slots.*.end_time' => 'required_with:courts.*.time_slots|date_format:H:i|after:courts.*.time_slots.*.start_time',
            'courts.*.time_slots.*.price' => 'required_with:courts.*.time_slots|numeric|min:0',
        ], [
            'name.required' => 'Không được bỏ trống'
        ]);

        if (PermissionHelper::isVenueOwner($user)) {
            $validatedData['owner_id'] = $user->id;
        } elseif (!PermissionHelper::isAdmin($user)) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
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

            if (!empty($validatedData['courts']) && is_array($validatedData['courts'])) {
                foreach ($validatedData['courts'] as $courtData) {
                    if (empty($courtData['name']) || empty($courtData['venue_type_id'])) {
                        continue;
                    }

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
                            if (empty($timeSlotData['start_time']) || empty($timeSlotData['end_time']) || empty($timeSlotData['price'])) {
                                continue;
                            }

                            $startTime = strlen($timeSlotData['start_time']) === 5 ? $timeSlotData['start_time'] . ':00' : $timeSlotData['start_time'];
                            $endTime = strlen($timeSlotData['end_time']) === 5 ? $timeSlotData['end_time'] . ':00' : $timeSlotData['end_time'];
                            $price = (float)$timeSlotData['price'];

                            $timeSlot = TimeSlot::create([
                                'court_id' => $court->id,
                                'start_time' => $startTime,
                                'end_time' => $endTime,
                                'label' => $startTime . ' - ' . $endTime,
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
            return redirect()->route('admin.venue.index')->with('success', 'Đăng ký thương hiệu và sân thành công!');
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
        $districts = District::where('province_id', $venue->province_id)->orderBy('name')->get();
        $venue_types = VenueType::orderBy('name')->get();

        return view('venue_owner.venue.edit', compact('venue', 'owners', 'provinces', 'districts', 'venue_types'));
    }
    public function update(Request $request, Venue $venue)
    {
        if (!PermissionHelper::ownsVenue($venue->id, Auth::user())) {
            abort(403, 'Bạn không có quyền sửa địa điểm này.');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:users,id',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'required|string',
            'phone' => ['nullable', 'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'venue_types' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if (isset($validatedData['start_time']) && strlen($validatedData['start_time']) === 5) {
            $validatedData['start_time'] .= ':00';
        }
        if (isset($validatedData['end_time']) && strlen($validatedData['end_time']) === 5) {
            $validatedData['end_time'] .= ':00';
        }

        $validatedData['is_active'] = $request->boolean('is_active');

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

        if ($request->has('venue_types')) {
            $venue->venueTypes()->sync($request->venue_types);
        } else {
            $venue->venueTypes()->sync([]);
        }

        return redirect()->route('admin.venue.index')->with('success', 'Cập nhật sân thành công!');
    }
    public function destroy(Venue $venue)
    {
        if (!PermissionHelper::ownsVenue($venue->id, Auth::user())) {
            abort(403, 'Bạn không có quyền xóa địa điểm này.');
        }

        $venue->delete();
        return redirect()->route('admin.venue.index')->with('success', 'Xóa sân thành công!');
    }
}