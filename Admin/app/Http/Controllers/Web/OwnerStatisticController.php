<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MoneyFlow;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OwnerStatisticController extends Controller
{

    public function index(Request $request)
    {
        $ownerId = Auth::id();
        $start = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfMonth();

        // 1. Lấy tất cả Venue ID thuộc quyền sở hữu
        $myVenueIds = Venue::where('owner_id', $ownerId)->pluck('id');

        // 2. Query cơ bản
        $query = MoneyFlow::whereIn('venue_id', $myVenueIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed');
        $queryPending = MoneyFlow::whereIn('venue_id', $myVenueIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'pending');
            $historyPayments = MoneyFlow::whereIn('venue_id', $myVenueIds)->get();
            // 3. Tính toán KPI
        $totalComplete  = $query->sum('venue_owner_amount'); // Tiền thực nhận
        $totalPending   = $queryPending->sum('venue_owner_amount'); // Tiền chờ duyệt
        $totalBookings = $query->count();

        // // 4. Biểu đồ tròn (Tỷ trọng doanh thu các sân)
        // $revenueByVenue = DB::table('money_flows')
        //     ->join('venues', 'money_flows.venue_id', '=', 'venues.id')
        //     ->whereIn('money_flows.venue_id', $myVenueIds)
        //     ->whereBetween('money_flows.created_at', [$start, $end])
        //     // ->where('money_flows.status', 'success')
        //     ->select('venues.name', DB::raw('SUM(money_flows.venue_owner_amount) as revenue'))
        //     ->groupBy('venues.id', 'venues.name')
        //     ->get();

        // // 5. Biểu đồ cột (Doanh thu theo ngày)
        // $dailyRevenue = MoneyFlow::whereIn('venue_id', $myVenueIds)
        //     ->whereBetween('created_at', [$start, $end])
        //     // ->where('status', 'success')
        //     ->selectRaw('DATE(created_at) as date, SUM(venue_owner_amount) as revenue')
        //     ->groupBy('date')
        //     ->orderBy('date')
        //     ->get();

        return view('venue_owner.statistics.index', compact(
            'totalComplete',
            'totalPending',
            'historyPayments',
            'totalBookings',
            'start',
            'end'
        ));
    }
}