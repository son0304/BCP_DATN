<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead($id)
    {
        Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}