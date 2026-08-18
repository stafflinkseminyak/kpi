<?php
/**
 * Save this file to: database/migrations/2026_08_18_150000_make_division_id_nullable_in_kpi_templates_table.php
 * (rename with today's actual timestamp prefix so it runs after the original
 * kpi_templates migration), then run: php artisan migrate
 *
 * Bug this fixes: saving a personal (employee_id-based) KPI template for an
 * employee who has no Contract yet — and no division_id set directly on
 * their Employee record either (e.g. a newly-added intern) — crashed with
 * "SQLSTATE[23000]: Integrity constraint violation: 1048 Column
 * 'division_id' cannot be null", because saveKpiTemplate() in
 * AdminKpiJobController.php has nothing to copy division_id from in that
 * case. division_id on a personal template is purely informational (shown
 * in the Saved Templates list so you can see what position the override is
 * for) — it is never part of how a personal template is looked up
 * (lookupKeys uses only employee_id, see saveKpiTemplate()) — so there's no
 * reason it must be non-null.
 *
 * Uses a raw statement (not ->change()) so this doesn't require the
 * doctrine/dbal package, matching how employee_id was added to this same
 * table. Can also be applied directly via SQL on production:
 *
 *   ALTER TABLE kpi_templates MODIFY division_id BIGINT UNSIGNED NULL;
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE kpi_templates MODIFY division_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        // Not reversible without first backfilling any NULL division_id rows —
        // left as a no-op rather than risk a failed rollback on production.
    }
};
