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
        Schema::create('ad_top_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->onDelete('cascade'); // Giả sử bảng sân là 'venues'
            $table->unsignedBigInteger('purchase_id')->nullable(); // Link tới lịch sử giao dịch
            $table->integer('priority_point')->default(0);
            $table->dateTime('end_at')->index(); // Đánh index để query nhanh
            $table->timestamps();

            // Mỗi sân chỉ nên có 1 dòng active tại 1 thời điểm trong bảng này để dễ tính toán
            // Nếu mua thêm thì update dòng cũ (cộng điểm/cộng ngày)
            $table->unique('venue_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_top_searches');
    }
};
