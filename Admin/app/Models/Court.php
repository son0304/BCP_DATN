<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    /** @use HasFactory<\Database\Factories\CourtFactory> */
    use HasFactory;
    protected $fillable = ['venue_id','venue_type_id','name','surface','is_indoor'];
    public function courts()
    {
        return $this->hasMany(Item::class);
    }
    public function images()
    {
        return $this->hasMany(Image::class);
    }
    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }
    public function venue_type()
    {
        return $this->belongsTo(VenueType::class, 'venue_type_id');
    }
    
    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

}