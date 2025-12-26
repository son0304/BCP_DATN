<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    // database/migrations/xxxx_xx_xx_create_services_table.php

    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            // Giả sử bạn có bảng categories, nếu chưa có thì bỏ constrained() đi
            $table->foreignId('category_id')->nullable()->constrained('service_categories')->nullOnDelete();

            $table->string('name'); // Tên dịch vụ: Coca, Mì tôm
            $table->string('unit'); // Đơn vị: Lon, Gói
            $table->text('description')->nullable();
            $table->enum('type', ['service', 'consumable', 'amenities']);
            $table->enum('process_status', ['new', 'processing', 'done'])->default('new');

            $table->timestamps();
            $table->softDeletes(); // Cột deleted_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};