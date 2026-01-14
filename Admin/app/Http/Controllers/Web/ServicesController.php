<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Venue;
use App\Models\VenueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServicesController extends Controller
{
    public function index(Request $request)
    {
        // Lấy ID user đang đăng nhập
        $ownerId = Auth::id();

        // Query dịch vụ
        $query = Service::query()
            ->with([
                'category',
                'images', // Load quan hệ images
                'venues' => function ($q) use ($ownerId) {
                    // Chỉ load thông tin pivot của các sân thuộc owner này (để hiển thị giá đúng)
                    $q->where('owner_id', $ownerId);
                }
            ])
            // QUAN TRỌNG: Lọc chỉ lấy dịch vụ thuộc các sân của Owner này
            ->whereHas('venues', function ($q) use ($ownerId) {
                $q->where('owner_id', $ownerId);
            });

        // Filter Category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $services = $query->paginate(10);

        // Lấy danh sách sân để hiển thị Sidebar/Modal
        $venues = Venue::where('owner_id', $ownerId)->get();
        $query->where('category_id', $request->category_id)->where('owner_id', $ownerId);
        $categories = ServiceCategory::where('owner_id', $ownerId)->get();
        Log::info("Fetched Services: " . print_r($services->toArray(), true));

        return view('venue_owner.services.index', compact('services', 'venues', 'categories'));
    }


    public function store(Request $request)
    {
        Log::info("Request Data: " . print_r($request->all(), true));

        // 1. Validate dữ liệu cho chắc chắn (Dù frontend có validate thì backend vẫn cần)
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'unit' => 'required|string',
            'type' => 'nullable|string',
            'venue_ids' => 'required|array',       // Bắt buộc phải là mảng
            'venue_ids.*' => 'exists:venues,id',   // Các ID trong mảng phải tồn tại trong bảng venues
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|max:2048',  // Tối đa 2MB
        ]);

        DB::beginTransaction(); // Dùng transaction để đảm bảo lưu cả 2 bảng thành công mới commit
        try {

            $service = Service::firstOrCreate(
                [
                    'name' => $request->name,
                    'category_id' => $request->category_id
                ],
                [
                    'unit' => $request->unit,
                    'type' => $request->type,
                    'description' => $request->description,
                ]
            );

            // --- BƯỚC 2: XỬ LÝ ẢNH ---
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->store('uploads/services', 'public');

                $service->images()->create([
                    'url' => 'storage/' . $path,
                    'description' => 'Ảnh đại diện cho dịch vụ: ' . $service->name,
                    'is_primary' => true,
                ]);
            }

            // --- BƯỚC 3: THÊM VÀO CÁC SÂN (Bảng trung gian) ---
            // Chuẩn bị dữ liệu cho bảng pivot (venue_services)
            $pivotData = [
                'price' => $request->price,
                'stock' => 0,
                'status' => ($request->status === 'active') ? 1 : 0, // Chuyển 'active' thành 1, 'inactive' thành 0
                'created_at' => now(),
                'updated_at' => now(),
            ];

            foreach ($request->venue_ids as $venueId) {

                $service->venues()->syncWithoutDetaching([
                    $venueId => $pivotData
                ]);
            }

            DB::commit(); // Lưu tất cả
            return redirect()->back()->with('success', 'Thêm dịch vụ thành công!');
        } catch (\Exception $e) {
            DB::rollBack(); // Có lỗi thì hủy hết thao tác db nãy giờ
            Log::error("Lỗi thêm dịch vụ: " . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        // 1. Validate
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'type'        => 'required|in:consumable,service,amenities',
            'unit'        => 'required|string|max:50',
            'price'       => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            // Kiểm tra mảng venue_ids
            'venue_ids'   => 'nullable|array',
            'venue_ids.*' => 'exists:venues,id',
        ]);

        DB::beginTransaction();
        try {
            $service = Service::findOrFail($id);

            // 2. Cập nhật thông tin cơ bản
            $service->update([
                'name'        => $validated['name'],
                'category_id' => $validated['category_id'],
                'type'        => $validated['type'],
                'unit'        => $validated['unit'],
                'description' => $validated['description'],
                'status'      => $validated['status'],
            ]);

            // 3. Xử lý Ảnh
            if ($request->hasFile('image')) {
                // Xóa ảnh cũ
                if ($service->images()->exists()) {
                    $oldImage = $service->images()->first();
                    $storagePath = str_replace('/storage/', '', $oldImage->url);
                    Storage::disk('public')->delete($storagePath);
                    $oldImage->delete();
                }

                // Lưu ảnh mới
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('services', $filename, 'public');

                $service->images()->create([
                    'url' => '/storage/' . $path,
                    'is_primary' => 1
                ]);
            }

            // 4. Xử lý Sân (Quan trọng)
            $venueIds = $request->input('venue_ids', []); // Danh sách sân được chọn từ form
            $inputPrice = ($validated['type'] === 'amenities') ? 0 : ($validated['price'] ?? 0);

            // Lấy danh sách các sân hiện tại để lấy lại stock cũ
            $currentVenues = $service->venues()->get()->keyBy('id');
            $syncData = [];

            foreach ($venueIds as $venueId) {
                // Mặc định stock = 0 nếu là sân mới
                $stockToSave = 0;

                // Nếu sân này đã có trong DB, giữ nguyên stock cũ
                if (isset($currentVenues[$venueId])) {
                    $stockToSave = $currentVenues[$venueId]->pivot->stock;
                }

                $syncData[$venueId] = [
                    'price' => $inputPrice, // Giá mới từ form
                    'stock' => $stockToSave // Stock cũ
                ];
            }

            // Hàm sync sẽ:
            // - Thêm mới các sân chưa có.
            // - Cập nhật các sân đã có.
            // - XÓA các sân không có trong $syncData (những sân bị bỏ tick).
            $service->venues()->sync($syncData);

            DB::commit();
            return redirect()->back()->with('success', 'Cập nhật dịch vụ thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating service ID $id: " . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $service = Service::with(['images', 'venueServices'])->findOrFail($id);

            // 1️⃣ Xóa ảnh (file + DB)
            foreach ($service->images as $image) {
                $path = str_replace(url('/storage'), 'public', $image->url);
                Storage::delete($path);
                $image->forceDelete(); // ❗ cứng
            }

            // 2️⃣ Xóa cứng toàn bộ venue_services
            $service->venueServices()->forceDelete();

            // 3️⃣ Xóa cứng service
            $service->forceDelete();
        });

        return back()->with('success', 'Đã xóa cứng dịch vụ và toàn bộ dữ liệu liên quan');
    }


    public function  update_stock(Request $request)
    {
        Log::info("Request Data for update_stock: " . print_r($request->all(), true));
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'venue_id'   => 'required|exists:venues,id',
            'price'      => 'required|numeric|min:0',
            'status'     => 'nullable|in:0,1',
            'stock'      => 'required|integer|min:0|max:200', // Thêm max:200 ở đây
        ], [
            'stock.max'  => 'Số lượng tồn kho không được vượt quá 200.',
            'stock.required' => 'Vui lòng không để trống số lượng tồn kho.',
            'stock.integer'  => 'Số lượng tồn kho phải là số nguyên.',
        ]);

        $venue_service = VenueService::where('venue_id', $validated['venue_id'])
            ->where('service_id', $validated['service_id'])
            ->first();

        if ($venue_service) {
            $venue_service->update([
                'price' => $validated['price'],
                'stock' => $validated['stock'],
            ]);
        }
        if (!$venue_service) {
            Log::warning("VenueService not found for service_id: {$validated['service_id']} and venue_id: {$validated['venue_id']}");
            return redirect()->back()->with('error', 'Dịch vụ không tồn tại cho sân này. Vui lòng thử lại.');
        }

        return redirect()->back()->with('success', 'Cập nhật kho và giá dịch vụ thành công!');
    }
}
