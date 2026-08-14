@extends('admin.layout')

@section('title', 'KPIs and Job Descriptions')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">KPIs and Job Descriptions</h1>
        <a href="{{ route('admin.kpi-jd.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg shadow hover:bg-green-700 transition" style="background-color:#16a34a !important; color:white !important;">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Generate Job Description
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 text-sm text-green-800 bg-green-100 border border-green-200 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 text-sm text-red-800 bg-red-100 border border-red-200 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Total Records Card --}}
    <div class="bg-gradient-to-r from-green-700 to-green-600 rounded-xl shadow-lg p-6 text-white" style="background:linear-gradient(to right,#15803d,#16a34a) !important; color:white !important; padding:24px; border-radius:12px">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-green-100 uppercase tracking-wide" style="color:rgba(220,252,231,1) !important; font-size:14px">Total Records to Date</p>
                <p class="text-4xl font-bold mt-1" style="color:white !important; font-size:36px; margin-top:4px">{{ $totalCount }}</p>
            </div>
            <div class="bg-white/20 rounded-full p-4" style="background:rgba(255,255,255,0.2) !important; border-radius:9999px; padding:16px">
                <svg class="w-8 h-8 text-white" style="width:32px;height:32px;color:white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
    </div>

    {{-- Status Tabs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Status Tabs">
                @php
                    $tabs = [
                        'all' => ['label' => 'All Records', 'count' => $totalCount, 'color' => 'gray'],
                        'pending_approval' => ['label' => 'Pending Approval', 'count' => $pendingCount, 'color' => 'yellow'],
                        'approved' => ['label' => 'Approved', 'count' => $approvedCount, 'color' => 'green'],
                        'rejected' => ['label' => 'Rejected', 'count' => $rejectedCount, 'color' => 'red'],
                    ];
                @endphp
                @foreach ($tabs as $key => $tab)
                    <a href="{{ route('admin.kpi-jd.index', ['status' => $key]) }}"
                       class="flex-1 text-center px-4 py-4 text-sm font-medium border-b-2 transition
                              {{ $statusFilter === $key
                                  ? 'border-green-600 text-green-700 bg-green-50'
                                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $tab['label'] }}
                        <span class="ml-2 inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                            @if($tab['color']==='yellow') bg-yellow-100 text-yellow-800
                            @elseif($tab['color']==='green') bg-green-100 text-green-800
                            @elseif($tab['color']==='red') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-gray-50 border-b border-gray-200">
            <div class="bg-white rounded-lg p-4 border border-gray-100 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase">Total Records</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalCount }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-100 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase">Pending Review</p>
                <p class="text-2xl font-bold text-gray-800">{{ $pendingCount }}</p>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-100 shadow-sm">
                <p class="text-xs font-medium text-gray-500 uppercase">Approved</p>
                <p class="text-2xl font-bold text-gray-800">{{ $approvedCount }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3">Contract No.</th>
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Position</th>
                        <th class="px-4 py-3">Start Date</th>
                        <th class="px-4 py-3">Job Description</th>
                        <th class="px-4 py-3">KPI</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($records as $record)
                        @php
                            $overallStatus = 'pending_approval';
                            if ($record->kpi_status === 'approved' && $record->jd_status === 'approved') $overallStatus = 'approved';
                            elseif ($record->kpi_status === 'rejected' || $record->jd_status === 'rejected') $overallStatus = 'rejected';
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $record->contract_number }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $record->employee_name }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $record->position_title }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">
                                {{ $record->start_date?->format('d M Y') }}
                            </td>

                            {{-- Job Description Button --}}
                            <td class="px-4 py-3">
                                <a href="#"
                                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white rounded hover:opacity-90 transition" style="background-color:#15803d !important; color:white !important; min-height:38px; min-width:100px;">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    View JD
                                </a>
                            </td>

                            {{-- KPI Button --}}
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.kpi-jd.kpi', $record) }}"
                                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white rounded hover:opacity-90 transition" style="background-color:#6d28d9 !important; color:white !important; min-height:38px; min-width:100px;">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    View KPI
                                </a>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3">
                                @if($overallStatus === 'pending_approval')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending Approval</span>
                                @elseif($overallStatus === 'approved')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                @elseif($overallStatus === 'rejected')
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                @endif
                            </td>

                            {{-- Actions (same style as Contracts) --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-2">
                                    @if($overallStatus === 'pending_approval')
                                        {{-- Row 1: Approve | Reject -- SUPER ADMIN ONLY --}}
                                        @if($isSuperAdmin)
                                        <div class="flex items-center gap-2">
                                            @if($record->kpi_status === 'pending_approval')
                                            <form method="POST" action="{{ route('admin.kpi-jd.approve-kpi', $record) }}" class="m-0">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white border border-green-600 rounded transition" style="background-color:#16a34a !important; color:white !important; min-height:38px; min-width:120px;" onclick="return confirm('Approve KPI?')">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Approve KPI
                                                </button>
                                            </form>
                                            @endif
                                            @if($record->jd_status === 'pending_approval')
                                            <form method="POST" action="{{ route('admin.kpi-jd.approve-jd', $record) }}" class="m-0">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white border border-green-600 rounded transition" style="background-color:#16a34a !important; color:white !important; min-height:38px; min-width:120px;" onclick="return confirm('Approve Job Description?')">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Approve JD
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($record->kpi_status === 'pending_approval')
                                            <form method="POST" action="{{ route('admin.kpi-jd.reject-kpi', $record) }}" class="m-0">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white rounded transition" style="background-color:#f97316 !important; color:white !important; min-height:38px; min-width:120px;" onclick="return confirm('Reject KPI?')">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Reject KPI
                                                </button>
                                            </form>
                                            @endif
                                            @if($record->jd_status === 'pending_approval')
                                            <form method="POST" action="{{ route('admin.kpi-jd.reject-jd', $record) }}" class="m-0">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white rounded transition" style="background-color:#f97316 !important; color:white !important; min-height:38px; min-width:120px;" onclick="return confirm('Reject Job Description?')">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Reject JD
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('admin.kpi-jd.destroy', $record) }}" class="m-0" onsubmit="return confirm('DELETE this record permanently?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white rounded transition" style="background-color:#dc2626 !important; color:white !important; min-height:38px; min-width:120px;">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                            <span class="text-xs text-gray-400">Awaiting admin action</span>
                                        @endif

                                    @elseif($overallStatus === 'approved')
                                        <div class="flex items-center gap-2">
                                            <div class="flex flex-col items-center justify-center p-2 rounded bg-green-50 border border-green-100 min-h-[38px] min-w-[120px]">
                                                <div class="text-sm font-medium text-green-700 flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Approved
                                                </div>
                                            </div>
                                            @if($isSuperAdmin)
                                            <form method="POST" action="{{ route('admin.kpi-jd.destroy', $record) }}" class="m-0" onsubmit="return confirm('DELETE this record permanently?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white rounded transition" style="background-color:#dc2626 !important; color:white !important; min-height:38px; min-width:120px;">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Delete
                                                </button>
                                            </form>
                                            @endif
                                        </div>

                                    @elseif($overallStatus === 'rejected')
                                        <div class="flex items-center gap-2">
                                            <div class="flex flex-col items-center justify-center p-2 rounded bg-red-50 border border-red-100 min-h-[38px] min-w-[120px]">
                                                <div class="text-sm font-medium text-red-700 flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Rejected
                                                </div>
                                            </div>
                                            @if($isSuperAdmin)
                                            <form method="POST" action="{{ route('admin.kpi-jd.destroy', $record) }}" class="m-0" onsubmit="return confirm('DELETE this record permanently?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white rounded transition" style="background-color:#dc2626 !important; color:white !important; min-height:38px; min-width:120px;">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Delete
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                <p class="mt-4 text-sm">No records found. Records are auto-created when contracts are generated.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection