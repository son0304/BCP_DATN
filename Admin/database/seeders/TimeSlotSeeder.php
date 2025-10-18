<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Court, TimeSlot};

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $courts = Court::all();

        foreach ($courts as $court) {
            for ($hour = 6; $hour < 22; $hour++) {
                $start = sprintf('%02d:00', $hour);
                $end = sprintf('%02d:00', $hour + 1);

                TimeSlot::firstOrCreate([
                    'court_id' => $court->id,
                    'start_time' => $start,
                    'end_time' => $end,
                ], [
                    'label' => $hour . 'h - ' . ($hour + 1) . 'h',
                ]);
            }
        }
    }
}
