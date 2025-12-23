<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'start_time',
        'end_time',
        'address_detail',
        'district_id',
        'province_id',
        'start_time',
        'end_time',
        'lat',
        'lng',
        'phone',
        'is_active',
        'admin_note',
    ];

    public function moneyFlows()
    {
        return $this->hasMany(MoneyFlow::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    public function district()
    {
        return $this->belongsTo(District::class);
    }
    public function courts()
    {
        return $this->hasMany(Court::class);
    }
    public function services()
    {
        return $this->belongsToMany(Service::class, 'venue_services')
            ->using(VenueService::class)
            ->withPivot(['id', 'price', 'stock', 'status'])
            ->withTimestamps();
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

    public function venueTypes()
    {
        return $this->belongsToMany(
            VenueType::class,
            'venue_venue_types',
            'venue_id',
            'venue_type_id'
        )->withPivot('created_at', 'updated_at');
    }
}