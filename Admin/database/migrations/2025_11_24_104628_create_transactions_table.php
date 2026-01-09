<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Thay thế booking_id bằng morphs
            // Nó sẽ tự tạo ra 2 cột: transactionable_id (bigint) và transactionable_type (string)
            $table->morphs('transactionable');

            $table->unsignedBigInteger('user_id')->nullable();

            // Nếu bạn chỉ dùng cho App ngân hàng như đã nói, bạn có thể bỏ 'wallet' và 'cash'
            // Nhưng tốt nhất nên để lại để dự phòng hoặc mở rộng sau này
            $table->enum('payment_source', ['momo', 'vnpay', 'wallet', 'cash', 'stripe', 'payos']);

            $table->decimal('amount', 12, 2);
            $table->string('note')->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            $table->enum('process_status', ['new', 'processing', 'done'])->default('new');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
