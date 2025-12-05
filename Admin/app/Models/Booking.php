<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'court_id', 'time_slot_id', 'date', 'status'];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function item()
    {
        return $this->hasOne(Item::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}