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
                // 1. Lấy Ticket và Khóa hàng (Lock)
                $ticket = Ticket::with(['items.booking.court.venue'])->lockForUpdate()->find($this->ticketId);

                if (!$ticket) {
                    Log::error("Ticket #{$this->ticketId} không tồn tại.");
                    return;
                }

                // 2. Kiểm tra trạng thái (Idempotency)
                if (in_array($ticket->status, ['completed', 'cancelled'])) {
                    Log::warning("Ticket #{$this->ticketId} đã ở trạng thái kết thúc ({$ticket->status}).");
                    return;
                }

                // 3. Xác định thông tin chủ sân
                $firstItem = $ticket->items->first();
                if (!$firstItem || !$firstItem->booking || !$firstItem->booking->court || !$firstItem->booking->court->venue) {
                    throw new \Exception("Không tìm thấy thông tin chủ sân cho Ticket #{$this->ticketId}");
                }

                $venue = $firstItem->booking->court->venue;
                $venueOwnerId = $venue->owner_id;

                // 4. Cập nhật Ticket sang Completed
                $ticket->update(['status' => 'completed']);

                // 5. Cập nhật các Bookings liên quan
                $bookingIds = $ticket->items->pluck('booking_id')->filter()->unique()->toArray();
                if (!empty($bookingIds)) {
                    Booking::whereIn('id', $bookingIds)->update(['status' => 'completed']);
                }

                // 6. Cập nhật Money Flows (Cột booking_id trong DB là ticket_id)
                MoneyFlow::where('booking_id', $this->ticketId)->update(['status' => 'completed']);

                // 7. Tính toán tiền bạc
                // venue_owner_amount: Số tiền chủ sân nhận được (sau khi trừ phí)
                // admin_amount: Số tiền phí sàn
                $ownerRevenue = MoneyFlow::where('booking_id', $this->ticketId)->sum('venue_owner_amount');
                $adminFee = MoneyFlow::where('booking_id', $this->ticketId)->sum('admin_amount');

                $finalAmount = 0;
                $logMessage = '';
                $type = '';

                if ($ticket->payment_method === 'cash') {
                    // Khách trả tiền mặt: Chủ sân đã cầm tiền -> Hệ thống trừ phí sàn trong ví
                    $finalAmount = -$adminFee;
                    $type = 'payment'; // Ghi nhận là một khoản thanh toán phí
                    $logMessage = "Hệ thống tự động trừ phí sàn Ticket #{$ticket->id} (Sân: {$venue->name}) - Khách trả tiền mặt";
                } else {
                    // Khách trả Online: Hệ thống giữ tiền -> Cộng tiền thực nhận vào ví chủ sân
                    $finalAmount = $ownerRevenue;
                    $type = 'deposit';
                    $logMessage = "Hệ thống tự động cộng doanh thu Ticket #{$ticket->id} (Sân: {$venue->name})";
                }

                // 8. Cập nhật Ví chủ sân
                if ($finalAmount != 0) {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $venueOwnerId],
                        ['balance' => 0]
                    );

                    $beforeBalance = $wallet->balance;

                    // increment() tự động xử lý được số âm (sẽ thành phép trừ)
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

                    Log::info("AUTO COMPLETE SUCCESS: Ticket #{$ticket->id}, Số tiền: {$finalAmount}, Chủ sân: #{$venueOwnerId}");
                }
            });
        } catch (\Exception $e) {
            Log::error("Lỗi AutoCompleteTicketJob (Ticket #{$this->ticketId}): " . $e->getMessage());
            // Quăng lỗi ra để Queue có thể retry nếu cấu hình
            throw $e;
        }
    }
}