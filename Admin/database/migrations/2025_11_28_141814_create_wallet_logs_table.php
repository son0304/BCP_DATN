<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_logs', function (Blueprint $table) {
            $table->id();

            // Liên kết với ví (Bắt buộc)
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();

            // Liên kết booking hoặc ticket (Có thể Null vì nạp tiền thì ko có booking)
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();

            // Loại giao dịch:
            // 'deposit' (Nạp tiền vào ví)
            // 'payment' (Thanh toán tiền vé)
            // 'refund' (Hoàn tiền)
            // 'withdraw' (Rút tiền - nếu có)
            $table->enum('type', ['deposit', 'payment', 'refund', 'withdraw']);

            // Số tiền giao dịch (Lưu số dương) - Dùng 15,2 để hỗ trợ số tiền lớn VNĐ
            $table->decimal('amount', 15, 2);

            // Số dư TRƯỚC và SAU khi giao dịch (Cực kỳ quan trọng để debug)
            $table->decimal('before_balance', 15, 2);
            $table->decimal('after_balance', 15, 2);

            // Mô tả chi tiết (VD: "Hoàn tiền vé #10 do hủy sớm")
            $table->string('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_logs');
    }
};