<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    /** @use HasFactory<\Database\Factories\ProvinceFactory> */
    use HasFactory;
    protected $fillable = ['name', 'code' ];
    public function districts (){
        return $this->hasMany(District::class);
    }
    public function users (){
        return $this->hasMany(User::class);
    }
    public function venues (){
        return $this->hasMany(Venue::class);
    }
}