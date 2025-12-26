<?php

namespace App\Helpers;

use App\Events\SidebarNoti;

class BroadcastHelper
{
    public static function
    updateCount($channel, $event, $count)
    {
        broadcast(new SidebarNoti($channel, $event, ['count' => $count]));
    }
}