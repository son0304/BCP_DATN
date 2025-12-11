<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'booking_id',
        'product_id',
        'quantity',
        'product_name',
        'product_price',
        'unit_price',
        'discount_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'product_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    protected $appends = ['is_booking', 'is_product', 'item_type'];

    // Relationships
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getIsBookingAttribute(): bool
    {
        return !is_null($this->booking_id);
    }

    public function getIsProductAttribute(): bool
    {
        return !is_null($this->product_id);
    }

    public function getItemTypeAttribute(): string
    {
        if ($this->is_booking) {
            return 'booking';
        }
        if ($this->is_product) {
            return 'product';
        }
        return 'unknown';
    }

    // Helper methods
    public function getSubtotalAttribute(): float
    {
        if ($this->is_product) {
            return ($this->product_price ?? $this->unit_price) * $this->quantity;
        }
        return $this->unit_price;
    }

    public function getTotalAttribute(): float
    {
        return $this->getSubtotalAttribute() - ($this->discount_amount ?? 0);
    }

    // Legacy relationships (có thể cần cho code cũ)
    public function court()
    {
        return $this->hasOneThrough(
            Court::class,
            Booking::class,
            'id',
            'id',
            'booking_id',
            'court_id'
        );
    }

    public function slot()
    {
        return $this->hasOneThrough(
            TimeSlot::class,
            Booking::class,
            'id',
            'id',
            'booking_id',
            'time_slot_id'
        );
    }
}
