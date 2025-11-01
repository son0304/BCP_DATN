<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Promotion::with('creator');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status (active/expired)
        // Chỉ filter theo thời gian (bỏ check usage_limit)
        if ($request->filled('status')) {
            $now = now();
            if ($request->status === 'active') {
                // Voucher đang hoạt động: đã bắt đầu và chưa hết hạn
                $query->where('start_at', '<=', $now)
                      ->where('end_at', '>=', $now);
            } elseif ($request->status === 'expired') {
                // Voucher đã hết hạn: đã qua ngày kết thúc HOẶC chưa đến ngày bắt đầu
                $query->where(function($q) use ($now) {
                    $q->where('end_at', '<', $now)
                      ->orWhere('start_at', '>', $now);
                });
            }
        }

        $promotions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.promotions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePromotionRequest $request)
    {
        DB::beginTransaction();
        try {
            // Convert datetime-local (VN timezone) về UTC để lưu vào database
            $startAt = \Carbon\Carbon::parse($request->start_at, 'Asia/Ho_Chi_Minh')->utc();
            $endAt = \Carbon\Carbon::parse($request->end_at, 'Asia/Ho_Chi_Minh')->utc();
            
            Promotion::create([
                'code' => strtoupper($request->code),
                'value' => $request->value,
                'type' => $request->type,
                'start_at' => $startAt->format('Y-m-d H:i:s'),
                'end_at' => $endAt->format('Y-m-d H:i:s'),
                'usage_limit' => $request->usage_limit ?? 0,
                'used_count' => 0,
                'created_by' => auth()->id(), // Lưu ID của admin đang tạo voucher
            ]);

            DB::commit();
            return redirect()->route('admin.promotions.index')
                ->with('success', 'Voucher đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi tạo voucher: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo voucher: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion)
    {
        $promotion->load('tickets.user', 'creator');
        return view('admin.promotions.show', compact('promotion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion)
    {
        return view('admin.promotions.edit', compact('promotion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePromotionRequest $request, Promotion $promotion)
    {
        // Kiểm tra usage_limit không được nhỏ hơn used_count
        $usageLimit = $request->usage_limit ?? 0;
        if ($usageLimit > 0 && $usageLimit < $promotion->used_count) {
            return back()->withInput()
                ->with('error', 'Giới hạn sử dụng (' . $usageLimit . ') không thể nhỏ hơn số lần đã sử dụng (' . $promotion->used_count . ').');
        }

        DB::beginTransaction();
        try {
            // Convert datetime-local (VN timezone) về UTC để lưu vào database
            $startAt = \Carbon\Carbon::parse($request->start_at, 'Asia/Ho_Chi_Minh')->utc();
            $endAt = \Carbon\Carbon::parse($request->end_at, 'Asia/Ho_Chi_Minh')->utc();
            
            $promotion->update([
                'code' => strtoupper($request->code),
                'value' => $request->value,
                'type' => $request->type,
                'start_at' => $startAt->format('Y-m-d H:i:s'),
                'end_at' => $endAt->format('Y-m-d H:i:s'),
                'usage_limit' => $usageLimit,
            ]);

            DB::commit();
            return redirect()->route('admin.promotions.index')
                ->with('success', 'Voucher đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi cập nhật voucher: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật voucher: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion)
    {
        DB::beginTransaction();
        try {
            // Kiểm tra xem voucher đã được sử dụng chưa
            if ($promotion->used_count > 0) {
                return back()->with('error', 'Không thể xóa voucher đã được sử dụng.');
            }

            $promotion->delete();

            DB::commit();
            return redirect()->route('admin.promotions.index')
                ->with('success', 'Voucher đã được xóa thành công!');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Lỗi khi xóa voucher: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xóa voucher: ' . $e->getMessage());
        }
    }
}

