<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // public function index(Request $request)
    // {
    //     // Lấy từ input tìm kiếm
    //     $search = $request->input('search');

    //     $tickets = Ticket::with(['user', 'items.booking.court', 'items.booking.timeSlot'])
    //         ->when($search, function ($query, $search) {
    //             $query->whereHas('user', function ($q) use ($search) {
    //                 $q->where('name', 'like', "%$search%");
    //             });
    //         })
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(10); // 10 ticket/trang

    //     return view('bookings.index', compact('tickets', 'search'));
    // }
    public function index(Request $request)
    {
        $query = Ticket::with('user', 'items.booking.court', 'items.booking.timeSlot');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $tickets = $query->latest()->paginate(10);

        return view('bookings.index', compact('tickets'))
            ->with('search', $request->search);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'payment_status' => 'required|string',
        ]);

        $ticket = \App\Models\Ticket::findOrFail($id);

        $ticket->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status,
        ]);

        return redirect()->back()->with('success', 'Cập nhật đơn hàng thành công!');
    }
}
