<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */

    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;


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
        'avt',
        'lat',
        'lng',
        'is_active',
        'email_verified_at',
        'email_verification_token',
        'is_email_verified'
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
        return $this->hasMany(Venue::class, 'owner_id');
    }
    public function review()
    {
        return $this->hasMany(Review::class);
    }
    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function merchantProfile()
    {
        return $this->hasOne(MerchantProfile::class);
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
            'is_email_verified' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
