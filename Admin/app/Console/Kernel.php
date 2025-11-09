<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            \Log::info('Scheduled task: Checking availability status');
            \App\Models\Availability::where('status', 'open')
                ->whereHas('timeSlot', function ($query) {
                    $query->whereRaw("CONCAT(availabilities.date, ' ', time_slots.end_time) < ?", [Carbon::now('Asia/Ho_Chi_Minh')]);
                })
                ->update(['status' => 'maintenance']);
        })->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
