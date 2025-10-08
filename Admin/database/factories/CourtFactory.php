<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{Venue, VenueType};
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Court>
 */
class CourtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */


    public function definition(): array
    {
        return [
            'venue_id' => Venue::inRandomOrder()->first()?->id ?? Venue::factory(),
            'venue_type_id' => VenueType::inRandomOrder()->first()?->id ?? VenueType::factory(),
            'name' => 'Sân ' . $this->faker->word(),
            'surface' => $this->faker->randomElement(['Cỏ nhân tạo', 'Sàn gỗ', 'Bê tông']),
            'price_per_hour' => $this->faker->randomFloat(2, 100000, 500000),
            'is_indoor' => $this->faker->boolean(),
        ];
    }
}