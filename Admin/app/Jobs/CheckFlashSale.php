<?php

namespace App\Jobs;

use App\Models\FlashSaleCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckFlashSale implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */

    protected $campaignId;
    protected $status;
    public function __construct($campaignId, $status)
    {
        $this->campaignId = $campaignId;
        $this->status = $status;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $campaign = FlashSaleCampaign::find($this->campaignId);
        if ($campaign) {
            $campaign->status = $this->status;
            $campaign->save();
            if ($this->status == 'inactive') {
                $campaign->flashSaleItems()->update(['status' => 'inactive']);
            }
            Log::info("QUEUE SUCCESS: Đã cập nhật chiến dịch ID {$this->campaignId} sang trạng thái: {$this->status}");
        } else {
            Log::error("QUEUE FAILED: Không tìm thấy chiến dịch ID {$this->campaignId}");
        }
    }
}