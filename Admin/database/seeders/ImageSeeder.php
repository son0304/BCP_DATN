<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Venue, Court, Image};

class ImageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Venue::all() as $venue) {
            // primary image
            Image::factory()->create([
                'venue_id' => $venue->id,
                'is_primary' => true,
            ]);
            // additional non-primary images
            Image::factory()->count(1)->create([
                'venue_id' => $venue->id,
                'is_primary' => false,
            ]);
        }

        foreach (Court::all() as $court) {
            // primary image
            Image::factory()->create([
                'court_id' => $court->id,
                'is_primary' => true,
            ]);
            // additional non-primary images
            Image::factory()->count(1)->create([
                'court_id' => $court->id,
                'is_primary' => false,
            ]);
        }
    }
}
