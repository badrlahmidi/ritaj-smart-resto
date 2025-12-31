<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Prix spécifiques optionnels
            $table->decimal('price_takeaway', 10, 2)->nullable()->after('price');
            $table->decimal('price_delivery', 10, 2)->nullable()->after('price_takeaway');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            // Ajout du type sur la commande (défaut : À table)
            $table->string('type')->default('dine_in')->after('status'); 
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_takeaway', 'price_delivery']);
        });
    }
};
