<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->bothify('KM###')),
            'value' => $this->faker->numberBetween(5, 50),
            'type' => $this->faker->randomElement(['%', 'VND']),
            'start_at' => now()->subDay(),
            'end_at' => now()->addDays(30),
            'usage_limit' => 50,
            'used_count' => 0,
        ];
    }
}
