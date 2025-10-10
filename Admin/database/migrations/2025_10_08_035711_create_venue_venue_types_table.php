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
    Schema::create('venue_venue_types', function (Blueprint $table) {
        $table->id();
        $table->foreignId('venue_id')->constrained('venues');
        $table->foreignId('venue_type_id')->constrained('venue_types');
        $table->timestamps();
        $table->softDeletes();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_venue_types');
    }
};