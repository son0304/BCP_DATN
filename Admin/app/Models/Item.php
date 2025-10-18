<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'booking_id', 'unit_price', 'discount_amount'];

    protected $appends = ['is_booking'];


    public function getIsBookingAttribute()
    {
        $date = request('date', now()->toDateString());

        $booking = Booking::where('court_id', $this->court_id)
            ->where('time_slot_id', $this->id)
            ->where('date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        return $booking?->status;
    }
    public function court()
    {
        return $this->booking->court();
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
