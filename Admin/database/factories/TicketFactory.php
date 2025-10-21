<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{User, Promotion};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::inRandomOrder()->value('id') ?? User::factory();
        $promotionId = $this->faker->boolean(60)
            ? (Promotion::inRandomOrder()->value('id') ?? Promotion::factory())
            : null;

        $subtotal = $this->faker->numberBetween(100000, 400000);
        $discount = $this->faker->numberBetween(0, (int)($subtotal * 0.3));
        $total = max(0, $subtotal - $discount);

        return [
            'user_id' => $userId,
            'promotion_id' => $promotionId,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $total,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['unpaid', 'paid']),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence(6) : null,
        ];
    }
}
