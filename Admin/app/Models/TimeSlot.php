<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    /** @use HasFactory<\Database\Factories\TimeSlotFactory> */
    use HasFactory;
    protected $fillable = [
        'start_time', 'end_time','label'   
    ];

    public function bookings (){
        return $this->hasMany(Booking::class);
    } 
    public function availability (){
        return $this->hasMany(Availability::class);
    } 
    public function items (){
        return $this->hasMany(Item::class);
    } 
}