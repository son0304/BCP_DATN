<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Court, TimeSlot, Availability};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; // <-- Thêm DB Facade
use Illuminate\Support\Facades\Log;

class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        // Xóa hết dữ liệu cũ để tránh trùng lặp khi chạy lại seeder
        DB::table('availabilities')->truncate();

        $courts = Court::with('timeSlots')->get();

        if ($courts->isEmpty()) {
            $this->command->warn('⚠️ Không có court nào trong DB. Hãy chạy CourtSeeder trước.');
            return;
        }

        $allAvailabilities = [];
        $now = Carbon::now();

        foreach ($courts as $court) {
            if ($court->timeSlots->isEmpty()) {
                $this->command->warn("⚠️ Court #{$court->id} ({$court->name}) chưa có TimeSlot nào.");
                continue;
            }

            // Tạo lịch cho 30 ngày tới (giống CourtController)
            foreach (range(0, 29) as $day) {
                $date = Carbon::today()->addDays($day)->toDateString();

                foreach ($court->timeSlots as $slot) {

                    $basePrice = $court->price_per_hour ?? 100000;
                    $randomPrice = $basePrice + (rand(-2, 5) * 10000);

                    $allAvailabilities[] = [
                        'court_id' => $court->id,
                        'slot_id'  => $slot->id,
                        'date'     => $date,
                        'price'    => $randomPrice, // <-- Thêm giá vào đây
                        'status'   => 'open',
                        'note'     => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // --- ĐÃ SỬA: SỬ DỤNG BULK INSERT ---
        // Chèn tất cả dữ liệu vào DB trong 1 câu lệnh duy nhất
        if (!empty($allAvailabilities)) {
            // Chia nhỏ mảng để insert nếu quá lớn (ví dụ: 500 record/lần)
            foreach (array_chunk($allAvailabilities, 500) as $chunk) {
                Availability::insert($chunk);
            }
        }

        $this->command->info('✅ Availabilities seeded thành công!');
    }
}