<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Item, Booking, Ticket};

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = Ticket::all();

        foreach ($tickets as $ticket) {
            // Chọn một booking ngẫu nhiên cho ticket
            $booking = Booking::inRandomOrder()->first();
            if (!$booking) continue;

            Item::create([
                'ticket_id' => $ticket->id,
                'booking_id' => $booking->id,
                'unit_price' => $booking->court->price_per_hour,
                'discount_amount' => 0,
            ]);
        }
    }
}