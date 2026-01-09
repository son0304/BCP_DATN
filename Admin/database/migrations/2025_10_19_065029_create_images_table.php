<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();

            // Đây là cặp morph
            $table->morphs('imageable'); // Tạo imageable_id + imageable_type
            $table->string('type')->nullable(); 
            $table->string('url');
            $table->text('description')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};