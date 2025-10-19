<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Venue, Court};

class CourtSeeder extends Seeder
{
    public function run(): void
    {
        $venues = Venue::all();
        foreach ($venues as $venue) {
            Court::factory()->count(3)->create([
                'venue_id' => $venue->id,
            ]);
        }
    }
}
