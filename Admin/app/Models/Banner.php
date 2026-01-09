<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'target_url',
        'position',
        'priority',
        'start_date',
        'end_date',
        'is_active'
    ];

    // Tự động ép kiểu ngày tháng để dễ xử lý trong code
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}