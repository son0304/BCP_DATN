<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flash_sale_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên chiến dịch: "Săn giờ vàng 20/10"
            $table->text('description')->nullable(); // Mô tả
            $table->dateTime('start_datetime'); // Thời gian bắt đầu
            $table->dateTime('end_datetime');   // Thời gian kết thúc

            // Trạng thái: pending (sắp tới), active (đang chạy), inactive (tắt), completed (xong)
            $table->enum('status', ['pending', 'active', 'inactive', 'completed'])->default('pending');
            $table->enum('process_status', ['new', 'processing', 'done'])->default('new');

            $table->timestamps(); // Tự động tạo created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('flash_sale_campaigns');
    }
};