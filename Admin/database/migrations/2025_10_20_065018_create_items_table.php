<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // --- Liên kết hóa đơn ---
            $table->foreignId('ticket_id')
                ->constrained('tickets')
                ->cascadeOnDelete();

            // Phân loại dòng: tiền sân hay tiền dịch vụ
            $table->enum('item_type', ['booking', 'service'])
                ->default('booking');

            // --- Liên kết nghiệp vụ ---
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('bookings')
                ->nullOnDelete();

            $table->foreignId('venue_service_id')
                ->nullable()
                ->constrained('venue_services')
                ->nullOnDelete();

            // --- Giá trị ---
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);

            // --- Trạng thái ---
            $table->enum('status', ['active', 'refund'])->default('active');
            $table->enum('process_status', ['new', 'processing', 'done'])->default('new');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
