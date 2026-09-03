@extends($layout)

@section('title', $title)
@section('header', $title)
@section('content-padding', 'pt-4 sm:pt-6 pb-24 lg:pb-8')

@php
    $totalCount = $records->count();
    $lateCount = $records->filter(fn ($record) => (int) $record->late_minutes > 0)->count();
    $verificationCount = $records->filter(fn ($record) => $record->check_in_status === 'alpha' || $record->check_out_status === 'alpha')->count();
    $checkedIn = $records->whereNotNull('check_in_at')->count();
    $checkedOut = $records->whereNotNull('check_out_at')->count();
    $attendanceRate = $totalCount > 0 ? round(($checkedIn / $totalCount) * 100) : 0;
    $dateLabel = \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y');
    $showClassColumn = (bool) ($showClass ?? false);
    $firstRecord = $records->first();
    $dailyAttendanceSetting = $firstRecord?->student?->institution_id
        ? \App\Models\DailyAttendanceSetting::forInstitution($firstRecord->student->institution_id)
        : null;
    $verificationDeadlineTime = $dailyAttendanceSetting?->check_in_verification_deadline ?? '23:59:00';
    $noCheckInOverdue = now()->greaterThan(\Carbon\Carbon::parse($date . ' ' . $verificationDeadlineTime));
    $operationalEndTime = $firstRecord?->student?->institution_id
        ? \App\Models\Setting::get('operational_end_time', '16:00', $firstRecord->student->institution_id)
        : '16:00';
    $hasApprovedFullDayStatus = function ($record) use ($date) {
        return $record->earlyLeaveRequests->contains(fn ($request) => $request->status === 'approved'
            && ($request->isTidakHadirCategory() || $request->isDispenCategory())
            && $request->coversDate($date));
    };
    $notYetEnteredCount = $records->filter(fn ($record) => $record->check_in_at === null && !$noCheckInOverdue && !$hasApprovedFullDayStatus($record))->count();
    $alphaCount = $records->filter(fn ($record) => $record->check_in_at === null && $noCheckInOverdue && !$hasApprovedFullDayStatus($record))->count();
    $exceptionCount = $lateCount + $verificationCount + $notYetEnteredCount + $alphaCount;
@endphp

@section('content')
<div class="space-y-4 sm:space-y-5 pb-24 sm:pb-6">
    <section class="relative overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-blue-100/70"></div>
        <div class="absolute -left-12 bottom-0 h-32 w-32 rounded-full bg-emerald-100/50"></div>
        <div class="relative p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-wider text-blue-600">{{ $dateLabel }}</p>
                    <h1 class="mt-1 text-xl sm:text-2xl font-black tracking-tight text-gray-900">{{ $title }}</h1>
                    <p class="mt-1 text-xs sm:text-sm leading-relaxed text-gray-500">{{ $class?->name ?? 'Semua kelas terkait' }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if(auth()->user()->hasRole('wali_kelas'))
                        <a href="{{ route('wali-kelas.daily-attendance.early-leave.index') }}"
                           class="rounded-2xl border border-blue-100 bg-blue-50 px-3 py-2 text-center text-[10px] font-black text-blue-700 transition hover:bg-blue-100">
                            <span class="block uppercase tracking-wider">Pengajuan</span>
                            <span class="block font-black">Izin / Dispen</span>
                        </a>
                    @endif
                    <div class="shrink-0 rounded-2xl bg-blue-600 px-3 py-2 text-center text-white shadow-lg shadow-blue-200/60">
                        <p class="text-[10px] font-bold uppercase opacity-80">Hadir</p>
                        <p class="text-base font-black">{{ $attendanceRate }}%</p>
                    </div>
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
                    <span class="block text-sm font-black text-gray-900">Filter presensi</span>
                    <span class="block truncate text-[11px] font-semibold text-gray-400">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}{{ ($showClass ?? false) && isset($classes) ? ' - ' . ($class?->name ?? 'Semua kelas') : '' }}
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
            @if(($showClass ?? false) && isset($classes))
                <label class="grid gap-1.5">
                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Kelas</span>
                    <select id="daily-attendance-class-filter"
                            name="class_id"
                            class="w-full rounded-2xl border-0 bg-white px-4 py-3 text-sm font-bold text-gray-700 ring-1 ring-gray-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua kelas</option>
                        @foreach($classes as $optionClass)
                            <option value="{{ $optionClass->id }}" @selected((string) ($selectedClassId ?? '') === (string) $optionClass->id)>{{ $optionClass->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <a href="{{ url()->current() }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-black text-gray-500 ring-1 ring-gray-100 transition hover:bg-gray-100 active:scale-[0.98] sm:self-end">Reset</a>
            <button class="rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-200/50 active:scale-[0.98] sm:self-end">Tampilkan</button>
            </div>
            </div>
        </div>
    </form>

    @if($exceptionCount > 0)
        <section class="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-3 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Terlambat</p>
                <p class="mt-1 text-xl font-black text-amber-700">{{ $lateCount }}</p>
            </div>
            <div class="rounded-2xl border border-purple-100 bg-purple-50 p-3 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-purple-600">Verifikasi</p>
                <p class="mt-1 text-xl font-black text-purple-700">{{ $verificationCount }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-3 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Belum masuk</p>
                <p class="mt-1 text-xl font-black text-gray-700">{{ $notYetEnteredCount }}</p>
            </div>
            <div class="rounded-2xl border border-rose-100 bg-rose-50 p-3 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-600">Alpha</p>
                <p class="mt-1 text-xl font-black text-rose-700">{{ $alphaCount }}</p>
            </div>
        </section>
        <p class="text-center text-[11px] text-gray-400">Absensi normal tidak mengirim notifikasi individual kepada guru.</p>
    @endif

    <section class="md:overflow-hidden md:rounded-3xl md:border md:border-gray-100 md:bg-white md:shadow-sm"
             x-data="{
                search: '',
                isDesktop: window.innerWidth >= 768,
                shouldShow(name) {
                    return !this.search || name.toLowerCase().includes(this.search.toLowerCase());
                }
             }"
             @resize.window="isDesktop = window.innerWidth >= 768">
        <div class="mb-3 space-y-3 md:hidden">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black text-gray-900">Daftar siswa</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Tap nama siswa untuk membuka detail.</p>
                </div>
                <span class="shrink-0 rounded-xl bg-white px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-gray-400 ring-1 ring-gray-100">{{ $totalCount }} data</span>
            </div>
            <div class="rounded-3xl border border-gray-100 bg-white p-3 shadow-sm">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" x-model="search" placeholder="Cari nama siswa..."
                           class="w-full rounded-2xl border-0 bg-gray-50 py-3 pl-9 pr-3 text-sm font-semibold text-gray-800 ring-1 ring-gray-100 placeholder:text-gray-400 focus:bg-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="hidden items-center justify-between gap-3 px-5 pt-5 pb-3 md:flex">
            <div>
                <h2 class="text-base font-black text-gray-900">Daftar siswa</h2>
                <p class="mt-0.5 text-xs text-gray-500">Status masuk, pulang, WA, dan bukti foto.</p>
            </div>
            <span class="shrink-0 rounded-xl bg-white px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-gray-400 ring-1 ring-gray-100">{{ $totalCount }} data</span>
        </div>
        <div class="hidden gap-4 border-t border-gray-100 bg-gray-50 px-5 py-3 text-[10px] font-black uppercase tracking-wider text-gray-400 md:grid {{ $showClassColumn ? 'md:grid-cols-[minmax(130px,1fr)_110px_120px_120px_100px_minmax(260px,1.25fr)]' : 'md:grid-cols-[minmax(180px,1fr)_120px_120px_100px_minmax(280px,1.35fr)]' }}">
            <div>Siswa</div>
            @if($showClassColumn)
                <div>Kelas</div>
            @endif
            <div class="text-center">Masuk</div>
            <div class="text-center">Pulang</div>
            <div class="text-center">WA</div>
            <div>Foto/Aksi</div>
        </div>
        <div class="space-y-3 pb-2 md:space-y-0 md:divide-y md:divide-gray-100 md:pb-0">
            @forelse($records as $record)
                @php
                    $checkInPresentation = $record->checkInPresentation();
                    $checkOutPresentation = $record->checkOutPresentation();
                    $studentInitial = strtoupper(substr($record->student->name ?? '-', 0, 1));
                    $pendingEarlyLeaveRequests = $record->earlyLeaveRequests->filter(fn ($request) => $request->date?->format('Y-m-d') === $date && $request->status === 'pending');
                    $pendingCorrections = $record->corrections->where('status', 'pending');
                    $reviewedCorrections = $record->corrections->whereIn('status', ['approved', 'rejected']);
                    $deviceAuditCount = (int) $record->check_in_suspicious + (int) $record->check_out_suspicious;
                    $studentActionCount = $pendingEarlyLeaveRequests->count() + $pendingCorrections->count() + ($record->check_in_status === 'alpha' ? 1 : 0) + $deviceAuditCount;
                    $requestGroupLabels = \App\Models\StudentEarlyLeaveRequest::categoryGroups();
                    $approvedNotHadirRequest = $record->earlyLeaveRequests
                        ->filter(fn ($request) => $request->status === 'approved' && $request->isTidakHadirCategory() && $request->coversDate($date))
                        ->first();
                    $approvedDispenRequest = $record->earlyLeaveRequests
                        ->filter(fn ($request) => $request->status === 'approved' && $request->isDispenCategory() && $request->coversDate($date))
                        ->first();
                    $approvedAbsenceLabel = $approvedNotHadirRequest
                        ? ($approvedNotHadirRequest->category === 'sakit' ? 'Sakit' : 'Izin')
                        : null;
                    $approvedAbsenceTone = $approvedNotHadirRequest?->category === 'sakit'
                        ? 'bg-orange-50 text-orange-700 ring-1 ring-orange-100'
                        : 'bg-sky-50 text-sky-700 ring-1 ring-sky-100';
                    $approvedAbsencePanel = $approvedNotHadirRequest?->category === 'sakit'
                        ? [
                            'box' => 'border-orange-100 bg-orange-50',
                            'eyebrow' => 'text-orange-600',
                            'title' => 'text-orange-950',
                            'meta' => 'text-orange-500',
                            'body' => 'text-orange-800',
                            'link' => 'text-orange-700 ring-orange-100',
                        ]
                        : [
                            'box' => 'border-sky-100 bg-sky-50',
                            'eyebrow' => 'text-sky-600',
                            'title' => 'text-sky-950',
                            'meta' => 'text-sky-500',
                            'body' => 'text-sky-800',
                            'link' => 'text-sky-700 ring-sky-100',
                        ];
                    $noCheckInOverdue = now()->greaterThan(\Carbon\Carbon::parse($date . ' ' . $verificationDeadlineTime));
                    $dispenNoCheckInOverdue = $approvedDispenRequest
                        && ! $record->check_in_at
                        && now()->greaterThan(\Carbon\Carbon::parse($date . ' ' . $operationalEndTime));
                    $noCheckInLabel = $approvedNotHadirRequest
                        ? $approvedAbsenceLabel
                        : ($approvedDispenRequest
                            ? ($dispenNoCheckInOverdue ? 'Perlu Verifikasi' : 'Hadir')
                            : ($noCheckInOverdue ? 'Alpha' : 'Belum masuk'));
                    $noCheckInTone = $approvedNotHadirRequest
                        ? $approvedAbsenceTone
                        : ($dispenNoCheckInOverdue
                            ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-100'
                            : ($noCheckInOverdue
                                ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-100'
                                : ($approvedDispenRequest
                                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                                    : 'bg-gray-50 text-gray-500 ring-1 ring-gray-100')));
                @endphp
                <div class="hidden gap-4 px-5 py-4 transition-colors hover:bg-gray-50/70 md:grid md:items-start {{ $showClassColumn ? 'md:grid-cols-[minmax(130px,1fr)_110px_120px_120px_100px_minmax(260px,1.25fr)]' : 'md:grid-cols-[minmax(180px,1fr)_120px_120px_100px_minmax(280px,1.35fr)]' }}">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-gray-900">{{ $record->student->name ?? '-' }}</p>
                        <p class="mt-0.5 text-xs font-semibold text-gray-400">{{ $record->student->nis ?? '' }}</p>
                    </div>

                    @if($showClassColumn)
                        <div class="text-sm font-semibold text-gray-600">{{ $record->class->name ?? '-' }}</div>
                    @endif

                    <div class="text-center">
                        @if($record->check_in_at)
                            <div class="inline-flex flex-col items-center gap-1">
                                <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-black ring-1 {{ $checkInPresentation[0]['tone'] }}">
                                    {{ $record->check_in_at->format('H:i') }}
                                </span>
                                <div class="flex flex-wrap justify-center gap-1">
                                    @foreach($checkInPresentation as $badge)
                                        <span class="inline-flex rounded-lg px-2 py-0.5 text-[10px] font-black ring-1 {{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <span class="inline-flex rounded-lg {{ $noCheckInTone }} px-2.5 py-1 text-xs font-bold">{{ $noCheckInLabel }}</span>
                            @if($dispenNoCheckInOverdue && ($canVerify ?? false))
                                <form method="POST" action="{{ route('wali-kelas.daily-attendance.dispen.confirm', $approvedDispenRequest) }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <button type="submit" class="inline-flex rounded-lg bg-amber-500 px-2.5 py-1 text-[10px] font-black text-white transition hover:bg-amber-600" onclick="return confirm('Konfirmasi kehadiran siswa tanpa bukti absen?')">Konfirmasi hadir</button>
                                </form>
                            @endif
                        @endif
                    </div>

                    <div class="space-y-2 text-center">
                        @if($record->check_out_at)
                            <div class="inline-flex flex-col items-center gap-1">
                                <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-black ring-1 {{ $checkOutPresentation[0]['tone'] }}">
                                    {{ $record->check_out_at->format('H:i') }}
                                </span>
                                <div class="flex flex-wrap justify-center gap-1">
                                    @foreach($checkOutPresentation as $badge)
                                        <span class="inline-flex rounded-lg px-2 py-0.5 text-[10px] font-black ring-1 {{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            @if($approvedNotHadirRequest)
                                <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $approvedAbsenceTone }}">{{ $approvedAbsenceLabel }}</span>
                            @else
                                <span class="text-xs font-bold text-gray-300">Belum</span>
                            @endif
                        @endif
                    </div>

                    <div class="text-center">
                        @php
                            $checkInWa = $record->whatsappLogs->firstWhere('event_type', 'check_in');
                            $checkOutWa = $record->whatsappLogs->firstWhere('event_type', 'check_out');
                            $absentWa = optional($absentWaLogsByStudent ?? collect())->get($record->student_id)?->first();
                            $waClasses = [
                                'sent' => 'bg-emerald-50 text-emerald-700',
                                'failed' => 'bg-rose-50 text-rose-700',
                                'pending' => 'bg-amber-50 text-amber-700',
                                'queued' => 'bg-blue-50 text-blue-700',
                            ];
                        @endphp
                        <div class="inline-flex flex-wrap justify-center gap-1">
                            @if($checkInWa)
                                <span title="{{ $checkInWa->error_message ?: 'WA masuk' }}" class="rounded-lg px-2 py-1 text-[10px] font-black {{ $waClasses[$checkInWa->status] ?? 'bg-gray-100 text-gray-600' }}">M {{ strtoupper($checkInWa->status) }}</span>
                                @if(($canVerify ?? false) && $checkInWa->status === 'failed')
                                    <form method="POST" action="{{ route('wali-kelas.daily-attendance.whatsapp.retry', $checkInWa) }}">
                                        @csrf
                                        <button class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-black text-blue-700">Ulangi M</button>
                                    </form>
                                @endif
                            @endif
                            @if($checkOutWa)
                                <span title="{{ $checkOutWa->error_message ?: 'WA pulang' }}" class="rounded-lg px-2 py-1 text-[10px] font-black {{ $waClasses[$checkOutWa->status] ?? 'bg-gray-100 text-gray-600' }}">P {{ strtoupper($checkOutWa->status) }}</span>
                                @if(($canVerify ?? false) && $checkOutWa->status === 'failed')
                                    <form method="POST" action="{{ route('wali-kelas.daily-attendance.whatsapp.retry', $checkOutWa) }}">
                                        @csrf
                                        <button class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-black text-blue-700">Ulangi P</button>
                                    </form>
                                @endif
                            @endif
                            @if($absentWa)
                                <span title="{{ $absentWa->error_message ?: 'WA alpha (tidak hadir)' }}" class="rounded-lg px-2 py-1 text-[10px] font-black {{ $waClasses[$absentWa->status] ?? 'bg-gray-100 text-gray-600' }}">A {{ strtoupper($absentWa->status) }}</span>
                                @if(($canVerify ?? false) && $absentWa->status === 'failed')
                                    <form method="POST" action="{{ route('wali-kelas.daily-attendance.whatsapp.retry', $absentWa) }}">
                                        @csrf
                                        <button class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-black text-blue-700">Ulangi A</button>
                                    </form>
                                @endif
                            @endif
                            @unless($checkInWa || $checkOutWa || $absentWa)
                                <span class="text-xs font-bold text-gray-300">-</span>
                            @endunless
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex flex-wrap gap-2">
                        @if($record->check_in_photo)
                            <a href="{{ route('attendance.media', [$record, 'check-in']) }}" target="_blank" rel="noopener" class="rounded-xl bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">Foto masuk</a>
                        @endif
                        @if($record->check_out_photo)
                            <a href="{{ route('attendance.media', [$record, 'check-out']) }}" target="_blank" rel="noopener" class="rounded-xl bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700">Foto pulang</a>
                        @endif
                        @unless($record->check_in_photo || $record->check_out_photo)
                            <span class="text-xs font-bold text-gray-300">-</span>
                        @endunless
                        </div>
                        @if($approvedNotHadirRequest)
                            <div class="rounded-2xl border {{ $approvedAbsencePanel['box'] }} px-3 py-2">
                                <p class="text-[10px] font-black uppercase tracking-wider {{ $approvedAbsencePanel['eyebrow'] }}">Ketidakhadiran disetujui</p>
                                <p class="mt-1 text-[11px] font-black {{ $approvedAbsencePanel['title'] }}">{{ $approvedNotHadirRequest->categoryLabel() }}</p>
                                <p class="mt-0.5 text-[10px] font-bold {{ $approvedAbsencePanel['meta'] }}">
                                    @if($approvedNotHadirRequest->isMultiDay())
                                        {{ $approvedNotHadirRequest->date->format('d M') }} - {{ $approvedNotHadirRequest->date_end->format('d M Y') }}
                                    @else
                                        {{ $approvedNotHadirRequest->date->format('d M Y') }}
                                    @endif
                                </p>
                                <p class="mt-1 break-words text-[11px] leading-4 {{ $approvedAbsencePanel['body'] }}">{{ $approvedNotHadirRequest->reason }}</p>
                                @if($approvedNotHadirRequest->evidence_path)
                                    <a href="{{ route('attendance.early-leave.evidence', $approvedNotHadirRequest) }}" target="_blank" rel="noopener" class="mt-2 inline-flex rounded-lg bg-white px-2.5 py-1.5 text-[10px] font-black ring-1 {{ $approvedAbsencePanel['link'] }}">Buka bukti</a>
                                @endif
                            </div>
                        @endif
                        @if($record->check_in_suspicious || $record->check_out_suspicious)
                            <div class="rounded-2xl border border-rose-100 bg-rose-50 px-3 py-2">
                                <p class="text-[10px] font-black uppercase tracking-wider text-rose-600">Cek titip absen</p>
                                @if($record->check_in_suspicious)
                                    <p class="mt-1 text-[11px] font-semibold leading-4 text-rose-700">Masuk: {{ $record->check_in_suspicious_reason }}</p>
                                @endif
                                @if($record->check_out_suspicious)
                                    <p class="mt-1 text-[11px] font-semibold leading-4 text-rose-700">Pulang: {{ $record->check_out_suspicious_reason }}</p>
                                @endif
                            </div>
                        @endif
                        @if($canVerify ?? false)
                            @foreach($pendingEarlyLeaveRequests as $earlyRequest)
                                <details class="group rounded-2xl border border-blue-100 bg-blue-50/80">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-3 py-2.5 text-xs font-black text-blue-800">
                                        <span class="flex min-w-0 items-center gap-2">
                                            <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                                            <span class="truncate">{{ $requestGroupLabels[$earlyRequest->categoryGroup()] ?? 'Pengajuan' }}</span>
                                        </span>
                                        <svg class="h-3.5 w-3.5 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                    </summary>
                                    <div class="grid gap-3 border-t border-blue-100 p-3">
                                        <div>
                                            <p class="text-[11px] font-black text-blue-950">{{ $earlyRequest->categoryLabel() }}</p>
                                            <p class="mt-1 text-[10px] font-bold text-blue-500">
                                                @if($earlyRequest->isMultiDay())
                                                    {{ $earlyRequest->date->format('d M') }} - {{ $earlyRequest->date_end->format('d M Y') }}
                                                @else
                                                    {{ $earlyRequest->date->format('d M Y') }}
                                                @endif
                                            </p>
                                            @if($earlyRequest->activity_name)
                                                <p class="mt-1 text-[11px] font-bold text-blue-900">{{ $earlyRequest->activity_name }}</p>
                                            @endif
                                            <p class="mt-1 break-words text-[11px] leading-4 text-blue-800">{{ $earlyRequest->reason }}</p>
                                            @if($earlyRequest->evidence_path)
                                                <a href="{{ route('attendance.early-leave.evidence', $earlyRequest) }}" target="_blank" rel="noopener" class="mt-2 inline-flex rounded-lg bg-white px-2.5 py-1.5 text-[10px] font-black text-blue-700 ring-1 ring-blue-100">Buka bukti</a>
                                            @endif
                                        </div>
                                        <div class="grid gap-2">
                                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.early-leave.review', $earlyRequest) }}" class="w-full">
                                                @csrf
                                                <input type="hidden" name="decision" value="approve">
                                                <button class="w-full rounded-xl bg-emerald-600 px-3 py-2.5 text-[11px] font-black text-white shadow-sm shadow-emerald-100 transition active:scale-[0.98]">Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.early-leave.review', $earlyRequest) }}" class="grid min-w-0 gap-2 lg:grid-cols-[minmax(0,1fr)_auto]">
                                                @csrf
                                                <input type="hidden" name="decision" value="reject">
                                                <input required name="reviewer_note" placeholder="Alasan penolakan" class="min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-[11px] focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                                <button class="rounded-xl bg-rose-600 px-4 py-2.5 text-[11px] font-black text-white shadow-sm shadow-rose-100 transition active:scale-[0.98]">Tolak</button>
                                            </form>
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        @endif
                        @foreach($pendingCorrections as $correction)
                            <div class="w-full rounded-2xl border border-amber-100 bg-amber-50/90 p-3">
                                <p class="text-[11px] font-black text-amber-900">Koreksi: {{ str_replace('_', ' ', $correction->requested_status) }}</p>
                                <p class="mt-1 break-words text-[11px] leading-4 text-amber-800">{{ $correction->reason }}</p>
                                @if($correction->evidence_path)
                                    <a href="{{ route('attendance.corrections.evidence', $correction) }}" target="_blank" rel="noopener" class="mt-2 inline-flex rounded-lg bg-white px-2.5 py-1.5 text-[10px] font-black text-amber-700 ring-1 ring-amber-100">Buka bukti</a>
                                @endif
                                @if(($canVerify ?? false))
                                    <div class="mt-3 grid gap-2">
                                        <form method="POST" action="{{ route('wali-kelas.daily-attendance.corrections.review', $correction) }}" class="w-full">
                                            @csrf
                                            <input type="hidden" name="decision" value="approve">
                                            <button class="w-full rounded-xl bg-emerald-600 px-3 py-2 text-[11px] font-black text-white shadow-sm shadow-emerald-100 transition active:scale-[0.98]">Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('wali-kelas.daily-attendance.corrections.review', $correction) }}" class="grid min-w-0 gap-2 lg:grid-cols-[minmax(0,1fr)_auto]">
                                            @csrf
                                            <input type="hidden" name="decision" value="reject">
                                            <input required name="reviewer_note" placeholder="Alasan penolakan" class="min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2 text-[11px] focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                            <button class="rounded-xl bg-rose-600 px-4 py-2 text-[11px] font-black text-white shadow-sm shadow-rose-100 transition active:scale-[0.98]">Tolak</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        @foreach($reviewedCorrections as $correction)
                            <p class="w-full text-[10px] font-semibold {{ $correction->status === 'approved' ? 'text-emerald-700' : 'text-rose-700' }}">
                                Koreksi {{ strtoupper($correction->status) }}: {{ str_replace('_', ' ', $correction->requested_status) }}
                                @if($correction->reviewer) oleh {{ $correction->reviewer->name }} @endif
                            </p>
                        @endforeach
                        @if(($canVerify ?? false) && $record->check_in_status === 'alpha')
                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.verify', $record) }}" class="flex flex-wrap gap-1">
                                @csrf
                                <input type="hidden" name="decision" value="approve">
                                <input type="text" name="verification_note" required placeholder="Alasan override" class="w-28 rounded-lg border border-gray-200 px-2 py-1.5 text-xs">
                                <button class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white">Ubah ke Terlambat</button>
                            </form>
                        @elseif(($canVerify ?? false) && $record->check_out_status === 'alpha')
                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.verify', $record) }}" class="flex flex-wrap gap-1">
                                @csrf
                                <input type="hidden" name="decision" value="approve">
                                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white">Setujui pulang</button>
                            </form>
                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.verify', $record) }}" class="flex flex-wrap gap-1">
                                @csrf
                                <input type="hidden" name="decision" value="reject">
                                <input type="text" name="verification_note" required placeholder="Alasan tolak" class="w-28 rounded-lg border border-gray-200 px-2 py-1.5 text-xs">
                                <button class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-bold text-white">Tolak pulang</button>
                            </form>
                        @elseif(($canVerify ?? false) && !$record->check_in_at && !$approvedNotHadirRequest)
                            <details class="group rounded-2xl border border-blue-100 bg-blue-50">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-3 py-2 text-xs font-black text-blue-700">
                                    Bantu absen
                                    <svg class="h-3.5 w-3.5 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </summary>
                                <form method="POST" action="{{ route('wali-kelas.daily-attendance.assist') }}" onsubmit="return submitTeacherAttendance(event)" class="grid gap-1 border-t border-blue-100 p-2">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $record->student_id }}">
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <input type="hidden" name="latitude">
                                    <input type="hidden" name="longitude">
                                    <input type="hidden" name="accuracy">
                                    <input type="text" name="verification_note" required placeholder="Alasan bantu absen" class="w-full rounded-lg border border-blue-100 px-2 py-1.5 text-xs">
                                    <button class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white">Simpan</button>
                                </form>
                            </details>
                        @endif
                        @if(($canVerify ?? false) && !$record->check_out_at && !$approvedNotHadirRequest)
                            <details class="group rounded-2xl border border-gray-200 bg-gray-50">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-3 py-2 text-xs font-black text-gray-700">
                                    Bantu pulang
                                    <svg class="h-3.5 w-3.5 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </summary>
                                <form method="POST" action="{{ route('wali-kelas.daily-attendance.assist-checkout') }}" onsubmit="return submitTeacherAttendance(event)" class="grid gap-1 border-t border-gray-200 p-2">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $record->student_id }}">
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <input type="hidden" name="latitude">
                                    <input type="hidden" name="longitude">
                                    <input type="hidden" name="accuracy">
                                    <input type="text" name="verification_note" required placeholder="Alasan bantu pulang" class="w-full rounded-lg border border-gray-200 px-2 py-1.5 text-xs">
                                    <button class="rounded-lg bg-gray-800 px-3 py-1.5 text-xs font-bold text-white">Simpan</button>
                                </form>
                            </details>
                        @endif
                    </div>
                </div>

                <article x-data="{ expanded: false }"
                         x-show='shouldShow(@json($record->student->name ?? ""))'
                         x-cloak
                         class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm md:hidden">
                    <button type="button" @click="expanded = !expanded" class="flex w-full items-center gap-3 p-4 text-left active:bg-gray-50 md:hidden">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-sm font-black text-blue-700 ring-1 ring-blue-100">
                            {{ $studentInitial }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-black text-gray-900">{{ $record->student->name ?? '-' }}</p>
                            <div class="mt-1 flex min-w-0 flex-wrap items-center gap-1.5">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $record->student->nis ?? '' }}</p>
                                @if($pendingEarlyLeaveRequests->isNotEmpty())
                                    <span class="rounded-lg bg-blue-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-blue-700 ring-1 ring-blue-100">{{ $pendingEarlyLeaveRequests->count() }} izin</span>
                                @endif
                                @if($pendingCorrections->isNotEmpty())
                                    <span class="rounded-lg bg-amber-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-amber-700 ring-1 ring-amber-100">{{ $pendingCorrections->count() }} koreksi</span>
                                @endif
                                @if($record->check_in_status === 'alpha' || $record->check_out_status === 'alpha')
                                    <span class="rounded-lg bg-amber-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-amber-700 ring-1 ring-amber-100">override</span>
                                @endif
                                @if($record->check_in_suspicious || $record->check_out_suspicious)
                                    <span class="rounded-lg bg-rose-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-rose-700 ring-1 ring-rose-100">cek titip</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if($studentActionCount > 0)
                                <span class="flex h-7 min-w-7 items-center justify-center rounded-xl bg-gray-900 px-2 text-[10px] font-black text-white">{{ $studentActionCount }}</span>
                            @endif
                            <svg class="h-4 w-4 text-gray-300 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <div x-show="expanded || isDesktop" x-collapse x-cloak class="border-t border-gray-50 p-4 md:contents md:border-0 md:p-0">
                    <div class="hidden md:block {{ $showClassColumn ? 'md:col-span-2' : 'md:col-span-3' }}">
                        <div class="flex items-center gap-3 md:block">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-sm font-black text-blue-700 ring-1 ring-blue-100 md:hidden">
                                {{ $studentInitial }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black text-gray-900">{{ $record->student->name ?? '-' }}</p>
                                <p class="text-xs font-semibold text-gray-400">{{ $record->student->nis ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    @if($showClassColumn)
                        <div class="mt-3 rounded-2xl bg-gray-50 px-3 py-2 text-xs font-bold text-gray-600 md:col-span-2 md:mt-0 md:bg-transparent md:px-0 md:py-0 md:text-sm md:font-semibold">{{ $record->class->name ?? '-' }}</div>
                    @else
                        <div class="mt-3 rounded-2xl bg-gray-50 px-3 py-2 text-xs font-bold text-gray-600 md:hidden">{{ $record->class->name ?? '-' }}</div>
                    @endif
                    <div class="mt-3 grid grid-cols-2 gap-2 md:contents">
                        <div class="rounded-2xl bg-gray-50 px-3 py-2 {{ $showClassColumn ? 'md:col-span-2' : 'md:col-span-3' }} md:mt-0 md:bg-transparent md:px-0 md:py-0 md:text-center">
                            <p class="mb-1 text-[10px] font-black uppercase tracking-wider text-gray-400 md:hidden">Masuk</p>
                            @if($record->check_in_at)
                                <div class="flex flex-col items-start gap-1">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-black ring-1 {{ $checkInPresentation[0]['tone'] }}">
                                        {{ $record->check_in_at->format('H:i') }}
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($checkInPresentation as $badge)
                                            <span class="inline-flex rounded-lg px-2 py-0.5 text-[9px] font-black ring-1 {{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="inline-flex rounded-lg {{ $noCheckInTone }} px-2.5 py-1 text-xs font-bold">{{ $noCheckInLabel }}</span>
                            @endif
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-3 py-2 md:col-span-2 md:mt-0 md:bg-transparent md:px-0 md:py-0 md:text-center">
                            <p class="mb-1 text-[10px] font-black uppercase tracking-wider text-gray-400 md:hidden">Pulang</p>
                            @if($record->check_out_at)
                                <div class="flex flex-col items-start gap-1">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-black ring-1 {{ $checkOutPresentation[0]['tone'] }}">
                                        {{ $record->check_out_at->format('H:i') }}
                                    </span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($checkOutPresentation as $badge)
                                            <span class="inline-flex rounded-lg px-2 py-0.5 text-[9px] font-black ring-1 {{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                @if($approvedNotHadirRequest)
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $approvedAbsenceTone }}">{{ $approvedAbsenceLabel }}</span>
                                @else
                                    <span class="text-xs font-bold text-gray-300">Belum</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    @if($canVerify ?? false)
                        <div class="mt-3 space-y-3 md:hidden">
                            @foreach($pendingEarlyLeaveRequests as $earlyRequest)
                                <section class="rounded-2xl border border-blue-100 bg-blue-50 p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-black uppercase tracking-wider text-blue-500">{{ $requestGroupLabels[$earlyRequest->categoryGroup()] ?? 'Pengajuan' }}</p>
                                            <p class="mt-0.5 text-sm font-black text-blue-950">{{ $earlyRequest->categoryLabel() }}</p>
                                            <p class="mt-0.5 text-[10px] font-bold text-blue-500">
                                                @if($earlyRequest->isMultiDay())
                                                    {{ $earlyRequest->date->format('d M') }} - {{ $earlyRequest->date_end->format('d M Y') }}
                                                @else
                                                    {{ $earlyRequest->date->format('d M Y') }}
                                                @endif
                                            </p>
                                            @if($earlyRequest->activity_name)
                                                <p class="mt-0.5 text-[11px] font-bold text-blue-900">{{ $earlyRequest->activity_name }}</p>
                                            @endif
                                        </div>
                                        <span class="shrink-0 rounded-lg bg-white px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-blue-700 ring-1 ring-blue-100">Menunggu</span>
                                    </div>
                                    <p class="mt-2 break-words text-xs leading-5 text-blue-800">{{ $earlyRequest->reason }}</p>
                                    @if($earlyRequest->evidence_path)
                                        <a href="{{ route('attendance.early-leave.evidence', $earlyRequest) }}" target="_blank" rel="noopener" class="mt-3 inline-flex rounded-xl bg-white px-3 py-2 text-[11px] font-black text-blue-700 ring-1 ring-blue-100">Buka bukti</a>
                                    @endif
                                    <div class="mt-3 grid gap-2">
                                        <form method="POST" action="{{ route('wali-kelas.daily-attendance.early-leave.review', $earlyRequest) }}" class="w-full">
                                            @csrf
                                            <input type="hidden" name="decision" value="approve">
                                            <button class="w-full rounded-xl bg-emerald-600 px-3 py-3 text-xs font-black text-white shadow-sm shadow-emerald-100 active:scale-[0.98]">Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('wali-kelas.daily-attendance.early-leave.review', $earlyRequest) }}" class="grid min-w-0 gap-2">
                                            @csrf
                                            <input type="hidden" name="decision" value="reject">
                                            <input required name="reviewer_note" placeholder="Alasan penolakan" class="w-full min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-3 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                            <button class="w-full rounded-xl bg-rose-600 px-3 py-3 text-xs font-black text-white shadow-sm shadow-rose-100 active:scale-[0.98]">Tolak</button>
                                        </form>
                                    </div>
                                </section>
                            @endforeach
                            @if(!$record->check_out_at && !$approvedNotHadirRequest)
                                <details class="group rounded-2xl border border-gray-200 bg-gray-50">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-3 py-3 text-xs font-black text-gray-700">
                                        Bantu pulang
                                        <svg class="h-3.5 w-3.5 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                    </summary>
                                    <form method="POST" action="{{ route('wali-kelas.daily-attendance.assist-checkout') }}" onsubmit="return submitTeacherAttendance(event)" class="grid gap-2 border-t border-gray-200 p-3">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $record->student_id }}">
                                        <input type="hidden" name="date" value="{{ $date }}">
                                        <input type="hidden" name="latitude">
                                        <input type="hidden" name="longitude">
                                        <input type="hidden" name="accuracy">
                                        <input type="text" name="verification_note" required placeholder="Alasan bantu pulang" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-3 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <button class="w-full rounded-xl bg-gray-900 px-3 py-3 text-xs font-black text-white active:scale-[0.98]">Simpan pulang</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    @endif
                    @if($approvedNotHadirRequest)
                        <section class="mt-3 rounded-2xl border {{ $approvedAbsencePanel['box'] }} p-3 md:hidden">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-black uppercase tracking-wider {{ $approvedAbsencePanel['meta'] }}">Ketidakhadiran disetujui</p>
                                    <p class="mt-0.5 text-sm font-black {{ $approvedAbsencePanel['title'] }}">{{ $approvedNotHadirRequest->categoryLabel() }}</p>
                                    <p class="mt-0.5 text-[10px] font-bold {{ $approvedAbsencePanel['meta'] }}">
                                        @if($approvedNotHadirRequest->isMultiDay())
                                            {{ $approvedNotHadirRequest->date->format('d M') }} - {{ $approvedNotHadirRequest->date_end->format('d M Y') }}
                                        @else
                                            {{ $approvedNotHadirRequest->date->format('d M Y') }}
                                        @endif
                                    </p>
                                </div>
                                <span class="shrink-0 rounded-lg bg-white px-2.5 py-1 text-[9px] font-black uppercase tracking-wider ring-1 {{ $approvedAbsencePanel['link'] }}">Approved</span>
                            </div>
                            <p class="mt-2 break-words text-xs leading-5 {{ $approvedAbsencePanel['body'] }}">{{ $approvedNotHadirRequest->reason }}</p>
                            @if($approvedNotHadirRequest->evidence_path)
                                <a href="{{ route('attendance.early-leave.evidence', $approvedNotHadirRequest) }}" target="_blank" rel="noopener" class="mt-3 inline-flex rounded-xl bg-white px-3 py-2 text-[11px] font-black ring-1 {{ $approvedAbsencePanel['link'] }}">Buka bukti</a>
                            @endif
                        </section>
                    @endif
                    <div class="mt-3 md:col-span-2 md:mt-0 md:text-center">
                        @php
                            $checkInWa = $record->whatsappLogs->firstWhere('event_type', 'check_in');
                            $checkOutWa = $record->whatsappLogs->firstWhere('event_type', 'check_out');
                            $absentWa = optional($absentWaLogsByStudent ?? collect())->get($record->student_id)?->first();
                            $waClasses = [
                                'sent' => 'bg-emerald-50 text-emerald-700',
                                'failed' => 'bg-rose-50 text-rose-700',
                                'pending' => 'bg-amber-50 text-amber-700',
                                'queued' => 'bg-blue-50 text-blue-700',
                            ];
                        @endphp
                        <p class="mb-1 text-[10px] font-black uppercase tracking-wider text-gray-400 md:hidden">WhatsApp</p>
                        <div class="inline-flex flex-wrap justify-start md:justify-center gap-1">
                            @if($checkInWa)
                                <span title="{{ $checkInWa->error_message ?: 'WA masuk' }}" class="rounded-lg px-2 py-1 text-[10px] font-black {{ $waClasses[$checkInWa->status] ?? 'bg-gray-100 text-gray-600' }}">M {{ strtoupper($checkInWa->status) }}</span>
                                @if(($canVerify ?? false) && $checkInWa->status === 'failed')
                                    <form method="POST" action="{{ route('wali-kelas.daily-attendance.whatsapp.retry', $checkInWa) }}">
                                        @csrf
                                        <button class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-black text-blue-700">Ulangi M</button>
                                    </form>
                                @endif
                            @endif
                            @if($checkOutWa)
                                <span title="{{ $checkOutWa->error_message ?: 'WA pulang' }}" class="rounded-lg px-2 py-1 text-[10px] font-black {{ $waClasses[$checkOutWa->status] ?? 'bg-gray-100 text-gray-600' }}">P {{ strtoupper($checkOutWa->status) }}</span>
                                @if(($canVerify ?? false) && $checkOutWa->status === 'failed')
                                    <form method="POST" action="{{ route('wali-kelas.daily-attendance.whatsapp.retry', $checkOutWa) }}">
                                        @csrf
                                        <button class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-black text-blue-700">Ulangi P</button>
                                    </form>
                                @endif
                            @endif
                            @if($absentWa)
                                <span title="{{ $absentWa->error_message ?: 'WA alpha (tidak hadir)' }}" class="rounded-lg px-2 py-1 text-[10px] font-black {{ $waClasses[$absentWa->status] ?? 'bg-gray-100 text-gray-600' }}">A {{ strtoupper($absentWa->status) }}</span>
                                @if(($canVerify ?? false) && $absentWa->status === 'failed')
                                    <form method="POST" action="{{ route('wali-kelas.daily-attendance.whatsapp.retry', $absentWa) }}">
                                        @csrf
                                        <button class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-black text-blue-700">Ulangi A</button>
                                    </form>
                                @endif
                            @endif
                            @unless($checkInWa || $checkOutWa || $absentWa)
                                <span class="text-xs font-bold text-gray-300">-</span>
                            @endunless
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 md:col-span-2 md:mt-0">
                        @if($record->check_in_photo)
                            <a href="{{ route('attendance.media', [$record, 'check-in']) }}" target="_blank" rel="noopener" class="rounded-xl bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">Foto masuk</a>
                        @endif
                        @if($record->check_in_suspicious || $record->check_out_suspicious)
                            <div class="w-full rounded-2xl border border-rose-100 bg-rose-50 px-3 py-2">
                                <p class="text-[10px] font-black uppercase tracking-wider text-rose-600">Cek titip absen</p>
                                @if($record->check_in_suspicious)
                                    <p class="mt-1 text-[11px] font-semibold leading-4 text-rose-700">Masuk: {{ $record->check_in_suspicious_reason }}</p>
                                @endif
                                @if($record->check_out_suspicious)
                                    <p class="mt-1 text-[11px] font-semibold leading-4 text-rose-700">Pulang: {{ $record->check_out_suspicious_reason }}</p>
                                @endif
                            </div>
                        @endif
                        @foreach($pendingCorrections as $correction)
                            <div class="w-full rounded-2xl border border-amber-100 bg-amber-50/90 p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-black uppercase tracking-wider text-amber-500">Koreksi absensi</p>
                                        <p class="mt-0.5 text-xs font-black text-amber-950">{{ str_replace('_', ' ', $correction->requested_status) }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-lg bg-white px-2 py-1 text-[9px] font-black uppercase tracking-wider text-amber-700 ring-1 ring-amber-100">Menunggu</span>
                                </div>
                                <p class="mt-2 break-words text-xs leading-5 text-amber-800">{{ $correction->reason }}</p>
                                @if($correction->evidence_path)
                                    <a href="{{ route('attendance.corrections.evidence', $correction) }}" target="_blank" rel="noopener" class="mt-2 inline-flex rounded-xl bg-white px-3 py-2 text-[11px] font-black text-amber-700 ring-1 ring-amber-100">Buka bukti</a>
                                @endif
                                @if(($canVerify ?? false))
                                    <div class="mt-3 grid gap-2">
                                        <form method="POST" action="{{ route('wali-kelas.daily-attendance.corrections.review', $correction) }}" class="w-full">
                                            @csrf
                                            <input type="hidden" name="decision" value="approve">
                                            <button class="w-full rounded-xl bg-emerald-600 px-3 py-3 text-xs font-black text-white shadow-sm shadow-emerald-100 active:scale-[0.98]">Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('wali-kelas.daily-attendance.corrections.review', $correction) }}" class="grid min-w-0 gap-2">
                                            @csrf
                                            <input type="hidden" name="decision" value="reject">
                                            <input required name="reviewer_note" placeholder="Alasan penolakan" class="w-full min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                            <button class="w-full rounded-xl bg-rose-600 px-3 py-3 text-xs font-black text-white shadow-sm shadow-rose-100 active:scale-[0.98]">Tolak</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        @foreach($reviewedCorrections as $correction)
                            <p class="w-full text-[10px] font-semibold {{ $correction->status === 'approved' ? 'text-emerald-700' : 'text-rose-700' }}">
                                Koreksi {{ strtoupper($correction->status) }}: {{ str_replace('_', ' ', $correction->requested_status) }}
                                @if($correction->reviewer) oleh {{ $correction->reviewer->name }} @endif
                            </p>
                        @endforeach
                        @if($record->check_out_photo)
                            <a href="{{ route('attendance.media', [$record, 'check-out']) }}" target="_blank" rel="noopener" class="rounded-xl bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700">Foto pulang</a>
                        @endif
                        @if(($canVerify ?? false) && $record->check_in_status === 'alpha')
                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.verify', $record) }}" class="grid w-full min-w-0 gap-2">
                                @csrf
                                <input type="hidden" name="decision" value="approve">
                                <input type="text" name="verification_note" required placeholder="Alasan override" class="w-full min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <button class="w-full rounded-xl bg-blue-600 px-3 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-100">Ubah ke Terlambat</button>
                            </form>
                        @elseif(($canVerify ?? false) && $record->check_out_status === 'alpha')
                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.verify', $record) }}" class="w-full">
                                @csrf
                                <input type="hidden" name="decision" value="approve">
                                <button class="w-full rounded-xl bg-emerald-600 px-3 py-2.5 text-xs font-bold text-white shadow-sm shadow-emerald-100">Setujui pulang</button>
                            </form>
                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.verify', $record) }}" class="grid w-full min-w-0 gap-2">
                                @csrf
                                <input type="hidden" name="decision" value="reject">
                                <input type="text" name="verification_note" required placeholder="Alasan penolakan" class="w-full min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <button class="w-full rounded-xl bg-rose-600 px-3 py-2.5 text-xs font-bold text-white shadow-sm shadow-rose-100">Tolak pulang</button>
                            </form>
                        @elseif(($canVerify ?? false) && !$record->check_in_at && !$approvedNotHadirRequest)
                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.assist') }}" onsubmit="return submitTeacherAttendance(event)" class="grid w-full min-w-0 gap-2">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $record->student_id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="latitude">
                                <input type="hidden" name="longitude">
                                <input type="hidden" name="accuracy">
                                <input type="text" name="verification_note" required placeholder="Alasan bantu absen" class="w-full min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-xs focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <button class="w-full rounded-xl bg-blue-600 px-3 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-100">Bantu Absen</button>
                            </form>
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
        <div class="rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-sm md:hidden" x-show="search && !Array.from($el.previousElementSibling.children).some(function(item) { return item.style.display !== 'none'; })" x-cloak>
            <p class="text-sm font-bold text-gray-500">Siswa tidak ditemukan</p>
            <p class="mt-1 text-xs text-gray-400">Coba kata kunci lain.</p>
        </div>
    </section>
</div>
@endsection

@if($canVerify ?? false)
    @push('scripts')
    <script>
        window.submitTeacherAttendance = function (event) {
            event.preventDefault();
            const form = event.target;
            if (!navigator.geolocation) {
                alert('Browser tidak mendukung lokasi GPS.');
                return false;
            }
            navigator.geolocation.getCurrentPosition(function (position) {
                form.latitude.value = position.coords.latitude;
                form.longitude.value = position.coords.longitude;
                form.accuracy.value = position.coords.accuracy || '';
                form.submit();
            }, function () {
                alert('Lokasi GPS wali kelas wajib diaktifkan.');
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
            return false;
        };
    </script>
    @endpush
@endif
