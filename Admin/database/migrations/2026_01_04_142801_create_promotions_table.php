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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('value', 10, 2);
            $table->enum('type', ['percentage', 'fixed'])->default('fixed');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->integer('usage_limit')->default(0);
            $table->integer('used_count')->default(0);
            $table->decimal('max_discount_amount', 12, 2)->nullable();
            $table->decimal('min_order_value', 12, 2)->nullable();
            $table->text('description')->nullable();

            $table->unsignedBigInteger('venue_id')->nullable(); // Null = Toàn hệ thống/Toàn chủ sân
            $table->unsignedBigInteger('creator_user_id'); // Người tạo voucher

            $table->enum('target_user_type', ['all', 'new_user'])->default('all');
            $table->enum('process_status', ['active', 'disabled'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('creator_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
