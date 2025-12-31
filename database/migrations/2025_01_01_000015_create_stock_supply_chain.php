<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Suppliers
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category')->nullable(); // Boucher, Primeur
                $table->string('contact_name')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Purchase Orders (Factures Achat)
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained();
                $table->string('reference_no')->nullable(); // N° Facture Fournisseur
                $table->date('date');
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('status')->default('draft'); // draft, ordered, received
                $table->foreignId('user_id')->constrained(); // Created by
                $table->timestamp('received_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
                $table->foreignId('ingredient_id')->constrained(); // From Module 1.3
                $table->decimal('quantity', 10, 3); // 10.500 kg
                $table->decimal('unit_cost', 10, 2); // Prix unitaire
                $table->decimal('total_cost', 10, 2); // Qty * Unit Cost
                $table->timestamps();
            });
        }

        // 3. Inventory Checks (Inventaires Physiques)
        if (!Schema::hasTable('inventory_checks')) {
            Schema::create('inventory_checks', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->foreignId('user_id')->constrained();
                $table->string('status')->default('draft'); // draft, completed
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('inventory_check_items')) {
            Schema::create('inventory_check_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_check_id')->constrained()->onDelete('cascade');
                $table->foreignId('ingredient_id')->constrained();
                $table->decimal('expected_quantity', 10, 3); // Snapshot of theoretical stock
                $table->decimal('actual_quantity', 10, 3); // Counted stock
                $table->decimal('difference', 10, 3); // Actual - Expected
                $table->decimal('cost_difference', 10, 2); // Difference * Unit Cost
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_check_items');
        Schema::dropIfExists('inventory_checks');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
