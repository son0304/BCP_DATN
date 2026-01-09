<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'value',
        'type',
        'start_at',
        'end_at',
        'usage_limit',
        'used_count',
        'creator_user_id',
        'process_status',
        'max_discount_amount',
        'min_order_value',
        'target_user_type',
        'venue_id',
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

    public function isActive(): bool
    {
        $now = now();
        return ($this->process_status === 'active') &&
            ($this->start_at <= $now) &&
            ($this->end_at >= $now) &&
            ($this->usage_limit === 0 || $this->used_count < $this->usage_limit);
    }

    /**
     * Kiểm tra phạm vi áp dụng dựa trên Role của creator_user_id
     */
    public function isValidForVenue(int $venueId, int $targetVenueOwnerId): bool
    {
        if (!is_null($this->venue_id)) {
            return $this->venue_id == $venueId;
        }

        $creator = $this->creator;

        if (!$creator || !$creator->role) {
            return false;
        }

        $roleName = $creator->role->name; // Lấy 'admin' hoặc 'venue_owner' từ bảng roles

        if ($roleName === 'admin') {
            return true; // Admin tạo trống = Toàn hệ thống
        }

        if ($roleName === 'venue_owner' || $roleName === 'owner') {
            // Chủ sân tạo trống = Chỉ áp dụng cho các sân của chính họ
            return $this->creator_user_id == $targetVenueOwnerId;
        }

        return false;
    }

    public function isEligible($orderTotal = null, $venueId, $ownerId, $user = null): bool
    {
        if (!$this->isActive()) return false;
        if (!$this->isValidForVenue($venueId, $ownerId)) return false;

        // SỬA: Chỉ check giá tiền nếu orderTotal được truyền vào (khác null)
        if (!is_null($orderTotal) && $this->min_order_value > 0 && $orderTotal < $this->min_order_value) {
            return false;
        }

        if ($user && !$this->canUserUse($user)) return false;

        return true;
    }

    public function isExpired(): bool
    {
        return $this->end_at < now() || ($this->usage_limit > 0 && $this->used_count >= $this->usage_limit);
    }

    public function calculateDiscount($orderTotal): float
    {
        $discount = ($this->type === 'percentage')
            ? ($orderTotal * $this->value) / 100
            : $this->value;

        if ($this->type === 'percentage' && $this->max_discount_amount > 0) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return (float) min($discount, $orderTotal);
    }

    public function canUserUse($user): bool
    {
        if (!$user) return false;

        if ($this->target_user_type === 'new_user') {
            $hasOrdered = $user->tickets()
                ->whereIn('status', ['paid', 'completed'])
                ->exists();
            if ($hasOrdered) return false;
        }


        $alreadyUsed = $user->tickets()
            ->where('promotion_id', $this->id)
            ->whereIn('status', ['paid', 'completed', 'pending'])
            ->exists();

        if ($alreadyUsed) {
            return false;
        }

        return true;
    }
}
