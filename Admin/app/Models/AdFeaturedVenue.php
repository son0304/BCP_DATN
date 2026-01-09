<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdFeaturedVenue extends Model
{
    protected $fillable = ['venue_id', 'purchase_id', 'priority_point', 'end_at'];

    protected $casts = [
        'end_at' => 'datetime',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
