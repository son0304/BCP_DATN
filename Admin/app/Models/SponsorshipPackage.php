<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SponsorshipPackage extends Model
{
    protected $table = 'sponsorship_packages';

    protected $fillable = [
        'name',
        'price',
        'duration_days',
        'description',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Quan hệ 1-N: Một gói có thể chứa nhiều quyền lợi (items)
     */
    public function items(): HasMany
    {
        return $this->hasMany(SponsorshipPackageItem::class, 'sponsorship_package_id');
    }
}
