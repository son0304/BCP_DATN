<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;
    protected $fillable = ['ticket_id', 'court_id', 'date', 'slot_id', 'unit_price'  ];
    
    public function ticket (){
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
    public function time_slot (){
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }
    public function court (){
        return $this->belongsTo(Court::class, 'court_id');
    }
}