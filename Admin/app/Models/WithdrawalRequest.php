<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    // Các hằng số trạng thái để dùng trong code cho sạch
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'amount',
        'fee',
        'actual_amount',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'status',
        'transaction_code',
        'user_note',
        'admin_note',
        'processed_at',
    ];

    // Tự động ép kiểu thời gian
    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'double',
        'actual_amount' => 'double',
    ];

    /**
     * Quan hệ với User (Người rút tiền)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
