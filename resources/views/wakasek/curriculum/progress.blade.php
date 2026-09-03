{{-- resources/views/wakasek/curriculum/progress.blade.php --}}
@extends('layouts.wakasek')

@section('title', 'Detail Progres Kurikulum')
@section('header', 'Progres Kurikulum')

@push('styles')
<style>
    @keyframes progressRise {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .progress-card { animation: progressRise .34s ease-out both; }
    .progress-card:nth-child(1) { animation-delay: .03s; }
    .progress-card:nth-child(2) { animation-delay: .06s; }
    .progress-card:nth-child(3) { animation-delay: .09s; }
    .progress-card:nth-child(4) { animation-delay: .12s; }
</style>
@endpush

@section('content')
@php
    $items = $progress->sortBy('class.name')->values();
    $totalSessions = (int) $items->sum('total');
    $totalClasses = $items->pluck('class_id')->filter()->unique()->count();
    $totalSubjects = $items->pluck('subject_id')->filter()->unique()->count();
    $maxSessions = max(1, (int) $items->max('total'));
    $avgEstimate = $items->count() > 0 ? round($items->avg(fn($item) => min(100, $item->total * 5))) : 0;
@endphp

<div x-data="{ query: '' }" class="space-y-4 sm:space-y-6 pb-24 lg:pb-8">
    {{-- Header --}}
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="relative flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('wakasek.curriculum.index') }}"
                   class="w-9 h-9 sm:w-10 sm:h-10 bg-gray-50 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl flex items-center justify-center transition-colors shrink-0 border border-gray-100">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-2xl font-black text-gray-900 tracking-tight truncate">Detail Progres Kurikulum</h1>
                    <p class="text-[10px] sm:text-sm text-gray-500 font-medium leading-relaxed">Rincian sesi mengajar per kelas dan mata pelajaran</p>
                </div>
            </div>
            <span class="hidden sm:inline-flex px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-xl text-[10px] font-black uppercase tracking-widest border border-indigo-100">
                {{ $items->count() }} data
            </span>
        </div>
    </div>

    {{-- Summary --}}
    <div class="relative -mx-4 sm:mx-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 overflow-x-auto scrollbar-hide snap-x snap-mandatory pb-2 sm:pb-0 sm:overflow-visible px-4 sm:px-0">
            <div class="progress-card snap-center shrink-0 w-34.5 sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Sesi</p>
                <p class="mt-1 text-xl sm:text-3xl font-black text-indigo-600">{{ $totalSessions }}</p>
            </div>
            <div class="progress-card snap-center shrink-0 w-34.5 sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Kelas</p>
                <p class="mt-1 text-xl sm:text-3xl font-black text-gray-900">{{ $totalClasses }}</p>
            </div>
            <div class="progress-card snap-center shrink-0 w-34.5 sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Mapel</p>
                <p class="mt-1 text-xl sm:text-3xl font-black text-blue-600">{{ $totalSubjects }}</p>
            </div>
            <div class="progress-card snap-center shrink-0 w-34.5 sm:w-auto bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Rata-rata</p>
                <p class="mt-1 text-xl sm:text-3xl font-black {{ $avgEstimate >= 75 ? 'text-emerald-600' : ($avgEstimate >= 40 ? 'text-amber-600' : 'text-rose-600') }}">{{ $avgEstimate }}%</p>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-sm p-3 sm:p-4">
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
            </svg>
            <input x-model.debounce.150ms="query" type="search" placeholder="Cari kelas atau mata pelajaran..."
                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-semibold text-gray-700 placeholder:text-gray-400">
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="lg:hidden space-y-3">
        @forelse($items as $item)
            @php
                $className = $item->class->name ?? '-';
                $subjectName = $item->subject->name ?? '-';
                $estimate = min(100, $item->total * 5);
                $relative = $maxSessions > 0 ? round(($item->total / $maxSessions) * 100) : 0;
                $barWidth = $item->total > 0 ? max(8, $estimate) : 0;
                $tone = $estimate >= 75 ? 'emerald' : ($estimate >= 40 ? 'amber' : 'rose');
                $statusLabel = $estimate >= 75 ? 'Tinggi' : ($estimate >= 40 ? 'Bertahap' : 'Perlu Dipantau');
                $searchText = \Illuminate\Support\Str::lower($className . ' ' . $subjectName);
            @endphp
            <article
                x-show="query === '' || @js($searchText).includes(query.toLowerCase())"
                x-transition
                class="progress-card bg-white rounded-2xl border border-gray-100 shadow-sm p-4 active:scale-[0.985] transition-all">
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-sm font-black shrink-0">
                        {{ strtoupper(substr($subjectName, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h2 class="text-sm font-black text-gray-900 truncate">{{ $subjectName }}</h2>
                                <p class="mt-0.5 text-[10px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-lg px-2 py-0.5 w-fit">{{ $className }}</p>
                            </div>
                            <span class="shrink-0 px-2 py-1 rounded-lg border text-[9px] font-black uppercase {{ $tone === 'emerald' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : ($tone === 'amber' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-rose-50 text-rose-700 border-rose-100') }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="rounded-xl bg-gray-50 border border-gray-100 px-3 py-2.5">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Sesi</p>
                        <p class="mt-1 text-lg font-black text-gray-900 leading-none">{{ $item->total }}</p>
                    </div>
                    <div class="rounded-xl bg-indigo-50 border border-indigo-100 px-3 py-2.5">
                        <p class="text-[9px] font-black text-indigo-500/70 uppercase tracking-widest">Ranking Relatif</p>
                        <p class="mt-1 text-lg font-black text-indigo-700 leading-none">{{ $relative }}%</p>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Estimasi Ketuntasan</span>
                        <span class="text-[10px] font-black text-blue-600">{{ $estimate }}%</span>
                    </div>
                    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-linear-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-700" style="width: {{ $barWidth }}%"></div>
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
                <p class="text-sm font-bold text-gray-400">Belum ada data progres mata pelajaran.</p>
            </div>
        @endforelse
    </div>

    {{-- Desktop Table --}}
    <div class="hidden lg:block bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black text-gray-900 tracking-tight uppercase">Rincian Per Kelas dan Mapel</h3>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">{{ $items->count() }} kombinasi pembelajaran</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest w-48">Kelas</th>
                        <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mata Pelajaran</th>
                        <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center w-40">Total Sesi</th>
                        <th class="px-8 py-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest w-64">Indikator Ketuntasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 bg-white">
                    @forelse($items as $item)
                        @php
                            $className = $item->class->name ?? '-';
                            $subjectName = $item->subject->name ?? '-';
                            $estimate = min(100, $item->total * 5);
                            $searchText = \Illuminate\Support\Str::lower($className . ' ' . $subjectName);
                        @endphp
                        <tr x-show="query === '' || @js($searchText).includes(query.toLowerCase())" x-transition class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-8 py-6">
                                <span class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $className }}</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-[10px] font-black group-hover:scale-110 transition-transform">
                                        {{ strtoupper(substr($subjectName, 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700">{{ $subjectName }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-xl border border-emerald-100">{{ $item->total }} Sesi</span>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col gap-2">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[10px] text-gray-400 font-bold uppercase">Estimasi</span>
                                        <span class="text-[10px] font-black text-blue-600">{{ $estimate }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                                        <div class="h-full bg-linear-to-r from-blue-500 to-indigo-500 rounded-full" style="width: {{ $estimate }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-12 text-center text-gray-400 font-medium italic">Belum ada data progres mata pelajaran yang tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
