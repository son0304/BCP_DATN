<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{User, District, Province};
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
 

public function definition(): array
{
    return [
        'owner_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
        'name' => $this->faker->company(),
        'address_detail' => $this->faker->address(),
        'district_id' => District::inRandomOrder()->first()?->id ?? District::factory(),
        'province_id' => Province::inRandomOrder()->first()?->id ?? Province::factory(),
        'lat' => $this->faker->latitude(),
        'lng' => $this->faker->longitude(),
        'phone' => $this->faker->phoneNumber(),
        'is_active' => $this->faker->boolean(90),
    ];
}
}