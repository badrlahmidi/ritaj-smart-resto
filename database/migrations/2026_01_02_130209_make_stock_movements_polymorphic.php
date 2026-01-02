<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Remove foreign key if it exists (need to check constraint name, usually table_column_foreign)
            // But since this is SQLite/MySQL specific, safer to just drop column. 
            // Ideally we'd drop foreign key first.
            $table->dropForeign(['ingredient_id']);
            $table->dropColumn('ingredient_id');
            
            // Add polymorphic columns
            $table->unsignedBigInteger('stockable_id');
            $table->string('stockable_type');
            $table->index(['stockable_id', 'stockable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['stockable_id', 'stockable_type']);
            $table->dropColumn(['stockable_id', 'stockable_type']);
            
            $table->foreignId('ingredient_id')->nullable()->constrained();
        });
    }
};