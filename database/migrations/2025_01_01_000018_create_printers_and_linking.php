<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create Printers Table
        if (!Schema::hasTable('printers')) {
            Schema::create('printers', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // ex: Epson Cuisine
                $table->string('type')->default('network'); // network, windows (usb/shared)
                
                // Network details
                $table->string('ip_address')->nullable(); // 192.168.1.200
                $table->integer('port')->default(9100);
                
                // Windows/USB details
                $table->string('path')->nullable(); // SMB share or Windows Printer Name
                
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add Printer to Categories (Routing)
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('printer_id')->nullable()->constrained('printers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign(['printer_id']);
                $table->dropColumn('printer_id');
            });
        }
        Schema::dropIfExists('printers');
    }
};
