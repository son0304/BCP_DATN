<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Court, TimeSlot};
use Illuminate\Support\Facades\DB;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        // ⚠️ Tạm tắt ràng buộc khóa ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        TimeSlot::truncate();

        // ✅ Bật lại sau khi xóa xong
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $courts = Court::all();

        foreach ($courts as $court) {
            for ($hour = 6; $hour < 22; $hour++) {
                $start = sprintf('%02d:00', $hour);
                $end = sprintf('%02d:00', $hour + 1);
                $label = "{$start} - {$end}";

                TimeSlot::firstOrCreate([
                    'court_id' => $court->id,
                    'start_time' => $start,
                    'end_time' => $end,
                ], [
                    'label' => $label,
                ]);
            }
        }

    }
}
