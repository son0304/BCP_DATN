<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyFlow extends Model
{
    protected $table = 'money_flows';

    protected $fillable = [
        'booking_id',
        'total_amount',
        'promotion_id',
        'promotion_amount',
        'venue_id',
        'admin_amount',
        'venue_owner_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'promotion_amount' => 'decimal:2',
        'admin_amount' => 'decimal:2',
        'venue_owner_amount' => 'decimal:2',
    ];

    // ---- Relationships ----

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
}
