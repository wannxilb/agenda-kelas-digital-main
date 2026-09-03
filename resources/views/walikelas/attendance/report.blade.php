@extends('layouts.walikelas')

@section('title', 'Laporan Presensi')
@section('header', 'Laporan Presensi')
@section('content-padding', 'py-4 sm:py-8 pb-28 lg:pb-8')

@section('content')
@php
    $startDate = request('start_date');
    $endDate = request('end_date');
    $activeSearch = request('search', '');
    $hasFilter = $startDate || $endDate;
    $periodLabel = $hasFilter
        ? trim(($startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') : 'Awal') . ' – ' . ($endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') : 'Sekarang'))
        : 'Semua periode';
    $presentTotal = ($summary['present'] ?? 0) + ($summary['late'] ?? 0);
    $attendanceRate = ($summary['total'] ?? 0) > 0 ? round(($presentTotal / $summary['total']) * 100, 1) : 0;
    $circumference = 97.4;
    $rateDash = round(($attendanceRate / 100) * $circumference, 1);
    $statusStyles = [
        'present' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'late' => 'bg-amber-50 text-amber-700 border-amber-100',
        'excused' => 'bg-sky-50 text-sky-700 border-sky-100',
        'sick' => 'bg-orange-50 text-orange-700 border-orange-100',
        'absent' => 'bg-rose-50 text-rose-700 border-rose-100',
        'unknown' => 'bg-amber-50 text-amber-700 border-amber-100',
    ];
    $statusDots = [
        'present' => 'bg-emerald-500',
        'late' => 'bg-amber-500',
        'excused' => 'bg-sky-500',
        'sick' => 'bg-orange-500',
        'absent' => 'bg-rose-500',
        'unknown' => 'bg-amber-500',
    ];
    $statusLabels = [
        'present' => 'Hadir',
        'late' => 'Telat',
        'excused' => 'Izin',
        'sick' => 'Sakit',
        'absent' => 'Alpha',
        'unknown' => 'Perlu Verifikasi',
    ];
@endphp

<div class="space-y-4 sm:space-y-6 pb-24 lg:pb-8"
     x-data="{
        filterOpen: {{ $hasFilter ? 'true' : 'false' }},
     }">
    @include('walikelas.partials.context-filter')

    {{-- Header Section --}}
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Laporan Presensi</h1>
                <p class="mt-2 text-xs sm:text-sm text-gray-500 font-medium max-w-2xl">
                    Analisis data kehadiran siswa di kelas {{ optional($class)->name ?? '-' }}
                    @if($hasFilter) • Periode: <span class="text-indigo-600 font-bold">{{ $periodLabel }}</span>@endif
                </p>
            </div>
            @if(Route::has('wali-kelas.export.attendance.pdf'))
            <a href="{{ route('wali-kelas.export.attendance.pdf', request()->query()) }}" target="_blank"
               class="inline-flex items-center gap-2 px-6 py-3 bg-rose-50 text-rose-700 rounded-2xl border border-rose-100 text-xs font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export PDF
            </a>
            @endif
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        {{-- Toggle Bar --}}
        <button type="button" @click="filterOpen = !filterOpen"
                class="w-full px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                     :class="filterOpen ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <span class="text-xs sm:text-sm font-bold text-gray-800">Filter Periode</span>
                    @if($hasFilter)
                        <p class="text-[10px] text-indigo-600 font-semibold">
                            {{ $periodLabel }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($hasFilter)
                    <a href="{{ route('wali-kelas.attendance.report', array_filter(['wali_context' => $selectedWaliContextKey ?? null])) }}"
                       class="px-2 py-1 bg-red-50 text-red-500 rounded-lg text-[10px] font-bold"
                       onclick="event.stopPropagation()">Reset</a>
                @endif
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                     :class="{ 'rotate-180': filterOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        {{-- Expandable Form --}}
        <div x-show="filterOpen" x-collapse x-cloak>
            <form method="GET" action="{{ route('wali-kelas.attendance.report') }}"
                  class="px-4 pb-4 sm:px-6 sm:pb-5 border-t border-gray-50 pt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @if($selectedWaliContextKey ?? null)
                    <input type="hidden" name="wali_context" value="{{ $selectedWaliContextKey }}">
                @endif
                @if($activeSearch !== '')
                    <input type="hidden" name="search" value="{{ $activeSearch }}">
                @endif
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all text-xs font-semibold">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Akhir</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all text-xs font-semibold">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider mb-1.5 block text-transparent">X</label>
                    <button type="submit" class="w-full py-2.5 bg-gray-900 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-black transition-all">
                        Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <div class="flex items-center mb-4 pb-2 border-b border-gray-50">
            <div class="w-1.5 h-5 bg-emerald-500 rounded-full mr-3"></div>
            <h3 class="text-sm sm:text-base font-bold text-gray-900">Ringkasan</h3>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            @php
                $statItems = [
                    ['label' => 'Total', 'value' => $summary['total'] ?? 0, 'color' => 'gray-900', 'dot' => 'bg-gray-900'],
                    ['label' => 'Hadir', 'value' => $summary['present'] ?? 0, 'color' => 'emerald-600', 'dot' => 'bg-emerald-500'],
                    ['label' => 'Alpa', 'value' => $summary['absent'] ?? 0, 'color' => 'rose-600', 'dot' => 'bg-rose-500'],
                    ['label' => 'Sakit', 'value' => $summary['sick'] ?? 0, 'color' => 'orange-600', 'dot' => 'bg-orange-500'],
                    ['label' => 'Izin', 'value' => $summary['excused'] ?? 0, 'color' => 'sky-600', 'dot' => 'bg-sky-500'],
                    ['label' => 'Telat', 'value' => $summary['late'] ?? 0, 'color' => 'amber-600', 'dot' => 'bg-amber-500'],
                ];
            @endphp
            @foreach($statItems as $s)
                <div class="flex items-center gap-2.5 px-3 py-2.5 bg-gray-50/50 rounded-xl">
                    <span class="w-2 h-6 {{ $s['dot'] }} rounded-full flex-shrink-0"></span>
                    <div class="min-w-0">
                        <p class="text-[8px] sm:text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-0.5">{{ $s['label'] }}</p>
                        <p class="text-xs sm:text-sm font-black text-{{ $s['color'] }} leading-none">{{ $s['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('wali-kelas.attendance.report') }}" id="wali-report-search-form" class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @foreach(request()->except(['search', 'page']) as $key => $value)
            @if(is_array($value))
                @foreach($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <div class="p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" id="wali-report-search" value="{{ $activeSearch }}" autocomplete="off" placeholder="Cari nama atau NIS siswa..."
                           class="block w-full pl-9 pr-12 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all text-xs font-semibold">
                    @if($activeSearch !== '')
                        <a href="{{ route('wali-kelas.attendance.report', request()->except(['search', 'page'])) }}"
                           class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-gray-400 hover:text-gray-600 bg-white rounded-lg transition-colors" title="Reset pencarian">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                    @endif
                </div>
                <button type="submit" class="sm:w-auto px-5 py-2.5 bg-gray-900 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-black transition-all">
                    Cari
                </button>
            </div>
        </div>
    </form>

    {{-- Student List --}}
    <div class="lg:bg-white lg:rounded-3xl lg:shadow-sm lg:border lg:border-gray-100 lg:overflow-hidden">
        <div class="hidden lg:flex px-6 py-5 border-b border-gray-50 items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 tracking-tight uppercase">Ringkasan Presensi</h3>
            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100">{{ $students->total() }} Siswa</span>
        </div>

        {{-- Mobile: Expandable Cards --}}
        <div class="lg:hidden space-y-2.5">
            @forelse($students as $student)
                @php
                    $counts = $studentCounts[$student->id] ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'sick' => 0, 'excused' => 0, 'late' => 0, 'main_status' => null, 'rate' => 0];
                    $totalDays = $counts['total'];
                    $p = $counts['present'];
                    $a = $counts['absent'];
                    $s = $counts['sick'];
                    $e = $counts['excused'];
                    $l = $counts['late'];
                    $studentRate = $counts['rate'];
                    $mainStatus = $counts['main_status'];
                @endphp
                <div x-data="{ expanded: false }"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <button type="button" @click="expanded = !expanded"
                            class="w-full px-4 py-3.5 flex items-center gap-3 text-left active:bg-gray-50 transition-colors">
                        <div class="w-11 h-11 bg-linear-to-br from-blue-500 to-indigo-600 text-white rounded-2xl flex items-center justify-center text-xs font-black shadow-lg shadow-indigo-100 shrink-0">
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-gray-900 truncate">{{ $student->name }}</p>
                            <div class="mt-1 flex items-center gap-2">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">NIS: {{ $student->nis }}</p>
                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                <p class="text-[10px] font-bold {{ $studentRate >= 80 ? 'text-emerald-600' : ($studentRate >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $studentRate }}%</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 {{ $statusStyles[$mainStatus] ?? 'bg-gray-50 text-gray-400 border-gray-100' }} text-[9px] font-black uppercase tracking-widest rounded-lg border">
                                <span class="w-1.5 h-1.5 {{ $statusDots[$mainStatus] ?? 'bg-gray-300' }} rounded-full"></span>
                                {{ $statusLabels[$mainStatus] ?? ($mainStatus ? $mainStatus : '—') }}
                            </span>
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
                                    <span class="text-xs font-black {{ $studentRate >= 80 ? 'text-emerald-600' : ($studentRate >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $studentRate }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $studentRate >= 80 ? 'bg-emerald-500' : ($studentRate >= 60 ? 'bg-amber-500' : 'bg-rose-500') }} h-full rounded-full transition-all duration-700" style="width: {{ $studentRate }}%"></div>
                                </div>
                            </div>

                            {{-- Status grid --}}
                            <div class="grid grid-cols-5 gap-1.5">
                                <div class="bg-emerald-50 rounded-xl p-2.5 text-center border border-emerald-100">
                                    <p class="text-[8px] font-black text-emerald-500 uppercase">Hadir</p>
                                    <p class="text-sm font-black text-emerald-700 mt-0.5">{{ $p }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-xl p-2.5 text-center border border-amber-100">
                                    <p class="text-[8px] font-black text-amber-500 uppercase">Telat</p>
                                    <p class="text-sm font-black text-amber-700 mt-0.5">{{ $l }}</p>
                                </div>
                                <div class="bg-sky-50 rounded-xl p-2.5 text-center border border-sky-100">
                                    <p class="text-[8px] font-black text-sky-500 uppercase">Izin</p>
                                    <p class="text-sm font-black text-sky-700 mt-0.5">{{ $e }}</p>
                                </div>
                                <div class="bg-orange-50 rounded-xl p-2.5 text-center border border-orange-100">
                                    <p class="text-[8px] font-black text-orange-500 uppercase">Sakit</p>
                                    <p class="text-sm font-black text-orange-700 mt-0.5">{{ $s }}</p>
                                </div>
                                <div class="bg-rose-50 rounded-xl p-2.5 text-center border border-rose-100">
                                    <p class="text-[8px] font-black text-rose-500 uppercase">Alpha</p>
                                    <p class="text-sm font-black text-rose-700 mt-0.5">{{ $a }}</p>
                                </div>
                            </div>

                            {{-- Action --}}
                            <button type="button" onclick="viewStudentAttendance({{ $student->id }}, @js($student->name))"
                                    class="w-full px-4 py-2.5 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 active:bg-indigo-100 transition-all">
                                Lihat Riwayat Bulanan
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center shadow-sm">
                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-gray-500">Data tidak ditemukan</p>
                </div>
            @endforelse

        </div>

        {{-- Desktop Table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rate</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Hadir</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Telat</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Izin</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sakit</th>
                        <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alpha</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($students as $student)
                        @php
                            $counts = $studentCounts[$student->id] ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'sick' => 0, 'excused' => 0, 'late' => 0, 'rate' => 0];
                            $totalDays = $counts['total'];
                            $p = $counts['present'];
                            $a = $counts['absent'];
                            $s = $counts['sick'];
                            $e = $counts['excused'];
                            $l = $counts['late'];
                            $studentRate = $counts['rate'];
                        @endphp
                        <tr class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-linear-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center text-white text-[10px] font-black shadow-sm group-hover:scale-110 transition-transform">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-black text-gray-900 truncate">{{ $student->name }}</div>
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">NIS: {{ $student->nis }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center"><span class="text-sm font-black text-indigo-600">{{ $studentRate }}%</span></td>
                            <td class="px-6 py-4 text-center"><span class="text-sm font-black text-emerald-600">{{ $p }}</span></td>
                            <td class="px-6 py-4 text-center"><span class="text-sm font-black text-amber-600">{{ $l }}</span></td>
                            <td class="px-6 py-4 text-center"><span class="text-sm font-black text-sky-600">{{ $e }}</span></td>
                            <td class="px-6 py-4 text-center"><span class="text-sm font-black text-orange-600">{{ $s }}</span></td>
                            <td class="px-6 py-4 text-center"><span class="text-sm font-black text-rose-600">{{ $a }}</span></td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="viewStudentAttendance({{ $student->id }}, @js($student->name))"
                                        class="px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-sm">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-8 py-16 text-center text-sm font-bold text-gray-400">Data tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-50">
                {{ $students->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    {{-- Info Tips --}}
    <div class="bg-linear-to-br from-blue-50 to-indigo-50 rounded-2xl p-4 sm:p-5 border border-blue-100 relative overflow-hidden">
        <svg class="absolute -right-4 -bottom-4 w-20 h-20 text-blue-500/10" fill="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="relative z-10">
            <h3 class="text-[11px] font-bold text-blue-800 tracking-wide mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Informasi
            </h3>
            <ul class="space-y-1.5 text-xs text-blue-700/90 font-medium">
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Ketuk kartu siswa untuk melihat detail jumlah kehadiran.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Gunakan tombol "Lihat Riwayat" untuk melihat presensi harian per bulan.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Gunakan filter periode untuk melihat laporan pada rentang tanggal tertentu.
                </li>
            </ul>
        </div>
    </div>

</div>

{{-- Modal Detail Presensi --}}
<div id="attendanceModal" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm hidden items-end sm:items-center justify-center z-50 transition-all duration-300">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden animate-slide-up">
        <div class="flex justify-center pt-3 pb-2 sm:hidden">
            <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
        </div>
        <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="min-w-0">
                <h3 class="text-base font-black text-gray-900" id="modalTitle">Riwayat Presensi</h3>
                <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-0.5 truncate" id="modalSubtitle"></p>
            </div>
            <button onclick="closeModal()" class="w-9 h-9 bg-gray-50 text-gray-400 hover:text-gray-600 rounded-xl flex items-center justify-center transition-colors shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="px-5 sm:px-6 py-5">
            <div class="mb-4">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-2">Pilih Bulan</label>
                <select id="monthFilter" onchange="loadStudentData()" class="w-full bg-gray-50 border border-gray-100 rounded-xl text-sm font-bold text-gray-700 px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ date('m') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="modalBody" class="max-h-[52vh] overflow-y-auto custom-scrollbar space-y-2 pr-1">
                <div class="text-center py-8 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Memuat data...</div>
            </div>
        </div>
        <div class="h-5 sm:hidden safe-bottom"></div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes slide-up {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    .animate-slide-up { animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
</style>
@endpush

@push('scripts')
<script>
    let currentStudentId = null;
    let currentStudentName = '';

    async function viewStudentAttendance(studentId, studentName) {
        currentStudentId = studentId;
        currentStudentName = studentName;
        loadStudentData();
    }

    async function loadStudentData() {
        document.getElementById('modalTitle').innerText = 'Riwayat Presensi';
        document.getElementById('modalSubtitle').innerText = currentStudentName;
        const month = document.getElementById('monthFilter').value;
        const modalBody = document.getElementById('modalBody');

        modalBody.innerHTML = '<div class="text-center py-8 text-[10px] font-bold text-gray-400 uppercase tracking-widest italic">Memuat data...</div>';
        document.getElementById('attendanceModal').classList.remove('hidden');
        document.getElementById('attendanceModal').classList.add('flex');

        try {
            const params = new URLSearchParams({
                month,
                year: '{{ date('Y') }}'
            });
            @if($selectedWaliContextKey ?? null)
                params.set('wali_context', @js($selectedWaliContextKey));
            @endif
            const response = await fetch(`/wali-kelas/attendance/report/student/${currentStudentId}?${params.toString()}`);
            const student = await response.json();

            if (!student.attendances || student.attendances.length === 0) {
                modalBody.innerHTML = '<div class="text-center py-10 text-xs font-medium text-gray-400">Tidak ada riwayat presensi di bulan ini.</div>';
                return;
            }

            const statusLabels = {'present': 'Hadir', 'absent': 'Alpha', 'sick': 'Sakit', 'late': 'Telat', 'excused': 'Izin'};
            const statusColors = {
                'present': 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'absent': 'bg-rose-50 text-rose-700 border-rose-100',
                'sick': 'bg-orange-50 text-orange-700 border-orange-100',
                'late': 'bg-amber-50 text-amber-700 border-amber-100',
                'excused': 'bg-sky-50 text-sky-700 border-sky-100'
            };

            modalBody.innerHTML = student.attendances.map(att => `
                <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black text-gray-900">${att.date}</p>
                            <p class="text-[10px] font-medium text-gray-400 mt-0.5">${att.note || 'Tanpa keterangan'}</p>
                        </div>
                        <span class="px-2.5 py-1 ${statusColors[att.status] || 'bg-gray-50 text-gray-700 border-gray-100'} text-[9px] font-black uppercase tracking-widest rounded-lg border shrink-0">${statusLabels[att.status] || att.status}</span>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            modalBody.innerHTML = '<div class="text-center py-8 text-rose-500 font-black uppercase text-[10px]">Gagal memuat data.</div>';
        }
    }

    function closeModal() {
        document.getElementById('attendanceModal').classList.add('hidden');
        document.getElementById('attendanceModal').classList.remove('flex');
    }
</script>
@endpush
