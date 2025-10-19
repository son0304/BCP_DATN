<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = ['court_id', 'start_time', 'end_time', 'label'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    
    public function availabilities()
    {
        return $this->hasMany(Availability::class, 'slot_id');
    }
    
    public function items()
    {
        return $this->hasMany(Item::class);
    }
    
    public function court()
    {
        return $this->belongsTo(Court::class, 'court_id');
    }
}