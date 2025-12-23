<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index(); // Link tới hóa đơn tổng

            // Phân loại dòng này là 'Tiền sân' hay 'Tiền dịch vụ'
            $table->enum('item_type', ['booking', 'service'])->default('booking');

            // --- Cột liên kết ---
            $table->unsignedBigInteger('booking_id')->nullable(); // Có thể null nếu chỉ mua nước mang về
            $table->unsignedBigInteger('venue_service_id')->nullable(); // Có thể null nếu dòng này là tiền sân

            // --- Cột giá trị ---
            $table->integer('quantity')->default(1); // Booking thường là 1, Nước là n
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            // Có thể thêm cột total_amount nếu muốn lưu cứng (unit_price * quantity - discount)

            $table->enum('status', ['active', 'refund'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            // Khóa ngoại
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('set null');
            $table->foreign('venue_service_id')->references('id')->on('venue_services')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
};