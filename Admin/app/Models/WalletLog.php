<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletLog extends Model
{
    use HasFactory;

    protected $table = 'wallet_logs';

    /**
     * Các trường được phép thêm dữ liệu (Mass Assignment)
     */
    protected $fillable = [
        'wallet_id',
        'ticket_id',
        'booking_id',
        'type',
        'amount',
        'before_balance',
        'after_balance',
        'process_status',
        'description',
    ];

    /**
     * Ép kiểu dữ liệu trả về để tiện xử lý (Số sẽ ra số, không phải chuỗi)
     */
    protected $casts = [
        'amount' => 'float',
        'before_balance' => 'float',
        'after_balance' => 'float',
        'created_at' => 'datetime',
    ];

    // --- RELATIONSHIPS (Liên kết bảng) ---

    /**
     * Log này thuộc về Ví nào
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}