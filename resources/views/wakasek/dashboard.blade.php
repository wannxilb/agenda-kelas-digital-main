@extends('layouts.wakasek')

@section('title', 'Dashboard Kurikulum')
@section('header', 'Dashboard')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
@endphp

<div class="space-y-4 sm:space-y-6 pb-24" x-data="{ tab: 'monitoring' }">

    {{-- Welcome Banner --}}
    <div class="relative overflow-hidden bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-blue-200/60">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-center justify-between gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-blue-100/90 tracking-wide">{{ $greeting }},</p>
                <h1 class="text-lg sm:text-2xl font-bold truncate">{{ Auth::user()->name }}</h1>
                <p class="mt-0.5 text-blue-100 text-[11px] sm:text-sm">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                        </svg>
                        Wakasek Kurikulum
                    </span>
                    <span class="inline-flex items-center px-2 sm:px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-medium backdrop-blur-sm ring-1 ring-white/10">
                        <svg class="w-3 h-3 mr-1 sm:mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Monitoring Akademik
                    </span>
                </div>
            </div>
            <div class="flex-shrink-0">
                <div class="w-14 h-14 sm:w-20 sm:h-20 bg-white/15 rounded-2xl flex items-center justify-center backdrop-blur-sm ring-1 ring-white/20 shadow-inner">
                    <span class="text-2xl sm:text-4xl font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards: horizontal scroll on mobile --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0 scrollbar-hide">

            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center relative overflow-hidden">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 z-10">Total Guru</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900 z-10">{{ $total_teachers }}</p>
                <svg class="absolute -bottom-2 -right-2 w-14 h-14 text-gray-50 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14z"></path></svg>
            </div>

            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Total Kelas</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $total_classes }}</p>
            </div>

            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Mata Pelajaran</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $total_subjects }}</p>
            </div>

            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-sky-50/50 rounded-2xl border border-sky-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Agenda Hari Ini</p>
                <p class="text-xl sm:text-2xl font-black text-sky-600">{{ $agendas_today }}</p>
            </div>

        </div>
        <p class="sm:hidden mt-2 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Pending Teacher Status Alert --}}
    @if(isset($pendingTeacherStatuses) && $pendingTeacherStatuses > 0)
    <a href="{{ route('wakasek.teacher-status.index') }}"
       class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3.5 hover:bg-amber-100/70 transition-all group">
        <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-white shrink-0 shadow-md shadow-amber-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-amber-900">{{ $pendingTeacherStatuses }} pengajuan izin/sakit/tugas luar menunggu persetujuan</p>
            <p class="text-[11px] text-amber-700/80 font-medium">Klik untuk meninjau dan memproses pengajuan guru.</p>
        </div>
        <svg class="w-5 h-5 text-amber-400 shrink-0 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
    </a>
    @endif

    {{-- Mobile Tab Switcher --}}
    <div class="lg:hidden flex bg-gray-100 rounded-2xl p-1 gap-1">
        <button type="button" @click="tab = 'monitoring'"
            :class="tab === 'monitoring' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Monitoring
        </button>
        <button type="button" @click="tab = 'kinerja'"
            :class="tab === 'kinerja' ? 'bg-white shadow-sm text-indigo-700' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold transition-all duration-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            Kinerja
        </button>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">

        {{-- Monitoring Agenda --}}
        <div :class="{ 'hidden': tab !== 'monitoring' }" class="lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-gray-800">Monitoring Agenda</h3>
                        <p class="text-[11px] sm:text-xs text-gray-500">Jurnal mengajar hari ini</p>
                    </div>
                </div>
                <a href="{{ route('wakasek.monitoring.agenda') }}" class="text-[11px] sm:text-xs font-medium text-blue-600 hover:underline active:text-blue-800">Detail →</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-4 sm:px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kelas</th>
                            <th class="px-4 sm:px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Terisi</th>
                            <th class="px-4 sm:px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($monitoring_classes->take(5) as $class)
                        <tr class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-4 sm:px-5 py-3.5">
                                <span class="text-sm font-semibold text-gray-900">{{ $class->name }}</span>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $class->monitoring_total_slots }} jadwal hari ini</p>
                            </td>
                            <td class="px-4 sm:px-5 py-3.5">
                                <span class="text-sm font-semibold text-gray-700">{{ $class->monitoring_filled_slots }}/{{ $class->monitoring_total_slots }}</span>
                                @if($class->monitoring_empty_upcoming_slots > 0)
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $class->monitoring_empty_upcoming_slots }} menunggu</p>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3.5">
                                @if($class->monitoring_status === 'jamkos')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-700 rounded-full text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        JAMKOS
                                    </span>
                                @elseif($class->monitoring_status === 'belum_terisi')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        BELUM
                                    </span>
                                @elseif($class->monitoring_status === 'kosong')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full text-[10px] font-bold">
                                        KOSONG
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        LENGKAP
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 sm:px-5 py-10 text-center">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs sm:text-sm text-gray-500 italic">Belum ada agenda hari ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <a href="{{ route('wakasek.monitoring.agenda') }}" class="block w-full py-3 text-center text-[11px] font-bold text-blue-600 hover:bg-blue-50 transition-colors border-t border-gray-50 tracking-wider">
                Lihat Semua Kelas →
            </a>
        </div>

        {{-- Kinerja Mengajar --}}
        <div :class="{ 'hidden': tab !== 'kinerja' }" class="lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="font-bold text-sm sm:text-base text-gray-800">Kinerja Mengajar</h3>
                </div>
                <a href="{{ route('wakasek.teaching.index') }}" class="text-[11px] sm:text-xs font-medium text-blue-600 hover:underline active:text-blue-800">Detail →</a>
            </div>

            <div class="divide-y divide-gray-50">
                @forelse($latest_agendas->take(5) as $agenda)
                <div class="px-4 sm:px-5 py-3 sm:py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-[10px] font-bold shrink-0">
                            {{ strtoupper(substr($agenda->teacher->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $agenda->teacher->name }}</p>
                            <p class="text-[11px] text-gray-500 truncate">{{ $agenda->subject->name }} • {{ $agenda->class->name }}</p>
                        </div>
                        <span class="text-[10px] text-gray-400 shrink-0">{{ $agenda->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @empty
                <div class="px-4 sm:px-5 py-8 sm:py-10 text-center">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="text-xs sm:text-sm text-gray-500 italic">Belum ada aktivitas mengajar</p>
                </div>
                @endforelse
            </div>

            <a href="{{ route('wakasek.teaching.index') }}" class="block w-full py-3 text-center text-[11px] font-bold text-indigo-600 hover:bg-indigo-50 transition-colors border-t border-gray-50 tracking-wider">
                Lihat Semua Guru →
            </a>
        </div>
    </div>

    {{-- Keaktifan Kelas --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-gray-800">Keaktifan Kelas</h3>
                    <p class="text-xs text-gray-500 truncate">Top 5 berdasarkan agenda publish</p>
                </div>
            </div>
            <span class="shrink-0 text-[10px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-lg">Top 5</span>
        </div>
        <div class="px-4 sm:px-6 py-4 sm:py-5 space-y-3">
            @php $maxCount = $class_stats->max('agendas_count') ?: 1; @endphp
            @forelse($class_stats->sortByDesc('agendas_count')->take(5) as $stat)
            @php
                $progress = $stat->agendas_count > 0 ? max(8, ($stat->agendas_count / $maxCount) * 100) : 0;
            @endphp
            <div class="rounded-xl border border-gray-100 bg-gray-50/40 px-3 py-3 sm:px-4 sm:py-3.5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <span class="block text-sm font-bold text-gray-800 truncate">{{ $stat->name }}</span>
                        <span class="block text-[10px] font-semibold text-gray-400 mt-0.5">Peringkat #{{ $loop->iteration }}</span>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="block text-sm font-black text-indigo-700">{{ $stat->agendas_count }}</span>
                        <span class="block text-[10px] font-semibold text-gray-400">Agenda</span>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-3">
                    <div class="h-2.5 flex-1 bg-white rounded-full overflow-hidden ring-1 ring-gray-100">
                        <div class="h-full rounded-full transition-all duration-700 bg-linear-to-r from-sky-500 via-indigo-500 to-emerald-500" style="width: {{ $progress }}%"></div>
                    </div>
                    <span class="w-10 text-right text-[10px] font-bold text-gray-500">{{ round(($stat->agendas_count / $maxCount) * 100) }}%</span>
                </div>
            </div>
            @empty
            <div class="py-6 text-center">
                <p class="text-xs sm:text-sm text-gray-400 italic">Belum ada data keaktifan</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Informasi Box --}}
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
                    Pantau kelengkapan jurnal mengajar guru setiap hari melalui menu Monitoring.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Evaluasi kinerja pengajaran guru secara berkala untuk menjaga mutu pembelajaran.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Gunakan menu Laporan Presensi untuk rekap kehadiran siswa bulanan.
                </li>
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
@endsection
