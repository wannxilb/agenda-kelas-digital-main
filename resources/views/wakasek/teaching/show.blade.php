@extends('layouts.wakasek')

@section('title', 'Detail Jurnal Mengajar - ' . $teacher->name)
@section('header', 'Detail Jurnal')

@php
    $compliance = $totalSchedules > 0 ? min(100, round(($totalAgendas / max(1, $totalSchedules)) * 100)) : 0;
    $maxMonthly = $monthlyStats->max() ?? 1;
@endphp

@push('styles')
<style>
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .anim-card { animation: fadeSlideUp 0.4s ease-out both; }
    .anim-card:nth-child(1) { animation-delay: 0.05s; }
    .anim-card:nth-child(2) { animation-delay: 0.1s; }
    .anim-card:nth-child(3) { animation-delay: 0.15s; }
    .anim-card:nth-child(4) { animation-delay: 0.2s; }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6 pb-28 lg:pb-8">
    <!-- Profile Header -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="relative">
            <a href="{{ route('wakasek.teaching.index') }}" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-gray-400 hover:text-indigo-600 transition-colors mb-3">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 sm:w-14 sm:h-14 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-2xl flex items-center justify-center text-lg sm:text-xl font-black shadow-lg shadow-indigo-200 shrink-0">
                    {{ strtoupper(substr($teacher->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-base sm:text-2xl font-black text-gray-900 tracking-tight truncate">{{ $teacher->name }}</h1>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5">
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-widest">NIP: {{ $teacher->nip ?? '-' }}</span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-400 hidden sm:inline">·</span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-gray-400">{{ $teacher->email }}</span>
                    </div>
                </div>
            </div>

            @if($subjects->count() > 0)
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach($subjects as $subject)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[9px] sm:text-[10px] font-bold border border-indigo-100">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        {{ $subject->name }}
                    </span>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="anim-card bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-indigo-400 transition-all duration-300">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Jurnal</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-3xl font-black text-indigo-600">{{ $totalAgendas }}</p>
            <p class="mt-1.5 text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catatan Aktivitas</p>
        </div>
        <div class="anim-card bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-gray-400 transition-all duration-300">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Jadwal</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-50 rounded-xl flex items-center justify-center text-gray-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-3xl font-black text-gray-900">{{ $totalSchedules }}</p>
            <p class="mt-1.5 text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jam Mengajar</p>
        </div>
        <div class="anim-card bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-amber-400 transition-all duration-300">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kelas</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-3xl font-black text-gray-900">{{ $uniqueClasses }}</p>
            <p class="mt-1.5 text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kelas Diampu</p>
        </div>
        <div class="anim-card bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-emerald-400 transition-all duration-300">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Minggu Ini</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-xl sm:text-3xl font-black {{ $weekCount > 0 ? 'text-emerald-600' : 'text-gray-300' }}">{{ $weekCount }}</p>
            <p class="mt-1.5 text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jurnal Diterbitkan</p>
        </div>
    </div>

    <!-- Monthly Trend + Compliance -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Monthly Trend Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-black text-gray-900 tracking-tight">Tren Aktivitas</h3>
                </div>
                <span class="text-[9px] sm:text-[10px] font-bold text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2.5 sm:px-3 py-1 rounded-lg">6 Bulan</span>
            </div>
            <div class="p-4 sm:p-6">
                @if($monthlyStats->count() > 0)
                    <div class="flex items-end gap-2 sm:gap-3 h-32 sm:h-40">
                        @foreach($monthlyStats->reverse() as $month => $count)
                            @php
                                $height = $maxMonthly > 0 ? round(($count / $maxMonthly) * 100) : 0;
                                $monthLabel = \Carbon\Carbon::parse($month . '-01')->translatedFormat('M');
                            @endphp
                            <div class="flex-1 self-stretch flex flex-col items-center justify-end gap-1 pb-1">
                                <span class="text-[9px] sm:text-[10px] font-black text-gray-700 leading-none">{{ $count }}</span>
                                <div class="w-full max-w-[28px] sm:max-w-[36px] rounded-t-lg transition-all duration-700 {{ $count > 0 ? 'bg-linear-to-t from-indigo-600 to-blue-500' : 'bg-gray-100' }}" style="height: {{ max(4, $height) }}%"></div>
                                <span class="text-[8px] sm:text-[9px] font-bold text-gray-400 uppercase tracking-wider">{{ $monthLabel }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-32 sm:h-40 text-center">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 rounded-xl flex items-center justify-center mb-2 sm:mb-3">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <p class="text-xs font-bold text-gray-400">Belum ada data tren</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Compliance Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm p-4 sm:p-6">
            <div class="flex items-center gap-2 mb-4 sm:mb-6">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center {{ $compliance >= 70 ? 'bg-emerald-50 text-emerald-600' : ($compliance >= 30 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-xs sm:text-sm font-black text-gray-900 tracking-tight">Kepatuhan Jurnal</h3>
            </div>

            <div class="flex flex-col items-center">
                <div class="relative w-28 h-28 sm:w-32 sm:h-32">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#f3f4f6" stroke-width="10"/>
                        <circle cx="60" cy="60" r="52" fill="none" stroke-width="10" stroke-linecap="round"
                                stroke="{{ $compliance >= 70 ? '#10b981' : ($compliance >= 30 ? '#f59e0b' : '#ef4444') }}"
                                stroke-dasharray="{{ 2 * 3.14159 * 52 }}"
                                stroke-dashoffset="{{ 2 * 3.14159 * 52 * (1 - $compliance / 100) }}"
                                class="transition-all duration-1000"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl sm:text-3xl font-black text-gray-900">{{ $compliance }}%</span>
                        <span class="text-[8px] sm:text-[9px] font-bold text-gray-400 uppercase tracking-widest">Komplian</span>
                    </div>
                </div>

                <div class="w-full mt-4 sm:mt-6 space-y-2 sm:space-y-3">
                    <div class="flex items-center justify-between p-2.5 sm:p-3 {{ $compliance >= 70 ? 'bg-emerald-50/50' : ($compliance >= 30 ? 'bg-amber-50/50' : 'bg-rose-50/50') }} rounded-xl">
                        <span class="text-[11px] sm:text-xs font-bold text-gray-500">Jurnal Ditulis</span>
                        <span class="text-xs sm:text-sm font-black text-gray-900">{{ $totalAgendas }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 sm:p-3 bg-gray-50 rounded-xl">
                        <span class="text-[11px] sm:text-xs font-bold text-gray-500">Jadwal Aktif</span>
                        <span class="text-xs sm:text-sm font-black text-gray-900">{{ $totalSchedules }}</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 sm:p-3 {{ $todayCount > 0 ? 'bg-emerald-50/50' : 'bg-gray-50' }} rounded-xl">
                        <span class="text-[11px] sm:text-xs font-bold text-gray-500">Hari Ini</span>
                        <span class="text-xs sm:text-sm font-black {{ $todayCount > 0 ? 'text-emerald-600' : 'text-gray-300' }}">{{ $todayCount }} jurnal</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agenda List -->
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-600">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-xs sm:text-sm font-black text-gray-900 tracking-tight">Daftar Jurnal</h3>
            </div>
            <form method="GET" action="{{ route('wakasek.teaching.show', $teacher->id) }}" class="relative group">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari jurnal..."
                       class="w-full sm:w-56 pl-9 pr-4 py-2 bg-gray-50 border-none rounded-xl text-xs font-semibold text-gray-700 focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner placeholder:text-gray-400">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-indigo-600 transition-colors pointer-events-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </form>
        </div>

        {{-- Desktop View --}}
        <div class="hidden lg:block">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Waktu</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kelas & Mapel</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest min-w-[300px]">Detail Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($agendas as $agenda)
                            @php
                                $isToday = \Carbon\Carbon::parse($agenda->date)->isToday();
                                $isYesterday = \Carbon\Carbon::parse($agenda->date)->isYesterday();
                            @endphp
                            <tr class="group hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-5 align-top">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 {{ $isToday ? 'bg-indigo-600 shadow-lg shadow-indigo-200' : ($isYesterday ? 'bg-gray-700' : 'bg-gray-200') }} text-white rounded-xl flex flex-col items-center justify-center flex-shrink-0">
                                            <span class="text-[7px] font-black uppercase leading-none opacity-70 mb-0.5">{{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('M') }}</span>
                                            <span class="text-xs font-black leading-none">{{ \Carbon\Carbon::parse($agenda->date)->format('d') }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                {{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('d M Y') }}
                                            </span>
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">
                                                {{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('l') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 align-top">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-black text-gray-900">{{ $agenda->class->name ?? '-' }}</span>
                                        <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest">{{ $agenda->subject->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-2">
                                        <p class="text-sm text-gray-800 font-bold leading-relaxed">{{ $agenda->title }}</p>
                                        @if($agenda->description)
                                            <p class="text-xs text-gray-500 leading-relaxed line-clamp-2">{{ strip_tags($agenda->description) }}</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-500">Belum ada catatan mengajar</p>
                                            <p class="text-xs text-gray-400 mt-1">Jurnal akan muncul setelah guru menerbitkan catatan</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile View --}}
        <div class="lg:hidden space-y-2 p-3 sm:p-4">
            @forelse($agendas as $agenda)
                @php
                    $isToday = \Carbon\Carbon::parse($agenda->date)->isToday();
                    $isYesterday = \Carbon\Carbon::parse($agenda->date)->isYesterday();
                    $dayLabel = $isToday ? 'Hari Ini' : ($isYesterday ? 'Kemarin' : null);
                @endphp
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden border-l-4 {{ $isToday ? 'border-l-indigo-500' : ($isYesterday ? 'border-l-gray-400' : 'border-l-gray-200') }}">
                    <div class="p-3.5">
                        <div class="flex items-start gap-2.5">
                            <div class="w-9 h-9 {{ $isToday ? 'bg-indigo-600' : ($isYesterday ? 'bg-gray-600' : 'bg-gray-200') }} text-white rounded-lg flex flex-col items-center justify-center shrink-0">
                                <span class="text-[6px] font-black uppercase leading-none opacity-70 mb-0.5">{{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('M') }}</span>
                                <span class="text-[10px] font-black leading-none">{{ \Carbon\Carbon::parse($agenda->date)->format('d') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-black text-gray-900 truncate">{{ $agenda->class->name ?? '-' }}</span>
                                            @if($dayLabel)
                                                <span class="shrink-0 px-1.5 py-0.5 {{ $isToday ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500' }} rounded text-[8px] font-bold">{{ $dayLabel }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest">{{ $agenda->subject->name ?? '-' }}</span>
                                            @if($agenda->room)
                                                <span class="flex items-center gap-0.5 text-[8px] font-bold text-gray-400">
                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                    {{ $agenda->room }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 bg-gray-50/80 rounded-lg p-2.5 border border-gray-100/50">
                                    <p class="text-xs font-bold text-gray-800 leading-snug">{{ $agenda->title }}</p>
                                    @if($agenda->description)
                                        <p class="text-[10px] text-gray-500 leading-relaxed mt-1 line-clamp-2">{{ strip_tags($agenda->description) }}</p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 mt-2">
                                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-[9px] font-medium text-gray-400">{{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('l, d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 p-10 sm:p-12 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-500">Belum ada catatan mengajar</p>
                            <p class="text-xs text-gray-400 mt-1">Jurnal akan muncul setelah guru menerbitkan catatan</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($agendas->hasPages())
            <div class="px-4 sm:px-6 py-4 border-t border-gray-50">
                {{ $agendas->appends(request()->query())->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>
</div>
@endsection
