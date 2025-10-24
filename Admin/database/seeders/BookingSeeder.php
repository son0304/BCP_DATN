<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Booking, Court, TimeSlot, User};

class BookingSeeder extends Seeder
{
public function run(): void
{
$users = User::pluck('id')->all();

foreach (range(1, 50) as $i) { // seed nhiều hơn để có dữ liệu phong phú
$court = Court::inRandomOrder()->first();
if (!$court) continue;

$slot = TimeSlot::where('court_id', $court->id)->inRandomOrder()->first();
if (!$slot) continue;

Booking::create([
'user_id' => $users[array_rand($users)] ?? null,
'court_id' => $court->id,
'time_slot_id' => $slot->id,
'date' => now()->addDays(rand(1, 10))->toDateString(),
'status' => 'pending',
]);
}
}
}