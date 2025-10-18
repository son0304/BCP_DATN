<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'start_time',
        'end_time',
        'address_detail',
        'district_id',
        'province_id',
        'lat',
        'lng',
        'phone',
        'is_active',
    ];

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function courts()
    {
        return $this->hasMany(Court::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tickets()
    {
        return $this->hasManyThrough(Ticket::class, Court::class, 'venue_id', 'court_id');
    }
    public function types()
    {
        return $this->belongsToMany(
            VenueType::class,
            'venue_venue_types',
            'venue_id',
            'venue_type_id'
        )->withPivot('created_at', 'updated_at');
    }
}