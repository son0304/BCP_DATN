<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        $status = $request->input('status');
        $venueId = $request->input('venue');

        if ($user->role !== 'venue_owner') {
            // abort(403);

            $query = Ticket::with([
                'user',
                'items.booking.court.venue',
                'items.booking.timeSlot',
            ])->whereHas('items.booking.court.venue', function ($q) use ($user, $venueId) {
                $q->where('owner_id', $user->id);
                if ($venueId) {
                    $q->where('id', $venueId);
                }
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        $venues = Venue::where('owner_id', $user->id)->get();


         return view('venue_owner.bookings.index', compact('tickets', 'search', 'status', 'venues', 'venueId'));
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status,
        ]);

        return redirect()->back()->with('success', 'Cập nhật đơn hàng thành công!');
    }
}
