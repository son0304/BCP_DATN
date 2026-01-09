<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'transactionable_id', // ID của Booking hoặc SponsoredVenue
        'transactionable_type', // Class của Booking hoặc SponsoredVenue
        'user_id',
        'payment_source',
        'amount',
        'note',
        'status',
        'process_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Lấy model sở hữu giao dịch này (Booking hoặc SponsoredVenue)
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
