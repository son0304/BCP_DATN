<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FlashSaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlashSaleItemController extends Controller
{
    public function create_flash_sale_items(Request $request)
    {
        Log::info('Received request to create flash sale items', ['request' => $request->all()]);
        $validatedData = $request->validate([
            'campaign_id' => 'required|exists:flash_sale_campaigns,id',
            'availability_ids' => 'required|array',
            'availability_ids.*' => 'exists:availabilities,id',
            'sale_price' => 'required|numeric|min:0',
        ]);
        foreach ($validatedData['availability_ids'] as $availability_id) {
            Log::info('Creating flash sale item', [
                'campaign_id' => $validatedData['campaign_id'],
                'availability_id' => $availability_id,
                'sale_price' => $validatedData['sale_price'],
            ]);
            FlashSaleItem::create([
                'campaign_id' => $validatedData['campaign_id'],
                'availability_id' => $availability_id,
                'sale_price' => $validatedData['sale_price'],
            ]);
        }
    }
}