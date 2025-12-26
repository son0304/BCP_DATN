<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'venue_type_id',
        'name',
        'surface',
        'process_status',
        'is_indoor'
    ];

    // Time slots are directly linked to courts
    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class, 'court_id');
    }
    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'court_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function venueType()
    {
        return $this->belongsTo(VenueType::class, 'venue_type_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'court_id');
    }
}