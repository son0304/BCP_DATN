<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\Court;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{

    public function updateAll(Request $request, Court $court)
    {
        $validated = $request->validate([
            'statuses' => 'present|array',
            'statuses.*' => 'required|in:open,maintenance',
        ]);

        $statuses = $validated['statuses'];

        DB::beginTransaction();
        try {
            $availabilitiesToUpdate = $court->availabilities()
                ->whereIn('status', ['open', 'maintenance'])
                ->whereIn('id', array_keys($statuses))
                ->get();

            foreach ($availabilitiesToUpdate as $availability) {

                $newStatus = $statuses[$availability->id];

                if ($availability->status !== $newStatus) {
                    $availability->status = $newStatus;
                    $availability->save();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật availability: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Đã có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại.');
        }

        return redirect()->back()->with('success', 'Cập nhật lịch hoạt động thành công!');
    }

    public function getAvailableSlots(Request $request)
    {
        try {
            $request->validate([
                'court_id' => 'required|exists:courts,id',
                'date' => 'required|date',
            ]);

            $courtId = $request->input('court_id');
            $date = $request->input('date');

            // Lấy thời gian hiện tại
            $now = now();
            $currentDate = $now->format('Y-m-d');
            $currentTime = $now->format('H:i:s');

            // Query cơ bản
            $query = Availability::select([
                'availabilities.slot_id as time_slot_id',
                'availabilities.price',
                'time_slots.start_time',
                'time_slots.end_time',
                'availabilities.status'
            ])
                ->join('time_slots', 'availabilities.slot_id', '=', 'time_slots.id')
                ->where('availabilities.court_id', $courtId)
                ->where('availabilities.date', $date)
                ->where('availabilities.status', 'open');

            // ✅ Nếu là ngày hôm nay → Chỉ lấy khung giờ chưa qua
            if ($date === $currentDate) {
                $query->where('time_slots.start_time', '>', $currentTime);
            }

            $slots = $query->orderBy('time_slots.start_time', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $slots
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải khung giờ: ' . $e->getMessage(), [
                'court_id' => $request->input('court_id'),
                'date' => $request->input('date'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi tải khung giờ.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }
}
