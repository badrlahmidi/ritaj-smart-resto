<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('tables', function (Blueprint $table) {
            // Drop old columns if they exist (cleanup)
            // $table->dropColumn(['status']); // We keep status but might need to migrate enum values
            
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete()->after('id');
            $table->integer('position_x')->default(0)->after('capacity');
            $table->integer('position_y')->default(0)->after('position_x');
            $table->string('shape')->default('square')->after('position_y'); // square, round, rectangle
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn(['area_id', 'position_x', 'position_y', 'shape']);
        });

        Schema::dropIfExists('areas');
    }
};
