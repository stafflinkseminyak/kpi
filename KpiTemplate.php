<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTemplate extends Model
{
    protected $table = 'kpi_templates';

    protected $fillable = [
        'division_id', 'sub_division_id', 'position_id', 'employee_id', 'kpi_data', 'created_by',
    ];

    protected $casts = [
        'kpi_data' => 'array',
    ];

    public function division() { return $this->belongsTo(Division::class); }
    public function subDivision() { return $this->belongsTo(SubDivision::class); }
    public function position() { return $this->belongsTo(Position::class); }
    public function employee() { return $this->belongsTo(Employee::class); }

    /**
     * Find the template that applies to a given Division/Sub-Division/Position
     * combination, falling back one level at a time to a broader template when no
     * exact match exists — a template set at the Division level applies to
     * everyone under it until someone adds a more specific one. Shared by the KPI
     * builder page (AdminKpiJobController::getKpiTemplate, live AJAX preview) and
     * forEmployee() below, so both resolve a person's KPI the same way.
     *
     * Always excludes person-specific templates (employee_id set) — those are
     * scoped to exactly one employee (see forEmployee()), and without this a
     * personal override could accidentally get handed to a *different* employee
     * who later shares that same Division/Sub-Division/Position.
     */
    public static function match($divisionId, $subDivisionId = null, $positionId = null): ?self
    {
        $subDivisionId = $subDivisionId ?: null;
        $positionId = $positionId ?: null;

        if ($subDivisionId && $positionId) {
            $exact = self::whereNull('employee_id')
                ->where('division_id', $divisionId)
                ->where('sub_division_id', $subDivisionId)
                ->where('position_id', $positionId)
                ->first();
            if ($exact) {
                return $exact;
            }
        }

        if ($subDivisionId) {
            $bySubDivision = self::whereNull('employee_id')
                ->where('division_id', $divisionId)
                ->where('sub_division_id', $subDivisionId)
                ->whereNull('position_id')
                ->first();
            if ($bySubDivision) {
                return $bySubDivision;
            }
        }

        return self::whereNull('employee_id')
            ->where('division_id', $divisionId)
            ->whereNull('sub_division_id')
            ->whereNull('position_id')
            ->first();
    }

    /**
     * Employees currently mapped to this template, resolved via their contract
     * (contracts.division_id + the sub_division_id/position_id stored inside
     * contracts.form_data, since neither is a dedicated column on contracts).
     * Used on the KPI list page so an admin can see who a template actually
     * affects before saving. Note: a broad template (no sub_division_id/position_id
     * set) will list everyone in the division here, even though a more specific
     * template may actually be the one that applies to some of them once
     * match()'s fallback runs — this is an informational "who's in scope" list,
     * not a precise "this exact template is theirs" guarantee.
     *
     * A person-specific template (employee_id set) skips all of that — it's
     * always exactly the one employee it was made for.
     */
    public function assignedEmployees()
    {
        if ($this->employee_id) {
            return $this->employee ? collect([$this->employee]) : collect();
        }

        return \App\Models\Employee::whereHas('contract', function ($q) {
            $q->where('division_id', $this->division_id);
            // Laravel's `column->key` JSON path syntax compiles to the right
            // JSON_EXTRACT (+ unquoting) SQL per database driver on its own —
            // portable across MySQL (production) and SQLite (local testing),
            // unlike the raw MySQL-only SQL this used to run. Compared as
            // strings on both sides since the value inside form_data was
            // written from an HTTP form field (so it's a JSON string, e.g.
            // "5"), not a JSON number — SQLite in particular won't treat
            // that as equal to a bound PHP int.
            if ($this->sub_division_id) {
                $q->where('form_data->sub_division_id', (string) $this->sub_division_id);
            }
            if ($this->position_id) {
                $q->where('form_data->position_id', (string) $this->position_id);
            }
        })->orderBy('first_name')->get();
    }

    /**
     * The reverse lookup of assignedEmployees(): given an employee, find the KPI
     * template that applies to them. A person-specific override (employee_id ===
     * $employee->id) always wins when one exists — e.g. an in-house promotion
     * that added/changed KPIs for just this person without a new Contract.
     * Otherwise falls back to their contract's Division/Sub-Division/Position via
     * match(), same as before. Returns null only if neither exists.
     */
    public static function forEmployee(\App\Models\Employee $employee): ?self
    {
        $personal = self::where('employee_id', $employee->id)->first();
        if ($personal) {
            return $personal;
        }

        $contract = $employee->contract;
        if (!$contract) {
            return null;
        }

        $formData = is_array($contract->form_data) ? $contract->form_data : [];

        return self::match(
            $contract->division_id,
            $formData['sub_division_id'] ?? null,
            $formData['position_id'] ?? null
        );
    }

    /**
     * Areas array from kpi_data, tolerant of the same shapes the KPI builder
     * page already handles (a plain numeric-keyed array of areas, or wrapped
     * under an 'areas' key alongside 'ytd_dashboard').
     */
    public function areas(): array
    {
        $data = $this->kpi_data ?? [];
        if (!is_array($data)) {
            return [];
        }
        if (isset($data['areas']) && is_array($data['areas'])) {
            return $data['areas'];
        }
        return array_values(array_filter($data, function ($v, $k) {
            return $k !== 'ytd_dashboard' && is_array($v) && isset($v['key_result_area']);
        }, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Turns this template's areas/indicators into the data the "Goals & KPI"
     * partial (resources/views/admin/kpi/goals-cards.blade.php) renders — one
     * group per Key Result Area, each with a weighted-average gauge and its
     * indicators' individual progress. Only indicators whose target_type is
     * number/currency/percentage AND have both a target and an actual value
     * can be calculated; everything else shows as "no data yet" rather than
     * guessing. Shared by the Performance page and the Employee Profile KPI tab
     * so both stay in sync automatically — there is nothing to keep synced
     * manually.
     */
    public function goalGroups(): array
    {
        $groups = [];

        foreach ($this->areas() as $area) {
            $indicators = [];

            foreach (($area['indicators'] ?? []) as $ind) {
                $type = $ind['target_type'] ?? 'text';
                $targetNum = self::parseNumeric($ind['target'] ?? null, $type);
                $actualNum = self::parseNumeric($ind['actual'] ?? null, $type);
                $canCompute = in_array($type, ['number', 'currency', 'percentage'], true)
                    && $targetNum !== null && $targetNum > 0;
                $pct = ($canCompute && $actualNum !== null)
                    ? (int) min(100, round(($actualNum / $targetNum) * 100))
                    : null;

                $indicators[] = [
                    'label' => $ind['kpi'] ?? '',
                    'weight' => self::parseNumeric($ind['weight'] ?? null),
                    'target' => $ind['target'] ?? '',
                    'actual' => $ind['actual'] ?? '',
                    'type' => $type,
                    'pct' => $pct,
                    'status' => self::statusFor($pct),
                ];
            }

            $computable = array_values(array_filter($indicators, fn ($i) => $i['pct'] !== null));
            $totalWeight = array_sum(array_map(fn ($i) => $i['weight'] ?? 0, $computable));
            $weightedSum = array_sum(array_map(fn ($i) => $i['pct'] * ($i['weight'] ?? 0), $computable));

            $areaPct = null;
            if (count($computable)) {
                $areaPct = $totalWeight > 0
                    ? (int) round($weightedSum / $totalWeight)
                    : (int) round(array_sum(array_map(fn ($i) => $i['pct'], $computable)) / count($computable));
            }
            $areaStatus = self::statusFor($areaPct);

            $groups[] = [
                'area' => $area['key_result_area'] ?: '(untitled area)',
                'area_pct' => $areaPct,
                'area_status' => $areaStatus,
                'area_gauge_svg' => self::gaugeSvg($areaPct ?? 0, $areaStatus['color']),
                'indicators' => $indicators,
            ];
        }

        return $groups;
    }

    /**
     * A single-number "how's this person doing overall" rollup across every
     * indicator in every area — the weighted average goalGroups() computes per
     * area, taken one level higher. Used by the Employee Profile KPI tab for a
     * compact summary + motivational message; the full per-area/per-indicator
     * detail (goalGroups()) is what the Performance page shows instead.
     */
    public function overallSummary(): array
    {
        $allIndicators = collect($this->goalGroups())->flatMap(fn ($g) => $g['indicators']);
        $computable = $allIndicators->filter(fn ($i) => $i['pct'] !== null);

        $totalWeight = $computable->sum(fn ($i) => $i['weight'] ?? 0);
        $weightedSum = $computable->sum(fn ($i) => $i['pct'] * ($i['weight'] ?? 0));

        $overallPct = null;
        if ($computable->count()) {
            $overallPct = $totalWeight > 0
                ? (int) round($weightedSum / $totalWeight)
                : (int) round($computable->avg('pct'));
        }

        return [
            'overall_pct' => $overallPct,
            'status' => self::statusFor($overallPct),
            'total_indicators' => $allIndicators->count(),
            'achieved_count' => $allIndicators->filter(fn ($i) => ($i['pct'] ?? -1) >= 100)->count(),
            'at_risk_count' => $allIndicators->filter(fn ($i) => $i['pct'] !== null && $i['pct'] < 50)->count(),
            'no_data_count' => $allIndicators->filter(fn ($i) => $i['pct'] === null)->count(),
        ];
    }

    private static function parseNumeric($value, ?string $type = null): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $raw = (string) $value;
        // Currency values are auto-formatted with '.' as a THOUSANDS separator
        // (e.g. "IDR 7.600.000" — see formatByType() in kpi-list.blade.php), never
        // as a decimal point. Stripping only digits/minus avoids PHP's float cast
        // truncating at the second '.' and misreading 7,600,000 as 7.6.
        $cleaned = $type === 'currency'
            ? preg_replace('/[^\d\-]/', '', $raw)
            : preg_replace('/[^\d.\-]/', '', $raw);
        return ($cleaned === '' || $cleaned === '-' || !is_numeric($cleaned)) ? null : (float) $cleaned;
    }

    private static function statusFor(?int $pct): array
    {
        if ($pct === null) {
            return ['color' => '#9ca3af', 'label' => '📋 No data yet', 'bg' => '#f3f4f6', 'txt' => '#6b7280', 'emoji' => '📋'];
        }
        if ($pct >= 100) {
            return ['color' => '#1baf7a', 'label' => '✅ Achieved', 'bg' => '#eaf3de', 'txt' => '#27500a', 'emoji' => '🎉'];
        }
        if ($pct >= 50) {
            return ['color' => '#534ab7', 'label' => '💪 In progress', 'bg' => '#eeedfe', 'txt' => '#3c3489', 'emoji' => '💪'];
        }
        return ['color' => '#e34948', 'label' => '⚠️ At risk', 'bg' => '#fcebeb', 'txt' => '#a32d2d', 'emoji' => '⚡'];
    }

    /**
     * Same half-circle gauge already used for the YTD Dashboard cards (see
     * createGaugeSVG in kpi-list.blade.php) and the KPI page's Preview modal —
     * reproduced server-side here so Performance and Employee Profile render
     * an identical diagram without needing JavaScript.
     */
    public static function gaugeSvg($percent, string $color): string
    {
        $capped = max(0, min((int) $percent, 100));
        $radius = 40;
        $circumference = M_PI * $radius;
        $offset = $circumference - ($capped / 100) * $circumference;

        return '<svg viewBox="0 0 120 70" width="90" height="53">'
            . '<path d="M 10 60 A 40 40 0 0 1 110 60" fill="none" stroke="#e5e7eb" stroke-width="10"/>'
            . '<path d="M 10 60 A 40 40 0 0 1 110 60" fill="none" stroke="' . $color . '" stroke-width="10" stroke-linecap="round" '
            . 'stroke-dasharray="' . $circumference . '" stroke-dashoffset="' . $offset . '"/>'
            . '<text x="30" y="55" style="font-size:14px;font-weight:800;fill:#1f2937;">' . $capped . '%</text>'
            . '</svg>';
    }

    /**
     * Builds starter KRA/KPI/Weight/Target rows from the Responsibilities
     * catalog already maintained for this Position on the Contract page
     * (Responsibility::position_id) — one Key Result Area per responsibility,
     * its description as the KPI text, weight split evenly across however
     * many responsibilities exist (still fully editable afterwards), and
     * Target left as an explicit placeholder since responsibilities don't
     * carry a measurable number. Returns [] when no Position is given, or
     * that Position has no responsibilities catalogued yet — the builder
     * just starts blank in that case (see respondWithKpiTemplate()) rather
     * than falling back to the old generic one-size-fits-all placeholder.
     */
    public static function defaultKpiDataForPosition($positionId): array
    {
        if (empty($positionId)) {
            return [];
        }

        $responsibilities = \App\Models\Responsibility::where('position_id', $positionId)
            ->orderBy('id')
            ->get();

        $count = $responsibilities->count();
        if ($count === 0) {
            return [];
        }

        $baseWeight = round(100 / $count, 2);
        $assigned = 0.0;
        $areas = [];

        foreach ($responsibilities as $i => $r) {
            $isLast = $i === $count - 1;
            // Last item soaks up the rounding remainder so weights always sum to 100.
            $weight = $isLast ? round(100 - $assigned, 2) : $baseWeight;
            $assigned += $weight;
            $weightDisplay = rtrim(rtrim(number_format($weight, 2), '0'), '.');

            $kraLabel = $r->title_en ?: $r->title_id ?: ('Responsibility ' . ($i + 1));
            // The 'kpi'/'target' fields are the primary ones (English, in
            // practice — see the builder's field labels), 'kpi_en'/'target_en'
            // the secondary translation slot (Indonesian, despite the "_en"
            // name — the field names are legacy and no longer match what
            // actually goes in them).
            $kpiPrimary = $r->description_en ?: $r->title_en ?: $kraLabel;
            $kpiSecondary = $r->description_id ?: $r->title_id ?: $kraLabel;

            $areas[] = [
                'no' => $i + 1,
                'key_result_area' => $kraLabel,
                'indicators' => [
                    [
                        'kpi' => $kpiPrimary,
                        'kpi_en' => $kpiSecondary,
                        'weight' => $weightDisplay,
                        'target' => 'To be defined',
                        'target_en' => 'Akan ditentukan',
                    ],
                ],
            ];
        }

        return $areas;
    }

    public static function defaultKpiData(): array
    {
        return [
            [
                'no' => 1,
                'key_result_area' => 'Recruitment',
                'indicators' => [
                    ['kpi' => 'Average lead time', 'weight' => '', 'target' => '60 calendar days'],
                    ['kpi' => 'Performance score within 6 months', 'weight' => '', 'target' => '8'],
                ]
            ],
            [
                'no' => 2,
                'key_result_area' => 'Training and Development',
                'indicators' => [
                    ['kpi' => 'Training Hours per year', 'weight' => '', 'target' => '40 hours/year'],
                    ['kpi' => '% difference in productivity before and after training', 'weight' => '10', 'target' => '50%'],
                ]
            ],
            [
                'no' => 3,
                'key_result_area' => 'Performance and Career Management',
                'indicators' => [
                    ['kpi' => '% of employees that fully execute their Individual Development Plan', 'weight' => '10', 'target' => '90%'],
                    ['kpi' => '% of employees that participate in career coaching program', 'weight' => '15', 'target' => '90%'],
                ]
            ],
            [
                'no' => 4,
                'key_result_area' => 'Employee Retention and Productivity',
                'indicators' => [
                    ['kpi' => '% of employees that leave the organization in a given time period', 'weight' => '15', 'target' => '2%'],
                    ['kpi' => 'Profit per employee', 'weight' => '10', 'target' => 'USD 2 Mio'],
                ]
            ],
        ];
    }
}