<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{Ticket, Booking, Item};

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
        return [
            'ticket_id' => Ticket::factory(), // Tạo hoặc lấy ID từ seeder
            'booking_id' => Booking::factory(), // Tạo kèm một booking tương ứng
            'status' => $this->faker->randomElement(['active', 'refund']), // Random trạng thái
            'unit_price' => $this->faker->numberBetween(100000, 500000),
            'discount_amount' => 0,
        ];
    }
}
