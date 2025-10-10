<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        // Lấy danh sách tất cả vé đặt (booking)
        return Ticket::with(['user', 'venue', 'court'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'venue_id' => 'required',
            'court_id' => 'required',
            'date' => 'required|date',
            'time_slot_id' => 'required',
        ]);

        $ticket = Ticket::create($validated);

        return response()->json([
            'message' => 'Đặt sân thành công!',
            'data' => $ticket
        ], 201);
    }

    public function show($id)
    {
        $ticket = Ticket::with(['user', 'venue', 'court'])->findOrFail($id);
        return response()->json($ticket);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update($request->all());

        return response()->json([
            'message' => 'Cập nhật thành công!',
            'data' => $ticket
        ]);
    }

    public function destroy($id)
    {
        Ticket::findOrFail($id)->delete();
        return response()->json(['message' => 'Xóa thành công!']);
    }
}

