<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Court, TimeSlot, Availability};
use Illuminate\Support\Carbon;

class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $courts = Court::all();
        foreach ($courts as $court) {
            $slotIds = TimeSlot::where('court_id', $court->id)->pluck('id');
            foreach (range(1, 7) as $day) {
                foreach ($slotIds as $slotId) {
                    Availability::firstOrCreate([
                        'court_id' => $court->id,
                        'date' => now()->addDays($day)->toDateString(),
                        'slot_id' => $slotId,
                    ], [
                        'status' => 'open',
                        'note' => null,
                    ]);
                }
            }
        }
    }
}
