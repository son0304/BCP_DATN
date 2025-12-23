<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCompleteTicketJob implements ShouldQueue
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
     * Thực thi logic cập nhật trạng thái
     */
    public function handle(): void
    {
        // Tải lại dữ liệu mới nhất từ DB
        $this->ticket->refresh();

        // Nếu vé vẫn đang ở trạng thái 3 (Đã Check-in) thì mới tự động hoàn thành
        if ($this->ticket->status == 3) {
            $this->ticket->update([
                'status' => 4 // Chuyển sang trạng thái 4: Hoàn thành
            ]);

            Log::info("HỆ THỐNG: Đơn hàng #{$this->ticket->id} đã tự động chuyển sang trạng thái HOÀN THÀNH do hết giờ.");
        }
    }
}