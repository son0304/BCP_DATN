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
        Schema::create('money_flows', function (Blueprint $table) {
            $table->id();

            // Thay thế booking_id bằng morphs
            // Nó sẽ tự tạo ra money_flowable_id và money_flowable_type
            $table->morphs('money_flowable');

            $table->decimal('total_amount', 12, 2);
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->decimal('promotion_amount', 12, 2)->default(0);

            $table->decimal('admin_amount', 12, 2);
            $table->decimal('venue_owner_amount', 12, 2);

            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->enum('process_status', ['new', 'processing', 'done'])->default('new');
            $table->string('note')->nullable();

            $table->unsignedBigInteger('venue_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_flows');
    }
};
