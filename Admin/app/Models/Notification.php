<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Thêm dòng này xử lý UUID

class Notification extends Model
{
    use HasFactory, HasUuids; // Sử dụng Trait HasUuids

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at'
    ];

    // Tự động chuyển đổi dữ liệu
    protected $casts = [
        'data' => 'array',       // JSON trong DB -> Mảng trong PHP
        'read_at' => 'datetime', // Timestamp -> Carbon Object
    ];

    // Hàm tiện ích: Đánh dấu đã đọc
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
}