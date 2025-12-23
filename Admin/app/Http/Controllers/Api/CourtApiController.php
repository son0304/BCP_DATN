<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\Court;
use Illuminate\Http\Request;

class CourtApiController extends Controller
{
    /**
     * Lấy danh sách tất cả sân.
     */
    public function index()
    {
        $courts = Court::with('timeSlots:id,court_id,label,start_time,end_time')->get();

        return response()->json([
            'message' => 'Lấy danh sách sân thành công',
            'success' => true,
            'data' => $courts,
        ]);
    }

    /**
     * Lấy thông tin chi tiết 1 sân.
     */
    public function show(Request $request, $id)
    {


        $validated = $request->validate(['date' => 'nullable|date_format:Y-m-d']);
        $date = $validated['date'] ?? now()->toDateString();
        $courts = Court::where('venue_id', $id)
            ->with([
                'timeSlots:id,court_id,label,start_time,end_time'
            ])
            ->get();

        if ($courts->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Không tìm thấy sân nào cho địa điểm này.',
                'data' => []
            ]);
        }

        $courtIds = $courts->pluck('id');
        $availabilities = Availability::whereIn('court_id', $courtIds)
            ->where('date', $date)
            ->with(['flashSaleItem' => function ($query) {
                $query->whereHas('campaign', function ($q) {
                    $q->where('status', 'active');
                });
            }])
            ->get()
            ->groupBy('court_id')
            ->map(fn($items) => $items->keyBy('slot_id'));
        foreach ($courts as $court) {
            $courtAvailabilities = $availabilities->get($court->id, collect());

            foreach ($court->timeSlots as $slot) {
                $availability = $courtAvailabilities->get($slot->id);

                $slot->availability_id = $availability ? $availability->id : null;
                $slot->status          = $availability ? $availability->status : 'unavailable'; 
                $slot->price           = $availability ? $availability->price : null;

                // Xử lý Flash Sale
                if ($availability && $availability->flashSaleItem) {
                    $slot->sale_price    = $availability->flashSaleItem->sale_price;
                    $slot->is_flash_sale = true;
                } else {
                    $slot->sale_price    = null;
                    $slot->is_flash_sale = false;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sân và lịch trống thành công',
            'data' => $courts,
        ]);
    }
}