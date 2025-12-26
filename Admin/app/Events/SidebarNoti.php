<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SidebarNoti
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    private $channelName;
    private $eventName;

    public function __construct($channelName, $eventName, $data)
    {
        $this->channelName = $channelName;
        $this->eventName = $eventName;
        $this->data = $data;          
    }

    public function broadcastOn(): array
    {
        return [new Channel($this->channelName)];
    }

    public function broadcastAs(): string
    {
        return $this->eventName;
    }
}