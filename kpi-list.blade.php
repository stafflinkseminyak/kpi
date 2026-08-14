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
        .ytd-card { border:1px solid #e5e7eb; border-radius:8px; padding:14px; background:#fff; }
        .ytd-card h4 { font-size:13px; font-weight:700; color:#1f2937; margin:0 0 4px 0; text-decoration:underline; }
        .ytd-val { font-size:18px; font-weight:700; margin:0; }
        .ytd-row { display:flex; justify-content:space-between; font-size:12px; color:#6b7280; margin:2px 0; }
        .ytd-bar { height:4px; border-radius:2px; margin:8px 0 10px 0; }
        .yoy-tag { font-size:10px; margin-top:2px; }
        .yoy-down { color:#ef4444; }
        .yoy-up { color:#22c55e; }
    </style>

    <div class="space-y-6">

        @if(session('success'))
            <div class="p-4 text-sm text-green-800 bg-green-100 border border-green-200 rounded-lg">{{ session('success') }}</div>
        @endif

        {{-- KPI Template Box --}}
        <section class="bg-white rounded-lg shadow border border-gray-100">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">KPI Template</h3>
                <p class="text-sm text-gray-500 mt-1">Set KPI targets for each division, sub-division, and position.</p>
            </div>
            <div class="p-6 border-b bg-gray-50/40">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <div class="md:col-span-4">
                        <label for="kpi_division_select" class="block text-sm font-medium text-gray-700 mb-2">Division</label>
                        <select id="kpi_division_select"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#287854] focus:border-transparent bg-white">
                            <option value="">Select division</option>
                            @foreach ($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label for="kpi_sub_division_select" class="block text-sm font-medium text-gray-700 mb-2">Sub-Division</label>
                        <select id="kpi_sub_division_select"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#287854] focus:border-transparent bg-white">
                            <option value="">Select sub-division</option>
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label for="kpi_position_select" class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                        <select id="kpi_position_select"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#287854] focus:border-transparent bg-white">
                            <option value="">Select position</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="kpi_placeholder" class="p-8 text-center text-gray-400">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="text-sm">Select a division to view or set KPI targets.</p>
            </div>
        </section>

        {{-- Saved Templates — full page width so Actions never needs a horizontal scroll --}}
        <section class="bg-white rounded-lg shadow border border-gray-100">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Saved KPI Templates</h3>
                <p class="text-sm text-gray-500 mt-1">Templates already configured for divisions.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#e6f1ec]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Division</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub-Division</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Areas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Employees</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $templates = \App\Models\KpiTemplate::with(['division', 'subDivision', 'position'])->latest()->get(); @endphp
                        @forelse ($templates as $tpl)
                        @php $assigned = $tpl->assignedEmployees(); @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $tpl->division?->name ?? '-' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ $tpl->subDivision?->name ?? 'All' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ $tpl->position?->name ?? 'All' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600">@php $kd = $tpl->kpi_data ?? []; $cnt = collect($kd)->filter(fn($v,$k) => is_numeric($k))->count(); @endphp {{ $cnt }} areas</td>
                            <td class="px-4 py-4 text-sm text-gray-600">
                                @if($assigned->isEmpty())
                                    <span class="text-gray-400 italic">No one yet</span>
                                @else
                                    <span title="{{ $assigned->pluck('full_name')->implode(', ') }}">
                                        {{ $assigned->count() }} {{ \Illuminate\Support\Str::plural('person', $assigned->count()) }}
                                        <span class="text-gray-400">({{ $assigned->pluck('first_name')->take(2)->implode(', ') }}{{ $assigned->count() > 2 ? ', ...' : '' }})</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="inline-flex flex-wrap items-center justify-end gap-1.5">
                                    <button type="button" onclick="loadTemplate({{ $tpl->division_id }}, {{ $tpl->sub_division_id ?? 'null' }}, {{ $tpl->position_id ?? 'null' }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-white rounded transition"
                                        style="background-color:#6d28d9 !important; color:white !important;">Edit</button>
                                    <button type="button" onclick='duplicateTemplate(@json($tpl->kpi_data))'
                                        title="Copy this template's KPI table into a new Division/Sub-Division/Position"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-white rounded transition"
                                        style="background-color:#0891b2 !important; color:white !important;">Duplicate</button>
                                    <form method="POST" action="{{ route('admin.kpi-jd.kpi-template.destroy', $tpl->id) }}" style="display:inline;"
                                        @php
                                            $warnLabel = $tpl->division?->name . ' — ' . ($tpl->subDivision?->name ?? 'All') . ($tpl->position ? ' — ' . $tpl->position->name : '');
                                            $warnAssigned = $assigned->isNotEmpty()
                                                ? " This is currently linked to {$assigned->count()} " . \Illuminate\Support\Str::plural('employee', $assigned->count()) . " ({$assigned->pluck('full_name')->implode(', ')}) — deleting it removes their KPI/progress too."
                                                : '';
                                        @endphp
                                        onsubmit="return confirm('Delete the KPI template for {{ addslashes($warnLabel) }}?{{ addslashes($warnAssigned) }}\n\nThis cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-white rounded transition"
                                            style="background-color:#dc2626 !important; color:white !important;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">No KPI templates saved yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- KPI Table Section (appears after selecting division) --}}
        <div id="kpi_template_section" style="display:none;">
            <section class="bg-white rounded-lg shadow border border-gray-100">
                <div class="p-6 border-b" style="background: linear-gradient(to right, #1f5f46, #287854); border-radius: 12px 12px 0 0;">
                    <h3 id="kpi_table_title" class="text-xl font-bold text-white">Template : KPI Table</h3>
                </div>
                <form method="POST" action="{{ route('admin.kpi-jd.kpi-template.save') }}">
                    @csrf
                    <input type="hidden" name="division_id" id="form_division_id">
                    <input type="hidden" name="sub_division_id" id="form_sub_division_id">
                    <input type="hidden" name="position_id" id="form_position_id">

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" id="kpi_table">
                            <thead>
                                <tr style="background:#287854;">
                                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#fff;width:50px;border:1px solid #1f5f46;">No.</th>
                                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#fff;width:240px;border:1px solid #1f5f46;">Key Result Areas</th>
                                    <th style="padding:10px 12px;text-align:left;font-weight:700;color:#fff;border:1px solid #1f5f46;">Key Performance Indicators</th>
                                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#fff;width:80px;border:1px solid #1f5f46;">Weight of KPIs</th>
                                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#fff;width:150px;border:1px solid #1f5f46;">Target</th>
                                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#fff;width:120px;border:1px solid #1f5f46;">Actual</th>
                                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#fff;width:80px;border:1px solid #1f5f46;">Score</th>
                                    <th style="padding:10px 12px;text-align:center;font-weight:700;color:#fff;width:80px;border:1px solid #1f5f46;">Final Score</th>
                                </tr>
                            </thead>
                            <tbody id="kpi_table_body"></tbody>
                        </table>
                    </div>

                    {{-- Running total of Weight of KPIs — flags early if it doesn't add up to 100% --}}
                    <div id="weight_total_banner" style="display:none;margin:14px 24px 20px;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;"></div>

                    {{-- YTD Dashboard Section — visually separated from the KPI table above it
                         so the two don't read as one crammed block. --}}
                    <div id="ytd_dashboard_section" style="display:none;">
                        <div style="padding:24px 24px 16px;margin:0 24px 20px;border-top:3px solid #e5e7eb;background:#fafafa;border-radius:0 0 8px 8px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                                <h3 style="font-size:18px;font-weight:700;color:#1f2937;margin:0;">📊 YTD Dashboard</h3>
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <span style="font-size:12px;color:#6b7280;border:1px solid #d1d5db;padding:4px 10px;border-radius:4px;">Select YTD Month</span>
                                    <span style="font-size:12px;font-weight:600;color:#1f2937;border:1px solid #d1d5db;padding:4px 10px;border-radius:4px;">JUN</span>
                                    <span style="font-size:12px;font-weight:600;color:#1f2937;border:1px solid #d1d5db;padding:4px 10px;border-radius:4px;">2020-21</span>
                                </div>
                            </div>
                            <div id="ytd_cards_grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;padding-bottom:20px;"></div>
                        </div>
                    </div>

                    <div class="p-6 flex items-center gap-3">
                        <button type="button" onclick="addResultArea()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition"
                            style="background-color:#287854 !important; color:white !important; border:none; cursor:pointer;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Result Area
                        </button>
                        <button type="button" onclick="showKpiPreview()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition"
                            style="background-color:#1f5f46 !important; color:white !important; border:none; cursor:pointer;">
                            👀 Preview on Performance
                        </button>
                        @if($isSuperAdmin)
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-lg shadow transition"
                            style="background-color:#16a34a !important; color:white !important; border:none; cursor:pointer;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save KPI Template
                        </button>
                        @endif
                    </div>
                </form>
            </section>
        </div>

        {{-- Preview modal: a rough mockup of how this KPI table's indicators would look
             as motivational "Goals & KPI" cards on the Performance page — built from
             whatever is currently typed in the form, so Vida can sanity-check it without
             leaving this page. Purely a client-side preview; nothing here is saved. --}}
        <div id="kpi_preview_modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:1000;align-items:center;justify-content:center;padding:20px;" onclick="if(event.target===this) closeKpiPreview()">
            <div style="background:#f8fafc;border-radius:16px;max-width:640px;width:100%;max-height:85vh;overflow-y:auto;padding:24px;position:relative;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <button type="button" onclick="closeKpiPreview()" title="Close"
                    style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:20px;line-height:1;cursor:pointer;color:#6b7280;">✕</button>
                <h3 style="margin:0 0 4px;font-size:1.05rem;font-weight:800;color:#1f5f46;">🎯 Preview: how this will look on Performance</h3>
                <p style="margin:0 0 18px;font-size:0.78rem;color:#6b7280;">A rough mockup based on what you're currently filling in on this form. Types that can be auto-calculated (Number/Currency/Percentage) get a progress bar; Text type is just shown as-is.</p>
                <div id="kpi_preview_cards" style="display:flex;flex-direction:column;gap:12px;"></div>
            </div>
        </div>
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
        var section = document.getElementById('kpi_template_section');
        var placeholder = document.getElementById('kpi_placeholder');
        var tbody = document.getElementById('kpi_table_body');
        var title = document.getElementById('kpi_table_title');
        var ytdSection = document.getElementById('ytd_dashboard_section');
        var ytdGrid = document.getElementById('ytd_cards_grid');
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

        function renderYTD(dashData) {
            ytdGrid.innerHTML = '';
            if (!dashData || !dashData.length) {
                ytdSection.style.display = 'none';
                return;
            }
            ytdSection.style.display = 'block';

            dashData.forEach(function(card) {
                var barColor = card.color === 'teal' ? '#14b8a6' : card.color === 'gold' ? '#eab308' : card.color === 'red' ? '#ef4444' : '#d1d5db';
                var valColor = card.color === 'teal' ? '#14b8a6' : card.color === 'gold' ? '#eab308' : card.color === 'red' ? '#ef4444' : '#6b7280';
                var yoyClass = card.yoy_dir === 'down' ? 'yoy-down' : 'yoy-up';
                var yoyArrow = card.yoy_dir === 'down' ? '↓' : card.yoy_dir === 'up' ? '↑' : '';

                var html = '<div class="ytd-card">';
                html += '<h4>' + card.title + '</h4>';
                html += '<p class="ytd-val" style="color:' + valColor + ';">' + card.value + '</p>';
                var tLabel = card.target_label || 'Target';
                html += '<div class="ytd-row"><span>' + tLabel + '</span><span style="font-weight:600;">' + card.target + '</span></div>';
                html += '<div class="ytd-row"><span>Last Year</span><span>' + card.last_year + '</span></div>';
                html += '<div class="ytd-bar" style="background:' + barColor + ';"></div>';
                html += '<div style="text-align:center;">' + createGaugeSVG(Math.min(card.percent, 200) > 100 ? 100 : card.percent, card.color) + '</div>';
                if (card.yoy) {
                    html += '<div class="yoy-tag ' + yoyClass + '" style="text-align:center;"><small>YoY</small> <strong>' + card.yoy + '</strong> ' + yoyArrow + '</div>';
                }
                html += '</div>';
                ytdGrid.innerHTML += html;
            });
        }

        function addAreaToTable(area) {
            areaCounter++;
            var areaIdx = areaCounter - 1;
            var indicators = area.indicators || [{ kpi: '', weight: '', target: '' }];

            indicators.forEach(function(ind, indIdx) {
                var tr = document.createElement('tr');
                tr.style.borderBottom = '1px solid #e5e7eb';

                if (indIdx === 0) {
                    var tdNo = document.createElement('td');
                    tdNo.style.cssText = 'padding:8px 12px;text-align:center;border:1px solid #e5e7eb;vertical-align:top;font-weight:700;color:#374151;';
                    tdNo.textContent = areaCounter;
                    tdNo.rowSpan = indicators.length;
                    tr.appendChild(tdNo);

                    var tdArea = document.createElement('td');
                    tdArea.style.cssText = 'padding:8px 12px;border:1px solid #e5e7eb;vertical-align:top;';
                    tdArea.rowSpan = indicators.length;
                    var areaInput = document.createElement('input');
                    areaInput.type = 'text'; areaInput.name = 'kpi_data[' + areaIdx + '][key_result_area]'; areaInput.value = area.key_result_area || '';
                    areaInput.placeholder = 'e.g. Recruitment';
                    if (areaInput.value) areaInput.title = areaInput.value;
                    areaInput.addEventListener('input', function() { areaInput.title = areaInput.value; });
                    areaInput.style.cssText = 'width:100%;border:1px solid #e5e7eb;border-radius:4px;padding:4px 8px;font-size:13px;font-weight:600;';
                    tdArea.appendChild(areaInput);
                    tr.appendChild(tdArea);

                    var hiddenNo = document.createElement('input'); hiddenNo.type = 'hidden'; hiddenNo.name = 'kpi_data[' + areaIdx + '][no]'; hiddenNo.value = areaCounter;
                    tr.appendChild(hiddenNo);
                }

                var fields = [
                    { key: 'kpi', ph: 'Key Performance Indicator', w: '100%', cell: 'padding:8px 12px;border:1px solid #e5e7eb;' },
                    { key: 'weight', ph: '-', w: '60px', cell: 'padding:8px 12px;text-align:center;border:1px solid #e5e7eb;' },
                    { key: 'target', ph: 'Target', w: '130px', cell: 'padding:8px 12px;text-align:center;border:1px solid #e5e7eb;' },
                    { key: 'actual', ph: '-', w: '100px', cell: 'padding:8px 12px;text-align:center;border:1px solid #e5e7eb;background:#fff7ed;' },
                    { key: 'score', ph: '-', w: '60px', cell: 'padding:8px 12px;text-align:center;border:1px solid #e5e7eb;' },
                    { key: 'final_score', ph: '-', w: '60px', cell: 'padding:8px 12px;text-align:center;border:1px solid #e5e7eb;' }
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
                        targetInput.style.cssText = 'flex:1;min-width:0;border:1px solid #e5e7eb;border-radius:4px;padding:4px 6px;font-size:13px;text-align:center;';
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
                        td.appendChild(wrap); tr.appendChild(td);
                        return;
                    }

                    var input = document.createElement('input'); input.type = 'text';
                    input.name = 'kpi_data[' + areaIdx + '][indicators][' + indIdx + '][' + f.key + ']';
                    input.value = ind[f.key] || ''; input.placeholder = f.ph;
                    // title = full-text tooltip on hover, so a value longer than the box (long
                    // KPI/target text) is still readable without needing to click into the field.
                    if (input.value) input.title = input.value;
                    input.addEventListener('input', function() { input.title = input.value; });
                    input.style.cssText = 'width:' + f.w + ';border:1px solid #e5e7eb;border-radius:4px;padding:4px 6px;font-size:13px;text-align:' + (f.key === 'kpi' ? 'left' : 'center') + ';' + (f.key === 'actual' ? 'font-weight:700;border-color:#fed7aa;' : '');
                    td.appendChild(input); tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });
        }

        function renderTable(kpiData) {
            tbody.innerHTML = '';
            areaCounter = 0;

            var areas = [];
            var ytdData = null;

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
                ytdData = kpiData.ytd_dashboard || null;
            }

            if (areas.length === 0) areas = [{ no: 1, key_result_area: '', indicators: [{ kpi: '', weight: '', target: '' }] }];
            areas.forEach(function(area) { addAreaToTable(area); });

            renderYTD(ytdData);
            updateWeightTotal();
        }

        window.addResultArea = function() {
            addAreaToTable({ no: areaCounter + 1, key_result_area: '', indicators: [{ kpi: '', weight: '', target: '' }, { kpi: '', weight: '', target: '' }] });
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

        divSelect.addEventListener('change', function() {
            var divId = parseInt(this.value);
            subSelect.innerHTML = '<option value="">Select sub-division</option>';
            if (!divId) { renderPositionsForSelection(); loadKpiData(); return; }
            subDivisions.forEach(function(sd) {
                if (parseInt(sd.division_id) === divId) {
                    var opt = document.createElement('option'); opt.value = sd.id; opt.textContent = sd.name; subSelect.appendChild(opt);
                }
            });
            renderPositionsForSelection();
            loadKpiData();
        });
        subSelect.addEventListener('change', function() { renderPositionsForSelection(); loadKpiData(); });
        posSelect.addEventListener('change', loadKpiData);

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

        function renderAreaCard(group) {
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

            var html = '<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 18px;">';
            html += '<div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;flex-wrap:wrap;">';
            html += '<div style="flex-shrink:0;">' + createGaugeSVG(areaPct === null ? 0 : areaPct, gaugeColor) + '</div>';
            html += '<div style="flex:1;min-width:160px;">';
            html += '<h4 style="margin:0 0 2px;font-size:0.95rem;font-weight:800;color:#1b4332;">' + escapeHtml(group.area) + '</h4>';
            html += '<div style="font-size:0.72rem;color:#9ca3af;">' + (areaPct === null ? 'Not enough data to calculate yet' : 'Weighted average across this area') + '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;border-top:1px solid #f3f4f6;padding-top:10px;">';
            html += results.map(renderIndicatorRow).join('');
            html += '</div>';
            html += '</div>';
            return html;
        }

        window.showKpiPreview = function() {
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

            // Group indicators by area, preserving the order areas first appear in.
            var groups = [];
            var groupIndex = {};
            rows.forEach(function(r) {
                if (!(r.area in groupIndex)) { groupIndex[r.area] = groups.length; groups.push({ area: r.area, rows: [] }); }
                groups[groupIndex[r.area]].rows.push(r);
            });

            var container = document.getElementById('kpi_preview_cards');
            container.innerHTML = groups.length
                ? groups.map(renderAreaCard).join('')
                : '<p style="color:#9ca3af;font-size:0.85rem;text-align:center;padding:20px;">No indicators filled in yet.</p>';

            document.getElementById('kpi_preview_modal').style.display = 'flex';
        };

        window.closeKpiPreview = function() {
            document.getElementById('kpi_preview_modal').style.display = 'none';
        };

        window.loadTemplate = function(divId, subDivId, posId) {
            isDuplicating = false; // Edit always loads/overwrites from the real saved template
            divSelect.value = divId; divSelect.dispatchEvent(new Event('change'));
            setTimeout(function() {
                if (subDivId) subSelect.value = subDivId;
                renderPositionsForSelection();
                if (posId) posSelect.value = posId;
                loadKpiData();
            }, 200);
        };
    })();
    </script>
@endsection