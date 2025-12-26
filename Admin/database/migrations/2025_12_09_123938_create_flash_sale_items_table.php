<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flash_sale_items', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại liên kết với bảng Campaigns
            $table->foreignId('campaign_id')
                ->constrained('flash_sale_campaigns')
                ->onDelete('cascade'); // Xóa chiến dịch thì xóa luôn item

            // Khóa ngoại liên kết với bảng Availabilities (Kho hàng gốc)
            // Giả sử bảng kho của bạn tên là 'availabilities'
            $table->foreignId('availability_id')
                ->constrained('availabilities')
                ->onDelete('cascade');

            $table->decimal('sale_price', 10, 2); // Giá bán Sale (VD: 50000.00)

            $table->integer('quantity')->default(1);   // Tổng số lượng bán (Thường là 1)
            $table->integer('sold_count')->default(0); // Số lượng đã bán

            // Trạng thái item: active (đang bán), sold_out (hết hàng), inactive (chủ sân ẩn)
            $table->enum('status', ['active', 'sold_out', 'inactive'])->default('active');
            $table->enum('process_status', ['new', 'processing', 'done'])->default('new');


            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('flash_sale_items');
    }
};