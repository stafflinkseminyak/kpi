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

        return view('admin.kpi.kpi-list', compact('records', 'isSuperAdmin', 'divisions', 'subDivisions', 'positions'));
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

        return response()->json([
            'ok' => true,
            'kpi_data' => $kpiData,
            'exists' => (bool) $template,
        ]);
    }

    public function saveKpiTemplate(Request $request)
    {
        $this->ensureSuperAdmin();
        abort_unless(in_array(request()->user()?->role, ['super_admin', 'admin'], true), 403);

        $validated = $request->validate([
            'division_id' => 'required|integer|exists:divisions,id',
            'sub_division_id' => 'nullable|integer|exists:sub_divisions,id',
            'position_id' => 'nullable|integer|exists:positions,id',
            'kpi_data' => 'required|array',
        ]);

        $kpiData = $validated['kpi_data'];

        // The KRA/indicator table form has no fields for `ytd_dashboard` at all, so a
        // straight overwrite here would silently wipe out any YTD Dashboard cards an
        // existing template already has. Preserve them until YTD Dashboard gets its
        // own proper editor (it's a separate, mostly-static department scorecard, not
        // part of this per-position KRA table).
        $existing = KpiTemplate::where('division_id', $validated['division_id'])
            ->where('sub_division_id', $validated['sub_division_id'] ?: null)
            ->where('position_id', $validated['position_id'] ?: null)
            ->first();
        if ($existing && !isset($kpiData['ytd_dashboard'])) {
            $existingData = $existing->kpi_data ?? [];
            if (is_array($existingData) && isset($existingData['ytd_dashboard'])) {
                $kpiData['ytd_dashboard'] = $existingData['ytd_dashboard'];
            }
        }

        $template = KpiTemplate::updateOrCreate(
            [
                'division_id' => $validated['division_id'],
                'sub_division_id' => $validated['sub_division_id'] ?: null,
                'position_id' => $validated['position_id'] ?: null,
            ],
            [
                'kpi_data' => $kpiData,
                'created_by' => request()->user()?->id,
            ]
        );

        return redirect()->route('admin.kpi-jd.kpi-list')
            ->with('success', 'KPI template saved for this division/sub-division.');
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