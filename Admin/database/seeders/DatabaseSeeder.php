<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ProvinceSeeder::class,
            DistrictSeeder::class,
            UserSeeder::class,
            VenueTypeSeeder::class,
            VenueSeeder::class,
            VenueVenueTypeSeeder::class,
            CourtSeeder::class,
            TimeSlotSeeder::class,
            AvailabilitySeeder::class,
            ImageSeeder::class,
            ServiceSeeder::class,
            PromotionSeeder::class,
            TicketSeeder::class,
            ItemSeeder::class,
            BookingSeeder::class,
            ReviewSeeder::class,
        ]);
    }
}
