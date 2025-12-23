<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'items';

    // 1. Khai báo đầy đủ các trường mới
    protected $fillable = [
        'ticket_id',
        'item_type',        // 'booking' hoặc 'service'
        'booking_id',       // ID đặt sân (nếu là tiền sân)
        'venue_service_id',       // ID dịch vụ (nếu là tiền nước/thuê đồ)
        'quantity',         // Số lượng
        'unit_price',       // Đơn giá tại thời điểm mua
        'discount_amount',  // Giảm giá
        'status',           // active, refund
    ];


    // --- RELATIONSHIPS (QUAN HỆ) ---

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function venueService()
    {
        return $this->belongsTo(VenueService::class, 'venue_service_id');
    }

    // --- ACCESSORS (XỬ LÝ DỮ LIỆU HIỂN THỊ) ---

    /**
     * Tự động lấy tên hiển thị:
     * - Nếu là Booking: Trả về "Sân 7A - Ca 17:00"
     * - Nếu là Service: Trả về "Nước Aquafina"
     */
    public function getItemNameAttribute()
    {
        if ($this->item_type === 'booking') {
            // Lấy thông tin từ quan hệ booking (cần Eager Load để tránh N+1)
            $booking = $this->booking;
            if (!$booking) return 'Lịch đặt sân (Đã hủy)';

            // Giả sử booking có quan hệ court và timeSlot
            $courtName = $booking->court->name ?? 'Sân ?';
            $timeSlot  = $booking->timeSlot->time ?? ''; // Ví dụ: 17:00 - 18:00

            return "Đặt sân: $courtName ($timeSlot)";
        }

        if ($this->item_type === 'service') {
            return $this->service->name ?? 'Dịch vụ (Đã xóa)';
        }

        return 'Mặt hàng không xác định';
    }

    /**
     * Lấy ảnh minh họa
     */
    public function getItemImageAttribute()
    {
        if ($this->item_type === 'service') {
            return $this->service->image_url ?? null;
        }
        return null; // Booking thường không có ảnh hoặc lấy ảnh sân
    }

    /**
     * Tính tổng tiền của dòng này (Số lượng * Đơn giá - Giảm giá)
     */
    public function getLineTotalAttribute()
    {
        return ($this->quantity * $this->unit_price) - $this->discount_amount;
    }
}