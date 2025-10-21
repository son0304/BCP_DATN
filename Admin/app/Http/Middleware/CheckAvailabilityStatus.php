<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Availability;
use Carbon\Carbon;

class CheckAvailabilityStatus
{
    public function handle($request, Closure $next)
    {
        \Log::info('Middleware CheckAvailabilityStatus started');
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        $updated = Availability::where('status', 'open')
            ->whereHas('timeSlot', function ($query) use ($now) {
                $query->whereRaw("CONCAT(availabilities.date, ' ', time_slots.end_time) < ?", [$now]);
            })
            ->update(['status' => 'maintenance']);

        \Log::info('CheckAvailabilityStatus ran at ' . $now . '. Updated ' . $updated . ' records.');

        return $next($request);
    }
}
