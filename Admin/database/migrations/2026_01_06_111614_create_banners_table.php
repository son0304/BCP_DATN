<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration để tạo bảng banners.
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // Thông tin nội dung
            $table->string('title')->nullable()->comment('Tiêu đề hiển thị trên banner');
            $table->string('image_url')->comment('Đường dẫn ảnh banner');
            $table->string('target_url')->nullable()->comment('Link điều hướng khi click');

            // Cấu hình hiển thị
            $table->enum('position', ['home_hero', 'list_sidebar', 'popup'])
                ->default('home_hero')
                ->comment('Vị trí hiển thị trên giao diện');

            $table->integer('priority')->default(0)->comment('Thứ tự ưu tiên (số càng nhỏ hiện trước)');

            // Logic thời gian và trạng thái
            $table->dateTime('start_date')->nullable()->comment('Ngày bắt đầu hiển thị');
            $table->dateTime('end_date')->nullable()->comment('Ngày kết thúc hiển thị');
            $table->boolean('is_active')->default(true)->comment('Trạng thái kích hoạt (Bật/Tắt)');

            $table->timestamps(); // Tạo created_at và updated_at
        });
    }

    /**
     * Thu hồi migration (Xóa bảng).
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};