<?php


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'items.court.venue', 'items.slot'])
            ->orderBy('created_at', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $bookings = $query->paginate(10);
        return view('bookings.index', compact('bookings'));
    }

    public function show($id)
    {
        $booking = Ticket::with(['user', 'items.court.venue', 'items.slot', 'promotion'])
            ->findOrFail($id);
        return view('bookings.show', compact('booking'));
    }

    public function edit($id)
    {
        $booking = Ticket::with(['user', 'items.court.venue', 'items.slot', 'promotion'])
            ->findOrFail($id);
        return view('bookings.edit', compact('booking'));
    }

    public function create()
    {
        // Redirect to index since booking creation is handled elsewhere
        return redirect()->route('admin.bookings.index')
            ->with('info', 'Tạo booking mới thông qua form đặt sân');
    }

    public function store(Request $request)
    {
        // Redirect to index since booking creation is handled elsewhere
        return redirect()->route('admin.bookings.index')
            ->with('info', 'Tạo booking mới thông qua form đặt sân');
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

    public function destroy($id)
    {
        $booking = Ticket::findOrFail($id);
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Xóa booking thành công!');
    }
}
