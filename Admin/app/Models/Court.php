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
        'price_per_hour',
        'is_indoor'
    ];

    // ✅ Thêm quan hệ mới: 1 sân có nhiều khung giờ
    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class);
    }


    public function items()
    {
        return $this->hasMany(Item::class, 'court_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'court_id');
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