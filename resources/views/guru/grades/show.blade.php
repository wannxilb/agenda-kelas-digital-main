@extends('layouts.guru')

@section('title', 'Detail Nilai Tugas')
@section('header', 'Detail Nilai Tugas')

@php
    $graded = $assignment->grades->filter(fn($g) => $g->score !== null);
    $ungraded = $assignment->grades->filter(fn($g) => $g->score === null);
    $avgScore = $graded->avg('score');
    $sortedGrades = $assignment->grades->sortBy(fn($grade) => strtolower($grade->student->name ?? ''));
    $isOverdue = $assignment->is_overdue;
    $overdueCount = $assignment->overdue_count ?? 0;
@endphp

@section('content')
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-6" x-data="{ search: '' }">

    {{-- Breadcrumb + Title --}}
    <div>
        <nav class="mb-3 flex items-center gap-2 text-xs font-semibold text-gray-400">
            <a href="{{ route('guru.grades.index') }}" class="hover:text-emerald-600 transition-colors">Nilai Tugas</a>
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
            <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">Maks {{ number_format((float) $assignment->max_score, 0) }}</span>
            @if($assignment->assigned_date)
                <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">Tugas {{ $assignment->assigned_date->format('d/m/Y') }}</span>
            @endif
            @if($assignment->due_date)
                <span class="inline-flex items-center px-2.5 py-1 {{ $isOverdue ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-gray-100 text-gray-600 border-transparent' }} rounded-lg text-xs font-bold border">
                    Batas {{ $assignment->due_date->format('d/m/Y') }}{{ $isOverdue ? ' - terlambat' : '' }}
                </span>
            @endif
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="grid grid-cols-2 sm:flex gap-2">
        <a href="{{ route('guru.grades.export', $assignment) }}"
           class="col-span-2 sm:col-span-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-3 bg-white text-emerald-700 border border-emerald-200 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-50 active:scale-95 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Excel
        </a>
        <a href="{{ route('guru.grades.edit', $assignment) }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-200/50 hover:bg-emerald-700 active:scale-95 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit
        </a>
        <form method="POST" action="{{ route('guru.grades.destroy', $assignment) }}" onsubmit="return confirm('Hapus nilai tugas ini? Semua data nilai siswa akan ikut terhapus.')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-rose-50 text-rose-600 border border-rose-200 rounded-xl text-sm font-bold hover:bg-rose-100 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Hapus
            </button>
        </form>
    </div>

    {{-- Stats Cards (Horizontal Scroll on Mobile) --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-5 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center relative overflow-hidden">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1 z-10">Total Siswa</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900 z-10">{{ $assignment->grades->count() }}</p>
                <svg class="absolute -bottom-2 -right-2 w-14 h-14 text-gray-50 opacity-50" fill="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Dinilai</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $graded->count() }}</p>
            </div>
            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Belum Dinilai</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $ungraded->count() }}</p>
            </div>
            <div class="snap-center shrink-0 w-[130px] sm:w-auto {{ $isOverdue ? 'bg-rose-50/70 border-rose-100' : 'bg-gray-50/70 border-gray-100' }} rounded-2xl border p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold {{ $isOverdue ? 'text-rose-600/70' : 'text-gray-400' }} uppercase tracking-wider mb-1">Terlambat</p>
                <p class="text-xl sm:text-2xl font-black {{ $isOverdue ? 'text-rose-700' : 'text-gray-500' }}">{{ $overdueCount }}</p>
            </div>
            <div class="snap-center shrink-0 w-[130px] sm:w-auto bg-sky-50/50 rounded-2xl border border-sky-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Rata-rata</p>
                <p class="text-xl sm:text-2xl font-black text-sky-600">{{ $avgScore !== null ? number_format($avgScore, 1) : '-' }}</p>
            </div>
        </div>
        <p class="sm:hidden mt-2 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-3 sm:p-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" x-model="search" placeholder="Cari nama siswa..."
                       class="block w-full pl-9 pr-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all text-sm">
            </div>
        </div>
    </div>

    {{-- Student Grades List --}}
    <div class="space-y-3 sm:space-y-0 sm:bg-white sm:rounded-2xl sm:shadow-sm sm:border sm:border-gray-100 sm:overflow-hidden">
        {{-- Desktop Table Header --}}
        <div class="hidden sm:grid sm:grid-cols-12 gap-4 px-6 py-4 border-b border-gray-50 bg-gray-50/50">
            <div class="col-span-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa</div>
            <div class="col-span-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Nilai</div>
            <div class="col-span-5 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Keterangan</div>
        </div>

        @forelse($sortedGrades as $grade)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sm:border-0 sm:shadow-none sm:rounded-none sm:border-b sm:border-gray-50 last:sm:border-b-0"
                 x-show="!search || '{{ strtolower($grade->student->name ?? '') }}'.includes(search.toLowerCase())"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100">

                {{-- Mobile: Card --}}
                <div class="sm:hidden px-4 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-linear-to-br from-emerald-500 to-teal-600 text-white rounded-xl flex items-center justify-center text-xs font-bold shadow-sm flex-shrink-0">
                            {{ strtoupper(substr($grade->student->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $grade->student->name ?? '-' }}</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $grade->student->nis ?? $grade->student->nisn ?? '' }}</p>
                        </div>
                        @if($grade->score !== null)
                            @php
                                $percentage = $assignment->max_score > 0 ? ($grade->score / $assignment->max_score) * 100 : 0;
                                $scoreColor = $percentage >= 80 ? 'emerald' : ($percentage >= 60 ? 'amber' : 'rose');
                            @endphp
                            <div class="flex-shrink-0 text-right">
                                <span class="inline-flex items-center px-3 py-1.5 bg-{{ $scoreColor }}-50 text-{{ $scoreColor }}-700 rounded-xl text-sm font-black border border-{{ $scoreColor }}-100">
                                    {{ number_format((float) $grade->score, 1) }}
                                </span>
                                <p class="text-[10px] font-bold text-gray-400 mt-0.5">{{ number_format($percentage, 0) }}%</p>
                            </div>
                        @else
                            <span class="inline-flex items-center px-3 py-1.5 {{ $isOverdue ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-gray-50 text-gray-400 border-gray-100' }} rounded-xl text-xs font-bold border">
                                {{ $isOverdue ? 'Terlambat' : 'Belum dinilai' }}
                            </span>
                        @endif
                    </div>
                    @if($grade->note)
                        <div class="mt-2.5 ml-13 pl-[52px]">
                            <p class="text-[11px] text-gray-400 leading-relaxed">{{ $grade->note }}</p>
                        </div>
                    @endif
                </div>

                {{-- Desktop: Row --}}
                <div class="hidden sm:grid sm:grid-cols-12 sm:gap-4 sm:items-center px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="col-span-5 flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 bg-linear-to-br from-emerald-500 to-teal-600 text-white rounded-xl flex items-center justify-center text-xs font-bold shadow-sm flex-shrink-0">
                            {{ strtoupper(substr($grade->student->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $grade->student->name ?? '-' }}</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $grade->student->nis ?? $grade->student->nisn ?? '' }}</p>
                        </div>
                    </div>
                    <div class="col-span-2 text-center">
                        @if($grade->score !== null)
                            <span class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-sm font-black border border-emerald-100">
                                {{ number_format((float) $grade->score, 1) }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 {{ $isOverdue ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-gray-50 text-gray-400 border-gray-100' }} rounded-lg text-xs font-bold border">
                                {{ $isOverdue ? 'Terlambat' : '-' }}
                            </span>
                        @endif
                    </div>
                    <div class="col-span-5">
                        <p class="text-xs text-gray-500 truncate">{{ $grade->note ?: '-' }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="sm:hidden bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
                <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3 border-2 border-dashed border-gray-200">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-800">Belum Ada Data Nilai</p>
                <p class="text-xs text-gray-400 mt-1">Klik tombol Edit untuk mulai mengisi nilai siswa.</p>
            </div>
            <div class="hidden sm:block p-10 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-800">Belum Ada Data Nilai</h3>
                <p class="text-sm text-gray-500 mt-1">Klik tombol Edit untuk mulai mengisi nilai siswa.</p>
            </div>
        @endforelse

    </div>

    {{-- Scroll to Top --}}
    <div class="sm:hidden fixed bottom-24 right-5 z-40" x-data="{ show: false }"
         @scroll.window="show = window.scrollY > 300"
         x-show="show" x-transition>
        <button @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="w-10 h-10 bg-white rounded-full shadow-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 active:scale-95 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
            </svg>
        </button>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
