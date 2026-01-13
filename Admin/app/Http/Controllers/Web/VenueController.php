<?php

namespace App\Http\Controllers\Web;

use App\Events\DataCreated;
use App\Events\DataDeleted;
use App\Events\DataUpdated;
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
use App\Mail\Confirm_Merchant;
use App\Mail\Reject_Merchant;
use App\Mail\Reject_Venue;
use App\Mail\VenueApprovedMail;
use App\Models\MerchantProfile;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VenueController extends Controller
{

    protected $nameChannel = 'venues';
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
        // 1. Load các quan hệ
        $venue->load([
            'owner.merchantProfile', // Đảm bảo đã load cái này
            'province',
            'district',
            'courts',
            'images',
            'services.images'
        ]);

        $user = Auth::user();

        // 2. Kiểm tra quyền
        if ($user->role->name !== 'admin' && $user->id !== $venue->owner_id) {
            abort(403);
        }

        // 3. LẤY HỒ SƠ MERCHANT RA BIẾN RIÊNG
        $merchant_profile = $venue->owner->merchantProfile ?? null;

        // 4. TRUYỀN THÊM BIẾN VÀO COMPACT
        if ($user->role->name === 'admin') {
            return view('admin.venue.show', compact('venue', 'merchant_profile'));
        } else {
            return view('venue_owner.venue.show', compact('venue', 'merchant_profile'));
        }
    }


    public function updateStatus(Request $request, Venue $venue)
    {
        // Kiểm tra quyền admin
        if (!PermissionHelper::isAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $validatedData = $request->validate([
            'is_active'  => 'required|in:0,1',
            'admin_note' => 'nullable|string',
        ]);

        // Cập nhật trạng thái
        $venue->update(['is_active' => $validatedData['is_active']]);
        $user = $venue->owner;

        // Lấy link Frontend để đối tác truy cập quản lý
        $urlWebAdmin = config('app.url_web_admin');

        if ($validatedData['is_active'] == 1) {
            // --- KÍCH HOẠT ---
            $venue->update([
                'is_active'  => 1,
                'admin_note' => null
            ]);

            if ($user) {
                // Gửi link Client để họ vào quản lý sân
                Mail::to($user->email)->send(new VenueApprovedMail($user, $urlWebAdmin));
            }
        } else {
            // --- TỪ CHỐI / TẠM DỪNG ---
            $venue->update([
                'is_active'  => 0,
                'admin_note' => $validatedData['admin_note'] ?? null
            ]);

            if ($user) {
                Mail::to($user->email)->send(new Reject_Venue($user, $venue, $urlWebAdmin));
            }
        }

        return redirect()->back()->with('success', $validatedData['is_active'] == 1 ? 'Đã kích hoạt!' : 'Đã cập nhật trạng thái.');
    }

    public function updateMerchant(Request $request, $id)
    {
        if (!PermissionHelper::isAdmin(Auth::user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }
        $venue = Venue::findOrFail($id);
        $merchant = $venue->owner->merchantProfile;
        $user = $venue->owner;

        // Lấy link Frontend
        $urlWebClient = config('app.url_web_client');

        $validatedData = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string',
        ]);

        if ($validatedData['status'] == 'approved') {
            $merchant->update(['status' => 'approved', 'admin_note' => null]);

            if ($user && $user->role_id != 1 && $user->role_id != 2) {
                $user->role_id = 2;
                $user->save();
            }
            if ($user) {
                $user->update(['role_id' => 2]);

                Mail::to($user->email)->send(new Confirm_Merchant($user, $merchant, $urlWebClient));
            }
        } elseif ($validatedData['status'] == 'rejected') {
            $merchant->update([
                'status' => 'rejected',
                'admin_note' => $validatedData['admin_note'] ?? null,
            ]);

            if ($user) {
                // Gửi mail Reject
                Mail::to($user->email)->send(new Reject_Merchant($user, $merchant, $urlWebClient));
            }
        }

        return back()->with('success', 'Đã cập nhật trạng thái hồ sơ.');
    }


    //==============Venue_Owner=================//

    public function create()
    {
        $user = Auth::user();

        // Kiểm tra quyền (Tuỳ logic dự án của bạn)
        // if (!PermissionHelper::isVenueOwner($user) && !PermissionHelper::isAdmin($user)) { abort(403); }

        $owners = [];
        if (isset($user->role) && $user->role->name === 'admin') {
            $owners = User::whereHas('role', function ($q) {
                $q->where('name', 'owner');
            })->get();
        }

        $venue_types = VenueType::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();

        // --- QUAN TRỌNG: Lấy toàn bộ Quận/Huyện để lọc phía Client ---
        $allDistricts = District::select('id', 'name', 'province_id')->orderBy('name')->get();

        return view('venue_owner.venue.create', compact('owners', 'venue_types', 'provinces', 'allDistricts'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Định nghĩa luật Validate
        $rules = [
            'name' => 'required|string|max:255',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'address_detail' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'start_time' => 'required|date_format:H:i',
            'end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
            'venue_types' => 'required|array|min:1',
            'phone' => 'nullable|string',

            // Validate Sân con
            'courts' => 'required|array|min:1',
            'courts.*.name' => 'required|string|max:255',
            'courts.*.venue_type_id' => 'required|exists:venue_types,id',
            'courts.*.is_indoor' => 'required|in:0,1',

            // Validate Khung giờ lồng trong sân con
            'courts.*.time_slots' => 'required|array|min:1',
            'courts.*.time_slots.*.start_time' => 'required|date_format:H:i',
            'courts.*.time_slots.*.end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
            'courts.*.time_slots.*.price' => 'required|numeric|min:0',

            // Ảnh
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'document_images' => 'required|array|min:1',
            'document_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ];

        if ($user->role->name === 'admin') {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules);

        // 2. Logic so sánh khung giờ của Sân Con phải nằm trong giờ của Venue
        $validator->after(function ($validator) use ($request) {
            $vStartStr = $request->input('start_time');
            $vEndStr = $request->input('end_time');
            if (!$vStartStr || !$vEndStr) return;

            // Chuẩn hóa giờ Venue
            $venueStart = Carbon::createFromFormat('H:i', $vStartStr);
            $venueEnd = Carbon::createFromFormat('H:i', $vEndStr === '24:00' ? '00:00' : $vEndStr);
            if ($vEndStr === '24:00' || $venueEnd->lte($venueStart)) $venueEnd->addDay();

            foreach ($request->input('courts', []) as $cIdx => $court) {
                foreach ($court['time_slots'] ?? [] as $sIdx => $slot) {
                    if (empty($slot['start_time']) || empty($slot['end_time'])) continue;

                    $slotStart = Carbon::createFromFormat('H:i', $slot['start_time']);
                    $slotEnd = Carbon::createFromFormat('H:i', $slot['end_time'] === '24:00' ? '00:00' : $slot['end_time']);
                    if ($slot['end_time'] === '24:00' || $slotEnd->lte($slotStart)) $slotEnd->addDay();

                    // Kiểm tra slot nằm trong venue
                    if ($slotStart->lt($venueStart) || $slotEnd->gt($venueEnd)) {
                        $validator->errors()->add("courts.{$cIdx}.time_slots.{$sIdx}.start_time", "Khung giờ phải nằm trong khoảng hoạt động ($vStartStr - $vEndStr)");
                    }
                }
            }
        });

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        // 3. Thực hiện Transaction lưu dữ liệu
        DB::beginTransaction();
        try {
            $owner_id = ($user->role->name === 'admin') ? $request->owner_id : $user->id;

            // B1: Tạo Venue
            $venue = Venue::create([
                'name' => $request->name,
                'owner_id' => $owner_id,
                'province_id' => $request->province_id,
                'district_id' => $request->district_id,
                'address_detail' => $request->address_detail,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'phone' => $request->phone,
                'start_time' => $request->start_time . ':00',
                'end_time' => $request->end_time === '24:00' ? '23:59:59' : $request->end_time . ':00',
                'is_active' => 0,
            ]);

            $venue->venueTypes()->attach($request->venue_types);

            // B2: Lưu ảnh (Sân & Giấy tờ)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $idx => $file) {
                    $path = $file->store('uploads/venues', 'public');
                    $venue->images()->create([
                        'url' => 'storage/' . $path,
                        'is_primary' => ($idx == $request->input('primary_image_index', 0)),
                        'type' => 'venue'
                    ]);
                }
            }
            if ($request->hasFile('document_images')) {
                foreach ($request->file('document_images') as $file) {
                    $path = $file->store('uploads/documents', 'public');
                    $venue->images()->create(['url' => 'storage/' . $path, 'is_primary' => 0, 'type' => 'document']);
                }
            }

            // B3: Tạo Sân con, TimeSlots và Availability 30 ngày
            foreach ($request->input('courts') as $courtData) {
                $court = Court::create([
                    'venue_id' => $venue->id,
                    'name' => $courtData['name'],
                    'venue_type_id' => $courtData['venue_type_id'],
                    'surface' => $courtData['surface'] ?? null,
                    'is_indoor' => $courtData['is_indoor'],
                ]);

                $availabilityToInsert = [];
                foreach ($courtData['time_slots'] as $slot) {
                    $startTime = $slot['start_time'] . ':00';
                    $endTime = ($slot['end_time'] === '24:00') ? '23:59:59' : $slot['end_time'] . ':00';

                    $timeSlot = TimeSlot::create([
                        'court_id' => $court->id,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'label' => $slot['start_time'] . ' - ' . $slot['end_time']
                    ]);

                    // Tạo Availability cho 30 ngày tới
                    for ($i = 0; $i < 30; $i++) {
                        $availabilityToInsert[] = [
                            'court_id' => $court->id,
                            'slot_id' => $timeSlot->id,
                            'date' => Carbon::today()->addDays($i)->toDateString(),
                            'price' => $slot['price'],
                            'status' => 'open',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (!empty($availabilityToInsert)) {
                    Availability::insert($availabilityToInsert);
                }
            }

            DB::commit();
            $route = ($user->role->name === 'admin') ? 'admin.venues.index' : 'owner.venues.index';
            return redirect()->route($route)->with('success', 'Đăng ký thương hiệu và hệ thống sân thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
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
        $venue->load('images');
        return view('venue_owner.venue.edit', compact('venue', 'owners', 'provinces', 'districts', 'venue_types'));
    }

    public function update(Request $request, $id)
    {
        $venue = Venue::findOrFail($id);
        $user = Auth::user();

        if (!PermissionHelper::isAdmin($user) && !PermissionHelper::ownsVenue($venue->id, $user)) {
            abort(403, 'Bạn không có quyền chỉnh sửa.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'province_id' => 'required',
            'district_id' => 'required',
            'address_detail' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'venue_types' => 'required|array',
            'start_time' => 'required',
            'end_time' => 'required',

            // 1. Quy tắc cho Ảnh Sân (Venue)
            'new_files.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_links.*' => 'url',
            'primary_image_index' => 'required',

            // 2. Quy tắc cho Ảnh Giấy Tờ (Document) - MỚI
            'new_document_files.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ];

        if (PermissionHelper::isAdmin($user)) {
            $rules['owner_id'] = 'required|exists:users,id';
        }

        $validator = Validator::make($request->all(), $rules);

        // After Hook: Kiểm tra số lượng ảnh sân (không cho phép > 5)
        $validator->after(function ($validator) use ($request, $venue) {
            $deletedVenueIds = array_filter(explode(',', $request->input('deleted_image_ids', '')));
            $remainingVenue = $venue->images()->where('type', 'venue')->whereNotIn('id', $deletedVenueIds)->count();
            $newCount = ($request->file('new_files') ? count($request->file('new_files')) : 0) + ($request->input('image_links') ? count($request->input('image_links')) : 0);

            if (($remainingVenue + $newCount) === 0) $validator->errors()->add('images', 'Phải có ít nhất 1 ảnh sân.');
            if (($remainingVenue + $newCount) > 5) $validator->errors()->add('images', 'Ảnh sân không được quá 5.');
        });

        if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput();

        DB::beginTransaction();
        try {
            $venue->update($request->only(['name', 'owner_id', 'province_id', 'district_id', 'address_detail', 'lat', 'lng', 'phone', 'start_time', 'end_time']));
            $venue->venueTypes()->sync($request->venue_types);

            // --- XỬ LÝ ẢNH SÂN (TYPE: VENUE) ---
            $deletedVenueIds = array_filter(explode(',', $request->input('deleted_image_ids', '')));
            if (!empty($deletedVenueIds)) {
                $imgs = $venue->images()->whereIn('id', $deletedVenueIds)->get();
                foreach ($imgs as $img) {
                    if (!filter_var($img->url, FILTER_VALIDATE_URL)) Storage::disk('public')->delete(str_replace('storage/', '', $img->url));
                    $img->delete();
                }
            }

            $newVenueMap = [];
            if ($request->hasFile('new_files')) {
                foreach ($request->file('new_files') as $idx => $file) {
                    $path = $file->store('uploads/venues', 'public');
                    $newVenueMap["new_file_{$idx}"] = $venue->images()->create(['url' => 'storage/' . $path, 'type' => 'venue', 'is_primary' => false]);
                }
            }
            if ($request->has('image_links')) {
                foreach ($request->input('image_links') as $idx => $link) {
                    if ($link) $newVenueMap["new_link_{$idx}"] = $venue->images()->create(['url' => $link, 'type' => 'venue', 'is_primary' => false]);
                }
            }

            // Set Primary cho ảnh Sân
            $venue->images()->where('type', 'venue')->update(['is_primary' => false]);
            $pKey = $request->primary_image_index;
            if (str_starts_with($pKey, 'existing_')) {
                $venue->images()->where('id', str_replace('existing_', '', $pKey))->update(['is_primary' => true]);
            } elseif (isset($newVenueMap[$pKey])) {
                $newVenueMap[$pKey]->update(['is_primary' => true]);
            }

            // --- XỬ LÝ GIẤY TỜ (TYPE: DOCUMENT) - MỚI ---
            $deletedDocIds = array_filter(explode(',', $request->input('deleted_document_ids', '')));
            if (!empty($deletedDocIds)) {
                $docs = $venue->images()->whereIn('id', $deletedDocIds)->get();
                foreach ($docs as $doc) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $doc->url));
                    $doc->delete();
                }
            }
            if ($request->hasFile('new_document_files')) {
                foreach ($request->file('new_document_files') as $file) {
                    $path = $file->store('uploads/documents', 'public');
                    $venue->images()->create(['url' => 'storage/' . $path, 'type' => 'document', 'is_primary' => false]);
                }
            }

            DB::commit();
            return redirect()->route('owner.venues.index')->with('success', 'Cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
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
            $venueId = $venue->id;

            $venue->delete();

            DB::commit();
            broadcast(new DataDeleted($venueId, $this->nameChannel, 'venue.deleted'));
            return redirect()->route('owner.venues.index')->with('success', 'Xóa sân thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Venue delete error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xóa: ' . $e->getMessage());
        }
    }
}