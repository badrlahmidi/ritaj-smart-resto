<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create orders table with UUID primary key
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            
            // Human readable ID (auto-incremented manually later)
            $table->unsignedBigInteger('local_id')->unique();
            
            // Relationships
            $table->foreignId('user_id')->constrained('users'); // The server/cashier
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            
            // Statuses
            $table->string('status')->default('pending'); // pending, sent_to_kitchen, ready, paid, cancelled
            $table->string('payment_status')->default('unpaid'); // unpaid, paid, partial
            $table->string('type')->default('dine_in'); // dine_in, takeaway, delivery
            
            // Financials
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable();
            
            // Sync/System
            $table->boolean('sync_status')->default(false)->index();
            $table->timestamps();
        });

        // Enable Auto Increment for local_id
        DB::statement('ALTER TABLE orders MODIFY local_id BIGINT UNSIGNED AUTO_INCREMENT');

        // Create order items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_uuid')->constrained('orders', 'uuid')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            
            // Quantities & Price
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            
            // Options & Notes
            $table->json('options')->nullable(); // Selected modifiers
            $table->string('notes')->nullable(); // Special instructions
            
            // Kitchen Flow
            $table->string('status')->default('pending'); // pending, sent, served
            $table->boolean('printed_kitchen')->default(false);
            $table->timestamp('printed_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};