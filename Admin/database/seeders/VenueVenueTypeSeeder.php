<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Venue, VenueType, VenueVenueType};

class VenueVenueTypeSeeder extends Seeder
{
    public function run(): void
    {
        $typeIds = VenueType::pluck('id')->all();
        foreach (Venue::all() as $venue) {
            $assigned = collect($typeIds)->shuffle()->take(rand(1, 2));
            foreach ($assigned as $typeId) {
                VenueVenueType::firstOrCreate([
                    'venue_id' => $venue->id,
                    'venue_type_id' => $typeId,
                ]);
            }
        }
    }
}
