<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatisticController extends Controller
{
    public function index(Request $request)
    {
        // 1. Xác định thời gian lọc
        $start = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfMonth();

        // 2. Định nghĩa Namespace Polymorphic để so sánh chính xác trong DB
        $typeTicket  = 'App\Models\Ticket';
        $typeSponsor = 'App\Models\SponsorshipPackage';

        // 3. TỔNG QUAN TÀI CHÍNH (Core logic tách dòng tiền)
        // Chỉ lấy status = 'completed' (đã hoàn thành/đã thanh toán)
        $finance = DB::table('money_flows')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->selectRaw("
                SUM(total_amount) as gmv,
                SUM(admin_amount) as total_profit,

                -- Tách Hoa hồng từ Đặt sân (Ticket)
                SUM(CASE WHEN money_flowable_type = ? THEN admin_amount ELSE 0 END) as commission_revenue,

                -- Tách Doanh thu từ Quảng cáo (Sponsorship)
                SUM(CASE WHEN money_flowable_type = ? THEN admin_amount ELSE 0 END) as ads_revenue,

                COUNT(id) as total_txns
            ", [$typeTicket, $typeSponsor])
            ->first();

        // 4. BIỂU ĐỒ DOANH THU THEO NGÀY (Stacked Chart)
        $chartData = DB::table('money_flows')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->selectRaw("
                DATE(created_at) as date,
                SUM(CASE WHEN money_flowable_type = ? THEN admin_amount ELSE 0 END) as commission,
                SUM(CASE WHEN money_flowable_type = ? THEN admin_amount ELSE 0 END) as ads
            ", [$typeTicket, $typeSponsor])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 5. TOP SÂN ĐÓNG GÓP HOA HỒNG CAO NHẤT (Chỉ tính Ticket)
        $topVenues = DB::table('venues')
            ->join('money_flows', 'venues.id', '=', 'money_flows.venue_id')
            ->whereBetween('money_flows.created_at', [$start, $end])
            ->where('money_flows.status', 'completed')
            ->where('money_flows.money_flowable_type', $typeTicket) // Chỉ tính tiền từ booking
            ->select(
                'venues.name',
                DB::raw('COUNT(money_flows.id) as total_bookings'),
                DB::raw('SUM(money_flows.admin_amount) as total_commission')
            )
            ->groupBy('venues.id', 'venues.name')
            ->orderByDesc('total_commission')
            ->limit(5)
            ->get();

        // 6. User mới
        $newUsers = DB::table('users')->whereBetween('created_at', [$start, $end])->count();

        return view('admin.statistics.index', compact(
            'finance',
            'chartData',
            'topVenues',
            'newUsers',
            'start',
            'end'
        ));
    }
}