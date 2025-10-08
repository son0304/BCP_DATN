<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;
    protected $fillable = ['user_id','court_id','time_slot_id','date','status'];
    public function court()
    {
        return $this->belongsTo(Court::class, 'court_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function time_slot()
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }
    
    
    

}