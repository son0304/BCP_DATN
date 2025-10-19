<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // Lấy từ input tìm kiếm
        $search = $request->input('search');

        $tickets = Ticket::with(['user', 'items.booking.court', 'items.booking.timeSlot'])
            ->when($search, function($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10); // 10 ticket/trang

        return view('bookings.index', compact('tickets', 'search'));
    }
}