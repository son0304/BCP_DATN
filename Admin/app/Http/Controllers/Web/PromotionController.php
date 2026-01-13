<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PromotionController extends Controller
{
    private function getRoleData()
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'user';

        return [
            'user' => $user,
            'role' => $role,
            'view_prefix' => ($role === 'admin') ? 'admin' : 'venue_owner',
            'route_prefix' => ($role === 'admin') ? 'admin' : 'owner'
        ];
    }

    public function index(Request $request)
    {
        $data = $this->getRoleData();

        // Khởi tạo query với điều kiện bắt buộc: chỉ lấy của người đang đăng nhập
        $query = Promotion::with(['creator', 'venue'])
            ->where('creator_user_id', $data['user']->id);

        // 1. Tìm kiếm theo Mã voucher hoặc Mô tả
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // 2. Lọc theo trạng thái xử lý (Đang chạy / Tạm tắt)
        if ($request->filled('status')) {
            $query->where('process_status', $request->status);
        }

        // 3. Lọc theo đối tượng khách hàng (Tất cả / Khách mới)
        if ($request->filled('target_user_type')) {
            $query->where('target_user_type', $request->target_user_type);
        }

        // 4. Lọc theo thời gian (Hết hạn / Đang hiệu lực)
        if ($request->filled('time_status')) {
            $now = now();
            if ($request->time_status === 'expired') {
                $query->where('end_at', '<', $now);
            } elseif ($request->time_status === 'active') {
                $query->where('start_at', '<=', $now)
                    ->where('end_at', '>=', $now);
            } elseif ($request->time_status === 'upcoming') {
                $query->where('start_at', '>', $now);
            }
        }

        $promotions = $query->latest()->paginate(15)->withQueryString();

        return view("{$data['view_prefix']}.promotions.index", compact('promotions'));
    }

    public function create()
    {
        $data = $this->getRoleData();

        // Admin có thể chọn tạo cho bất kỳ sân nào hoặc toàn hệ thống
        // Chủ sân chỉ được chọn các sân của họ
        $venues = Venue::when(in_array($data['role'], ['venue_owner', 'owner']), function ($q) use ($data) {
            return $q->where('owner_id', $data['user']->id);
        })->select('id', 'name')->get();

        return view("{$data['view_prefix']}.promotions.create", compact('venues'));
    }

    public function store(Request $request)
    {
        $data = $this->getRoleData();

        $request->validate(
            [
                'code'             => 'required|string|max:50|unique:promotions,code',
                'value'            => 'required|numeric|min:0' . ($request->type === 'percentage' ? '|max:100' : ''),
                'type'             => 'required|in:percentage,fixed',
                'start_at'         => 'required|date',
                'end_at'           => 'required|date|after:start_at',
                'usage_limit'      => 'nullable|integer',
                'venue_id'         => 'nullable|exists:venues,id',
                'process_status'   => 'required|in:active,disabled',
                'target_user_type' => 'required|in:all,new_user',
            ],
            [
                // code
                'code.required' => 'Vui lòng nhập mã voucher.',
                'code.string'   => 'Mã voucher phải là chuỗi ký tự.',
                'code.max'      => 'Mã voucher không được vượt quá 50 ký tự.',
                'code.unique'   => 'Mã voucher đã tồn tại.',

                // value
                'value.required' => 'Vui lòng nhập giá trị giảm.',
                'value.numeric'  => 'Giá trị giảm phải là số.',
                'value.min'      => 'Giá trị giảm không được nhỏ hơn 0.',
                'value.max'      => 'Giá trị giảm theo phần trăm không được vượt quá 100%.', // Thông báo lỗi khi vượt 100%

                // type
                'type.required' => 'Vui lòng chọn loại giảm giá.',
                'type.in'       => 'Loại giảm giá không hợp lệ.',

                // start_at
                'start_at.required' => 'Vui lòng chọn thời gian bắt đầu.',
                'start_at.date'     => 'Thời gian bắt đầu không hợp lệ.',

                // end_at
                'end_at.required' => 'Vui lòng chọn thời gian kết thúc.',
                'end_at.date'     => 'Thời gian kết thúc không hợp lệ.',
                'end_at.after'    => 'Thời gian kết thúc phải sau thời gian bắt đầu.',

                // usage_limit
                'usage_limit.integer' => 'Giới hạn sử dụng phải là số nguyên.',

                // venue_id
                'venue_id.exists' => 'Sân được chọn không tồn tại.',

                // process_status
                'process_status.required' => 'Vui lòng chọn trạng thái.',
                'process_status.in'       => 'Trạng thái không hợp lệ.',

                // target_user_type
                'target_user_type.required' => 'Vui lòng chọn đối tượng áp dụng.',
                'target_user_type.in'       => 'Đối tượng áp dụng không hợp lệ.',
            ]
        );

        $usageLimit = $request->has('is_unlimited') ? -1 : ($request->usage_limit ?? 0);

        Promotion::create([
            'code'                => strtoupper($request->code),
            'value'               => $request->value,
            'type'                => $request->type,
            'start_at'            => Carbon::parse($request->start_at),
            'end_at'              => Carbon::parse($request->end_at),
            'usage_limit'         => $usageLimit,
            'used_count'          => 0,
            'min_order_value'     => $request->min_order_value ?? 0,
            'max_discount_amount' => $request->type === 'percentage' ? $request->max_discount_amount : null,
            'venue_id'            => $request->venue_id,
            'target_user_type'    => $request->target_user_type,
            'process_status'      => $request->process_status,
            'description'         => $request->description,
            'creator_user_id'     => $data['user']->id, // Lưu ID người tạo
        ]);

        return redirect()->route("{$data['route_prefix']}.promotions.index")->with('success', 'Tạo mã thành công!');
    }

    public function edit(Promotion $promotion)
    {
        $data = $this->getRoleData();

        // CẬP NHẬT: Bảo mật - Không cho phép sửa nếu không phải là người tạo
        if ($promotion->creator_user_id !== $data['user']->id) {
            abort(403, 'Bạn không có quyền chỉnh sửa mã này.');
        }

        $venues = Venue::when(in_array($data['role'], ['venue_owner', 'owner']), function ($q) use ($data) {
            return $q->where('owner_id', $data['user']->id);
        })->select('id', 'name')->get();

        return view("{$data['view_prefix']}.promotions.edit", compact('promotion', 'venues'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->getRoleData();

        // CẬP NHẬT: Bảo mật - Không cho phép cập nhật nếu không phải là người tạo
        if ($promotion->creator_user_id !== $data['user']->id) {
            abort(403);
        }

        $request->validate([
            'value'               => 'required|numeric|min:0',
            'type'                => 'required|in:percentage,fixed',
            'start_at'            => 'required|date',
            'end_at'              => 'required|date|after:start_at',
            'process_status'      => 'required|in:active,disabled',
            'target_user_type'    => 'required|in:all,new_user',
        ]);

        $usageLimit = $request->has('is_unlimited') ? -1 : ($request->usage_limit ?? 0);

        $promotion->update([
            'value'               => $request->value,
            'type'                => $request->type,
            'start_at'            => Carbon::parse($request->start_at),
            'end_at'              => Carbon::parse($request->end_at),
            'usage_limit'         => $usageLimit,
            'min_order_value'     => $request->min_order_value ?? 0,
            'max_discount_amount' => $request->type === 'percentage' ? $request->max_discount_amount : null,
            'venue_id'            => $request->venue_id,
            'target_user_type'    => $request->target_user_type,
            'process_status'      => $request->process_status,
            'description'         => $request->description,
        ]);

        return redirect()->route("{$data['route_prefix']}.promotions.index")->with('success', 'Cập nhật thành công!');
    }

    public function destroy(Promotion $promotion)
    {
        $data = $this->getRoleData();

        // CẬP NHẬT: Bảo mật - Không cho phép xóa nếu không phải là người tạo
        if ($promotion->creator_user_id !== $data['user']->id) {
            abort(403);
        }

        $promotion->delete();
        return back()->with('success', 'Đã xóa mã khuyến mãi.');
    }
}