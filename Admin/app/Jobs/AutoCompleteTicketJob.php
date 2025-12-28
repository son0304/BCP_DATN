<?php

namespace App\Jobs;

use App\Models\MoneyFlow;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCompleteTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $ticketId;

    public function __construct(int $ticketId)
    {
        $this->ticketId = $ticketId;
    }

    public function handle(): void
    {
        Log::info('AUTO COMPLETE JOB START', [
            'ticket_id' => $this->ticketId
        ]);

        $ticket = Ticket::find($this->ticketId);
        $money_flows = MoneyFlow::where('ticket_id', $this->ticketId)->get();
        if (!$ticket) {
            Log::error("Ticket {$this->ticketId} không tồn tại");
            return;
        }

        // Nếu cần điều kiện
        // if ($ticket->status !== 'checkin') return;

        $ticket->update([
            'status' => 'completed'
        ]);
        $money_flows->update([
            'status' => 'completed'
        ]);

        Log::info("Ticket {$ticket->id} COMPLETED");
    }
}