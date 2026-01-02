<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_uuid')->constrained('orders', 'uuid')->cascadeOnDelete();
            $table->decimal('amount', 10, 2); // Amount actually paid in this transaction
            $table->string('payment_method')->default('cash'); // cash, card, transfer, free
            
            // Cash specifics
            $table->decimal('amount_tendered', 10, 2)->nullable(); // How much customer gave
            $table->decimal('change_due', 10, 2)->nullable(); // How much returned
            
            $table->string('transaction_reference')->nullable(); // For card/app
            $table->foreignId('user_id')->constrained(); // Cashier
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
