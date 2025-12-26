<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'services';

    protected $fillable = ['category_id', 'name', 'unit', 'type', 'description', 'process_status',];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    // SỬA: Đổi tên thành số nhiều cho đúng chuẩn
    public function venueServices()
    {
        return $this->hasMany(VenueService::class, 'service_id');
    }

    // Quan hệ đa hình lấy ảnh
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // Quan hệ Many-to-Many với Venue (thông qua bảng trung gian venue_services)
    public function venues()
    {
        return $this->belongsToMany(Venue::class, 'venue_services')
            ->using(VenueService::class)
            ->withPivot(['id', 'price', 'stock', 'status'])
            ->withTimestamps();
    }
}