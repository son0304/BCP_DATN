<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SponsoredVenue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatisticController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfMonth();

        // Định nghĩa class name để so sánh trong SQL
        $bookingType = Booking::class;
        $sponsorType = SponsoredVenue::class;

        // 1. KPI TÀI CHÍNH TỔNG QUAN
        $finance = DB::table('money_flows')
            ->whereBetween('created_at', [$start, $end])
            ->where('process_status', 'completed') // Lưu ý: model bạn ghi là process_status
            ->selectRaw("
                SUM(total_amount) as gmv,
                SUM(admin_amount) as profit,
                SUM(venue_owner_amount) as payout,
                COUNT(id) as txn_count
            ")->first();

        // 2. CƠ CẤU NGUỒN THU (Dựa trên Polymorphic Type)
        $revenueSources = DB::table('money_flows')
            ->whereBetween('created_at', [$start, $end])
            ->where('process_status', 'completed')
            ->selectRaw("
                CASE
                    WHEN money_flowable_type = ? THEN 'Đặt sân'
                    WHEN money_flowable_type = ? THEN 'Quảng cáo'
                    ELSE 'Khác'
                END as source,
                SUM(total_amount) as total
            ", [$bookingType, $sponsorType])
            ->groupBy('source')
            ->get();

        // 3. THỐNG KÊ BỘ MÔN (Phải Join kèm điều kiện Type là Booking)
        $sportStats = DB::table('money_flows')
            ->join('bookings', function ($join) use ($bookingType) {
                $join->on('money_flows.money_flowable_id', '=', 'bookings.id')
                    ->where('money_flows.money_flowable_type', '=', $bookingType);
            })
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->join('venue_types', 'courts.venue_type_id', '=', 'venue_types.id')
            ->whereBetween('money_flows.created_at', [$start, $end])
            ->where('money_flows.process_status', 'completed')
            ->select('venue_types.name', DB::raw('SUM(money_flows.total_amount) as value'))
            ->groupBy('venue_types.id', 'venue_types.name')
            ->get();

        // 4. THỐNG KÊ ĐỊA PHƯƠNG
        $geoStats = DB::table('money_flows')
            ->join('venues', 'money_flows.venue_id', '=', 'venues.id')
            ->join('provinces', 'venues.province_id', '=', 'provinces.id')
            ->whereBetween('money_flows.created_at', [$start, $end])
            ->where('money_flows.process_status', 'completed')
            ->select('provinces.name', DB::raw('SUM(money_flows.total_amount) as total'))
            ->groupBy('provinces.id', 'provinces.name')
            ->orderByDesc('total')->limit(5)->get();

        // 5. BIỂU ĐỒ TĂNG TRƯỞNG
        $chartData = DB::table('money_flows')
            ->whereBetween('created_at', [$start, $end])
            ->where('process_status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as gmv, SUM(admin_amount) as profit')
            ->groupBy('date')->orderBy('date')->get();

        // 6. TOP ĐỐI TÁC
        $topVenues = DB::table('venues')
            ->leftJoin('money_flows', 'venues.id', '=', 'money_flows.venue_id')
            ->whereBetween('money_flows.created_at', [$start, $end])
            ->where('money_flows.process_status', 'completed')
            ->select(
                'venues.name',
                DB::raw("SUM(CASE WHEN money_flowable_type = '$bookingType' THEN 1 ELSE 0 END) as total_bookings"),
                DB::raw('SUM(money_flows.admin_amount) as total_admin_profit')
            )
            ->groupBy('venues.id', 'venues.name')
            ->orderByDesc('total_admin_profit')
            ->limit(5)
            ->get();

        $newUsers = DB::table('users')->whereBetween('created_at', [$start, $end])->count();

        return view('admin.statistics.index', compact(
            'finance',
            'revenueSources',
            'sportStats',
            'geoStats',
            'chartData',
            'topVenues',
            'newUsers',
            'start',
            'end'
        ));
    }
}
