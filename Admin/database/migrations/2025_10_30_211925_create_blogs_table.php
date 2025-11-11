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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id(); // id int AUTO_INCREMENT
            $table->string('title'); // varchar(255) NOT NULL
            $table->text('excerpt')->nullable(); // text NULL
            $table->text('content'); // text NOT NULL
            $table->string('image_url', 500)->nullable(); // varchar(500) NULL
            $table->string('author', 100)->default('BCP Sports'); // varchar(100) DEFAULT 'BCP Sports'
            $table->date('published_at')->nullable()->default(\DB::raw('CURDATE()')); // date NULL DEFAULT CURDATE()
            $table->timestamp('created_at')->useCurrent(); // timestamp DEFAULT CURRENT_TIMESTAMP
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable(); // ON UPDATE CURRENT_TIMESTAMP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
