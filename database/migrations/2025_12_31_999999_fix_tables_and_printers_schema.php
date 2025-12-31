<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Fix 'tables' table - add missing columns if they don't exist
        Schema::table('tables', function (Blueprint $table) {
            if (!Schema::hasColumn('tables', 'status')) {
                $table->string('status')->default('available')->after('capacity'); // available, occupied, reserved
            }
            if (!Schema::hasColumn('tables', 'area_id')) {
                 $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete()->after('id');
            }
            if (!Schema::hasColumn('tables', 'shape')) {
                $table->string('shape')->default('square')->after('status');
            }
            if (!Schema::hasColumn('tables', 'position_x')) {
                $table->integer('position_x')->default(0);
            }
            if (!Schema::hasColumn('tables', 'position_y')) {
                $table->integer('position_y')->default(0);
            }
            if (!Schema::hasColumn('tables', 'current_order_uuid')) {
                 $table->string('current_order_uuid')->nullable();
            }
        });

        // 2. Fix 'printers' table - ensure columns exist and fix types
        Schema::table('printers', function (Blueprint $table) {
            if (!Schema::hasColumn('printers', 'type')) {
                $table->string('type')->default('network'); // network, usb
            }
            if (!Schema::hasColumn('printers', 'path')) {
                $table->string('path')->nullable(); // IP address or USB path
            }
            // Ensure is_active exists
            if (!Schema::hasColumn('printers', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
        
        // 3. Fix 'products' table for kitchen station if missing
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'kitchen_station')) {
             Schema::table('products', function (Blueprint $table) {
                $table->string('kitchen_station')->default('default')->nullable();
             });
        }
    }

    public function down()
    {
        // We typically don't drop columns in fix migrations to avoid data loss during rollbacks in dev
    }
};
