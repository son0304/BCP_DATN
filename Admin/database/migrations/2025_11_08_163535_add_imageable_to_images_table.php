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
        if (!Schema::hasColumn('images', 'imageable_id')) {
            Schema::table('images', function (Blueprint $table) {
                $table->unsignedBigInteger('imageable_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['imageable_id', 'imageable_type']);
        });
    }
};
