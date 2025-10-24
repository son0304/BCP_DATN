<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
}
