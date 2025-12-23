<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
            $query->where(function ($q) use ($search) {
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
                $query->where(function ($q) use ($now) {
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
    public function store(Request $request)
    {
        // 2. Validate dữ liệu Tạo mới
        $request->validate([
            'code'                => 'required|string|max:50|unique:promotions,code',
            'value'               => 'required|numeric|min:0',
            'type'                => 'required|in:money,%', // Chấp nhận 'money' (giảm tiền) hoặc '%' (phần trăm)
            // after_or_equal:now => Phải từ thời điểm hiện tại trở đi
            'start_at' => 'required|date|after_or_equal:now',
            // after:start_at => Phải sau ngày bắt đầu
            'end_at'   => 'required|date|after:start_at', // Ngày kết thúc phải sau ngày bắt đầu
            'usage_limit'         => 'required|integer|min:1',
            'max_discount_amount' => 'nullable|numeric|min:0',
        ], [
            'code.required'          => 'Mã khuyến mãi không được bỏ trống.',
            'code.unique'            => 'Mã khuyến mãi này đã tồn tại.',
            'value.required'         => 'Giá trị giảm không được bỏ trống.',
            'type.required'          => 'Loại giảm giá không hợp lệ.',
            'start_at.required'      => 'Thời gian bắt đầu là bắt buộc.',
            'start_at.after_or_equal' => 'Thời gian bắt đầu không được nhỏ hơn thời điểm hiện tại.',
            'end_at.required'        => 'Thời gian kết thúc là bắt buộc.',
            'end_at.after'           => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'usage_limit.required'   => 'Giới hạn sử dụng là bắt buộc.',
            'usage_limit.min'        => 'Giới hạn sử dụng phải ít nhất là 1.',
        ]);

        // Validate logic bổ sung: Nếu là % thì giá trị không quá 100
        if ($request->type === '%' && $request->value > 100) {
            return back()->withErrors(['value' => 'Giá trị phần trăm giảm giá không được vượt quá 100.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Convert datetime-local (VN timezone) về UTC để lưu vào database
            $startAt = \Carbon\Carbon::parse($request->start_at, 'Asia/Ho_Chi_Minh')->utc();
            $endAt = \Carbon\Carbon::parse($request->end_at, 'Asia/Ho_Chi_Minh')->utc();

            Promotion::create([
                'code'                => strtoupper($request->code),
                'value'               => $request->value,
                'type'                => $request->type,
                'start_at'            => $startAt->format('Y-m-d H:i:s'),
                'end_at'              => $endAt->format('Y-m-d H:i:s'),
                'usage_limit'         => $request->usage_limit,
                'used_count'          => 0,
                'created_by'          => auth()->id(),
                'max_discount_amount' => $request->type === '%' ? ($request->max_discount_amount ?? 0) : null,
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
    public function update(Request $request, Promotion $promotion)
    {
        // 3. Validate dữ liệu Cập nhật
        $request->validate([
            // Mã code là duy nhất nhưng trừ chính nó ra
            'code'                => ['required', 'string', 'max:50', Rule::unique('promotions')->ignore($promotion->id)],
            'value'               => 'required|numeric|min:0',
            'type'                => 'required|in:money,%',
            'start_at'            => 'required|date', // Khi update có thể giữ nguyên ngày cũ, không bắt buộc after_or_equal today
            'end_at'              => 'required|date|after:start_at',
            // Giới hạn sử dụng mới không được nhỏ hơn số lượng đã dùng
            'usage_limit'         => 'required|integer|min:' . $promotion->used_count,
            'max_discount_amount' => 'nullable|numeric|min:0',
        ], [
            'code.required'        => 'Mã khuyến mãi không được bỏ trống.',
            'code.unique'          => 'Mã khuyến mãi này đã tồn tại.',
            'value.required'       => 'Giá trị giảm không được bỏ trống.',
            'end_at.after'         => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'usage_limit.min'      => "Giới hạn sử dụng không được thấp hơn số lượng đã dùng ({$promotion->used_count}).",
        ]);

        if ($request->type === '%' && $request->value > 100) {
            return back()->withErrors(['value' => 'Giá trị phần trăm giảm giá không được vượt quá 100.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $startAt = \Carbon\Carbon::parse($request->start_at, 'Asia/Ho_Chi_Minh')->utc();
            $endAt = \Carbon\Carbon::parse($request->end_at, 'Asia/Ho_Chi_Minh')->utc();

            $promotion->update([
                'code'                => strtoupper($request->code),
                'value'               => $request->value,
                'type'                => $request->type,
                'start_at'            => $startAt->format('Y-m-d H:i:s'),
                'end_at'              => $endAt->format('Y-m-d H:i:s'),
                'usage_limit'         => $request->usage_limit,
                'max_discount_amount' => $request->type === '%' ? ($request->max_discount_amount ?? $promotion->max_discount_amount) : null,
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
