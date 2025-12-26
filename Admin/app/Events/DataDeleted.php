<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $nameChannel;
    public $nameEvent;

    public function __construct($id, $nameChannel, $nameEvent)
    {
        $this->id = $id;
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