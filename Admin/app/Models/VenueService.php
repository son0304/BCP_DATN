<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot; // ✅ ĐÚNG
use Illuminate\Database\Eloquent\SoftDeletes;

class VenueService extends Pivot // ✅ PHẢI là Pivot
{
    use SoftDeletes;

    protected $table = 'venue_services';

    protected $fillable = [
        'venue_id',
        'service_id',
        'price',
        'stock',
        'status'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}