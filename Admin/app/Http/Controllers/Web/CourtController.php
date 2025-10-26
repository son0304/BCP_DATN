<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use App\Models\VenueType;
use App\Models\TimeSlot;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CourtController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role->name === 'admin') {
            $courts = Court::with(['venue', 'venueType'])->latest()->paginate(10);
        }
        else if ($user->role->name === 'venue_owner') {
            $courts = Court::with(['venue', 'venueType'])
                ->whereHas('venue', function($query) use ($user) {
                    $query->where('owner_id', $user->id);
                })
                ->latest()
                ->paginate(10);
        }
        else {
            abort(403, 'Bạn không có quyền truy cập trang này');
        }

        return view('courts.index', compact('courts'));
    }

    public function show(Court $court)
    {
        $court->load(['venue', 'venueType']);
        $availabilities = Availability::where('court_id', $court->id)
            ->where('date', '>=', Carbon::today())
            ->with('timeSlot')
            ->orderBy('date')
            ->orderBy('slot_id')
            ->get()
            ->groupBy('date');

        return view('courts.show', compact('court', 'availabilities'));
    }

    public function create()
    {
        $user = auth()->user();

        if ($user->role->name === 'admin') {
            $venues = Venue::orderBy('name')->get();
        }
        else if ($user->role->name === 'venue_owner') {
            $venues = Venue::where('owner_id', $user->id)->orderBy('name')->get();
        }
        else {
            abort(403, 'Bạn không có quyền truy cập trang này');
        }

        $venueTypes = VenueType::orderBy('name')->get();

        // Lọc trùng khung giờ theo start_time và end_time
        $timeSlots = TimeSlot::select('id', 'start_time', 'end_time')
            ->distinct()
            ->orderBy('start_time')
            ->get();

        return view('courts.create', compact('venues', 'venueTypes', 'timeSlots'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'surface' => 'nullable|string|max:255',
            'is_indoor' => 'required|boolean',

            // Dữ liệu mới từ form
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => 'required|date_format:H:i|after:time_slots.*.start_time',
            'time_slots.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $court = Court::create([
                'name' => $validatedData['name'],
                'venue_id' => $validatedData['venue_id'],
                'venue_type_id' => $validatedData['venue_type_id'],
                'surface' => $validatedData['surface'],
                'is_indoor' => $validatedData['is_indoor'],
            ]);

            $now = Carbon::now();
            $availabilitiesToInsert = [];

            foreach ($validatedData['time_slots'] as $slot) {

                // Tìm hoặc tạo slot
                $timeSlot = TimeSlot::firstOrCreate([
                    'start_time' => $slot['start_time'],
                    'end_time'   => $slot['end_time'],
                ]);

                // Tạo lịch 30 ngày
                for ($i = 0; $i < 30; $i++) {
                    $date = Carbon::today()->addDays($i)->toDateString();
                    $availabilitiesToInsert[] = [
                        'court_id' => $court->id,
                        'date' => $date,
                        'slot_id' => $timeSlot->id,
                        'price' => $slot['price'],
                        'status' => 'open',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($availabilitiesToInsert)) {
                Availability::insert($availabilitiesToInsert);
            }

            DB::commit();
            return redirect()->route('admin.courts.index')->with('success', 'Thêm sân và lịch hoạt động thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Court store error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Đã có lỗi xảy ra khi lưu sân.');
        }
    }


    public function edit(Court $court)
    {
        $venues = Venue::orderBy('name')->get();
        $venueTypes = VenueType::orderBy('name')->get();
        $timeSlots = TimeSlot::orderBy('start_time')->get();

        $currentPrices = Availability::where('court_id', $court->id)
            ->where('date', '>=', Carbon::today())
            ->with('timeSlot')
            ->get()
            ->unique('slot_id');

        $currentPricesDetailed = $currentPrices->map(function ($item) {
            return [
                'start_time' => $item->timeSlot->start_time,
                'end_time'   => $item->timeSlot->end_time,
                'price'      => $item->price,
            ];
        });

        return view('courts.edit', compact('court', 'venues', 'venueTypes', 'timeSlots', 'currentPricesDetailed'));
    }

    public function update(Request $request, Court $court)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => 'required|date_format:H:i|after:time_slots.*.start_time',
            'time_slots.*.price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($court, $request) {

            $court->update($request->only('name', 'venue_id', 'venue_type_id'));

            // Xóa lịch tương lai chưa đặt
            Availability::where('court_id', $court->id)
                ->where('status', 'open')
                ->where('date', '>=', Carbon::today())
                ->delete();

            $now = Carbon::now();
            $insert = [];

            foreach ($request->time_slots as $slot) {
                $timeSlot = TimeSlot::firstOrCreate([
                    'start_time' => $slot['start_time'],
                    'end_time'   => $slot['end_time'],
                ]);

                for ($i = 0; $i < 30; $i++) {
                    $insert[] = [
                        'court_id' => $court->id,
                        'date' => Carbon::today()->addDays($i)->toDateString(),
                        'slot_id' => $timeSlot->id,
                        'price' => $slot['price'],
                        'status' => 'open',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            Availability::insert($insert);
        });

        return redirect()->route('admin.courts.index')
            ->with('success', 'Cập nhật sân và khung giờ thành công!');
    }


    public function destroy(Court $court)
    {
        $court->delete();
        return redirect()->route('admin.courts.index')->with('success', 'Xóa sân thành công!');
    }
}
