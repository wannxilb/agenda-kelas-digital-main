@extends('layouts.wakasek')

@section('title', 'Evaluasi Akademik')
@section('header', 'Evaluasi Akademik')

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes barFill {
        from { width: 0; }
    }
    @keyframes donutDraw {
        from { stroke-dashoffset: 251.2; }
    }
    .anim-card { animation: fadeSlideUp 0.4s ease-out both; }
    .anim-card:nth-child(1) { animation-delay: 0.03s; }
    .anim-card:nth-child(2) { animation-delay: 0.06s; }
    .anim-card:nth-child(3) { animation-delay: 0.09s; }
    .anim-card:nth-child(4) { animation-delay: 0.12s; }
    .bar-animate { animation: barFill 1s ease-out both; animation-delay: 0.3s; }
    .donut-animate { animation: donutDraw 1.2s ease-out both; animation-delay: 0.4s; }
</style>
@endpush

@section('content')
@php
    use Carbon\Carbon;
    $currentDate = Carbon::now();
@endphp

<div class="space-y-4 sm:space-y-6 pb-28 lg:pb-8" x-data="evaluationDashboard()">

    {{-- Header --}}
    <div class="bg-white rounded-3xl p-4 sm:p-6 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-xl font-black text-gray-900 tracking-tight truncate">Evaluasi Akademik</h1>
                    <p class="text-[10px] sm:text-sm text-gray-500 font-medium">Analisis data kehadiran, performa guru, dan ketercapaian materi</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="px-3 py-1.5 sm:px-4 sm:py-2 bg-indigo-50 text-indigo-700 rounded-xl text-[10px] sm:text-xs font-bold uppercase tracking-widest border border-indigo-100 whitespace-nowrap">
                    {{ $currentDate->translatedFormat('F Y') }}
                </div>
                <a href="{{ route('wakasek.evaluation.report') }}" class="px-3 py-1.5 sm:px-4 sm:py-2 bg-indigo-600 text-white rounded-xl text-[10px] sm:text-xs font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-sm shadow-indigo-200 whitespace-nowrap">
                    Laporan
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards (Horizontal scroll on mobile) --}}
    <div class="relative -mx-4 sm:mx-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0 sm:overflow-visible px-4 sm:px-0">
            <div class="anim-card snap-center shrink-0 w-[155px] sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-indigo-500 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Jurnal</p>
                        <p class="text-xl sm:text-3xl font-black text-gray-900 mt-0.5" x-data="counter({{ $totalJournals }})" x-text="display"></p>
                    </div>
                    <div class="w-9 h-9 sm:w-12 sm:h-12 bg-indigo-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-indigo-600 group-hover:scale-110 group-hover:bg-indigo-100 transition-all duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs text-gray-500">
                    <span class="font-bold text-indigo-600">{{ $todayJournals }}</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-400">hari ini</span>
                </div>
            </div>

            <div class="anim-card snap-center shrink-0 w-[155px] sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-blue-500 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kepatuhan</p>
                        <p class="text-xl sm:text-3xl font-black mt-0.5 {{ $teachingCompliance >= 70 ? 'text-emerald-600' : ($teachingCompliance >= 30 ? 'text-amber-600' : 'text-rose-600') }}" x-data="counter({{ $teachingCompliance }})" x-text="display + '%'"></p>
                    </div>
                    <div class="w-9 h-9 sm:w-12 sm:h-12 bg-blue-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-blue-600 group-hover:scale-110 group-hover:bg-blue-100 transition-all duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-1.5">
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bar-animate h-full rounded-full {{ $teachingCompliance >= 70 ? 'bg-emerald-500' : ($teachingCompliance >= 30 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $teachingCompliance }}%"></div>
                        </div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-[8px] sm:text-[9px] font-bold text-gray-400">{{ $actualThisWeek }} jurnal</span>
                        <span class="text-[8px] sm:text-[9px] font-bold text-gray-400">{{ $expectedPerWeek }} jadwal</span>
                    </div>
                </div>
            </div>

            <div class="anim-card snap-center shrink-0 w-[155px] sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-purple-500 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kehadiran</p>
                        <p class="text-xl sm:text-3xl font-black mt-0.5 {{ $avgAttendance >= 80 ? 'text-emerald-600' : ($avgAttendance >= 60 ? 'text-amber-600' : 'text-rose-600') }}" x-data="counter({{ $avgAttendance }})" x-text="display + '%'"></p>
                    </div>
                    <div class="w-9 h-9 sm:w-12 sm:h-12 bg-purple-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-purple-600 group-hover:scale-110 group-hover:bg-purple-100 transition-all duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] sm:text-xs text-gray-500">
                    <span class="font-bold text-purple-600">{{ $totalStudents }}</span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-400">siswa aktif</span>
                </div>
            </div>

            <div class="anim-card snap-center shrink-0 w-[155px] sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-emerald-500 hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Guru</p>
                        <p class="text-xl sm:text-3xl font-black text-gray-900 mt-0.5" x-data="counter({{ $totalTeachers }})" x-text="display"></p>
                    </div>
                    <div class="w-9 h-9 sm:w-12 sm:h-12 bg-emerald-50 rounded-xl sm:rounded-2xl flex items-center justify-center text-emerald-600 group-hover:scale-110 group-hover:bg-emerald-100 transition-all duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                </div>
                <div class="flex items-center text-[10px] sm:text-xs text-gray-400">
                    Tenaga pendidik aktif
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 p-1 bg-gray-100 rounded-xl">
        <button @click="activeTab = 'kehadiran'" :class="activeTab === 'kehadiran' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-2.5 rounded-lg text-[10px] sm:text-xs font-bold uppercase tracking-widest transition-all duration-200 text-center">
            Kehadiran
        </button>
        <button @click="activeTab = 'jurnal'" :class="activeTab === 'jurnal' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-2.5 rounded-lg text-[10px] sm:text-xs font-bold uppercase tracking-widest transition-all duration-200 text-center">
            Jurnal
        </button>
        <button @click="activeTab = 'performa'" :class="activeTab === 'performa' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-2.5 rounded-lg text-[10px] sm:text-xs font-bold uppercase tracking-widest transition-all duration-200 text-center">
            Performa Guru
        </button>
    </div>

    {{-- Tab Content: Kehadiran --}}
    <div x-show="activeTab === 'kehadiran'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Attendance Ranking --}}
            <div class="lg:col-span-2 bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs sm:text-sm font-black text-gray-900 tracking-tight uppercase">Ranking Kehadiran Kelas</h3>
                        <p class="text-[9px] sm:text-xs text-gray-400 mt-0.5">Berdasarkan persentase kehadiran siswa</p>
                    </div>
                    <a href="{{ route('wakasek.evaluation.report') }}" class="text-[9px] sm:text-[10px] font-bold text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition-colors shrink-0">
                        Lihat Semua
                    </a>
                </div>
                <div class="p-3 sm:p-6">
                    <div class="space-y-2 sm:space-y-3">
                        @forelse($classAttendance->take(8) as $index => $class)
                            @php
                                $pct = $class['percentage'];
                                $barColor = $pct >= 80 ? 'from-emerald-500 to-emerald-600' : ($pct >= 60 ? 'from-amber-500 to-amber-600' : 'from-rose-500 to-rose-600');
                                $textColor = $pct >= 80 ? 'text-emerald-600' : ($pct >= 60 ? 'text-amber-600' : 'text-rose-600');
                                $bgColor = $pct >= 80 ? 'bg-emerald-50' : ($pct >= 60 ? 'bg-amber-50' : 'bg-rose-50');
                                $rankBg = $index < 3 ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-500';
                            @endphp
                            <div class="group p-2.5 sm:p-3 rounded-xl hover:bg-gray-50 transition-all duration-200">
                                <div class="flex justify-between items-center mb-1.5 sm:mb-2">
                                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                        <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg {{ $rankBg }} flex items-center justify-center text-[9px] sm:text-[10px] font-black shrink-0">{{ $index + 1 }}</span>
                                        <div class="min-w-0">
                                            <span class="text-xs sm:text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition-colors truncate block">{{ $class['name'] }}</span>
                                            <p class="text-[8px] sm:text-[9px] font-bold text-gray-400">{{ $class['student_count'] }} siswa</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <span class="px-2 py-0.5 {{ $bgColor }} {{ $textColor }} rounded-md text-[8px] sm:text-[9px] font-black">{{ $pct }}%</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 sm:h-2 overflow-hidden">
                                    <div class="bar-animate bg-linear-to-r {{ $barColor }} h-full rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 sm:py-12">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </div>
                                <p class="text-xs sm:text-sm font-bold text-gray-400">Belum ada data kehadiran</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Absence Breakdown --}}
            <div class="space-y-4 sm:space-y-6">
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm p-4 sm:p-6">
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 tracking-tight uppercase mb-4 sm:mb-5">Ketidakhadiran</h3>

                    {{-- Donut Chart --}}
                    @if($totalAbsences > 0)
                    <div class="flex justify-center mb-4 sm:mb-6">
                        <div class="relative" x-data="donutChart()">
                            <svg viewBox="0 0 100 100" class="transform -rotate-90 w-28 h-28 sm:w-36 sm:h-36">
                                @php
                                    $radius = 40;
                                    $circumference = 2 * pi() * $radius;
                                    $offset = 0;
                                    $absenceColors = ['absent' => '#f43f5e', 'sick' => '#f97316', 'excused' => '#0ea5e9', 'late' => '#f59e0b'];
                                    $absenceKeys = ['absent', 'sick', 'excused', 'late'];
                                @endphp
                                <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="#f3f4f6" stroke-width="8"/>
                                @foreach($absenceKeys as $key)
                                    @php
                                        $count = $absenceStats[$key] ?? 0;
                                        $pctVal = $totalAbsences > 0 ? ($count / $totalAbsences) : 0;
                                        $dashLen = $pctVal * $circumference;
                                    @endphp
                                    @if($count > 0)
                                        <circle cx="50" cy="50" r="{{ $radius }}" fill="none"
                                            stroke="{{ $absenceColors[$key] }}" stroke-width="8"
                                            stroke-dasharray="{{ $dashLen }} {{ $circumference - $dashLen }}"
                                            stroke-dashoffset="{{ -$offset }}"
                                            class="donut-animate transition-all duration-500"
                                            @mouseenter="hovered = '{{ $key }}'" @mouseleave="hovered = null"
                                            style="cursor: pointer; filter: drop-shadow(0 0 4px {{ $absenceColors[$key] }}33);"
                                        />
                                    @endif
                                    @php $offset += $dashLen; @endphp
                                @endforeach
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-xl sm:text-2xl font-black text-gray-900">{{ $totalAbsences }}</span>
                                <span class="text-[7px] sm:text-[8px] font-bold text-gray-400 uppercase tracking-widest">Total</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="space-y-2">
                        @php
                            $absenceTypes = [
                                ['key' => 'absent', 'label' => 'Alpha', 'color' => 'bg-rose-500', 'text' => 'text-rose-700', 'cardBg' => 'bg-rose-50', 'border' => 'border-rose-100/50'],
                                ['key' => 'sick', 'label' => 'Sakit', 'color' => 'bg-orange-500', 'text' => 'text-orange-700', 'cardBg' => 'bg-orange-50', 'border' => 'border-orange-100/50'],
                                ['key' => 'excused', 'label' => 'Izin', 'color' => 'bg-sky-500', 'text' => 'text-sky-700', 'cardBg' => 'bg-sky-50', 'border' => 'border-sky-100/50'],
                                ['key' => 'late', 'label' => 'Telat', 'color' => 'bg-amber-500', 'text' => 'text-amber-700', 'cardBg' => 'bg-amber-50', 'border' => 'border-amber-100/50'],
                            ];
                        @endphp
                        @foreach($absenceTypes as $type)
                            @php
                                $count = $absenceStats[$type['key']] ?? 0;
                                $pctVal = $totalAbsences > 0 ? round(($count / $totalAbsences) * 100, 1) : 0;
                            @endphp
                            <div class="flex items-center justify-between p-2.5 sm:p-3 {{ $type['cardBg'] }} rounded-xl border {{ $type['border'] }} hover:shadow-sm transition-all group">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="w-2 h-2 {{ $type['color'] }} rounded-full shrink-0"></div>
                                    <span class="text-xs sm:text-sm font-bold {{ $type['text'] }}">{{ $type['label'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <span class="text-[9px] sm:text-[10px] font-bold text-gray-400 group-hover:text-gray-600 transition-colors">{{ $pctVal }}%</span>
                                    <span class="text-base sm:text-lg font-black {{ $type['text'] }}">{{ $count }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Content: Jurnal --}}
    <div x-show="activeTab === 'jurnal'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Journal Compliance Detail --}}
            <div class="lg:col-span-2 bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-50">
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 tracking-tight uppercase">Kepatuhan Jurnal</h3>
                    <p class="text-[9px] sm:text-xs text-gray-400 mt-0.5">Perbandingan jurnal diisi vs jadwal mengajar minggu ini</p>
                </div>
                <div class="p-4 sm:p-6">
                    {{-- Stat pills horizontal scroll on mobile --}}
                    <div class="relative -mx-4 sm:mx-0">
                        <div class="flex sm:grid sm:grid-cols-3 gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0 sm:overflow-visible px-4 sm:px-0 mb-4 sm:mb-6">
                            <div class="snap-center shrink-0 w-[120px] sm:w-auto text-center p-3 sm:p-4 bg-emerald-50 rounded-2xl border border-emerald-100/50">
                                <p class="text-xl sm:text-3xl font-black text-emerald-600" x-data="counter({{ $actualThisWeek }})" x-text="display"></p>
                                <p class="text-[8px] sm:text-[10px] font-bold text-emerald-700 uppercase tracking-widest mt-0.5">Jurnal Diisi</p>
                            </div>
                            <div class="snap-center shrink-0 w-[120px] sm:w-auto text-center p-3 sm:p-4 bg-indigo-50 rounded-2xl border border-indigo-100/50">
                                <p class="text-xl sm:text-3xl font-black text-indigo-600" x-data="counter({{ $expectedPerWeek }})" x-text="display"></p>
                                <p class="text-[8px] sm:text-[10px] font-bold text-indigo-700 uppercase tracking-widest mt-0.5">Jadwal Total</p>
                            </div>
                            <div class="snap-center shrink-0 w-[120px] sm:w-auto text-center p-3 sm:p-4 rounded-2xl border {{ $teachingCompliance >= 70 ? 'bg-emerald-50 border-emerald-100/50' : ($teachingCompliance >= 30 ? 'bg-amber-50 border-amber-100/50' : 'bg-rose-50 border-rose-100/50') }}">
                                <p class="text-xl sm:text-3xl font-black {{ $teachingCompliance >= 70 ? 'text-emerald-600' : ($teachingCompliance >= 30 ? 'text-amber-600' : 'text-rose-600') }}" x-data="counter({{ $teachingCompliance }})" x-text="display + '%'"></p>
                                <p class="text-[8px] sm:text-[10px] font-bold uppercase tracking-widest mt-0.5 {{ $teachingCompliance >= 70 ? 'text-emerald-700' : ($teachingCompliance >= 30 ? 'text-amber-700' : 'text-rose-700') }}">Kepatuhan</p>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="flex items-center gap-3">
                        <div class="flex-1 bg-gray-100 rounded-full h-5 sm:h-6 overflow-hidden border border-gray-200">
                            <div class="bar-animate h-full rounded-full bg-linear-to-r {{ $teachingCompliance >= 70 ? 'from-emerald-500 to-emerald-600' : ($teachingCompliance >= 30 ? 'from-amber-500 to-amber-600' : 'from-rose-500 to-rose-600') }} transition-all duration-1000" style="width: {{ max($teachingCompliance, 5) }}%"></div>
                        </div>
                        <span class="shrink-0 text-xs sm:text-sm font-black {{ $teachingCompliance >= 70 ? 'text-emerald-600' : ($teachingCompliance >= 30 ? 'text-amber-600' : 'text-rose-600') }}">{{ $teachingCompliance }}%</span>
                    </div>
                </div>
            </div>

            {{-- Top Classes by Agenda --}}
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-xs sm:text-sm font-black text-gray-900 tracking-tight uppercase">Top Kelas Aktif</h3>
                        <p class="text-[9px] sm:text-xs text-gray-400 mt-0.5">Berdasarkan jumlah jurnal</p>
                    </div>
                    <span class="text-[9px] sm:text-[10px] font-bold text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2 sm:px-2.5 py-1 rounded-lg shrink-0">Jurnal</span>
                </div>
                <div class="p-3 sm:p-6">
                    <div class="space-y-3 sm:space-y-4">
                        @forelse($topClasses as $index => $class)
                            @php
                                $maxCount = $topClasses->max('count') ?? 1;
                                $width = $maxCount > 0 ? ($class['count'] / $maxCount) * 100 : 0;
                                $medalBg = $index === 0 ? 'bg-amber-400 text-white' : ($index === 1 ? 'bg-gray-300 text-gray-700' : ($index === 2 ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-500'));
                            @endphp
                            <div class="group p-2 sm:p-2.5 rounded-lg hover:bg-gray-50 transition-all">
                                <div class="flex justify-between items-center mb-1.5">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-5 h-5 sm:w-6 sm:h-6 rounded-md {{ $medalBg }} flex items-center justify-center text-[8px] sm:text-[9px] font-black shrink-0">{{ $index + 1 }}</span>
                                        <span class="text-xs sm:text-sm font-bold text-gray-700 group-hover:text-indigo-600 transition-colors truncate">{{ $class['name'] }}</span>
                                    </div>
                                    <span class="text-[11px] sm:text-xs font-black text-indigo-600 shrink-0">{{ $class['count'] }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bar-animate bg-linear-to-r from-indigo-500 to-blue-500 h-full rounded-full" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs sm:text-sm font-bold text-gray-400 text-center py-6 sm:py-8">Belum ada data</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Content: Performa Guru --}}
    <div x-show="activeTab === 'performa'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Teacher Performance List --}}
            <div class="lg:col-span-2 bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-50">
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 tracking-tight uppercase">Ranking Performa Guru</h3>
                    <p class="text-[9px] sm:text-xs text-gray-400 mt-0.5">Berdasarkan kepatuhan pengisian jurnal mengajar</p>
                </div>
                <div class="p-3 sm:p-6">
                    <div class="space-y-2 sm:space-y-3">
                        @forelse($teacherPerformance as $index => $teacher)
                            @php
                                $comp = $teacher['compliance'];
                                $barColor = $comp >= 70 ? 'from-emerald-500 to-teal-500' : ($comp >= 30 ? 'from-amber-400 to-orange-500' : 'from-rose-500 to-red-500');
                                $textColor = $comp >= 70 ? 'text-emerald-600' : ($comp >= 30 ? 'text-amber-600' : 'text-rose-600');
                                $badgeBg = $comp >= 70 ? 'bg-emerald-50 text-emerald-700' : ($comp >= 30 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');
                                $rankBg = $index < 3 ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500';
                            @endphp
                            <a href="{{ route('wakasek.teaching.show', $teacher['id']) }}" class="block group p-2.5 sm:p-3 rounded-xl hover:bg-gray-50 transition-all duration-200">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-lg {{ $rankBg }} flex items-center justify-center text-[9px] sm:text-[10px] font-black shrink-0">{{ $index + 1 }}</span>
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-lg sm:rounded-xl flex items-center justify-center text-[10px] sm:text-xs font-black shadow shrink-0">
                                        {{ strtoupper(substr($teacher['name'], 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="min-w-0">
                                                <span class="text-xs sm:text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors truncate block">{{ $teacher['name'] }}</span>
                                                @if($teacher['subjects'])
                                                    <p class="text-[8px] sm:text-[9px] font-bold text-gray-400 truncate">{{ $teacher['subjects'] }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                                                <div class="flex items-center gap-1.5 sm:gap-2 text-[10px] sm:text-xs">
                                                    <span class="font-bold text-indigo-600">{{ $teacher['agenda_count'] }}</span>
                                                    <span class="text-gray-300">/</span>
                                                    <span class="font-bold text-gray-500">{{ $teacher['schedule_count'] }}</span>
                                                </div>
                                                <span class="px-2 py-0.5 rounded-lg text-[8px] sm:text-[9px] font-black {{ $badgeBg }}">{{ $comp }}%</span>
                                            </div>
                                        </div>
                                        <div class="mt-1.5 w-full bg-gray-100 rounded-full h-1.5 sm:h-2 overflow-hidden">
                                            <div class="h-full rounded-full bg-linear-to-r {{ $barColor }}" style="width: {{ $comp }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 sm:py-12">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <p class="text-xs sm:text-sm font-bold text-gray-400">Belum ada data guru</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Teacher Summary Stats --}}
            <div class="space-y-4 sm:space-y-6">
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm p-4 sm:p-6">
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 tracking-tight uppercase mb-4 sm:mb-5">Ringkasan</h3>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-indigo-50 rounded-xl border border-indigo-100/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] sm:text-xs font-bold text-gray-500">Total Guru</p>
                                    <p class="text-base sm:text-xl font-black text-gray-900">{{ $totalTeachers }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl border border-emerald-100/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 shrink-0">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] sm:text-xs font-bold text-gray-500">Guru Aktif</p>
                                    <p class="text-base sm:text-xl font-black text-emerald-600">{{ $activeTeachers }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] sm:text-xs font-bold text-gray-400">{{ $totalTeachers > 0 ? round(($activeTeachers / $totalTeachers) * 100) : 0 }}%</span>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600 shrink-0">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] sm:text-xs font-bold text-gray-500">Guru Tak Aktif</p>
                                    <p class="text-base sm:text-xl font-black text-gray-500">{{ $inactiveTeachers }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-xl border border-amber-100/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 shrink-0">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] sm:text-xs font-bold text-gray-500">Rata-rata</p>
                                    <p class="text-base sm:text-xl font-black text-amber-600">{{ $avgCompliance }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Overall compliance bar --}}
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-[10px] sm:text-xs font-black text-gray-900 uppercase tracking-wider">Kepatuhan Global</h4>
                        <span class="text-xs sm:text-sm font-black {{ $teachingCompliance >= 70 ? 'text-emerald-600' : ($teachingCompliance >= 30 ? 'text-amber-600' : 'text-rose-600') }}">{{ $teachingCompliance }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 sm:h-4 overflow-hidden border border-gray-200">
                        <div class="h-full rounded-full bg-linear-to-r {{ $teachingCompliance >= 70 ? 'from-emerald-500 to-emerald-600' : ($teachingCompliance >= 30 ? 'from-amber-500 to-amber-600' : 'from-rose-500 to-rose-600') }}" style="width: {{ max($teachingCompliance, 3) }}%"></div>
                    </div>
                    <div class="flex justify-between mt-1.5">
                        <span class="text-[9px] font-bold text-gray-400">{{ $actualThisWeek }} jurnal terisi</span>
                        <span class="text-[9px] font-bold text-gray-400">dari {{ $expectedPerWeek }} jadwal</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-linear-to-r from-indigo-600 to-blue-600 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-lg shadow-indigo-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl sm:rounded-2xl flex items-center justify-center text-white backdrop-blur-sm shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm sm:text-base font-black text-white">Aksi Cepat</p>
                    <p class="text-[10px] sm:text-xs text-indigo-200 font-medium">Monitor guru, unduh laporan, atau lihat detail kelas</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <a href="{{ route('wakasek.teaching.index') }}" class="flex-1 sm:flex-none text-center px-3 sm:px-5 py-2 sm:py-2.5 bg-white/15 text-white rounded-xl text-[10px] sm:text-xs font-bold uppercase tracking-widest hover:bg-white/30 transition-all backdrop-blur-sm border border-white/20 whitespace-nowrap">
                    Monitoring Guru
                </a>
                <a href="{{ route('wakasek.export.teaching') }}" class="flex-1 sm:flex-none text-center px-3 sm:px-5 py-2 sm:py-2.5 bg-white/15 text-white rounded-xl text-[10px] sm:text-xs font-bold uppercase tracking-widest hover:bg-white/30 transition-all backdrop-blur-sm border border-white/20 whitespace-nowrap">
                    Export Laporan
                </a>
                <a href="{{ route('wakasek.evaluation.report') }}" class="flex-1 sm:flex-none text-center px-3 sm:px-5 py-2 sm:py-2.5 bg-white text-indigo-600 rounded-xl text-[10px] sm:text-xs font-bold uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-sm whitespace-nowrap">
                    Laporan Evaluasi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function evaluationDashboard() {
    return {
        activeTab: 'kehadiran'
    }
}

function counter(target) {
    return {
        display: '0',
        init() {
            let start = 0;
            const duration = 1000;
            const step = target / (duration / 16);
            const animate = () => {
                start += step;
                if (start >= target) {
                    this.display = Number.isInteger(target) ? target.toString() : target.toFixed(1);
                    return;
                }
                this.display = Number.isInteger(target) ? Math.floor(start).toString() : start.toFixed(1);
                requestAnimationFrame(animate);
            };
            setTimeout(() => requestAnimationFrame(animate), 200);
        }
    }
}

function donutChart() {
    return {
        hovered: null
    }
}
</script>
@endpush
