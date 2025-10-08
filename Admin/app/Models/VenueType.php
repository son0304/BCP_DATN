<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueType extends Model
{
    /** @use HasFactory<\Database\Factories\VenueTypeFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'description'
    ];

    public function venue_venue_types()
    {
        return $this->hasMany(VenueVenueType::class);
    }
    public function courts()
    {
        return $this->hasMany(Court::class);
    }
}