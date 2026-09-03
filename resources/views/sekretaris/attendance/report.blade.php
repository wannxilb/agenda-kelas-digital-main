@extends('layouts.sekretaris')

@section('title', 'Laporan Presensi')
@section('header', 'Laporan Presensi Siswa')

@section('content')
@php
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

<div class="space-y-6 sm:space-y-8 pb-8">
    {{-- Header Section --}}
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-blue-50 rounded-full blur-3xl opacity-50"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Laporan Presensi</h1>
                <p class="mt-2 text-xs sm:text-sm text-gray-500 font-medium max-w-2xl">Analisis data kehadiran siswa di kelas {{ optional($class)->name ?? '-' }}</p>
            </div>
            <a href="{{ route('sekretaris.print.attendance', request()->query()) }}" target="_blank"
               class="inline-flex items-center gap-2 px-6 py-3 bg-rose-50 text-rose-700 rounded-2xl border border-rose-100 text-xs font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export PDF
            </a>
        </div>
    </div>

    @include('sekretaris.partials.class-filter')

    {{-- Filter --}}
    @php $hasFilter = request()->anyFilled(['start_date', 'end_date']); @endphp
    <div x-data="{ showFilter: {{ $hasFilter ? 'true' : 'false' }} }" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Toggle Bar --}}
        <button @click="showFilter = !showFilter"
                class="w-full px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                     :class="showFilter ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </div>
                <div class="text-left">
                    <span class="text-xs sm:text-sm font-bold text-gray-800">Filter Tanggal</span>
                    @if($hasFilter)
                        <p class="text-[10px] text-blue-600 font-semibold">
                            {{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->translatedFormat('d M') : 'Semua' }}
                            –
                            {{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->translatedFormat('d M Y') : 'Sekarang' }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($hasFilter)
                    <a href="{{ route('sekretaris.attendance.report') }}"
                       class="px-2 py-1 bg-red-50 text-red-500 rounded-lg text-[10px] font-bold"
                       onclick="event.stopPropagation()">Reset</a>
                @endif
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                     :class="{ 'rotate-180': showFilter }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>

        {{-- Expandable Form --}}
        <div x-show="showFilter" x-collapse x-cloak>
            <form method="GET" action="{{ route('sekretaris.attendance.report') }}"
                  class="px-4 pb-4 sm:px-6 sm:pb-5 border-t border-gray-50 pt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Akhir</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all text-xs font-semibold">
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
                    ['label' => 'Total', 'value' => $summary['total'], 'color' => 'gray-900', 'dot' => 'bg-gray-900'],
                    ['label' => 'Hadir', 'value' => $summary['present'], 'color' => 'emerald-600', 'dot' => 'bg-emerald-500'],
                    ['label' => 'Alpa', 'value' => $summary['absent'], 'color' => 'rose-600', 'dot' => 'bg-rose-500'],
                    ['label' => 'Sakit', 'value' => $summary['sick'], 'color' => 'orange-600', 'dot' => 'bg-orange-500'],
                    ['label' => 'Izin', 'value' => $summary['excused'], 'color' => 'sky-600', 'dot' => 'bg-sky-500'],
                    ['label' => 'Telat', 'value' => $summary['late'], 'color' => 'amber-600', 'dot' => 'bg-amber-500'],
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
    <form method="GET" action="{{ url()->current() }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" id="report-search" value="{{ request('search') }}" autocomplete="off"
                       placeholder="Cari nama atau NIS siswa..."
                       class="block w-full pl-9 pr-12 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-500/10 transition-all text-sm font-semibold">
                @if(request('search'))
                    <a href="{{ url()->current() }}?{{ http_build_query(request()->except(['search', 'page'])) }}"
                       class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-gray-400 hover:text-gray-600 bg-white rounded-lg transition-colors" title="Reset pencarian">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Student List --}}
    <div class="lg:bg-white lg:rounded-2xl lg:shadow-sm lg:border lg:border-gray-100 lg:overflow-hidden">
        <div class="hidden lg:flex px-6 sm:px-8 py-5 sm:py-6 border-b border-gray-50 items-center justify-between bg-gray-50/30">
            <h3 class="text-sm sm:text-lg font-black text-gray-900">Ringkasan Presensi</h3>
            <span class="px-3 py-1.5 bg-white text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] rounded-xl border border-gray-100 shadow-sm">{{ $students->total() }} Siswa</span>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-50">
                <thead>
                    <tr class="bg-gray-50/20">
                        <th class="px-8 py-5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Hadir</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alpa</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sakit</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Izin</th>
                        <th class="px-8 py-5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Telat</th>
                        <th class="px-8 py-5 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    @forelse($students as $student)
                    @php
                        $counts = $studentCounts[$student->id] ?? ['present' => 0, 'absent' => 0, 'sick' => 0, 'excused' => 0, 'late' => 0];
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors group">
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
                        <td class="px-8 py-5 text-center text-sm font-black text-emerald-600">{{ $counts['present'] }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-rose-600">{{ $counts['absent'] }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-orange-600">{{ $counts['sick'] }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-sky-600">{{ $counts['excused'] }}</td>
                        <td class="px-8 py-5 text-center text-sm font-black text-amber-600">{{ $counts['late'] }}</td>
                        <td class="px-8 py-5 text-right whitespace-nowrap">
                            <button onclick="viewStudentAttendance({{ $student->id }}, @js($student->name))"
                                    class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-sm">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-400">Belum ada data</p>
                                <p class="text-[10px] text-gray-300 mt-0.5">Coba ubah filter tanggal</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="lg:hidden space-y-2.5">
            @forelse($students as $student)
            @php
                $counts = $studentCounts[$student->id] ?? ['total' => 0, 'present' => 0, 'absent' => 0, 'sick' => 0, 'excused' => 0, 'late' => 0, 'main_status' => null, 'rate' => 0];
                $totalDays = $counts['total'];
                $present = $counts['present'];
                $absent = $counts['absent'];
                $sick = $counts['sick'];
                $excused = $counts['excused'];
                $late = $counts['late'];
                $studentPresent = $present + $late;
                $studentRate = $totalDays > 0 ? round(($studentPresent / $totalDays) * 100) : 0;
                $mainStatus = $counts['main_status'];
            @endphp
            <div x-data="{ expanded: false }"
                 class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <button type="button" @click="expanded = !expanded"
                        class="w-full px-4 py-3.5 flex items-center gap-3 text-left active:bg-gray-50 transition-colors">
                    <div class="w-11 h-11 bg-linear-to-br from-blue-500 to-indigo-600 text-white rounded-2xl flex items-center justify-center text-xs font-black shadow-lg shadow-blue-100 shrink-0">
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
                                <p class="text-sm font-black text-emerald-700 mt-0.5">{{ $present }}</p>
                            </div>
                            <div class="bg-amber-50 rounded-xl p-2.5 text-center border border-amber-100">
                                <p class="text-[8px] font-black text-amber-500 uppercase">Telat</p>
                                <p class="text-sm font-black text-amber-700 mt-0.5">{{ $late }}</p>
                            </div>
                            <div class="bg-sky-50 rounded-xl p-2.5 text-center border border-sky-100">
                                <p class="text-[8px] font-black text-sky-500 uppercase">Izin</p>
                                <p class="text-sm font-black text-sky-700 mt-0.5">{{ $excused }}</p>
                            </div>
                            <div class="bg-orange-50 rounded-xl p-2.5 text-center border border-orange-100">
                                <p class="text-[8px] font-black text-orange-500 uppercase">Sakit</p>
                                <p class="text-sm font-black text-orange-700 mt-0.5">{{ $sick }}</p>
                            </div>
                            <div class="bg-rose-50 rounded-xl p-2.5 text-center border border-rose-100">
                                <p class="text-[8px] font-black text-rose-500 uppercase">Alpha</p>
                                <p class="text-sm font-black text-rose-700 mt-0.5">{{ $absent }}</p>
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
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center shadow-sm">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Data tidak ditemukan</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Coba ubah filter tanggal atau kata kunci pencarian</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($students->hasPages())
        <div class="px-6 sm:px-8 py-5 sm:py-6 border-t border-gray-50 bg-gray-50/30">
            {{ $students->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal Detail --}}
<div id="attendanceModal" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm hidden items-end sm:items-center justify-center z-50">
    <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-slide-up sm:animate-fade-in">
        {{-- Handle bar mobile --}}
        <div class="h-5 sm:hidden bg-white flex items-center justify-center pt-2">
            <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
        </div>
        <div class="px-6 sm:px-8 py-5 sm:py-6 border-b border-gray-100 flex items-center justify-between bg-white">
            <div class="min-w-0">
                <h3 class="text-base sm:text-lg font-black text-gray-900" id="modalTitle">Riwayat Presensi</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5 truncate" id="modalSubtitle"></p>
            </div>
            <button onclick="closeModal()" class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-50 text-gray-400 hover:text-gray-600 rounded-full flex items-center justify-center transition-colors flex-shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="px-6 sm:px-8 py-5 sm:py-6">
            <div class="mb-4">
                <label class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1 mb-2 block">Pilih Bulan</label>
                <select id="monthFilter" onchange="loadStudentData()" class="w-full bg-gray-50 border-none rounded-2xl text-xs sm:text-sm font-bold text-gray-600 px-4 py-2.5 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-inner">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ date('m') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="max-h-[50vh] overflow-y-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-50">
                    <thead class="sticky top-0 bg-white">
                        <tr>
                            <th class="px-2 py-3 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                            <th class="px-2 py-3 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-2 py-3 text-left text-[9px] font-black text-gray-400 uppercase tracking-widest">Ket</th>
                        </tr>
                    </thead>
                    <tbody id="modalBody" class="divide-y divide-gray-50"></tbody>
                </table>
            </div>
        </div>
        <div class="h-6 sm:hidden bg-white flex items-center justify-center pb-2">
            <div class="w-12 h-1 bg-gray-100 rounded-full"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes slide-up {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    @keyframes fade-in {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .animate-slide-up { animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
    .animate-fade-in { animation: fade-in 0.2s ease-out; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 999px; }
</style>
@endpush

@push('scripts')
<script>
    let currentStudentId = null;
    let currentStudentName = '';

    (function () {
        const searchInput = document.getElementById('report-search');
        if (!searchInput) return;
        let timer;
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(() => searchInput.form.submit(), 600);
        });
    })();
</script>
<script>
    async function viewStudentAttendance(studentId, studentName) {
        currentStudentId = studentId;
        currentStudentName = studentName;
        loadStudentData();
    }

    async function loadStudentData() {
        document.getElementById('modalSubtitle').innerText = currentStudentName;
        const month = document.getElementById('monthFilter').value;
        const modalBody = document.getElementById('modalBody');

        modalBody.innerHTML = '<tr><td colspan="3" class="text-center py-8"><div class="inline-flex items-center gap-2 text-[10px] font-bold text-gray-400"><div class="w-4 h-4 border-2 border-gray-300 border-t-transparent rounded-full animate-spin"></div>Memuat data...</div></td></tr>';

        const modal = document.getElementById('attendanceModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        try {
            const params = new URLSearchParams({
                month: month,
                year: '{{ date('Y') }}',
            });

            @if(request('class_id'))
                params.set('class_id', '{{ request('class_id') }}');
            @endif

            const response = await fetch(`{{ route('sekretaris.attendance.report.student', ':id') }}`.replace(':id', currentStudentId) + `?${params.toString()}`);
            const student = await response.json();

            modalBody.innerHTML = '';
            if (student.attendances.length === 0) {
                modalBody.innerHTML = '<tr><td colspan="3" class="text-center py-10 text-xs font-medium text-gray-400">Tidak ada riwayat presensi di bulan ini.</td></tr>';
            } else {
                const statusConfig = {
                    'present': { label: 'Hadir', class: 'bg-emerald-50 text-emerald-700' },
                    'absent': { label: 'Alpa', class: 'bg-rose-50 text-rose-700' },
                    'sick': { label: 'Sakit', class: 'bg-orange-50 text-orange-700' },
                    'late': { label: 'Telat', class: 'bg-amber-50 text-amber-700' },
                    'excused': { label: 'Izin', class: 'bg-sky-50 text-sky-700' }
                };
                student.attendances.forEach(att => {
                    const cfg = statusConfig[att.status] || { label: att.status, class: 'bg-gray-50 text-gray-700' };
                    modalBody.innerHTML += `
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-2 py-4 text-xs text-gray-900 font-bold">${att.date}</td>
                            <td class="px-2 py-4"><span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-black ${cfg.class}">${cfg.label}</span></td>
                            <td class="px-2 py-4 text-[10px] text-gray-500 font-medium italic">${att.note || '-'}</td>
                        </tr>
                    `;
                });
            }
        } catch (error) {
            modalBody.innerHTML = '<tr><td colspan="3" class="text-center py-8 text-rose-500 font-black uppercase text-[10px]">Gagal memuat data.</td></tr>';
        }
    }

    function closeModal() {
        const modal = document.getElementById('attendanceModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.getElementById('attendanceModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
@endpush
