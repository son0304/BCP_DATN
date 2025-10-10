<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueVenueType extends Model
{
    /** @use HasFactory<\Database\Factories\VenueVenueTypeFactory> */
    use HasFactory;
    protected $fillable = [
        'venue_id',
        'venue_type_id'
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }
    public function venue_type()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }
}