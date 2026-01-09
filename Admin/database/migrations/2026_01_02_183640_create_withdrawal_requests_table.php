<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();

            // Tham chiếu tới user rút tiền
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Số tiền
            $table->decimal('amount', 15, 2)->comment('Số tiền yêu cầu rút');
            $table->decimal('fee', 15, 2)->default(0)->comment('Phí giao dịch nếu có');
            $table->decimal('actual_amount', 15, 2)->comment('Số tiền thực chuyển sau khi trừ phí');

            // Thông tin ngân hàng tại thời điểm rút (Snapshot)
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_account_name');

            // Trạng thái: pending, approved, rejected, cancelled
            $table->string('status')->default('pending');

            // Thông tin đối soát
            $table->string('transaction_code')->nullable()->comment('Mã giao dịch ngân hàng');
            $table->text('user_note')->nullable()->comment('Ghi chú từ người rút');
            $table->text('admin_note')->nullable()->comment('Phản hồi từ admin/Lý do từ chối');

            // Thời gian xử lý
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Đánh index để truy vấn nhanh
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
