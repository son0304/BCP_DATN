<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{MoneyFlow, Wallet, Transaction, Ticket, SponsorshipPackage};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatisticController extends Controller
{
    public function index(Request $request)
    {
        // 1. Xử lý thời gian
        $start = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::now()->startOfMonth();
        $end   = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now()->endOfMonth();

        $typeTicket  = (new Ticket())->getMorphClass();
        $typeSponsor = (new SponsorshipPackage())->getMorphClass();

        // 2. NHÓM 1: DÒNG TIỀN MẶT (CASH-FLOW)
        $cashIn = DB::table('transactions')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'success')
            ->sum('amount');

        $cashOut = DB::table('withdrawal_requests')
            ->whereBetween('processed_at', [$start, $end])
            ->where('status', 'approved')
            ->sum('amount');

        // Số dư thực tế trong Bank (Lũy kế toàn thời gian)
        $allTimeIn = DB::table('transactions')->where('status', 'success')->sum('amount');
        $allTimeOut = DB::table('withdrawal_requests')->where('status', 'approved')->sum('amount');
        $actualBankBalance = $allTimeIn - $allTimeOut;

        // 3. NHÓM 2: PHÂN TÍCH LỢI NHUẬN (PROFIT ANALYSIS)
        $finance = DB::table('money_flows')
            ->leftJoin('promotions', 'money_flows.promotion_id', '=', 'promotions.id')
            ->leftJoin('users', 'promotions.creator_user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->whereBetween('money_flows.created_at', [$start, $end])
            ->where('money_flows.status', 'completed')
            ->selectRaw("
                SUM(money_flows.total_amount) as gmv,
                SUM(money_flows.admin_amount) as net_profit,

                -- Phân loại nguồn thu
                SUM(CASE WHEN money_flowable_type = ? THEN money_flows.admin_amount ELSE 0 END) as commission_revenue,
                SUM(CASE WHEN money_flowable_type = ? THEN money_flows.admin_amount ELSE 0 END) as ads_revenue,

                -- Chi phí Voucher Admin
                SUM(CASE WHEN roles.name LIKE '%admin%' THEN money_flows.promotion_amount ELSE 0 END) as admin_voucher_cost,

                -- Hoa hồng gốc (Trước trừ voucher)
                SUM(money_flows.admin_amount + (CASE WHEN roles.name LIKE '%admin%' THEN money_flows.promotion_amount ELSE 0 END)) as gross_commission
            ", [$typeTicket, $typeSponsor])
            ->first();

        // 4. NHÓM 3: ĐỐI SOÁT VÍ (LIABILITIES)
        $adminWallet = Wallet::whereHas('user.role', function ($q) {
            $q->where('name', 'like', '%admin%');
        })->sum('balance');
        $totalOwnerLiability = Wallet::whereHas('user.role', function ($q) {
            $q->where('name', 'venue_owner');
        })->sum('balance');

        // 5. NHÓM 4: BIỂU ĐỒ STACKED (Hoa hồng & Quảng cáo)
        $chartData = DB::table('money_flows')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->selectRaw("
                DATE(created_at) as date,
                SUM(CASE WHEN money_flowable_type = ? THEN admin_amount ELSE 0 END) as commission,
                SUM(CASE WHEN money_flowable_type = ? THEN admin_amount ELSE 0 END) as ads
            ", [$typeTicket, $typeSponsor])
            ->groupBy('date')->orderBy('date')->get();

        // 6. TOP SÂN ĐÓNG GÓP CAO NHẤT
        $topVenues = DB::table('venues')
            ->join('money_flows', 'venues.id', '=', 'money_flows.venue_id')
            ->whereBetween('money_flows.created_at', [$start, $end])
            ->where('money_flows.status', 'completed')
            ->select('venues.name', DB::raw('SUM(money_flows.admin_amount) as total_commission'))
            ->groupBy('venues.id', 'venues.name')->orderByDesc('total_commission')->limit(5)->get();

        // 7. SỔ CÁI CHI TIẾT
        $recentTransactions = MoneyFlow::with(['venue', 'money_flowable', 'promotion.creator.role'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.statistics.index', compact(
            'actualBankBalance',
            'cashIn',
            'cashOut',
            'finance',
            'adminWallet',
            'totalOwnerLiability',
            'chartData',
            'topVenues',
            'recentTransactions',
            'start',
            'end'
        ));
    }
}