<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /** @use HasFactory<\Database\Factories\ImageFactory> */
    use HasFactory;
    protected $fillable = ['id', 'venue_id', 'court_id', 'url' , 'description','is_primary'];
   
    public function venue()
    {
        return $this->belongsTo(Venue::class , 'venue_id');
    }
    public function court (){
        return $this->belongsTo(Court::class, 'court_id');
    }
}