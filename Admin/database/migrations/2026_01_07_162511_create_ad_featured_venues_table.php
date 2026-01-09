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
        Schema::create('ad_featured_venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->onDelete('cascade');
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->string('section_name')->default('home_featured')->index();
            $table->dateTime('end_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_featured_venues');
    }
};
