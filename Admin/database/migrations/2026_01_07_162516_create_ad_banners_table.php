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
        Schema::create('ad_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->onDelete('cascade');
            $table->unsignedBigInteger('purchase_id')->nullable(); // Link tới bảng money_flows/đơn hàng

            $table->string('title')->nullable(); // Có thể lưu tên sân hoặc tiêu đề quảng cáo
            $table->string('target_url')->nullable();
            $table->string('position')->default('home_hero')->index();
            $table->integer('priority')->default(999); // Thường quảng cáo hiện sau admin

            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->index(); // Ngày hết hạn gói
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_banners');
    }
};
