<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{User, Court, TimeSlot};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $courtId = Court::inRandomOrder()->value('id') ?? Court::factory();
        $slotId = TimeSlot::where('court_id', $courtId)->inRandomOrder()->value('id') ?? TimeSlot::factory();
        return [
            'user_id' => User::inRandomOrder()->value('id') ?? User::factory(),
            'court_id' => $courtId,
            'time_slot_id' => $slotId,
            'date' => now()->addDays($this->faker->numberBetween(1, 14))->toDateString(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
        ];
    }
}
