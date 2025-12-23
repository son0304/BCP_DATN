<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->index(); // ID chủ sân

            // Logic: Nếu NULL = Dịch vụ chung. Nếu có ID = Dịch vụ riêng (Bóng đá/Cầu lông)
            $table->unsignedBigInteger('venue_type_id')->nullable()->index();

            $table->string('name'); // Vd: Giải khát, Cho thuê đồ
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Xóa mềm

            // Khóa ngoại (Giả sử bạn đã có bảng venue_types)
            $table->foreign('venue_type_id')->references('id')->on('venue_types')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_categories');
    }
};