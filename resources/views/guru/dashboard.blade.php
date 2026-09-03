{{-- resources/views/guru/dashboard.blade.php --}}
@extends('layouts.guru')

@section('title', 'Dashboard Guru')
@section('header', 'Dashboard')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
@endphp

<div class="space-y-4 sm:space-y-6 pb-24" x-data="{ tab: 'agenda' }">

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-blue-200/60">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-blue-100/90 tracking-wide">{{ $greeting }},</p>
                <h1 class="text-lg sm:text-2xl font-bold truncate">{{ $teacher->name }}</h1>
                <p class="mt-0.5 text-blue-100 text-[11px] sm:text-sm">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        Guru Pengajar
                    </span>
                    @if($homeroomClass)
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Wali Kelas: {{ $homeroomClass->name }}
                    </span>
                    @endif
                </div>
            </div>
            <div class="flex-shrink-0">
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur-sm ring-1 ring-white/20 shadow-inner">
                    <span class="text-2xl sm:text-4xl font-bold">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards: horizontal scroll on mobile --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0 scrollbar-hide">
            
            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center relative overflow-hidden">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 z-10">Total Jurnal</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900 z-10">{{ $totalAgendas }}</p>
                <svg class="absolute -bottom-2 -right-2 w-14 h-14 text-gray-50 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"></path></svg>
            </div>

            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Kelas Diajar</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $totalClasses }}</p>
            </div>

            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Mata Pelajaran</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $totalSubjects }}</p>
            </div>

            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-sky-50/50 rounded-2xl border border-sky-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Total Siswa</p>
                <p class="text-xl sm:text-2xl font-black text-sky-600">{{ $totalStudents }}</p>
            </div>
            
        </div>
        <p class="sm:hidden mt-2 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Rekap Izin / Sakit / Tugas Luar Bulan Ini --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between gap-3">
            <div class="flex items-center space-x-3 min-w-0">
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-sm sm:text-base text-gray-800 truncate">Rekap Izin / Sakit / Tugas Luar</h3>
                    <p class="text-[11px] sm:text-xs text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }} · hanya yang disetujui</p>
                </div>
            </div>
            <a href="{{ route('guru.teacher-status.index') }}" class="shrink-0 text-[11px] sm:text-xs font-medium text-blue-600 hover:underline active:text-blue-800">Riwayat →</a>
        </div>
        <div class="grid grid-cols-4 divide-x divide-gray-50">
            <div class="px-3 sm:px-5 py-4 sm:py-5 text-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Izin (hari)</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $teacherStatusSummary['izin_days'] ?? 0 }}</p>
            </div>
            <div class="px-3 sm:px-5 py-4 sm:py-5 text-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-orange-600/70 uppercase tracking-wider mb-1">Sakit (hari)</p>
                <p class="text-xl sm:text-2xl font-black text-orange-600">{{ $teacherStatusSummary['sakit_days'] ?? 0 }}</p>
            </div>
            <div class="px-3 sm:px-5 py-4 sm:py-5 text-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-blue-600/70 uppercase tracking-wider mb-1">Tugas Luar (hari)</p>
                <p class="text-xl sm:text-2xl font-black text-blue-600">{{ $teacherStatusSummary['tugas_days'] ?? 0 }}</p>
            </div>
            <div class="px-3 sm:px-5 py-4 sm:py-5 text-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $teacherStatusSummary['total_days'] ?? 0 }}</p>
            </div>
        </div>
        @if(($teacherStatusSummary['pending'] ?? 0) > 0)
        <div class="px-4 sm:px-6 py-2.5 bg-amber-50/70 border-t border-amber-100 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
            <p class="text-[11px] sm:text-xs font-semibold text-amber-700">{{ $teacherStatusSummary['pending'] }} pengajuan menunggu persetujuan wakasek</p>
        </div>
        @endif
    </div>

    {{-- Mobile Tab Switcher --}}
    <div class="lg:hidden flex bg-gray-100 rounded-2xl p-1 gap-1">
        <button type="button" @click="tab = 'agenda'"
            :class="tab === 'agenda' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Jurnal
        </button>
        <button type="button" @click="tab = 'jadwal'"
            :class="tab === 'jadwal' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Jadwal
        </button>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">

        {{-- Jadwal Hari Ini --}}
        <div :class="{ 'hidden': tab !== 'jadwal' }" class="lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-800">Jadwal Hari Ini</h3>
                        <p class="text-[11px] sm:text-xs text-gray-500">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($todaySchedules as $schedule)
                <div class="px-4 sm:px-5 py-3 sm:py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 text-center w-12 sm:w-14">
                            <p class="text-xs sm:text-sm font-bold text-blue-600">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</p>
                            <p class="text-[10px] sm:text-xs text-gray-400">{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</p>
                        </div>
                        <div class="w-px h-7 bg-gray-200 flex-shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $schedule->subject->name ?? '-' }}</p>
                            <p class="text-[11px] sm:text-xs text-gray-500 truncate">{{ $schedule->class->name ?? '-' }} • {{ $schedule->room ?? 'R. Kelas' }}</p>
                        </div>
                        <a href="{{ route('guru.agenda.create', ['schedule_id' => $schedule->id]) }}"
                           class="flex-shrink-0 px-3 py-1.5 bg-blue-600 text-white rounded-xl text-[10px] font-bold transition-all hover:bg-blue-700 active:scale-95 shadow-sm">
                            ISI
                        </a>
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 sm:py-10 text-center">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-xs sm:text-sm text-gray-500 italic">Tidak ada jadwal hari ini</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Jurnal Terbaru --}}
        <div :class="{ 'hidden': tab !== 'agenda' }" class="lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm sm:text-base text-gray-800">Jurnal Terbaru</h3>
                </div>
                <a href="{{ route('guru.agenda.index') }}" class="text-[11px] sm:text-xs font-medium text-blue-600 hover:underline active:text-blue-800">Lihat semua →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentAgendas as $agenda)
                <div class="px-4 sm:px-5 py-3 sm:py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $agenda->title }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ strip_tags($agenda->description) }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('d F Y') }}</span>
                                <span class="text-[11px] text-gray-400">•</span>
                                <span class="text-[11px] text-gray-400 truncate">{{ $agenda->class->name ?? '-' }}</span>
                            </div>
                        </div>
                        @if($agenda->subject)
                        <span class="flex-shrink-0 px-2 py-0.5 text-[10px] sm:text-xs bg-indigo-100 text-indigo-700 rounded-full font-medium truncate max-w-[80px]">{{ $agenda->subject->name }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 sm:py-10 text-center">
                    <svg class="w-10 h-10 sm:w-12 sm:h-12 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-xs sm:text-sm text-gray-500 italic">Belum ada jurnal</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Grafik Aktivitas Mengajar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
            <div class="flex items-center justify-between gap-3 mb-0.5">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-linear-to-br from-purple-500 to-indigo-600 text-white rounded-xl shadow-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">Aktivitas Mengajar</h3>
                        <p class="text-[11px] text-gray-400">6 bulan terakhir</p>
                    </div>
                </div>
                <span class="text-[10px] font-bold text-purple-600 bg-purple-50 border border-purple-100 px-2.5 py-1 rounded-lg shrink-0">6 Bulan</span>
            </div>
        </div>
        <div class="px-2 sm:px-4 pt-1 sm:pt-2 pb-3 sm:pb-4">
            @if(count($monthlyStats ?? []) > 0)
            <div class="relative" x-data="{ mobile: window.innerWidth < 640 }">
                <canvas id="agendaChart" class="w-full" :style="'height: ' + (mobile ? 200 : 260) + 'px;'"></canvas>
                <div class="absolute inset-0 pointer-events-none bg-linear-to-r from-white/0 via-transparent to-white/0"></div>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-8 sm:py-10 text-center">
                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-500">Belum Ada Data</p>
                <p class="text-xs text-gray-400 mt-1">Mulai isi jurnal mengajar untuk melihat grafik aktivitas.</p>
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
                    Isi jurnal mengajar setelah sesi pembelajaran selesai.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Gunakan menu Cetak Laporan untuk rekap mengajar bulanan.
                </li>
                @if($homeroomClass)
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Anda adalah wali kelas {{ $homeroomClass->name }} — pantau presensi siswa.
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const agendaCanvas = document.getElementById('agendaChart');
        if (agendaCanvas && @json($monthlyStats ?? []).length > 0) {
            const agendaData = @json($monthlyStats ?? []);
            const isMobile = window.innerWidth < 640;

            const ctx = agendaCanvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, isMobile ? 200 : 260);
            gradient.addColorStop(0, 'rgba(139, 92, 246, 0.9)');
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0.7)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: agendaData.map(item => {
                        const [year, month] = item.month.split('-');
                        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                        return isMobile ? monthNames[parseInt(month) - 1].substring(0, 3) : monthNames[parseInt(month) - 1];
                    }),
                    datasets: [{
                        label: 'Jumlah Jurnal',
                        data: agendaData.map(item => item.total),
                        backgroundColor: gradient,
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: isMobile ? 0 : 1,
                        borderRadius: isMobile ? 4 : 6,
                        borderSkipped: false,
                        maxBarThickness: isMobile ? 28 : 48,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleFont: { size: isMobile ? 11 : 13, weight: '600' },
                            bodyFont: { size: isMobile ? 12 : 14 },
                            padding: isMobile ? 8 : 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw} jurnal`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { drawBorder: false, color: '#f1f5f9' },
                            ticks: {
                                stepSize: 1,
                                font: { size: isMobile ? 9 : 11, family: 'Inter' },
                                color: '#94a3b8',
                                padding: isMobile ? 4 : 8,
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: isMobile ? 9 : 11, weight: '600', family: 'Inter' },
                                color: '#64748b',
                                maxRotation: 0,
                            }
                        }
                    },
                    animation: {
                        duration: isMobile ? 600 : 800,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
