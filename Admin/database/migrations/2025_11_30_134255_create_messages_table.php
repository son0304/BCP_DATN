<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // File: create_messages_table.php

    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Liên kết với cuộc hội thoại
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();

            // SENDER ID: Cần phải là NULLABLE để BOT hoặc GUEST có thể gửi.
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
            $table->enum('process_status', ['new', 'processing', 'done'])->default('new');

            $table->string('guest_token', 100)->nullable()->index(); // Dành cho khách
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};