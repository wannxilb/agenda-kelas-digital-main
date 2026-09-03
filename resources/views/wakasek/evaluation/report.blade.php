@extends('layouts.wakasek')

@section('title', 'Laporan Evaluasi')
@section('header', 'Laporan Evaluasi')

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
    .anim-card { animation: fadeSlideUp 0.4s ease-out both; }
    .anim-card:nth-child(1) { animation-delay: 0.05s; }
    .anim-card:nth-child(2) { animation-delay: 0.1s; }
    .anim-card:nth-child(3) { animation-delay: 0.15s; }
    .anim-card:nth-child(4) { animation-delay: 0.2s; }
    .bar-animate { animation: barFill 1s ease-out both; animation-delay: 0.3s; }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6 pb-28 lg:pb-8">
    <!-- Header -->
    <div class="bg-white rounded-3xl p-4 sm:p-6 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="relative">
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('wakasek.evaluation.index') }}" class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-50 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg sm:rounded-xl flex items-center justify-center transition-colors shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-2xl font-black text-gray-900 tracking-tight truncate">Laporan Evaluasi</h1>
                    <p class="text-[10px] sm:text-sm text-gray-500 font-medium">Rekapitulasi hasil evaluasi belajar dan progres kurikulum per kelas</p>
                </div>
            </div>
            @if($activeYear)
                <div class="mt-2 sm:mt-0 sm:absolute sm:top-0 sm:right-0 px-3 py-1.5 sm:px-4 sm:py-2 bg-indigo-50 text-indigo-700 rounded-xl text-[9px] sm:text-xs font-bold uppercase tracking-widest border border-indigo-100 w-fit">
                    TA: {{ $activeYear->name }}
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Stats (horiz scroll on mobile) -->
    <div class="relative -mx-4 sm:mx-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0 sm:overflow-visible px-4 sm:px-0">
            <div class="anim-card snap-center shrink-0 w-[140px] sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-indigo-400 transition-all duration-300">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Kelas</p>
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                </div>
                <p class="text-lg sm:text-3xl font-black text-gray-900">{{ $classReport->count() }}</p>
            </div>
            <div class="anim-card snap-center shrink-0 w-[140px] sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-indigo-400 transition-all duration-300">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Siswa</p>
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                </div>
                <p class="text-lg sm:text-3xl font-black text-gray-900">{{ $totalStudents }}</p>
            </div>
            <div class="anim-card snap-center shrink-0 w-[140px] sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-indigo-400 transition-all duration-300">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Jurnal</p>
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                </div>
                <p class="text-lg sm:text-3xl font-black text-indigo-600">{{ $totalAgendas }}</p>
            </div>
            <div class="anim-card snap-center shrink-0 w-[140px] sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-emerald-400 transition-all duration-300">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Rata-rata</p>
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform duration-300 shrink-0">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <p class="text-lg sm:text-3xl font-black {{ $avgAttendance >= 80 ? 'text-emerald-600' : ($avgAttendance >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $avgAttendance }}%</p>
            </div>
        </div>
    </div>

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50">
            <h3 class="text-sm font-black text-gray-900 tracking-tight uppercase">Rekap Per Kelas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest w-12">No</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Kelas</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Siswa</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Jurnal</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Kehadiran</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($classReport->sortByDesc('attendance_percentage') as $index => $class)
                        @php
                            $pct = $class['attendance_percentage'];
                            if ($pct >= 80) {
                                $barColor = 'bg-emerald-500';
                                $textColor = 'text-emerald-700';
                                $bgLight = 'bg-emerald-50';
                                $borderColor = 'border-emerald-100';
                                $statusLabel = 'Baik';
                                $statusBg = 'bg-emerald-50 text-emerald-700';
                            } elseif ($pct >= 60) {
                                $barColor = 'bg-amber-500';
                                $textColor = 'text-amber-700';
                                $bgLight = 'bg-amber-50';
                                $borderColor = 'border-amber-100';
                                $statusLabel = 'Sedang';
                                $statusBg = 'bg-amber-50 text-amber-700';
                            } else {
                                $barColor = 'bg-rose-500';
                                $textColor = 'text-rose-700';
                                $bgLight = 'bg-rose-50';
                                $borderColor = 'border-rose-100';
                                $statusLabel = 'Perlu Perhatian';
                                $statusBg = 'bg-rose-50 text-rose-700';
                            }
                        @endphp
                        <tr class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-gray-400 font-bold text-xs">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-xl flex items-center justify-center text-xs font-black shadow-sm">
                                        {{ strtoupper(substr($class['name'], 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $class['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 bg-gray-50 text-gray-700 rounded-lg text-xs font-bold border border-gray-100">
                                    {{ $class['student_count'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold border border-indigo-100">
                                    {{ $class['agenda_count'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2.5">
                                    <div class="w-20 bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="{{ $barColor }} h-full rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-black {{ $textColor }} w-12 text-right">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 {{ $statusBg }} rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $borderColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">Belum ada data kelas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="lg:hidden space-y-3">
        @forelse($classReport->sortByDesc('attendance_percentage') as $index => $class)
            @php
                $pct = $class['attendance_percentage'];
                $statusLabel = $pct >= 80 ? 'Baik' : ($pct >= 60 ? 'Sedang' : 'Perlu Perhatian');
                $accentBorder = $pct >= 80 ? 'border-l-emerald-500' : ($pct >= 60 ? 'border-l-amber-400' : 'border-l-rose-500');
                $statusBadgeBg = $pct >= 80 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($pct >= 60 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');
                $progressBg = $pct >= 80 ? 'from-emerald-500 to-teal-500' : ($pct >= 60 ? 'from-amber-400 to-orange-500' : 'from-rose-500 to-red-500');
                $pctColor = $pct >= 80 ? 'text-emerald-600' : ($pct >= 60 ? 'text-amber-600' : 'text-rose-600');
            @endphp
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden border-l-4 {{ $accentBorder }} active:scale-[0.98] transition-all duration-200">
                <div class="p-3.5">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-xl flex items-center justify-center text-xs font-black shadow-lg shrink-0">
                            {{ strtoupper(substr($class['name'], 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-gray-900 truncate">{{ $class['name'] }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 mt-0.5">{{ $class['student_count'] }} siswa</p>
                                </div>
                                <span class="shrink-0 px-2 py-0.5 rounded-lg text-[9px] font-bold border {{ $statusBadgeBg }}">{{ $statusLabel }}</span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <div class="rounded-lg bg-indigo-50/60 border border-indigo-100/50 px-2.5 py-1.5 flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-md bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-[8px] font-black text-indigo-600/70 uppercase tracking-wider leading-none">Jurnal</p>
                                        <p class="text-xs font-black text-indigo-700 leading-none mt-0.5">{{ $class['agenda_count'] }}</p>
                                    </div>
                                </div>
                                <div class="rounded-lg bg-gray-50/60 border border-gray-100/50 px-2.5 py-1.5 flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-md bg-gray-100 flex items-center justify-center text-gray-600 shrink-0">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-[8px] font-black text-gray-500/70 uppercase tracking-wider leading-none">Siswa</p>
                                        <p class="text-xs font-black text-gray-700 leading-none mt-0.5">{{ $class['student_count'] }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Kehadiran</span>
                                    <span class="text-[11px] font-black {{ $pctColor }}">{{ $pct }}%</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-linear-to-r {{ $progressBg }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <p class="text-sm font-bold text-gray-500">Belum ada data kelas</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
