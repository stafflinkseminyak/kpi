{{--
    YTD Dashboard cards — a separate, mostly-static company-wide operational
    scorecard (Employee Expense, Revenue Per Employee, Full/Part Time
    Employees, Training Hours, etc.). Originally built into the KPI builder
    page; moved here per request — visible on Performance to super_admin only.

    It has nothing to do with the per-employee weighted "Goals & KPI" section
    (goals-cards.blade.php / KpiTemplate::goalGroups()) — these cards come
    straight from whatever is stored in the resolved KpiTemplate's
    kpi_data['ytd_dashboard'], with a couple of cards (Full/Part Time
    Employees, Revenue Per Employee) refreshed with live figures by the
    controller before this partial ever sees them.

    Expects: $cards — the array from AdminController::performance()'s
    $ytdDashboardCards, or [].

    Save this file to: resources/views/admin/kpi/ytd-dashboard-cards.blade.php
    Include with: @include('admin.kpi.ytd-dashboard-cards', ['cards' => $ytdDashboardCards ?? []])
--}}
@if(!empty($cards))
    <style>
        .ytd-card { border:1px solid #e5e7eb; border-radius:8px; padding:14px; background:#fff; }
        .ytd-card h4 { font-size:13px; font-weight:700; color:#1f2937; margin:0 0 4px 0; text-decoration:underline; }
        .ytd-val { font-size:18px; font-weight:700; margin:0; }
        .ytd-row { display:flex; justify-content:space-between; font-size:12px; color:#6b7280; margin:2px 0; }
        .ytd-bar { height:4px; border-radius:2px; margin:8px 0 10px 0; }
        .yoy-tag { font-size:10px; margin-top:2px; }
        .yoy-down { color:#ef4444; }
        .yoy-up { color:#22c55e; }
    </style>
    <div style="background:#fff; border:1px solid #e5e7eb; border-top:4px solid #1f5f46; border-radius:12px; padding:22px 24px; margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div style="display:flex;align-items:center;">
                <div style="width:28px; height:28px; border-radius:8px; background:#eaf3de; display:flex; align-items:center; justify-content:center; margin-right:10px; flex-shrink:0;">
                    <svg style="width:15px; height:15px; color:#1f5f46;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <span style="font-size:1rem; font-weight:800; color:#1b4332;">YTD Dashboard</span>
            </div>
            <span style="font-size:0.7rem; font-weight:600; color:#9ca3af; border:1px solid #e5e7eb; padding:3px 10px; border-radius:20px;">Visible to super_admin only</span>
        </div>
        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px;">
            @foreach($cards as $card)
                @php
                    $barColor = ($card['color'] ?? null) === 'teal' ? '#14b8a6' : (($card['color'] ?? null) === 'gold' ? '#eab308' : (($card['color'] ?? null) === 'red' ? '#ef4444' : '#d1d5db'));
                    $valColor = ($card['color'] ?? null) === 'teal' ? '#14b8a6' : (($card['color'] ?? null) === 'gold' ? '#eab308' : (($card['color'] ?? null) === 'red' ? '#ef4444' : '#6b7280'));
                    $gaugeColor = ($card['color'] ?? null) === 'teal' ? '#14b8a6' : (($card['color'] ?? null) === 'gold' ? '#eab308' : (($card['color'] ?? null) === 'red' ? '#ef4444' : '#9ca3af'));
                    $percent = min((int) ($card['percent'] ?? 0), 100);
                @endphp
                <div class="ytd-card">
                    <h4>{{ $card['title'] ?? '' }}</h4>
                    <p class="ytd-val" style="color:{{ $valColor }};">{{ $card['value'] ?? '—' }}</p>
                    <div class="ytd-row"><span>{{ $card['target_label'] ?? 'Target' }}</span><span style="font-weight:600;">{{ $card['target'] ?? '—' }}</span></div>
                    <div class="ytd-row"><span>Last Year</span><span>{{ $card['last_year'] ?? '—' }}</span></div>
                    <div class="ytd-bar" style="background:{{ $barColor }};"></div>
                    <div style="text-align:center;">{!! \App\Models\KpiTemplate::gaugeSvg($percent, $gaugeColor) !!}</div>
                    @if(!empty($card['yoy']))
                        <div class="yoy-tag {{ ($card['yoy_dir'] ?? null) === 'down' ? 'yoy-down' : 'yoy-up' }}" style="text-align:center;">
                            <small>YoY</small> <strong>{{ $card['yoy'] }}</strong> {{ ($card['yoy_dir'] ?? null) === 'down' ? '↓' : (($card['yoy_dir'] ?? null) === 'up' ? '↑' : '') }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
