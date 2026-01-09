<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdBanner extends Model
{
    protected $fillable = [
        'venue_id',
        'purchase_id',
        'title',
        'target_url',
        'position',
        'priority',
        'start_at',
        'end_at',
        'is_active'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
