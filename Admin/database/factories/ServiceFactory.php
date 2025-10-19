<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Venue;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venue_id' => Venue::inRandomOrder()->value('id') ?? Venue::factory(),
            'name' => $this->faker->randomElement(['Nước suối', 'Khăn', 'Thuê giày', 'Bóng']),
            'unit' => $this->faker->randomElement(['chai', 'cái', 'giờ', 'gói']),
            'price' => $this->faker->numberBetween(10000, 80000),
        ];
    }
}
