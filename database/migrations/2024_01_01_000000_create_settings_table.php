<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // Limitation explicite à 100 caractères pour éviter l'erreur "Key too long"
            // 100 + 100 = 200 chars * 4 bytes = 800 bytes (< 1000 bytes max)
            $table->string('group', 100);
            $table->string('name', 100);
            $table->boolean('locked')->default(false);
            $table->json('payload');
            $table->timestamps();
            
            $table->unique(['group', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
