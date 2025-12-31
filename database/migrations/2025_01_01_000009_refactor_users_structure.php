<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_url')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('remember_token');
            // We'll drop the old pin_code column if it exists to clean up
            if (Schema::hasColumn('users', 'pin_code')) {
                $table->dropColumn('pin_code');
            }
        });

        // Create user_pins table
        Schema::create('user_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('pin_code'); // Hashed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_pins');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_url', 'is_active']);
            $table->string('pin_code')->nullable(); // Restore if rolling back
        });
    }
};
