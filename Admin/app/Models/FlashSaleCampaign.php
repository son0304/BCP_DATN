<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSaleCampaign extends Model
{
    use HasFactory;

    protected $table = 'flash_sale_campaigns';

    protected $fillable = [
        'name',
        'description',
        'start_datetime',
        'end_datetime',
        'status',
        'process_status',
    ];

    // Cast dữ liệu ngày tháng để code dễ xử lý
    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    // Relationship: Một chiến dịch có nhiều Item
    public function items()
    {
        return $this->hasMany(FlashSaleItem::class, 'campaign_id');
    }

    // Scope: Lấy các chiến dịch đang diễn ra (Helper để query cho nhanh)
    public function scopeActive($query)
    {
        $now = now();
        return $query->where('status', 'active')
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>=', $now);
    }
}