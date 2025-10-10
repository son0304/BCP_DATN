<?php


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'venue'])
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(10);
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show($id)
    {
        $booking = Ticket::with(['user', 'venue', 'items.court', 'items.slot'])
            ->findOrFail($id);
        return view('admin.bookings.show', compact('booking'));
    }

    public function edit($id)
    {
        $booking = Ticket::findOrFail($id);
        return view('admin.bookings.edit', compact('booking'));
    }

    public function update(Request $request, $id)
    {
        $booking = Ticket::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'payment_status' => 'nullable|in:paid,unpaid,refund',
        ]);

        $booking->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status ?? $booking->payment_status,
        ]);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Cập nhật booking thành công!');
    }
}
