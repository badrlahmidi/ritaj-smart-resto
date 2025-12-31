<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Server who opened it
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->string('type')->default('dine_in'); // dine_in, takeaway, delivery
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Unit price at moment of sale
            $table->decimal('total', 10, 2); // (price * qty) + options
            
            // Smart Features
            $table->json('options')->nullable(); // Snapshot of selected options [{name: "Saignant", price: 0}]
            $table->string('note')->nullable(); // "Sans oignon"
            $table->string('status')->default('pending'); // pending (new in cart), sent (in kitchen), served
            $table->timestamp('printed_at')->nullable(); // To avoid double printing
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
