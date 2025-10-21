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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('time_slots')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('price', 10)->default(0);
            $table->enum('status', ['open', 'booked', 'closed', 'maintenance'])->default('open');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['court_id', 'slot_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};