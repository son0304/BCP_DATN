<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'value',
        'type',                 // 'percentage' hoặc 'fixed'
        'start_at',
        'end_at',
        'usage_limit',          // Quy ước: -1 (hoặc <0) là Vô hạn, >0 là giới hạn, 0 là Tắt
        'used_count',
        'creator_user_id',
        'process_status',       // 'active' hoặc 'disabled'
        'max_discount_amount',
        'min_order_value',
        'target_user_type',     // 'all' hoặc 'new_user'
        'venue_id',             // Null = Áp dụng toàn hệ thống (Admin) hoặc toàn chuỗi (Owner)
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'value' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'min_order_value' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
        ];
    }

    /* ------------------------------ RELATIONSHIPS ----------------------------- */

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    /* ---------------------------------- LOGIC --------------------------------- */

    /**
     * Kiểm tra trạng thái khả dụng của mã.
     */
    public function isActive(): bool
    {
        $now = now();

        // 1. Check trạng thái và thời gian
        if ($this->process_status !== 'active') return false;
        if ($now < $this->start_at || $now > $this->end_at) return false;

        // 2. Check số lượng
        // < 0 : Vô hạn
        if ($this->usage_limit < 0) {
            return true;
        }

        // > 0 : Có giới hạn
        if ($this->usage_limit > 0) {
            return $this->used_count < $this->usage_limit;
        }

        // = 0 : Hết lượt / Tắt
        return false;
    }

    /**
     * QUAN TRỌNG: Hàm này đã sửa để nhận tham số là Object Venue
     * @param Venue $venue Object sân đang đặt vé
     */
    public function isValidForVenue(Venue $venue): bool
    {
        // Trường hợp 1: Mã này gán cứng cho 1 sân cụ thể (venue_id trong bảng promotions không null)
        if (!is_null($this->venue_id)) {
            return $this->venue_id === $venue->id;
        }

        // Trường hợp 2: Mã toàn hệ thống hoặc mã theo chủ sân (venue_id is NULL)
        $creator = $this->creator;

        // Nếu data lỗi không tìm thấy người tạo -> chặn luôn cho an toàn
        if (!$creator || !$creator->role) {
            return false;
        }

        $roleName = $creator->role->name;

        // Admin tạo -> Áp dụng mọi sân
        if ($roleName === 'admin') {
            return true;
        }

        // Chủ sân tạo -> Chỉ áp dụng cho các sân thuộc sở hữu của họ
        if (in_array($roleName, ['venue_owner', 'owner'])) {
            // So sánh ID người tạo mã với ID chủ sân
            return $this->creator_user_id === $venue->owner_id;
        }

        return false;
    }

    /**
     * Hàm kiểm tra tổng hợp.
     * @param float|null $orderTotal
     * @param Venue $venue  <-- BẮT BUỘC LÀ OBJECT VENUE
     * @param User|null $user
     */
    public function isEligible(?float $orderTotal, Venue $venue, ?User $user = null): bool
    {
        // 1. Check hiệu lực cơ bản
        if (!$this->isActive()) return false;

        // 2. Check phạm vi áp dụng (Sân)
        // Truyền $venue (Object) vào hàm isValidForVenue
        if (!$this->isValidForVenue($venue)) return false;

        // 3. Check giá trị đơn hàng tối thiểu (nếu có yêu cầu check)
        if (!is_null($orderTotal) && $this->min_order_value > 0) {
            if ($orderTotal < $this->min_order_value) return false;
        }

        // 4. Check điều kiện người dùng (nếu user đã đăng nhập)
        if ($user && !$this->canUserUse($user)) return false;

        return true;
    }

    public function isExpired(): bool
    {
        return now() > $this->end_at ||
            ($this->usage_limit > 0 && $this->used_count >= $this->usage_limit);
    }

    public function calculateDiscount(float $orderTotal): float
    {
        if ($this->type === 'percentage') {
            $discount = ($orderTotal * $this->value) / 100;
            if ($this->max_discount_amount > 0) {
                $discount = min($discount, $this->max_discount_amount);
            }
        } else {
            $discount = $this->value;
        }

        return (float) min($discount, $orderTotal);
    }

    public function canUserUse(User $user): bool
    {
        // 1. Check đối tượng khách hàng mới
        if ($this->target_user_type === 'new_user') {
            $hasPaidTicket = $user->tickets()
                ->whereIn('status', ['paid', 'completed'])
                ->exists();

            if ($hasPaidTicket) return false;
        }

        $alreadyUsed = $user->tickets()
            ->where('promotion_id', $this->id)
            ->whereIn('status', ['paid', 'completed', 'pending'])
            ->exists();

        return !$alreadyUsed;
    }
}