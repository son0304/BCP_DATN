<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Province;
use App\Models\District;
use App\Models\VenueType;
use App\Models\Venue;
use App\Models\VenueVenueType;
use App\Models\Court;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\Availability;
use App\Models\Booking;
use App\Models\Promotion;
use App\Models\Ticket;
use App\Models\Item;
use App\Models\Review;
use App\Models\Image;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // ==========================
        // Roles
        // ==========================
        $roles = ['Admin', 'Manager', 'Customer'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => $roleName.' role', 'created_at' => now(), 'updated_at' => now()]
            );
        }
        $roleIds = Role::pluck('id')->toArray();

        // ==========================
        // Users
        // ==========================
        $users = [];
        foreach (range(1, 5) as $i) {
            $users[] = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password'),
                'role_id' => $faker->randomElement($roleIds),
                'phone' => $faker->phoneNumber,
                'district_id' => null,
                'province_id' => null,
                'lat' => $faker->latitude,
                'lng' => $faker->longitude,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $userIds = collect($users)->pluck('id')->toArray();

        // ==========================
        // Provinces & Districts
        // ==========================
        $provinces = Province::factory(5)->create();
        foreach ($provinces as $province) {
            District::factory(5)->create(['province_id' => $province->id]);
        }
        $provinceIds = Province::pluck('id')->toArray();
        $districtIds = District::pluck('id')->toArray();

        // ==========================
        // Venue Types
        // ==========================
        $venueTypes = VenueType::factory(3)->create();
        $venueTypeIdsAll = $venueTypes->pluck('id')->toArray();

        // ==========================
        // Time Slots
        // ==========================
        $timeSlotLabels = [
            ['start' => '08:00:00', 'end' => '10:00:00', 'label' => '8h - 10h'],
            ['start' => '10:00:00', 'end' => '12:00:00', 'label' => '10h - 12h'],
            ['start' => '12:00:00', 'end' => '14:00:00', 'label' => '12h - 14h'],
            ['start' => '14:00:00', 'end' => '16:00:00', 'label' => '14h - 16h'],
            ['start' => '16:00:00', 'end' => '18:00:00', 'label' => '16h - 18h'],
        ];
        foreach ($timeSlotLabels as $ts) {
            TimeSlot::firstOrCreate(
                ['label' => $ts['label']],
                ['start_time' => $ts['start'], 'end_time' => $ts['end'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
        $timeSlotIds = TimeSlot::pluck('id')->toArray();

        // ==========================
        // Venues
        // ==========================
        $venues = Venue::factory(10)->create([
            'district_id' => $faker->randomElement($districtIds),
            'province_id' => $faker->randomElement($provinceIds),
            'owner_id' => $faker->randomElement($userIds),
        ]);

        foreach ($venues as $venue) {
            // Venue Types
            $venueTypeIds = $faker->randomElements($venueTypeIdsAll, rand(1,2));
            foreach ($venueTypeIds as $vtId) {
                VenueVenueType::firstOrCreate([
                    'venue_id' => $venue->id,
                    'venue_type_id' => $vtId
                ]);
            }

            // Courts
            foreach (range(1,3) as $i) {
                $court = Court::create([
                    'venue_id' => $venue->id,
                    'venue_type_id' => $faker->randomElement($venueTypeIds),
                    'name' => 'Court '.$i,
                    'surface' => $faker->randomElement(['Cỏ', 'Cỏ nhân tạo', 'Gạch']),
                    'price_per_hour' => $faker->numberBetween(100000,500000),
                    'is_indoor' => $faker->boolean,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Availability
                foreach (range(1,5) as $dayOffset) {
                    foreach ($timeSlotIds as $slotId) {
                        Availability::firstOrCreate([
                            'court_id' => $court->id,
                            'date' => now()->addDays($dayOffset)->toDateString(),
                            'slot_id' => $slotId
                        ], [
                            'status' => $faker->randomElement(['open','closed','booked']),
                            'note' => $faker->sentence(5),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Court Images
                foreach (range(1,2) as $i) {
                    Image::firstOrCreate([
                        'court_id' => $court->id,
                        'url' => 'https://picsum.photos/200?random='.$faker->unique()->numberBetween(1000,2000)
                    ], [
                        'venue_id' => null,
                        'description' => $faker->sentence(3),
                        'is_primary' => $i===1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Services
            foreach (range(1,2) as $i) {
                Service::create([
                    'venue_id' => $venue->id,
                    'name' => $faker->word.' Service',
                    'unit' => $faker->randomElement(['chai','giờ','quả']),
                    'price' => $faker->numberBetween(10000,50000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Venue Images
            foreach (range(1,2) as $i) {
                Image::create([
                    'venue_id' => $venue->id,
                    'court_id' => null,
                    'url' => 'https://picsum.photos/200?random='.$faker->unique()->numberBetween(1,1000),
                    'description' => $faker->sentence(3),
                    'is_primary' => $i===1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ==========================
        // Promotions
        // ==========================
        $promotions = [];
        foreach (range(1,5) as $i) {
            $promotions[] = Promotion::create([
                'code' => 'PROMO'.$i,
                'value' => $faker->numberBetween(5,50),
                'type' => $faker->randomElement(['%','VND']),
                'start_at' => now()->subDays(1),
                'end_at' => now()->addDays(30),
                'usage_limit' => $faker->numberBetween(5,20),
                'used_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $promotionsIds = collect($promotions)->pluck('id')->toArray();

        // ==========================
        // Tickets & Items
        // ==========================
        foreach (range(1,20) as $i) {
            $userId = $faker->randomElement($userIds);
            $promotionId = $faker->randomElement($promotionsIds);

            $ticket = Ticket::create([
                'user_id' => $userId,
                'promotion_id' => $promotionId,
                'subtotal' => $faker->numberBetween(100000,500000),
                'discount_amount' => $faker->numberBetween(0,50000),
                'total_amount' => $faker->numberBetween(50000,500000),
                'status' => $faker->randomElement(['pending','confirmed','cancelled']),
                'payment_status' => $faker->randomElement(['unpaid','paid']),
                'notes' => $faker->sentence(5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $court = Court::inRandomOrder()->first();
            $timeSlotId = $faker->randomElement($timeSlotIds);

            Item::create([
                'ticket_id' => $ticket->id,
                'court_id' => $court->id,
                'date' => now()->addDays(rand(1,10))->toDateString(),
                'slot_id' => $timeSlotId,
                'unit_price' => $court->price_per_hour,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==========================
        // Bookings
        // ==========================
        foreach (range(1,20) as $i) {
            Booking::create([
                'user_id' => $faker->randomElement($userIds),
                'court_id' => Court::inRandomOrder()->first()->id,
                'time_slot_id' => $faker->randomElement($timeSlotIds),
                'date' => now()->addDays(rand(1,10))->toDateString(),
                'status' => $faker->randomElement(['pending','confirmed','cancelled']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ==========================
        // Reviews
        // ==========================
        foreach (range(1,30) as $i) {
            Review::create([
                'user_id' => $faker->randomElement($userIds),
                'venue_id' => Venue::inRandomOrder()->first()->id,
                'rating' => $faker->numberBetween(1,5),
                'comment' => $faker->sentence(8),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}