<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Printers Configuration
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Cuisine Chaude", "Bar"
            $table->string('type')->default('network'); // network, usb
            $table->string('ip_address')->nullable();
            $table->integer('port')->default(9100);
            $table->string('path')->nullable(); // For USB (/dev/usb/lp0) or Windows share
            $table->json('station_tags')->nullable(); // ["kitchen", "pizza"] - What stations this printer handles
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Print Jobs Queue (Database driven queue for reliability)
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printer_id')->constrained()->onDelete('cascade');
            $table->longText('content'); // ESC/POS binary content or JSON data
            $table->string('status')->default('pending'); // pending, processing, printed, failed
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('printers');
    }
};
