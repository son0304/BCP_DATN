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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // ID người đăng (Chủ sân hoặc Người dùng)
            $table->unsignedBigInteger('user_id');

            // Phân loại: 'sale' (từ hệ thống), 'user_post' (người dùng tự đăng)
            $table->enum('type', ['sale', 'user_post'])->default('user_post');

            // Link tới chiến dịch sale (nếu type là sale)
            $table->unsignedBigInteger('reference_id')->nullable();

            // Nội dung bài đăng
            $table->text('content');

            // Số điện thoại để người dùng tự gọi cho nhau
            $table->string('phone_contact')->nullable();

            // Trạng thái: pending (chờ duyệt), active (đang hiện), hidden (ẩn/đã xong)
            $table->enum('status', ['pending', 'active', 'hidden'])->default('pending');

            $table->timestamps();

            // Ràng buộc khóa ngoại nếu cần (tùy chọn)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};