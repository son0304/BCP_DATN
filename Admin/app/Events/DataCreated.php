<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Quan trọng: Dùng Now để không đợi Queue
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Kế thừa ShouldBroadcastNow để bắn tin NGAY LẬP TỨC
class DataCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $nameChannel;
    public $nameEvent;

    // Nhận dữ liệu muốn truyền xuống View
    public function __construct($data, $nameChannel, $nameEvent)
    {
        $this->data = $data;
        $this->nameChannel = $nameChannel;
        $this->nameEvent = $nameEvent;
    }

    // Định nghĩa tên kênh (Ví dụ kênh chung 'public-updates')
    public function broadcastOn()
    {
        return new Channel($this->nameChannel);
    }

    // Đặt tên sự kiện cho dễ gọi ở JS (tùy chọn, mặc định là tên Class)
    public function broadcastAs()
    {
        return  $this->nameEvent;
    }
}