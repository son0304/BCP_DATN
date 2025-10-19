<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Venue, District, Province, User};

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $ownerIds = User::pluck('id')->all();
        if (empty($ownerIds)) {
            return;
        }

        Venue::factory()->count(10)->create();
    }
}
