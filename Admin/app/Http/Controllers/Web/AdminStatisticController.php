<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MoneyFlow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatisticController extends Controller
{
    public function index(Request $request)
    {
        // 1. Xử lý thời gian lọc (Mặc định: Ngày đầu tháng -> Ngày cuối tháng hiện tại)
        $start = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfMonth();

        // 2. Query Chung
        // QUAN TRỌNG: Chỉ thống kê các giao dịch đã thành công (tránh tính tiền ảo từ đơn pending/cancelled)
        // Nếu bảng money_flows của bạn chưa update status chuẩn, hãy tạm bỏ ->where('status', 'success')
        $query = MoneyFlow::whereBetween('created_at', [$start, $end]);
        // ->where('status', 'success');

        // 3. Tính toán KPI Tổng quan
        $stats = $query->select(
            DB::raw('SUM(admin_amount) as total_profit'),       // Lợi nhuận Admin
            DB::raw('SUM(venue_owner_amount) as total_payout'), // Tiền trả Chủ sân
            DB::raw('SUM(total_amount) as total_gmv'),          // Tổng dòng tiền (GMV)
            DB::raw('COUNT(id) as total_transactions')          // Tổng số đơn
        )->first();

        // Gán biến để truyền sang View (tránh lỗi undefined)
        $netProfit  = $stats->total_profit ?? 0;
        $partnerPay = $stats->total_payout ?? 0;
        $totalGMV   = $stats->total_gmv ?? 0;
        $totalTxn   = $stats->total_transactions ?? 0;

        // 4. Dữ liệu Biểu đồ (Line Chart - Lợi nhuận theo ngày)
        $chartData = MoneyFlow::whereBetween('created_at', [$start, $end])
            // ->where('status', 'success')
            ->selectRaw('DATE(created_at) as date, SUM(admin_amount) as profit')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 5. Top 5 Sân hiệu quả nhất
        $topVenues = DB::table('money_flows')
            ->join('venues', 'money_flows.venue_id', '=', 'venues.id')
            ->whereBetween('money_flows.created_at', [$start, $end])
            // ->where('money_flows.status', 'success')
            ->select(
                'venues.name',
                DB::raw('SUM(money_flows.admin_amount) as profit_contribution'),
                DB::raw('SUM(money_flows.total_amount) as total_revenue')
            )
            ->groupBy('venues.id', 'venues.name')
            ->orderByDesc('profit_contribution')
            ->limit(5)
            ->get();

        return view('admin.statistics.index', compact(
            'netProfit',
            'partnerPay',
            'totalGMV',
            'totalTxn',
            'chartData',
            'topVenues',
            'start',
            'end'
        ));
    }
}