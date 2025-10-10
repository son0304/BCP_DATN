<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;
    protected $fillable = ['user_id', 'venue_id', 'rating','comment'  ];
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function venue (){
        return $this->belongsTo(Venue::class, 'venue_id');
    }
}