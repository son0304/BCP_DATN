<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Venue, Service};

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Venue::all() as $venue) {
            Service::factory()->count(2)->create([
                'venue_id' => $venue->id,
            ]);
        }
    }
}
