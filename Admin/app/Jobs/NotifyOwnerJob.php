<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\Notification; // Nhớ import Model Notification
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Nhớ import Str để tạo UUID

class NotifyOwnerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        // Load user để lấy tên khách hàng
        $this->ticket = $ticket->load('user');
    }

    public function handle(): void
    {
        $ownerId = $this->ticket->getOwnerId();
        if (!$ownerId || $this->ticket->status === 'canceled') return;

        // Tránh gửi trùng nếu job retry
        $exists = Notification::where('user_id', $ownerId)
            ->where('type', 'warning')
            ->where('data->booking_id', $this->ticket->id)
            ->exists();

        if (!$exists) {
            Notification::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'user_id' => $ownerId,
                'type' => 'warning',
                'title' => 'Sắp hết giờ',
                'message' => "Đơn hàng #{$this->ticket->id} sắp hết thời gian.",
                'data' => [
                    'booking_id' => $this->ticket->id,
                    'link' => '/owner/bookings?search=' . $this->ticket->booking_code,
                ],
            ]);
        }
    }
}