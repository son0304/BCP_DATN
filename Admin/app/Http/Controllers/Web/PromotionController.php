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
    /**
     * Hàm helper để lấy prefix dựa trên role nhằm tránh lặp lại code
     */
    private function getRoleData()
    {
        $user = Auth::user();
        // Giả sử role là relationship. Nếu role là string, hãy sửa thành: $role = $user->role;
        $role = $user->role->name;

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
        $query = Promotion::with(['creator', 'venue']);

        if ($data['role'] === 'venue_owner') {
            $query->where('creator_user_id', $data['user']->id);
        } else if ($data['role'] === 'admin') {
            $query->whereHas('creator', function ($q) {
                $q->whereHas('role', function ($sq) {
                    $sq->where('name', 'admin');
                });
            });
        }

        $promotions = $query->latest()->paginate(15)->withQueryString();
        return view("{$data['view_prefix']}.promotions.index", compact('promotions'));
    }

    public function create()
    {
        $data = $this->getRoleData();

        $venues = Venue::when($data['role'] === 'venue_owner', function ($q) use ($data) {
            return $q->where('owner_id', $data['user']->id);
        })->select('id', 'name')->get();

        return view("{$data['view_prefix']}.promotions.create", compact('venues'));
    }

    public function store(Request $request)
    {
        $data = $this->getRoleData();

        $rules = [
            'code' => 'required|string|max:50|unique:promotions,code',
            'value' => 'required|numeric|min:0',
            'type' => 'required|in:percentage,fixed',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'usage_limit' => 'required|integer|min:0',
            'venue_id' => 'nullable|exists:venues,id',
            'process_status' => 'required|in:active,disabled',
            'target_user_type' => 'required|in:all,new_user',
            'description' => 'nullable|string'
        ];

        $request->validate($rules);

        // Bảo mật: Kiểm tra quyền sở hữu sân
        if ($data['role'] === 'venue_owner' && $request->filled('venue_id')) {
            $isMine = Venue::where('id', $request->venue_id)->where('owner_id', $data['user']->id)->exists();
            if (!$isMine) abort(403, 'Bạn không có quyền áp dụng mã cho sân này.');
        }

        Promotion::create([
            'code'                => strtoupper($request->code),
            'value'               => $request->value,
            'type'                => $request->type,
            'start_at'            => Carbon::parse($request->start_at, 'Asia/Ho_Chi_Minh')->utc(),
            'end_at'              => Carbon::parse($request->end_at, 'Asia/Ho_Chi_Minh')->utc(),
            'usage_limit'         => $request->usage_limit,
            'used_count'          => 0,
            'min_order_value'     => $request->min_order_value ?? 0,
            'max_discount_amount' => $request->type === 'percentage' ? $request->max_discount_amount : null,
            'venue_id'            => $request->venue_id,
            'target_user_type'    => $request->target_user_type,
            'process_status'      => $request->process_status,
            'description'         => $request->description,
            'creator_user_id'     => $data['user']->id,
        ]);

        return redirect()->route("{$data['route_prefix']}.promotions.index")->with('success', 'Tạo mã thành công!');
    }

    public function edit(Promotion $promotion)
    {
        $data = $this->getRoleData();

        // Kiểm tra quyền sở hữu bản ghi
        if ($data['role'] === 'venue_owner' && $promotion->creator_user_id !== $data['user']->id) {
            abort(403);
        }

        $venues = Venue::when($data['role'] === 'venue_owner', function ($q) use ($data) {
            return $q->where('owner_id', $data['user']->id);
        })->select('id', 'name')->get();

        return view("{$data['view_prefix']}.promotions.edit", compact('promotion', 'venues'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->getRoleData();

        if ($data['role'] === 'venue_owner' && $promotion->creator_user_id !== $data['user']->id) {
            abort(403);
        }

        // BẮT BUỘC PHẢI CÓ VALIDATE
        $request->validate([
            'value' => 'required|numeric|min:0',
            'type' => 'required|in:percentage,fixed',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'usage_limit' => 'required|integer|min:' . $promotion->used_count,
            'venue_id' => 'nullable|exists:venues,id',
            'process_status' => 'required|in:active,disabled',
            'target_user_type' => 'required|in:all,new_user',
        ]);

        // Bảo mật: Kiểm tra quyền sở hữu sân khi update
        if ($data['role'] === 'venue_owner' && $request->filled('venue_id')) {
            $isMine = Venue::where('id', $request->venue_id)->where('owner_id', $data['user']->id)->exists();
            if (!$isMine) abort(403);
        }

        $promotion->update([
            'value'               => $request->value,
            'type'                => $request->type,
            'start_at'            => Carbon::parse($request->start_at, 'Asia/Ho_Chi_Minh')->utc(),
            'end_at'              => Carbon::parse($request->end_at, 'Asia/Ho_Chi_Minh')->utc(),
            'usage_limit'         => $request->usage_limit,
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

        if ($data['role'] === 'venue_owner' && $promotion->creator_user_id !== $data['user']->id) {
            abort(403);
        }

        if ($promotion->used_count > 0) {
            return back()->with('error', 'Mã đã được sử dụng, không thể xóa. Hãy chuyển sang trạng thái Tạm tắt.');
        }

        $promotion->delete();
        return back()->with('success', 'Đã xóa mã khuyến mãi.');
    }
}