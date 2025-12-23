<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyOwnerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ticket;

    /**
     * Khởi tạo Job với đối tượng Ticket
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Thực thi logic thông báo
     */
    public function handle(): void
    {
        // Tải lại dữ liệu mới nhất từ DB để kiểm tra trạng thái thực tế
        $this->ticket->refresh();

        // Chỉ thông báo nếu vé vẫn đang ở trạng thái 3 (Đã Check-in)
        // Nếu khách đã hủy hoặc hoàn thành sớm thì không thông báo nữa
        if ($this->ticket->status == 3) {
            
            // Ở đây bạn có thể viết logic gửi thông báo:
            // 1. Gửi qua Firebase (cho App)
            // 2. Gửi qua Pusher/Socket (để hiện thông báo real-time trên Web)
            // 3. Gửi Mail hoặc Zalo
            
            // Ví dụ ghi log để kiểm tra:
            Log::info("THÔNG BÁO HẾT GIỜ: Đơn hàng #{$this->ticket->id} của khách {$this->ticket->user->name} sắp hết thời gian chơi (còn 10 phút).");
            
            // Nếu bạn dùng hệ thống Notification của Laravel:
            // $owner = $this->ticket->items->first()->booking->court->venue->owner;
            // $owner->notify(new \App\Notifications\TicketExpiringNotification($this->ticket));
        }
    }
}