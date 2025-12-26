<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $nameChannel;
    public $nameEvent;

    public function __construct($data, $nameChannel, $nameEvent)
    {
        $this->data = $data;
        $this->nameChannel = $nameChannel;
        $this->nameEvent = $nameEvent;
    }

    public function broadcastOn()
    {
        return new Channel($this->nameChannel);
    }

    public function broadcastAs()
    {
        return $this->nameEvent;
    }
}