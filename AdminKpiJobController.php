<?php

namespace App\Http\Controllers;

use App\Models\KpiJobDescription;
use App\Models\KpiTemplate;
use App\Models\Division;
use App\Models\SubDivision;
use App\Models\Position;
use Illuminate\Http\Request;

class AdminKpiJobController extends Controller
{
    private function ensureSuperAdmin()
    {
        abort_unless(in_array(auth()->user()?->role, ['super_admin'], true), 403);
    }


public function index(Request $request)
    {
        $this->ensureSuperAdmin();
        $user = $request->user();
        $isSuperAdmin = $user && in_array($user->role, ['super_admin', 'admin'], true);
        $statusFilter = $request->get('status', 'all');

        $query = KpiJobDescription::query()->latest();

        // Role-based visibility (same rules as contracts)
        if (!$isSuperAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        if ($statusFilter === 'pending_approval') {
            $query->where(function ($q) {
                $q->where('kpi_status', 'pending_approval')
                  ->orWhere('jd_status', 'pending_approval');
            });
        } elseif ($statusFilter === 'approved') {
            $query->where('kpi_status', 'approved')->where('jd_status', 'approved');
        } elseif ($statusFilter === 'rejected') {
            $query->where(function ($q) {
                $q->where('kpi_status', 'rejected')
                  ->orWhere('jd_status', 'rejected');
            });
        }

        $records = $query->get();
        $allRecords = KpiJobDescription::query();
        if (!$isSuperAdmin) {
            $allRecords->where('created_by', $user->id);
        }
        $totalCount = $allRecords->count();

        $pendingCount = KpiJobDescription::query()
            ->when(!$isSuperAdmin, fn($q) => $q->where('created_by', $user->id))
            ->where(fn($q) => $q->where('kpi_status', 'pending_approval')->orWhere('jd_status', 'pending_approval'))
            ->count();

        $approvedCount = KpiJobDescription::query()
            ->when(!$isSuperAdmin, fn($q) => $q->where('created_by', $user->id))
            ->where('kpi_status', 'approved')
            ->where('jd_status', 'approved')
            ->count();

        $rejectedCount = KpiJobDescription::query()
            ->when(!$isSuperAdmin, fn($q) => $q->where('created_by', $user->id))
            ->where(fn($q) => $q->where('kpi_status', 'rejected')->orWhere('jd_status', 'rejected'))
            ->count();

        return view('admin.kpi.index', compact(
            'records', 'totalCount', 'pendingCount', 'approvedCount', 'rejectedCount',
            'statusFilter', 'isSuperAdmin'
        ));
    }

    public function showKpi(KpiJobDescription $record)
    {
        $this->ensureSuperAdmin();
        $kpiData = $record->kpi_data ?? KpiJobDescription::defaultKpiData();
        $isSuperAdmin = in_array(request()->user()?->role, ['super_admin', 'admin'], true);

        return view('admin.kpi.kpi-show', compact('record', 'kpiData', 'isSuperAdmin'));
    }

    public function saveKpi(Request $request, KpiJobDescription $record)
    {
        $this->ensureSuperAdmin();
        $record->update([
            'kpi_data' => $request->input('kpi_data', []),
        ]);

        return redirect()->route('admin.kpi-jd.index')
            ->with('success', 'KPI targets saved successfully.');
    }

    public function approveKpi(KpiJobDescription $record)
    {
        $this->ensureSuperAdmin();
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);
        $record->update([
            'kpi_status' => 'approved',
            'kpi_approved_by' => request()->user()->id,
            'kpi_approved_at' => now(),
        ]);
        return redirect()->route('admin.kpi-jd.index')->with('success', 'KPI approved.');
    }

    public function rejectKpi(KpiJobDescription $record)
    {
        $this->ensureSuperAdmin();
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);
        $record->update([
            'kpi_status' => 'rejected',
            'kpi_rejected_by' => request()->user()->id,
            'kpi_rejected_at' => now(),
        ]);
        return redirect()->route('admin.kpi-jd.index')->with('success', 'KPI rejected.');
    }

    public function approveJd(KpiJobDescription $record)
    {
        $this->ensureSuperAdmin();
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);
        $record->update([
            'jd_status' => 'approved',
            'jd_approved_by' => request()->user()->id,
            'jd_approved_at' => now(),
        ]);
        return redirect()->route('admin.kpi-jd.index')->with('success', 'Job Description approved.');
    }

    public function rejectJd(KpiJobDescription $record)
    {
        $this->ensureSuperAdmin();
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);
        $record->update([
            'jd_status' => 'rejected',
            'jd_rejected_by' => request()->user()->id,
            'jd_rejected_at' => now(),
        ]);
        return redirect()->route('admin.kpi-jd.index')->with('success', 'Job Description rejected.');
    }

    public function destroy(KpiJobDescription $record)
    {
        $this->ensureSuperAdmin();
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);
        $record->delete();
        return redirect()->route('admin.kpi-jd.index')->with('success', 'Record deleted.');
    }

    public function kpiList(Request $request)
    {
        $this->ensureSuperAdmin();
        $user = $request->user();
        $isSuperAdmin = $user && in_array($user->role, ['super_admin', 'admin'], true);

        $records = KpiJobDescription::query()->latest();
        if (!$isSuperAdmin) {
            $records->where('created_by', $user->id);
        }
        $records = $records->get();

        $divisions = Division::orderBy('name')->get();
        $subDivisions = SubDivision::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        // ==================== EMPLOYEES NEEDING A KPI ====================
        // Everyone with a contract but no KPI template resolving to them yet — via
        // the exact same Division/Sub-Division/Position resolution KpiTemplate::
        // forEmployee() uses for the Performance page and Employee Profile, so
        // this list can never disagree with what those pages consider "covered".
        // A to-do list for admins: click through to pre-fill the builder above.
        $divisionsById = $divisions->keyBy('id');
        $subDivisionsById = $subDivisions->keyBy('id');
        $positionsById = $positions->keyBy('id');

        // Only exclude people who've actually left — someone still "joining soon"
        // (contract in, onboarding/account not finished yet) still has a real
        // contracted position and needs a KPI just as much as anyone already
        // active. Login/account status has nothing to do with it.
        //
        // NOT `where('status', '!=', 'terminated')` — in SQL, NULL != 'terminated'
        // evaluates to NULL, not true, so a plain != silently drops every row
        // whose status is blank/unset instead of including them. That made
        // employees with no status set invisible to this whole feature: not in
        // "needs a KPI", not in "already covered", not even in the "not linked to
        // a Contract" list below, since they never reached that split at all.
        $nonTerminatedEmployees = \App\Models\Employee::with(['contract', 'division'])
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'terminated');
            })
            ->orderBy('first_name')
            ->get();

        // Employee.contract_id has to actually be set for someone to be checked at
        // all — an Employee record that predates this link (or was never
        // connected to its Contract) is invisible to this whole feature, same as
        // it already is to assignedEmployees() on the Saved Templates list. Listed
        // by name below (not just counted) so it's something an admin can actually
        // go fix, not just a mystery gap.
        $employeesWithContract = $nonTerminatedEmployees->whereNotNull('contract_id');
        $employeesWithoutContract = $nonTerminatedEmployees->whereNull('contract_id')->values();
        $employeesWithoutContractCount = $employeesWithoutContract->count();

        $employeesNeedingKpi = $employeesWithContract
            // A template that resolves but has zero KPI areas (e.g. an old dummy/
            // placeholder record someone started and never filled in) doesn't
            // actually give this person a KPI — treat it the same as no template
            // at all, instead of silently marking them "covered".
            ->filter(function ($e) {
                $tpl = \App\Models\KpiTemplate::forEmployee($e);
                return $tpl === null || empty($tpl->areas());
            })
            ->map(function ($e) use ($divisionsById, $subDivisionsById, $positionsById) {
                $formData = is_array($e->contract?->form_data) ? $e->contract->form_data : [];
                $divisionId = $e->contract?->division_id;
                $subDivisionId = $formData['sub_division_id'] ?? null;
                $positionId = $formData['position_id'] ?? null;

                return [
                    'employee' => $e,
                    'division_id' => $divisionId,
                    'sub_division_id' => $subDivisionId,
                    'position_id' => $positionId,
                    'division_name' => $divisionId ? optional($divisionsById->get($divisionId))->name : null,
                    'sub_division_name' => $subDivisionId ? optional($subDivisionsById->get($subDivisionId))->name : null,
                    'position_name' => $positionId ? optional($positionsById->get($positionId))->name : null,
                ];
            })
            ->values();

        $employeesAlreadyCoveredCount = $employeesWithContract->count() - $employeesNeedingKpi->count();

        // For the "By Person" mode's employee picker — everyone non-terminated,
        // not just those with a linked Contract. "By Person" mode exists
        // precisely to cover people the Contract-based flow can't reach (e.g.
        // an employee whose contract predates this system and was made
        // manually, so there's no Contract record to auto-link at all) — so
        // restricting it to $employeesWithContract only would defeat that.
        // saveKpiTemplate() already falls back to the Employee's own
        // division_id when there's no Contract to read one from.
        $employeesForKpiPicker = $nonTerminatedEmployees->values();

        return view('admin.kpi.kpi-list', compact(
            'records', 'isSuperAdmin', 'divisions', 'subDivisions', 'positions', 'employeesNeedingKpi',
            'employeesWithoutContract', 'employeesWithoutContractCount', 'employeesAlreadyCoveredCount',
            'employeesForKpiPicker'
        ));
    }

    public function jdList(Request $request)
    {
        $this->ensureSuperAdmin();
        $user = $request->user();
        $isSuperAdmin = $user && in_array($user->role, ['super_admin', 'admin'], true);

        $records = KpiJobDescription::query()->latest();
        if (!$isSuperAdmin) {
            $records->where('created_by', $user->id);
        }
        $records = $records->get();

        $divisions = Division::orderBy('name')->get();
        $subDivisions = SubDivision::orderBy('name')->get();

        return view('admin.kpi.jd-list', compact('records', 'isSuperAdmin', 'divisions', 'subDivisions'));
    }

    public function getKpiTemplate($divisionId, $subDivisionId = null, $positionId = null)
    {
        $this->ensureSuperAdmin();

        $subDivisionId = ($subDivisionId && $subDivisionId !== '0') ? $subDivisionId : null;
        $positionId = ($positionId && $positionId !== '0') ? $positionId : null;

        // Most-specific-first fallback (division+sub-division+position, then
        // division+sub-division, then division-only) — see KpiTemplate::match().
        $template = KpiTemplate::match($divisionId, $subDivisionId, $positionId);

        return $this->respondWithKpiTemplate($template);
    }

    /**
     * "By Person" counterpart to getKpiTemplate() above — used when the builder
     * is scoped to one specific employee instead of a Division/Sub-Division/
     * Position (e.g. an in-house promotion that needs its own KPIs). Reuses
     * KpiTemplate::forEmployee(), which already checks for a personal override
     * first and falls back to their resolved position-level template — so a
     * brand-new personal KPI naturally starts as a copy of whatever they
     * currently have, exactly like "Duplicate" does for position templates.
     */
    public function getKpiTemplateForEmployee($employeeId)
    {
        $this->ensureSuperAdmin();

        $employee = \App\Models\Employee::findOrFail($employeeId);
        $template = KpiTemplate::forEmployee($employee);
        $isPersonal = $template && (int) $template->employee_id === (int) $employee->id;

        return $this->respondWithKpiTemplate($template, [
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            // Tells the builder whether Save will update an existing personal
            // KPI or create a brand-new one (starting from the copy above).
            'is_personal' => $isPersonal,
        ]);
    }

    /**
     * Shared by getKpiTemplate() (Division/Sub-Division/Position) and
     * getKpiTemplateForEmployee() (one specific person) — everything past
     * "which template did we resolve" (data-shape normalization, live YTD
     * Dashboard numbers, JSON response) is identical either way.
     */
    private function respondWithKpiTemplate(?KpiTemplate $template, array $extra = [])
    {
        $kpiData = $template ? $template->kpi_data : KpiTemplate::defaultKpiData();

        // Ensure data is in new array format (not old associative format)
        if (is_array($kpiData) && !empty($kpiData)) {
            // If it has 'ytd_dashboard' key, extract the areas separately
            $areas = [];
            $ytd = null;
            if (isset($kpiData['ytd_dashboard'])) {
                $ytd = $kpiData['ytd_dashboard'];
            }
            foreach ($kpiData as $k => $v) {
                if ($k !== 'ytd_dashboard' && is_array($v) && isset($v['key_result_area'])) {
                    $areas[] = $v;
                } elseif (is_numeric($k) && is_array($v) && isset($v['key_result_area'])) {
                    $areas[] = $v;
                }
            }
            if (!empty($areas)) {
                $kpiData = $areas;
                if ($ytd) {
                    $kpiData = ['areas' => $areas, 'ytd_dashboard' => $ytd];
                }
            }
        }

        // Inject live contract counts for FT/PT employee cards
        $thisYear = now()->year;

        // Active employees = users who have an approved contract (active profile with login)
        $ftActive = \DB::table('contracts')
            ->join('users', 'contracts.employee_user_id', '=', 'users.id')
            ->where('contracts.status', 'approved')
            ->where('contracts.employment_basis', 'LIKE', '%full%')
            ->distinct('contracts.employee_user_id')->count('contracts.employee_user_id');

        $ptActive = \DB::table('contracts')
            ->join('users', 'contracts.employee_user_id', '=', 'users.id')
            ->where('contracts.status', 'approved')
            ->where('contracts.employment_basis', 'LIKE', '%part%')
            ->distinct('contracts.employee_user_id')->count('contracts.employee_user_id');

        // This Year = total contracts issued (approved) this year, regardless of status changes
        $ftThisYear = \DB::table('contracts')->where('status', 'approved')
            ->whereYear('created_at', $thisYear)
            ->where('employment_basis', 'LIKE', '%full%')->count();
        $ptThisYear = \DB::table('contracts')->where('status', 'approved')
            ->whereYear('created_at', $thisYear)
            ->where('employment_basis', 'LIKE', '%part%')->count();

        // Last Year
        $ftLastYear = \DB::table('contracts')->where('status', 'approved')
            ->whereYear('created_at', $thisYear - 1)
            ->where('employment_basis', 'LIKE', '%full%')->count();
        $ptLastYear = \DB::table('contracts')->where('status', 'approved')
            ->whereYear('created_at', $thisYear - 1)
            ->where('employment_basis', 'LIKE', '%part%')->count();

        // Open vacancies from careers page (published = open position)
        // Full-time: type contains 'full' or type is empty (most positions default to full-time)
        $ftVacancies = \DB::table('careers')->where('status', 'published')
            ->where(function($q) {
                $q->where('type', 'LIKE', '%full%')
                  ->orWhere('type', '')
                  ->orWhereNull('type');
            })->count();

        $ptVacancies = \DB::table('careers')->where('status', 'published')
            ->where('type', 'LIKE', '%part%')->count();

        // Gauge percent: active employees / total needed (active + open vacancies)
        $ftTotal = $ftActive + $ftVacancies;
        $ptTotal = $ptActive + $ptVacancies;
        $ftPct = $ftTotal > 0 ? round(($ftActive / $ftTotal) * 100) : 0;
        $ptPct = $ptTotal > 0 ? round(($ptActive / $ptTotal) * 100) : 0;

        // Update the YTD dashboard cards with live data
        if (is_array($kpiData) && isset($kpiData['ytd_dashboard'])) {
            foreach ($kpiData['ytd_dashboard'] as &$card) {
                if ($card['title'] === 'Full Time Employees') {
                    $card['value'] = (string) $ftActive;
                    $card['target'] = (string) $ftThisYear;
                    $card['last_year'] = (string) $ftLastYear;
                    $card['percent'] = $ftPct;
                    $card['color'] = $ftActive > 0 ? 'teal' : 'gray';
                }
                if ($card['title'] === 'Part Time Employees') {
                    $card['value'] = (string) $ptActive;
                    $card['target'] = (string) $ptThisYear;
                    $card['last_year'] = (string) $ptLastYear;
                    $card['percent'] = $ptPct;
                    $card['color'] = $ptActive > 0 ? 'teal' : 'gray';
                }
            }
            unset($card);
        }

        return response()->json(array_merge([
            'ok' => true,
            'kpi_data' => $kpiData,
            'exists' => (bool) $template,
        ], $extra));
    }

    public function saveKpiTemplate(Request $request)
    {
        $this->ensureSuperAdmin();
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);

        $validated = $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
            'division_id' => 'required_without:employee_id|nullable|integer|exists:divisions,id',
            'sub_division_id' => 'nullable|integer|exists:sub_divisions,id',
            'position_id' => 'nullable|integer|exists:positions,id',
            'kpi_data' => 'required|array',
        ]);

        $kpiData = $validated['kpi_data'];
        $employeeId = $validated['employee_id'] ?? null;

        // A personal KPI is identified purely by employee_id — never by Division/
        // Sub-Division/Position, so saving one can never overwrite (or get
        // confused with) the shared position template it may have started as a
        // copy of. Division/Sub-Division/Position are still stored alongside it,
        // filled in from the employee's contract, purely so the Saved Templates
        // list can show what position this override is for.
        $lookupKeys = $employeeId
            ? ['employee_id' => $employeeId]
            : [
                'division_id' => $validated['division_id'],
                'sub_division_id' => $validated['sub_division_id'] ?: null,
                'position_id' => $validated['position_id'] ?: null,
            ];

        // The KRA/indicator table form has no fields for `ytd_dashboard` at all, so a
        // straight overwrite here would silently wipe out any YTD Dashboard cards an
        // existing template already has. Preserve them until YTD Dashboard gets its
        // own proper editor (it's a separate, mostly-static department scorecard, not
        // part of this per-position KRA table).
        $existing = KpiTemplate::where($lookupKeys)->first();
        if ($existing && !isset($kpiData['ytd_dashboard'])) {
            $existingData = $existing->kpi_data ?? [];
            if (is_array($existingData) && isset($existingData['ytd_dashboard'])) {
                $kpiData['ytd_dashboard'] = $existingData['ytd_dashboard'];
            }
        }

        $attributes = [
            'kpi_data' => $kpiData,
            'created_by' => request()->user()?->id,
        ];

        if ($employeeId) {
            $employee = \App\Models\Employee::find($employeeId);
            $formData = is_array($employee?->contract?->form_data) ? $employee->contract->form_data : [];
            // division_id is a required column — fall back to the Employee's own
            // division_id in the unlikely case their contract link is missing it.
            $attributes['division_id'] = $employee?->contract?->division_id ?? $employee?->division_id;
            $attributes['sub_division_id'] = $formData['sub_division_id'] ?? null;
            $attributes['position_id'] = $formData['position_id'] ?? null;
        }

        $template = KpiTemplate::updateOrCreate($lookupKeys, $attributes);

        return redirect()->route('admin.kpi-jd.kpi-list')->with(
            'success',
            $employeeId ? 'Personal KPI saved for this employee.' : 'KPI template saved for this division/sub-division.'
        );
    }

    /**
     * Generates the "SURAT PERNYATAAN DAN PERSETUJUAN KEY PERFORMANCE
     * INDICATOR (KPI)" acknowledgement document for one specific employee.
     *
     * This fills Legal's own, already-approved .docx (the one they supplied,
     * with the header tidied up and fonts unified per Ayu's request) as a
     * PhpWord TemplateProcessor template — it is NOT rebuilt from scratch, so
     * every bit of Legal's layout/branding/decoration is reproduced exactly.
     * The template lives at storage/app/kpi-templates/kpi-acknowledgement-template.docx
     * and only has plain ${placeholder} merge fields swapped in for the
     * dynamic bits (doc number, name, position, department, KPI rows, total weight).
     */
    public function generateKpiDocument($employeeId)
    {
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);

        $employee = \App\Models\Employee::with(['contract', 'division'])->findOrFail($employeeId);
        $template = KpiTemplate::forEmployee($employee);

        if (!$template || empty($template->areas())) {
            return redirect()->back()->with('error', "No KPI has been set for {$employee->full_name} yet \u{2014} build one on the KPI page first.");
        }

        // Mirrors the KPI builder's own table — Key Result Area / KPI / Weight /
        // Target — with the Key Result Area repeated on every indicator row under it.
        $rows = [];
        foreach ($template->areas() as $area) {
            foreach (($area['indicators'] ?? []) as $ind) {
                if (($ind['kpi'] ?? '') === '' && ($ind['weight'] ?? '') === '') {
                    continue; // skip genuinely-blank rows left in the builder
                }
                $rows[] = [
                    'area' => $area['key_result_area'] ?? '',
                    'kpi' => $ind['kpi'] ?? '',
                    'kpi_en' => $ind['kpi_en'] ?? '',
                    'target' => $ind['target'] ?? '',
                    'target_en' => $ind['target_en'] ?? '',
                    'weight' => $ind['weight'] ?? '',
                ];
            }
        }

        return $this->buildKpiWordFromTemplate($employee, $rows);
    }

    /**
     * Escapes text for safe insertion into an OOXML <w:t> run — PhpWord's
     * TemplateProcessor::setValue() does NOT escape XML special characters
     * on its own, so free-text employee/KPI data must be escaped here first.
     */
    private static function escapeForWordXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function buildKpiWordFromTemplate(\App\Models\Employee $employee, array $rows)
    {
        $templatePath = storage_path('app/kpi-templates/kpi-acknowledgement-template.docx');
        abort_unless(file_exists($templatePath), 500, 'KPI acknowledgement template file is missing on the server.');

        $fullName = self::escapeForWordXml($employee->full_name);
        $positionName = self::escapeForWordXml($employee->position_title ?: '\u{2014}');
        $divisionName = self::escapeForWordXml($employee->division?->name ?: ($employee->contract?->division?->name ?? '\u{2014}'));
        $docNumber = self::escapeForWordXml(sprintf('SLS-KPI-%s-%04d', now()->format('Y'), $employee->id));

        $totalWeight = 0;
        $rowsForXml = [];
        foreach ($rows as $row) {
            $weightNum = (float) preg_replace('/[^\d.\-]/', '', (string) $row['weight']);
            $totalWeight += $weightNum;
            $rowsForXml[] = [
                'area' => (string) $row['area'],
                'kpi' => (string) $row['kpi'],
                'kpi_en' => (string) ($row['kpi_en'] ?? ''),
                'target' => (string) $row['target'],
                'target_en' => (string) ($row['target_en'] ?? ''),
                'weight_display' => rtrim(rtrim(number_format($weightNum, 2), '0'), '.'),
            ];
        }

        $processor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
        $processor->setValue('doc_number', $docNumber);
        $processor->setValue('employee_name', $fullName);
        $processor->setValue('position', $positionName);
        $processor->setValue('department', $divisionName);
        $processor->setValue('total_weight', rtrim(rtrim(number_format($totalWeight, 2), '0'), '.'));

        // The KPI table body's row count is variable (one row per indicator, across
        // however many Key Result Areas an employee has) — PhpWord's cloneRow() can
        // clone a row N times, but only in TemplateProcessor's plain flat-list way,
        // and the table is edited enough elsewhere that hand-building the rows
        // (buildKpiTableRowsXml) as raw OOXML is simpler to keep in sync than a
        // clone+per-cell setValue loop. It's generated below and swapped in for a
        // single marker row, after everything else has been filled in normally.
        $markerToken = 'KPIROWSMARKERXYZ123';
        $processor->setValue('kpi_rows_marker', $markerToken);

        $filename = \Illuminate\Support\Str::slug('KPI-' . $employee->full_name) . '.docx';
        $publicPath = public_path('downloads/' . $filename);
        $processor->saveAs($publicPath);

        $zip = new \ZipArchive();
        if ($zip->open($publicPath) === true) {
            $docXml = $zip->getFromName('word/document.xml');
            if ($docXml !== false) {
                $markerPos = strpos($docXml, $markerToken);
                if ($markerPos !== false) {
                    // Find the marker row's own opening <w:tr> — searching backward for the
                    // bare literal '<w:tr' also matches '<w:trPr>' (row properties), which
                    // sits between <w:tr> and the marker text and is closer, so skip past any
                    // such false match until the char right after "<w:tr" is '>' or a space.
                    $searchLimit = $markerPos;
                    $trStart = false;
                    while (($candidate = strrpos(substr($docXml, 0, $searchLimit), '<w:tr')) !== false) {
                        $nextChar = $docXml[$candidate + 5] ?? '';
                        if ($nextChar === '>' || $nextChar === ' ') {
                            $trStart = $candidate;
                            break;
                        }
                        $searchLimit = $candidate;
                    }
                    $trEnd = strpos($docXml, '</w:tr>', $markerPos) + 7;
                    $rowsXml = $this->buildKpiTableRowsXml($rowsForXml);
                    $docXml = substr($docXml, 0, $trStart) . $rowsXml . substr($docXml, $trEnd);
                    $zip->addFromString('word/document.xml', $docXml);
                }
            }
            $zip->close();
        }

        return response()->download($publicPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    /**
     * Builds the raw <w:tr> XML for every KPI row, with the No. and Key Result
     * Area cells vertically merged (w:vMerge) across each area's indicators —
     * "restart" on the first indicator in an area, plain continuation (empty
     * cell) on the rest, exactly like the merged No./Key Result Area columns in
     * the KPI builder's own table.
     */
    private function buildKpiTableRowsXml(array $rows): string
    {
        $font = '<w:rFonts w:ascii="Palatino Linotype" w:eastAsia="Palatino Linotype" w:hAnsi="Palatino Linotype" w:cs="Palatino Linotype"/>';
        $cellMar = '<w:tcMar><w:top w:w="80" w:type="dxa"/><w:left w:w="120" w:type="dxa"/><w:bottom w:w="80" w:type="dxa"/><w:right w:w="120" w:type="dxa"/></w:tcMar>';

        $xml = '';
        foreach ($rows as $row) {
            // Key Result Area repeats on every indicator row under it, rather than
            // being merged (w:vMerge) down a single cell — a merged cell that happens
            // to straddle a page break renders its text on whichever side Word
            // decides, which can put the area label on the row after the one it
            // belongs to. Repeating it is a little more verbose but never misplaced.
            $areaText = self::escapeForWordXml($row['area']);
            $kraCell = '<w:tc><w:tcPr><w:tcW w:w="1800" w:type="dxa"/>' . $cellMar . '<w:vAlign w:val="center"/></w:tcPr>'
                . '<w:p><w:r><w:rPr>' . $font . '<w:b/><w:bCs/><w:sz w:val="21"/><w:szCs w:val="21"/></w:rPr><w:t>' . $areaText . '</w:t></w:r></w:p></w:tc>';

            $kpiEnPara = $row['kpi_en'] !== ''
                ? '<w:p><w:r><w:rPr>' . $font . '<w:i/><w:iCs/><w:color w:val="595959"/><w:sz w:val="19"/><w:szCs w:val="19"/></w:rPr><w:t>' . self::escapeForWordXml($row['kpi_en']) . '</w:t></w:r></w:p>'
                : '';
            $kpiCell = '<w:tc><w:tcPr><w:tcW w:w="3600" w:type="dxa"/>' . $cellMar . '<w:vAlign w:val="center"/></w:tcPr>'
                . '<w:p><w:pPr><w:spacing w:after="30"/></w:pPr><w:r><w:rPr>' . $font . '<w:sz w:val="21"/><w:szCs w:val="21"/></w:rPr><w:t>' . self::escapeForWordXml($row['kpi']) . '</w:t></w:r></w:p>' . $kpiEnPara . '</w:tc>';

            $weightCell = '<w:tc><w:tcPr><w:tcW w:w="900" w:type="dxa"/>' . $cellMar . '<w:vAlign w:val="center"/></w:tcPr>'
                . '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr>' . $font . '<w:sz w:val="21"/><w:szCs w:val="21"/></w:rPr><w:t>' . self::escapeForWordXml($row['weight_display']) . '%</w:t></w:r></w:p></w:tc>';

            $targetEnPara = $row['target_en'] !== ''
                ? '<w:p><w:r><w:rPr>' . $font . '<w:i/><w:iCs/><w:color w:val="595959"/><w:sz w:val="19"/><w:szCs w:val="19"/></w:rPr><w:t>' . self::escapeForWordXml($row['target_en']) . '</w:t></w:r></w:p>'
                : '';
            $targetCell = '<w:tc><w:tcPr><w:tcW w:w="2800" w:type="dxa"/>' . $cellMar . '<w:vAlign w:val="center"/></w:tcPr>'
                . '<w:p><w:pPr><w:spacing w:after="30"/></w:pPr><w:r><w:rPr>' . $font . '<w:sz w:val="21"/><w:szCs w:val="21"/></w:rPr><w:t>' . self::escapeForWordXml($row['target']) . '</w:t></w:r></w:p>' . $targetEnPara . '</w:tc>';

            $xml .= '<w:tr><w:trPr><w:cantSplit/></w:trPr>' . $kraCell . $kpiCell . $weightCell . $targetCell . '</w:tr>';
        }

        return $xml;
    }

    public function destroyKpiTemplate(KpiTemplate $template)
    {
        $this->ensureSuperAdmin();
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);

        $template->delete();

        return redirect()->route('admin.kpi-jd.kpi-list')
            ->with('success', 'KPI template deleted.');
    }
}