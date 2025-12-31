<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Units
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Kilogramme
            $table->string('symbol'); // kg
            $table->timestamps();
        });

        // 2. Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // 3. Stock Movements (Traceability)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // purchase, sale, waste, adjustment
            $table->decimal('quantity', 10, 4); // +/- quantity
            $table->decimal('cost', 10, 4)->default(0); // Cost at that moment
            $table->string('reference')->nullable(); // Order #123 or Invoice #INV-001
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });

        // 4. Update Ingredients table to link with Units and Suppliers
        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained();
            // We keep 'unit' string column for backward compat or migration script if needed, 
            // but ideally we should migrate data. For MVP v2, we assume fresh or we manually update.
        });

        // 5. Update Product_Ingredient pivot
        Schema::table('product_ingredient', function (Blueprint $table) {
            $table->decimal('wastage_percent', 5, 2)->default(0)->after('quantity');
        });
        
        // Seed default units
        DB::table('units')->insert([
            ['name' => 'Kilogramme', 'symbol' => 'kg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gramme', 'symbol' => 'g', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Litre', 'symbol' => 'L', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Millilitre', 'symbol' => 'ml', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pièce/Unité', 'symbol' => 'pcs', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('product_ingredient', function (Blueprint $table) {
            $table->dropColumn('wastage_percent');
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropColumn(['unit_id', 'supplier_id']);
        });

        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('units');
    }
};
