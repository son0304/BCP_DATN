<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Court;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hour = $this->faker->numberBetween(6, 21);
        $start = sprintf('%02d:00', $hour);
        $end = sprintf('%02d:00', $hour + 1);
        return [
            'court_id' => Court::inRandomOrder()->value('id') ?? Court::factory(),
            'start_time' => $start,
            'end_time' => $end,
            'label' => $hour . 'h - ' . ($hour + 1) . 'h',
        ];
    }
}
