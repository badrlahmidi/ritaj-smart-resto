<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('tables', function (Blueprint $table) {
            
            if (!Schema::hasColumn('tables', 'area_id')) {
                $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete()->after('id');
            }

            // Fix: Add capacity if it doesn't exist
            if (!Schema::hasColumn('tables', 'capacity')) {
                $table->integer('capacity')->default(4);
            }

            if (!Schema::hasColumn('tables', 'position_x')) {
                $table->integer('position_x')->default(0);
            }

            if (!Schema::hasColumn('tables', 'position_y')) {
                $table->integer('position_y')->default(0);
            }

            if (!Schema::hasColumn('tables', 'shape')) {
                $table->string('shape')->default('square');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            if (Schema::hasColumn('tables', 'area_id')) {
                $table->dropForeign(['area_id']);
                $table->dropColumn('area_id');
            }
            $table->dropColumn(['position_x', 'position_y', 'shape', 'capacity']);
        });

        Schema::dropIfExists('areas');
    }
};
