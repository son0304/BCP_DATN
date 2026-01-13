<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{MoneyFlow, Venue, Booking, Ticket};
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
        $daysCount = $start->diffInDays($end) + 1;

        $vId = $request->venue_id;
        $filterVenueIds = $vId ? [$vId] : $myVenueIds;

        // 1. Lấy thống kê tổng quan
        $stats = MoneyFlow::whereIn('venue_id', $filterVenueIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->selectRaw(
                "
                SUM(venue_owner_amount) as total_net,
                SUM(admin_amount) as total_fee,
                COUNT(CASE WHEN money_flowable_type = ? THEN 1 END) as total_bookings",
                [(new Ticket())->getMorphClass()]
            )
            ->first();

        // 2. Dữ liệu biểu đồ hàng ngày
        $dailyData = MoneyFlow::whereIn('venue_id', $filterVenueIds)
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->selectRaw(
                '
                DATE(created_at) as date,
                SUM(venue_owner_amount) as revenue,
                COUNT(CASE WHEN money_flowable_type = ? THEN 1 END) as bookings',
                [(new Ticket())->getMorphClass()]
            )
            ->groupBy('date')->orderBy('date')->get();

        // 3. Doanh thu theo từng sân bóng
        $revenueByVenue = DB::table('money_flows')
            ->join('venues', 'money_flows.venue_id', '=', 'venues.id')
            ->whereIn('money_flows.venue_id', $myVenueIds)
            ->whereBetween('money_flows.created_at', [$start, $end])
            ->where('money_flows.status', 'completed')
            ->select('venues.name', DB::raw('SUM(money_flows.venue_owner_amount) as total'))
            ->groupBy('venues.id', 'venues.name')->orderByDesc('total')->limit(5)->get();

        // 4. Doanh thu theo dịch vụ
        $revenueByService = DB::table('items')
            ->join('venue_services', 'items.venue_service_id', '=', 'venue_services.id')
            ->join('services', 'venue_services.service_id', '=', 'services.id')
            ->whereIn('venue_services.venue_id', $filterVenueIds)
            ->whereBetween('items.created_at', [$start, $end])
            ->select('services.name', DB::raw('SUM(items.quantity) as qty'), DB::raw('SUM(items.quantity * items.unit_price) as total_revenue'))
            ->groupBy('services.id', 'services.name')->orderByDesc('total_revenue')->limit(5)->get();

        // 5. Tính toán tỉ lệ lấp đầy
        $capacityByHour = DB::table('time_slots')
            ->join('courts', 'time_slots.court_id', '=', 'courts.id')
            ->whereIn('courts.venue_id', $filterVenueIds)
            ->select(DB::raw('TIME_FORMAT(time_slots.start_time, "%H:00") as hour'), DB::raw('COUNT(*) as court_count'))
            ->groupBy('hour')->get();

        $bookedByHour = DB::table('bookings')
            ->join('time_slots', 'bookings.time_slot_id', '=', 'time_slots.id')
            ->whereIn('bookings.court_id', function ($q) use ($filterVenueIds) {
                $q->select('id')->from('courts')->whereIn('venue_id', $filterVenueIds);
            })
            ->whereBetween('bookings.date', [$start, $end])
            ->whereIn('bookings.status', ['confirmed', 'completed', 'checkin'])
            ->select(DB::raw('TIME_FORMAT(time_slots.start_time, "%H:00") as hour'), DB::raw('COUNT(*) as total'))
            ->groupBy('hour')->pluck('total', 'hour');

        $vacancyLabels = [];
        $vacancyValues = [];
        foreach ($capacityByHour as $cap) {
            $vacancyLabels[] = $cap->hour;
            $totalSlots = $cap->court_count * $daysCount;
            $booked = $bookedByHour[$cap->hour] ?? 0;
            $vacancyValues[] = max(0, $totalSlots - $booked);
        }

        $totalPotentialSlots = array_sum(array_map(fn($c) => $c->court_count * $daysCount, $capacityByHour->toArray()));
        $occupancyRate = $totalPotentialSlots > 0 ? round(($stats->total_bookings / $totalPotentialSlots) * 100, 1) : 0;

        // 6. Danh sách giao dịch gần đây (ĐÃ SỬA: Thêm load promotion.creator.role)
        $transactions = MoneyFlow::with(['venue', 'money_flowable', 'promotion.creator.role'])
            ->whereIn('venue_id', $filterVenueIds)
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->paginate(10)->appends($request->all());

        return view('venue_owner.statistics.index', compact(
            'allMyVenues',
            'stats',
            'dailyData',
            'revenueByVenue',
            'revenueByService',
            'vacancyLabels',
            'vacancyValues',
            'occupancyRate',
            'start',
            'end',
            'transactions'
        ));
    }
}