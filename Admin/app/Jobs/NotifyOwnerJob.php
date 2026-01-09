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
        try {
            $customerName = $this->ticket->user->name ?? 'Khách';
            $msg = "Đơn hàng #{$this->ticket->id} của khách {$customerName} sắp hết thời gian chơi (còn 10 phút).";

            Log::info("THÔNG BÁO HẾT GIỜ: " . $msg);
            $ownerId = $this->ticket->getOwnerId();

            if ($ownerId && $this->ticket->status !== 'canceled') {
                Notification::create([
                    'id' => Str::uuid(),
                    'user_id' => $ownerId,
                    'type' => 'warning',
                    'title' => 'Sắp hết giờ',
                    'message' => $msg,
                    'data' => [
                        'booking_id' => $this->ticket->id,
                        'link' => '/owner/bookings?search=' . $this->ticket->booking_code,
                    ],
                    'read_at' => null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Lỗi Job NotifyOwner: " . $e->getMessage());
        }
    }
}