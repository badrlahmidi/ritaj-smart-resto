<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('synced_at')->nullable()->after('sync_status');
            
            // Index composites pour améliorer les performances
            $table->index(['sync_status', 'synced_at'], 'idx_sync_status_synced_at');
            $table->index(['status', 'created_at'], 'idx_status_created_at');
            $table->index(['table_id', 'status'], 'idx_table_status');
        });
        
        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_uuid', 'product_id'], 'idx_order_product');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_sync_status_synced_at');
            $table->dropIndex('idx_status_created_at');
            $table->dropIndex('idx_table_status');
            $table->dropColumn('synced_at');
        });
        
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_product');
        });
    }
};
