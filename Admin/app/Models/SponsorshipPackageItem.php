<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsorshipPackageItem extends Model
{
    protected $table = 'sponsorship_package_items';

    protected $fillable = [
        'sponsorship_package_id',
        'type',
        'settings'
    ];

    // Tự động chuyển JSON trong database thành Array trong PHP để dễ dùng
    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Quan hệ ngược: Item thuộc về 1 gói cha
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(SponsorshipPackage::class, 'sponsorship_package_id');
    }
}
