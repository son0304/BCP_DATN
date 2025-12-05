<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    /**
     * Tên bảng được sử dụng bởi model.
     * Mặc định Laravel sẽ tìm 'messages'
     *
     * @var string
     */
    protected $table = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'guest_token',
        'message',
    ];

    /**
     * Lấy cuộc hội thoại mà tin nhắn này thuộc về.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Lấy người gửi tin nhắn (nếu là người dùng đã đăng nhập).
     */
    public function sender(): BelongsTo
    {
        // sender_id có thể là NULL (dành cho bot/guest), 
        // nhưng BelongsTo vẫn hoạt động tốt khi key là null.
        return $this->belongsTo(User::class, 'sender_id');
    }

    // --- Phương thức tiện ích để xử lý BOT/GUEST ---

    /**
     * Kiểm tra xem tin nhắn có phải do BOT gửi không.
     * (Giả định: Bot gửi tin nhắn với sender_id là NULL và không có guest_token)
     */
    public function isBotMessage(): bool
    {
        return is_null($this->sender_id) && is_null($this->guest_token);
    }

    /**
     * Kiểm tra xem tin nhắn có phải do khách (Guest) gửi không.
     */
    public function isGuestMessage(): bool
    {
        return !is_null($this->guest_token);
    }
}