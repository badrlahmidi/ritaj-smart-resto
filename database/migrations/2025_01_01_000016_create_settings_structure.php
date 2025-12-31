<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            
            // A. Identity
            $table->string('restaurant_name')->default('Ritaj Smart Resto');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            
            // B. Receipt
            $table->text('receipt_header')->nullable(); // "Bienvenue"
            $table->text('receipt_footer')->nullable(); // "Merci"
            $table->boolean('show_wifi')->default(false);
            $table->string('wifi_ssid')->nullable();
            $table->string('wifi_password')->nullable();
            $table->boolean('show_server_name')->default(true);
            $table->boolean('show_tva_breakdown')->default(true);
            $table->string('qr_code_link')->nullable();

            // C. Workflow
            $table->string('service_mode')->default('standard'); // standard, fast_food
            $table->boolean('table_closure_auto')->default(true);
            $table->boolean('stock_block_sale')->default(false);
            $table->boolean('stock_allow_negative')->default(true);
            $table->decimal('default_vat_rate', 5, 2)->default(10.00);

            // D. Modules
            $table->boolean('module_kds')->default(true);
            $table->boolean('module_delivery')->default(false);
            $table->boolean('module_smart_cash')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
