@extends('layouts.guru')

@section('title', 'Nilai Tugas')
@section('header', 'Nilai Tugas')

@php
    $activeFilterCount = collect([$classId, $subjectId, $academicYearId, $assignedDate, $search])->filter()->count();
@endphp

@section('content')
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-6">

    <div class="sm:flex sm:items-center sm:justify-between sm:gap-4">
        <div>
            <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                Rekap nilai per <strong class="text-gray-700">kelas, mapel, dan semester</strong>. Buka rekap untuk melihat tugas-tugas di dalamnya.
            </p>
            @if($selectedAcademicYear)
                <p class="mt-1 text-[11px] sm:text-xs font-bold text-emerald-700">
                    {{ $selectedAcademicYear->name }} - Semester {{ $selectedAcademicYear->semester }}
                </p>
            @endif
        </div>
        <a href="{{ route('guru.grades.create') }}"
           class="hidden sm:inline-flex shrink-0 items-center justify-center gap-2 px-4 py-2.5 bg-linear-to-r from-emerald-600 to-teal-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-200/50 hover:from-emerald-700 hover:to-teal-700 active:scale-[0.98] transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"></path>
            </svg>
            Buat Nilai Tugas
        </a>
    </div>

    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-5 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Tugas</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $totalAssignments }}</p>
                <p class="mt-1 text-[10px] text-gray-400">{{ $semesterGroups->count() }} rekap</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-emerald-50/70 rounded-2xl border border-emerald-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Sudah Dinilai</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-700">{{ $totalGraded }}</p>
                <p class="mt-1 text-[10px] text-emerald-600/70">entri siswa</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-amber-50/70 rounded-2xl border border-amber-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Belum Dinilai</p>
                <p class="text-xl sm:text-2xl font-black text-amber-700">{{ $totalUngraded }}</p>
                <p class="mt-1 text-[10px] text-amber-600/70">entri siswa</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-rose-50/70 rounded-2xl border border-rose-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-1">Terlambat</p>
                <p class="text-xl sm:text-2xl font-black text-rose-700">{{ $totalOverdue }}</p>
                <p class="mt-1 text-[10px] text-rose-600/70">lewat deadline</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-sky-50/70 rounded-2xl border border-sky-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Rata-rata</p>
                <p class="text-xl sm:text-2xl font-black text-sky-700">{{ $avgScore !== null ? number_format($avgScore, 1) : '-' }}</p>
                <p class="mt-1 text-[10px] text-sky-600/70">nilai semester</p>
            </div>
        </div>
        <p class="sm:hidden mt-2 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">Geser ringkasan</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ filterOpen: @js((bool) $activeFilterCount) }">
        <form method="GET" class="p-3 sm:p-4">
            @if($classId)
                <input type="hidden" name="class_id" value="{{ $classId }}">
            @endif
            @if($subjectId)
                <input type="hidden" name="subject_id" value="{{ $subjectId }}">
            @endif
            @if($academicYearId)
                <input type="hidden" name="academic_year_id" value="{{ $academicYearId }}">
            @endif
            @if($assignedDate)
                <input type="hidden" name="assigned_date" value="{{ $assignedDate }}">
            @endif
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama tugas atau keterangan..."
                       class="block w-full pl-9 pr-9 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all text-sm">
                @if($search)
                    <a href="{{ route('guru.grades.index', array_filter(['class_id' => $classId, 'subject_id' => $subjectId, 'academic_year_id' => $academicYearId, 'assigned_date' => $assignedDate])) }}"
                       class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </form>

        <div class="px-3 sm:px-4 pb-3 sm:pb-4">
            <button type="button" @click="filterOpen = !filterOpen"
                    class="w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all"
                    :class="filterOpen ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter rekap
                    @if($activeFilterCount)
                        <span class="inline-flex items-center justify-center min-w-5 h-5 px-1 bg-emerald-600 text-white rounded-full text-[9px] font-bold">{{ $activeFilterCount }}</span>
                    @endif
                </span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="filterOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        <div x-show="filterOpen" x-collapse x-cloak class="border-t border-gray-100">
            <form method="GET" class="p-4 space-y-4">
                @if($search)
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif

                @if($activeFilterCount)
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Filter aktif</p>
                        <a href="{{ route('guru.grades.index') }}" class="text-[11px] font-bold text-rose-500 hover:text-rose-600 transition-colors">Reset</a>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Semester</label>
                        <select name="academic_year_id" id="filter_academic_year_id">
                            <option value="">Semester aktif</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (string) $academicYearId === (string) $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} - {{ $year->semester }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kelas</label>
                        <select name="class_id" id="filter_class_id">
                            <option value="">Semua kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ (string) $classId === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</label>
                        <select name="subject_id" id="filter_subject_id">
                            <option value="">Semua mapel</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (string) $subjectId === (string) $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal Tugas</label>
                        <input type="date" name="assigned_date" value="{{ $assignedDate ?? '' }}"
                               class="block w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all text-sm text-gray-700">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-linear-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-200/50 hover:from-emerald-700 hover:to-teal-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                    Terapkan Filter
                </button>
            </form>
        </div>
    </div>

    <div class="sm:hidden fixed bottom-24 right-5 z-40">
        <a href="{{ route('guru.grades.create') }}"
           class="flex items-center justify-center w-14 h-14 bg-emerald-600 text-white rounded-2xl shadow-lg shadow-emerald-300/50 active:scale-95 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"></path>
            </svg>
        </a>
    </div>

    <div class="space-y-3">
        @forelse($semesterGroups as $groupIndex => $group)
            @php
                $completion = $group['grades_count'] > 0 ? round(($group['graded_count'] / $group['grades_count']) * 100) : 0;
                $latestDate = $group['latest_date'];
                $hasOverdue = $group['overdue_count'] > 0;
                $gradeLevel = $group['class']->grade_level ?? '-';
            @endphp
            <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                     x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full text-left p-4 sm:p-5 hover:bg-gray-50/80 transition-colors">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-700 border border-emerald-100 flex flex-col items-center justify-center shadow-sm shrink-0">
                            <span class="text-[8px] font-black uppercase leading-none text-emerald-500">Kelas</span>
                            <span class="mt-0.5 text-sm sm:text-base font-black leading-none">{{ $gradeLevel }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="sm:flex sm:items-start sm:justify-between sm:gap-4">
                                <div class="min-w-0">
                                    <h2 class="text-sm sm:text-base font-black text-gray-900 truncate">
                                        {{ $group['class']->name ?? '-' }} - {{ $group['subject']->name ?? '-' }}
                                    </h2>
                                    <p class="mt-1 text-[11px] sm:text-xs text-gray-500">
                                        {{ $group['academic_year']->name ?? 'Tahun ajaran' }} - Semester {{ $group['academic_year']->semester ?? '-' }}
                                    </p>
                                </div>
                                <div class="mt-3 sm:mt-0 flex items-center gap-2 sm:justify-end">
                                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-[10px] font-bold">
                                        {{ $group['assignment_count'] }} tugas
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-bold border border-emerald-100">
                                        {{ $completion }}% selesai
                                    </span>
                                    @if($hasOverdue)
                                        <span class="inline-flex items-center px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-[10px] font-bold border border-rose-100">
                                            {{ $group['overdue_count'] }} terlambat
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full bg-linear-to-r from-emerald-500 to-teal-500" style="width: {{ $completion }}%"></div>
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
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata</p>
                            <p class="mt-0.5 text-sm font-black text-gray-900">{{ $group['average_score'] !== null ? number_format($group['average_score'], 1) : '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-white px-3 py-2 border border-gray-100">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Dinilai</p>
                            <p class="mt-0.5 text-sm font-black text-gray-900">{{ $group['graded_count'] }}/{{ $group['grades_count'] }}</p>
                        </div>
                        <div class="rounded-xl bg-white px-3 py-2 border border-gray-100">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Terakhir</p>
                            <p class="mt-0.5 text-sm font-black text-gray-900">{{ $latestDate ? $latestDate->format('d/m') : '-' }}</p>
                        </div>
                        <div class="rounded-xl {{ $hasOverdue ? 'bg-rose-50 border-rose-100' : 'bg-white border-gray-100' }} px-3 py-2 border">
                            <p class="text-[9px] font-bold {{ $hasOverdue ? 'text-rose-500' : 'text-gray-400' }} uppercase tracking-wider">Lewat</p>
                            <p class="mt-0.5 text-sm font-black {{ $hasOverdue ? 'text-rose-700' : 'text-gray-900' }}">{{ $group['overdue_count'] }}</p>
                        </div>
                    </div>
                    <div class="hidden sm:grid sm:grid-cols-12 gap-4 px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        <div class="col-span-5">Tugas</div>
                        <div class="col-span-2 text-center">Tanggal</div>
                        <div class="col-span-2 text-center">Rata-rata</div>
                        <div class="col-span-1 text-center">Terisi</div>
                        <div class="col-span-2 text-right">Aksi</div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach($group['assignments'] as $assignment)
                            @php
                                $isOverdue = $assignment->is_overdue;
                            @endphp
                            <div class="px-4 sm:px-5 py-3 bg-white sm:grid sm:grid-cols-12 sm:gap-4 sm:items-center">
                                <div class="sm:col-span-5 min-w-0">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate">{{ $assignment->title }}</p>
                                        @if($isOverdue)
                                            <span class="shrink-0 inline-flex items-center px-2 py-0.5 bg-rose-50 text-rose-700 rounded-md text-[9px] font-black border border-rose-100">Terlambat</span>
                                        @endif
                                    </div>
                                    @if($assignment->description)
                                        <p class="mt-0.5 text-xs text-gray-400 line-clamp-1">{{ $assignment->description }}</p>
                                    @endif
                                    @if($assignment->due_date)
                                        <p class="mt-1 text-[10px] font-bold {{ $isOverdue ? 'text-rose-500' : 'text-gray-400' }}">
                                            Batas: {{ $assignment->due_date->format('d/m/Y') }}{{ $isOverdue ? ' - ' . $assignment->overdue_count . ' belum dinilai' : '' }}
                                        </p>
                                    @endif
                                </div>
                                <div class="mt-2 sm:mt-0 sm:col-span-2 sm:text-center">
                                    <span class="text-xs font-semibold text-gray-500">{{ $assignment->assigned_date?->format('d/m/Y') ?? '-' }}</span>
                                </div>
                                <div class="mt-2 sm:mt-0 sm:col-span-2 sm:text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 bg-sky-50 text-sky-700 rounded-lg text-xs font-black border border-sky-100">
                                        {{ $assignment->average_score !== null ? number_format($assignment->average_score, 1) : '-' }}
                                    </span>
                                </div>
                                <div class="mt-2 sm:mt-0 sm:col-span-1 sm:text-center">
                                    <span class="text-xs font-black text-gray-700">{{ $assignment->graded_count }}/{{ $assignment->grades_count }}</span>
                                </div>
                                <div class="mt-3 sm:mt-0 sm:col-span-2 flex sm:justify-end gap-2">
                                    <a href="{{ route('guru.grades.show', $assignment) }}"
                                       class="flex-1 sm:flex-none inline-flex items-center justify-center px-3 py-2 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold border border-emerald-100 hover:bg-emerald-100 transition-all">
                                        Detail
                                    </a>
                                    <a href="{{ route('guru.grades.export', $assignment) }}"
                                       class="flex-1 sm:flex-none inline-flex items-center justify-center px-3 py-2 bg-white text-gray-600 rounded-xl text-xs font-bold border border-gray-200 hover:bg-gray-50 transition-all">
                                        Excel
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="p-4 bg-white border-t border-gray-100 flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('guru.grades.summary', array_filter(['class' => $group['class']->id ?? null, 'subject' => $group['subject']->id ?? null, 'academic_year_id' => $group['academic_year']->id ?? null])) }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 active:scale-[0.98] transition-all">
                            Buka rekap semester
                        </a>
                        <a href="{{ route('guru.grades.create', array_filter(['class_id' => $group['class']->id ?? null, 'subject_id' => $group['subject']->id ?? null])) }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-900 text-white rounded-xl text-xs font-bold hover:bg-gray-800 active:scale-[0.98] transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"></path>
                            </svg>
                            Tambah tugas di kelas ini
                        </a>
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
                <p class="text-sm text-gray-500 mt-1">Buat tugas pertama untuk mulai mencatat nilai siswa.</p>
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
document.addEventListener('DOMContentLoaded', function() {
    ['#filter_academic_year_id', '#filter_class_id', '#filter_subject_id'].forEach(function(selector) {
        var element = document.querySelector(selector);
        if (!element || element.tomselect) return;

        new TomSelect(selector, {
            create: false,
            allowEmptyOption: true,
            sortField: { field: 'text', direction: 'asc' },
            maxOptions: null
        });
    });
});
</script>
@endpush
