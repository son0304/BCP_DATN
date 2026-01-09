<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property User $user
 * @property TicketItem[] $items
 */
class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'promotion_id', 'subtotal', 'discount_amount', 'total_amount', 'status', 'payment_status', 'payment_method', 'process_status', 'notes', 'guest', 'booking_code'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // Get venue through items->court->venue (simplified approach)
    public function venue()
    {
        // Tìm Venue ID thông qua bảng items và courts
        $venueId = $this->join('items', 'tickets.id', '=', 'items.ticket_id')
            ->join('courts', 'items.court_id', '=', 'courts.id')
            ->where('tickets.id', $this->id)
            ->value('courts.venue_id');

        // Trả về Object Venue hoặc null
        return $venueId ? Venue::find($venueId) : null;
    }

    // Get all venues for this ticket
    public function venues()
    {
        return $this->hasManyThrough(
            Venue::class,
            Court::class,
            'id', // Foreign key on courts table
            'id', // Foreign key on venues table
            'id', // Local key on tickets table
            'venue_id' // Local key on courts table
        )->join('items', 'courts.id', '=', 'items.court_id')
            ->where('items.ticket_id', $this->id)
            ->distinct();
    }
    public function moneyFlows()
    {
        return $this->morphMany(MoneyFlow::class, 'money_flowable');
    }

    public function getOwnerId()
    {
        return DB::table('items')
            ->join('bookings', 'items.booking_id', '=', 'bookings.id')
            ->join('courts', 'bookings.court_id', '=', 'courts.id')
            ->join('venues', 'courts.venue_id', '=', 'venues.id')
            ->where('items.ticket_id', $this->id)
            ->value('venues.owner_id');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}