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
        Schema::create('venue_services', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại liên kết tới bảng venues
            $table->foreignId('venue_id')->constrained('venues')->onDelete('cascade');

            // Khóa ngoại liên kết tới bảng services vừa tạo ở trên
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');

            // Các thuộc tính riêng của dịch vụ tại sân đó
            $table->decimal('price', 12, 2)->default(0); // Giá bán
            $table->integer('stock')->default(0);        // Tồn kho
            $table->tinyInteger('status')->default(1);   // 1: Đang bán, 0: Ngưng bán

            $table->timestamps();
            $table->softDeletes(); // Nếu bạn muốn xóa mềm cả liên kết này

            // Đảm bảo 1 dịch vụ chỉ xuất hiện 1 lần trong 1 sân (tránh trùng lặp)
            $table->unique(['venue_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_services');
    }
};