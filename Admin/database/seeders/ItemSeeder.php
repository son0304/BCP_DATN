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
            // Tạo mới một booking cho item này để đảm bảo dữ liệu sạch
            // (Hoặc bạn có thể lấy booking có sẵn chưa được gán)
            $booking = Booking::factory()->create();

            Item::create([
                'ticket_id' => $ticket->id,
                'booking_id' => $booking->id,
                // Lấy giá tiền từ bảng Courts thông qua quan hệ Booking
                'unit_price' => $booking->court->price_per_hour ?? 100000,
                'discount_amount' => 0,
                'status' => 'active', // Mặc định là active
            ]);

            // Cập nhật lại tổng tiền cho Ticket (tùy chọn, để dữ liệu đẹp)
            // $ticket->refreshTotalAmount();
        }
    }
}