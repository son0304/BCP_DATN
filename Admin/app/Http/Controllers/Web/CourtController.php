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
use Illuminate\Support\Facades\Validator; // Thêm Validator
use App\Helpers\PermissionHelper; // Thêm PermissionHelper

class CourtController extends Controller
{
    /**
     * Hiển thị danh sách sân (toàn bộ hoặc theo quyền)
     */
    public function index()
    {
        $user = Auth::user();

        if (PermissionHelper::isAdmin($user)) {
            $courts = Court::with(['venue', 'venueType'])->latest()->paginate(10);
            return view('admin.courts.index', compact('courts'));
        } elseif (PermissionHelper::isVenueOwner($user)) {
            $courts = Court::with(['venue', 'venueType'])
                ->whereHas('venue', function ($query) use ($user) {
                    $query->where('owner_id', $user->id);
                })
                ->latest()
                ->paginate(10);
            return view('venue_owner.courts.index', compact('courts'));
        } else {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
    }

    /**
     * Hiển thị danh sách sân theo venue cụ thể
     */
    public function indexByVenue(Venue $venue)
    {
        $user = Auth::user();

        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::ownsVenue($venue->id, $user)) {
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
            abort(404, 'Sân không thuộc thương hiệu này.');
        }

        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::ownsVenue($venue->id, $user)) {
            abort(403, 'Bạn không có quyền xem sân này.');
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

        if (PermissionHelper::isAdmin($user)) {
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

        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::ownsVenue($venue->id, $user)) {
            abort(403, 'Bạn không có quyền thêm sân cho thương hiệu này.');
        }

        $venueTypes = VenueType::orderBy('name')->get();

        return view('venue_owner.courts.create', compact('venue', 'venueTypes'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'surface' => 'nullable|string|max:255',
            'is_indoor' => 'required|boolean',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
            'time_slots.*.price' => 'required|numeric|min:0',
        ];

        $messages = [
            'name.required' => 'Tên sân không được bỏ trống.',
            'name.max' => 'Tên sân không được vượt quá 255 ký tự.',
            'venue_type_id.required' => 'Vui lòng chọn loại sân.',
            'venue_type_id.exists' => 'Loại sân không hợp lệ.',
            'is_indoor.in' => 'Trạng thái sân (Trong nhà/Ngoài trời) không hợp lệ.',
            'time_slots.*.start_time.required' => 'Giờ bắt đầu khung giờ không được bỏ trống.',
            'time_slots.*.start_time.date_format' => 'Giờ bắt đầu khung giờ sai định dạng.',
            'time_slots.*.end_time.required' => 'Giờ kết thúc khung giờ không được bỏ trống.',
            'time_slots.*.price.required' => 'Giá tiền không được bỏ trống.',
            'time_slots.*.price.numeric' => 'Giá tiền phải là một số.',
            'time_slots.*.price.min' => 'Giá tiền không được nhỏ hơn 0.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $user = Auth::user();
        $venueId = $request->input('venue_id');
        $venue = Venue::findOrFail($venueId);

        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::ownsVenue($venueId, $user)) {
            abort(403, 'Bạn không có quyền thêm sân cho thương hiệu này.');
        }

        $validator->after(function ($validator) use ($request, $venue) {
            $venueStartTimeStr = substr($venue->start_time, 0, 5);
            $venueEndTimeStr = $venue->end_time === '23:59:59' ? '24:00' : substr($venue->end_time, 0, 5);

            $venueStart = Carbon::createFromFormat('H:i', $venueStartTimeStr);
            $venueEnd = Carbon::createFromFormat('H:i', $venueEndTimeStr === '24:00' ? '00:00' : $venueEndTimeStr);

            if ($venueEndTimeStr === '24:00' || $venueEnd->lte($venueStart)) {
                $venueEnd->addDay();
            }

            foreach ($request->input('time_slots', []) as $index => $slot) {
                if (empty($slot['start_time']) || empty($slot['end_time'])) continue;

                $slotStart = Carbon::createFromFormat('H:i', $slot['start_time']);
                $slotEnd = Carbon::createFromFormat('H:i', $slot['end_time'] === '24:00' ? '00:00' : $slot['end_time']);

                // Xử lý slot qua đêm
                if ($slot['end_time'] === '24:00' || $slotEnd->lte($slotStart)) {
                    $slotEnd->addDay();
                }

                // Điều chỉnh ngày của slot để so sánh với VenueStart/VenueEnd
                if ($slotStart->lt($venueStart) && $slotStart->day === $venueStart->day) {
                    $slotStart->addDay();
                    $slotEnd->addDay();
                }
                // Đảm bảo slot không bắt đầu sau khi Venue đã đóng (trừ trường hợp qua đêm)
                // và không kết thúc sau khi Venue đã đóng
                if ($slotStart->gte($slotEnd)) {
                    $validator->errors()->add("time_slots.{$index}.end_time", 'Giờ kết thúc phải sau giờ bắt đầu.');
                    continue;
                }

                $errorMessage = 'Khung giờ phải nằm trong giờ hoạt động của thương hiệu (' . $venueStartTimeStr . ' - ' . $venueEndTimeStr . ').';

                if ($slotStart->lt($venueStart) || $slotEnd->gt($venueEnd)) {
                    $validator->errors()->add("time_slots.{$index}.start_time", $errorMessage);
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        // Chuẩn hóa định dạng giờ (HH:mm:ss và 23:59:59 cho 24:00)
        foreach ($validatedData['time_slots'] as &$slot) {
            if (strlen($slot['start_time']) === 5) {
                $slot['start_time'] .= ':00';
            }

            if ($slot['end_time'] === '24:00') {
                $slot['end_time'] = '23:59:59';
            } elseif (strlen($slot['end_time']) === 5) {
                $slot['end_time'] .= ':00';
            }
        }
        unset($slot);


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
                        // Label không dùng HH:mm:ss, dùng H:i như người dùng nhập
                        'label' => substr($slot['start_time'], 0, 5) . ' - ' . ($slot['end_time'] === '23:59:59' ? '24:00' : substr($slot['end_time'], 0, 5))
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

            // Chuyển hướng sử dụng route name như VenueController
            return redirect()->route('owner.venues.show', ['venue' => $court->venue_id])
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

        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::ownsVenue($venue->id, $user)) {
            abort(403, 'Bạn không có quyền chỉnh sửa sân này.');
        }

        $venueTypes = VenueType::orderBy('name')->get();
        // Chỉ cần lấy tất cả venues nếu là admin, nếu không thì chỉ venue hiện tại
        $venues = PermissionHelper::isAdmin($user) ? Venue::orderBy('name')->get() : collect([$venue]);


        $currentPricesDetailed = Availability::where('court_id', $court->id)
            ->where('date', '>=', now()->toDateString())
            ->with('timeSlot')
            ->get()
            ->groupBy('slot_id')
            ->map(fn($g) => [
                'start_time' => substr($g->first()->timeSlot->start_time, 0, 5),
                // Chuyển 23:59:59 thành 24:00 nếu cần hiển thị
                'end_time'   => $g->first()->timeSlot->end_time === '23:59:59' ? '24:00' : substr($g->first()->timeSlot->end_time, 0, 5),
                'price'      => $g->first()->price,
            ])
            ->values()
            ->toArray();

        return view('venue_owner.courts.edit', compact('court', 'venues', 'venueTypes', 'currentPricesDetailed', 'venue'));
    }

    public function update(Request $request, Venue $venue, Court $court)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'surface' => 'nullable|string|max:255',
            'is_indoor' => 'required|boolean',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
            'time_slots.*.price' => 'required|numeric|min:0',
        ];

        $messages = [
            'name.required' => 'Tên sân không được bỏ trống.',
            'name.max' => 'Tên sân không được vượt quá 255 ký tự.',
            'venue_type_id.required' => 'Vui lòng chọn loại sân.',
            'venue_type_id.exists' => 'Loại sân không hợp lệ.',
            'is_indoor.in' => 'Trạng thái sân (Trong nhà/Ngoài trời) không hợp lệ.',
            'time_slots.*.start_time.required' => 'Giờ bắt đầu khung giờ không được bỏ trống.',
            'time_slots.*.start_time.date_format' => 'Giờ bắt đầu khung giờ sai định dạng.',
            'time_slots.*.end_time.required' => 'Giờ kết thúc khung giờ không được bỏ trống.',
            'time_slots.*.price.required' => 'Giá tiền không được bỏ trống.',
            'time_slots.*.price.numeric' => 'Giá tiền phải là một số.',
            'time_slots.*.price.min' => 'Giá tiền không được nhỏ hơn 0.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $user = Auth::user();
        $newVenueId = $request->input('venue_id');
        $newVenue = Venue::findOrFail($newVenueId);

        // Kiểm tra quyền: Admin hoặc Owner của Venue hiện tại/mới
        if (!PermissionHelper::isAdmin($user) && (!PermissionHelper::ownsVenue($venue->id, $user) || !PermissionHelper::ownsVenue($newVenueId, $user))) {
            abort(403, 'Bạn không có quyền cập nhật sân này.');
        }

        // Logic kiểm tra Time Slot phải nằm trong giới hạn của Venue
        $validator->after(function ($validator) use ($request, $newVenue) {
            $venueStartTimeStr = substr($newVenue->start_time, 0, 5);
            $venueEndTimeStr = $newVenue->end_time === '23:59:59' ? '24:00' : substr($newVenue->end_time, 0, 5);

            $venueStart = Carbon::createFromFormat('H:i', $venueStartTimeStr);
            $venueEnd = Carbon::createFromFormat('H:i', $venueEndTimeStr === '24:00' ? '00:00' : $venueEndTimeStr);

            if ($venueEndTimeStr === '24:00' || $venueEnd->lte($venueStart)) {
                $venueEnd->addDay();
            }

            foreach ($request->input('time_slots', []) as $index => $slot) {
                if (empty($slot['start_time']) || empty($slot['end_time'])) continue;

                $slotStart = Carbon::createFromFormat('H:i', $slot['start_time']);
                $slotEnd = Carbon::createFromFormat('H:i', $slot['end_time'] === '24:00' ? '00:00' : $slot['end_time']);

                // Xử lý slot qua đêm
                if ($slot['end_time'] === '24:00' || $slotEnd->lte($slotStart)) {
                    $slotEnd->addDay();
                }

                // Điều chỉnh ngày của slot để so sánh với VenueStart/VenueEnd
                if ($slotStart->lt($venueStart) && $slotStart->day === $venueStart->day) {
                    $slotStart->addDay();
                    $slotEnd->addDay();
                }

                if ($slotStart->gte($slotEnd)) {
                    $validator->errors()->add("time_slots.{$index}.end_time", 'Giờ kết thúc phải sau giờ bắt đầu.');
                    continue;
                }

                $errorMessage = 'Khung giờ phải nằm trong giờ hoạt động của thương hiệu (' . $venueStartTimeStr . ' - ' . $venueEndTimeStr . ').';

                if ($slotStart->lt($venueStart) || $slotEnd->gt($venueEnd)) {
                    $validator->errors()->add("time_slots.{$index}.start_time", $errorMessage);
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        // Chuẩn hóa định dạng giờ (HH:mm:ss và 23:59:59 cho 24:00)
        foreach ($validatedData['time_slots'] as &$slot) {
            if (strlen($slot['start_time']) === 5) {
                $slot['start_time'] .= ':00';
            }

            if ($slot['end_time'] === '24:00') {
                $slot['end_time'] = '23:59:59';
            } elseif (strlen($slot['end_time']) === 5) {
                $slot['end_time'] .= ':00';
            }
        }
        unset($slot);


        DB::beginTransaction();
        try {
            // 1. Cập nhật thông tin cơ bản sân
            $court->update([
                'name' => $validatedData['name'],
                'venue_id' => $validatedData['venue_id'],
                'venue_type_id' => $validatedData['venue_type_id'],
                'surface' => $validatedData['surface'] ?? null,
                'is_indoor' => $validatedData['is_indoor'],
            ]);

            $now = Carbon::now();

            // 2. Xóa các Availability chưa được đặt (status = 'open') từ hôm nay trở đi
            Availability::where('court_id', $court->id)
                ->where('date', '>=', Carbon::today())
                ->where('status', 'open')
                ->delete();

            // 3. Thêm TimeSlot mới (firstOrCreate) và tạo Availability 30 ngày
            $availabilitiesToInsert = [];
            foreach ($validatedData['time_slots'] as $slot) {
                $timeSlot = TimeSlot::firstOrCreate(
                    [
                        'court_id' => $court->id,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                    ],
                    [
                        'label' => substr($slot['start_time'], 0, 5) . ' - ' . ($slot['end_time'] === '23:59:59' ? '24:00' : substr($slot['end_time'], 0, 5))
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

            return redirect()->route('owner.venues.courts.show', [
                'venue' => $validatedData['venue_id'], // Sử dụng venue_id mới nếu có thay đổi
                'court' => $court->id,
            ])->with('success', 'Cập nhật sân và khung giờ thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Court update error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra khi cập nhật sân: ' . $e->getMessage());
        }
    }

    /**
     * Xóa sân
     */
    public function destroy(Court $court)
    {
        $user = Auth::user();

        // Load venue để kiểm tra quyền
        $venue = $court->venue;

        if (!PermissionHelper::ownsVenue($venue->id, $user)) {
            abort(403, 'Bạn không có quyền xóa sân này.');
        }

        DB::beginTransaction();
        try {
            $venueId = $venue->id;

            // Xóa sân sẽ tự động xóa các TimeSlot và Availability (nếu có foreign key cascade)
            $court->delete();

            DB::commit();

            // Chuyển hướng sử dụng route name như VenueController
            return redirect()->route('owner.venues.show', ['venue' => $venueId])
                ->with('success', 'Đã xóa sân thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Court destroy error: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi xóa sân: ' . $e->getMessage());
        }
    }
}
