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
     * INDICATOR (KPI)" acknowledgement document for one specific employee —
     * built with PHPWord, reusing the exact same StaffLink letterhead (logo,
     * gold header/footer banners, tagline, contact block) as
     * AdminContractController::buildContractWord(), so it matches the
     * company's other official documents pixel-for-pixel. Wording/layout
     * matches the format Legal signed off on (see the .docx they supplied).
     */
    public function generateKpiDocument($employeeId)
    {
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);

        $employee = \App\Models\Employee::with(['contract', 'division'])->findOrFail($employeeId);
        $template = KpiTemplate::forEmployee($employee);

        if (!$template || empty($template->areas())) {
            return redirect()->back()->with('error', "No KPI has been set for {$employee->full_name} yet \u{2014} build one on the KPI page first.");
        }

        $rows = [];
        foreach ($template->areas() as $area) {
            foreach (($area['indicators'] ?? []) as $ind) {
                if (($ind['kpi'] ?? '') === '' && ($ind['weight'] ?? '') === '') {
                    continue; // skip genuinely-blank rows left in the builder
                }
                $rows[] = [
                    'kpi' => $ind['kpi'] ?? '',
                    'target' => $ind['target'] ?? '',
                    'weight' => $ind['weight'] ?? '',
                ];
            }
        }

        return $this->buildKpiWord($employee, $rows);
    }

    private function buildKpiWord(\App\Models\Employee $employee, array $rows)
    {
        // PhpWord doesn't escape & in XML output — same guard buildContractWord() uses.
        $fullName = str_replace('&', 'and', $employee->full_name);
        $positionName = str_replace('&', 'and', $employee->position_title ?: '\u{2014}');
        $divisionName = str_replace('&', 'and', $employee->division?->name ?: ($employee->contract?->division?->name ?? '\u{2014}'));
        array_walk($rows, function (&$row) {
            foreach ($row as &$value) {
                $value = str_replace('&', 'and', (string) $value);
            }
        });

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Palatino Linotype');
        $phpWord->setDefaultFontSize(8);

        $boldStyle = ['bold' => true];
        $italicStyle = ['italic' => true];
        $normalStyle = [];
        $paraJustify = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceBefore' => 0, 'spaceAfter' => 60, 'lineHeight' => 1.08];
        $paraCenter = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 60, 'lineHeight' => 1.08];
        $paraCenterBig = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 80, 'lineHeight' => 1.08];

        // A4 page + margins, header/footer heights — identical to buildContractWord()
        $section = $phpWord->addSection([
            'pageSizeW' => 11906, 'pageSizeH' => 16838,
            'marginTop' => 1950, 'marginBottom' => 1200, 'marginLeft' => 1134, 'marginRight' => 1134,
            'headerHeight' => 100, 'footerHeight' => 1,
        ]);

        // --- HEADER/FOOTER: the same gold-wave letterhead used on Contracts ---
        $header = $section->addHeader();
        $goldHeaderPath = public_path('images/contracts/letterhead-header.png');
        if (file_exists($goldHeaderPath)) {
            $header->addImage($goldHeaderPath, [
                'width' => 595, 'height' => 89, 'positioning' => 'absolute',
                'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_CENTER,
                'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                'posVertical' => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_TOP,
                'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                'wrappingStyle' => 'behind',
            ]);
        }

        $footer = $section->addFooter();
        $footer->addPreserveText('PAGE | {PAGE}', ['size' => 7.5, 'color' => '595959'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $goldFooterPath = public_path('images/contracts/letterhead-footer.png');
        if (file_exists($goldFooterPath)) {
            $footer->addImage($goldFooterPath, [
                'width' => 595, 'height' => 76, 'positioning' => 'absolute',
                'posHorizontal' => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_CENTER,
                'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                'posVertical' => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_BOTTOM,
                'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_PAGE,
                'wrappingStyle' => 'behind',
            ]);
        }

        // --- Logo + tagline + contact block (same as Contract's) ---
        $bodyHeaderTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $bodyHeaderTable->addRow();
        $leftCell = $bodyHeaderTable->addCell(4656, ['borderSize' => 0]);
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $leftCell->addImage($logoPath, ['width' => 122, 'height' => 78, 'wrappingStyle' => 'inline']);
        }
        $leftCell->addText('Making Dreams Possible', ['italic' => true, 'bold' => true, 'size' => 9, 'color' => '1a6b30', 'name' => 'Georgia'], ['spaceAfter' => 0, 'spaceBefore' => 40, 'indentation' => ['left' => 340]]);
        $leftCell->addText('One Person at a Time', ['italic' => true, 'bold' => true, 'size' => 9, 'color' => '1a6b30', 'name' => 'Georgia'], ['spaceAfter' => 0, 'indentation' => ['left' => 340]]);

        $rightCell = $bodyHeaderTable->addCell(5044, ['borderSize' => 0]);
        $rightCell->addText('Contact Details and Social Media', ['bold' => true, 'size' => 10, 'color' => '1a6b30', 'name' => 'Georgia'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 10, 'spaceBefore' => 0]);
        $rightCell->addText('M: +62 857-3966-0906', ['size' => 8.5, 'bold' => true, 'color' => '333333'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $rightCell->addText('E: info@stafflink.pro', ['size' => 8.5, 'color' => '5b8fad'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0]);
        $rightCell->addText('Website : www.stafflink.pro', ['size' => 8.5, 'bold' => true, 'color' => '333333'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 10]);

        $bodyHeaderTable->addRow(1);
        $greenCell = $bodyHeaderTable->addCell(9700, ['gridSpan' => 2, 'borderBottomSize' => 6, 'borderBottomColor' => '1a6b30']);
        $greenCell->addText('', ['size' => 1], ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 0.5]);

        // --- Doc number + bilingual title (wording matches Legal's finalized copy) ---
        $section->addText('No. Dokumen / Document No.: SLS-KPI-' . now()->format('Y') . '-____', ['size' => 8, 'color' => '595959'], $paraJustify);

        $tr = $section->addTextRun($paraCenterBig);
        $tr->addText('SURAT PERNYATAAN DAN PERSETUJUAN KEY PERFORMANCE INDICATOR (KPI)', ['bold' => true, 'size' => 11]);
        $tr->addTextBreak();
        $tr->addText('KPI STATEMENT AND ACKNOWLEDGEMENT FORM', ['bold' => true, 'italic' => true, 'size' => 9]);

        $tr = $section->addTextRun($paraJustify);
        $tr->addText('Yang bertanda tangan di bawah ini: ', $normalStyle);
        $tr->addText('The undersigned:', $italicStyle);

        $this->addKpiWordInfoRows($section, [
            ['Nama / Name', $fullName],
            ['Jabatan / Position', $positionName],
            ['Departemen / Department', $divisionName],
            ['Periode Berlaku / Effective Period', '_____________________ s.d./to _____________________'],
        ]);

        $tr = $section->addTextRun($paraJustify);
        $tr->addText('dengan ini menyatakan telah menerima dan memahami Key Performance Indicator (KPI) yang ditetapkan oleh Perusahaan sebagai berikut: ', $normalStyle);
        $tr->addText('hereby declares to have received and understood the Key Performance Indicators (KPIs) established by the Company as follows:', $italicStyle);

        // --- KPI table: same D9E2F3/F2F2F2 shading Legal's document uses, and
        // cantSplit on every row so no row can ever break across a page again ---
        $kpiTable = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80]);
        $kpiTable->addRow(400, ['tblHeader' => true, 'cantSplit' => true]);
        $kpiTable->addCell(500, ['bgColor' => 'D9E2F3'])->addText('No', $boldStyle, $paraCenter);
        $kpiTable->addCell(3900, ['bgColor' => 'D9E2F3'])->addText('Key Performance Indicator (KPI)', $boldStyle, $paraCenter);
        $kpiTable->addCell(3800, ['bgColor' => 'D9E2F3'])->addText('Target', $boldStyle, $paraCenter);
        $kpiTable->addCell(1500, ['bgColor' => 'D9E2F3'])->addText('Bobot / Weight', $boldStyle, $paraCenter);

        $totalWeight = 0;
        foreach ($rows as $i => $row) {
            $kpiTable->addRow(null, ['cantSplit' => true]);
            $kpiTable->addCell(500)->addText((string) ($i + 1), $normalStyle, $paraCenter);
            $kpiTable->addCell(3900)->addText($row['kpi'], $normalStyle, ['spaceAfter' => 0]);
            $kpiTable->addCell(3800)->addText($row['target'], $normalStyle, ['spaceAfter' => 0]);
            $weightNum = (float) preg_replace('/[^\d.\-]/', '', (string) $row['weight']);
            $totalWeight += $weightNum;
            $kpiTable->addCell(1500)->addText(rtrim(rtrim(number_format($weightNum, 2), '0'), '.') . '%', $normalStyle, $paraCenter);
        }
        $kpiTable->addRow(null, ['cantSplit' => true]);
        $totalCell = $kpiTable->addCell(8200, ['gridSpan' => 3, 'bgColor' => 'F2F2F2']);
        $totalCell->addText('Total Bobot / Total Weight', $boldStyle, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT, 'spaceAfter' => 0]);
        $kpiTable->addCell(1500, ['bgColor' => 'F2F2F2'])->addText(rtrim(rtrim(number_format($totalWeight, 2), '0'), '.') . '%', $boldStyle, $paraCenter);

        // --- Declaration — exact wording Legal approved ---
        $section->addText('PERNYATAAN / STATEMENT', $boldStyle, ['spaceBefore' => 200, 'spaceAfter' => 120]);

        $declarations = [
            ['Saya telah menerima, membaca, dan memahami seluruh Key Performance Indicator (KPI) beserta target dan bobot penilaian sebagaimana tercantum dalam tabel di atas.',
             'I have received, read, and understood all Key Performance Indicators (KPIs), including the targets and assessment weights as set out in the table above.'],
            ['Saya menandatangani dokumen ini secara sadar, sukarela, tanpa paksaan dari pihak mana pun, dan dalam keadaan cakap hukum, setelah diberikan kesempatan untuk bertanya dan mendiskusikan KPI tersebut, sesuai dengan syarat sahnya perjanjian sebagaimana diatur dalam Pasal 1320 Kitab Undang-Undang Hukum Perdata (KUHPerdata).',
             'I sign this document consciously, voluntarily, free from coercion by any party, and with full legal capacity, having been given the opportunity to ask questions and discuss the KPIs, in accordance with the requirements for a valid agreement as stipulated in Article 1320 of the Indonesian Civil Code (KUHPerdata).'],
            ['Saya menyetujui bahwa KPI di atas digunakan sebagai dasar penilaian kinerja saya selama periode berlaku, dan hasil penilaian tersebut dapat menjadi pertimbangan Perusahaan dalam keputusan kepegawaian sesuai dengan peraturan perundang-undangan yang berlaku, perjanjian kerja, dan/atau peraturan perusahaan.',
             'I agree that the above KPIs shall serve as the basis for the assessment of my performance during the effective period, and that the results of such assessment may be taken into consideration by the Company in employment-related decisions in accordance with the prevailing laws and regulations, the employment agreement, and/or the company regulations.'],
        ];
        foreach ($declarations as $i => $pair) {
            $tr = $section->addTextRun($paraJustify);
            $tr->addText(($i + 1) . '. ' . $pair[0] . ' ', $normalStyle);
            $tr->addText($pair[1], $italicStyle);
        }

        $tr = $section->addTextRun($paraJustify);
        $tr->addText('*) Penilaian KPI kehadiran dan ketepatan waktu tidak mengurangi hak cuti, istirahat, dan izin yang dijamin oleh peraturan perundang-undangan; ketidakhadiran berdasarkan hak tersebut tidak diperhitungkan sebagai ketidakhadiran. ', $italicStyle);
        $tr->addText('*) The assessment of the attendance and punctuality KPI shall not reduce any leave, rest, or permitted-absence entitlements guaranteed by the prevailing laws and regulations; any absence based on such entitlements shall not be counted as an absence.', $italicStyle);

        $declarations2 = [
            ['Saya berkomitmen untuk berupaya secara maksimal mencapai target yang ditetapkan dan akan segera menginformasikan kepada atasan langsung apabila terdapat kendala yang berpotensi menghambat pencapaian target.',
             'I am committed to making my best efforts to achieve the targets set and will promptly inform my direct supervisor of any obstacles that may hinder the achievement of such targets.'],
            ['Saya memahami bahwa KPI ini dapat ditinjau dan disesuaikan oleh Perusahaan dari waktu ke waktu sesuai kebutuhan bisnis, dengan pemberitahuan terlebih dahulu kepada saya.',
             'I understand that these KPIs may be reviewed and adjusted by the Company from time to time in line with business needs, with prior notice to me.'],
            ['Pernyataan ini tidak mengurangi hak dan kewajiban para pihak berdasarkan peraturan perundang-undangan di bidang ketenagakerjaan, perjanjian kerja, dan/atau peraturan perusahaan yang berlaku.',
             'This statement does not reduce the rights and obligations of the parties under the prevailing employment laws and regulations, the employment agreement, and/or the applicable company regulations.'],
            ['Dokumen ini dibuat dalam Bahasa Indonesia dan Bahasa Inggris sesuai Undang-Undang Nomor 24 Tahun 2009. Apabila terdapat perbedaan penafsiran antara kedua versi, maka versi Bahasa Indonesia yang berlaku.',
             'This document is executed in Indonesian and English in accordance with Law No. 24 of 2009. In the event of any inconsistency between the two versions, the Indonesian version shall prevail.'],
        ];
        foreach ($declarations2 as $i => $pair) {
            $tr = $section->addTextRun($paraJustify);
            $tr->addText(($i + 4) . '. ' . $pair[0] . ' ', $normalStyle);
            $tr->addText($pair[1], $italicStyle);
        }

        $tr = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'spaceBefore' => 120, 'spaceAfter' => 240]);
        $tr->addText('Demikian pernyataan ini dibuat dengan sebenarnya untuk digunakan sebagaimana mestinya. ', $normalStyle);
        $tr->addText('This statement is made truthfully to be used as appropriate.', $italicStyle);

        // --- Signature block ---
        $section->addText('_____________________, _____________________ ' . now()->format('Y'), $normalStyle, ['spaceAfter' => 240]);

        $sigTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 80]);
        $sigTable->addRow();
        $cell1 = $sigTable->addCell(4500, ['borderSize' => 0]);
        $cell1->addText('Disetujui dan diakui oleh,', $boldStyle, $normalStyle);
        $cell1->addText('Acknowledged and agreed by,', $italicStyle, $normalStyle);
        $cell1->addTextBreak(4);
        $cell1->addText('_____________________________', $normalStyle, ['spaceAfter' => 0]);
        $cell1->addText('Nama / Name : ' . $fullName, $normalStyle, ['spaceAfter' => 0]);
        $cell1->addText('Jabatan / Position : ' . $positionName, $normalStyle, ['spaceAfter' => 0]);
        $cell1->addText('Tanggal / Date : _____________________', $normalStyle, ['spaceAfter' => 0]);

        $cell2 = $sigTable->addCell(4500, ['borderSize' => 0]);
        $cell2->addText('Ditetapkan oleh, untuk dan atas nama Perusahaan,', $boldStyle, $normalStyle);
        $cell2->addText('Established by, for and on behalf of the Company,', $italicStyle, $normalStyle);
        $cell2->addTextBreak(4);
        $cell2->addText('_____________________________', $normalStyle, ['spaceAfter' => 0]);
        $cell2->addText('Nama / Name : _____________________', $normalStyle, ['spaceAfter' => 0]);
        $cell2->addText('Jabatan / Position : _____________________', $normalStyle, ['spaceAfter' => 0]);
        $cell2->addText('Tanggal / Date : _____________________', $normalStyle, ['spaceAfter' => 0]);

        // --- Save + same post-process border fix buildContractWord() applies
        // (PhpWord's borderSize=0 emits w:val="single" w:sz="0", which Word
        // still renders as a hairline border unless swapped to w:val="none") ---
        $filename = \Illuminate\Support\Str::slug('KPI-' . $fullName) . '.docx';
        $publicPath = public_path('downloads/' . $filename);
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($publicPath);

        $zip = new \ZipArchive();
        if ($zip->open($publicPath) === true) {
            $headerXml = $zip->getFromName('word/header1.xml');
            if ($headerXml) {
                $headerXml = preg_replace('/w:val="single"\s+w:sz="0"/', 'w:val="none" w:sz="0"', $headerXml);
                $zip->addFromString('word/header1.xml', $headerXml);
            }
            $docXml = $zip->getFromName('word/document.xml');
            if ($docXml) {
                $docXml = preg_replace('/w:val="single"\s+w:sz="0"/', 'w:val="none" w:sz="0"', $docXml);
                $zip->addFromString('word/document.xml', $docXml);
            }
            $zip->close();
        }

        return response()->download($publicPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    private function addKpiWordInfoRows($section, array $rows)
    {
        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 40]);
        foreach ($rows as $row) {
            $label = $row[0];
            $value = $row[1] ?? '';
            $table->addRow();
            $c1 = $table->addCell(3600, ['borderSize' => 0]);
            if (strpos($label, '/') !== false) {
                $parts = explode('/', $label, 2);
                $tr = $c1->addTextRun(['spaceAfter' => 0]);
                $tr->addText(trim($parts[0]) . ' / ', []);
                $tr->addText(trim($parts[1]), ['italic' => true]);
            } else {
                $c1->addText($label, [], ['spaceAfter' => 0]);
            }
            $c2 = $table->addCell(300, ['borderSize' => 0]);
            $c2->addText(':', [], ['spaceAfter' => 0]);
            $c3 = $table->addCell(5100, ['borderSize' => 0]);
            $c3->addText($value, [], ['spaceAfter' => 0]);
        }
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