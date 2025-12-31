<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
<<<<<<< HEAD
use Illuminate\Support\Facades\DB;
=======
use Illuminate\Support\Facades\DB; // N'oubliez pas cet import !
>>>>>>> 80726fd (Fix local migration issues manually)

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
<<<<<<< HEAD
            // On crée d'abord la colonne en tant que clé unique simple
            $table->unsignedBigInteger('local_id')->unique(); 
=======
            // 1. On crée d'abord la colonne en tant que clé UNIQUE (obligatoire pour l'auto-incrément secondaire)
            $table->unsignedBigInteger('local_id')->unique();
            
>>>>>>> 80726fd (Fix local migration issues manually)
            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->foreignId('waiter_id')->constrained('users');
            $table->enum('status', ['pending', 'sent_to_kitchen', 'ready', 'paid', 'cancelled'])->default('pending');
            $table->boolean('sync_status')->default(false)->index();
            $table->string('payment_method')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_uuid')->constrained('orders', 'uuid')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('notes')->nullable();
            $table->boolean('printed_kitchen')->default(false);
            $table->timestamps();
        });

<<<<<<< HEAD
        // Application de l'AUTO_INCREMENT sur local_id via SQL brut
        // MySQL exige qu'une colonne auto-increment soit une clé (ici UNIQUE)
=======
        // 2. On active l'AUTO_INCREMENT manuellement après la création
>>>>>>> 80726fd (Fix local migration issues manually)
        DB::statement('ALTER TABLE orders MODIFY local_id BIGINT UNSIGNED AUTO_INCREMENT');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
