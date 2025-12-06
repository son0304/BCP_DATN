<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'description',
        'is_primary',
        'imageable_id',
        'imageable_type',
        'venue_id',
        'court_id',
    ];

    public function imageable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute($value)
    {
        // 1. Nếu ảnh đã là link online (Cloudinary, Firebase...) thì giữ nguyên
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // 2. Nếu là ảnh trong storage, tự động thêm domain vào trước
        // Hàm asset() sẽ lấy APP_URL trong file .env (ví dụ http://127.0.0.1:8000)
        return asset($value);
    }
}
