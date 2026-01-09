<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MoneyFlow extends Model
{
    protected $table = 'money_flows';

    protected $fillable = [
        'money_flowable_id', // ID của Booking hoặc SponsoredVenue
        'money_flowable_type', // Class của Booking hoặc SponsoredVenue
        'total_amount',
        'promotion_id',
        'promotion_amount',
        'venue_id',
        'admin_amount',
        'venue_owner_amount',
        'process_status',
        'note'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'promotion_amount' => 'decimal:2',
        'admin_amount' => 'decimal:2',
        'venue_owner_amount' => 'decimal:2',
    ];

    /**
     * Lấy model nguồn tạo ra dòng tiền này
     */
    public function money_flowable(): MorphTo
    {
        return $this->morphTo();
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
