<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Ticket, Promotion, User};

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        Ticket::factory()->count(20)->create();
    }
}
