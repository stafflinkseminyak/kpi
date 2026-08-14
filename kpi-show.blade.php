@extends('admin.layout')
@section('title', 'KPIs for ' . $record->position_title)
@section('content')
<div style="max-width:1200px;margin:0 auto;padding:2rem;">

    {{-- Back button --}}
    <a href="{{ route('admin.kpi-jd.index') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-lg shadow hover:opacity-90 transition"
       style="background-color:#1f5f46 !important; color:white !important; margin-bottom:1.5rem; text-decoration:none;">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to KPIs and Job Descriptions
    </a>

    {{-- Title --}}
    <h1 style="font-size:1.8rem;font-weight:800;color:#1f2937;margin-bottom:0.5rem;">KPIs for {{ $record->position_title }}</h1>
    <p style="font-size:14px;color:#6b7280;margin-bottom:0.5rem;">
        Employee: <strong style="color:#1f2937;">{{ $record->employee_name }}</strong> &bull;
        Contract: <strong style="color:#1f2937;">{{ $record->contract_number }}</strong> &bull;
        Start: <strong style="color:#1f2937;">{{ $record->start_date?->format('d M Y') }}</strong>
    </p>

    {{-- Status bar --}}
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:2rem;">
        <span style="display:inline-block;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;
            @if($record->kpi_status === 'approved') background:#d1fae5;color:#065f46;
            @elseif($record->kpi_status === 'rejected') background:#fee2e2;color:#991b1b;
            @else background:#fef3c7;color:#92400e;
            @endif">
            KPI Status: {{ $record->kpi_status === 'pending_approval' ? 'Pending Approval' : ucfirst($record->kpi_status) }}
        </span>
        @if($isSuperAdmin && $record->kpi_status === 'pending_approval')
        <form method="POST" action="{{ route('admin.kpi-jd.approve-kpi', $record) }}" style="display:inline;">
            @csrf
            <button type="submit" style="padding:6px 16px;border-radius:6px;font-size:12px;font-weight:700;background:#1f5f46;color:#fff;border:none;cursor:pointer;">Approve</button>
        </form>
        <form method="POST" action="{{ route('admin.kpi-jd.reject-kpi', $record) }}" style="display:inline;">
            @csrf
            <button type="submit" style="padding:6px 16px;border-radius:6px;font-size:12px;font-weight:700;background:#dc2626;color:#fff;border:none;cursor:pointer;">Reject</button>
        </form>
        @endif
    </div>

    {{-- Quote --}}
    <div style="background:#f0fdf4;border-left:4px solid #1f5f46;padding:1rem 1.5rem;border-radius:0 8px 8px 0;margin-bottom:2.5rem;">
        <p style="font-size:14px;color:#374151;font-style:italic;line-height:1.6;">
            "Your monthly performance will be analyzed based on multiple KPIs. You are required to keep a record of the following things in an excel sheet and send the same to your reporting manager at the end of every month."
        </p>
    </div>

    {{-- KPI Infographic Layout --}}
    <div style="display:flex;gap:3rem;align-items:flex-start;">

        {{-- Left: Purple Circle --}}
        <div style="flex-shrink:0;position:relative;">
            <div style="width:200px;height:200px;border-radius:50%;background:linear-gradient(135deg,#6d28d9 0%,#4c1d95 100%);display:flex;align-items:center;justify-content:center;box-shadow:0 8px 30px rgba(109,40,217,0.3);">
                <span style="color:#fff;font-size:2.5rem;font-weight:900;letter-spacing:1px;">KPIs</span>
            </div>
            {{-- Connecting line --}}
            <div style="position:absolute;top:100px;left:200px;width:60px;height:2px;background:#d1d5db;"></div>
        </div>

        {{-- Right: KPI Categories --}}
        <div style="flex:1;">
            <form method="POST" action="{{ route('admin.kpi-jd.kpi.save', $record) }}">
                @csrf

                @php
                    $categories = [
                        'meetings_done' => ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'color' => '#1f5f46', 'bg' => '#d1fae5'],
                        'calls_made' => ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'color' => '#1f5f46', 'bg' => '#d1fae5'],
                        'mails_sent' => ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => '#6d28d9', 'bg' => '#ede9fe'],
                        'connects_made' => ['icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', 'color' => '#1f5f46', 'bg' => '#d1fae5'],
                        'conversion_rate' => ['icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#6d28d9', 'bg' => '#ede9fe'],
                    ];
                    $idx = 0;
                @endphp

                @foreach ($categories as $catKey => $cat)
                @php
                    $catData = $kpiData[$catKey] ?? ['title' => ucwords(str_replace('_', ' ', $catKey)), 'targets' => ['', '']];
                    $idx++;
                @endphp
                <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:2rem;position:relative;padding-left:20px;">
                    {{-- Connector dot --}}
                    <div style="position:absolute;left:-6px;top:18px;width:12px;height:12px;border-radius:50%;background:{{ $cat['color'] }};border:3px solid #fff;box-shadow:0 0 0 2px {{ $cat['color'] }};z-index:2;"></div>
                    @if($idx < 5)
                    <div style="position:absolute;left:-1px;top:30px;width:2px;height:calc(100% + 16px);background:#e5e7eb;z-index:1;"></div>
                    @endif

                    {{-- Icon circle --}}
                    <div style="width:48px;height:48px;border-radius:50%;background:{{ $cat['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid {{ $cat['color'] }};">
                        <svg width="22" height="22" fill="none" stroke="{{ $cat['color'] }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cat['icon'] }}"/></svg>
                    </div>

                    {{-- Content --}}
                    <div style="flex:1;">
                        <h3 style="font-size:16px;font-weight:800;color:#1f2937;margin-bottom:6px;">{{ $catData['title'] }}</h3>
                        @foreach ($catData['targets'] as $tIdx => $target)
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                            <span style="color:{{ $cat['color'] }};font-size:16px;font-weight:700;">&rsaquo;</span>
                            @if($isSuperAdmin)
                            <input type="text" name="kpi_data[{{ $catKey }}][targets][{{ $tIdx }}]" value="{{ $target }}"
                                   style="flex:1;border:1px solid #e5e7eb;border-radius:6px;padding:6px 12px;font-size:13px;color:#374151;outline:none;transition:border .15s;"
                                   onfocus="this.style.borderColor='#1f5f46'" onblur="this.style.borderColor='#e5e7eb'"
                                   placeholder="Add target here...">
                            @else
                            <span style="font-size:13px;color:#6b7280;">{{ $target ?: 'Add text here' }}</span>
                            @endif
                        </div>
                        @endforeach
                        <input type="hidden" name="kpi_data[{{ $catKey }}][title]" value="{{ $catData['title'] }}">
                    </div>
                </div>
                @endforeach

                <div style="margin-top:1.5rem;padding-left:20px;display:flex;gap:12px;align-items:center;">
                    @if($isSuperAdmin)
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-lg shadow transition"
                            style="background-color:#16a34a !important; color:white !important; border:none; cursor:pointer; min-height:42px;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save KPI Targets
                    </button>
                    @endif
                    <a href="{{ route('admin.kpi-jd.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-lg shadow transition"
                       style="background-color:#6b7280 !important; color:white !important; text-decoration:none; min-height:42px;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Back to List
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var msg = document.createElement('div');
        msg.innerHTML = '{{ session("success") }}';
        msg.style.cssText = 'position:fixed;top:20px;right:20px;background:#1f5f46;color:#fff;padding:16px 24px;border-radius:10px;font-weight:600;font-size:14px;z-index:9999;box-shadow:0 4px 15px rgba(0,0,0,0.2);';
        document.body.appendChild(msg);
        setTimeout(function() { msg.remove(); }, 4000);
    });
</script>
@endif
@endsection