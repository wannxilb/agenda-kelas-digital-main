@extends('layouts.wakasek')

@section('title', 'Laporan Presensi')
@section('header', 'Laporan Presensi')

@push('styles')
<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

    /* Bottom-sheet modal on mobile/tablet, centered dialog on desktop */
    @media (max-width: 1023px) {
        #attendanceModal .sheet-panel {
            position: fixed;
            left: 0; right: 0; bottom: 0;
            width: 100%;
            max-width: 100%;
            max-height: 88vh;
            border-radius: 1.5rem 1.5rem 0 0;
            transform: translateY(100%);
            transition: transform .32s cubic-bezier(.32,.72,0,1);
        }
        #attendanceModal.is-open .sheet-panel { transform: translateY(0); }
    }

    [x-cloak] { display: none !important; }

    .tap-active:active { transform: scale(0.97); }

    .chip-scroll::-webkit-scrollbar { display: none; }
    .chip-scroll { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
@php
    $hasFilter = request()->anyFilled(['academic_year_id', 'class_id', 'start_date', 'end_date']);
    $rangeLabel = request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->translatedFormat('d M') : 'Semua';
    $rangeLabel .= ' – ';
    $rangeLabel .= request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->translatedFormat('d M Y') : 'Sekarang';
    $statItems = [
        ['label' => 'Total', 'value' => $summary['total'], 'color' => 'text-gray-900', 'dot' => 'bg-gray-900'],
        ['label' => 'Hadir', 'value' => $summary['present'], 'color' => 'text-emerald-600', 'dot' => 'bg-emerald-500'],
        ['label' => 'Alpa', 'value' => $summary['absent'], 'color' => 'text-rose-600', 'dot' => 'bg-rose-500'],
        ['label' => 'Sakit', 'value' => $summary['sick'], 'color' => 'text-orange-600', 'dot' => 'bg-orange-500'],
        ['label' => 'Izin', 'value' => $summary['excused'], 'color' => 'text-sky-600', 'dot' => 'bg-sky-500'],
        ['label' => 'Telat', 'value' => $summary['late'], 'color' => 'text-amber-600', 'dot' => 'bg-amber-500'],
    ];
@endphp

<div class="space-y-6 sm:space-y-8 pb-8"
     x-data="{
        filterOpen: false,
        activeFilters: {{ collect(['academic_year_id','class_id','start_date','end_date'])->filter(fn($k) => request($k))->count() }}
     }"
     x-init="filterOpen = window.matchMedia('(min-width: 1024px)').matches">

    {{-- Header --}}
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-blue-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Laporan Presensi</h1>
                <p class="mt-2 text-xs sm:text-sm text-gray-500 font-medium max-w-2xl">Analisis data kehadiran siswa</p>
            </div>
            <div class="flex items-center gap-2.5 self-start md:self-auto">
                <a href="{{ route('wakasek.reports.export.pdf', request()->query()) }}" target="_blank" id="exportPdfLink"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-rose-50 text-rose-700 rounded-2xl border border-rose-100 text-xs font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export PDF
                </a>
                <a href="{{ route('wakasek.reports.export.excel', request()->query()) }}" id="exportExcelLink"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 text-xs font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Excel
                </a>
            </div>
        </div>
    </div>

    {{-- Quick range chips --}}
    <div class="flex gap-2 overflow-x-auto chip-scroll -mx-4 px-4 sm:mx-0 sm:px-0">
        <button type="button" data-range-chip="today" onclick="setQuickRange('today')" class="shrink-0 px-3.5 py-2 rounded-full bg-white border border-gray-200 text-[11px] font-bold text-gray-600 active:bg-blue-600 active:text-white active:border-blue-600 transition-all tap-active">Hari Ini</button>
        <button type="button" data-range-chip="week" onclick="setQuickRange('week')" class="shrink-0 px-3.5 py-2 rounded-full bg-white border border-gray-200 text-[11px] font-bold text-gray-600 active:bg-blue-600 active:text-white active:border-blue-600 transition-all tap-active">Minggu Ini</button>
        <button type="button" data-range-chip="month" onclick="setQuickRange('month')" class="shrink-0 px-3.5 py-2 rounded-full bg-white border border-gray-200 text-[11px] font-bold text-gray-600 active:bg-blue-600 active:text-white active:border-blue-600 transition-all tap-active">Bulan Ini</button>
        <a href="{{ route('wakasek.reports.attendance') }}" class="shrink-0 px-3.5 py-2 rounded-full bg-white border border-gray-200 text-[11px] font-bold text-gray-400 active:bg-gray-100 transition-all tap-active flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Reset
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Toggle bar --}}
        <button @click="filterOpen = !filterOpen"
                class="w-full px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between lg:pointer-events-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                     :class="filterOpen ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <div class="flex items-center gap-2">
                        <span class="text-xs sm:text-sm font-bold text-gray-800">Filter Pencarian</span>
                        <span x-show="activeFilters > 0" x-cloak class="w-5 h-5 rounded-full bg-blue-600 text-white text-[9px] font-black flex items-center justify-center" x-text="activeFilters"></span>
                    </div>
                    @if($hasFilter)
                        <p class="text-[10px] text-blue-600 font-semibold">{{ $rangeLabel }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($hasFilter)
                    <a href="{{ route('wakasek.reports.attendance') }}"
                       class="px-2 py-1 bg-red-50 text-red-500 rounded-lg text-[10px] font-bold"
                       onclick="event.stopPropagation()">Reset</a>
                @endif
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 lg:hidden" :class="{ 'rotate-180': filterOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        {{-- Expandable form --}}
        <div x-show="filterOpen" x-transition x-cloak class="lg:!block">
            <form id="attendanceFilterForm" method="GET" action="{{ route('wakasek.reports.attendance') }}"
                  class="px-4 pb-4 sm:px-6 sm:pb-5 border-t border-gray-50 pt-4">
                <input type="hidden" name="search" id="searchInputHidden" value="{{ request('search') }}">
                <div id="attendanceFilterLoading" class="hidden items-center gap-2 rounded-2xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 mb-3">
                    <div class="h-4 w-4 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
                    Memuat laporan...
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Periode Data</label>
                        <select name="academic_year_id" class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                            <option value="" {{ request('academic_year_id') ? '' : 'selected' }}>Semua periode</option>
                            @foreach($academicYears->groupBy('name') as $name => $years)
                                @php $ids = $years->pluck('id')->implode(','); @endphp
                                <option value="{{ $ids }}" {{ request('academic_year_id') == $ids ? 'selected' : '' }}>
                                    {{ $name }}{{ $years->contains('is_active', true) ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Pilih Kelas</label>
                        <select name="class_id" class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                            <option value="" {{ request('class_id') ? '' : 'selected' }}>Semua kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Tanggal Mulai</label>
                        <input id="startDateInput" type="date" name="start_date" value="{{ request('start_date') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Tanggal Akhir</label>
                        <input id="endDateInput" type="date" name="end_date" value="{{ request('end_date') }}"
                               class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                    </div>
                </div>
                <div class="flex justify-end pt-3">
                    <button id="attendanceFilterSubmit" type="submit"
                            class="w-full sm:w-auto px-6 sm:px-10 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-blue-700 transition-all flex items-center justify-center gap-2 tap-active disabled:cursor-wait disabled:bg-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Tampilkan Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div id="attendanceSummary" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 transition-all duration-300">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-50">
            <div class="flex items-center">
                <div class="w-1.5 h-5 bg-blue-600 rounded-full mr-3"></div>
                <h3 class="text-sm sm:text-base font-bold text-gray-900">Ringkasan</h3>
            </div>
            <span class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $rangeLabel }}</span>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            @foreach($statItems as $s)
                <div class="flex items-center gap-2.5 px-3 py-2.5 bg-gray-50/50 rounded-xl">
                    <span class="w-2 h-6 {{ $s['dot'] }} rounded-full flex-shrink-0"></span>
                    <div class="min-w-0">
                        <p class="text-[8px] sm:text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-0.5">{{ $s['label'] }}</p>
                        <p class="text-xs sm:text-sm font-black {{ $s['color'] }} leading-none">{{ $s['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Search (di luar container agar tidak ikut ter-render ulang saat mengetik) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-3 sm:p-4 lg:px-6 sm:px-8">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input id="studentSearch" type="text" oninput="scheduleSearchSubmit()" value="{{ request('search') }}" placeholder="Cari nama atau NIS siswa..."
                   class="block w-full pl-9 pr-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all text-sm font-semibold">
        </div>
    </div>

    {{-- Student List --}}
    <div id="attendanceStudents" class="lg:bg-white lg:rounded-2xl lg:shadow-sm lg:border lg:border-gray-100 lg:overflow-hidden transition-all duration-300">
        <div class="hidden lg:flex px-6 sm:px-8 py-5 sm:py-6 border-b border-gray-50 items-center justify-between bg-gray-50/30">
            <h3 class="text-sm sm:text-lg font-black text-gray-900">Data Presensi Siswa</h3>
            <span class="px-3 py-1.5 bg-white text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] rounded-xl border border-gray-100 shadow-sm">{{ $students->total() }} Siswa</span>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead>
                    <tr class="bg-gray-50/20">
                        <th class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa</th>
                        <th class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kelas</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Hadir</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alpa</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sakit</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Izin</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Telat</th>
                        <th class="px-8 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody" class="bg-white divide-y divide-gray-50">
                    @forelse($students as $student)
                    @php
                        $counts = $studentCounts[$student->id] ?? ['present' => 0, 'absent' => 0, 'sick' => 0, 'excused' => 0, 'late' => 0, 'total' => 0];
                        $present = $counts['present'];
                        $absent = $counts['absent'];
                        $sick = $counts['sick'];
                        $excused = $counts['excused'];
                        $late = $counts['late'];
                        $total = $counts['total'];
                        $compliance = $total > 0 ? round(($present / $total) * 100) : 0;
                        $historicalClass = $student->classHistories->first();
                        $displayClass = $historicalClass ? $historicalClass->class : $student->class;
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors group" data-name="{{ Str::lower($student->name) }}" data-nis="{{ $student->nis }}" data-compliance="{{ $compliance }}">
                        <td class="px-8 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white text-[10px] font-black shadow-sm mr-4 group-hover:scale-110 transition-transform">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-black text-gray-900 truncate">{{ $student->name }}</div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">NIS: {{ $student->nis }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <span class="px-3 py-1 text-[10px] font-bold bg-blue-50 text-blue-700 rounded-lg border border-blue-100 uppercase tracking-wider whitespace-nowrap">
                                {{ $displayClass->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-8 py-5 text-center text-sm font-black text-emerald-600">{{ $present }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-rose-600">{{ $absent }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-orange-600">{{ $sick }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-sky-600">{{ $excused }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-amber-600">{{ $late }}</td>
                        <td class="px-8 py-5 text-right whitespace-nowrap">
                            <button onclick="viewStudentAttendance({{ $student->id }}, @js($student->name))"
                                    class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-sm">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-400">Tidak ada data siswa ditemukan</p>
                                <p class="text-[10px] text-gray-300 mt-0.5">Sesuaikan filter untuk menampilkan data</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div id="studentCardList" class="lg:hidden space-y-2.5 mt-3">
            @forelse($students as $student)
            @php
                $counts = $studentCounts[$student->id] ?? ['present' => 0, 'absent' => 0, 'sick' => 0, 'excused' => 0, 'late' => 0, 'total' => 0];
                $present = $counts['present'];
                $absent = $counts['absent'];
                $sick = $counts['sick'];
                $excused = $counts['excused'];
                $late = $counts['late'];
                $total = $counts['total'];
                $compliance = $total > 0 ? round(($present / $total) * 100) : 0;
                $ring = $compliance >= 80 ? 'ring-emerald-400 text-emerald-700 bg-emerald-50' : ($compliance >= 50 ? 'ring-amber-400 text-amber-700 bg-amber-50' : 'ring-rose-400 text-rose-700 bg-rose-50');
                $barColor = $compliance >= 80 ? 'bg-emerald-500' : ($compliance >= 50 ? 'bg-amber-500' : 'bg-rose-500');
                $barLabel = $compliance >= 80 ? 'text-emerald-600' : ($compliance >= 50 ? 'text-amber-600' : 'text-rose-600');
                $statusLabel = $compliance >= 80 ? 'Baik' : ($compliance >= 50 ? 'Perlu Pantau' : 'Prioritas');
                $statusClass = $compliance >= 80 ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : ($compliance >= 50 ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-rose-50 text-rose-700 border-rose-100');
                $historicalClass = $student->classHistories->first();
                $displayClass = $historicalClass ? $historicalClass->class : $student->class;
            @endphp
            <div data-report-student class="student-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                 data-name="{{ Str::lower($student->name) }}" data-nis="{{ $student->nis }}" data-compliance="{{ $compliance }}"
                 x-data="{ expanded: false }">
                <button type="button" @click="expanded = !expanded"
                        class="w-full px-4 py-3.5 flex items-center gap-3 text-left active:bg-gray-50 transition-colors">
                    <div class="relative shrink-0">
                        <div class="w-11 h-11 bg-linear-to-br from-blue-500 to-indigo-600 text-white rounded-2xl flex items-center justify-center text-xs font-black shadow-lg shadow-blue-100">
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                        </div>
                        <span class="absolute -bottom-1 -right-1 min-w-[22px] h-[18px] px-1 rounded-full ring-2 ring-white {{ $ring }} text-[8px] font-black flex items-center justify-center">{{ $compliance }}%</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-black text-gray-900 truncate">{{ $student->name }}</p>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-md border border-blue-100/50 shrink-0">{{ $displayClass->name ?? '-' }}</span>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider truncate">NIS: {{ $student->nis }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="shrink-0 px-2 py-1 rounded-lg border text-[9px] font-black uppercase {{ $statusClass }}">{{ $statusLabel }}</span>
                        <svg class="w-4 h-4 text-gray-300 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>

                <div x-show="expanded" x-collapse x-cloak class="px-4 pb-4 border-t border-gray-50">
                    <div class="pt-3 space-y-3">
                        {{-- Progress bar --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tingkat Kehadiran</span>
                                <span class="text-xs font-black {{ $barLabel }}">{{ $compliance }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="{{ $barColor }} h-full rounded-full transition-all duration-700" style="width: {{ max($compliance, 4) }}%"></div>
                            </div>
                        </div>

                        {{-- Status grid --}}
                        <div class="grid grid-cols-5 gap-1.5">
                            <div class="bg-emerald-50 rounded-xl p-2.5 text-center border border-emerald-100">
                                <p class="text-[8px] font-black text-emerald-500 uppercase">Hadir</p>
                                <p class="text-sm font-black text-emerald-700 mt-0.5">{{ $present }}</p>
                            </div>
                            <div class="bg-rose-50 rounded-xl p-2.5 text-center border border-rose-100">
                                <p class="text-[8px] font-black text-rose-500 uppercase">Alpa</p>
                                <p class="text-sm font-black text-rose-700 mt-0.5">{{ $absent }}</p>
                            </div>
                            <div class="bg-orange-50 rounded-xl p-2.5 text-center border border-orange-100">
                                <p class="text-[8px] font-black text-orange-500 uppercase">Sakit</p>
                                <p class="text-sm font-black text-orange-700 mt-0.5">{{ $sick }}</p>
                            </div>
                            <div class="bg-sky-50 rounded-xl p-2.5 text-center border border-sky-100">
                                <p class="text-[8px] font-black text-sky-500 uppercase">Izin</p>
                                <p class="text-sm font-black text-sky-700 mt-0.5">{{ $excused }}</p>
                            </div>
                            <div class="bg-amber-50 rounded-xl p-2.5 text-center border border-amber-100">
                                <p class="text-[8px] font-black text-amber-500 uppercase">Telat</p>
                                <p class="text-sm font-black text-amber-700 mt-0.5">{{ $late }}</p>
                            </div>
                        </div>

                        {{-- Action --}}
                        <button type="button" onclick="viewStudentAttendance({{ $student->id }}, @js($student->name))"
                                class="w-full px-4 py-2.5 bg-blue-50 border border-blue-100 text-blue-600 rounded-xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 active:bg-blue-100 transition-all">
                            Lihat Riwayat Bulanan
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div id="studentEmptyState" class="flex flex-col items-center justify-center py-12 sm:py-16 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <p class="text-xs sm:text-sm font-bold text-gray-400">Belum ada data siswa</p>
                <p class="text-[9px] sm:text-[10px] text-gray-300 mt-1">Sesuaikan filter untuk menampilkan data</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($students->hasPages())
        <div class="px-6 sm:px-8 py-5 sm:py-6 border-t border-gray-50 bg-gray-50/30">
            {{ $students->appends(request()->query())->links('vendor.pagination.wakasek-mobile') }}
        </div>
        @endif
    </div>
</div>

{{-- Modal Detail Presensi --}}
<div id="attendanceModal" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm hidden items-center justify-center z-50 lg:p-4">
    <div class="sheet-panel bg-white shadow-2xl w-full lg:max-w-2xl lg:max-h-[85vh] lg:rounded-3xl rounded-t-3xl flex flex-col border border-white/50">
        {{-- Drag handle (mobile only) --}}
        <div class="lg:hidden flex justify-center pt-2.5 pb-1 shrink-0">
            <div class="w-10 h-1.5 rounded-full bg-gray-200"></div>
        </div>
        <div class="px-4 sm:px-6 py-2.5 sm:py-5 border-b border-gray-100 flex items-center justify-between shrink-0 gap-2">
            <div class="min-w-0 flex-1">
                <h3 class="text-xs sm:text-sm font-black text-gray-900 truncate" id="modalTitle">Detail Presensi</h3>
                <p class="text-[9px] sm:text-[10px] font-semibold text-gray-400 mt-0.5">Riwayat kehadiran per bulan</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <select id="monthFilter" onchange="loadStudentData()" class="bg-gray-50 border border-gray-200 rounded-lg text-[10px] sm:text-xs font-bold text-gray-600 px-2.5 sm:px-3 py-1.5 sm:py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ date('m') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                <button onclick="closeModal()" class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 active:text-gray-600 active:bg-gray-200 sm:hover:text-gray-600 sm:hover:bg-gray-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div class="p-4 sm:p-6 overflow-y-auto flex-1">
            <div id="modalBody" class="space-y-1.5 sm:space-y-2">
                <!-- Data akan dimuat lewat JS -->
            </div>
            <div id="modalLoader" class="hidden items-center justify-center py-8">
                <div class="w-6 h-6 sm:w-8 sm:h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentStudentId = null;
    let currentStudentName = '';

    const statusConfig = {
        present: { label: 'Hadir', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200/50', dot: 'bg-emerald-500' },
        absent: { label: 'Alpha', bg: 'bg-rose-50 text-rose-700 border-rose-200/50', dot: 'bg-rose-500' },
        sick: { label: 'Sakit', bg: 'bg-orange-50 text-orange-700 border-orange-200/50', dot: 'bg-orange-500' },
        excused: { label: 'Izin', bg: 'bg-sky-50 text-sky-700 border-sky-200/50', dot: 'bg-sky-500' },
        late: { label: 'Terlambat', bg: 'bg-amber-50 text-amber-700 border-amber-200/50', dot: 'bg-amber-500' },
    };

    // ---------- Quick date range chips ----------
    function toDateInputValue(d) {
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function setQuickRange(type) {
        const now = new Date();
        let start, end;

        if (type === 'today') {
            start = end = now;
        } else if (type === 'week') {
            const day = now.getDay() === 0 ? 7 : now.getDay();
            start = new Date(now);
            start.setDate(now.getDate() - (day - 1));
            end = now;
        } else if (type === 'month') {
            start = new Date(now.getFullYear(), now.getMonth(), 1);
            end = now;
        }

        document.getElementById('startDateInput').value = toDateInputValue(start);
        document.getElementById('endDateInput').value = toDateInputValue(end);

        document.querySelectorAll('[data-range-chip]').forEach(chip => {
            const isActive = chip.dataset.rangeChip === type;
            chip.classList.toggle('bg-blue-600', isActive);
            chip.classList.toggle('border-blue-600', isActive);
            chip.classList.toggle('text-white', isActive);
            chip.classList.toggle('bg-white', !isActive);
            chip.classList.toggle('border-gray-200', !isActive);
            chip.classList.toggle('text-gray-600', !isActive);
        });

        submitAttendanceFilter();
    }

    let attendanceReqSeq = 0;

    async function submitAttendanceFilter({ quiet = false } = {}) {
        const form = document.getElementById('attendanceFilterForm');
        const summary = document.getElementById('attendanceSummary');
        const students = document.getElementById('attendanceStudents');
        const loading = document.getElementById('attendanceFilterLoading');
        const submitButton = document.getElementById('attendanceFilterSubmit');
        const params = new URLSearchParams(new FormData(form));
        const url = `${form.action}?${params.toString()}`;
        const reqSeq = ++attendanceReqSeq;

        if (!quiet) {
            loading?.classList.remove('hidden');
            loading?.classList.add('flex');
            if (submitButton) submitButton.disabled = true;

            [summary, students].forEach(section => {
                section.classList.add('opacity-50', 'scale-[0.995]', 'pointer-events-none');
            });
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) throw new Error('Gagal memuat laporan');

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextSummary = doc.getElementById('attendanceSummary');
            const nextStudents = doc.getElementById('attendanceStudents');
            const nextPdfLink = doc.getElementById('exportPdfLink');
            const nextExcelLink = doc.getElementById('exportExcelLink');

            if (!nextSummary || !nextStudents) {
                if (reqSeq === attendanceReqSeq) window.location.href = url;
                return;
            }

            // Abaikan respons basi dari request yang sudah ketinggalan
            if (reqSeq === attendanceReqSeq) {
                summary.replaceWith(nextSummary);
                students.replaceWith(nextStudents);

                if (nextPdfLink) document.getElementById('exportPdfLink').href = nextPdfLink.href;
                if (nextExcelLink) document.getElementById('exportExcelLink').href = nextExcelLink.href;

                history.replaceState({}, '', url);
            }
        } catch (error) {
            if (reqSeq === attendanceReqSeq) window.location.href = url;
        } finally {
            loading?.classList.add('hidden');
            loading?.classList.remove('flex');
            if (submitButton) submitButton.disabled = false;
        }
    }

    document.getElementById('attendanceFilterForm')?.addEventListener('submit', function(event) {
        event.preventDefault();
        submitAttendanceFilter();
    });

    // ---------- Search (global, langsung memfilter tanpa refresh terlihat) ----------
    let searchTimer = null;

    function scheduleSearchSubmit() {
        document.getElementById('searchInputHidden').value = document.getElementById('studentSearch').value.trim();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => submitAttendanceFilter({ quiet: true }), 300);
    }

    // ---------- Modal ----------
    async function viewStudentAttendance(studentId, studentName) {
        currentStudentId = studentId;
        currentStudentName = studentName;
        loadStudentData();
    }

    async function loadStudentData() {
        document.getElementById('modalTitle').innerText = 'Detail Presensi: ' + currentStudentName;
        const month = document.getElementById('monthFilter').value;
        const modalBody = document.getElementById('modalBody');
        const modalLoader = document.getElementById('modalLoader');

        modalBody.innerHTML = '';
        modalLoader.classList.remove('hidden');
        modalLoader.classList.add('flex');

        const modal = document.getElementById('attendanceModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        requestAnimationFrame(() => modal.classList.add('is-open'));
        document.body.style.overflow = 'hidden';

        try {
            const response = await fetch(`/wakasek/reports/attendance/student/${currentStudentId}?month=${month}&year={{ date('Y') }}`);
            const student = await response.json();

            modalLoader.classList.add('hidden');
            modalLoader.classList.remove('flex');

            if (student.attendances.length === 0) {
                modalBody.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-8 sm:py-10">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-gray-400">Tidak ada riwayat presensi</p>
                        <p class="text-[9px] sm:text-[10px] text-gray-300 mt-1">di bulan ini</p>
                    </div>`;
            } else {
                student.attendances.forEach(att => {
                    const cfg = statusConfig[att.status] || { label: att.status, bg: 'bg-gray-50 text-gray-700 border-gray-200/50', dot: 'bg-gray-500' };
                    modalBody.innerHTML += `
                        <div class="flex items-center gap-3 p-2.5 sm:p-3 rounded-xl bg-gray-50/50 border border-gray-100/50 active:bg-gray-50 sm:hover:bg-gray-50 transition-all">
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <span class="text-[11px] sm:text-xs font-bold text-gray-400 w-14 sm:w-16 shrink-0">${att.date}</span>
                                <span class="inline-flex items-center gap-1.5 px-2 sm:px-2.5 py-1 rounded-lg text-[9px] sm:text-[10px] font-bold ${cfg.bg}">
                                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full ${cfg.dot}"></span>
                                    ${cfg.label}
                                </span>
                            </div>
                            <span class="text-[9px] sm:text-[10px] font-semibold text-gray-400 text-right truncate max-w-[120px] sm:max-w-[200px]">${att.note || '-'}</span>
                        </div>`;
                });
            }
        } catch (error) {
            modalLoader.classList.add('hidden');
            modalLoader.classList.remove('flex');
            modalBody.innerHTML = `
                <div class="flex flex-col items-center justify-center py-8 sm:py-10">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-rose-50 rounded-2xl flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                    </div>
                    <p class="text-xs sm:text-sm font-bold text-rose-500">Gagal memuat data</p>
                    <p class="text-[9px] sm:text-[10px] text-gray-400 mt-1">Periksa koneksi dan coba lagi</p>
                </div>`;
        }
    }

    function closeModal() {
        const modal = document.getElementById('attendanceModal');
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 250);
    }

    document.getElementById('attendanceModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Swipe-down-to-close for mobile bottom sheet
    (function() {
        const panel = document.querySelector('#attendanceModal .sheet-panel');
        let startY = 0, currentY = 0, dragging = false;

        panel.addEventListener('touchstart', (e) => {
            if (window.innerWidth >= 1024) return;
            startY = e.touches[0].clientY;
            dragging = true;
            panel.style.transition = 'none';
        }, { passive: true });

        panel.addEventListener('touchmove', (e) => {
            if (!dragging) return;
            currentY = e.touches[0].clientY - startY;
            if (currentY > 0) panel.style.transform = `translateY(${currentY}px)`;
        }, { passive: true });

        panel.addEventListener('touchend', () => {
            if (!dragging) return;
            dragging = false;
            panel.style.transition = '';
            panel.style.transform = '';
            if (currentY > 100) closeModal();
            currentY = 0;
        });
    })();
</script>
@endsection
