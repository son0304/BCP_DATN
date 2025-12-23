<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_categories';

    protected $fillable = [
        'owner_id',
        'venue_type_id',
        'name',
        'description',
    ];

    // Quan hệ: 1 Danh mục có nhiều Dịch vụ
    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    // SCOPE QUAN TRỌNG: Lấy danh mục phù hợp cho 1 Sân
    // Logic: Lấy danh mục chung (null) HOẶC danh mục đúng loại sân (bóng đá/cầu lông)
    public function scopeForVenue($query, $ownerId, $venueTypeId)
    {
        return $query->where('owner_id', $ownerId)
            ->where(function ($q) use ($venueTypeId) {
                $q->whereNull('venue_type_id')
                    ->orWhere('venue_type_id', $venueTypeId);
            });
    }
}