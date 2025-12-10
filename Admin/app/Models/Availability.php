<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'court_id',
        'slot_id',
        'price',
        'date',
        'status',
        'note',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }
    public function flashSaleItem()
    {
        return $this->hasOne(FlashSaleItem::class, 'availability_id');
    }
}
