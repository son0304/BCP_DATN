<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sponsorship_package_items', function (Blueprint $table) {
            $table->id();
            // Liên kết với bảng gói cha
            $table->foreignId('sponsorship_package_id')
                ->constrained('sponsorship_packages')
                ->onDelete('cascade');

            $table->string('type'); // 'top_search', 'featured', 'banner'
            $table->json('settings')->nullable(); // Lưu cấu hình riêng: {"point": 50} hoặc {"section": "home"}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsorship_package_items');
    }
};
