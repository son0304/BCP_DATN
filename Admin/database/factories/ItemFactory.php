<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{Ticket, Court, TimeSlot};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
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
            'ticket_id' => Ticket::inRandomOrder()->value('id') ?? Ticket::factory(),
            'court_id' => $courtId,
            'date' => now()->addDays($this->faker->numberBetween(1, 14))->toDateString(),
            'slot_id' => $slotId,
            'unit_price' => $this->faker->numberBetween(100000, 500000),
        ];
    }
}
