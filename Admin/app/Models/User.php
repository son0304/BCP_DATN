<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'district_id',
        'province_id',
        'lat',
        'lng',
        'is_active'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    public function venues()
    {
        return $this->hasMany(Venue::class);
    }
    public function review()
    {
        return $this->hasMany(Review::class);
    }




    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}