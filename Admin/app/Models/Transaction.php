<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'booking_id',
        'user_id',
        'payment_source',
        'amount',
        'note',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ---- Relationships ----

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}