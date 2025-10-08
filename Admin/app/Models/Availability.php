<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = ['court_id', 'slot_id', 'date', 'status', 'note'];

    public function court()
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }
}