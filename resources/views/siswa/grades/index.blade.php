@extends('layouts.siswa')

@section('title', 'Nilai Tugas')
@section('header', 'Nilai Tugas')
@section('content-padding', 'pt-4 sm:pt-6 pb-24 lg:pb-8')

@php
    $activeFilterCount = collect([$subjectId, $academicYearId])->filter()->count();
    $subjectOptions = $subjects->map(fn($s) => ['value' => (string) $s->id, 'label' => $s->name])->values()->toArray();
    $yearOptions = $academicYears->map(fn($y) => ['value' => (string) $y->id, 'label' => $y->name . ' - Semester ' . $y->semester])->values()->toArray();
@endphp

@section('content')
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-6">

    <div class="sm:flex sm:items-center sm:justify-between sm:gap-4">
        <div>
            <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                Nilai tugas kamu per <strong class="text-gray-700">mata pelajaran</strong> dari guru pengajar yang mengampu mapel tersebut.
            </p>
            @if($academicYears->isNotEmpty())
                <p class="mt-1 text-[11px] sm:text-xs font-bold text-emerald-700">
                    {{ $academicYears->firstWhere('is_active', true)?->name ?? 'Tahun ajaran aktif' }} - Semester {{ $academicYears->firstWhere('is_active', true)?->semester ?? '-' }}
                </p>
            @endif
        </div>
        <span class="mt-2 sm:mt-0 hidden sm:inline-flex shrink-0 items-center gap-2 px-4 py-2.5 bg-blue-50 text-blue-700 rounded-xl text-xs font-bold border border-blue-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            Hanya untuk kamu
        </span>
    </div>

    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-5 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Tugas</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $totalAssignments }}</p>
                <p class="mt-1 text-[10px] text-gray-400">{{ $subjectGroups->count() }} mapel</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-emerald-50/70 rounded-2xl border border-emerald-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Sudah Dinilai</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-700">{{ $totalGraded }}</p>
                <p class="mt-1 text-[10px] text-emerald-600/70">tugas</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-amber-50/70 rounded-2xl border border-amber-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Belum Dinilai</p>
                <p class="text-xl sm:text-2xl font-black text-amber-700">{{ $totalUngraded }}</p>
                <p class="mt-1 text-[10px] text-amber-600/70">tugas</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-rose-50/70 rounded-2xl border border-rose-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-1">Lewat Batas</p>
                <p class="text-xl sm:text-2xl font-black text-rose-700">{{ $totalOverdue }}</p>
                <p class="mt-1 text-[10px] text-rose-600/70">belum dinilai</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-sky-50/70 rounded-2xl border border-sky-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Rata-rata</p>
                <p class="text-xl sm:text-2xl font-black text-sky-700">{{ $avgScore !== null ? number_format($avgScore, 1) : '-' }}</p>
                <p class="mt-1 text-[10px] text-sky-600/70">semua mapel</p>
            </div>
        </div>
        <p class="sm:hidden mt-2 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">Geser ringkasan</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100" x-data="{ filterOpen: @js((bool) $activeFilterCount) }">
        <div class="p-3 sm:p-4">
            <button type="button" @click="filterOpen = !filterOpen"
                    class="w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all"
                    :class="filterOpen ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter nilai
                    @if($activeFilterCount)
                        <span class="inline-flex items-center justify-center min-w-5 h-5 px-1 bg-emerald-600 text-white rounded-full text-[9px] font-bold">{{ $activeFilterCount }}</span>
                    @endif
                </span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="filterOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        <div x-show="filterOpen" x-collapse x-cloak class="border-t border-gray-100 rounded-b-2xl">
            <form method="GET" class="p-4 space-y-4">
                @if($activeFilterCount)
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Filter aktif</p>
                        <a href="{{ route('siswa.grades.index') }}" class="text-[11px] font-bold text-rose-500 hover:text-rose-600 transition-colors">Reset</a>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</label>
                        <div class="relative" x-data="searchableSelect({
                            name: 'subject_id',
                            value: @js((string) $subjectId),
                            placeholder: 'Semua mapel',
                            options: @js($subjectOptions)
                        })">
                            <button type="button" @click="toggle()"
                                    class="w-full flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl border bg-white text-left text-sm font-semibold transition-all"
                                    :class="open ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-gray-200 hover:border-gray-300'">
                                <span class="truncate" :class="value ? 'text-gray-900' : 'text-gray-400'" x-text="selectedLabel"></span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" x-cloak @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute left-0 right-0 mt-2 z-50 bg-white rounded-xl border border-gray-200 shadow-xl overflow-hidden">
                                <div class="p-2">
                                    <div class="relative">
                                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                                        </svg>
                                        <input x-model="search" type="text" placeholder="Cari mapel..."
                                               class="w-full pl-9 pr-3 py-2 rounded-lg bg-gray-50 border border-gray-200 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-400" />
                                    </div>
                                </div>
                                <div class="max-h-56 overflow-y-auto pb-2">
                                    <template x-for="opt in filteredOptions" :key="opt.value">
                                        <button type="button" @click="select(opt.value)"
                                                class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm text-left transition-colors"
                                                :class="opt.value === value ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-gray-700 hover:bg-gray-50'">
                                            <span class="truncate" x-text="opt.label"></span>
                                            <svg x-show="opt.value === value" class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </template>
                                    <p x-show="filteredOptions.length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">Tidak ada hasil</p>
                                </div>
                            </div>

                            <input type="hidden" :name="name" :value="value" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Semester</label>
                        <div class="relative" x-data="searchableSelect({
                            name: 'academic_year_id',
                            value: @js((string) $academicYearId),
                            placeholder: 'Semua',
                            options: @js($yearOptions)
                        })">
                            <button type="button" @click="toggle()"
                                    class="w-full flex items-center justify-between gap-2 px-4 py-2.5 rounded-xl border bg-white text-left text-sm font-semibold transition-all"
                                    :class="open ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-gray-200 hover:border-gray-300'">
                                <span class="truncate" :class="value ? 'text-gray-900' : 'text-gray-400'" x-text="selectedLabel"></span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" x-cloak @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 class="absolute left-0 right-0 mt-2 z-50 bg-white rounded-xl border border-gray-200 shadow-xl overflow-hidden">
                                <div class="p-2">
                                    <div class="relative">
                                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"></path>
                                        </svg>
                                        <input x-model="search" type="text" placeholder="Cari semester..."
                                               class="w-full pl-9 pr-3 py-2 rounded-lg bg-gray-50 border border-gray-200 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-400" />
                                    </div>
                                </div>
                                <div class="max-h-56 overflow-y-auto pb-2">
                                    <template x-for="opt in filteredOptions" :key="opt.value">
                                        <button type="button" @click="select(opt.value)"
                                                class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm text-left transition-colors"
                                                :class="opt.value === value ? 'bg-emerald-50 text-emerald-700 font-bold' : 'text-gray-700 hover:bg-gray-50'">
                                            <span class="truncate" x-text="opt.label"></span>
                                            <svg x-show="opt.value === value" class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </template>
                                    <p x-show="filteredOptions.length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">Tidak ada hasil</p>
                                </div>
                            </div>

                            <input type="hidden" :name="name" :value="value" />
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-linear-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-200/50 hover:from-emerald-700 hover:to-teal-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    Terapkan Filter
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($subjectGroups as $groupIndex => $group)
            @php
                $hasOverdue = $group['overdue_count'] > 0;
                $avg = $group['average_score'];
                $latestDate = $group['grades']->first()?->assignment->assigned_date;
            @endphp
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                     x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full text-left p-4 sm:p-5 hover:bg-gray-50/80 transition-colors">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex flex-col items-center justify-center shadow-sm shrink-0">
                            <span class="text-[8px] font-black uppercase leading-none text-emerald-500">Mapel</span>
                            <span class="mt-0.5 text-sm sm:text-base font-black leading-none">{{ strtoupper(substr($group['subject']->name ?? '?', 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="sm:flex sm:items-start sm:justify-between sm:gap-4">
                                <div class="min-w-0">
                                    <h2 class="text-sm sm:text-base font-black text-gray-900 truncate">
                                        {{ $group['subject']->name ?? '-' }}
                                    </h2>
                                    <p class="mt-1 text-[11px] sm:text-xs text-gray-500">
                                        {{ $group['class']->name ?? 'Kelas' }}
                                        @if($group['teacher'])
                                            <span class="mx-1 text-gray-300">•</span>
                                            Guru: {{ $group['teacher']->name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="mt-3 sm:mt-0 flex items-center gap-2 sm:justify-end">
                                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-[10px] font-bold">
                                        {{ $group['assignment_count'] }} tugas
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-bold border border-emerald-100">
                                        {{ $group['completion'] }}% dinilai
                                    </span>
                                    @if($hasOverdue)
                                        <span class="inline-flex items-center px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-[10px] font-bold border border-rose-100">
                                            {{ $group['overdue_count'] }} lewat batas
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full bg-linear-to-r from-emerald-500 to-teal-500" style="width: {{ $group['completion'] }}%"></div>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 mt-3 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>

                <div x-show="open" x-collapse class="border-t border-gray-100 bg-gray-50/40">
                    <div class="grid grid-cols-2 gap-2 p-4 sm:grid-cols-4 sm:px-5">
                        <div class="rounded-xl bg-white px-3 py-2 border border-gray-100">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata Saya</p>
                            <p class="mt-0.5 text-sm font-black text-gray-900">{{ $avg !== null ? number_format($avg, 1) : '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-white px-3 py-2 border border-gray-100">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Dinilai</p>
                            <p class="mt-0.5 text-sm font-black text-gray-900">{{ $group['graded_count'] }}/{{ $group['assignment_count'] }}</p>
                        </div>
                        <div class="rounded-xl bg-white px-3 py-2 border border-gray-100">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Terakhir</p>
                            <p class="mt-0.5 text-sm font-black text-gray-900">{{ $latestDate ? $latestDate->format('d/m') : '-' }}</p>
                        </div>
                        <div class="rounded-xl {{ $hasOverdue ? 'bg-rose-50 border-rose-100' : 'bg-white border-gray-100' }} px-3 py-2 border">
                            <p class="text-[9px] font-bold {{ $hasOverdue ? 'text-rose-500' : 'text-gray-400' }} uppercase tracking-wider">Lewat Batas</p>
                            <p class="mt-0.5 text-sm font-black {{ $hasOverdue ? 'text-rose-700' : 'text-gray-900' }}">{{ $group['overdue_count'] }}</p>
                        </div>
                    </div>

                    <div class="hidden sm:grid sm:grid-cols-12 gap-4 px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        <div class="col-span-5">Tugas</div>
                        <div class="col-span-2 text-center">Tanggal</div>
                        <div class="col-span-1 text-center">Nilai</div>
                        <div class="col-span-1 text-center">%</div>
                        <div class="col-span-3 text-right">Status</div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($group['grades'] as $grade)
                            @php
                                $assignment = $grade->assignment;
                                $maxScore = (float) ($assignment->max_score ?? 100);
                                $percentage = $grade->score !== null && $maxScore > 0 ? round(($grade->score / $maxScore) * 100) : null;
                                $isOverdue = (bool) $grade->is_overdue;
                                $scoreColor = $percentage === null ? '' : ($percentage >= 80 ? 'emerald' : ($percentage >= 60 ? 'amber' : 'rose'));
                            @endphp
                            <div class="px-4 sm:px-5 py-3 bg-white sm:grid sm:grid-cols-12 sm:gap-4 sm:items-center">
                                <div class="sm:col-span-5 min-w-0">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <a href="{{ route('siswa.grades.show', $grade) }}" class="text-sm font-bold text-gray-900 truncate hover:text-emerald-700 transition-colors">{{ $assignment->title }}</a>
                                        @if($isOverdue)
                                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 bg-rose-50 text-rose-700 rounded-md text-[9px] font-black border border-rose-100">Lewat batas</span>
                                        @endif
                                    </div>
                                    @if($assignment->description)
                                        <p class="mt-0.5 text-xs text-gray-400 line-clamp-1">{{ $assignment->description }}</p>
                                    @endif
                                    @if($assignment->due_date)
                                        <p class="mt-1 text-[10px] font-bold {{ $isOverdue ? 'text-rose-500' : 'text-gray-400' }}">
                                            Batas: {{ $assignment->due_date->format('d/m/Y') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="mt-2 sm:mt-0 sm:col-span-2 sm:text-center">
                                    <span class="text-xs font-semibold text-gray-500">{{ $assignment->assigned_date?->format('d/m/Y') ?? '-' }}</span>
                                </div>
                                <div class="mt-2 sm:mt-0 sm:col-span-1 sm:text-center">
                                    @if($grade->score !== null)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black border {{ $scoreColor === 'emerald' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : ($scoreColor === 'amber' ? 'bg-amber-50 text-amber-700 border-amber-100' : 'bg-rose-50 text-rose-700 border-rose-100') }}">
                                            {{ number_format((float) $grade->score, 1) }}
                                        </span>
                                    @else
                                        <span class="text-xs font-bold text-gray-300">-</span>
                                    @endif
                                </div>
                                <div class="mt-1 sm:mt-0 sm:col-span-1 sm:text-center">
                                    @if($percentage !== null)
                                        <span class="text-xs font-black {{ $scoreColor === 'rose' ? 'text-rose-600' : ($scoreColor === 'amber' ? 'text-amber-600' : 'text-emerald-600') }}">{{ $percentage }}%</span>
                                    @else
                                        <span class="text-xs font-bold text-gray-300">-</span>
                                    @endif
                                </div>
                                <div class="mt-2 sm:mt-0 sm:col-span-3 flex sm:justify-end gap-2">
                                    @if($grade->score !== null)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-bold border border-emerald-100">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            Selesai
                                        </span>
                                    @elseif($isOverdue)
                                        <span class="inline-flex items-center px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-[10px] font-bold border border-rose-100">Menunggu nilai</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-[10px] font-bold border border-amber-100">Belum dinilai</span>
                                    @endif
                                    <a href="{{ route('siswa.grades.show', $grade) }}"
                                       class="inline-flex items-center justify-center px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-xs font-bold border border-gray-200 hover:bg-gray-100 transition-all">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m-7 4h8m-8 4h5m-7 6h12a2 2 0 002-2V5a2 2 0 00-2-2H8.5L4 7.5V19a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-800">Belum ada nilai tugas</h3>
                <p class="text-sm text-gray-500 mt-1">Nilai tugas dari guru pengajar akan muncul di sini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('searchableSelect', (config) => ({
        open: false,
        search: '',
        name: config.name,
        value: config.value ?? '',
        placeholder: config.placeholder ?? 'Pilih...',
        options: config.options ?? [],
        get selectedLabel() {
            const found = this.options.find((o) => o.value === this.value);
            return found ? found.label : this.placeholder;
        },
        get filteredOptions() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.options;
            return this.options.filter((o) => o.label.toLowerCase().includes(q));
        },
        toggle() {
            this.open = !this.open;
            if (this.open) this.search = '';
        },
        select(value) {
            this.value = value;
            this.open = false;
            this.search = '';
        },
    }));
});
</script>
@endpush
