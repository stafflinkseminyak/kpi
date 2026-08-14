<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds employee_id (nullable) to kpi_templates — a template with this set is a
 * per-person KPI override (e.g. an in-house promotion that adds/changes KPIs
 * for one specific employee without a new Contract), separate from the
 * regular Division/Sub-Division/Position templates shared by everyone in that
 * scope. See KpiTemplate::forEmployee() for how a person-specific template
 * takes priority over the position-based one when resolving someone's KPI.
 *
 * No foreign key constraint, matching how position_id was added — kept as a
 * plain nullable column so this can also be applied directly via SQL on
 * production without worrying about constraint/ordering issues:
 *
 *   ALTER TABLE kpi_templates ADD COLUMN employee_id BIGINT UNSIGNED NULL AFTER position_id;
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('position_id');
        });
    }

    public function down(): void
    {
        Schema::table('kpi_templates', function (Blueprint $table) {
            $table->dropColumn('employee_id');
        });
    }
};
