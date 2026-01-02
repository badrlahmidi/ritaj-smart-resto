<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Discounts & Charges
            $table->decimal('discount_amount', 10, 2)->default(0)->after('total_amount');
            $table->string('discount_type')->nullable()->after('discount_amount'); // fixed, percentage
            $table->decimal('service_charge', 10, 2)->default(0)->after('discount_type');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('service_charge');
            
            // Customer / Delivery Info
            $table->string('customer_name')->nullable()->after('table_id');
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->text('customer_address')->nullable()->after('customer_phone');
            
            // Notes
            $table->text('notes')->nullable()->after('customer_address');
            
            // Reason for cancellation
            $table->string('cancel_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'discount_amount', 'discount_type', 'service_charge', 'tax_amount',
                'customer_name', 'customer_phone', 'customer_address', 'notes', 'cancel_reason'
            ]);
        });
    }
};