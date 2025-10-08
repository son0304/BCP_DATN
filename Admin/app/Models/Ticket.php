<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;
    protected $fillable = ['user_id', 'promotion_id', 'subtotal', 'discount_amount', 'total_amount','status', 'payment_status', 'notes'];
    
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }    
    public function promotion (){
        return $this->belongsTo(Promotion::class, 'promotion_id');
    } 
    public function items (){
        return $this->hasMany(Item::class);
    } 
}