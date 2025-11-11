<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // Số tiền giảm tối đa (áp dụng khi type = '%')
            $table->decimal('max_discount_amount', 12, 2)->nullable()->after('used_count');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('max_discount_amount');
        });
    }
};

