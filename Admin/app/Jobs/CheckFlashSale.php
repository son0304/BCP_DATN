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
            \Illuminate\Support\Facades\DB::transaction(function () use ($campaign) {

                // 1. Cập nhật trạng thái Campaign
                $campaign->status = $this->status;
                $campaign->save();

                $campaign->items()->update(['status' => $this->status]);
            });

            Log::info("Đã đồng bộ trạng thái {$this->status} cho Campaign #{$this->campaignId} và tất cả Items.");
        }
    }
}