<?php

namespace App\Jobs;

use App\Models\FlashSaleCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckFlashSale implements ShouldQueue
{
    use Queueable;

    protected $campaignId;
    protected $status;

    public function __construct($campaignId, $status)
    {
        $this->campaignId = $campaignId;
        $this->status = $status;
    }

    public function handle(): void
    {
        $campaign = FlashSaleCampaign::find($this->campaignId);

        // 1. Kiểm tra nếu Campaign đã bị xóa
        if (!$campaign) {
            return;
        }

        // 2. KIỂM TRA LOGIC THỜI GIAN (QUAN TRỌNG ĐỂ TRÁNH JOB CŨ)
        $now = now();
        // Cho phép sai số khoảng 2 phút (do server delay)
        $tolerance = 2;

        if ($this->status === 'active') {
            // Nếu Job này định bật active, nhưng thời gian bắt đầu trong DB lại ở tương lai xa hơn hiện tại
            // => Có nghĩa là User đã dời lịch bắt đầu trễ hơn. Job này là Job cũ -> BỎ QUA.
            if ($now->lessThan($campaign->start_datetime->subMinutes($tolerance))) {
                Log::info("Bỏ qua Job Active cho Campaign #{$this->campaignId} vì lịch đã bị dời sang: " . $campaign->start_datetime);
                return;
            }
        }

        if ($this->status === 'inactive') {
            // Nếu Job này định tắt (inactive), nhưng thời gian kết thúc trong DB lại ở tương lai
            // => Có nghĩa là User đã gia hạn thêm giờ. Job này là Job cũ -> BỎ QUA.
            if ($now->lessThan($campaign->end_datetime->subMinutes($tolerance))) {
                Log::info("Bỏ qua Job Inactive cho Campaign #{$this->campaignId} vì lịch đã được gia hạn đến: " . $campaign->end_datetime);
                return;
            }
        }

        // 3. Thực hiện update nếu hợp lệ
        DB::transaction(function () use ($campaign) {
            // Cập nhật trạng thái Campaign
            $campaign->status = $this->status;
            $campaign->save();

            // Cập nhật trạng thái các Item con
            // Lưu ý: Nếu inactive (hết giờ) thì set items về inactive hoặc expired tuỳ logic
            $campaign->items()->update(['status' => $this->status]);
        });

        Log::info("Đã đồng bộ trạng thái {$this->status} cho Campaign #{$this->campaignId} (Chạy đúng lịch).");
    }
}