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
        Schema::create('notifications', function (Blueprint $table) {
            // 1. Dùng UUID làm ID (chuẩn cho notification để tránh đoán ID)
            // Nếu muốn dùng ID số bình thường thì đổi thành: $table->id();
            $table->uuid('id')->primary();

            // 2. Người nhận thông báo
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // 3. Phân loại & Nội dung
            $table->string('type')->default('info'); // VD: system, booking, danger...
            $table->string('title');
            $table->text('message')->nullable();

            // 4. Dữ liệu JSON (Lưu ID booking, link, v.v...)
            $table->json('data')->nullable();

            // 5. Trạng thái đọc
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};