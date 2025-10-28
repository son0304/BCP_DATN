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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CourtController extends Controller
{
    /**
     * Hiển thị danh sách sân (toàn bộ hoặc theo quyền)
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            $courts = Court::with(['venue', 'venueType'])->latest()->paginate(10);
        } elseif ($user->role->name === 'venue_owner') {
            $courts = Court::with(['venue', 'venueType'])
                ->whereHas('venue', function ($query) use ($user) {
                    $query->where('owner_id', $user->id);
                })
                ->latest()
                ->paginate(10);
        } else {
            abort(403, 'Bạn không có quyền truy cập trang này');
        }

        return view('venue_owner.courts.index', compact('courts'));
    }

    /**
     * Hiển thị danh sách sân theo venue cụ thể
     */
    public function indexByVenue(Venue $venue)
    {
        $user = Auth::user();

        if ($user->role->name !== 'admin' && $venue->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền xem sân của thương hiệu này.');
        }

        $courts = Court::with('venueType')
            ->where('venue_id', $venue->id)
            ->latest()
            ->paginate(10);

        return view('venue_owner.courts.index', compact('courts', 'venue'));
    }

    /**
     * Hiển thị chi tiết một sân
     */
    public function show(Venue $venue, Court $court)
    {
        $user = Auth::user();

        if ($court->venue_id !== $venue->id) {
            abort(404, 'Sân không thuộc thương hiệu của bạn.');
        }

        $court->load(['venue', 'venueType']);

        // Lấy danh sách lịch hoạt động
        $availabilities = Availability::where('court_id', $court->id)
            ->where('date', '>=', Carbon::today())
            ->with('timeSlot')
            ->orderBy('date')
            ->orderBy('slot_id')
            ->get()
            ->groupBy('date');

        if ($user->role->name === 'admin') {
            return view('admin.courts.show', compact('venue', 'court', 'availabilities'));
        } else {
            return view('venue_owner.courts.show', compact('venue', 'court', 'availabilities'));
        }
    }

    /**
     * Form tạo sân mới
     */
    public function create($venueId)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Bạn cần đăng nhập để truy cập.');
        }

        $venue = Venue::findOrFail($venueId);

        // Kiểm tra quyền của venue_owner
        if ($user->role->name === 'venue_owner' && $venue->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền thêm sân cho thương hiệu này.');
        }

        $venueTypes = VenueType::orderBy('name')->get();

        return view('venue_owner.courts.create', compact('venue', 'venueTypes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'surface' => 'nullable|string|max:255',
            'is_indoor' => 'required|boolean',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => 'required|date_format:H:i|after:time_slots.*.start_time',
            'time_slots.*.price' => 'required|numeric|min:0',
        ], [
            'time_slots.required' => 'Bạn phải thêm ít nhất một khung giờ.',
        ]);

        $user = Auth::user();

        // Kiểm tra quyền sở hữu với venue_owner
        if ($user->role->name === 'venue_owner') {
            $venue = Venue::findOrFail($validatedData['venue_id']);
            if ($venue->owner_id !== $user->id) {
                abort(403, 'Bạn không có quyền thêm sân cho thương hiệu này.');
            }
        }

        DB::beginTransaction();

        try {
            $court = Court::create([
                'name' => $validatedData['name'],
                'venue_id' => $validatedData['venue_id'],
                'venue_type_id' => $validatedData['venue_type_id'],
                'surface' => $validatedData['surface'] ?? null,
                'is_indoor' => $validatedData['is_indoor'],
            ]);

            $now = Carbon::now();
            $availabilitiesToInsert = [];

            foreach ($validatedData['time_slots'] as $slot) {
                $timeSlot = TimeSlot::firstOrCreate(
                    [
                        'court_id' => $court->id,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                    ],
                    [
                        'label' => date('H:i', strtotime($slot['start_time'])) . ' - ' . date('H:i', strtotime($slot['end_time']))
                    ]
                );

                for ($i = 0; $i < 30; $i++) {
                    $availabilitiesToInsert[] = [
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

            Availability::insert($availabilitiesToInsert);

            DB::commit();

            return redirect()->to('/venue/' . $court->venue_id)
                ->with('success', 'Thêm sân và tạo lịch hoạt động thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Court store error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi lưu sân: ' . $e->getMessage());
        }
    }


    /**
     * Form chỉnh sửa sân
     */

    public function edit(Venue $venue, Court $court)
    {
        $user = Auth::user();

        // Kiểm tra quyền: admin hoặc chủ venue
        if ($user->role->name !== 'admin' && $venue->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa sân này.');
        }

        $venueTypes = VenueType::orderBy('name')->get();
        $venues = Venue::orderBy('name')->get();

        $currentPricesDetailed = Availability::where('court_id', $court->id)
            ->where('date', '>=', now()->toDateString())
            ->with('timeSlot')
            ->get()
            ->groupBy('slot_id')
            ->map(fn($g) => [
                'start_time' => $g->first()->timeSlot->start_time,
                'end_time'   => $g->first()->timeSlot->end_time,
                'price'      => $g->first()->price,
            ])
            ->values()
            ->toArray();

        return view('venue_owner.courts.edit', compact('court', 'venues', 'venueTypes', 'currentPricesDetailed', 'venue'));
    }

    public function update(Request $request, Venue $venue, Court $court)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'surface' => 'nullable|string|max:255',
            'is_indoor' => 'required|boolean',
        ]);

        $user = Auth::user();
        if ($user->role->name !== 'admin' && $venue->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền cập nhật sân này.');
        }

        $court->update($validated);

        return redirect()->route('venue.courts.index', $venue->id)
            ->with('success', 'Cập nhật sân thành công!');
    }


    /**
     * Xóa sân
     */
    public function destroy(Court $court)
    {
        $user = Auth::user();
        if ($user->role->name !== 'admin' && $court->venue->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền xóa sân này.');
        }

        $court->delete();

        return redirect()->route('venue.courts.index', $court->venue_id)
            ->with('success', 'Đã xóa sân thành công.');
    }
}
