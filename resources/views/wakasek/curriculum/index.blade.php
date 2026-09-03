{{-- resources/views/wakasek/curriculum/index.blade.php --}}
@extends('layouts.wakasek')

@section('title', 'Progres Kurikulum')
@section('header', 'Progres Kurikulum')

@push('styles')
<style>
    .curriculum-scroll::-webkit-scrollbar { display: none; }
    .curriculum-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes curriculumRise {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .curriculum-card { animation: curriculumRise .34s ease-out both; }
    .curriculum-card:nth-child(1) { animation-delay: .03s; }
    .curriculum-card:nth-child(2) { animation-delay: .06s; }
    .curriculum-card:nth-child(3) { animation-delay: .09s; }
    .curriculum-card:nth-child(4) { animation-delay: .12s; }
</style>
@endpush

@section('content')
@php
    $sortedClasses = $classes->sortByDesc('agendas_count')->values();
    $sortedSubjects = $subjects->sortByDesc('agendas_count')->values();
    $maxClassSessions = max(1, (int) $sortedClasses->max('agendas_count'));
    $maxSubjectSessions = max(1, (int) $sortedSubjects->max('agendas_count'));
    $totalClassSessions = (int) $classes->sum('agendas_count');
    $totalSubjectSessions = (int) $subjects->sum('agendas_count');
    $activeClasses = $classes->where('agendas_count', '>', 0)->count();
    $activeSubjects = $subjects->where('agendas_count', '>', 0)->count();
@endphp

<div
    x-data="{ tab: 'classes', query: '' }"
    class="space-y-4 sm:space-y-7 pb-24 sm:pb-8"
>
    {{-- Mobile Header --}}
    <div class="sm:hidden bg-white rounded-3xl p-5 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="relative flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg font-black text-gray-900 tracking-tight truncate">Progres Kurikulum</h1>
                    <p class="text-xs text-gray-500 font-medium leading-relaxed">Pantau sesi agenda per kelas dan mata pelajaran</p>
                </div>
            </div>
            <a href="{{ route('wakasek.curriculum.progress') }}"
               class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-indigo-200/50 active:scale-95 transition-all"
               title="Detail progres">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>

        <div class="relative mt-4 grid grid-cols-3 gap-2">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Sesi</p>
                <p class="mt-1 text-xl font-black leading-none text-indigo-600">{{ $totalClassSessions }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Kelas</p>
                <p class="mt-1 text-xl font-black leading-none text-emerald-600">{{ $activeClasses }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-3">
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">Mapel</p>
                <p class="mt-1 text-xl font-black leading-none text-blue-600">{{ $activeSubjects }}</p>
            </div>
        </div>
    </div>

    {{-- Desktop Header --}}
    <div class="hidden sm:block bg-white rounded-3xl p-6 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="relative flex items-center justify-between gap-6">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl font-black text-gray-900 tracking-tight">Progres Kurikulum</h1>
                    <p class="text-sm text-gray-500 font-medium">Ketuntasan materi pembelajaran berdasarkan agenda per kelas dan mata pelajaran.</p>
                </div>
            </div>
            <a href="{{ route('wakasek.curriculum.progress') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-indigo-200/50 hover:bg-indigo-700 active:scale-95 transition-all duration-300">
                Detail Progres
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Controls --}}
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm p-3 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-gray-300 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
                <input x-model.debounce.150ms="query" type="search" placeholder="Cari kelas atau mata pelajaran..."
                       class="w-full h-11 pl-10 pr-4 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-semibold text-gray-700 placeholder:text-gray-400">
            </div>
            <div class="grid grid-cols-2 rounded-xl bg-gray-100 p-1 sm:hidden">
                <button type="button" @click="tab='classes'"
                        :class="tab === 'classes' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500'"
                        class="h-9 rounded-lg text-[11px] font-black uppercase tracking-widest transition-all">
                    Kelas
                </button>
                <button type="button" @click="tab='subjects'"
                        :class="tab === 'subjects' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500'"
                        class="h-9 rounded-lg text-[11px] font-black uppercase tracking-widest transition-all">
                    Mapel
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Content --}}
    <div class="sm:hidden">
        <div x-show="tab === 'classes'" class="space-y-3">
            @forelse($sortedClasses as $class)
                @php
                    $percent = $maxClassSessions > 0 ? round(($class->agendas_count / $maxClassSessions) * 100) : 0;
                    $barWidth = $class->agendas_count > 0 ? max(8, $percent) : 0;
                    $statusLabel = $class->agendas_count >= 20 ? 'Tinggi' : ($class->agendas_count >= 8 ? 'Stabil' : ($class->agendas_count > 0 ? 'Mulai' : 'Belum Ada'));
                    $statusClass = $class->agendas_count >= 20 ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : ($class->agendas_count >= 8 ? 'bg-sky-50 text-sky-700 border-sky-100' : ($class->agendas_count > 0 ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-gray-100 text-gray-500 border-gray-200'));
                @endphp
                <article
                    x-show="query === '' || '{{ \Illuminate\Support\Str::lower($class->name) }}'.includes(query.toLowerCase())"
                    x-transition
                    class="curriculum-card bg-white rounded-3xl border border-gray-100 shadow-sm p-4 active:scale-[0.985] transition-all">
                    <div class="flex items-start gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center text-sm font-black shrink-0">
                            {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <h2 class="text-sm font-black text-gray-900 leading-snug truncate">{{ $class->name }}</h2>
                                <span class="shrink-0 px-2 py-1 rounded-lg border text-[9px] font-black uppercase {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                            <p class="mt-0.5 text-[10px] font-semibold text-gray-400">{{ $class->agendas_count }} sesi agenda tercatat</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Dibanding kelas tertinggi</span>
                            <span class="text-[10px] font-black text-gray-900">{{ $percent }}%</span>
                        </div>
                        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-linear-to-r from-indigo-500 to-blue-500 transition-all duration-700" style="width: {{ $barWidth }}%"></div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-3xl border border-gray-100 p-10 text-center">
                    <p class="text-sm font-bold text-gray-400">Belum ada data kelas.</p>
                </div>
            @endforelse
        </div>

        <div x-show="tab === 'subjects'" x-cloak class="space-y-3">
            @forelse($sortedSubjects as $subject)
                @php
                    $percent = $maxSubjectSessions > 0 ? round(($subject->agendas_count / $maxSubjectSessions) * 100) : 0;
                    $barWidth = $subject->agendas_count > 0 ? max(8, $percent) : 0;
                @endphp
                <article
                    x-show="query === '' || '{{ \Illuminate\Support\Str::lower($subject->name) }}'.includes(query.toLowerCase())"
                    x-transition
                    class="curriculum-card bg-white rounded-3xl border border-gray-100 shadow-sm p-4 active:scale-[0.985] transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-sky-50 text-sky-700 border border-sky-100 flex items-center justify-center text-sm font-black shrink-0">
                            {{ strtoupper(substr($subject->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-black text-gray-900 leading-snug truncate">{{ $subject->name }}</h2>
                            <p class="mt-0.5 text-[10px] font-semibold text-gray-400">Ranking #{{ $loop->iteration }} dari {{ $subjects->count() }} mapel</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-lg font-black text-sky-700 leading-none">{{ $subject->agendas_count }}</p>
                            <p class="text-[9px] font-black text-gray-400 uppercase">sesi</p>
                        </div>
                    </div>
                    <div class="mt-4 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-linear-to-r from-blue-500 to-indigo-500 transition-all duration-700" style="width: {{ $barWidth }}%"></div>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-3xl border border-gray-100 p-10 text-center">
                    <p class="text-sm font-bold text-gray-400">Belum ada data mata pelajaran.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Desktop Content --}}
    <div class="hidden sm:grid grid-cols-1 lg:grid-cols-2 gap-7">
        <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-7 py-5 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Ketuntasan Per Kelas</h3>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">{{ $activeClasses }} kelas aktif dari {{ $classes->count() }}</p>
                </div>
                <span class="text-[10px] font-black text-emerald-700 uppercase tracking-widest bg-emerald-50 px-3 py-1.5 rounded-xl border border-emerald-100">Real-time</span>
            </div>
            <div class="p-7 space-y-5">
                @foreach($sortedClasses as $class)
                    @php
                        $percent = $maxClassSessions > 0 ? round(($class->agendas_count / $maxClassSessions) * 100) : 0;
                        $barWidth = $class->agendas_count > 0 ? max(8, $percent) : 0;
                    @endphp
                    <div class="group">
                        <div class="flex justify-between items-center gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 shrink-0 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-[10px] font-black group-hover:scale-105 transition-transform">
                                    {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </div>
                                <span class="text-sm font-bold text-gray-700 group-hover:text-blue-700 transition-colors truncate">{{ $class->name }}</span>
                            </div>
                            <span class="text-xs font-black text-indigo-600 whitespace-nowrap">{{ $class->agendas_count }} Sesi</span>
                        </div>
                        <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-linear-to-r from-indigo-500 to-blue-500 rounded-full transition-all duration-700" style="width: {{ $barWidth }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-7 py-5 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-gray-900">Ketuntasan Per Mapel</h3>
                    <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mt-1">Top 10 agenda terbanyak</p>
                </div>
                <span class="text-[10px] font-black text-sky-700 uppercase tracking-widest bg-sky-50 px-3 py-1.5 rounded-xl border border-sky-100">Statistik</span>
            </div>
            <div class="p-7 space-y-5">
                @foreach($sortedSubjects->take(10) as $subject)
                    @php
                        $percent = $maxSubjectSessions > 0 ? round(($subject->agendas_count / $maxSubjectSessions) * 100) : 0;
                        $barWidth = $subject->agendas_count > 0 ? max(8, $percent) : 0;
                    @endphp
                    <div class="group">
                        <div class="flex justify-between items-center gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 shrink-0 bg-sky-50 text-sky-700 border border-sky-100 rounded-xl flex items-center justify-center text-[10px] font-black group-hover:scale-105 transition-transform">
                                    {{ strtoupper(substr($subject->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-bold text-gray-700 group-hover:text-sky-700 transition-colors truncate">{{ $subject->name }}</span>
                            </div>
                            <span class="text-xs font-black text-sky-700 whitespace-nowrap">{{ $subject->agendas_count }} Sesi</span>
                        </div>
                        <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-linear-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-700" style="width: {{ $barWidth }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="sm:hidden pt-1">
        <a href="{{ route('wakasek.curriculum.progress') }}"
           class="flex h-12 items-center justify-center gap-2 rounded-2xl bg-indigo-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-200/50 active:scale-[0.98] transition-all">
            Buka Detail Progres
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
</div>
@endsection
