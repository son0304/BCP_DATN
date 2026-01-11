<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\MoneyFlow;
use App\Models\Ticket;
use App\Models\Wallet;
use App\Models\WalletLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
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
        Log::info('--- AUTO COMPLETE JOB START ---', ['ticket_id' => $this->ticketId]);

        try {
            DB::transaction(function () {
                // 1. Lấy Ticket và Khóa dòng dữ liệu (Lock)
                $ticket = Ticket::with(['items'])->lockForUpdate()->find($this->ticketId);

                if (!$ticket) {
                    Log::error("Ticket #{$this->ticketId} không tồn tại.");
                    return;
                }

                // 2. Kiểm tra trạng thái (Tránh xử lý 2 lần)
                if (in_array($ticket->status, ['completed', 'cancelled'])) {
                    Log::warning("Ticket #{$this->ticketId} đã ở trạng thái kết thúc ({$ticket->status}).");
                    return;
                }

                // 3. Xác định ID chủ sân (Sử dụng hàm có sẵn trong Model Ticket)
                $venueOwnerId = $ticket->getOwnerId();

                if (!$venueOwnerId) {
                    throw new \Exception("Không tìm thấy thông tin chủ sân cho Ticket #{$this->ticketId}");
                }

                // 4. Cập nhật Ticket sang Completed
                $ticket->update(['status' => 'completed']);

                // 5. Cập nhật các Bookings liên quan (Lấy từ items của ticket)
                $bookingIds = $ticket->items->pluck('booking_id')->filter()->unique()->toArray();
                if (!empty($bookingIds)) {
                    Booking::whereIn('id', $bookingIds)->update(['status' => 'completed']);
                }

                // 6. Cập nhật Money Flows
                // LOGIC QUAN TRỌNG:
                // Vì Model MoneyFlow dùng quan hệ đa hình (money_flowable), và Ticket có quan hệ morphMany.
                // Nên "ticket_id" trong DB chính là cột "money_flowable_id" khi type là Ticket.
                MoneyFlow::where('money_flowable_id', $this->ticketId)
                    ->where('money_flowable_type', Ticket::class)
                    ->update(['status' => 'completed']);

                // 7. Tính toán tiền bạc (Dựa trên dòng tiền của Ticket này)
                $queryMoneyFlow = MoneyFlow::where('money_flowable_id', $this->ticketId)
                    ->where('money_flowable_type', Ticket::class);

                $ownerRevenue = $queryMoneyFlow->sum('venue_owner_amount');
                $adminFee     = $queryMoneyFlow->sum('admin_amount');

                $finalAmount = 0;
                $logMessage = '';
                $type = '';

                // Xác định số tiền cộng/trừ ví
                if ($ticket->payment_method === 'cash') {
                    // Tiền mặt: Khách trả chủ sân -> Trừ phí sàn của chủ sân
                    $finalAmount = -$adminFee;
                    $type = 'payment';
                    $logMessage = "Hệ thống trừ phí sàn Ticket #{$ticket->id} - Khách trả tiền mặt";
                } else {
                    // Online: Tiền đang ở sàn -> Cộng doanh thu cho chủ sân
                    $finalAmount = $ownerRevenue;
                    $type = 'deposit';
                    $logMessage = "Hệ thống cộng doanh thu Ticket #{$ticket->id}";
                }

                // 8. Cập nhật Ví chủ sân
                if ($finalAmount != 0) {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $venueOwnerId],
                        ['balance' => 0]
                    );

                    $beforeBalance = $wallet->balance;

                    // increment hoạt động đúng với cả số âm (thành trừ tiền)
                    $wallet->increment('balance', $finalAmount);

                    // Ghi log biến động số dư
                    WalletLog::create([
                        'wallet_id'      => $wallet->id,
                        'before_balance' => $beforeBalance,
                        'after_balance'  => $beforeBalance + $finalAmount,
                        'amount'         => $finalAmount,
                        'type'           => $type,
                        'description'    => $logMessage
                    ]);

                    Log::info("AUTO COMPLETE SUCCESS: Ticket #{$ticket->id}, Ví thay đổi: {$finalAmount}, Owner ID: {$venueOwnerId}");
                }
            });
        } catch (\Exception $e) {
            Log::error("Lỗi AutoCompleteTicketJob (Ticket #{$this->ticketId}): " . $e->getMessage());
            throw $e;
        }
    }
}