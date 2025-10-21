<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{Court, TimeSlot};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Availability>
 */
class AvailabilityFactory extends Factory
{
    public function definition(): array
    {
        $court = Court::inRandomOrder()->first() ?? Court::factory()->create();
        $slot = TimeSlot::where('court_id', $court->id)->inRandomOrder()->first()
                 ?? TimeSlot::factory()->create(['court_id' => $court->id]);

        return [
            'court_id' => $court->id,
            'slot_id' => $slot->id,
            'date' => now()->addDays($this->faker->numberBetween(1, 14))->toDateString(),
            'status' => $this->faker->randomElement(['open', 'booked', 'maintenance']),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
