{{-- resources/views/walikelas/dashboard.blade.php --}}
@extends('layouts.walikelas')

@section('title', 'Dashboard Wali Kelas')
@section('header', 'Dashboard')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
@endphp

<div class="space-y-4 sm:space-y-6 pb-24" x-data="{ tab: 'presensi' }">
    @include('walikelas.partials.context-filter')

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-blue-200/60">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-blue-100/90 tracking-wide">{{ $greeting }},</p>
                <h1 class="text-lg sm:text-2xl font-bold truncate">{{ explode(' ', Auth::user()->name)[0] }}</h1>
                <p class="mt-0.5 text-blue-100 text-[11px] sm:text-sm">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Wali Kelas
                    </span>
                    @if($has_class)
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Kelas {{ $class->name }}
                    </span>
                    @endif
                </div>
            </div>
            <div class="shrink-0">
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur-sm ring-1 ring-white/20 shadow-inner">
                    <span class="text-2xl sm:text-4xl font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($has_class)

    {{-- Stats Cards: horizontal scroll on mobile --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0 scrollbar-hide">

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center relative overflow-hidden">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 z-10">Total Siswa</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900 z-10">{{ $total_students }}</p>
                <svg class="absolute -bottom-2 -right-2 w-14 h-14 text-gray-50 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Kehadiran Hari Ini</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $attendanceRate }}%</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Jurnal Minggu Ini</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $weekAgendaCount }}</p>
            </div>

            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-sky-50/50 rounded-2xl border border-sky-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Mapel Aktif</p>
                <p class="text-xl sm:text-2xl font-black text-sky-600">{{ $subjectAgendas->count() }}</p>
            </div>

        </div>
        <p class="sm:hidden mt-2 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Mobile Tab Switcher --}}
    <div class="lg:hidden flex bg-gray-100 rounded-2xl p-1 gap-1">
        <button type="button" @click="tab = 'presensi'"
            :class="tab === 'presensi' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Presensi
        </button>
        <button type="button" @click="tab = 'agenda'"
            :class="tab === 'agenda' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Agenda
        </button>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        {{-- Left Column: Presensi (Attendance Breakdown + Weekly Trend) --}}
        <div :class="{ 'hidden': tab === 'agenda' }" class="lg:block lg:col-span-2 space-y-4 sm:space-y-6">

            {{-- Attendance Breakdown --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-800">Kehadiran Hari Ini</h3>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    @php
                        $attendanceTypes = [
                            ['label' => 'Hadir', 'count' => $presentCount, 'bg' => 'bg-emerald-500', 'text' => 'text-emerald-700', 'cardBg' => 'bg-emerald-50', 'icon' => '✓'],
                            ['label' => 'Terlambat', 'count' => $lateCount, 'bg' => 'bg-amber-500', 'text' => 'text-amber-700', 'cardBg' => 'bg-amber-50', 'icon' => 'T'],
                            ['label' => 'Izin', 'count' => $excusedCount, 'bg' => 'bg-sky-500', 'text' => 'text-sky-700', 'cardBg' => 'bg-sky-50', 'icon' => 'I'],
                            ['label' => 'Sakit', 'count' => $sickCount, 'bg' => 'bg-orange-500', 'text' => 'text-orange-700', 'cardBg' => 'bg-orange-50', 'icon' => 'S'],
                            ['label' => 'Alpha', 'count' => $absentCount, 'bg' => 'bg-rose-500', 'text' => 'text-rose-700', 'cardBg' => 'bg-rose-50', 'icon' => 'A'],
                        ];
                    @endphp

                    <div class="space-y-2.5">
                        @foreach($attendanceTypes as $type)
                            <div class="flex items-center justify-between p-3 {{ $type['cardBg'] }} rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 {{ $type['bg'] }} rounded-lg flex items-center justify-center text-white shadow-sm">
                                        <span class="font-black text-xs">{{ $type['icon'] }}</span>
                                    </div>
                                    <span class="text-sm font-bold {{ $type['text'] }}">{{ $type['label'] }}</span>
                                </div>
                                <span class="text-lg font-black {{ $type['text'] }}">{{ $type['count'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if($total_students > 0)
                        <div class="mt-4 pt-4 border-t border-gray-50">
                            <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden flex">
                                @foreach($attendanceTypes as $type)
                                    @php $width = ($type['count'] / $total_students) * 100; @endphp
                                    @if($type['count'] > 0)
                                        <div class="{{ $type['bg'] }} h-full transition-all" style="width: {{ $width }}%"></div>
                                    @endif
                                @endforeach
                            </div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase text-center tracking-widest mt-2">
                                {{ $totalPresent }} dari {{ $total_students }} siswa hadir
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Weekly Trend Chart --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-800">Tren Kehadiran</h3>
                            <p class="text-[11px] text-gray-400">Minggu ini (Senin - Minggu)</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 bg-gray-50 border border-gray-100 px-2.5 py-1 rounded-lg">Minggu Ini</span>
                </div>
                <div class="px-1 sm:px-4 pt-2 pb-3 sm:pb-4">
                    @if($weeklyTrend->sum('total') > 0)
                        <div id="weeklyTrendChart"></div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 sm:py-10 text-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <p class="text-xs sm:text-sm text-gray-500 italic">Belum ada data kehadiran minggu ini</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Right Column: Agenda --}}
        <div :class="{ 'hidden': tab !== 'agenda' }" class="lg:block lg:col-span-1 space-y-4 sm:space-y-6">

            {{-- Recent Agendas --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col max-h-125 lg:max-h-150">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-800">Jurnal Terakhir</h3>
                    </div>
                    <a href="{{ route('wali-kelas.agenda.index', array_filter(['wali_context' => $selectedWaliContextKey ?? null])) }}" class="text-[11px] sm:text-xs font-medium text-blue-600 hover:underline active:text-blue-800">Semua →</a>
                </div>
                <div class="flex-1 overflow-y-auto divide-y divide-gray-50 custom-scrollbar">
                    @forelse($latest_agendas as $agenda)
                        @php
                            $isToday = \Carbon\Carbon::parse($agenda->date)->isToday();
                        @endphp
                        <div class="px-4 sm:px-5 py-3 sm:py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $agenda->subject->name ?? '-' }}</h4>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="text-[11px] text-gray-400">{{ $agenda->teacher->name }}</span>
                                        <span class="text-[11px] text-gray-400">•</span>
                                        <span class="text-[11px] text-gray-400">{{ $agenda->date->translatedFormat('d M Y') }}</span>
                                    </div>
                                </div>
                                @if($isToday)
                                    <span class="shrink-0 px-2 py-0.5 text-[10px] sm:text-xs bg-indigo-100 text-indigo-700 rounded-full font-medium">Hari Ini</span>
                                @else
                                    <span class="shrink-0 px-2 py-0.5 text-[10px] sm:text-xs bg-emerald-100 text-emerald-700 rounded-full font-medium">Terisi</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center flex flex-col items-center justify-center h-full">
                            <div class="w-10 h-10 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest">Belum Ada Agenda</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Subject Distribution --}}
            @if($subjectAgendas->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center gap-3">
                    <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <h3 class="font-bold text-sm sm:text-base text-gray-800">Mapel Bulan Ini</h3>
                </div>
                <div class="p-4 sm:p-6 space-y-3">
                    @php
                        $barGradients = [
                            'bg-linear-to-r from-indigo-500 to-blue-500',
                            'bg-linear-to-r from-emerald-500 to-teal-500',
                            'bg-linear-to-r from-amber-500 to-orange-500',
                            'bg-linear-to-r from-purple-500 to-pink-500',
                            'bg-linear-to-r from-sky-500 to-cyan-500',
                        ];
                    @endphp
                    @foreach($subjectAgendas as $subject => $count)
                        @php
                            $maxCount = $subjectAgendas->max();
                            $width = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-sm font-bold text-gray-700">{{ $subject }}</span>
                                <span class="text-xs font-black text-indigo-600">{{ $count }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $barGradients[$loop->index % 5] }} h-full rounded-full transition-all duration-700" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Tips Box --}}
    <div class="bg-linear-to-br from-blue-50 to-indigo-50 rounded-2xl p-5 border border-blue-100 relative overflow-hidden">
        <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-blue-500/10" fill="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="relative z-10">
            <h3 class="text-[11px] font-bold text-blue-800 tracking-wide mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Informasi
            </h3>
            <ul class="space-y-1.5 text-xs text-blue-700/90 font-medium">
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Pantau kehadiran siswa kelas {{ $class->name ?? '-' }} secara real-time melalui menu Presensi.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Lihat laporan bulanan untuk rekapitulasi kehadiran seluruh siswa.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Gunakan menu Arsip untuk melihat jurnal mengajar yang sudah diisi oleh guru.
                </li>
            </ul>
        </div>
    </div>

    @endif
</div>

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const weeklyData = @json($weeklyTrend->values());
        if (!weeklyData || weeklyData.length === 0) return;

        const isMobile = window.innerWidth < 640;

        var options = {
            series: [{
                name: 'Kehadiran',
                data: weeklyData.map(item => item.rate)
            }],
            chart: {
                height: isMobile ? 200 : 260,
                type: 'bar',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                animations: { enabled: true, speed: 600, easing: 'easeOutQuart' }
            },
            colors: ['#6366f1'],
            fill: {
                type: 'solid',
                opacity: 1
            },
            stroke: {
                show: false
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: isMobile ? '9px' : '11px',
                    fontWeight: 700,
                    fontFamily: 'Inter',
                    colors: ['#374151']
                },
                offsetY: -8,
                formatter: function(val) { return val + '%'; }
            },
            markers: {
                size: 0
            },
            xaxis: {
                categories: weeklyData.map(item => item.day),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#94a3b8',
                        fontSize: isMobile ? '9px' : '11px',
                        fontWeight: 600,
                        fontFamily: 'Inter'
                    }
                }
            },
            yaxis: {
                min: 0,
                max: 100,
                tickAmount: 4,
                labels: {
                    style: { colors: '#94a3b8', fontSize: isMobile ? '9px' : '11px' },
                    formatter: function(val) { return val + '%'; }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } },
                padding: { left: isMobile ? 0 : 5, right: isMobile ? 0 : 5 }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function(val) { return val + '%'; }
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '60%'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#weeklyTrendChart"), options);
        chart.render();
    });
</script>
@endpush
@endsection
