<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = auth()->user();

        $tickets = Ticket::with([
            'user',
            'items.booking.court.venue',
            'items.booking.timeSlot',
        ])
            ->whereHas('items.booking.court.venue', function ($q) use ($user) {
                $q->where('user_id', $user->id); 
            })
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bookings.index', compact('tickets', 'search'));
    }
}