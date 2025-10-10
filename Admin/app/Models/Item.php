<?php

class Item extends Model
{
    protected $fillable = ['ticket_id', 'court_id', 'date', 'slot_id', 'unit_price', 'final_price'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function slot()
    {
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }
}
