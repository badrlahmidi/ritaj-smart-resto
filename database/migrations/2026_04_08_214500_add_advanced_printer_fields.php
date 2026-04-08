<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            if (! Schema::hasColumn('printers', 'paper_width')) {
                $table->unsignedSmallInteger('paper_width')->default(80)->after('station_tags');
            }

            if (! Schema::hasColumn('printers', 'auto_cut')) {
                $table->boolean('auto_cut')->default(true)->after('paper_width');
            }

            if (! Schema::hasColumn('printers', 'cash_drawer')) {
                $table->boolean('cash_drawer')->default(false)->after('auto_cut');
            }

            if (! Schema::hasColumn('printers', 'max_retries')) {
                $table->unsignedTinyInteger('max_retries')->default(3)->after('cash_drawer');
            }
        });
    }

    public function down(): void
    {
        Schema::table('printers', function (Blueprint $table) {
            $columns = collect(['paper_width', 'auto_cut', 'cash_drawer', 'max_retries'])
                ->filter(fn ($column) => Schema::hasColumn('printers', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
