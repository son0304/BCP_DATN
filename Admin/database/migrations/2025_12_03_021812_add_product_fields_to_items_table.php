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
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('booking_id')->constrained('products')->nullOnDelete();
            $table->integer('quantity')->default(1)->after('product_id');
            $table->string('product_name')->nullable()->after('quantity');
            $table->decimal('product_price', 10, 2)->nullable()->after('product_name');
            
            // Make booking_id nullable since items can now be products
            $table->foreignId('booking_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'quantity', 'product_name', 'product_price']);
            $table->foreignId('booking_id')->nullable(false)->change();
        });
    }
};
