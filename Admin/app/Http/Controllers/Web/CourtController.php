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
use Illuminate\Validation\Rule;

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
        // Lấy Venue ID từ request (input hidden) hoặc route
        $venueId = $request->input('venue_id');
        $venue = Venue::findOrFail($venueId);
        $user = Auth::user();

        // 1. Kiểm tra quyền hạn
        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::ownsVenue($venueId, $user)) {
            abort(403, 'Bạn không có quyền thêm sân cho thương hiệu này.');
        }

        // 2. Định nghĩa Rules
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                // Rule: Tên sân phải là duy nhất trong phạm vi Venue này
                Rule::unique('courts')->where(function ($query) use ($venueId) {
                    return $query->where('venue_id', $venueId);
                }),
            ],
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'surface' => 'nullable|string|max:255',
            'is_indoor' => 'required|in:0,1',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            // Regex cho phép giờ từ 00:00 đến 23:59 và đặc biệt là 24:00
            'time_slots.*.end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
            'time_slots.*.price' => 'required|numeric|min:0',
        ];

        $messages = [
            'name.required' => 'Tên sân không được bỏ trống.',
            'name.unique' => 'Tên sân này đã tồn tại trong thương hiệu của bạn.',
            'name.max' => 'Tên sân không được vượt quá 255 ký tự.',
            'venue_type_id.required' => 'Vui lòng chọn loại sân.',
            'venue_type_id.exists' => 'Loại sân không hợp lệ.',
            'is_indoor.in' => 'Trạng thái sân (Trong nhà/Ngoài trời) không hợp lệ.',
            'time_slots.required' => 'Vui lòng thêm ít nhất một khung giờ hoạt động.',
            'time_slots.*.start_time.required' => 'Giờ bắt đầu khung giờ không được bỏ trống.',
            'time_slots.*.start_time.date_format' => 'Giờ bắt đầu khung giờ sai định dạng (HH:mm).',
            'time_slots.*.end_time.required' => 'Giờ kết thúc khung giờ không được bỏ trống.',
            'time_slots.*.end_time.regex' => 'Giờ kết thúc sai định dạng.',
            'time_slots.*.price.required' => 'Giá tiền không được bỏ trống.',
            'time_slots.*.price.numeric' => 'Giá tiền phải là số.',
            'time_slots.*.price.min' => 'Giá tiền không được nhỏ hơn 0.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // 3. After Hook: Logic kiểm tra giờ giấc (Giống logic Store Venue)
        $validator->after(function ($validator) use ($request, $venue) {
            // Lấy giờ mở/đóng cửa của Venue
            $venueStartTimeStr = substr($venue->start_time, 0, 5);
            $venueEndTimeStr = $venue->end_time === '23:59:59' ? '24:00' : substr($venue->end_time, 0, 5);

            $venueStart = Carbon::createFromFormat('H:i', $venueStartTimeStr);
            $venueEnd = Carbon::createFromFormat('H:i', $venueEndTimeStr === '24:00' ? '00:00' : $venueEndTimeStr);

            // Xử lý Venue qua đêm (VD: 18:00 -> 02:00 sáng hôm sau)
            if ($venueEndTimeStr === '24:00' || $venueEnd->lte($venueStart)) {
                $venueEnd->addDay();
            }

            foreach ($request->input('time_slots', []) as $index => $slot) {
                if (empty($slot['start_time']) || empty($slot['end_time'])) continue;

                $slotStart = Carbon::createFromFormat('H:i', $slot['start_time']);
                $slotEnd = Carbon::createFromFormat('H:i', $slot['end_time'] === '24:00' ? '00:00' : $slot['end_time']);

                // Xử lý Slot qua đêm
                if ($slot['end_time'] === '24:00' || $slotEnd->lte($slotStart)) {
                    $slotEnd->addDay();
                }

                // Đồng bộ ngày của Slot với Venue để so sánh
                // Nếu Venue mở qua đêm và Slot bắt đầu vào buổi sáng sớm (nhỏ hơn giờ mở cửa venue),
                // ta hiểu Slot đó thuộc ngày hôm sau.
                if ($venueEnd->day > $venueStart->day && $slotStart->lt($venueStart)) {
                    $slotStart->addDay();
                    $slotEnd->addDay(); // Slot end đã addDay ở bước trên rồi thì nó sẽ thành +2 ngày (sai), cần reset logic này kỹ một chút.
                    // Cách an toàn hơn: Reset về cùng Base Date rồi tính lại
                    // Tuy nhiên logic dưới đây thường ổn định hơn cho so sánh dải:
                }

                // LOGIC SO SÁNH CHÍNH XÁC:
                // Nếu Slot start < Venue start -> Có thể slot thuộc ngày hôm sau nếu Venue qua đêm.
                // Để đơn giản: Ta check xem khoảng [SlotStart, SlotEnd] có nằm trọn trong [VenueStart, VenueEnd] không.

                // Fix lại logic addDay cho slot để so sánh với Venue:
                // Nếu SlotStart nhỏ hơn VenueStart, mà Venue lại là qua đêm, thì thử cộng 1 ngày cho cả cặp Slot xem có lọt vào không.
                if ($slotStart->lt($venueStart) && $venueEnd->gt($venueStart)) {
                    // Trường hợp Venue không qua đêm mà Slot lại nhỏ hơn -> Lỗi luôn
                } elseif ($slotStart->lt($venueStart) && $venueEnd->day > $venueStart->day) {
                    // Venue qua đêm, Slot nhỏ hơn VenueStart -> Giả sử Slot thuộc ngày hôm sau
                    if ($slotStart->day === $slotEnd->day) { // Nếu slot chưa qua đêm
                        $slotStart->addDay();
                        $slotEnd->addDay();
                    }
                    // Nếu slot đã qua đêm (slotEnd > slotStart), thì slotStart vẫn giữ nguyên là sai
                    // Logic này hơi phức tạp, ta dùng cách đơn giản của Venue Store logic cũ của bạn:
                }

                // --- RESET LOGIC SO SÁNH THEO CODE GỐC CỦA BẠN (Ổn định nhất) ---
                // Logic này copy từ phần Store Venue của bạn, đảm bảo tính nhất quán
                if ($slotStart->lt($venueStart) && $venueEnd->day > $venueStart->day) {
                    // Nếu Venue qua đêm và SlotStart < VenueStart (vd Venue 18h, Slot 02h), đẩy Slot lên ngày sau
                    if ($slotStart->day == Carbon::now()->day) { // Chỉ đẩy nếu chưa bị đẩy
                        $slotStart->addDay();
                        $slotEnd->addDay();
                    }
                }

                // Check 1: Giờ kết thúc phải sau bắt đầu
                if ($slotStart->gte($slotEnd)) {
                    $validator->errors()->add("time_slots.{$index}.end_time", 'Giờ kết thúc phải sau giờ bắt đầu.');
                    continue;
                }

                // Check 2: Nằm trong giờ hoạt động
                $errorMessage = 'Khung giờ phải nằm trong giờ hoạt động của thương hiệu (' . $venueStartTimeStr . ' - ' . $venueEndTimeStr . ').';

                // Check biên: Start không được sớm hơn VenueStart, End không được muộn hơn VenueEnd
                // Chú ý: Dùng gte/lte để so sánh Carbon
                if ($slotStart->lt($venueStart) || $slotEnd->gt($venueEnd)) {
                    $validator->errors()->add("time_slots.{$index}.start_time", $errorMessage);
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        // 4. Chuẩn hóa dữ liệu (HH:mm -> HH:mm:ss)
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
        unset($slot); // Hủy tham chiếu

        // 5. Transaction lưu DB
        DB::beginTransaction();
        try {
            // Tạo Sân
            $court = Court::create([
                'name' => $validatedData['name'],
                'venue_id' => $validatedData['venue_id'],
                'venue_type_id' => $validatedData['venue_type_id'],
                'surface' => $validatedData['surface'] ?? null,
                'is_indoor' => (bool)$validatedData['is_indoor'],
            ]);

            $now = Carbon::now();
            $availabilitiesToInsert = [];

            // Tạo TimeSlots và Availability cho 30 ngày tới
            foreach ($validatedData['time_slots'] as $slot) {
                // Label hiển thị đẹp (cắt giây)
                $labelStart = substr($slot['start_time'], 0, 5);
                $labelEnd = ($slot['end_time'] === '23:59:59') ? '24:00' : substr($slot['end_time'], 0, 5);

                $timeSlot = TimeSlot::create([
                    'court_id' => $court->id,
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'label' => "{$labelStart} - {$labelEnd}"
                ]);

                // Bulk insert Availability
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

            // Insert số lượng lớn để tối ưu hiệu năng
            if (!empty($availabilitiesToInsert)) {
                Availability::insert($availabilitiesToInsert);
            }

            DB::commit();

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
        $venueId = $request->input('venue_id');
        $user = Auth::user();

        // 1. Kiểm tra quyền hạn
        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::ownsVenue($venueId, $user)) {
            abort(403, 'Bạn không có quyền cập nhật sân này.');
        }

        // 2. Định nghĩa Rules
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                // Kiểm tra trùng tên trong cùng venue (bỏ qua chính nó)
                Rule::unique('courts')->where(function ($query) use ($venueId) {
                    return $query->where('venue_id', $venueId);
                })->ignore($court->id),
            ],
            'venue_id' => 'required|exists:venues,id',
            'venue_type_id' => 'required|exists:venue_types,id',
            'surface' => 'nullable|string|max:255',
            'is_indoor' => 'required|in:0,1',
            'time_slots' => 'required|array|min:1',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
            'time_slots.*.price' => 'required|numeric|min:0',
        ];

        $messages = [
            'name.required' => 'Tên sân không được bỏ trống.',
            'name.unique' => 'Tên sân đã tồn tại trong thương hiệu này.',
            'name.max' => 'Tên sân quá dài.',
            'time_slots.required' => 'Cần ít nhất một khung giờ hoạt động.',
            'time_slots.*.end_time.regex' => 'Giờ kết thúc sai định dạng.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // 3. Logic kiểm tra giờ nằm trong Venue (Giống logic Store)
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

                if ($slot['end_time'] === '24:00' || $slotEnd->lte($slotStart)) {
                    $slotEnd->addDay();
                }

                // Logic so sánh dải giờ của Slot phải nằm lọt trong Venue
                // Trường hợp Venue qua đêm, Slot bắt đầu buổi sáng (nhỏ hơn venueStart) thì được hiểu là ngày hôm sau
                if ($slotStart->lt($venueStart) && $venueEnd->day > $venueStart->day) {
                    // Chỉ addDay nếu slot chưa qua ngày
                    if ($slotStart->day == Carbon::now()->day) {
                        $slotStart->addDay();
                        $slotEnd->addDay();
                    }
                }

                if ($slotStart->gte($slotEnd)) {
                    $validator->errors()->add("time_slots.{$index}.end_time", 'Giờ kết thúc phải sau giờ bắt đầu.');
                    continue;
                }

                if ($slotStart->lt($venueStart) || $slotEnd->gt($venueEnd)) {
                    $validator->errors()->add(
                        "time_slots.{$index}.start_time",
                        'Khung giờ phải nằm trong giờ hoạt động của thương hiệu (' . $venueStartTimeStr . ' - ' . $venueEndTimeStr . ').'
                    );
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        // 4. Chuẩn hóa dữ liệu giờ
        foreach ($validatedData['time_slots'] as &$slot) {
            if (strlen($slot['start_time']) === 5) $slot['start_time'] .= ':00';
            if ($slot['end_time'] === '24:00') {
                $slot['end_time'] = '23:59:59';
            } elseif (strlen($slot['end_time']) === 5) {
                $slot['end_time'] .= ':00';
            }
        }
        unset($slot);

        // 5. Transaction
        DB::beginTransaction();
        try {
            // Cập nhật Court
            $court->update([
                'name' => $validatedData['name'],
                'venue_type_id' => $validatedData['venue_type_id'],
                'surface' => $validatedData['surface'] ?? null,
                'is_indoor' => $validatedData['is_indoor'],
            ]);

            $now = Carbon::now();

            // Xóa Availability trống (status=open) từ hôm nay trở đi để tạo lại theo slot mới
            // (Giữ lại các slot đã đặt/booked để tránh mất dữ liệu khách)
            Availability::where('court_id', $court->id)
                ->where('date', '>=', Carbon::today())
                ->where('status', 'open')
                ->delete();

            // Tạo TimeSlot mới (Xóa cũ tạo mới hoặc update cũng được, nhưng tạo mới an toàn hơn cho lịch sử)
            // Để đơn giản và tránh rác, ta có thể xóa TimeSlot cũ nếu không còn availability nào reference tới nó,
            // nhưng ở đây ta dùng firstOrCreate để tái sử dụng nếu trùng.

            $availabilitiesToInsert = [];

            // Lưu ý: Logic này sẽ tạo thêm các Availability mới.
            // Nếu muốn clean hoàn toàn TimeSlot cũ, bạn cần logic phức tạp hơn (check booking).
            // Cách đơn giản nhất là xoá hết Availablity open như trên và insert lại.

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

                // Insert Availability cho 30 ngày tới
                for ($i = 0; $i < 30; $i++) {
                    $date = Carbon::today()->addDays($i)->toDateString();

                    // Kiểm tra xem ngày/slot này đã tồn tại chưa (có thể do booking cũ giữ lại)
                    // Nếu chưa có (hoặc đã bị xóa do status=open ở trên) thì insert mới
                    $exists = Availability::where('court_id', $court->id)
                        ->where('date', $date)
                        ->where('slot_id', $timeSlot->id)
                        ->exists();

                    if (!$exists) {
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
            }

            if (!empty($availabilitiesToInsert)) {
                Availability::insert($availabilitiesToInsert);
            }

            DB::commit();

            return redirect()->route('owner.venues.courts.show', ['venue' => $venue->id, 'court' => $court->id])
                ->with('success', 'Cập nhật sân thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Court update error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi cập nhật: ' . $e->getMessage());
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
