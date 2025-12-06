<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // File: create_conversations_table.php

    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_one_id');
            $table->unsignedBigInteger('user_two_id');

            // Thêm khóa ngoại
            $table->foreign('user_one_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('user_two_id')->references('id')->on('users')->cascadeOnDelete();

            $table->enum('type', ['admin_to_venue_owner', 'venue_owner_to_user'])->default('venue_owner_to_user');

            // Cần đảm bảo ràng buộc UNIQUE không bị ảnh hưởng bởi thứ tự ID (nên dùng index kết hợp)
            $table->unique(['user_one_id', 'user_two_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
