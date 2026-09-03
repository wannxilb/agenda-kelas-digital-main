@extends('layouts.walikelas')

@section('title', 'Nilai Siswa')
@section('header', 'Nilai Siswa')
@section('content-padding', 'py-4 sm:py-8 pb-28 lg:pb-8')

@section('content')
@php
    $activeFilterCount = collect([$subjectId ?? null, $search ?? null])->filter()->count();
    $subjectId = $subjectId ?? null;
    $search = $search ?? null;
@endphp

<div class="space-y-4 sm:space-y-6"
     x-data="{
        filterOpen: {{ $activeFilterCount ? 'true' : 'false' }},
     }">

    @include('walikelas.partials.context-filter')

    {{-- Header --}}
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Nilai Siswa</h1>
                <p class="mt-2 text-xs sm:text-sm text-gray-500 font-medium max-w-2xl">
                    Rekap nilai tugas siswa di kelas {{ $has_class ? optional($class)->name : '-' }}
                    @if($has_class && ($selectedAcademicYear ?? null))
                        <span class="text-indigo-600 font-bold">• {{ $selectedAcademicYear->name }} - Semester {{ $selectedAcademicYear->semester }}</span>
                    @endif
                </p>
            </div>
            <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-50 text-emerald-700 rounded-xl text-xs font-bold border border-emerald-100 self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Hanya baca
            </span>
        </div>
    </div>

    @if(!$has_class)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-800">Belum ada kelas</h3>
            <p class="text-sm text-gray-500 mt-1">Anda belum menjadi wali kelas pada tahun ajaran yang dipilih.</p>
        </div>
    @else
        {{-- Stat cards --}}
        <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
            <div class="flex sm:grid sm:grid-cols-4 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
                <div class="snap-center shrink-0 w-[132px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4">
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Siswa</p>
                    <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $totalStudents }}</p>
                </div>
                <div class="snap-center shrink-0 w-[132px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4">
                    <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tugas</p>
                    <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $totalAssignments }}</p>
                </div>
                <div class="snap-center shrink-0 w-[132px] sm:w-auto bg-indigo-50/70 rounded-2xl border border-indigo-100 p-3.5 sm:p-4">
                    <p class="text-[9px] sm:text-[10px] font-bold text-indigo-600/70 uppercase tracking-wider mb-1">Terisi</p>
                    <p class="text-xl sm:text-2xl font-black text-indigo-700">{{ $totalGraded }}</p>
                    <p class="mt-1 text-[10px] text-indigo-600/70">nilai</p>
                </div>
                <div class="snap-center shrink-0 w-[132px] sm:w-auto bg-rose-50/70 rounded-2xl border border-rose-100 p-3.5 sm:p-4">
                    <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-1">Belum / Lewat</p>
                    <p class="text-xl sm:text-2xl font-black text-rose-700">{{ $totalOverdue }}</p>
                    <p class="mt-1 text-[10px] text-rose-600/70">terlambat</p>
                </div>
            </div>
        </div>

        {{-- Rata-rata per mapel --}}
        @if($subjectGroups->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                @foreach($subjectGroups as $group)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100 flex flex-col items-center justify-center shrink-0">
                                <span class="text-[7px] font-black uppercase leading-none text-indigo-400">Mapel</span>
                                <span class="mt-0.5 text-sm font-black leading-none">{{ strtoupper(substr($group['subject']->name ?? '?', 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-black text-gray-900 truncate">{{ $group['subject']->name ?? '-' }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    {{ $group['assignment_count'] }} tugas • {{ $group['graded_count'] }} nilai
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-lg font-black {{ $group['average_score'] !== null ? 'text-emerald-600' : 'text-gray-300' }}">
                                    {{ $group['average_score'] !== null ? number_format($group['average_score'], 1) : '-' }}
                                </p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata</p>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 bg-linear-to-r from-indigo-50 to-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-900 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-7 4h8m-8 4h5m-7 6h12a2 2 0 002-2V5a2 2 0 00-2-2H8.5L4 7.5V19a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-black text-gray-900">Rata-rata Keseluruhan</p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $totalStudents }} siswa</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-lg font-black {{ $avgScore !== null ? 'text-emerald-600' : 'text-gray-300' }}">
                                {{ $avgScore !== null ? number_format($avgScore, 1) : '-' }}
                            </p>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Kelas</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filter --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <button type="button" @click="filterOpen = !filterOpen"
                    class="w-full px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-colors"
                         :class="filterOpen ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <span class="text-xs sm:text-sm font-bold text-gray-800">Filter Nilai</span>
                        @if($activeFilterCount)
                            <p class="text-[10px] text-indigo-600 font-semibold">{{ $activeFilterCount }} filter aktif</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($activeFilterCount)
                        <a href="{{ route('wali-kelas.grades.index', array_filter(['wali_context' => $selectedWaliContextKey ?? null])) }}"
                           class="px-2 py-1 bg-red-50 text-red-500 rounded-lg text-[10px] font-bold"
                           onclick="event.stopPropagation()">Reset</a>
                    @endif
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                         :class="{ 'rotate-180': filterOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </button>

            <div x-show="filterOpen" x-collapse x-cloak>
                <form method="GET" action="{{ route('wali-kelas.grades.index') }}"
                      class="px-4 pb-4 sm:px-6 sm:pb-5 border-t border-gray-50 pt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if($selectedWaliContextKey ?? null)
                        <input type="hidden" name="wali_context" value="{{ $selectedWaliContextKey }}">
                    @endif
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Mata Pelajaran</label>
                        <select name="subject_id" class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all text-xs font-semibold">
                            <option value="">Semua mapel</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string) $subjectId === (string) $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Cari Siswa</label>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Nama / NIS siswa..."
                               class="w-full px-3 py-2.5 bg-gray-50 border-none rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 transition-all text-xs font-semibold">
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="w-full py-2.5 bg-gray-900 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-black transition-all">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Daftar siswa --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100 p-4 sm:p-5 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black text-gray-900">Daftar Siswa</h2>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $totalStudents }} siswa di {{ optional($class)->name }}</p>
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($students as $student)
                    @php
                        $studentGrades = $grades->where('student_id', $student->id);
                        $studentGraded = $studentGrades->where('score', '!==', null);
                        $studentAvg = $studentGraded->count() ? $studentGraded->avg('score') : null;
                        $studentOverdue = $studentGrades->sum('overdue');
                        $keyword = strtolower($search ?? '');
                        $nisText = strtolower(($student->nis ?? '') . ' ' . ($student->nisn ?? ''));
                        $hidden = $keyword && !str_contains(strtolower($student->name) . ' ' . $nisText, $keyword);
                    @endphp
                    <div class="transition-colors">
                        <div class="flex items-center gap-3 sm:gap-4 p-4 sm:px-5">
                            <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center justify-center shrink-0">
                                <span class="text-sm font-black">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-black text-gray-900 truncate">{{ $student->name }}</p>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $student->nis ?? $student->nisn ?? 'Siswa' }}</p>
                            </div>
                            <div class="hidden sm:flex items-center gap-4 shrink-0">
                                <div class="text-right">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata</p>
                                    <p class="text-base font-black {{ $studentAvg !== null ? 'text-emerald-600' : 'text-gray-300' }}">{{ $studentAvg !== null ? number_format($studentAvg, 1) : '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Terisi</p>
                                    <p class="text-base font-black text-gray-800">{{ $studentGraded->count() }}/{{ $studentGrades->count() }}</p>
                                </div>
                            </div>
                            <div class="sm:hidden text-right shrink-0">
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata</p>
                                <p class="text-base font-black {{ $studentAvg !== null ? 'text-emerald-600' : 'text-gray-300' }}">{{ $studentAvg !== null ? number_format($studentAvg, 1) : '-' }}</p>
                            </div>
                            @if($studentOverdue > 0)
                                <span class="shrink-0 inline-flex items-center px-2 py-1 bg-rose-50 text-rose-600 border border-rose-100 rounded-lg text-[9px] font-black uppercase tracking-wider">
                                    {{ $studentOverdue }} lewat
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-gray-200">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-800">Tidak ada siswa</h3>
                        <p class="text-sm text-gray-500 mt-1">Tidak ditemukan siswa di kelas ini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush
