<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSaleItem extends Model
{
    use HasFactory;

    protected $table = 'flash_sale_items';

    protected $fillable = [
        'campaign_id',
        'availability_id',
        'sale_price',
        'quantity',
        'sold_count',
        'status',
    ];

    // Relationship: Item thuộc về 1 Chiến dịch
    public function campaign()
    {
        return $this->belongsTo(FlashSaleCampaign::class, 'campaign_id');
    }

    // Relationship: Item gắn với 1 Slot trong kho (Availabilities)
    public function availability()
    {
        return $this->belongsTo(Availability::class, 'availability_id');
    }

    // Hàm kiểm tra xem còn hàng để bán không
    public function isAvailable()
    {
        return $this->status === 'active' && $this->sold_count < $this->quantity;
    }
}