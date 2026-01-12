<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',           // 'sale' hoặc 'user_post'
        'reference_id',   // Lưu ID của FlashSaleCampaign (nếu là bài sale)
        'venue_id',       // Lưu ID của Venue để điều hướng
        'content',
        'phone_contact',
        'status',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}