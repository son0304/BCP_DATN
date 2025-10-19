<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimeSlot;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timeSlots = [
            ['start_time' => '06:00:00', 'end_time' => '07:00:00', 'label' => 'Sáng sớm'],
            ['start_time' => '07:00:00', 'end_time' => '08:00:00', 'label' => 'Sáng'],
            ['start_time' => '08:00:00', 'end_time' => '09:00:00', 'label' => 'Sáng'],
            ['start_time' => '09:00:00', 'end_time' => '10:00:00', 'label' => 'Sáng'],
            ['start_time' => '10:00:00', 'end_time' => '11:00:00', 'label' => 'Sáng'],
            ['start_time' => '11:00:00', 'end_time' => '12:00:00', 'label' => 'Trưa'],
            ['start_time' => '12:00:00', 'end_time' => '13:00:00', 'label' => 'Trưa'],
            ['start_time' => '13:00:00', 'end_time' => '14:00:00', 'label' => 'Chiều'],
            ['start_time' => '14:00:00', 'end_time' => '15:00:00', 'label' => 'Chiều'],
            ['start_time' => '15:00:00', 'end_time' => '16:00:00', 'label' => 'Chiều'],
            ['start_time' => '16:00:00', 'end_time' => '17:00:00', 'label' => 'Chiều'],
            ['start_time' => '17:00:00', 'end_time' => '18:00:00', 'label' => 'Chiều'],
            ['start_time' => '18:00:00', 'end_time' => '19:00:00', 'label' => 'Tối'],
            ['start_time' => '19:00:00', 'end_time' => '20:00:00', 'label' => 'Tối'],
            ['start_time' => '20:00:00', 'end_time' => '21:00:00', 'label' => 'Tối'],
            ['start_time' => '21:00:00', 'end_time' => '22:00:00', 'label' => 'Tối'],
            ['start_time' => '22:00:00', 'end_time' => '23:00:00', 'label' => 'Tối muộn'],
        ];

        foreach ($timeSlots as $slot) {
            TimeSlot::firstOrCreate(
                ['start_time' => $slot['start_time'], 'end_time' => $slot['end_time']],
                $slot
            );
        }
    }
}