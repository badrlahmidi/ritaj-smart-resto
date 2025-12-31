<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit')->default('kg'); // kg, l, unit, g
            $table->decimal('cost_per_unit', 10, 4)->default(0); // Prix d'achat
            $table->decimal('stock_quantity', 10, 4)->default(0);
            $table->decimal('alert_threshold', 10, 4)->default(1);
            $table->timestamps();
        });

        Schema::create('product_ingredient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 4); // Quantité nécessaire pour 1 recette
            $table->string('unit')->nullable(); // Unité utilisée dans la recette (optionnel si différent)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_ingredient');
        Schema::dropIfExists('ingredients');
    }
};
