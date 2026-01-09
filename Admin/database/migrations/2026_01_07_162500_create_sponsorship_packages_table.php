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
        Schema::create('sponsorship_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên gói: VD "Gói Siêu Tốc (Combo)"
            $table->decimal('price', 15, 2); // Giá trọn gói
            $table->integer('duration_days'); // Thời hạn chung (VD: 7 ngày)
            $table->text('description')->nullable(); // Mô tả hiển thị cho đẹp
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsorship_packages');
    }
};
