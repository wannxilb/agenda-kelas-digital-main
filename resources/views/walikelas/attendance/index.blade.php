@extends('layouts.walikelas')

@section('title', 'Monitoring Presensi')
@section('header', 'Monitoring Presensi')
@section('content-padding', 'py-4 sm:py-8 pb-28 lg:pb-8')

@section('content')
@if($has_class ?? false)
@php
    $activeSearch = request('search', '');
    $activeStatusFilter = request('status', 'all');
    $totalStudentsBeforeStatusFilter = $totalStudentsBeforeStatusFilter ?? $students->count();
    $resolvedStatuses = $resolvedStatuses ?? [];
    $approvedAbsences = $approvedAbsences ?? collect();
    $displayStatuses = [];
    foreach ($students as $student) {
        $att = $student->attendances->first();
        $resolved = $resolvedStatuses[$student->id] ?? null;
        if ($resolved === 'not_yet') {
            $resolved = null;
        }
        $displayStatuses[$student->id] = $resolved ?? $att?->status;
    }
    $presentCount = $students->filter(fn($s) => ($displayStatuses[$s->id] ?? null) === 'present')->count();
    $excusedCount = $students->filter(fn($s) => ($displayStatuses[$s->id] ?? null) === 'excused')->count();
    $sickCount = $students->filter(fn($s) => ($displayStatuses[$s->id] ?? null) === 'sick')->count();
    $lateCount = $students->filter(fn($s) => ($displayStatuses[$s->id] ?? null) === 'late')->count();
    $absentCount = $students->filter(fn($s) => ($displayStatuses[$s->id] ?? null) === 'absent')->count();
    $noDataCount = $students->filter(fn($s) => empty($displayStatuses[$s->id]))->count();
    $totalPresent = $presentCount + $lateCount;
    $approvedLeaveCount = $excusedCount + $sickCount;
    $recordedCount = $totalPresent + $approvedLeaveCount + $absentCount;
    $rate = $students->count() > 0 ? round(($totalPresent / $students->count()) * 100, 1) : 0;
    $recordedRate = $students->count() > 0 ? round(($recordedCount / $students->count()) * 100, 1) : 0;
    $circumference = 97.4;
    $rateDash = round(($rate / 100) * $circumference, 1);
    $isToday = \Carbon\Carbon::parse($date)->isToday();
    $statusStyles = [
        'present' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'excused' => 'bg-sky-50 text-sky-700 border-sky-100',
        'sick' => 'bg-orange-50 text-orange-700 border-orange-100',
        'late' => 'bg-amber-50 text-amber-700 border-amber-100',
        'absent' => 'bg-rose-50 text-rose-700 border-rose-100',
        'unknown' => 'bg-amber-50 text-amber-700 border-amber-100',
    ];
    $statusAccents = [
        'present' => 'border-l-emerald-500',
        'excused' => 'border-l-sky-500',
        'sick' => 'border-l-orange-500',
        'late' => 'border-l-amber-500',
        'absent' => 'border-l-rose-500',
        'unknown' => 'border-l-amber-500',
    ];
    $statusDots = [
        'present' => 'bg-emerald-500',
        'excused' => 'bg-sky-500',
        'sick' => 'bg-orange-500',
        'late' => 'bg-amber-500',
        'absent' => 'bg-rose-500',
        'unknown' => 'bg-amber-500',
    ];
    $statusLabels = [
        'present' => 'Hadir',
        'excused' => 'Izin',
        'sick' => 'Sakit',
        'late' => 'Telat',
        'absent' => 'Alpha',
        'unknown' => 'Perlu Verifikasi',
    ];
@endphp

<div class="space-y-4 sm:space-y-6 pb-24 lg:pb-8"
     x-data="{
        search: @js($activeSearch),
        filter: @js($activeStatusFilter),
        expandedStudents: {},
        filteredCount: {{ ($has_class ?? false) ? $students->count() : 0 }},
        totalStudents: {{ ($has_class ?? false) ? $students->count() : 0 }},
        updateCount() {
            this.$nextTick(() => {
                this.filteredCount = new Set(
                    Array.from(document.querySelectorAll('[data-student-filter-row]:not([data-hidden])'))
                        .map((row) => row.dataset.studentFilterRow)
                ).size;
            });
        },
        shouldShow(name, status, nis = '') {
            const keyword = this.search.toLowerCase();
            const haystack = (name + ' ' + nis).toLowerCase();
            if (keyword && !haystack.includes(keyword)) return false;
            if (this.filter === 'all') return true;
            if (this.filter === 'none') return !status;
            return status === this.filter;
        }
     }"
     x-init="updateCount()">
    @include('walikelas.partials.context-filter')

    {{-- Date Selector Card --}}
    <form method="GET" action="{{ route('wali-kelas.attendance.index') }}" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        @if($selectedWaliContextKey ?? null)
            <input type="hidden" name="wali_context" value="{{ $selectedWaliContextKey }}">
        @endif
        @if($activeSearch !== '')
            <input type="hidden" name="search" value="{{ $activeSearch }}">
        @endif
        @if($activeStatusFilter !== 'all')
            <input type="hidden" name="status" value="{{ $activeStatusFilter }}">
        @endif
        <div class="p-4 sm:p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-base sm:text-lg font-black text-gray-900 tracking-tight">Kelas {{ $class->name ?? '-' }}</h1>
                    <p class="text-[11px] sm:text-xs text-gray-500 font-medium truncate">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</p>
                </div>
                @if($isToday)
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-bold rounded-lg border border-emerald-100 uppercase tracking-wider shrink-0">Hari Ini</span>
                @endif
            </div>
            <div class="flex gap-2 sm:gap-3">
                <div class="flex-1">
                    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                           class="block w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-transparent transition-all text-sm font-bold text-gray-900 shadow-inner">
                </div>
                <button type="submit"
                        class="shrink-0 px-4 sm:px-5 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-indigo-200/70 hover:bg-indigo-700 active:scale-95 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
    </form>

    {{-- Attendance Rate Hero (Mobile-first) --}}
    <div class="bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-5 text-white shadow-lg shadow-blue-200/60 relative overflow-hidden">
        <div class="absolute -top-8 -right-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-10 -left-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex items-center gap-4">
            {{-- Circular progress --}}
            <div class="relative shrink-0 w-16 h-16 sm:w-20 sm:h-20">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3"/>
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="white" stroke-width="3"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $rateDash }}, {{ $circumference }}"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-sm sm:text-base font-black">{{ $rate }}%</span>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[11px] font-semibold text-blue-100/90 tracking-wide">Hadir Fisik</p>
                <p class="text-lg sm:text-xl font-bold">{{ $totalPresent }} <span class="text-blue-200 font-medium text-sm">/ {{ $students->count() }} siswa</span></p>
                <p class="text-[11px] text-blue-100/80 mt-0.5">Hadir + telat. Izin/sakit dan alpha tetap dihitung sebagai status tercatat.</p>
            </div>
            <a href="{{ route('wali-kelas.attendance.report', array_filter(['wali_context' => $selectedWaliContextKey ?? null])) }}"
               class="shrink-0 p-2.5 bg-white/15 rounded-xl backdrop-blur-sm ring-1 ring-white/20 hover:bg-white/25 active:scale-95 transition-all"
               title="Laporan Presensi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </a>
        </div>
        <div class="relative mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-2xl bg-white/15 px-3 py-2 ring-1 ring-white/15 backdrop-blur-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-blue-100/80">Tercatat</p>
                <p class="mt-0.5 text-sm font-black">{{ $recordedCount }}/{{ $students->count() }}</p>
                <p class="text-[10px] font-semibold text-blue-100/75">{{ $recordedRate }}% status final</p>
            </div>
            <div class="rounded-2xl bg-white/15 px-3 py-2 ring-1 ring-white/15 backdrop-blur-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-blue-100/80">Izin/Sakit</p>
                <p class="mt-0.5 text-sm font-black">{{ $approvedLeaveCount }}</p>
                <p class="text-[10px] font-semibold text-blue-100/75">{{ $excusedCount }} izin / {{ $sickCount }} sakit</p>
            </div>
            <div class="rounded-2xl bg-white/15 px-3 py-2 ring-1 ring-white/15 backdrop-blur-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-blue-100/80">Alpha</p>
                <p class="mt-0.5 text-sm font-black">{{ $absentCount }}</p>
                <p class="text-[10px] font-semibold text-blue-100/75">tanpa izin</p>
            </div>
            <div class="rounded-2xl bg-white/15 px-3 py-2 ring-1 ring-white/15 backdrop-blur-sm">
                <p class="text-[9px] font-black uppercase tracking-wider text-blue-100/80">Belum</p>
                <p class="mt-0.5 text-sm font-black">{{ $noDataCount }}</p>
                <p class="text-[10px] font-semibold text-blue-100/75">belum absen</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards: horizontal scroll on mobile --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-6 gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">

            <div class="snap-center shrink-0 w-27.5 sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Hadir</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $presentCount }}</p>
            </div>

            <div class="snap-center shrink-0 w-27.5 sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Telat</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $lateCount }}</p>
            </div>

            <div class="snap-center shrink-0 w-27.5 sm:w-auto bg-sky-50/50 rounded-2xl border border-sky-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-sky-600/70 uppercase tracking-wider mb-1">Izin</p>
                <p class="text-xl sm:text-2xl font-black text-sky-600">{{ $excusedCount }}</p>
            </div>

            <div class="snap-center shrink-0 w-27.5 sm:w-auto bg-orange-50/50 rounded-2xl border border-orange-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-orange-600/70 uppercase tracking-wider mb-1">Sakit</p>
                <p class="text-xl sm:text-2xl font-black text-orange-600">{{ $sickCount }}</p>
            </div>

            <div class="snap-center shrink-0 w-27.5 sm:w-auto bg-rose-50/50 rounded-2xl border border-rose-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-1">Alpha</p>
                <p class="text-xl sm:text-2xl font-black text-rose-600">{{ $absentCount }}</p>
            </div>

            <div class="snap-center shrink-0 w-27.5 sm:w-auto bg-gray-50 rounded-2xl border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Belum</p>
                <p class="text-xl sm:text-2xl font-black text-gray-600">{{ $noDataCount }}</p>
            </div>

        </div>
        <p class="sm:hidden mt-1 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Attendance Bar --}}
    @if($students->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="w-full bg-gray-100 rounded-full h-2.5 sm:h-3 overflow-hidden flex">
                @if($presentCount > 0)
                    <div class="bg-emerald-500 h-full transition-all duration-700" style="width: {{ ($presentCount / $students->count()) * 100 }}%"></div>
                @endif
                @if($lateCount > 0)
                    <div class="bg-amber-500 h-full transition-all duration-700" style="width: {{ ($lateCount / $students->count()) * 100 }}%"></div>
                @endif
                @if($excusedCount > 0)
                    <div class="bg-sky-500 h-full transition-all duration-700" style="width: {{ ($excusedCount / $students->count()) * 100 }}%"></div>
                @endif
                @if($sickCount > 0)
                    <div class="bg-orange-500 h-full transition-all duration-700" style="width: {{ ($sickCount / $students->count()) * 100 }}%"></div>
                @endif
                @if($absentCount > 0)
                    <div class="bg-rose-500 h-full transition-all duration-700" style="width: {{ ($absentCount / $students->count()) * 100 }}%"></div>
                @endif
            </div>
            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 mt-3">
                @foreach([['Hadir','bg-emerald-500'], ['Telat','bg-amber-500'], ['Izin','bg-sky-500'], ['Sakit','bg-orange-500'], ['Alpha','bg-rose-500']] as [$label, $color])
                    <span class="inline-flex items-center gap-1 text-[9px] font-bold text-gray-400 uppercase tracking-wider">
                        <span class="w-2 h-2 {{ $color }} rounded-full"></span>{{ $label }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Search & Filter --}}
    <div class="space-y-3">
        <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-3 sm:p-4">
                <div class="flex items-center gap-2 mb-3 sm:hidden">
                    <span class="inline-flex items-center justify-center w-7 h-7 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 12.414V19a1 1 0 01-.553.894l-4 2A1 1 0 019 21v-8.586L3.293 6.707A1 1 0 013 6V4z"></path>
                        </svg>
                    </span>
                    <p class="text-xs font-black text-gray-900">Filter siswa</p>
                    <span id="wali-attendance-filter-count" class="ml-auto text-[10px] font-black text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-1 rounded-lg">{{ $students->count() }} / {{ $students->count() }}</span>
                </div>
                <form method="GET" action="{{ route('wali-kelas.attendance.index') }}" id="wali-attendance-search-form" onsubmit="return false">
                    @if($selectedWaliContextKey ?? null)
                        <input type="hidden" name="wali_context" value="{{ $selectedWaliContextKey }}">
                    @endif
                    <input type="hidden" name="date" value="{{ $date }}">
                    @if($activeStatusFilter !== 'all')
                        <input type="hidden" name="status" value="{{ $activeStatusFilter }}">
                    @endif
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="search" id="wali-attendance-search" value="{{ $activeSearch }}" x-model="search" @input="updateCount()" autocomplete="off" placeholder="Cari nama atau NIS siswa..."
                               class="block w-full pl-9 pr-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-semibold">
                    </div>
                </form>
            </div>
        </div>

        {{-- Status Filter Chips --}}
        <div class="flex gap-2 overflow-x-auto hide-scrollbar pb-1 -mx-1 px-1">
            @foreach([
                ['all', 'Semua', 'bg-indigo-600 text-white border-indigo-600', 'bg-gray-50 text-gray-600 border-gray-200'],
                ['present', 'Hadir', 'bg-emerald-500 text-white border-emerald-500', 'bg-gray-50 text-gray-600 border-gray-200'],
                ['late', 'Telat', 'bg-amber-500 text-white border-amber-500', 'bg-gray-50 text-gray-600 border-gray-200'],
                ['excused', 'Izin', 'bg-sky-500 text-white border-sky-500', 'bg-gray-50 text-gray-600 border-gray-200'],
                ['sick', 'Sakit', 'bg-orange-500 text-white border-orange-500', 'bg-gray-50 text-gray-600 border-gray-200'],
                ['absent', 'Alpha', 'bg-rose-500 text-white border-rose-500', 'bg-gray-50 text-gray-600 border-gray-200'],
                ['none', 'Belum', 'bg-gray-600 text-white border-gray-600', 'bg-gray-50 text-gray-600 border-gray-200'],
            ] as [$value, $label, $activeClass, $inactiveClass])
                <a href="{{ route('wali-kelas.attendance.index', array_merge(request()->except('status'), $value === 'all' ? [] : ['status' => $value])) }}"
                   class="shrink-0 px-3.5 py-2 rounded-xl text-[11px] font-bold border transition-all active:scale-95 {{ $activeStatusFilter === $value ? $activeClass . ' shadow-md scale-[1.02]' : $inactiveClass }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Student List --}}
    <div class="sm:bg-white sm:rounded-3xl sm:border sm:border-gray-100 sm:shadow-sm sm:overflow-hidden">
        <div class="hidden sm:flex px-6 py-5 border-b border-gray-50 items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 tracking-tight uppercase">Daftar Siswa</h3>
            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100">{{ $students->count() }} Siswa</span>
        </div>

        {{-- Mobile: Card List --}}
        <div class="sm:hidden space-y-3">
            @forelse($students as $student)
                @php
                    $att = $student->attendances->first();
                    $attStatus = $displayStatuses[$student->id] ?? null;
                    $approvedAbsence = $approvedAbsences->get($student->id);
                    $attendanceNote = $att?->note ?: $approvedAbsence?->reason;
                @endphp
                <div data-student-filter-row="{{ $student->id }}"
                     data-student-name="{{ \Illuminate\Support\Str::lower($student->name) }}"
                     data-student-nis="{{ \Illuminate\Support\Str::lower((string) $student->nis) }}"
                     class="bg-white rounded-2xl shadow-sm border border-gray-100 border-l-4 {{ $attStatus ? ($statusAccents[$attStatus] ?? 'border-l-gray-200') : 'border-l-gray-200' }} overflow-hidden">
                    <button type="button" @click="expandedStudents[{{ $student->id }}] = !expandedStudents[{{ $student->id }}]"
                            class="w-full px-4 py-3.5 flex items-center gap-3 text-left active:bg-gray-50 transition-colors">
                        <div class="w-11 h-11 bg-linear-to-br from-blue-500 to-indigo-600 text-white rounded-2xl flex items-center justify-center text-xs font-black shadow-lg shadow-indigo-100 shrink-0">
                            {{ strtoupper(substr($student->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-gray-900 truncate">{{ $student->name }}</p>
                            <div class="mt-1 flex items-center gap-2">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">NIS: {{ $student->nis }}</p>
                                @if($att && $att->check_in_time)
                                    <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                    <p class="text-[10px] font-bold text-gray-500">{{ \Carbon\Carbon::parse($att->check_in_time)->format('H:i') }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 min-w-0">
                            @if($attStatus)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 {{ $statusStyles[$attStatus] ?? 'bg-gray-50 text-gray-700 border-gray-100' }} text-[9px] font-black uppercase tracking-widest rounded-lg border">
                                    <span class="w-1.5 h-1.5 {{ $statusDots[$attStatus] ?? 'bg-gray-400' }} rounded-full"></span>
                                    {{ $statusLabels[$attStatus] ?? $attStatus }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 text-gray-400 text-[9px] font-black uppercase tracking-widest rounded-lg border border-gray-100">
                                    <span class="w-1.5 h-1.5 bg-gray-300 rounded-full"></span>
                                    Belum
                                </span>
                            @endif
                            <svg class="w-4 h-4 text-gray-300 transition-transform duration-200" :class="expandedStudents[{{ $student->id }}] ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>
                    <div x-show="expandedStudents[{{ $student->id }}]" x-collapse x-cloak class="px-4 pb-4 border-t border-gray-50">
                        <div class="pt-3 grid grid-cols-2 gap-2.5">
                            <div class="p-3 bg-gray-50 rounded-xl">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Status</span>
                                <span class="text-sm font-black text-gray-800">
                                    {{ $attStatus ? ($statusLabels[$attStatus] ?? $attStatus) : 'Belum diabsen' }}
                                </span>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-xl">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Waktu Absen</span>
                                <span class="text-sm font-black text-gray-800">
                                    {{ $att && $att->check_in_time ? \Carbon\Carbon::parse($att->check_in_time)->format('H:i') : '-' }}
                                </span>
                            </div>
                            @if($attendanceNote)
                                <div class="col-span-2 p-3 bg-indigo-50/60 rounded-xl border border-indigo-100">
                                    <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider block mb-1">Keterangan</span>
                                    @if($approvedAbsence)
                                        <p class="text-[10px] font-black uppercase tracking-wider text-indigo-500">Ketidakhadiran disetujui</p>
                                    @endif
                                    <p class="mt-1 text-xs text-indigo-800 font-medium leading-relaxed">{{ $attendanceNote }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <p class="text-sm font-bold text-gray-500">Tidak ada data siswa</p>
                </div>
            @endforelse

            {{-- Empty search/filter state --}}
            <div id="wali-attendance-empty-filter"
                 class="hidden bg-white rounded-2xl border border-gray-100 p-8 text-center">
                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Siswa tidak ditemukan</p>
                <p class="text-xs text-gray-400 mt-1">Coba ubah filter atau kata kunci pencarian</p>
                <button type="button" @click="search = ''; filter = 'all'; updateCount()"
                        class="mt-3 text-xs font-bold text-blue-600 hover:underline">Reset Filter</button>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Siswa</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Waktu</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($students as $student)
                        @php
                            $att = $student->attendances->first();
                            $attStatus = $displayStatuses[$student->id] ?? $att?->status;
                            $approvedAbsence = $approvedAbsences->get($student->id);
                            $attendanceNote = $att?->note ?: $approvedAbsence?->reason;
                        @endphp
                        <tr data-student-filter-row="{{ $student->id }}"
                            data-student-name="{{ \Illuminate\Support\Str::lower($student->name) }}"
                            data-student-nis="{{ \Illuminate\Support\Str::lower((string) $student->nis) }}"
                            class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-[10px] font-black shadow-sm group-hover:scale-110 transition-transform">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">{{ $student->name }}</span>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">NIS: {{ $student->nis }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($attStatus)
                                    <span class="px-3 py-1.5 {{ $statusStyles[$attStatus] ?? 'bg-gray-50 text-gray-700 border-gray-100' }} text-[10px] font-black uppercase tracking-widest rounded-lg border inline-flex items-center gap-1.5">
                                        {{ $statusLabels[$attStatus] ?? $attStatus }}
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 bg-gray-50 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-lg border border-gray-100 inline-flex items-center gap-1.5">
                                        Belum Absen
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-gray-700">
                                    {{ $att && $att->check_in_time ? \Carbon\Carbon::parse($att->check_in_time)->format('H:i') : '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($attendanceNote)
                                    <div class="max-w-xs">
                                        @if($approvedAbsence)
                                            <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Ketidakhadiran disetujui</p>
                                        @endif
                                        <p class="text-xs text-gray-500 font-medium">{{ $attendanceNote }}</p>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-500 font-medium">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">Tidak ada data siswa</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info Tips --}}
    <div class="bg-linear-to-br from-blue-50 to-indigo-50 rounded-2xl p-4 sm:p-5 border border-blue-100 relative overflow-hidden">
        <svg class="absolute -right-4 -bottom-4 w-20 h-20 text-blue-500/10" fill="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="relative z-10">
            <h3 class="text-[11px] font-bold text-blue-800 tracking-wide mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Informasi
            </h3>
            <ul class="space-y-1.5 text-xs text-blue-700/90 font-medium">
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Data presensi diisi oleh sekretaris kelas setiap hari.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Gunakan filter status untuk melihat siswa berdasarkan kehadiran.
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-500 mt-0.5">•</span>
                    Ketuk kartu siswa untuk melihat detail waktu absen dan keterangan.
                </li>
            </ul>
        </div>
    </div>

</div>

@else
<div class="space-y-4 sm:space-y-6 pb-24 lg:pb-8">
    <div class="bg-white border border-gray-100 rounded-2xl p-10 sm:p-16 text-center shadow-sm">
        <div class="flex flex-col items-center gap-4">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-50 rounded-2xl flex items-center justify-center border-2 border-dashed border-gray-200">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-black text-gray-900 mb-1">Akses Terbatas</h3>
                <p class="text-xs sm:text-sm text-gray-500 font-medium max-w-sm mx-auto">Halaman ini hanya tersedia untuk Wali Kelas yang sudah ditugaskan ke kelas tertentu.</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('wali-attendance-search');
        const count = document.getElementById('wali-attendance-filter-count');
        const emptyState = document.getElementById('wali-attendance-empty-filter');
        const rows = Array.from(document.querySelectorAll('[data-student-filter-row]'));

        if (!input || rows.length === 0) return;

        function applySearchFilter() {
            const keyword = input.value.trim().toLowerCase();
            const matchedIds = new Set();

            rows.forEach((row) => {
                const haystack = `${row.dataset.studentName || ''} ${row.dataset.studentNis || ''}`;
                const matched = !keyword || haystack.includes(keyword);

                row.classList.toggle('hidden', !matched);
                if (matched) {
                    matchedIds.add(row.dataset.studentFilterRow);
                }
            });

            if (count) {
                count.textContent = `${matchedIds.size} / {{ $students->count() }}`;
            }

            if (emptyState) {
                emptyState.classList.toggle('hidden', !keyword || matchedIds.size > 0);
            }
        }

        input.addEventListener('input', applySearchFilter);
        applySearchFilter();
    })();
</script>
@endpush
