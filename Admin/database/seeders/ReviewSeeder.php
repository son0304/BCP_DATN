<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Review, Venue, User};

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $venueIds = Venue::pluck('id')->all();
        $userIds = User::pluck('id')->all();
        if (empty($venueIds) || empty($userIds)) return;

        Review::factory()->count(30)->create();
    }
}
