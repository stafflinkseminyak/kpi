@extends('admin.layout')

@section('page-title', 'Job Descriptions')
@section('title', 'Job Descriptions')

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            {{-- Left Column: Job Description Data Box --}}
            <section class="bg-white rounded-lg shadow border border-gray-100">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Job Description Data</h3>
                    <p class="text-sm text-gray-500 mt-1">View and manage employee job descriptions linked to contracts.</p>
                </div>
                <div class="p-6 border-b bg-gray-50/40">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                        <div class="md:col-span-6">
                            <label for="jd_division_select" class="block text-sm font-medium text-gray-700 mb-2">Division</label>
                            <select id="jd_division_select"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#287854] focus:border-transparent bg-white">
                                <option value="">Select division</option>
                                @foreach ($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-6">
                            <label for="jd_sub_division_select" class="block text-sm font-medium text-gray-700 mb-2">Sub-Division</label>
                            <select id="jd_sub_division_select"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#287854] focus:border-transparent bg-white">
                                <option value="">Select sub-division</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#e6f1ec]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contract</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($records as $record)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $record->employee_name }}</div>
                                    <div class="text-xs text-gray-400">{{ $record->start_date?->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $record->position_title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $record->contract_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($record->jd_status === 'approved')
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                    @elseif($record->jd_status === 'rejected')
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="#"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 text-[13px] font-medium text-white rounded transition"
                                       style="background-color:#15803d !important; color:white !important;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        View JD
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <p class="text-sm">No Job Description records found. Records are auto-created when contracts are generated.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script>
    (function() {
        var subDivisions = @json($subDivisions);
        var divSelect = document.getElementById('jd_division_select');
        var subSelect = document.getElementById('jd_sub_division_select');

        divSelect?.addEventListener('change', function() {
            var divId = parseInt(this.value);
            subSelect.innerHTML = '<option value="">Select sub-division</option>';
            if (!divId) return;
            subDivisions.forEach(function(sd) {
                if (parseInt(sd.division_id) === divId) {
                    var opt = document.createElement('option');
                    opt.value = sd.id;
                    opt.textContent = sd.name;
                    subSelect.appendChild(opt);
                }
            });
        });
    })();
    </script>
@endsection