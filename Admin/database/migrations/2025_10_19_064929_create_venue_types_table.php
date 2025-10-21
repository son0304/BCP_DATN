<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('venue_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Loại sân (vd: 5 người, 7 người, tennis, badminton)
            $table->text('description')->nullable();  // Mô tả chi tiết loại sân
            $table->timestamps();
            $table->softDeletes();            // Cho phép xóa mềm
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_types');
    }
};
