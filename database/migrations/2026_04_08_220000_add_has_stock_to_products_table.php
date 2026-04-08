<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'has_stock')) {
            Schema::table('products', function (Blueprint $table) {
                // Indicates whether this product's stock should be deducted directly
                // (e.g. a bottled drink vs. a recipe-based dish handled via ingredients)
                $table->boolean('has_stock')->default(false)->after('is_available');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'has_stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('has_stock');
            });
        }
    }
};
