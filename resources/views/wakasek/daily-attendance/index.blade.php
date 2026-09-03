@extends('layouts.wakasek')

@section('title', 'Monitoring Kehadiran')
@section('header', 'Monitoring Kehadiran')

@php
    $totalCount = $records->count();
    $checkedIn = $records->whereNotNull('check_in_at')->count();
    $checkedOut = $records->whereNotNull('check_out_at')->count();
    $lateCount = $records->filter(fn ($record) => (int) $record->late_minutes > 0)->count();
    $verificationCount = $records->filter(fn ($record) => $record->check_in_status === 'alpha' || $record->check_out_status === 'alpha')->count();
    $unconfirmedCount = max(0, $totalCount - $checkedIn);
    $attendanceRate = $totalCount > 0 ? round(($checkedIn / $totalCount) * 100) : 0;
    $dateLabel = \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y');
    $today = \Carbon\Carbon::parse($date)->isToday();
    $firstRecord = $records->first();
    $dailyAttendanceSetting = $firstRecord?->student?->institution_id
        ? \App\Models\DailyAttendanceSetting::forInstitution($firstRecord->student->institution_id)
        : null;
    $verificationDeadlineTime = $dailyAttendanceSetting?->check_in_verification_deadline ?? '23:59:00';
@endphp

@section('content')
<div class="space-y-4 sm:space-y-5 pb-24 lg:pb-8">

    {{-- Header --}}
    <section class="relative overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-blue-100/70"></div>
        <div class="absolute -left-12 bottom-0 h-32 w-32 rounded-full bg-emerald-100/50"></div>
        <div class="relative p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-blue-600">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        {{ $dateLabel }}
                    </p>
                    <h1 class="mt-1 text-xl sm:text-2xl font-black tracking-tight text-gray-900">Monitoring Kehadiran</h1>
                    <p class="mt-1 text-xs sm:text-sm leading-relaxed text-gray-500">{{ $class?->name ?? 'Semua kelas terkait' }} · {{ $totalCount }} siswa</p>
                </div>
                <div class="shrink-0 rounded-2xl bg-blue-600 px-3 py-2 text-center text-white shadow-lg shadow-blue-200/60">
                    <p class="text-[10px] font-bold uppercase opacity-80">Masuk</p>
                    <p class="text-base font-black">{{ $attendanceRate }}%</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-3 gap-2.5">
                <div class="rounded-2xl bg-gray-50/90 p-3 ring-1 ring-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Siswa</p>
                    <p class="mt-1 text-xl font-black text-gray-900">{{ $totalCount }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50 p-3 ring-1 ring-emerald-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Masuk</p>
                    <p class="mt-1 text-xl font-black text-emerald-700">{{ $checkedIn }}</p>
                </div>
                <div class="rounded-2xl bg-blue-50 p-3 ring-1 ring-blue-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Pulang</p>
                    <p class="mt-1 text-xl font-black text-blue-700">{{ $checkedOut }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Filter --}}
    <form method="GET"
          class="rounded-3xl border border-gray-100 bg-white p-3 shadow-sm"
          x-data="{ filterOpen: {{ request()->hasAny(['date', 'class_id']) ? 'true' : 'false' }} }">
        <button type="button"
                @click="filterOpen = !filterOpen"
                class="flex w-full items-center justify-between gap-3 rounded-2xl bg-gray-50 px-4 py-3 text-left ring-1 ring-gray-100 transition hover:bg-gray-100 active:scale-[0.98]"
                :class="filterOpen ? 'bg-white ring-2 ring-blue-500' : ''">
            <span class="flex min-w-0 items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-200/50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-black text-gray-900">Filter monitoring</span>
                    <span class="block truncate text-[11px] font-semibold text-gray-400">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}{{ ' - ' . ($class?->name ?? 'Semua kelas') }}
                    </span>
                </span>
            </span>
            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform" :class="filterOpen ? 'rotate-180 text-blue-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div class="grid transition-[grid-template-rows] duration-300 ease-out grid-rows-[0fr]"
             :class="filterOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
            <div class="min-h-0 overflow-hidden">
            <div class="mt-3 grid gap-2 rounded-2xl border border-gray-100 bg-gray-50 p-2 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto]">
                <label class="grid gap-1.5">
                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Tanggal absensi</span>
                    <input type="date" name="date" value="{{ $date }}" class="rounded-2xl border-0 bg-white px-4 py-3 text-sm font-bold text-gray-700 ring-1 ring-gray-100 focus:ring-2 focus:ring-blue-500">
                </label>
                <label class="grid gap-1.5">
                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Kelas</span>
                    <select name="class_id" class="w-full rounded-2xl border-0 bg-white px-4 py-3 text-sm font-bold text-gray-700 ring-1 ring-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua kelas</option>
                        @foreach($classes as $optionClass)
                            <option value="{{ $optionClass->id }}" @selected((string) ($selectedClassId ?? '') === (string) $optionClass->id)>{{ $optionClass->name }}</option>
                        @endforeach
                    </select>
                </label>
                <a href="{{ route('wakasek.daily-attendance.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-black text-gray-500 ring-1 ring-gray-100 transition hover:bg-gray-100 active:scale-[0.98] sm:self-end">Reset</a>
                <button class="rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-200/50 active:scale-[0.98] sm:self-end">Tampilkan</button>
            </div>
            </div>
        </div>
    </form>

    {{-- Stat cards --}}
    <section class="grid grid-cols-3 gap-2.5">
        <div class="rounded-2xl border border-rose-100 bg-rose-50 p-3 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-rose-500">Terlambat</p>
            <p class="mt-1 text-xl font-black text-rose-700">{{ $lateCount }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-3 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Verifikasi</p>
            <p class="mt-1 text-xl font-black text-amber-700">{{ $verificationCount }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-3 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Belum absen</p>
            <p class="mt-1 text-xl font-black text-gray-700">{{ $unconfirmedCount }}</p>
        </div>
    </section>

    {{-- Per-class monitoring --}}
    <section class="md:overflow-hidden md:rounded-3xl md:border md:border-gray-100 md:bg-white md:shadow-sm">
        <div class="flex items-center justify-between gap-3 px-5 pt-5 pb-3">
            <div>
                <h2 class="text-base font-black text-gray-900">Rekap per Kelas</h2>
                <p class="mt-0.5 text-xs text-gray-500">Tingkat kehadiran {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}</p>
            </div>
            <span class="shrink-0 rounded-xl bg-white px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-gray-400 ring-1 ring-gray-100">{{ $classStats->count() }} kelas</span>
        </div>

        <div class="space-y-3 p-4 sm:p-5 md:pt-0">
            @forelse($classStats as $stat)
                @php
                    $cls = $stat->class;
                    $rate = $stat->attendance_rate;
                    $isSelected = $selectedClassId && (int) $selectedClassId === (int) $cls->id;
                    $status = $stat->total === 0
                        ? ['label' => 'Belum Ada Data', 'chip' => 'bg-gray-100 text-gray-500 ring-gray-100']
                        : ($rate >= 80
                            ? ['label' => 'Lancar', 'chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-100']
                            : ($rate >= 50
                                ? ['label' => 'Perlu Perhatian', 'chip' => 'bg-amber-50 text-amber-700 ring-amber-100']
                                : ['label' => 'Kritis', 'chip' => 'bg-rose-50 text-rose-700 ring-rose-100']));
                    $bar = $rate >= 80 ? 'from-emerald-500 to-emerald-600' : ($rate >= 50 ? 'from-amber-500 to-amber-600' : 'from-rose-500 to-rose-600');
                    $barEmpty = $rate >= 80 ? 'bg-emerald-100' : ($rate >= 50 ? 'bg-amber-100' : 'bg-rose-100');
                @endphp
                <a href="{{ route('wakasek.daily-attendance.index', ['date' => $date, 'class_id' => $cls->id]) }}"
                   class="block rounded-2xl border bg-white p-4 transition active:scale-[0.99] sm:hover:border-blue-200 sm:hover:shadow-md {{ $isSelected ? 'border-blue-400 ring-2 ring-blue-200/60' : 'border-gray-100 shadow-sm' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-gray-900 truncate">{{ $cls->name }}</span>
                                @if($isSelected)
                                    <span class="shrink-0 rounded-lg bg-blue-600 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-white">Dipilih</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-[11px] font-semibold text-gray-400 truncate">
                                {{ $cls->homeroomTeacher->name ?? 'Wali kelas belum diisi' }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-lg px-2 py-1 text-[9px] font-black uppercase tracking-wider ring-1 {{ $status['chip'] }}">{{ $status['label'] }}</span>
                    </div>

                    <div class="mt-4 grid grid-cols-4 gap-2">
                        <div class="rounded-xl bg-gray-50 px-2 py-1.5 text-center">
                            <p class="text-sm font-black text-gray-900 leading-none">{{ $stat->checked_in }}</p>
                            <p class="mt-1 text-[8px] font-bold uppercase tracking-wide text-gray-400">Masuk</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 px-2 py-1.5 text-center">
                            <p class="text-sm font-black text-blue-700 leading-none">{{ $stat->checked_out }}</p>
                            <p class="mt-1 text-[8px] font-bold uppercase tracking-wide text-blue-500">Pulang</p>
                        </div>
                        <div class="rounded-xl bg-amber-50 px-2 py-1.5 text-center">
                            <p class="text-sm font-black text-amber-700 leading-none">{{ $stat->late }}</p>
                            <p class="mt-1 text-[8px] font-bold uppercase tracking-wide text-amber-500">Telat</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 px-2 py-1.5 text-center">
                            <p class="text-sm font-black text-rose-700 leading-none">{{ $stat->not_checked_in }}</p>
                            <p class="mt-1 text-[8px] font-bold uppercase tracking-wide text-rose-500">Belum absen</p>
                        </div>
                    </div>

                    <div class="mt-3 space-y-2">
                        <div>
                            <div class="flex items-center justify-between text-[10px] font-bold">
                                <span class="text-gray-400 uppercase tracking-wider">Masuk</span>
                                <span class="text-gray-600">{{ $rate }}%</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full {{ $barEmpty }} overflow-hidden">
                                <div class="h-full rounded-full bg-linear-to-r {{ $bar }} transition-all duration-700" style="width: {{ max($rate, 4) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-[10px] font-bold">
                                <span class="text-gray-400 uppercase tracking-wider">Pulang</span>
                                <span class="text-gray-600">{{ $stat->checkout_rate }}%</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-blue-100 overflow-hidden">
                                <div class="h-full rounded-full bg-linear-to-r from-sky-500 to-blue-600 transition-all duration-700" style="width: {{ max($stat->checkout_rate, 4) }}%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 text-gray-300">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <p class="mt-3 text-sm font-bold text-gray-700">{{ $emptyText }}</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Detail siswa per kelas (hanya saat kelas dipilih) --}}
    @if($class)
    <section class="md:overflow-hidden md:rounded-3xl md:border md:border-gray-100 md:bg-white md:shadow-sm"
             x-data="{
                search: '',
                shouldShow(name) {
                    return !this.search || name.toLowerCase().includes(this.search.toLowerCase());
                }
             }">
        <div class="flex items-center justify-between gap-3 px-5 pt-5 pb-3">
            <div class="min-w-0">
                <h2 class="text-base font-black text-gray-900 truncate">Detail Siswa · {{ $class->name }}</h2>
                <p class="mt-0.5 text-xs text-gray-500">Status masuk/pulang siswa</p>
            </div>
            <a href="{{ route('wakasek.daily-attendance.index', ['date' => $date]) }}" class="shrink-0 rounded-xl bg-gray-50 px-3 py-1.5 text-[10px] font-black text-gray-500 ring-1 ring-gray-100 transition hover:bg-gray-100">Semua kelas</a>
        </div>

        <div class="px-5 pb-3">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" x-model="search" placeholder="Cari nama / NIS siswa..."
                       class="w-full rounded-2xl border-0 bg-gray-50 py-2.5 pl-9 pr-3 text-sm font-semibold text-gray-800 ring-1 ring-gray-100 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="hidden gap-4 border-t border-gray-100 bg-gray-50 px-5 py-3 text-[10px] font-black uppercase tracking-wider text-gray-400 md:grid md:grid-cols-[minmax(200px,1fr)_110px_110px_120px]">
            <div>Siswa</div>
            <div class="text-center">Masuk</div>
            <div class="text-center">Pulang</div>
            <div class="text-center">Status</div>
        </div>
        <div class="space-y-3 pb-2 md:space-y-0 md:divide-y md:divide-gray-100 md:pb-0">
            @forelse($records as $record)
                @php
                    $studentInitial = strtoupper(substr($record->student->name ?? '-', 0, 1));
                    $isLate = (int) $record->late_minutes > 0;
                    $needsVerification = $record->check_in_status === 'alpha' || $record->check_out_status === 'alpha';
                    $suspicious = (bool) $record->check_in_suspicious || (bool) $record->check_out_suspicious;
                    $isEarly = (int) $record->early_leave_minutes > 0;

                    $masukBadge = $record->check_in_at
                        ? ($record->check_in_status === 'alpha'
                            ? ['label' => 'Alpha', 'chip' => 'bg-rose-50 text-rose-700 ring-rose-100']
                            : ($needsVerification
                                ? ['label' => 'Verifikasi', 'chip' => 'bg-amber-50 text-amber-700 ring-amber-100']
                                : ($isLate
                                    ? ['label' => 'Terlambat', 'chip' => 'bg-rose-50 text-rose-700 ring-rose-100']
                                    : ($suspicious
                                        ? ['label' => 'Titip', 'chip' => 'bg-rose-50 text-rose-700 ring-rose-100']
                                        : ['label' => 'Tepat', 'chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-100']))))
                        : null;

                    $pulangBadge = $record->check_out_at
                        ? ($isEarly
                            ? ['label' => 'Cepat', 'chip' => 'bg-amber-50 text-amber-700 ring-amber-100']
                            : ['label' => 'Selesai', 'chip' => 'bg-blue-50 text-blue-700 ring-blue-100'])
                        : null;

                    $approvedTidakHadir = $record->earlyLeaveRequests
                        ->filter(fn ($request) => $request->status === 'approved' && $request->isTidakHadirCategory() && $request->coversDate($date))
                        ->first();
                    $approvedDispenRequest = $record->earlyLeaveRequests
                        ->filter(fn ($request) => $request->status === 'approved' && $request->isDispenCategory() && $request->coversDate($date))
                        ->first();
                    $noCheckInOverdue = now()->greaterThan(\Carbon\Carbon::parse($date . ' ' . $verificationDeadlineTime));
                    $noCheckInBadge = $approvedTidakHadir
                        ? ['label' => $approvedTidakHadir->category === 'sakit' ? 'Sakit' : 'Izin', 'chip' => 'bg-rose-50 text-rose-700 ring-rose-100']
                        : ($approvedDispenRequest
                            ? ['label' => 'Hadir', 'chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-100']
                            : ($noCheckInOverdue
                                ? ['label' => 'Alpha', 'chip' => 'bg-rose-50 text-rose-700 ring-rose-100']
                                : ['label' => 'Belum absen', 'chip' => 'bg-gray-100 text-gray-500 ring-gray-100']));

                    $statusBadge = $record->check_in_at
                        ? ['label' => 'Hadir', 'chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-100']
                        : $noCheckInBadge;
                @endphp

                <div class="hidden gap-4 px-5 py-4 transition-colors hover:bg-gray-50/70 md:grid md:items-center md:grid-cols-[minmax(200px,1fr)_110px_110px_120px]"
                     x-show='shouldShow(@json($record->student->name ?? ""))' x-cloak>
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-blue-500 to-indigo-600 text-xs font-black text-white">{{ $studentInitial }}</div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-gray-900">{{ $record->student->name ?? '-' }}</p>
                            <p class="text-[11px] font-semibold text-gray-400">NIS: {{ $record->student->nis ?? '' }}</p>
                        </div>
                    </div>
                    <div class="text-center">
                        @if($record->check_in_at)
                            <span class="inline-flex flex-col items-center gap-1">
                                <span class="text-sm font-black text-gray-900">{{ $record->check_in_at->format('H:i') }}</span>
                                @if($masukBadge)
                                    <span class="rounded-lg px-2 py-0.5 text-[9px] font-black ring-1 {{ $masukBadge['chip'] }}">{{ $masukBadge['label'] }}</span>
                                @endif
                            </span>
                        @else
                            <span class="inline-flex rounded-lg px-2 py-0.5 text-[9px] font-black ring-1 {{ $noCheckInBadge['chip'] }}">{{ $noCheckInBadge['label'] }}</span>
                        @endif
                    </div>
                    <div class="text-center">
                        @if($record->check_out_at)
                            <span class="inline-flex flex-col items-center gap-1">
                                <span class="text-sm font-black text-gray-900">{{ $record->check_out_at->format('H:i') }}</span>
                                @if($pulangBadge)
                                    <span class="rounded-lg px-2 py-0.5 text-[9px] font-black ring-1 {{ $pulangBadge['chip'] }}">{{ $pulangBadge['label'] }}</span>
                                @endif
                            </span>
                        @else
                            <span class="text-xs font-bold text-gray-300">Belum</span>
                        @endif
                    </div>
                    <div class="text-center">
                        <span class="rounded-lg px-2 py-1 text-[9px] font-black uppercase tracking-wider ring-1 {{ $statusBadge['chip'] }}">{{ $statusBadge['label'] }}</span>
                    </div>
                </div>

                <article x-show='shouldShow(@json($record->student->name ?? ""))' x-cloak
                         class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm md:hidden">
                    <div class="flex items-center gap-3 p-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-sm font-black text-blue-700 ring-1 ring-blue-100">{{ $studentInitial }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-black text-gray-900">{{ $record->student->name ?? '-' }}</p>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $record->student->nis ?? '' }}</p>
                        </div>
                        <span class="shrink-0 rounded-lg px-2 py-1 text-[9px] font-black uppercase tracking-wider ring-1 {{ $statusBadge['chip'] }}">{{ $statusBadge['label'] }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 px-4 pb-4">
                        <div class="rounded-2xl bg-gray-50 px-3 py-2">
                            <p class="mb-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Masuk</p>
                            @if($record->check_in_at)
                                <p class="text-sm font-black text-gray-900">{{ $record->check_in_at->format('H:i') }}</p>
                                @if($masukBadge)
                                    <span class="mt-1 inline-flex rounded-lg px-2 py-0.5 text-[9px] font-black ring-1 {{ $masukBadge['chip'] }}">{{ $masukBadge['label'] }}</span>
                                @endif
                            @else
                                <span class="inline-flex rounded-lg px-2 py-0.5 text-[9px] font-black ring-1 {{ $noCheckInBadge['chip'] }}">{{ $noCheckInBadge['label'] }}</span>
                            @endif
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-3 py-2">
                            <p class="mb-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Pulang</p>
                            @if($record->check_out_at)
                                <p class="text-sm font-black text-gray-900">{{ $record->check_out_at->format('H:i') }}</p>
                                @if($pulangBadge)
                                    <span class="mt-1 inline-flex rounded-lg px-2 py-0.5 text-[9px] font-black ring-1 {{ $pulangBadge['chip'] }}">{{ $pulangBadge['label'] }}</span>
                                @endif
                            @else
                                <span class="text-xs font-bold text-gray-300">Belum</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-sm md:rounded-none md:border-0 md:shadow-none">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 text-gray-300">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <p class="mt-3 text-sm font-bold text-gray-700">{{ $emptyText }}</p>
                </div>
            @endforelse
        </div>
    </section>
    @endif
</div>
@endsection
