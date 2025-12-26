<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_profiles', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại liên kết với bảng users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Thông tin doanh nghiệp / Sân
            $table->string('business_name')->comment('Tên hiển thị (Tên sân hoặc Tên công ty)');
            $table->string('business_address')->comment('Địa chỉ kinh doanh');

            // Thông tin ngân hàng
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_account_name')->comment('Tên chủ tài khoản');

            // Trạng thái & Ghi chú
            $table->enum('status', ['pending', 'approved', 'rejected', 'resubmitted'])->default('pending');
            $table->enum('process_status', ['new', 'processing', 'done'])->default('new');

            $table->text('admin_note')->nullable()->comment('Lý do từ chối nếu có');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_profiles');
    }
};