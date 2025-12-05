<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'type',
    ];

    // --- Mối quan hệ tới Users ---

    /**
     * Lấy người dùng đầu tiên (user_one) tham gia cuộc hội thoại.
     */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    /**
     * Lấy người dùng thứ hai (user_two) tham gia cuộc hội thoại.
     */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    // --- Mối quan hệ tới Messages ---

    /**
     * Lấy tất cả tin nhắn thuộc cuộc hội thoại này.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // --- Phương thức tiện ích ---

    /**
     * Lấy người dùng còn lại trong cuộc hội thoại (ngoại trừ người dùng hiện tại).
     * * @param \App\Models\User $currentUser
     * @return \App\Models\User|null
     */
    public function getOtherParticipant(User $currentUser)
    {
        if ($this->user_one_id === $currentUser->id) {
            // Tải (load) userTwo nếu chưa được tải
            return $this->userTwo;
        }
        if ($this->user_two_id === $currentUser->id) {
            // Tải (load) userOne nếu chưa được tải
            return $this->userOne;
        }
        return null;
    }
}