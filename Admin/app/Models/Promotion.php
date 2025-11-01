<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    /** @use HasFactory<\Database\Factories\PromotionFactory> */
    use HasFactory;
    protected $fillable = ['code', 'value', 'type','start_at','end_at', 'usage_limit', 'used_count', 'created_by'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function tickets (){
        return $this->hasMany(Ticket::class);
    }

    /**
     * Người tạo voucher
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Kiểm tra voucher có đang hoạt động không
     */
    public function isActive(): bool
    {
        $now = now();
        
        // Kiểm tra voucher đã bắt đầu chưa
        if ($this->start_at > $now) {
            return false; // Voucher chưa bắt đầu
        }
        
        // Kiểm tra voucher đã hết hạn chưa
        if ($this->end_at < $now) {
            return false; // Voucher đã hết hạn
        }
        
        // Kiểm tra giới hạn sử dụng
        if ($this->usage_limit > 0 && $this->used_count >= $this->usage_limit) {
            return false; // Đã hết lượt sử dụng
        }
        
        return true; // Voucher đang hoạt động
    }

    /**
     * Kiểm tra voucher đã hết hạn chưa
     */
    public function isExpired(): bool
    {
        return !$this->isActive();
    }
}