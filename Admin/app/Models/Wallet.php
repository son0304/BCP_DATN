<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'status',
        'balance',
        'process_status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function logs()
    {
        return $this->hasMany(WalletLog::class, 'wallet_id');
    }
}