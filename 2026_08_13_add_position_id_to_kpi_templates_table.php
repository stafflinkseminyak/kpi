<?php
/**
 * Save this file to: database/migrations/2026_08_13_150000_add_position_id_to_kpi_templates_table.php
 * (rename with today's actual timestamp prefix so it runs after the original
 * kpi_templates migration), then run: php artisan migrate
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_templates', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('sub_division_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kpi_templates', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });
    }
};
