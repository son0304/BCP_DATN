<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    /** @use HasFactory<\Database\Factories\DistrictFactory> */
    use HasFactory;
    protected $fillable  = ['province_id', 'name', 'code'];
    public function users (){
        return $this->hasMany(User::class);
    }
    public function venues (){
        return $this->hasMany(Venue::class);
    }
    public function privince (){
        return $this->belongsTo(Province::class, 'province_id');
    }
}