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

class BrandController extends Controller
{
    /**
     * Hiển thị danh sách các thương hiệu.
     */
    public function index()
    {
        $venues = Venue::with('owner', 'province')->latest()->paginate(10);
        return view('brand.index', compact('venues'));
    }

    /**
     * Hiển thị form tạo mới.
     */
    public function create()
    {
        $owners = User::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $districts = District::orderBy('name')->get();
        $venue_types = VenueType::orderBy('name')->get();
        $timeSlots = TimeSlot::orderBy('start_time')->get();

        return view('brand.create', compact('owners', 'provinces', 'districts', 'venue_types', 'timeSlots'));
    }

    /**
     * Lưu một thương hiệu mới vào database.
     */
    public function store(Request $request)
    {
        // Validate cơ bản cho venue + cấu trúc courts nếu có
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:users,id',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'required|string',
            'phone' => ['nullable','regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'courts' => 'nullable|array',
            // nếu courts có -> validate từng phần
            'courts.*.name' => 'required_with:courts|string|max:255',
            'courts.*.venue_type_id' => 'required_with:courts|exists:venue_types,id',
            'courts.*.surface' => 'nullable|string|max:255',
            'courts.*.is_indoor' => 'nullable|in:0,1',
            'courts.*.time_slots' => 'nullable|array',
            'courts.*.time_slots.*.start_time' => 'required_with:courts.*.time_slots|date_format:H:i',
            'courts.*.time_slots.*.end_time' => 'required_with:courts.*.time_slots|date_format:H:i|after:courts.*.time_slots.*.start_time',
            'courts.*.time_slots.*.price' => 'required_with:courts.*.time_slots|numeric|min:0',
            
        ],[
            'name.required' => 'Không được bỏ trống'
        ]);

        // Chuẩn hoá thời gian thành HH:MM:SS (nếu cần)
        if (isset($validatedData['start_time']) && strlen($validatedData['start_time']) === 5) {
            $validatedData['start_time'] .= ':00';
        }
        if (isset($validatedData['end_time']) && strlen($validatedData['end_time']) === 5) {
            $validatedData['end_time'] .= ':00';
        }

        // Tạo transaction: Venue + Courts + Availabilities
        DB::beginTransaction();
        try {
            // Tạo Venue (chỉ lấy trường cần thiết tránh mass assignment issues)
            $venue = Venue::create([
                'name' => $validatedData['name'],
                'owner_id' => $validatedData['owner_id'],
                'province_id' => $validatedData['province_id'],
                'district_id' => $validatedData['district_id'],
                'address_detail' => $validatedData['address_detail'],
                'phone' => $validatedData['phone'] ?? null,
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
                // mặc định is_active = 0 khi tạo (nếu model có trường)
                'is_active' => 0,
            ]);

            // Nếu có courts, tạo từng court kèm availabilities
            if (!empty($validatedData['courts']) && is_array($validatedData['courts'])) {
                foreach ($validatedData['courts'] as $courtData) {
                    // Nếu thiếu name hoặc venue_type (validate đã kiểm) nhưng check an toàn
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

                    // Tạo time slots và availabilities cho 30 ngày
                    $availabilitiesToInsert = [];
                    $now = Carbon::now();

                    if (!empty($courtData['time_slots']) && is_array($courtData['time_slots'])) {
                        foreach ($courtData['time_slots'] as $timeSlotData) {
                            // Validate time slot data
                            if (empty($timeSlotData['start_time']) || empty($timeSlotData['end_time']) || empty($timeSlotData['price'])) {
                                continue;
                            }

                            // Convert time format to HH:MM:SS if needed
                            $startTime = strlen($timeSlotData['start_time']) === 5 ? $timeSlotData['start_time'] . ':00' : $timeSlotData['start_time'];
                            $endTime = strlen($timeSlotData['end_time']) === 5 ? $timeSlotData['end_time'] . ':00' : $timeSlotData['end_time'];
                            $price = (float)$timeSlotData['price'];

                            // Create time slot
                            $timeSlot = TimeSlot::create([
                                'court_id' => $court->id,
                                'start_time' => $startTime,
                                'end_time' => $endTime,
                                'label' => $startTime . ' - ' . $endTime,
                            ]);

                            // Create availabilities for 30 days
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
            return redirect()->route('admin.brand.index')->with('success', 'Đăng ký thương hiệu và sân thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Brand store error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi lưu: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị chi tiết một thương hiệu.
     */
    public function show(Venue $venue)
    {
        return view('brand.show', compact('venue'));
    }

    /**
     * Hiển thị form chỉnh sửa.
     */
    public function edit(Venue $venue)
    {
        $owners = User::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        $districts = District::where('province_id', $venue->province_id)->orderBy('name')->get();
        $venue_types = VenueType::orderBy('name')->get();

        return view('brand.edit', compact('venue', 'owners', 'provinces', 'districts', 'venue_types'));
    }

    /**
     * Cập nhật một thương hiệu trong database.
     */
    public function update(Request $request, Venue $venue)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:users,id',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'required|string',
            'phone' => ['nullable','regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-9]|9[0-9])[0-9]{7}$/'],
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

        return redirect()->route('admin.brand.index')->with('success', 'Cập nhật sân thành công!');
    }

    /**
     * Xóa một thương hiệu.
     */
    public function destroy(Venue $venue)
    {
        $venue->delete();
        return redirect()->route('admin.brand.index')->with('success', 'Xóa sân thành công!');
    }
}
