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
        $courts = Court::with(['venue', 'venueType'])->latest()->paginate(10);
        return view('courts.index', compact('courts'));
    }

    // public function show(Court $court)
    // {
    //     $court->load(['venue', 'venueType']);
    //     $availabilities = Availability::where('court_id', $court->id)
    //         ->where('date', '>=', Carbon::today())
    //         ->with('timeSlot')
    //         ->orderBy('date')
    //         ->orderBy('slot_id')
    //         ->get()
    //         ->groupBy('date');

    //     return view('courts.show', compact('court', 'availabilities'));
    // }

    // public function create()
    // {
    //     $venues = Venue::orderBy('name')->get();
    //     $venueTypes = VenueType::orderBy('name')->get();
    //     $timeSlots = TimeSlot::orderBy('start_time')->get();

    //     return view('courts.create', compact('venues', 'venueTypes', 'timeSlots'));
    // }


    // public function store(Request $request)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'name' => 'required|string|max:255',
    //             'venue_id' => 'required|exists:venues,id',
    //             'venue_type_id' => 'required|exists:venue_types,id',
    //             'surface' => 'nullable|string|max:255',
    //             'is_indoor' => 'required|boolean',
    //             'slot_ids' => 'required|array|min:1',
    //             'slot_ids.*' => 'required|exists:time_slots,id',
    //             'slot_prices' => 'required|array|min:1',
    //             'slot_prices.*' => 'required|numeric|min:0',
    //         ]);

    //         if (count($validatedData['slot_ids']) !== count($validatedData['slot_prices'])) {
    //             return back()->withInput()->with('error', 'Dữ liệu khung giờ và giá không khớp.');
    //         }

    //         DB::beginTransaction();
    //         try {
    //             $court = Court::create([
    //                 'name' => $validatedData['name'],
    //                 'venue_id' => $validatedData['venue_id'],
    //                 'venue_type_id' => $validatedData['venue_type_id'],
    //                 'surface' => $validatedData['surface'],
    //                 'is_indoor' => $validatedData['is_indoor'],
    //             ]);

    //             $availabilitiesToInsert = [];
    //             $now = Carbon::now();

    //             foreach ($validatedData['slot_ids'] as $index => $slotId) {
    //                 $price = $validatedData['slot_prices'][$index];
    //                 for ($i = 0; $i < 30; $i++) {
    //                     $date = Carbon::today()->addDays($i)->toDateString();
    //                     $availabilitiesToInsert[] = [
    //                         'court_id' => $court->id,
    //                         'date' => $date,
    //                         'slot_id' => $slotId,
    //                         'price' => $price,
    //                         'status' => 'open',
    //                         'created_at' => $now,
    //                         'updated_at' => $now,
    //                     ];
    //                 }
    //             }

    //             if (!empty($availabilitiesToInsert)) {
    //                 Availability::insert($availabilitiesToInsert);
    //             }

    //             DB::commit();
    //             return redirect()->route('admin.courts.index')->with('success', 'Thêm sân và tạo lịch hoạt động thành công!');
    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             Log::error('Court store error: ' . $e->getMessage());
    //             return redirect()->back()->withInput()->with('error', 'Đã có lỗi xảy ra: ' . $e->getMessage());
    //         }
    //     } catch (\Exception $e) {
    //         return redirect()->back()->withInput()->with('error', 'Lỗi không xác định: ' . $e->getMessage());
    //     }
    // }

    // public function edit(Court $court)
    // {
    //     $venues = Venue::orderBy('name')->get();
    //     $venueTypes = VenueType::orderBy('name')->get();
    //     $timeSlots = TimeSlot::orderBy('start_time')->get();
    //     $currentPrices = Availability::where('court_id', $court->id)
    //         ->where('date', '>=', Carbon::today())
    //         ->orderBy('date', 'asc')
    //         ->get()
    //         ->unique('slot_id')
    //         ->pluck('price', 'slot_id');

    //     Log::info('Edit court data:', [
    //         'court_id' => $court->id,
    //         'venues_count' => $venues->count(),
    //         'venue_types_count' => $venueTypes->count(),
    //         'time_slots_count' => $timeSlots->count(),
    //         'current_prices_count' => $currentPrices->count()
    //     ]);

    //     return view('courts.edit', compact('court', 'venues', 'venueTypes', 'timeSlots', 'currentPrices'));
    // }
    // public function update(Request $request, Court $court)
    // {
    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'venue_id' => 'required|exists:venues,id',
    //         'venue_type_id' => 'required|exists:venue_types,id',
    //         'surface' => 'nullable|string|max:255',
    //         'is_indoor' => 'required|boolean',
    //         'slot_ids' => 'nullable|array',
    //         'slot_ids.*' => 'required|exists:time_slots,id',
    //         'slot_prices' => 'nullable|array',
    //         'slot_prices.*' => 'required|numeric|min:0',
    //     ]);

    //     DB::beginTransaction();
    //     try {

    //         $court->update([
    //             'name' => $validatedData['name'],
    //             'venue_id' => $validatedData['venue_id'],
    //             'venue_type_id' => $validatedData['venue_type_id'],
    //             'surface' => $validatedData['surface'],
    //             'is_indoor' => $validatedData['is_indoor'],
    //         ]);
    //         Availability::where('court_id', $court->id)
    //             ->where('date', '>=', Carbon::today())
    //             ->where('status', 'open')
    //             ->delete();
    //         if (!empty($validatedData['slot_ids'])) {
    //             $availabilitiesToInsert = [];
    //             $now = Carbon::now();
    //             foreach ($validatedData['slot_ids'] as $index => $slotId) {
    //                 $price = $validatedData['slot_prices'][$index];
    //                 for ($i = 0; $i < 30; $i++) {
    //                     $date = Carbon::today()->addDays($i)->toDateString();
    //                     $availabilitiesToInsert[] = [
    //                         'court_id' => $court->id,
    //                         'date' => $date,
    //                         'slot_id' => $slotId,
    //                         'price' => $price,
    //                         'status' => 'open',
    //                         'created_at' => $now,
    //                         'updated_at' => $now,
    //                     ];
    //                 }
    //             }
    //             if (!empty($availabilitiesToInsert)) {
    //                 Availability::insert($availabilitiesToInsert);
    //             }
    //         }
    //         DB::commit();
    //         return redirect()->route('admin.courts.index')->with('success', 'Cập nhật sân và lịch hoạt động thành công!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật.');
    //     }
    // }
    // public function destroy(Court $court)
    // {
    //     $court->delete();
    //     return redirect()->route('admin.courts.index')->with('success', 'Xóa sân thành công!');
    // }
}