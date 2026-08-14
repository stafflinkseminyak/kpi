<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTemplate extends Model
{
    protected $table = 'kpi_templates';

    protected $fillable = [
        'division_id', 'sub_division_id', 'position_id', 'kpi_data', 'created_by',
    ];

    protected $casts = [
        'kpi_data' => 'array',
    ];

    public function division() { return $this->belongsTo(Division::class); }
    public function subDivision() { return $this->belongsTo(SubDivision::class); }
    public function position() { return $this->belongsTo(Position::class); }

    /**
     * Find the template that applies to a given Division/Sub-Division/Position
     * combination, falling back one level at a time to a broader template when no
     * exact match exists — a template set at the Division level applies to
     * everyone under it until someone adds a more specific one. Shared by the KPI
     * builder page (AdminKpiJobController::getKpiTemplate, live AJAX preview) and
     * forEmployee() below, so both resolve a person's KPI the same way.
     */
    public static function match($divisionId, $subDivisionId = null, $positionId = null): ?self
    {
        $subDivisionId = $subDivisionId ?: null;
        $positionId = $positionId ?: null;

        if ($subDivisionId && $positionId) {
            $exact = self::where('division_id', $divisionId)
                ->where('sub_division_id', $subDivisionId)
                ->where('position_id', $positionId)
                ->first();
            if ($exact) {
                return $exact;
            }
        }

        if ($subDivisionId) {
            $bySubDivision = self::where('division_id', $divisionId)
                ->where('sub_division_id', $subDivisionId)
                ->whereNull('position_id')
                ->first();
            if ($bySubDivision) {
                return $bySubDivision;
            }
        }

        return self::where('division_id', $divisionId)
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
     */
    public function assignedEmployees()
    {
        return \App\Models\Employee::whereHas('contract', function ($q) {
            $q->where('division_id', $this->division_id);
            if ($this->sub_division_id) {
                $q->whereRaw(
                    "CAST(JSON_UNQUOTE(JSON_EXTRACT(form_data, '$.sub_division_id')) AS UNSIGNED) = ?",
                    [$this->sub_division_id]
                );
            }
            if ($this->position_id) {
                $q->whereRaw(
                    "CAST(JSON_UNQUOTE(JSON_EXTRACT(form_data, '$.position_id')) AS UNSIGNED) = ?",
                    [$this->position_id]
                );
            }
        })->orderBy('first_name')->get();
    }

    /**
     * The reverse lookup of assignedEmployees(): given an employee, find the KPI
     * template that applies to them via their contract (division_id column +
     * sub_division_id/position_id stored in contracts.form_data). Returns null if
     * the employee has no contract, or no template has been set up for them yet.
     */
    public static function forEmployee(\App\Models\Employee $employee): ?self
    {
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