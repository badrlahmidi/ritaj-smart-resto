<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Option Groups (Cuisson, Sauces...)
        Schema::create('option_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_multiselect')->default(false); // Radio vs Checkbox
            $table->integer('max_options')->nullable(); // Limit for multiselect
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        // 2. Options (Bleu, Saignant, Mayo...)
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('option_group_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price_modifier', 10, 2)->default(0);
            $table->timestamps();
        });

        // 3. Pivot: Products <-> Option Groups
        Schema::create('option_group_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('option_group_id')->constrained()->onDelete('cascade');
            $table->integer('sort_order')->default(0);
        });

        // 4. Update Products Table
        Schema::table('products', function (Blueprint $table) {
            $table->string('short_description')->nullable()->after('name');
            $table->string('kitchen_station')->default('kitchen')->after('category_id'); // kitchen, bar, pizza
            $table->boolean('is_combo')->default(false)->after('kitchen_station');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['short_description', 'kitchen_station', 'is_combo']);
        });

        Schema::dropIfExists('option_group_product');
        Schema::dropIfExists('options');
        Schema::dropIfExists('option_groups');
    }
};
