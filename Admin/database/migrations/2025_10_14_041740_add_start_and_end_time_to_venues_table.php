<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            if (!Schema::hasColumn('venues', 'start_time')) {
                $table->time('start_time')->nullable();
            }
            if (!Schema::hasColumn('venues', 'end_time')) {
                $table->time('end_time')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
