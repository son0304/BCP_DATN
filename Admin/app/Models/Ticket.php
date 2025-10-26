<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property User $user
 * @property TicketItem[] $items
 */
class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'promotion_id', 'subtotal', 'discount_amount', 'total_amount', 'status', 'payment_status', 'notes'];

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
        $item = $this->items()->with('court.venue')->first();
        return $item ? $item->court->venue : null;
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
}
