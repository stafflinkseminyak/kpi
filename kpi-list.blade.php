@extends('admin.layout')

@section('page-title', 'KPIs')
@section('title', 'KPIs')

@section('content')
    <style>
        .gauge-container { position:relative; width:120px; height:70px; margin:0 auto; }
        .gauge-bg { fill:none; stroke:#e5e7eb; stroke-width:12; }
        .gauge-fill { fill:none; stroke-width:12; stroke-linecap:round; transition:stroke-dashoffset 0.8s ease; }
        .gauge-teal { stroke:#14b8a6; }
        .gauge-gold { stroke:#eab308; }
        .gauge-red { stroke:#ef4444; }
        .gauge-gray { stroke:#9ca3af; }
        .gauge-pct { font-size:18px; font-weight:800; fill:#1f2937; }
        .gauge-label { font-size:9px; fill:#9ca3af; }
    </style>

    <div class="space-y-6">

        @if(session('success'))
            <div class="p-4 text-sm text-green-800 bg-green-100 border border-green-200 rounded-lg">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 text-sm text-red-800 bg-red-100 border border-red-200 rounded-lg">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
        {{-- KPI Template Box — sticky so it stays in view while the Saved
             Templates list next to it grows long and gets scrolled through. --}}
        <section id="kpi_template_card" class="bg-white rounded-lg shadow border border-gray-100 xl:sticky xl:top-6">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">KPI Template</h3>
                <p class="text-xs text-gray-500 mt-1">Set KPI targets for each division, sub-division, and position — or for one specific person.</p>
            </div>

            {{-- Two scopes for a KPI: shared by everyone in a Position (default), or a
                 one-off for a specific Employee — e.g. an in-house promotion that adds/
                 changes KPIs for just them without a new Contract. A personal KPI always
                 takes priority over the position one when resolving someone's KPI (see
                 KpiTemplate::forEmployee()), and starts as a copy of their current
                 position KPI so nothing has to be retyped. --}}
            <div class="px-4 pt-3 flex gap-1.5">
                <button type="button" id="kpi_mode_position_btn" onclick="switchKpiMode('position')"
                    class="px-3 py-1 text-xs font-semibold rounded-md transition" style="background:#287854;color:#fff;border:none;cursor:pointer;">
                    By Position
                </button>
                <button type="button" id="kpi_mode_person_btn" onclick="switchKpiMode('person')"
                    class="px-3 py-1 text-xs font-semibold rounded-md transition" style="background:#f3f4f6;color:#374151;border:none;cursor:pointer;">
                    By Person
                </button>
            </div>

            <div id="kpi_mode_position_fields" class="p-3 border-b bg-gray-50/40">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-x-3 gap-y-1.5 items-end">
                    <div class="md:col-span-6">
                        <label for="kpi_division_select" class="block text-[11px] font-medium text-gray-700 mb-0.5">Division</label>
                        <select id="kpi_division_select"
                            class="w-full px-2 py-1 text-xs border border-gray-200 rounded-md focus:ring-2 focus:ring-[#287854] focus:border-transparent bg-white">
                            <option value="">Select division</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-6">
                        <label for="kpi_sub_division_select" class="block text-[11px] font-medium text-gray-700 mb-0.5">Sub-Division</label>
                        <select id="kpi_sub_division_select"
                            class="w-full px-2 py-1 text-xs border border-gray-200 rounded-md focus:ring-2 focus:ring-[#287854] focus:border-transparent bg-white">
                            <option value="">Select sub-division</option>
                        </select>
                    </div>
                    <div class="md:col-span-6">
                        <label for="kpi_position_select" class="block text-[11px] font-medium text-gray-700 mb-0.5">Position</label>
                        <select id="kpi_position_select"
                            class="w-full px-2 py-1 text-xs border border-gray-200 rounded-md focus:ring-2 focus:ring-[#287854] focus:border-transparent bg-white">
                            <option value="">Select position</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="kpi_mode_person_fields" class="p-3 border-b bg-gray-50/40" style="display:none;">
                <label for="kpi_employee_select" class="block text-[11px] font-medium text-gray-700 mb-0.5">Employee</label>
                <select id="kpi_employee_select"
                    class="w-full px-2 py-1 text-xs border border-gray-200 rounded-md focus:ring-2 focus:ring-[#287854] focus:border-transparent bg-white">
                    <option value="">Select employee</option>
                    @foreach ($employeesForKpiPicker ?? [] as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-gray-400 mt-1">Starts as a copy of their current position KPI — edit/add rows and save, and it becomes theirs specifically (their shared position template is untouched).</p>
            </div>

            <div id="kpi_placeholder" class="p-6 text-center text-gray-400">
                <svg class="mx-auto h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="text-xs">Select a division to view or set KPI targets.</p>
            </div>
        </section>

        {{-- Saved Templates — Actions is a single "more" menu (see below) so this
             column stays narrow enough to sit side-by-side without needing a
             horizontal scroll for the buttons. --}}
        <section class="bg-white rounded-lg shadow border border-gray-100">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">Saved KPI Templates</h3>
                <p class="text-xs text-gray-500 mt-1">Templates already configured for divisions.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#e6f1ec]">
                        <tr>
                            <th class="py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider" style="padding-left:18px;padding-right:8px;">Division</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Sub-Division</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Areas</th>
                            <th class="py-2 text-right text-[11px] font-medium text-gray-500 uppercase tracking-wider" style="padding-left:8px;padding-right:18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $templates = \App\Models\KpiTemplate::with(['division', 'subDivision', 'position', 'employee'])->latest()->get(); @endphp
                        @forelse ($templates as $tpl)
                        @php
                            $assigned = $tpl->assignedEmployees();
                            // Only dim a template when EVERY employee it currently covers has
                            // left — a position-level template still actively covering at
                            // least one active employee stays normal, even if someone else
                            // who used to hold that position is now terminated.
                            $allAssignedTerminated = $assigned->isNotEmpty() && $assigned->every(fn ($e) => $e->status === 'terminated');

                            // For a personal (employee_id) template, show this employee's
                            // CURRENT Division/Sub-Division/Position live, via the same
                            // resolution Employee::resolvedPositionIds() uses everywhere
                            // else — not the snapshot captured back when the KPI was last
                            // saved, which goes stale the moment their Division/Sub-Division/
                            // Position changes afterwards (e.g. an intern who had none at
                            // save time, assigned one later on the Employee page).
                            if ($tpl->employee_id && $tpl->employee) {
                                $liveIds = $tpl->employee->resolvedPositionIds();
                                $displayDivision = $liveIds['division_id'] ? \App\Models\Division::find($liveIds['division_id']) : null;
                                $displaySubDivision = $liveIds['sub_division_id'] ? \App\Models\SubDivision::find($liveIds['sub_division_id']) : null;
                                $displayPosition = $liveIds['position_id'] ? \App\Models\Position::find($liveIds['position_id']) : null;
                            } else {
                                $displayDivision = $tpl->division;
                                $displaySubDivision = $tpl->subDivision;
                                $displayPosition = $tpl->position;
                            }
                        @endphp
                        <tr class="transition" style="{{ $allAssignedTerminated ? 'background:#f3f4f6;' : '' }}" @if(!$allAssignedTerminated) onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''" @endif>
                            <td class="py-2 text-xs font-medium" style="padding-left:18px;padding-right:8px;color:{{ $allAssignedTerminated ? '#9ca3af' : '#111827' }};">{{ $displayDivision?->name ?? '-' }}</td>
                            <td class="px-2 py-2 text-xs" style="color:{{ $allAssignedTerminated ? '#b0b5bd' : '#4b5563' }};">{{ $displaySubDivision?->name ?? 'All' }}</td>
                            <td class="px-2 py-2 text-xs" style="color:{{ $allAssignedTerminated ? '#b0b5bd' : '#4b5563' }};">{{ $displayPosition?->name ?? 'All' }}</td>
                            <td class="px-2 py-2 text-xs" style="color:{{ $allAssignedTerminated ? '#b0b5bd' : '#4b5563' }};">@php $kd = $tpl->kpi_data ?? []; $cnt = collect($kd)->filter(fn($v,$k) => is_numeric($k))->count(); @endphp {{ $cnt }}</td>
                            <td class="py-2 text-right relative" style="padding-left:8px;padding-right:18px;">
                                @php
                                    $warnLabel = $tpl->employee_id
                                        ? 'Personal KPI — ' . ($tpl->employee?->full_name ?? 'this employee')
                                        : $tpl->division?->name . ' — ' . ($tpl->subDivision?->name ?? 'All') . ($tpl->position ? ' — ' . $tpl->position->name : '');
                                    $warnAssigned = $assigned->isNotEmpty()
                                        ? 'This is currently linked to ' . $assigned->count() . ' ' . \Illuminate\Support\Str::plural('employee', $assigned->count()) . ' (' . $assigned->pluck('full_name')->implode(', ') . ') — deleting it removes their KPI/progress too.'
                                        : '';
                                @endphp
                                <button type="button" onclick="toggleActionsMenu(event, {{ $tpl->id }})" title="Actions"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                                </button>
                                <div id="actions-menu-{{ $tpl->id }}" class="actions-menu" style="display:none;position:fixed;z-index:1000;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);min-width:140px;overflow:hidden;">
                                    @if($tpl->employee_id)
                                    <button type="button" onclick="loadPersonalTemplate({{ $tpl->employee_id }}); closeAllActionMenus();"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path d="M17.5 3.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 8.5-8.5z"/></svg>
                                        Edit
                                    </button>
                                    @else
                                    <button type="button" onclick="loadTemplate({{ $tpl->division_id }}, {{ $tpl->sub_division_id ?? 'null' }}, {{ $tpl->position_id ?? 'null' }}); closeAllActionMenus();"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path d="M17.5 3.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 8.5-8.5z"/></svg>
                                        Edit
                                    </button>
                                    @endif
                                    <button type="button" onclick='duplicateTemplate(@json($tpl->kpi_data)); closeAllActionMenus();'
                                        title="Use this template's KPI table as the starting point for a new Division/Sub-Division/Position"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                        Use Template
                                    </button>
                                    <button type="button" onclick='openDeleteConfirm({{ $tpl->id }}, @json($warnLabel), @json($warnAssigned))'
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12"/><path d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                        Delete
                                    </button>
                                </div>
                                <form method="POST" action="{{ route('admin.kpi-jd.kpi-template.destroy', $tpl->id) }}" id="delete-form-{{ $tpl->id }}" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 text-xs">No KPI templates saved yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        </div>

        {{-- Custom delete-confirmation modal, styled like the app instead of the
             browser's native confirm() dialog. --}}
        <div class="modal-overlay" id="delete_confirm_modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:1000;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this) closeDeleteConfirm()">
            <div style="background:#fff;border-radius:14px;max-width:440px;width:100%;padding:24px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:6px;">
                    <div style="width:38px;height:38px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px;">🗑️</div>
                    <div>
                        <h3 style="font-size:16px;font-weight:700;color:#1f2937;margin:0;">Delete this KPI template?</h3>
                        <p id="delete_confirm_label" style="font-size:13px;color:#6b7280;margin:4px 0 0;"></p>
                    </div>
                </div>
                <p id="delete_confirm_warning" style="font-size:13px;color:#92400e;background:#fef3c7;border-radius:8px;padding:10px 12px;margin:14px 0 0;display:none;"></p>
                <p style="font-size:12.5px;color:#9ca3af;margin:14px 0 0;">This cannot be undone.</p>
                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
                    <button type="button" onclick="closeDeleteConfirm()" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg transition" style="background-color:#f3f4f6 !important;color:#374151 !important;border:none;cursor:pointer;">Cancel</button>
                    <button type="button" onclick="confirmDeleteProceed()" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg transition" style="background-color:#dc2626 !important;color:white !important;border:none;cursor:pointer;">Delete</button>
                </div>
            </div>
        </div>

        {{-- KPI Table Section (appears after selecting division) --}}
        <div id="kpi_template_section" style="display:none;">
            <section class="bg-white rounded-lg shadow border border-gray-100">
                <div class="p-4 border-b" style="background: linear-gradient(to right, #1f5f46, #287854); border-radius: 12px 12px 0 0;">
                    <h3 id="kpi_table_title" class="text-base font-bold text-white">Template : KPI Table</h3>
                </div>
                <form method="POST" action="{{ route('admin.kpi-jd.kpi-template.save') }}">
                    @csrf
                    <input type="hidden" name="division_id" id="form_division_id">
                    <input type="hidden" name="sub_division_id" id="form_sub_division_id">
                    <input type="hidden" name="position_id" id="form_position_id">
                    <input type="hidden" name="employee_id" id="form_employee_id">

                    <div class="overflow-x-auto">
                        <table class="w-full text-xs" id="kpi_table">
                            <thead>
                                <tr style="background:#287854;">
                                    <th style="padding:7px 9px;text-align:center;font-weight:700;color:#fff;width:36px;border:1px solid #1f5f46;">No.</th>
                                    <th style="padding:7px 9px;text-align:left;font-weight:700;color:#fff;width:200px;border:1px solid #1f5f46;">Key Result Areas</th>
                                    <th style="padding:7px 9px;text-align:left;font-weight:700;color:#fff;border:1px solid #1f5f46;">Key Performance Indicators</th>
                                    <th style="padding:7px 9px;text-align:center;font-weight:700;color:#fff;width:60px;border:1px solid #1f5f46;">Weight (%)</th>
                                    <th style="padding:7px 9px;text-align:center;font-weight:700;color:#fff;width:130px;border:1px solid #1f5f46;">Target</th>
                                    <th style="padding:7px 9px;text-align:center;font-weight:700;color:#fff;width:100px;border:1px solid #1f5f46;">Actual</th>
                                    <th style="padding:7px 9px;text-align:center;font-weight:700;color:#fff;width:60px;border:1px solid #1f5f46;">Score</th>
                                    <th style="padding:7px 9px;text-align:center;font-weight:700;color:#fff;width:65px;border:1px solid #1f5f46;">Final</th>
                                </tr>
                            </thead>
                            <tbody id="kpi_table_body"></tbody>
                        </table>
                    </div>

                    {{-- Running total of Weight of KPIs — flags early if it doesn't add up to 100% --}}
                    <div id="weight_total_banner" style="display:none;margin:12px 20px 16px;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;"></div>

                    {{-- Live preview — replaces the old YTD Dashboard box in this exact spot.
                         YTD Dashboard itself moved to the Performance page (super_admin only);
                         this always-on preview shows how the KRA table above would look as
                         "Goals & KPI" cards, updating as you type — no button/pop-up needed. --}}
                    <div id="kpi_preview_section" style="display:none;">
                        <div style="padding:18px 20px 12px;margin:0 20px 16px;border-top:3px solid #e5e7eb;background:#fafafa;border-radius:0 0 8px 8px;">
                            <h3 style="font-size:15px;font-weight:700;color:#1f5f46;margin:0 0 4px;">🎯 Preview: how this will look on Performance</h3>
                            <p style="margin:0 0 14px;font-size:0.78rem;color:#6b7280;">Updates live as you fill in the table above. Types that can be auto-calculated (Number/Currency/Percentage) get a progress bar; Text type is shown as-is.</p>
                            <div id="kpi_preview_cards_inline" style="display:flex;flex-direction:column;gap:12px;"></div>
                        </div>
                    </div>

                    <div class="p-3 flex items-center gap-1.5">
                        <button type="button" onclick="addResultArea()" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-md transition"
                            style="background-color:#287854 !important; color:white !important; border:none; cursor:pointer;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Result Area
                        </button>
                        @if($isSuperAdmin)
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-white rounded-md shadow transition"
                            style="background-color:#16a34a !important; color:white !important; border:none; cursor:pointer;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save KPI Template
                        </button>
                        @endif
                    </div>
                </form>
            </section>
        </div>

        {{-- Not an "action" list like "Employees Needing a KPI" below — a
             data-fixing notification. These employees' Employee record has no
             contract_id linked, so their Division/Sub-Division/Position can't
             be resolved at all (same reason they'd be missing from Active/
             Inactive KPI below too). Fix the link on their profile first,
             then they'll fall into the KPI-needed list like everyone else. --}}
        @if($employeesWithoutContractCount > 0)
        <section class="bg-white rounded-lg shadow border border-gray-100">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">🔔 Notification</h3>
                <p class="text-xs font-semibold mt-1" style="color:#991b1b;">⚠️ {{ $employeesWithoutContractCount }} {{ \Illuminate\Support\Str::plural('employee', $employeesWithoutContractCount) }} not linked to a Contract yet, so their Division/Position is unknown.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead style="background:#fee2e2;">
                        <tr>
                            <th class="px-2 py-2 text-left text-[11px] font-medium uppercase tracking-wider" style="color:#991b1b;">Employee</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium uppercase tracking-wider" style="color:#991b1b;">Division (from profile)</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium uppercase tracking-wider" style="color:#991b1b;">Status</th>
                            <th class="px-2 py-2 text-right text-[11px] font-medium uppercase tracking-wider" style="color:#991b1b;">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($employeesWithoutContract as $e)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-2 py-2 text-xs font-medium text-gray-900">{{ $e->full_name }}</td>
                            <td class="px-2 py-2 text-xs text-gray-600">{{ $e->division?->name ?? '—' }}</td>
                            <td class="px-2 py-2 text-xs text-gray-600">{{ ucfirst(str_replace('-', ' ', $e->status)) }}</td>
                            <td class="px-2 py-2 text-right">
                                <a href="{{ route('admin.linkers-hub.employee-profile', $e->id) }}"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-md transition"
                                    style="background-color:#991b1b !important; color:white !important; text-decoration:none;">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Who actually has a working KPI right now, and who doesn't — split
             out of "Saved KPI Templates" (that card is template metadata only:
             which Division/Sub-Division/Position/Person a template is set up
             for, not who it currently covers). Active = resolved KPI, still
             employed. Inactive = resolved KPI, but the employee has since
             left — their KPI data is still on file, just no longer in force. --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
        <section class="bg-white rounded-lg shadow border border-gray-100">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">✅ Active KPI</h3>
                <p class="text-xs text-gray-500 mt-1">{{ count($activeKpi) }} {{ \Illuminate\Support\Str::plural('employee', count($activeKpi)) }} currently covered by a KPI.</p>
            </div>
            <div class="overflow-x-auto" style="max-height:360px;">
                <table class="w-full">
                    <thead style="background:#e6f1ec;position:sticky;top:0;">
                        <tr>
                            <th class="px-2 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Division</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-2 py-2 text-center text-[11px] font-medium text-gray-500 uppercase tracking-wider">Areas</th>
                            <th class="px-2 py-2 text-right text-[11px] font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($activeKpi as $row)
                        @php
                            $rowTpl = $row['template'];
                            $rowMenuKey = 'active-' . $rowTpl->id . '-' . $row['employee']->id;
                            $rowAssigned = $rowTpl->assignedEmployees();
                            $rowWarnLabel = $rowTpl->employee_id
                                ? 'Personal KPI — ' . $row['employee']->full_name
                                : $row['division_name'] . ' — ' . ($row['sub_division_name'] ?? 'All') . ($row['position_name'] ? ' — ' . $row['position_name'] : '');
                            $rowWarnAssigned = $rowAssigned->count() > 1
                                ? 'This is currently linked to ' . $rowAssigned->count() . ' employees (' . $rowAssigned->pluck('full_name')->implode(', ') . ') — deleting it removes their KPI/progress too.'
                                : '';
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-2 py-2 text-xs font-medium text-gray-900">{{ $row['employee']->full_name }}</td>
                            <td class="px-2 py-2 text-xs text-gray-600">{{ $row['division_name'] ?? '—' }}</td>
                            <td class="px-2 py-2 text-xs text-gray-600">{{ $row['position_name'] ?? 'All' }}</td>
                            <td class="px-2 py-2 text-xs text-gray-600 text-center">{{ $row['area_count'] }}</td>
                            <td class="px-2 py-2 text-right relative">
                                <button type="button" onclick="toggleActionsMenu(event, '{{ $rowMenuKey }}')" title="Actions"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                                </button>
                                <div id="actions-menu-{{ $rowMenuKey }}" class="actions-menu" style="display:none;position:fixed;z-index:1000;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);min-width:140px;overflow:hidden;">
                                    @if($rowTpl->employee_id)
                                    <button type="button" onclick="loadPersonalTemplate({{ $row['employee']->id }}); closeAllActionMenus();"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path d="M17.5 3.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 8.5-8.5z"/></svg>
                                        Edit
                                    </button>
                                    @else
                                    <button type="button" onclick="loadTemplate({{ $row['division_id'] ?? 'null' }}, {{ $row['sub_division_id'] ?? 'null' }}, {{ $row['position_id'] ?? 'null' }}); closeAllActionMenus();"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path d="M17.5 3.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 8.5-8.5z"/></svg>
                                        Edit
                                    </button>
                                    @endif
                                    <button type="button" onclick='duplicateTemplate(@json($rowTpl->kpi_data)); closeAllActionMenus();'
                                        title="Copy this KPI's table into a new Division/Sub-Division/Position"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                        Duplicate
                                    </button>
                                    <button type="button" onclick='openDeleteConfirm({{ $rowTpl->id }}, @json($rowWarnLabel), @json($rowWarnAssigned))'
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12"/><path d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 text-xs">No one has an active KPI yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white rounded-lg shadow border border-gray-100">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">🚫 Inactive KPI</h3>
                <p class="text-xs text-gray-500 mt-1">{{ count($inactiveKpi) }} terminated {{ \Illuminate\Support\Str::plural('employee', count($inactiveKpi)) }} still {{ count($inactiveKpi) === 1 ? 'has' : 'have' }} KPI data on file.</p>
            </div>
            <div class="overflow-x-auto" style="max-height:360px;">
                <table class="w-full">
                    <thead style="background:#f3f4f6;position:sticky;top:0;">
                        <tr>
                            <th class="px-2 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Division</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-2 py-2 text-center text-[11px] font-medium text-gray-500 uppercase tracking-wider">Areas</th>
                            <th class="px-2 py-2 text-right text-[11px] font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($inactiveKpi as $row)
                        @php
                            $rowTpl = $row['template'];
                            $rowMenuKey = 'inactive-' . $rowTpl->id . '-' . $row['employee']->id;
                            $rowAssigned = $rowTpl->assignedEmployees();
                            $rowWarnLabel = $rowTpl->employee_id
                                ? 'Personal KPI — ' . $row['employee']->full_name
                                : $row['division_name'] . ' — ' . ($row['sub_division_name'] ?? 'All') . ($row['position_name'] ? ' — ' . $row['position_name'] : '');
                            $rowWarnAssigned = $rowAssigned->count() > 1
                                ? 'This is currently linked to ' . $rowAssigned->count() . ' employees (' . $rowAssigned->pluck('full_name')->implode(', ') . ') — deleting it removes their KPI/progress too.'
                                : '';
                        @endphp
                        <tr class="transition" style="background:#f9fafb;">
                            <td class="px-2 py-2 text-xs font-medium" style="color:#9ca3af;">
                                {{ $row['employee']->full_name }}
                                <span style="display:inline-block;margin-left:6px;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;background:#e5e7eb;color:#6b7280;white-space:nowrap;">Terminated</span>
                            </td>
                            <td class="px-2 py-2 text-xs" style="color:#b0b5bd;">{{ $row['division_name'] ?? '—' }}</td>
                            <td class="px-2 py-2 text-xs" style="color:#b0b5bd;">{{ $row['position_name'] ?? 'All' }}</td>
                            <td class="px-2 py-2 text-xs text-center" style="color:#b0b5bd;">{{ $row['area_count'] }}</td>
                            <td class="px-2 py-2 text-right relative">
                                <button type="button" onclick="toggleActionsMenu(event, '{{ $rowMenuKey }}')" title="Actions"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
                                </button>
                                <div id="actions-menu-{{ $rowMenuKey }}" class="actions-menu" style="display:none;position:fixed;z-index:1000;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);min-width:140px;overflow:hidden;">
                                    @if($rowTpl->employee_id)
                                    <button type="button" onclick="loadPersonalTemplate({{ $row['employee']->id }}); closeAllActionMenus();"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path d="M17.5 3.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 8.5-8.5z"/></svg>
                                        Edit
                                    </button>
                                    @else
                                    <button type="button" onclick="loadTemplate({{ $row['division_id'] ?? 'null' }}, {{ $row['sub_division_id'] ?? 'null' }}, {{ $row['position_id'] ?? 'null' }}); closeAllActionMenus();"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/><path d="M17.5 3.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 8.5-8.5z"/></svg>
                                        Edit
                                    </button>
                                    @endif
                                    <button type="button" onclick='duplicateTemplate(@json($rowTpl->kpi_data)); closeAllActionMenus();'
                                        title="Copy this KPI's table into a new Division/Sub-Division/Position"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                        Duplicate
                                    </button>
                                    <button type="button" onclick='openDeleteConfirm({{ $rowTpl->id }}, @json($rowWarnLabel), @json($rowWarnAssigned))'
                                        class="w-full flex items-center gap-2 text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 transition">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12"/><path d="M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 text-xs">No inactive KPI data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        </div>

        {{-- Everyone with a contract but no KPI template resolving to them yet (same
             Division/Sub-Division/Position resolution as KpiTemplate::forEmployee(),
             so this can never disagree with the Performance page or Employee Profile
             about who's "covered"). A to-do list — click through to pre-fill the
             builder above with that person's scope. --}}
        @if($employeesNeedingKpi->isNotEmpty() || ($employeesWithoutContractCount ?? 0) > 0)
        <section class="bg-white rounded-lg shadow border border-gray-100">
            <div class="p-4 border-b">
                <h3 class="text-base font-semibold text-gray-900">📋 Employees Needing a KPI</h3>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $employeesNeedingKpi->count() }} {{ \Illuminate\Support\Str::plural('employee', $employeesNeedingKpi->count()) }} need{{ $employeesNeedingKpi->count() === 1 ? 's' : '' }} a KPI
                </p>
            </div>
            @if($employeesNeedingKpi->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead style="background:#fef3c7;">
                        <tr>
                            <th class="px-2 py-2 text-left text-[11px] font-medium uppercase tracking-wider" style="color:#92400e;">Employee</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium uppercase tracking-wider" style="color:#92400e;">Division</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium uppercase tracking-wider" style="color:#92400e;">Sub-Division</th>
                            <th class="px-2 py-2 text-left text-[11px] font-medium uppercase tracking-wider" style="color:#92400e;">Position</th>
                            <th class="px-2 py-2 text-right text-[11px] font-medium uppercase tracking-wider" style="color:#92400e;">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($employeesNeedingKpi as $row)
                        @php $isTerminated = $row['is_terminated'] ?? false; @endphp
                        <tr class="transition" style="{{ $isTerminated ? 'background:#f3f4f6;' : '' }}" @if(!$isTerminated) onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''" @endif>
                            <td class="px-2 py-2 text-xs font-medium" style="color:{{ $isTerminated ? '#9ca3af' : '#111827' }};">
                                {{ $row['employee']->full_name }}
                                @if($isTerminated)
                                    <span style="display:inline-block;margin-left:6px;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;background:#e5e7eb;color:#6b7280;white-space:nowrap;">Terminated</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-xs" style="color:{{ $isTerminated ? '#b0b5bd' : '#4b5563' }};">{{ $row['division_name'] ?? '—' }}</td>
                            <td class="px-2 py-2 text-xs" style="color:{{ $isTerminated ? '#b0b5bd' : '#4b5563' }};">{{ $row['sub_division_name'] ?? 'All' }}</td>
                            <td class="px-2 py-2 text-xs" style="color:{{ $isTerminated ? '#b0b5bd' : '#4b5563' }};">{{ $row['position_name'] ?? 'All' }}</td>
                            <td class="px-2 py-2 text-right">
                                @unless($isTerminated)
                                <button type="button"
                                    onclick="createKpiForEmployee({{ $row['division_id'] ?? 'null' }}, {{ $row['sub_division_id'] ?? 'null' }}, {{ $row['position_id'] ?? 'null' }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-md transition"
                                    style="background-color:#287854 !important; color:white !important; border:none; cursor:pointer;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Create KPI
                                </button>
                                @endunless
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>
        @endif

    </div>

    <script>
    (function() {
        var subDivisions = @json($subDivisions);
        var positions = @json($positions);
        var divSelect = document.getElementById('kpi_division_select');
        var subSelect = document.getElementById('kpi_sub_division_select');
        var posSelect = document.getElementById('kpi_position_select');
        var formDiv = document.getElementById('form_division_id');
        var formSubDiv = document.getElementById('form_sub_division_id');
        var formPos = document.getElementById('form_position_id');
        var formEmployee = document.getElementById('form_employee_id');
        var employeeSelect = document.getElementById('kpi_employee_select');
        var kpiModePositionFields = document.getElementById('kpi_mode_position_fields');
        var kpiModePersonFields = document.getElementById('kpi_mode_person_fields');
        var kpiModePositionBtn = document.getElementById('kpi_mode_position_btn');
        var kpiModePersonBtn = document.getElementById('kpi_mode_person_btn');
        var section = document.getElementById('kpi_template_section');
        var placeholder = document.getElementById('kpi_placeholder');
        var tbody = document.getElementById('kpi_table_body');
        var title = document.getElementById('kpi_table_title');
        var previewSection = document.getElementById('kpi_preview_section');
        var previewCards = document.getElementById('kpi_preview_cards_inline');
        var weightBanner = document.getElementById('weight_total_banner');
        var areaCounter = 0;
        var isDuplicating = false; // true while a "Duplicate" is waiting for a new Division/Sub-Division

        // ---- Target type: short codes shown in the tiny dropdown glued to each Target field ----
        var TARGET_TYPES = [
            { value: 'number',     label: '#',  title: 'Number (e.g. 8 placements)' },
            { value: 'currency',   label: 'Rp', title: 'Currency (IDR)' },
            { value: 'percentage', label: '%',  title: 'Percentage' },
            { value: 'text',       label: 'Aa', title: "Free text — can't auto-calculate progress" }
        ];

        // Light, non-destructive auto-formatting: only touches values the admin typed as a
        // plain number, leaves anything already worded (e.g. "8 placements/month") untouched.
        function formatByType(value, type) {
            var raw = String(value == null ? '' : value).trim();
            if (!raw) return raw;
            if (type === 'percentage' && /^-?\d+(\.\d+)?$/.test(raw)) return raw + '%';
            if (type === 'currency' && /^-?\d+$/.test(raw)) return 'IDR ' + Number(raw).toLocaleString('id-ID');
            return raw;
        }

        function updateWeightTotal() {
            if (!weightBanner) return;
            var inputs = tbody.querySelectorAll('input[name*="[weight]"]');
            if (!inputs.length) { weightBanner.style.display = 'none'; return; }

            var total = 0;
            inputs.forEach(function(inp) {
                var v = parseFloat(String(inp.value || '').replace(/[^\d.\-]/g, ''));
                if (!isNaN(v)) total += v;
            });
            total = Math.round(total * 100) / 100;

            weightBanner.style.display = 'block';
            if (Math.abs(total - 100) < 0.01) {
                weightBanner.style.background = '#dcfce7';
                weightBanner.style.color = '#15803d';
                weightBanner.textContent = '✅ Total weight: ' + total + '% — adds up to 100%.';
            } else {
                var diff = Math.abs(Math.round((100 - total) * 100) / 100);
                weightBanner.style.background = '#fef3c7';
                weightBanner.style.color = '#92400e';
                weightBanner.textContent = '⚠️ Total weight: ' + total + '% — should add up to 100% ('
                    + (total > 100 ? 'over by ' + diff + '%' : 'short by ' + diff + '%') + ').';
            }
        }
        // Weight inputs are created dynamically, so listen on the tbody itself (event delegation)
        // rather than attaching a listener per input.
        tbody.addEventListener('input', function(e) {
            if (e.target && e.target.name && e.target.name.indexOf('[weight]') !== -1) updateWeightTotal();
        });
        // Same delegation for the live preview — any edit to Key Result Area, KPI,
        // Weight, Target, or Target Type should refresh it immediately.
        tbody.addEventListener('input', updatePreview);
        tbody.addEventListener('change', updatePreview);

        function createGaugeSVG(percent, color) {
            var capped = Math.min(percent, 100);
            var radius = 40;
            var circumference = Math.PI * radius; // half-circle
            var offset = circumference - (capped / 100) * circumference;
            var strokeColor = color === 'teal' ? '#14b8a6' : color === 'gold' ? '#eab308' : color === 'red' ? '#ef4444' : '#9ca3af';

            return '<svg viewBox="0 0 120 70" width="120" height="70">' +
                '<path d="M 10 60 A 40 40 0 0 1 110 60" fill="none" stroke="#e5e7eb" stroke-width="10"/>' +
                '<path d="M 10 60 A 40 40 0 0 1 110 60" fill="none" stroke="' + strokeColor + '" stroke-width="10" stroke-linecap="round" ' +
                'stroke-dasharray="' + circumference + '" stroke-dashoffset="' + offset + '" style="transition:stroke-dashoffset 0.8s ease;"/>' +
                '<text x="27" y="65" style="font-size:8px;fill:#9ca3af;">0%</text>' +
                '<text x="42" y="55" style="font-size:16px;font-weight:800;fill:#1f2937;">' + percent + '%</text>' +
                '<text x="88" y="65" style="font-size:8px;fill:#9ca3af;">100%</text>' +
                '</svg>';
        }

        function addAreaToTable(area) {
            areaCounter++;
            var areaIdx = areaCounter - 1;
            var indicators = area.indicators || [{ kpi: '', weight: '', target: '' }];

            indicators.forEach(function(ind, indIdx) {
                var tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #e5e7eb';
                tr.dataset.areaIdx = areaIdx;
                tr.dataset.indIdx = indIdx;

                if (indIdx === 0) {
                    var tdNo = document.createElement('td');
                    tdNo.style.cssText = 'padding:6px 9px;text-align:center;border:1px solid #e5e7eb;vertical-align:top;font-weight:700;color:#374151;';
                    tdNo.textContent = areaCounter;
                    tdNo.rowSpan = indicators.length;
                    tr.appendChild(tdNo);

                    var tdArea = document.createElement('td');
                    tdArea.style.cssText = 'padding:6px 9px;border:1px solid #e5e7eb;vertical-align:top;';
                    tdArea.rowSpan = indicators.length;
                    var areaInput = document.createElement('input');
                    areaInput.type = 'text'; areaInput.name = 'kpi_data[' + areaIdx + '][key_result_area]'; areaInput.value = area.key_result_area || '';
                    areaInput.placeholder = 'e.g. Recruitment';
                    if (areaInput.value) areaInput.title = areaInput.value;
                    areaInput.addEventListener('input', function() { areaInput.title = areaInput.value; });
                    areaInput.style.cssText = 'width:100%;border:1px solid #e5e7eb;border-radius:4px;padding:4px 7px;font-size:12px;font-weight:600;';
                    tdArea.appendChild(areaInput);

                    var addIndBtn = document.createElement('button');
                    addIndBtn.type = 'button';
                    addIndBtn.title = 'Add another indicator to this area';
                    addIndBtn.textContent = '+ Add Indicator';
                    addIndBtn.style.cssText = 'margin-top:5px;background:none;border:none;padding:0;font-size:10px;font-weight:600;color:#287854;cursor:pointer;';
                    addIndBtn.addEventListener('click', function() { addIndicatorRow(tr); });
                    tdArea.appendChild(addIndBtn);

                    tr.appendChild(tdArea);

                    var hiddenNo = document.createElement('input'); hiddenNo.type = 'hidden'; hiddenNo.name = 'kpi_data[' + areaIdx + '][no]'; hiddenNo.value = areaCounter;
                    tr.appendChild(hiddenNo);
                }

                var fields = [
                    { key: 'kpi', ph: 'Key Performance Indicator', w: '100%', cell: 'padding:6px 9px;border:1px solid #e5e7eb;' },
                    { key: 'weight', ph: '-', w: '60px', cell: 'padding:6px 9px;text-align:center;border:1px solid #e5e7eb;' },
                    { key: 'target', ph: 'Target', w: '130px', cell: 'padding:6px 9px;text-align:center;border:1px solid #e5e7eb;' },
                    { key: 'actual', ph: '-', w: '100px', cell: 'padding:6px 9px;text-align:center;border:1px solid #e5e7eb;background:#fff7ed;' },
                    { key: 'score', ph: '-', w: '60px', cell: 'padding:6px 9px;text-align:center;border:1px solid #e5e7eb;' },
                    { key: 'final_score', ph: '-', w: '60px', cell: 'padding:6px 9px;text-align:center;border:1px solid #e5e7eb;' }
                ];

                fields.forEach(function(f) {
                    var td = document.createElement('td'); td.style.cssText = f.cell;

                    // Target gets a small "type" dropdown glued onto the same input (Number /
                    // Currency IDR / Percentage / Text) — no new column needed. The type is
                    // saved alongside the value (kpi_data[...][target_type]) so Performance can
                    // later know how to read/format it, and it also drives the light
                    // auto-formatting below and the Preview calculation.
                    if (f.key === 'target') {
                        td.style.textAlign = 'left';
                        var wrap = document.createElement('div'); wrap.style.cssText = 'display:flex;gap:4px;align-items:center;';

                        var typeSelect = document.createElement('select');
                        typeSelect.name = 'kpi_data[' + areaIdx + '][indicators][' + indIdx + '][target_type]';
                        typeSelect.title = 'Target type — for consistent formatting and auto-calculating progress later on Performance';
                        typeSelect.style.cssText = 'width:40px;flex-shrink:0;border:1px solid #e5e7eb;border-radius:4px;padding:4px 1px;font-size:12px;text-align:center;background:#fff;';
                        TARGET_TYPES.forEach(function(t) {
                            var opt = document.createElement('option'); opt.value = t.value; opt.textContent = t.label; opt.title = t.title;
                            if ((ind.target_type || 'text') === t.value) opt.selected = true;
                            typeSelect.appendChild(opt);
                        });

                        var targetInput = document.createElement('input'); targetInput.type = 'text';
                        targetInput.name = 'kpi_data[' + areaIdx + '][indicators][' + indIdx + '][target]';
                        targetInput.value = ind.target || ''; targetInput.placeholder = f.ph;
                        targetInput.style.cssText = 'flex:1;min-width:0;border:1px solid #e5e7eb;border-radius:4px;padding:4px 6px;font-size:12px;text-align:center;';
                        if (targetInput.value) targetInput.title = targetInput.value;
                        targetInput.addEventListener('input', function() { targetInput.title = targetInput.value; });
                        targetInput.addEventListener('blur', function() {
                            targetInput.value = formatByType(targetInput.value, typeSelect.value);
                            targetInput.title = targetInput.value;
                        });
                        typeSelect.addEventListener('change', function() {
                            targetInput.value = formatByType(targetInput.value, typeSelect.value);
                            targetInput.title = targetInput.value;
                        });

                        wrap.appendChild(typeSelect); wrap.appendChild(targetInput);

                        // Field name stays target_en (unchanged) — but in practice the
                        // Target box above is filled in English first (the language that
                        // actually appears as primary in the generated document), so this
                        // second box is where the Indonesian version goes, not English.
                        // Shown as an italic sub-line under the Target on the document.
                        var targetEnInput = document.createElement('input'); targetEnInput.type = 'text';
                        targetEnInput.name = 'kpi_data[' + areaIdx + '][indicators][' + indIdx + '][target_en]';
                        targetEnInput.value = ind.target_en || ''; targetEnInput.placeholder = 'Bahasa Indonesia (optional)';
                        if (targetEnInput.value) targetEnInput.title = targetEnInput.value;
                        targetEnInput.addEventListener('input', function() { targetEnInput.title = targetEnInput.value; });
                        targetEnInput.style.cssText = 'width:100%;margin-top:3px;border:1px solid #e5e7eb;border-radius:4px;padding:3px 6px;font-size:11px;font-style:italic;color:#6b7280;text-align:center;';

                        td.appendChild(wrap); td.appendChild(targetEnInput); tr.appendChild(td);
                        return;
                    }

                    var input = document.createElement('input'); input.type = 'text';
                    input.name = 'kpi_data[' + areaIdx + '][indicators][' + indIdx + '][' + f.key + ']';
                    input.value = ind[f.key] || ''; input.placeholder = f.ph;
                    // title = full-text tooltip on hover, so a value longer than the box (long
                    // KPI/target text) is still readable without needing to click into the field.
                    if (input.value) input.title = input.value;
                    input.addEventListener('input', function() { input.title = input.value; });
                    input.style.cssText = 'width:' + f.w + ';border:1px solid #e5e7eb;border-radius:4px;padding:4px 6px;font-size:12px;text-align:' + (f.key === 'kpi' ? 'left' : 'center') + ';' + (f.key === 'actual' ? 'font-weight:700;border-color:#fed7aa;' : '');

                    // The KPI/indicator cell also carries a small delete-row ("x") button so an
                    // unwanted indicator (e.g. an unused extra row within an area) can be removed
                    // without retyping everything else.
                    if (f.key === 'kpi') {
                        var kpiWrap = document.createElement('div'); kpiWrap.style.cssText = 'display:flex;gap:4px;align-items:center;';
                        input.style.width = '100%'; input.style.flex = '1'; input.style.minWidth = '0';
                        var delBtn = document.createElement('button');
                        delBtn.type = 'button'; delBtn.title = 'Remove this indicator';
                        delBtn.style.cssText = 'flex-shrink:0;display:flex;align-items:center;justify-content:center;width:18px;height:18px;border:none;background:none;color:#9ca3af;cursor:pointer;border-radius:4px;';
                        delBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>';
                        delBtn.addEventListener('mouseenter', function() { delBtn.style.color = '#dc2626'; delBtn.style.background = '#fee2e2'; });
                        delBtn.addEventListener('mouseleave', function() { delBtn.style.color = '#9ca3af'; delBtn.style.background = 'none'; });
                        delBtn.addEventListener('click', function() { removeIndicatorRow(tr); });
                        kpiWrap.appendChild(input); kpiWrap.appendChild(delBtn);

                        // Field name stays kpi_en (unchanged) — but in practice the KPI
                        // box above is filled in English first (the language that
                        // actually appears as primary in the generated document), so this
                        // second box is where the Indonesian version goes, not English.
                        // Shown as an italic sub-line under the KPI name on the document.
                        var kpiEnInput = document.createElement('input'); kpiEnInput.type = 'text';
                        kpiEnInput.name = 'kpi_data[' + areaIdx + '][indicators][' + indIdx + '][kpi_en]';
                        kpiEnInput.value = ind.kpi_en || ''; kpiEnInput.placeholder = 'Bahasa Indonesia (optional)';
                        if (kpiEnInput.value) kpiEnInput.title = kpiEnInput.value;
                        kpiEnInput.addEventListener('input', function() { kpiEnInput.title = kpiEnInput.value; });
                        kpiEnInput.style.cssText = 'width:100%;margin-top:3px;border:1px solid #e5e7eb;border-radius:4px;padding:3px 6px;font-size:11px;font-style:italic;color:#6b7280;';

                        td.appendChild(kpiWrap); td.appendChild(kpiEnInput); tr.appendChild(td);
                        return;
                    }

                    // Weight is always a percentage — auto-append "%" on blur so typing a
                    // plain number (e.g. "50") reads back as "50%" without the admin having
                    // to type the sign themselves. Same light, non-destructive formatting
                    // Target already gets for its Percentage type (formatByType), and the
                    // weight total banner already strips non-numeric characters before
                    // summing, so this never breaks that calculation.
                    if (f.key === 'weight') {
                        input.addEventListener('blur', function() {
                            input.value = formatByType(input.value, 'percentage');
                            input.title = input.value;
                        });
                    }

                    td.appendChild(input); tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });
        }

        // Reconstructs the full { key_result_area, indicators: [...] } structure straight
        // from whatever is currently typed into the table — used so adding/removing a single
        // indicator row can re-render the whole table (correct rowSpans, re-indexed field
        // names) without losing anything the user already filled in elsewhere.
        function collectKpiDataFromForm() {
            var areas = [];
            var currentArea = null;
            tbody.querySelectorAll('tr').forEach(function(rowEl) {
                var areaInput = rowEl.querySelector('input[name*="[key_result_area]"]');
                if (areaInput) {
                    currentArea = { key_result_area: areaInput.value, indicators: [] };
                    areas.push(currentArea);
                }
                var kpiInput = rowEl.querySelector('input[name*="[kpi]"]');
                if (!kpiInput || !currentArea) return;
                currentArea.indicators.push({
                    kpi: kpiInput.value || '',
                    kpi_en: (rowEl.querySelector('input[name*="[kpi_en]"]') || {}).value || '',
                    weight: (rowEl.querySelector('input[name*="[weight]"]') || {}).value || '',
                    target: (rowEl.querySelector('input[name*="[target]"]') || {}).value || '',
                    target_en: (rowEl.querySelector('input[name*="[target_en]"]') || {}).value || '',
                    target_type: (rowEl.querySelector('select[name*="[target_type]"]') || {}).value || 'text',
                    actual: (rowEl.querySelector('input[name*="[actual]"]') || {}).value || '',
                    score: (rowEl.querySelector('input[name*="[score]"]') || {}).value || '',
                    final_score: (rowEl.querySelector('input[name*="[final_score]"]') || {}).value || ''
                });
            });
            return areas;
        }

        function removeIndicatorRow(rowEl) {
            var areaIdx = parseInt(rowEl.dataset.areaIdx, 10);
            var indIdx = parseInt(rowEl.dataset.indIdx, 10);
            var areas = collectKpiDataFromForm();
            if (!areas[areaIdx]) return;
            areas[areaIdx].indicators.splice(indIdx, 1);
            if (areas[areaIdx].indicators.length === 0) areas.splice(areaIdx, 1); // drop the area if it's now empty
            renderTable(areas);
        }

        function addIndicatorRow(rowEl) {
            var areaIdx = parseInt(rowEl.dataset.areaIdx, 10);
            var areas = collectKpiDataFromForm();
            if (!areas[areaIdx]) return;
            areas[areaIdx].indicators.push({ kpi: '', weight: '', target: '' });
            renderTable(areas);
        }

        function renderTable(kpiData) {
            tbody.innerHTML = '';
            areaCounter = 0;

            var areas = [];

            if (Array.isArray(kpiData)) {
                areas = kpiData;
            } else if (kpiData && typeof kpiData === 'object') {
                if (kpiData.areas && Array.isArray(kpiData.areas)) {
                    areas = kpiData.areas;
                } else {
                    Object.keys(kpiData).forEach(function(k) {
                        if (k !== 'ytd_dashboard' && k !== 'areas') {
                            var item = kpiData[k];
                            if (item && item.key_result_area) areas.push(item);
                        }
                    });
                }
                // 'ytd_dashboard', if present, is intentionally not read here — it's a
                // separate, mostly-static department scorecard (now shown on the
                // Performance page, super_admin only) and has nothing to do with this
                // KRA table's own live preview below.
            }

            if (areas.length === 0) areas = [{ no: 1, key_result_area: '', indicators: [{ kpi: '', weight: '', target: '' }] }];
            areas.forEach(function(area) { addAreaToTable(area); });

            updateWeightTotal();
            updatePreview();
        }

        window.addResultArea = function() {
            addAreaToTable({ no: areaCounter + 1, key_result_area: '', indicators: [{ kpi: '', weight: '', target: '' }] });
            updateWeightTotal();
        };

        // ---- Position dropdown: filtered by the selected Division, and further narrowed
        // by Sub-Division once one is picked (mirrors the same cascading filter already
        // used on the Contract creation page, for consistency): a position with no
        // sub_division_id is division-wide and always shown; one with a sub_division_id
        // only shows once that specific sub-division is selected. ----
        function renderPositionsForSelection() {
            var divId = parseInt(divSelect.value);
            var subDivVal = subSelect.value || '';
            var previousVal = posSelect.value;
            posSelect.innerHTML = '<option value="">Select position</option>';
            if (!divId) return;
            positions
                .filter(function(p) { return parseInt(p.division_id) === divId; })
                .filter(function(p) { return !p.sub_division_id || String(p.sub_division_id) === String(subDivVal); })
                .forEach(function(p) {
                    var opt = document.createElement('option'); opt.value = p.id; opt.textContent = p.name;
                    if (String(p.id) === String(previousVal)) opt.selected = true;
                    posSelect.appendChild(opt);
                });
        }

        // ---- Duplicate: clone a saved template's KPI table into the form, but force
        // the admin to pick a *different* Division/Sub-Division/Position before it can
        // be saved, so the source template is never accidentally overwritten. ----
        window.duplicateTemplate = function(kpiData) {
            isDuplicating = true;
            divSelect.value = '';
            subSelect.innerHTML = '<option value="">Select sub-division</option>';
            posSelect.innerHTML = '<option value="">Select position</option>';
            formDiv.value = '';
            formSubDiv.value = '';
            formPos.value = '';
            renderTable(kpiData);
            title.textContent = 'Template : KPI Table (Duplicated - choose a Division, Sub-Division & Position to save this into)';
            section.style.display = 'block';
            placeholder.style.display = 'none';
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        function loadKpiData() {
            var divId = divSelect.value;
            var subDivId = subSelect.value || '0';
            var posId = posSelect.value || '0';
            if (!divId) { if (!isDuplicating) { section.style.display = 'none'; placeholder.style.display = 'block'; } return; }
            formDiv.value = divId; formSubDiv.value = subDivId || ''; formPos.value = posId || '';

            var divName = divSelect.options[divSelect.selectedIndex].text;
            var subName = subSelect.value ? subSelect.options[subSelect.selectedIndex].text : '';
            var posName = posSelect.value ? posSelect.options[posSelect.selectedIndex].text : '';
            var scopeLabel = divName + (subName ? ' - ' + subName : '') + (posName ? ' - ' + posName : '');

            // While duplicating, the table already holds the cloned KPI data - picking a
            // target Division/Sub-Division/Position here should only update the title, not
            // fetch and overwrite it with whatever (or nothing) is already saved there.
            if (isDuplicating) {
                title.textContent = 'Template : KPI Table (Duplicated - saving into ' + scopeLabel + ')';
                section.style.display = 'block'; placeholder.style.display = 'none';
                return;
            }

            title.textContent = 'Template : KPI Table for ' + scopeLabel;

            fetch('/admin/kpi-jd/kpi-template/' + divId + '/' + subDivId + '/' + posId)
                .then(function(r) { return r.json(); })
                .then(function(data) { renderTable(data.kpi_data); section.style.display = 'block'; placeholder.style.display = 'none'; })
                .catch(function() { renderTable([]); section.style.display = 'block'; placeholder.style.display = 'none'; });
        }

        // Pulled out of the divSelect 'change' listener so loadTemplate() (Edit) can
        // rebuild the Sub-Division options directly, in order, without dispatching a
        // fake 'change' event — dispatching one used to also fire loadKpiData() with
        // the Sub-Division/Position not set yet, kicking off a throwaway fetch that
        // could occasionally resolve AFTER the real one and clobber it with the wrong
        // (or default) KPI data — a race that was invisible on a fast local server but
        // showed up on production's less predictable network timing.
        function renderSubDivisionsForSelection() {
            var divId = parseInt(divSelect.value);
            subSelect.innerHTML = '<option value="">Select sub-division</option>';
            if (!divId) return;
            subDivisions.forEach(function(sd) {
                if (parseInt(sd.division_id) === divId) {
                    var opt = document.createElement('option'); opt.value = sd.id; opt.textContent = sd.name; subSelect.appendChild(opt);
                }
            });
        }

        divSelect.addEventListener('change', function() {
            renderSubDivisionsForSelection();
            renderPositionsForSelection();
            loadKpiData();
        });
        subSelect.addEventListener('change', function() { renderPositionsForSelection(); loadKpiData(); });
        posSelect.addEventListener('change', loadKpiData);

        // ---- "By Position" vs "By Person" mode toggle. Switching clears whatever
        // the other mode had selected, so Save always unambiguously targets one
        // scope or the other (never both a Division/Sub/Position AND an employee_id
        // at the same time). ----
        window.switchKpiMode = function(mode) {
            isDuplicating = false;
            var toPosition = mode === 'position';
            kpiModePositionFields.style.display = toPosition ? '' : 'none';
            kpiModePersonFields.style.display = toPosition ? 'none' : '';
            kpiModePositionBtn.style.background = toPosition ? '#287854' : '#f3f4f6';
            kpiModePositionBtn.style.color = toPosition ? '#fff' : '#374151';
            kpiModePersonBtn.style.background = toPosition ? '#f3f4f6' : '#287854';
            kpiModePersonBtn.style.color = toPosition ? '#374151' : '#fff';

            if (toPosition) {
                employeeSelect.value = ''; formEmployee.value = '';
            } else {
                divSelect.value = '';
                subSelect.innerHTML = '<option value="">Select sub-division</option>';
                posSelect.innerHTML = '<option value="">Select position</option>';
                formDiv.value = ''; formSubDiv.value = ''; formPos.value = '';
            }
            section.style.display = 'none';
            placeholder.style.display = 'block';
        };

        // "By Person" counterpart to loadKpiData() above — fetches via
        // getKpiTemplateForEmployee(), which returns either their existing
        // personal KPI or a copy of their current position KPI as a starting
        // point (see that method's docblock). is_personal in the response tells
        // us which, purely for the title text below.
        function loadKpiDataForEmployee() {
            var employeeId = employeeSelect.value;
            if (!employeeId) { section.style.display = 'none'; placeholder.style.display = 'block'; return; }
            formEmployee.value = employeeId;

            var empName = employeeSelect.options[employeeSelect.selectedIndex].text;
            title.textContent = 'Template : Personal KPI for ' + empName + '...';

            fetch('/admin/kpi-jd/kpi-template-employee/' + employeeId)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    title.textContent = 'Template : Personal KPI for ' + empName
                        + (data.is_personal ? '' : ' (starting from their current position KPI)');
                    renderTable(data.kpi_data);
                    section.style.display = 'block'; placeholder.style.display = 'none';
                })
                .catch(function() { renderTable([]); section.style.display = 'block'; placeholder.style.display = 'none'; });
        }
        employeeSelect.addEventListener('change', loadKpiDataForEmployee);

        // ---- Preview: read whatever is currently typed in the form (nothing is saved) and
        // render it as motivational "Goals & KPI" cards, matching the visual language already
        // used on the Performance page. Cards are grouped by Key Result Area: each area gets a
        // gauge diagram (the same half-circle gauge already used for YTD Dashboard) showing its
        // weighted-average progress, and each indicator underneath keeps a slim progress bar. ----
        function escapeHtml(str) {
            var div = document.createElement('div'); div.textContent = str || ''; return div.innerHTML;
        }

        function parseNumeric(str, type) {
            if (!str) return null;
            // Currency values are auto-formatted with '.' as a THOUSANDS separator
            // (see formatByType above), never a decimal point — stripping only
            // digits/minus avoids parseFloat stopping at the second '.' and
            // misreading "IDR 7.600.000" as 7.6.
            var cleaned = type === 'currency'
                ? String(str).replace(/[^\d\-]/g, '')
                : String(str).replace(/[^\d.\-]/g, '');
            if (!cleaned) return null;
            var n = parseFloat(cleaned);
            return isNaN(n) ? null : n;
        }

        function computeIndicatorResult(r) {
            var targetNum = parseNumeric(r.target, r.type);
            var actualNum = parseNumeric(r.actual, r.type);
            var canCompute = (r.type === 'number' || r.type === 'currency' || r.type === 'percentage') && targetNum !== null && targetNum > 0;
            var pct = (canCompute && actualNum !== null) ? Math.min(100, Math.round((actualNum / targetNum) * 100)) : null;
            return { targetNum: targetNum, actualNum: actualNum, canCompute: canCompute, pct: pct };
        }

        function statusStyle(pct) {
            if (pct === null) return { color: '#9ca3af', label: '📋 No data yet', bg: '#f3f4f6', txt: '#6b7280', emoji: '📋' };
            if (pct >= 100) return { color: '#1baf7a', label: '✅ Achieved', bg: '#eaf3de', txt: '#27500a', emoji: '🎉' };
            if (pct >= 50) return { color: '#534ab7', label: '💪 In progress', bg: '#eeedfe', txt: '#3c3489', emoji: '💪' };
            return { color: '#e34948', label: '⚠️ At risk', bg: '#fcebeb', txt: '#a32d2d', emoji: '⚡' };
        }

        function renderIndicatorRow(r) {
            var s = statusStyle(r.pct);
            var barPct = r.pct === null ? 0 : r.pct;
            var valueLine = r.canCompute
                ? (r.actualNum !== null ? r.actualNum : 0) + ' / ' + r.targetNum + (r.type === 'percentage' ? '%' : '')
                : (r.actual || '—') + ' / ' + (r.target || '—') + ' (type: Text — actual entered manually)';

            var html = '<div>';
            html += '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:6px;">';
            html += '<div style="font-size:0.82rem;font-weight:600;color:#1b4332;line-height:1.4;flex:1;">';
            html += '<span style="margin-right:6px;">' + s.emoji + '</span>' + escapeHtml(r.kpi);
            if (r.weight) html += '<span style="font-size:0.7rem;color:#9ca3af;font-weight:400;"> · weight ' + escapeHtml(r.weight) + '%</span>';
            html += '</div>';
            html += '<span style="font-size:0.66rem;padding:3px 9px;border-radius:20px;font-weight:600;white-space:nowrap;background:' + s.bg + ';color:' + s.txt + ';">' + s.label + '</span>';
            html += '</div>';
            html += '<div style="display:flex;align-items:center;gap:10px;">';
            html += '<div style="flex:1;height:7px;background:#f3f4f6;border-radius:4px;overflow:hidden;"><div style="width:' + barPct + '%;height:100%;background:' + s.color + ';border-radius:4px;"></div></div>';
            html += '<span style="font-size:0.75rem;font-weight:700;min-width:32px;text-align:right;color:' + s.color + ';">' + (r.pct === null ? '—' : r.pct + '%') + '</span>';
            html += '</div>';
            html += '<div style="font-size:0.68rem;color:#6b7280;margin-top:2px;">' + escapeHtml(valueLine) + '</div>';
            html += '</div>';
            return html;
        }

        // One accent color per Key Result Area (cycling), so a set of several
        // areas reads as a colorful board at a glance instead of identical white
        // cards — independent of the gauge's own status color (which stays
        // teal/gold/red/gray, since that one has to keep meaning "on track" vs
        // "at risk"). Same palette used server-side in goals-cards.blade.php.
        var AREA_ACCENT_COLORS = ['#1d4ed8', '#b45309', '#6d28d9', '#15803d', '#0f766e', '#be185d'];

        function renderAreaCard(group, areaIndex) {
            var accent = AREA_ACCENT_COLORS[areaIndex % AREA_ACCENT_COLORS.length];
            var results = group.rows.map(function(r) {
                var res = computeIndicatorResult(r);
                res.kpi = r.kpi; res.weight = r.weight; res.target = r.target; res.actual = r.actual; res.type = r.type;
                return res;
            });

            // Weighted average across this area's computable indicators — weight is used as
            // the weighting factor, falling back to a plain average if none carry a weight.
            var computable = results.filter(function(r) { return r.pct !== null; });
            var totalWeight = computable.reduce(function(sum, r) { return sum + (parseFloat(String(r.weight).replace(/[^\d.\-]/g, '')) || 0); }, 0);
            var weightedSum = computable.reduce(function(sum, r) {
                return sum + (r.pct * (parseFloat(String(r.weight).replace(/[^\d.\-]/g, '')) || 0));
            }, 0);
            var areaPct = null;
            if (computable.length) {
                areaPct = totalWeight > 0
                    ? Math.round(weightedSum / totalWeight)
                    : Math.round(computable.reduce(function(s, r) { return s + r.pct; }, 0) / computable.length);
            }
            var gaugeColor = areaPct === null ? 'gray' : areaPct >= 100 ? 'teal' : areaPct >= 50 ? 'gold' : 'red';

            var html = '<div style="background:#fff;border:1px solid #e5e7eb;border-top:4px solid ' + accent + ';border-radius:12px;padding:16px 18px;">';
            html += '<div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;flex-wrap:wrap;">';
            html += '<div style="flex-shrink:0;">' + createGaugeSVG(areaPct === null ? 0 : areaPct, gaugeColor) + '</div>';
            html += '<div style="flex:1;min-width:160px;">';
            html += '<h4 style="margin:0 0 2px;font-size:0.95rem;font-weight:800;color:' + accent + ';">' + escapeHtml(group.area) + '</h4>';
            html += '<div style="font-size:0.72rem;color:#9ca3af;">' + (areaPct === null ? 'Not enough data to calculate yet' : 'Weighted average across this area') + '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;border-top:1px solid #f3f4f6;padding-top:10px;">';
            html += results.map(renderIndicatorRow).join('');
            html += '</div>';
            html += '</div>';
            return html;
        }

        // Renders inline, right where the old YTD Dashboard box used to sit — no
        // button, no pop-up. Called after every table render and on every edit to
        // the table (see the tbody 'input'/'change' listeners above).
        function updatePreview() {
            var rows = [];
            var currentArea = '';
            tbody.querySelectorAll('tr').forEach(function(rowEl) {
                var areaInput = rowEl.querySelector('input[name*="[key_result_area]"]');
                if (areaInput) currentArea = areaInput.value;

                var kpiInput = rowEl.querySelector('input[name*="[kpi]"]');
                if (!kpiInput) return; // header/no-indicator row

                rows.push({
                    area: currentArea || '(untitled area)',
                    kpi: kpiInput.value || '(not filled in yet)',
                    weight: (rowEl.querySelector('input[name*="[weight]"]') || {}).value || '',
                    target: (rowEl.querySelector('input[name*="[target]"]') || {}).value || '',
                    type: (rowEl.querySelector('select[name*="[target_type]"]') || {}).value || 'text',
                    actual: (rowEl.querySelector('input[name*="[actual]"]') || {}).value || ''
                });
            });

            if (!rows.length) {
                previewSection.style.display = 'none';
                return;
            }
            previewSection.style.display = 'block';

            // Group indicators by area, preserving the order areas first appear in.
            var groups = [];
            var groupIndex = {};
            rows.forEach(function(r) {
                if (!(r.area in groupIndex)) { groupIndex[r.area] = groups.length; groups.push({ area: r.area, rows: [] }); }
                groups[groupIndex[r.area]].rows.push(r);
            });

            previewCards.innerHTML = groups.map(renderAreaCard).join('');
        }

        window.loadTemplate = function(divId, subDivId, posId) {
            isDuplicating = false; // Edit always loads/overwrites from the real saved template
            switchKpiMode('position');
            // Set Division → Sub-Division → Position in order and only fetch once
            // everything is in its final state — see renderSubDivisionsForSelection()
            // above for why this no longer dispatches a fake 'change' event.
            divSelect.value = divId;
            renderSubDivisionsForSelection();
            if (subDivId) subSelect.value = subDivId;
            renderPositionsForSelection();
            if (posId) posSelect.value = posId;
            loadKpiData();
        };

        // Edit action on a *personal* row in the Saved Templates list below.
        window.loadPersonalTemplate = function(employeeId) {
            isDuplicating = false;
            switchKpiMode('person');
            employeeSelect.value = employeeId;
            loadKpiDataForEmployee();
        };

        // "Create KPI" from the Employees Needing a KPI list below — same as Edit
        // (no template exists yet for them, so the fetch naturally comes back with
        // the blank starter template), just also scrolls the builder into view since
        // that list can be scrolled well past it.
        window.createKpiForEmployee = function(divId, subDivId, posId) {
            loadTemplate(divId, subDivId, posId);
            var card = document.getElementById('kpi_template_card');
            if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        // ---- Per-row "⋮" actions menu (Edit / Duplicate / Delete), replacing the
        // three always-visible buttons so the Saved Templates column stays narrow
        // enough to sit side-by-side with the KPI Template card. ----
        window.closeAllActionMenus = function() {
            document.querySelectorAll('.actions-menu').forEach(function(m) { m.style.display = 'none'; });
        };
        // Positioned as `fixed` and placed with getBoundingClientRect() (rather than
        // `absolute` inside the table), so it escapes the Saved Templates table's
        // overflow-x-auto wrapper instead of getting clipped for rows near the
        // bottom of the list — and flips above the button when there's not enough
        // room below.
        window.toggleActionsMenu = function(evt, id) {
            evt.stopPropagation();
            var menu = document.getElementById('actions-menu-' + id);
            var wasOpen = menu.style.display === 'block';
            closeAllActionMenus();
            if (wasOpen) return;

            var btn = evt.currentTarget.getBoundingClientRect();
            menu.style.display = 'block'; // measure it before positioning
            var menuHeight = menu.offsetHeight;
            var menuWidth = menu.offsetWidth;
            var spaceBelow = window.innerHeight - btn.bottom;
            var top = (spaceBelow >= menuHeight + 8) ? btn.bottom + 4 : btn.top - menuHeight - 4;
            var left = Math.min(btn.right - menuWidth, window.innerWidth - menuWidth - 8);

            menu.style.top = Math.max(8, top) + 'px';
            menu.style.left = Math.max(8, left) + 'px';
        };
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.actions-menu') && !e.target.closest('[onclick^="toggleActionsMenu"]')) {
                closeAllActionMenus();
            }
        });
        window.addEventListener('scroll', closeAllActionMenus, true);
        window.addEventListener('resize', closeAllActionMenus);

        // ---- Custom delete-confirmation modal (replaces the native confirm()) ----
        var pendingDeleteTemplateId = null;
        window.openDeleteConfirm = function(id, label, warning) {
            pendingDeleteTemplateId = id;
            document.getElementById('delete_confirm_label').textContent = 'For: ' + label;
            var warnEl = document.getElementById('delete_confirm_warning');
            if (warning) { warnEl.textContent = '⚠️ ' + warning; warnEl.style.display = 'block'; }
            else { warnEl.style.display = 'none'; }
            document.getElementById('delete_confirm_modal').style.display = 'flex';
        };
        window.closeDeleteConfirm = function() {
            pendingDeleteTemplateId = null;
            document.getElementById('delete_confirm_modal').style.display = 'none';
        };
        window.confirmDeleteProceed = function() {
            if (pendingDeleteTemplateId) {
                var form = document.getElementById('delete-form-' + pendingDeleteTemplateId);
                if (form) form.submit();
            }
            closeDeleteConfirm();
        };
    })();
    </script>
@endsection