<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::with(['user', 'promotion', 'items.court.venue', 'items.booking.timeSlot'])
            ->latest()
            ->paginate(15);

        return view('tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['user', 'promotion', 'items.court.venue', 'items.booking.timeSlot']);
        
        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $users = User::orderBy('name')->get();
        $promotions = Promotion::where('is_active', true)->orderBy('name')->get();
        
        $ticket->load(['user', 'promotion', 'items.court.venue', 'items.booking.timeSlot']);
        
        return view('tickets.edit', compact('ticket', 'users', 'promotions'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'payment_status' => 'required|in:pending,paid,refunded',
            'notes' => 'nullable|string|max:1000',
        ]);

        $ticket->update($validatedData);

        return redirect()->route('admin.tickets.index')
            ->with('success', 'Cập nhật vé thành công!');
    }

    public function destroy(Ticket $ticket)
    {
        DB::beginTransaction();
        try {
            // Delete related items first
            $ticket->items()->delete();
            
            // Delete the ticket
            $ticket->delete();
            
            DB::commit();
            
            return redirect()->route('admin.tickets.index')
                ->with('success', 'Xóa vé thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi xóa vé: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $ticket->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công!',
            'ticket' => $ticket->fresh()
        ]);
    }
}
