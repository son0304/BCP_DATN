<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    /** @use HasFactory<\Database\Factories\PromotionFactory> */
    use HasFactory;
    protected $fillable = ['code', 'value', 'type','start_at','end_at', 'usage_limit', 'used_count'];

    public function tickets (){
        return $this->hasMany(Ticket::class);
    }
}