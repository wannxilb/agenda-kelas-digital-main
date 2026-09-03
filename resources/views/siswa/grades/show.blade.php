@extends('layouts.siswa')

@section('title', 'Detail Nilai Tugas')
@section('header', 'Detail Nilai Tugas')
@section('content-padding', 'pt-4 sm:pt-6 pb-24 lg:pb-8')

@php
    $assignment = $grade->assignment;
    $maxScore = (float) ($assignment->max_score ?? 100);
    $percentage = $grade->score !== null && $maxScore > 0 ? round(($grade->score / $maxScore) * 100) : null;
    $scoreColor = $percentage === null ? '' : ($percentage >= 80 ? 'emerald' : ($percentage >= 60 ? 'amber' : 'rose'));
    $scoreBadge = $scoreColor === 'emerald'
        ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
        : ($scoreColor === 'amber' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-rose-50 text-rose-700 border-rose-100');
    $avgScore = $assignment->grades->pluck('score')->filter()->avg();
    $isOverdue = $assignment->due_date && $assignment->due_date->lt(now()->startOfDay()) && $grade->score === null;
    $graded = $assignment->grades->filter(fn ($g) => $g->score !== null);
@endphp

@section('content')
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-6">

    {{-- Breadcrumb + Title --}}
    <div>
        <nav class="mb-3 flex items-center gap-2 text-xs font-semibold text-gray-400">
            <a href="{{ route('siswa.grades.index') }}" class="hover:text-emerald-600 transition-colors">Nilai Tugas</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-700">Detail</span>
        </nav>
        <h1 class="text-xl sm:text-2xl font-black text-gray-900">{{ $assignment->title }}</h1>
        @if($assignment->description)
            <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ $assignment->description }}</p>
        @endif
        <div class="mt-3 flex flex-wrap gap-2">
            <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-100">{{ $assignment->subject->name ?? '-' }}</span>
            <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">{{ $assignment->class->name ?? '-' }}</span>
            <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">Maks {{ number_format($maxScore, 0) }}</span>
            @if($assignment->assigned_date)
                <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">Tugas {{ $assignment->assigned_date->format('d/m/Y') }}</span>
            @endif
            @if($assignment->due_date)
                <span class="inline-flex items-center px-2.5 py-1 {{ $isOverdue ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-gray-100 text-gray-600 border-transparent' }} rounded-lg text-xs font-bold border">
                    Batas {{ $assignment->due_date->format('d/m/Y') }}{{ $isOverdue ? ' - lewat' : '' }}
                </span>
            @endif
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-5 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center relative overflow-hidden">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 z-10">Nilai Kamu</p>
                @if($grade->score !== null)
                    <p class="text-xl sm:text-2xl font-black text-gray-900 z-10">{{ number_format((float) $grade->score, 1) }}</p>
                    <p class="mt-0.5 text-[10px] text-gray-400 z-10">dari {{ number_format($maxScore, 0) }}</p>
                @else
                    <p class="text-xl sm:text-2xl font-black text-gray-300 z-10">-</p>
                    <p class="mt-0.5 text-[10px] text-gray-400 z-10">belum dinilai</p>
                @endif
                <svg class="absolute -bottom-2 -right-2 w-14 h-14 text-emerald-50 opacity-60" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div class="snap-center shrink-0 w-[130px] sm:w-auto {{ $percentage !== null ? ($scoreColor === 'emerald' ? 'bg-emerald-50/70 border-emerald-100' : ($scoreColor === 'amber' ? 'bg-amber-50/70 border-amber-100' : 'bg-rose-50/70 border-rose-100')) : 'bg-gray-50/70 border-gray-100' }} rounded-2xl border p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pencapaian</p>
                <p class="text-xl sm:text-2xl font-black {{ $scoreColor === 'rose' ? 'text-rose-700' : ($scoreColor === 'amber' ? 'text-amber-700' : 'text-emerald-700') }}">{{ $percentage !== null ? $percentage . '%' : '-' }}</p>
                <p class="mt-1 text-[10px] {{ $scoreColor === 'rose' ? 'text-rose-600/70' : ($scoreColor === 'amber' ? 'text-amber-600/70' : 'text-emerald-600/70') }}">{{ $percentage !== null ? ($percentage >= 80 ? 'Baik sekali' : ($percentage >= 60 ? 'Cukup baik' : 'Perlu ditingkatkan')) : '' }}</p>
            </div>
            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-sky-50/70 rounded-2xl border border-sky-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Rata-rata Kelas</p>
                <p class="text-xl sm:text-2xl font-black text-sky-700">{{ $avgScore !== null ? number_format($avgScore, 1) : '-' }}</p>
                <p class="mt-1 text-[10px] text-sky-600/70">dari {{ $graded->count() }} siswa</p>
            </div>
            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-emerald-50/70 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Nilai Tertinggi</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-700">{{ $graded->max('score') !== null ? number_format((float) $graded->max('score'), 1) : '-' }}</p>
            </div>
            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Posisi Kamu</p>
                @if($grade->score !== null && $graded->count() > 0)
                    @php
                        $rank = $graded->map(fn ($g) => (float) $g->score)->sortDesc()->values()->search(fn ($score) => $score == (float) $grade->score) + 1;
                    @endphp
                    <p class="text-xl sm:text-2xl font-black text-gray-900">#{{ $rank }}</p>
                    <p class="mt-1 text-[10px] text-gray-400">dari {{ $graded->count() }} siswa</p>
                @else
                    <p class="text-xl sm:text-2xl font-black text-gray-300">-</p>
                    <p class="mt-1 text-[10px] text-gray-400">belum dinilai</p>
                @endif
            </div>
        </div>
        <p class="sm:hidden mt-2 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Guru Pengajar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-5">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-linear-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-xs font-black shadow-sm shrink-0">
                {{ strtoupper(substr($assignment->teacher->name ?? '?', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Guru Pengajar</p>
                <p class="mt-0.5 text-sm font-black text-gray-900">{{ $assignment->teacher->name ?? '-' }}</p>
                <p class="mt-0.5 text-xs font-semibold text-gray-500">{{ $assignment->subject->name ?? '-' }}</p>
            </div>
        </div>
    </div>

    @if($grade->note)
        <div class="bg-amber-50/60 rounded-2xl border border-amber-100 p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-wider text-amber-600">Catatan Guru</p>
                    <p class="mt-1 text-sm leading-relaxed text-amber-900">{{ $grade->note }}</p>
                </div>
            </div>
        </div>
    @endif

    <a href="{{ route('siswa.grades.index') }}"
       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200 hover:bg-emerald-100 active:scale-[0.98] transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Kembali ke Nilai Tugas
    </a>
</div>
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
