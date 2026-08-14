{{--
    Shared "Goals & KPI" cards — one per Key Result Area, each with a weighted
    gauge diagram plus a progress bar per indicator underneath. Included from
    both the Performance page (admin/performance/index.blade.php, HR block)
    and the Employee Profile KPI tab (admin/linkers-hub/employee-profile.blade.php)
    so the two never drift out of sync — they both just render whatever
    KpiTemplate::goalGroups() computed from the KPI builder page's data.

    Expects: $groups — the array returned by KpiTemplate::goalGroups(), or [].

    Save this file to: resources/views/admin/kpi/goals-cards.blade.php
    Include with: @include('admin.kpi.goals-cards', ['groups' => $kpiGoalGroups ?? []])
--}}
@if(empty($groups))
    <p style="color:#9ca3af;font-size:0.85rem;text-align:center;padding:20px 0;">No KPI targets have been set for this position yet.</p>
@else
    <div style="display:flex;flex-direction:column;gap:14px;">
        @foreach($groups as $group)
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px 18px;">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px;flex-wrap:wrap;">
                    <div style="flex-shrink:0;">{!! $group['area_gauge_svg'] !!}</div>
                    <div style="flex:1;min-width:160px;">
                        <h4 style="margin:0 0 2px;font-size:0.95rem;font-weight:800;color:#1b4332;">{{ $group['area'] }}</h4>
                        <div style="font-size:0.72rem;color:#9ca3af;">
                            {{ $group['area_pct'] === null ? 'Not enough data to calculate yet' : 'Weighted average across this area' }}
                        </div>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;border-top:1px solid #f3f4f6;padding-top:10px;">
                    @foreach($group['indicators'] as $ind)
                        <div>
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:6px;">
                                <div style="font-size:0.82rem;font-weight:600;color:#1b4332;line-height:1.4;flex:1;">
                                    <span style="margin-right:6px;">{{ $ind['status']['emoji'] }}</span>{{ $ind['label'] ?: '(untitled indicator)' }}
                                    @if($ind['weight'] !== null)
                                        <span style="font-size:0.7rem;color:#9ca3af;font-weight:400;"> · weight {{ rtrim(rtrim(number_format($ind['weight'], 2), '0'), '.') }}%</span>
                                    @endif
                                </div>
                                <span style="font-size:0.66rem;padding:3px 9px;border-radius:20px;font-weight:600;white-space:nowrap;background:{{ $ind['status']['bg'] }};color:{{ $ind['status']['txt'] }};">
                                    {{ $ind['status']['label'] }}
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="flex:1;height:7px;background:#f3f4f6;border-radius:4px;overflow:hidden;">
                                    <div style="width:{{ $ind['pct'] ?? 0 }}%;height:100%;background:{{ $ind['status']['color'] }};border-radius:4px;"></div>
                                </div>
                                <span style="font-size:0.75rem;font-weight:700;min-width:32px;text-align:right;color:{{ $ind['status']['color'] }};">
                                    {{ $ind['pct'] === null ? '—' : $ind['pct'] . '%' }}
                                </span>
                            </div>
                            <div style="font-size:0.68rem;color:#6b7280;margin-top:2px;">{{ $ind['actual'] ?: '—' }} / {{ $ind['target'] ?: '—' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif
