<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('server'); // admin, manager, server, kitchen
            }
            
            if (!Schema::hasColumn('users', 'pin_code')) {
                $table->string('pin_code')->nullable(); // For POS login
            }
            
            if (!Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // On ne drop que si la colonne existe pour éviter les erreurs
            $columnsToDrop = [];
            
            if (Schema::hasColumn('users', 'role')) $columnsToDrop[] = 'role';
            if (Schema::hasColumn('users', 'pin_code')) $columnsToDrop[] = 'pin_code';
            if (Schema::hasColumn('users', 'avatar_url')) $columnsToDrop[] = 'avatar_url';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
