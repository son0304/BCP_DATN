<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Thêm cột owner_id
            $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        // Bỏ unique constraint trên slug (nếu tồn tại) và thêm unique cho slug + owner_id
        // Sử dụng DB facade để xử lý an toàn hơn
        if (Schema::hasColumn('product_categories', 'slug')) {
            // Kiểm tra xem unique constraint có tồn tại không
            $indexes = DB::select("SHOW INDEX FROM product_categories WHERE Key_name = 'product_categories_slug_unique'");
            if (count($indexes) > 0) {
                Schema::table('product_categories', function (Blueprint $table) {
                    $table->dropUnique(['slug']);
                });
            }
            
            // Thêm unique constraint mới cho slug + owner_id
            Schema::table('product_categories', function (Blueprint $table) {
                $table->unique(['slug', 'owner_id'], 'product_categories_slug_owner_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropUnique('product_categories_slug_owner_unique');
            $table->unique('slug');
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }
};
