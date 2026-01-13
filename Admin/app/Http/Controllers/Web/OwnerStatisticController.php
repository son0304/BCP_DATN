<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{MoneyFlow, Venue};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class OwnerStatisticController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = Auth::id();
        $allMyVenues = Venue::where('owner_id', $ownerId)->get();
        $myVenueIds = $allMyVenues->pluck('id');

        $start = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->subDays(29)->startOfDay();
        $end = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfDay();

        $vId = $request->venue_id;
        $filterVenueIds = $vId ? [$vId] : $myVenueIds;

        // 1. Lấy thống kê KPI bằng SQL để tối ưu hiệu năng
        // Logic: Join từ MoneyFlow -> Promotion -> User (Creator) -> Role
        $stats = MoneyFlow::leftJoin('promotions', 'money_flows.promotion_id', '=', 'promotions.id')
            ->leftJoin('users', 'promotions.creator_user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->whereIn('money_flows.venue_id', $filterVenueIds)
            ->whereBetween('money_flows.created_at', [$start, $end])
            ->where('money_flows.status', 'completed')
            ->selectRaw("
                SUM(money_flows.venue_owner_amount) as total_net,
                COUNT(CASE WHEN money_flowable_type LIKE '%Ticket%' THEN 1 END) as total_bookings,
                SUM(CASE
                    WHEN money_flows.promotion_id IS NOT NULL
                    AND (roles.name NOT LIKE '%admin%' OR roles.name IS NULL)
                    THEN money_flows.promotion_amount
                    ELSE 0
                END) as total_owner_voucher
            ")
            ->first();

        // 2. Dữ liệu biểu đồ hàng ngày (Doanh thu thực nhận)
        $dailyData = MoneyFlow::whereIn('venue_id', $filterVenueIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(venue_owner_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 3. Danh sách giao dịch (Eager load lồng nhau để lấy Role người tạo voucher)
        $transactions = MoneyFlow::with([
            'venue',
            'money_flowable',
            'promotion.creator.role' // Quan trọng: lấy role để check ở View
        ])
            ->whereIn('venue_id', $filterVenueIds)
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->all());

        // Giả định logic tính tỷ lệ lấp đầy
        $occupancyRate = 0;

        return view('venue_owner.statistics.index', compact(
            'allMyVenues',
            'stats',
            'dailyData',
            'transactions',
            'occupancyRate',
            'start',
            'end'
        ));
    }
}