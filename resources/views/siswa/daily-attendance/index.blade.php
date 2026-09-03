@extends('layouts.siswa')

@section('title', 'Absensi Harian')
@section('header', 'Absensi Harian')
@section('content-padding', 'pt-4 sm:pt-6 pb-24 lg:pb-8')

@php
    $checkInStatus = $attendance?->check_in_status;
    $checkOutStatus = $attendance?->check_out_status;
    $checkInDone = (bool) $attendance?->check_in_at;
    $checkOutDone = (bool) $attendance?->check_out_at;
    $nowTime = now()->format('H:i');
    $checkInStart = substr($setting->check_in_start, 0, 5);
    $checkInDeadline = substr($setting->check_in_deadline, 0, 5);
    $checkoutTime = substr($scheduledCheckout, 0, 5);
    $todayLabel = now()->translatedFormat('l, d F Y');
    $earlyStatus = $earlyLeaveRequest?->status;
    $todayDate = now()->toDateString();
    $hasApprovedTidakHadir = (bool) ($approvedTidakHadir ?? false);
    $approvedTidakHadirRequest = $approvedTidakHadirRequest ?? null;
    $approvedTidakHadirLabel = $approvedTidakHadirRequest
        ? ($approvedTidakHadirRequest->category === 'sakit' ? 'Sakit' : 'Izin')
        : 'Izin';
    $operationalEndTime = \App\Models\Setting::get('operational_end_time', '16:00', $student->institution_id);
    $checkInOpenAt = \Carbon\Carbon::parse($todayDate . ' ' . $setting->check_in_start);
    $checkInCloseAt = \Carbon\Carbon::parse($todayDate . ' ' . $operationalEndTime);
    if ($checkInOpenAt->greaterThanOrEqualTo($checkInCloseAt)) {
        $checkInCloseAt = \Carbon\Carbon::parse($todayDate . ' 23:59:59');
    }
    $checkInVerificationAt = \Carbon\Carbon::parse($todayDate . ' ' . $setting->check_in_verification_deadline);
    $checkOutOpenAt = \Carbon\Carbon::parse($todayDate . ' ' . $scheduledCheckout)
        ->subMinutes((int) $setting->check_out_tolerance_minutes);
    $checkOutCloseAt = \Carbon\Carbon::parse($todayDate . ' ' . $operationalEndTime);
    if (\Carbon\Carbon::parse($todayDate . ' ' . $scheduledCheckout)->greaterThanOrEqualTo($checkOutCloseAt)) {
        $checkOutCloseAt = \Carbon\Carbon::parse($todayDate . ' ' . $scheduledCheckout)->addMinutes(30);
    }
    $isOperationalDay = $isOperationalDay ?? true;
    $nonOperationalMessage = 'Hari ini bukan hari operasional sekolah.';
    $canCheckInNow = $isOperationalDay && now()->betweenIncluded($checkInOpenAt, $checkInCloseAt);
    $canCheckOutNow = !$hasApprovedTidakHadir && $isOperationalDay && (now()->lessThanOrEqualTo($checkOutCloseAt) || $earlyStatus === 'approved') && (now()->greaterThanOrEqualTo($checkOutOpenAt) || $earlyStatus === 'approved');
    $checkOutOpenLabel = $checkOutOpenAt->format('H:i');
    $checkOutCloseLabel = $checkOutCloseAt->format('H:i');

    $checkInStatusLabel = !$checkInDone && $isOperationalDay
        ? ($hasApprovedTidakHadir
            ? $approvedTidakHadirLabel
            : (now()->greaterThan($checkInVerificationAt) ? 'Alpha' : 'Belum absen'))
        : null;
    $checkInStatusTone = !$checkInDone && $isOperationalDay
        ? ($hasApprovedTidakHadir
            ? 'bg-sky-50 text-sky-700 border-sky-100'
            : (now()->greaterThan($checkInVerificationAt) ? 'bg-rose-50 text-rose-700 border-rose-100' : 'bg-gray-50 text-gray-500 border-gray-100'))
        : null;
    $checkInTabSubtitle = $checkInDone
        ? 'Sudah tercatat'
        : ($hasApprovedTidakHadir
            ? 'Izin ketidakhadiran aktif'
            : ($canCheckInNow
                ? 'Kamera aktif'
                : ($isOperationalDay
                    ? (now()->lessThan($checkInOpenAt) ? 'Buka ' . $checkInStart : 'Tutup ' . $checkInCloseAt->format('H:i'))
                    : 'Hari tutup')));

    $checkInPresentation = $attendance?->checkInPresentation() ?? [];
    $checkOutPresentation = $attendance?->checkOutPresentation() ?? [];

    $earlyBadge = match ($earlyStatus) {
        'approved' => ['Disetujui', 'bg-emerald-50 text-emerald-700 border-emerald-100'],
        'rejected' => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-100'],
        'pending' => ['Menunggu', 'bg-amber-50 text-amber-700 border-amber-100'],
        'cancelled' => ['Dibatalkan', 'bg-gray-50 text-gray-500 border-gray-100'],
        default => null,
    };

    $initialAttendanceTab = !$checkInDone && $canCheckInNow && !$hasApprovedTidakHadir
        ? 'check_in'
        : (!$checkOutDone && $canCheckOutNow ? 'check_out' : 'check_in');
    $checkInAvailable = !$checkInDone && $canCheckInNow && !$hasApprovedTidakHadir;
    $checkOutAvailable = !$checkOutDone && $canCheckOutNow;
    $checkInLockedMessage = $checkInDone
        ? 'Absensi masuk sudah tercatat.'
        : ($hasApprovedTidakHadir
            ? 'Anda memiliki pengajuan ketidakhadiran yang disetujui, sehingga tidak dapat melakukan absensi masuk.'
            : ($isOperationalDay
                ? (now()->lessThan($checkInOpenAt)
                    ? 'Absensi masuk dibuka pukul ' . $checkInStart . '.'
                    : 'Batas absensi masuk sudah ditutup pukul ' . $checkInCloseAt->format('H:i') . '.')
                : $nonOperationalMessage));
    $checkOutLockedMessage = $checkOutDone
        ? 'Absensi pulang sudah tercatat.'
        : ($hasApprovedTidakHadir
            ? 'Anda memiliki pengajuan ketidakhadiran yang disetujui, sehingga tidak dapat melakukan absensi pulang.'
            : ($isOperationalDay
                ? (now()->lessThan($checkOutOpenAt) && $earlyStatus !== 'approved'
                    ? 'Absensi pulang dibuka pukul ' . $checkOutOpenLabel . '.'
                    : 'Batas absensi pulang ditutup pukul ' . $checkOutCloseLabel . '.')
                : $nonOperationalMessage));
    $groupedCategories = \App\Models\StudentEarlyLeaveRequest::groupedCategoryOptions();
    $categoryGroups = \App\Models\StudentEarlyLeaveRequest::categoryGroups();
    $categoryDefinitions = \App\Models\StudentEarlyLeaveRequest::categoryDefinitions();
    $evidenceRequiredCategories = \App\Models\StudentEarlyLeaveRequest::evidenceRequiredCategories();
@endphp

@section('content')
<div class="space-y-4 sm:space-y-5 pb-24 sm:pb-6">
    <section class="relative overflow-hidden rounded-3xl bg-white border border-gray-100 shadow-sm">
        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-blue-100/60"></div>
        <div class="absolute -left-12 bottom-0 h-32 w-32 rounded-full bg-emerald-100/50"></div>
        <div class="relative p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-wider text-blue-600">{{ $todayLabel }}</p>
                    <h1 class="mt-1 text-xl sm:text-2xl font-black tracking-tight text-gray-900">Absensi hari ini</h1>
                    <p class="mt-1 text-xs sm:text-sm leading-relaxed text-gray-500">
                        {{ $isOperationalDay ? 'Masuk ' . $checkInStart . '-' . $checkInDeadline . '. Pulang mulai ' . $checkoutTime . '.' : $nonOperationalMessage }}
                    </p>
                </div>
                <div class="shrink-0 rounded-2xl bg-blue-600 px-3 py-2 text-center text-white shadow-lg shadow-blue-200/60">
                    <p class="text-[10px] font-bold uppercase opacity-80">Sekarang</p>
                    <p class="text-base font-black">{{ $nowTime }}</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-2.5">
                <div class="rounded-2xl bg-gray-50/90 p-3 ring-1 ring-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $checkInDone ? 'bg-emerald-100 text-emerald-700' : 'bg-white text-gray-400' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Masuk</p>
                            <p class="truncate text-sm font-black text-gray-900">{{ $attendance?->check_in_at?->format('H:i') ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @if($checkInStatusLabel)
                            <span class="inline-flex rounded-lg border px-2 py-1 text-[10px] font-black {{ $checkInStatusTone }}">{{ $checkInStatusLabel }}</span>
                        @endif
                        @foreach($checkInPresentation as $badge)
                            <span class="inline-flex rounded-lg border px-2 py-1 text-[10px] font-black {{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-2xl bg-gray-50/90 p-3 ring-1 ring-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl {{ $checkOutDone ? 'bg-emerald-100 text-emerald-700' : 'bg-white text-gray-400' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"></path></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Pulang</p>
                            <p class="truncate text-sm font-black text-gray-900">{{ $attendance?->check_out_at?->format('H:i') ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach($checkOutPresentation as $badge)
                            <span class="inline-flex rounded-lg border px-2 py-1 text-[10px] font-black {{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-[11px] font-bold text-gray-600 ring-1 ring-gray-100">
                    <span class="h-1.5 w-1.5 rounded-full {{ $locationEnabled ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                    {{ $locationEnabled ? 'GPS wajib aktif' : 'GPS opsional' }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-[11px] font-bold {{ $isOperationalDay ? 'text-emerald-700' : 'text-rose-700' }} ring-1 ring-gray-100">
                    <span class="h-1.5 w-1.5 rounded-full {{ $isOperationalDay ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    {{ $isOperationalDay ? 'Hari operasional' : 'Hari non-operasional' }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-[11px] font-bold text-gray-600 ring-1 ring-gray-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                    Foto {{ $setting->require_check_in_photo || $setting->require_check_out_photo ? 'wajib' : 'opsional' }}
                </span>
            </div>
        </div>
    </section>

    <section class="space-y-3" x-data="{ selectedAttendance: @js($initialAttendanceTab) }">
        <div class="rounded-2xl border border-gray-100 bg-white p-1.5 shadow-sm">
            <div class="grid grid-cols-2 gap-1.5">
                <button type="button" @click="selectedAttendance = 'check_in'; window.dispatchEvent(new CustomEvent('attendance-tab-change'))"
                        class="rounded-xl px-2.5 py-2 text-left transition active:scale-[0.98]"
                        :class="selectedAttendance === 'check_in' ? 'bg-blue-600 text-white shadow-md shadow-blue-200/50' : 'bg-gray-50 text-gray-600'">
                    <span class="block text-[9px] font-black uppercase tracking-wider opacity-75">Jam masuk</span>
                    <span class="mt-0.5 block text-xs font-black">Masuk</span>
                    <span class="mt-0.5 block truncate text-[10px] font-bold opacity-80">
                        {{ $checkInTabSubtitle }}
                    </span>
                </button>
                <button type="button" @click="selectedAttendance = 'check_out'; window.dispatchEvent(new CustomEvent('attendance-tab-change'))"
                        class="rounded-xl px-2.5 py-2 text-left transition active:scale-[0.98]"
                        :class="selectedAttendance === 'check_out' ? 'bg-gray-900 text-white shadow-md shadow-gray-200/60' : 'bg-gray-50 text-gray-600'">
                    <span class="block text-[9px] font-black uppercase tracking-wider opacity-75">Jam pulang</span>
                    <span class="mt-0.5 block text-xs font-black">Pulang</span>
                    <span class="mt-0.5 block truncate text-[10px] font-bold opacity-80">
                        {{ $checkOutDone ? 'Sudah tercatat' : ($hasApprovedTidakHadir ? 'Izin ketidakhadiran aktif' : ($canCheckOutNow ? 'Kamera aktif' : ($isOperationalDay ? 'Buka ' . $checkOutOpenLabel : 'Hari tutup'))) }}
                    </span>
                </button>
            </div>
        </div>

        <div x-show="selectedAttendance === 'check_in'" x-cloak>
            @include('siswa.daily-attendance.partials.camera-card', [
                'title' => 'Absen Masuk',
                'subtitle' => $checkInDone ? 'Tercatat pukul ' . $attendance->check_in_at->format('H:i') : 'Ambil foto saat tiba di sekolah.',
                'route' => route('siswa.daily-attendance.check-in'),
                'photo' => $attendance?->check_in_photo ? route('attendance.media', [$attendance, 'check-in']) : null,
                'photoAlt' => 'Foto masuk',
                'badges' => $checkInPresentation,
                'accent' => 'blue',
                'primaryLabel' => 'Kirim Masuk',
                'captureLabel' => 'Ambil Foto',
                'locationEnabled' => $locationEnabled,
                'available' => $checkInAvailable,
                'lockedMessage' => $checkInLockedMessage,
            ])
        </div>

        <div x-show="selectedAttendance === 'check_out'" x-cloak>
            @include('siswa.daily-attendance.partials.camera-card', [
                'title' => 'Absen Pulang',
                'subtitle' => $checkOutDone ? 'Tercatat pukul ' . $attendance->check_out_at->format('H:i') : 'Ambil foto saat meninggalkan sekolah.',
                'route' => route('siswa.daily-attendance.check-out'),
                'photo' => $attendance?->check_out_photo ? route('attendance.media', [$attendance, 'check-out']) : null,
                'photoAlt' => 'Foto pulang',
                'badges' => $checkOutPresentation,
                'accent' => 'slate',
                'primaryLabel' => 'Kirim Pulang',
                'captureLabel' => 'Ambil Foto',
                'locationEnabled' => $locationEnabled,
                'available' => $checkOutAvailable,
                'lockedMessage' => $checkOutLockedMessage,
            ])
        </div>
    </section>

    <section class="rounded-3xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
            <div class="flex items-center gap-3 sm:block">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gray-50 text-gray-500 ring-1 ring-gray-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div class="min-w-0 sm:hidden">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-black text-gray-950">Pengajuan khusus</h2>
                        @if($earlyBadge)
                            <span class="rounded-lg border px-2 py-0.5 text-[10px] font-black {{ $earlyBadge[1] }}">{{ $earlyBadge[0] }}</span>
                        @endif
                    </div>
                    <p class="mt-0.5 text-[11px] font-semibold text-gray-500">Opsional, hanya untuk kondisi tertentu.</p>
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <div class="hidden flex-wrap items-center gap-2 sm:flex">
                    <h2 class="text-sm font-black text-gray-950">Pengajuan khusus</h2>
                    @if($earlyBadge)
                        <span class="rounded-lg border px-2 py-0.5 text-[10px] font-black {{ $earlyBadge[1] }}">{{ $earlyBadge[0] }}</span>
                    @endif
                </div>
                <p class="mt-1 text-xs leading-5 text-gray-500">
                    Bagian ini tidak wajib diisi setiap hari. Gunakan hanya saat sakit, izin tidak hadir, dispen kegiatan, atau perlu pulang cepat.
                </p>
                <a href="{{ route('siswa.daily-attendance.early-leave.history') }}" class="mt-2 inline-flex items-center gap-1 rounded-xl bg-white px-3 py-1.5 text-[10px] font-black text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-50">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Lihat riwayat pengajuan
                </a>
                @if($earlyLeaveRequest)
                    <div class="mt-3 rounded-2xl bg-gray-50 px-3 py-2 ring-1 ring-gray-100">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Pengajuan terakhir</p>
                                <p class="mt-0.5 text-xs font-bold text-gray-800">{{ $earlyLeaveRequest->categoryLabel() }}</p>
                                @if($earlyLeaveRequest->activity_name)
                                    <p class="mt-0.5 text-[11px] font-bold text-gray-600">Kegiatan: {{ $earlyLeaveRequest->activity_name }}</p>
                                @endif
                                @if($earlyLeaveRequest->activity_start_time || $earlyLeaveRequest->activity_end_time)
                                    <p class="mt-0.5 text-[11px] font-semibold text-gray-500">
                                        Jam: {{ $earlyLeaveRequest->activity_start_time ? \Illuminate\Support\Carbon::parse($earlyLeaveRequest->activity_start_time)->format('H:i') : '?' }} - {{ $earlyLeaveRequest->activity_end_time ? \Illuminate\Support\Carbon::parse($earlyLeaveRequest->activity_end_time)->format('H:i') : '?' }}
                                    </p>
                                @endif
                            </div>
                            @if($earlyLeaveRequest->status === 'pending')
                                <div class="flex shrink-0 items-center gap-1.5">
                                    <button type="button" @click="$el.closest('[data-edit-request]').querySelector('[data-edit-form]').open = !$el.closest('[data-edit-request]').querySelector('[data-edit-form]').open"
                                            class="rounded-xl border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-[10px] font-black text-blue-600 transition hover:bg-blue-100">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('siswa.daily-attendance.early-leave.cancel', $earlyLeaveRequest) }}"
                                          onsubmit="return confirm('Batalkan pengajuan ini? Wali kelas belum memprosesnya.')">
                                        @csrf
                                        <button class="rounded-xl border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-[10px] font-black text-rose-600 transition hover:bg-rose-100">
                                            Batalkan
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($earlyLeaveRequest && $earlyLeaveRequest->status === 'pending')
                    <div data-edit-request class="mt-3" x-data="{ open: false }">
                        <div x-show="open" x-cloak x-data="{ editCategory: @js($earlyLeaveRequest->category), singleDay: @js(\App\Models\StudentEarlyLeaveRequest::SINGLE_DAY_CATEGORIES) }" class="rounded-2xl border border-gray-100 bg-white p-3 ring-1 ring-gray-100">
                            <form method="POST" action="{{ route('siswa.daily-attendance.early-leave.update', $earlyLeaveRequest) }}" enctype="multipart/form-data" class="grid gap-3" x-ref="editForm">
                                @csrf
                                <div>
                                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Ubah pengajuan</span>
                                </div>
                                <label class="grid min-w-0 gap-1.5">
                                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Jenis izin / dispen</span>
                                    <select name="category" required x-model="editCategory" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        @foreach($groupedCategories as $groupKey => $options)
                                            <optgroup label="{{ $categoryGroups[$groupKey] }}">
                                                @foreach($options as $value => $label)
                                                    <option value="{{ $value }}" @selected($value === $earlyLeaveRequest->category)>{{ $label }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </label>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="grid min-w-0 gap-1.5">
                                        <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Tanggal</span>
                                        <input type="date" name="date" value="{{ $earlyLeaveRequest->date->format('Y-m-d') }}" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    </label>
                                    <label class="grid min-w-0 gap-1.5">
                                        <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Sampai tanggal</span>
                                        <input type="date" name="date_end" value="{{ $earlyLeaveRequest->date_end?->format('Y-m-d') }}" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    </label>
                                </div>
                                <label class="grid min-w-0 gap-1.5">
                                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Keterangan</span>
                                    <textarea name="reason" required maxlength="1000" class="min-h-24 w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-medium leading-5 text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{{ old('reason', $earlyLeaveRequest->reason) }}</textarea>
                                </label>
                                <label class="grid min-w-0 gap-1.5">
                                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Nama kegiatan</span>
                                    <input type="text" name="activity_name" maxlength="255" :required="editCategory === 'kegiatan_tambahan'" value="{{ old('activity_name', $earlyLeaveRequest->activity_name) }}" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    <span class="px-1 text-[10px] font-semibold leading-4 text-gray-400">Wajib diisi untuk kegiatan tambahan / ekstrakurikuler.</span>
                                </label>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2" x-show="editCategory === 'kegiatan_tambahan'">
                                    <label class="grid min-w-0 gap-1.5">
                                        <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Jam mulai (opsional)</span>
                                        <input type="time" name="activity_start_time" value="{{ old('activity_start_time', $earlyLeaveRequest->activity_start_time) }}" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    </label>
                                    <label class="grid min-w-0 gap-1.5">
                                        <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Jam selesai (opsional)</span>
                                        <input type="time" name="activity_end_time" value="{{ old('activity_end_time', $earlyLeaveRequest->activity_end_time) }}" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    </label>
                                </div>
                                <label class="grid min-w-0 gap-1.5">
                                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Bukti pendukung (opsional)</span>
                                    <input type="file" name="evidence" accept="image/*,.pdf" class="w-full rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-3 py-3 text-xs file:mr-3 file:rounded-xl file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-xs file:font-bold file:text-blue-700">
                                    <p class="px-1 text-[10px] font-semibold leading-4 text-gray-400">
                                        @if($earlyLeaveRequest->evidence_path)
                                            Bukti lama tetap dipakai jika tidak ada file baru dipilih.
                                            <a href="{{ route('attendance.early-leave.evidence', $earlyLeaveRequest) }}" target="_blank" rel="noopener" class="font-black text-blue-600 underline">Lihat bukti lama</a>.
                                        @else
                                            Upload bukti bila diperlukan.
                                        @endif
                                    </p>
                                </label>
                                <div class="grid gap-2">
                                    <button class="rounded-2xl bg-gray-900 px-4 py-3 text-sm font-black text-white shadow-lg shadow-gray-200/60 active:scale-[0.98]">Simpan Perubahan</button>
                                    <button type="button" @click="open = false" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-black text-gray-600 active:scale-[0.98]">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                @if(!$isOperationalDay)
                    <div class="mt-3 rounded-2xl border border-gray-100 bg-gray-50 px-3 py-3">
                        <p class="text-xs font-black text-gray-900">Pengajuan ditutup</p>
                        <p class="mt-1 text-[11px] font-semibold leading-5 text-gray-500">{{ $nonOperationalMessage }}</p>
                    </div>
                @elseif(!$earlyLeaveRequest || in_array($earlyLeaveRequest->status, ['rejected', 'cancelled']))
                    <details class="group mt-3 overflow-hidden rounded-2xl border border-gray-100 bg-gray-50/70">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3 transition hover:bg-gray-100/70">
                            <span class="flex min-w-0 items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white text-gray-600 ring-1 ring-gray-200">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-xs font-black text-gray-900">Buat pengajuan</span>
                                    <span class="block truncate text-[11px] font-semibold text-gray-500">Buka form jika ada kondisi khusus</span>
                                </span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>
                        <form method="POST" action="{{ route('siswa.daily-attendance.early-leave.store') }}" enctype="multipart/form-data" class="grid gap-3 border-t border-gray-100 bg-white p-3"
                              x-data="{
                                  category: '',
                                  requiredEvidence: @js($evidenceRequiredCategories),
                                  singleDayCategories: @js(\App\Models\StudentEarlyLeaveRequest::SINGLE_DAY_CATEGORIES),
                                  defs: @js($categoryDefinitions),
                                  labels: @js(\App\Models\StudentEarlyLeaveRequest::CATEGORY_OPTIONS),
                                  def() { return this.defs[this.category] || null; },
                                  isSingleDay() { return this.singleDayCategories.includes(this.category); },
                                  categoryLabel() { return this.labels[this.category] || ''; },
                                  evidenceText() { const d = this.def(); return d ? (d.evidence === 'required' ? 'Bukti pendukung wajib diunggah.' : 'Bukti pendukung opsional.') : ''; },
                                  checkInText() { const d = this.def(); return d ? (d.needs_check_in ? 'Perlu absen masuk terlebih dahulu (jika diajukan hari ini).' : 'Bisa diajukan tanpa absen masuk (jika diajukan hari ini).') : ''; },
                                  durationText() { const d = this.def(); return d ? (d.multi_day ? 'Bisa diajukan untuk beberapa hari, isi tanggal selesai.' : 'Hanya bisa diajukan untuk hari ini.') : ''; },
                                  descriptionText() { const d = this.def(); return d ? d.description : ''; },
                                  evidenceChip() { const d = this.def(); return d ? (d.evidence === 'required' ? 'Bukti wajib' : 'Tanpa bukti') : ''; },
                                  evidenceChipClass() { const d = this.def(); return d && d.evidence === 'required' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'; },
                                  durationChip() { const d = this.def(); return d ? (d.multi_day ? 'Bisa multi-hari' : 'Hari ini saja') : ''; },
                                  durationChipClass() { const d = this.def(); return d && d.multi_day ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-500'; }
                              }">
                            @csrf
                            <div class="grid gap-3">
                                <label class="grid min-w-0 gap-1.5">
                                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Jenis izin / dispen</span>
                                    <select name="category" required x-model="category" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <option value="">Pilih jenis</option>
                                        @foreach($groupedCategories as $groupKey => $options)
                                            <optgroup label="{{ $categoryGroups[$groupKey] }}">
                                                @foreach($options as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    <span class="px-1 text-[10px] font-semibold leading-4 text-gray-400">Ketidakhadiran: tidak masuk seharian. Dispensasi: punya kegiatan, tidak ikut pelajaran. Pulang cepat: masuk lalu pulang lebih awal.</span>
                                </label>

                                <div x-show="category" x-cloak class="grid gap-3 rounded-2xl border border-blue-100 bg-blue-50/70 p-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-[10px] font-black uppercase tracking-wider text-blue-600">Jenis terpilih</p>
                                            <span class="shrink-0 rounded-lg bg-white px-2 py-0.5 text-[10px] font-black text-blue-700 ring-1 ring-blue-100" x-text="categoryLabel()"></span>
                                        </div>
                                        <p class="mt-1.5 text-xs font-medium leading-5 text-blue-800" x-text="descriptionText()"></p>
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            <span class="rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider" :class="evidenceChipClass()" x-text="evidenceChip()"></span>
                                            <span class="rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider" :class="durationChipClass()" x-text="durationChip()"></span>
                                        </div>
                                    </div>
                                    <ul class="grid gap-1.5 border-t border-blue-100 pt-3">
                                        <li class="flex items-start gap-2 text-[11px] font-semibold leading-4 text-blue-900">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span x-text="durationText()"></span>
                                        </li>
                                        <li class="flex items-start gap-2 text-[11px] font-semibold leading-4 text-blue-900">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span x-text="checkInText()"></span>
                                        </li>
                                        <li class="flex items-start gap-2 text-[11px] font-semibold leading-4 text-blue-900">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span x-text="evidenceText()"></span>
                                        </li>
                                        <li x-show="def() && def().auto_checkout" class="flex items-start gap-2 text-[11px] font-semibold leading-4 text-indigo-900">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span>Jika disetujui, absensi pulang otomatis tercatat sebagai kegiatan.</span>
                                        </li>
                                        <li x-show="def() && def().remote" class="flex items-start gap-2 text-[11px] font-semibold leading-4 text-indigo-900">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span>Jika disetujui, bisa absen masuk dari luar area sekolah.</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label class="grid min-w-0 gap-1.5">
                                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Tanggal</span>
                                    <input type="date" name="date" value="{{ $todayDate }}" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </label>
                                <label class="grid min-w-0 gap-1.5" x-show="!isSingleDay() && category !== 'sakit'">
                                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Sampai tanggal</span>
                                    <input type="date" name="date_end" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </label>
                            </div>
                            <div x-show="category === 'kegiatan_tambahan'" x-cloak class="grid gap-3 rounded-2xl border border-blue-100 bg-blue-50/60 p-3">
                                <p class="text-[10px] font-black uppercase tracking-wider text-blue-600">Kegiatan di sela pelajaran</p>
                                <label class="grid min-w-0 gap-1.5">
                                    <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Nama kegiatan</span>
                                    <input type="text" name="activity_name" maxlength="255" placeholder="Contoh: Latihan futsal" :required="category === 'kegiatan_tambahan'" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="grid min-w-0 gap-1.5">
                                        <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Jam mulai (opsional)</span>
                                        <input type="time" name="activity_start_time" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    </label>
                                    <label class="grid min-w-0 gap-1.5">
                                        <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Jam selesai (opsional)</span>
                                        <input type="time" name="activity_end_time" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                    </label>
                                </div>
                                <p class="px-1 text-[10px] font-semibold leading-4 text-blue-800">Presensi tetap hadir karena kamu sudah absen masuk. Bisa kembali ke kelas setelah kegiatan selesai.</p>
                            </div>
                            <label class="grid min-w-0 gap-1.5">
                                <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Keterangan</span>
                                <textarea name="reason" required maxlength="1000" placeholder="Tulis alasan singkat dan jelas..." class="min-h-24 w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-medium leading-5 text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                            </label>
                            <label class="grid min-w-0 gap-1.5">
                                <span class="flex items-center justify-between gap-2 px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                    <span>Bukti pendukung</span>
                                    <span class="rounded-lg px-2 py-0.5"
                                          :class="requiredEvidence.includes(category) ? 'bg-blue-50 text-blue-700' : 'bg-gray-50 text-gray-400'"
                                          x-text="requiredEvidence.includes(category) ? 'Wajib' : 'Opsional'">Opsional</span>
                                </span>
                                <input type="file" name="evidence" accept="image/*,.pdf" :required="requiredEvidence.includes(category)" class="w-full rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-3 py-3 text-xs file:mr-3 file:rounded-xl file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-xs file:font-bold file:text-blue-700">
                                <p class="px-1 text-[10px] font-semibold leading-4 text-gray-400">
                                    Bukti wajib untuk lomba, kegiatan sekolah, sakit, dan izin.
                                </p>
                            </label>
                            <button class="rounded-2xl bg-gray-900 px-4 py-3 text-sm font-black text-white shadow-lg shadow-gray-200/60 active:scale-[0.98]">
                                Kirim Pengajuan
                            </button>
                        </form>
                    </details>
                @endif
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between gap-3 border-b border-gray-100 p-4 sm:p-5">
            <div>
                <h2 class="text-base font-black text-gray-900">Riwayat terbaru</h2>
                <p class="mt-0.5 text-xs text-gray-500">10 catatan terakhir dan pengajuan koreksi.</p>
            </div>
            <span class="shrink-0 rounded-xl bg-gray-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-gray-400">{{ $history->count() }} data</span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($history as $item)
                @php
                    $historyPresentation = $item->checkInPresentation();
                @endphp
                <article class="p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                        <div class="flex items-center gap-3 sm:block">
                            <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-2xl bg-gray-50 ring-1 ring-gray-100">
                                <span class="text-sm font-black text-gray-900">{{ $item->date->format('d') }}</span>
                                <span class="text-[9px] font-bold uppercase text-gray-400">{{ $item->date->translatedFormat('M') }}</span>
                            </div>
                            <div class="min-w-0 sm:hidden">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-black text-gray-900">{{ $item->date->translatedFormat('l') }}</p>
                                </div>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach($historyPresentation as $badge)
                                        <span class="rounded-lg px-2 py-1 text-[10px] font-black {{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                                    @endforeach
                                </div>
                                <p class="mt-0.5 text-[11px] font-semibold text-gray-400">{{ $item->date->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="hidden flex-wrap items-center gap-2 sm:flex">
                                <p class="text-sm font-black text-gray-900">{{ $item->date->translatedFormat('l') }}</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($historyPresentation as $badge)
                                        <span class="rounded-lg px-2 py-1 text-[10px] font-black {{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <div class="rounded-2xl bg-gray-50 px-3 py-2">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Masuk</p>
                                    <p class="text-sm font-black text-gray-900">{{ $item->check_in_at?->format('H:i') ?? '-' }}</p>
                                </div>
                                <div class="rounded-2xl bg-gray-50 px-3 py-2">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Pulang</p>
                                    <p class="text-sm font-black text-gray-900">{{ $item->check_out_at?->format('H:i') ?? '-' }}</p>
                                </div>
                            </div>

                            @if($item->corrections->isNotEmpty())
                                <div class="mt-3 space-y-2">
                                    @foreach($item->corrections as $correction)
                                        @php
                                            $correctionBadge = match ($correction->status) {
                                                'approved' => ['Disetujui', 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
                                                'rejected' => ['Ditolak', 'bg-rose-50 text-rose-700 ring-rose-100'],
                                                default => ['Menunggu', 'bg-amber-50 text-amber-700 ring-amber-100'],
                                            };
                                        @endphp
                                        <div class="rounded-2xl bg-white px-3 py-2 ring-1 ring-gray-100">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="min-w-0 truncate text-xs font-black text-gray-800">
                                                    Koreksi {{ $correction->target_event === 'check_in' ? 'masuk' : 'pulang' }}
                                                </p>
                                                <span class="shrink-0 rounded-lg px-2 py-1 text-[10px] font-black ring-1 {{ $correctionBadge[1] }}">{{ $correctionBadge[0] }}</span>
                                            </div>
                                            <p class="mt-1 text-[11px] font-semibold text-gray-400">
                                                {{ str_replace('_', ' ', $correction->requested_status) }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <details class="group mt-3 overflow-hidden rounded-2xl border border-blue-100 bg-blue-50/60">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-3">
                                    <span class="flex min-w-0 items-center gap-2">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-xs font-black text-blue-900">Ajukan koreksi</span>
                                            <span class="block truncate text-[11px] font-semibold text-blue-700/70">Untuk izin, sakit, atau kegiatan sekolah</span>
                                        </span>
                                    </span>
                                    <svg class="h-4 w-4 shrink-0 text-blue-500 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </summary>
                                <form method="POST" action="{{ route('siswa.daily-attendance.corrections.store', $item) }}" enctype="multipart/form-data" class="grid gap-3 border-t border-blue-100 bg-white p-3">
                                    @csrf
                                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                        <label class="grid min-w-0 gap-1.5">
                                            <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Bagian</span>
                                            <select name="target_event" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                                <option value="">Pilih bagian</option>
                                                <option value="check_in">Absensi masuk</option>
                                                <option value="check_out">Absensi pulang</option>
                                            </select>
                                        </label>
                                        <label class="grid min-w-0 gap-1.5">
                                            <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Alasan</span>
                                            <select name="requested_status" required class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-bold text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                                <option value="">Pilih alasan</option>
                                                <option value="izin_kegiatan">Izin kegiatan sekolah</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="izin_lainnya">Izin lainnya</option>
                                            </select>
                                        </label>
                                    </div>
                                    <label class="grid min-w-0 gap-1.5">
                                        <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Keterangan</span>
                                        <textarea name="reason" required maxlength="1000" placeholder="Tulis alasan singkat dan jelas..." class="min-h-24 w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-xs font-medium leading-5 text-gray-700 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100"></textarea>
                                    </label>
                                    <label class="grid min-w-0 gap-1.5">
                                        <span class="px-1 text-[10px] font-black uppercase tracking-wider text-gray-400">Bukti pendukung</span>
                                        <input type="file" name="evidence" accept="image/*,.pdf" class="w-full rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-3 py-3 text-xs file:mr-3 file:rounded-xl file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-xs file:font-bold file:text-blue-700">
                                    </label>
                                    <button class="rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-200/50 active:scale-[0.98]">
                                        Kirim Koreksi
                                    </button>
                                </form>
                            </details>
                        </div>
                    </div>
                </article>
            @empty
                <div class="p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 text-gray-300">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <p class="mt-3 text-sm font-bold text-gray-700">Belum ada riwayat</p>
                    <p class="mt-1 text-xs text-gray-500">Catatan absensi akan muncul setelah kamu melakukan absensi.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
window.cameraAttendance = function(locationRequired) {
    return {
        stream: null,
        cameraReady: false,
        photoData: '',
        latitude: '',
        longitude: '',
        accuracy: '',
        deviceFingerprint: '',
        submitting: false,
        error: '',
        async prepareSubmit(event) {
            if (this.submitting) return;

            event.preventDefault();
            this.error = '';

            if (!this.photoData) {
                this.error = 'Ambil foto terlebih dahulu sebelum mengirim absensi.';
                return;
            }

            event.target.querySelector('input[name="photo_data"]').value = this.photoData;
            this.deviceFingerprint = this.buildDeviceFingerprint();
            event.target.querySelector('input[name="device_fingerprint"]').value = this.deviceFingerprint;

            if (!locationRequired) {
                this.submitting = true;
                event.target.submit();
                return;
            }

            if (!navigator.geolocation) {
                this.error = 'Browser tidak mendukung lokasi GPS.';
                return;
            }

            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0
                    });
                });
                this.latitude = position.coords.latitude;
                this.longitude = position.coords.longitude;
                this.accuracy = position.coords.accuracy || '';
                event.target.querySelector('input[name="latitude"]').value = this.latitude;
                event.target.querySelector('input[name="longitude"]').value = this.longitude;
                event.target.querySelector('input[name="accuracy"]').value = this.accuracy;
                this.submitting = true;
                event.target.submit();
            } catch (error) {
                const messages = {
                    1: 'Izin lokasi browser masih ditolak. Buka pengaturan situs lalu izinkan Location.',
                    2: 'Lokasi belum bisa dideteksi. Pastikan layanan lokasi perangkat aktif.',
                    3: 'Deteksi lokasi terlalu lama. Coba lagi atau pindah ke jaringan yang lebih stabil.',
                };
                this.error = messages[error.code] || 'Lokasi GPS wajib diaktifkan untuk melakukan absensi.';
            }
        },
        async startCamera() {
            this.error = '';
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.error = 'Browser tidak mendukung kamera langsung.';
                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 720 },
                        height: { ideal: 960 },
                    },
                    audio: false
                });
                this.$refs.video.srcObject = this.stream;
                this.cameraReady = true;
            } catch (error) {
                this.error = 'Kamera tidak bisa dibuka. Izinkan akses kamera di browser.';
            }
        },
        capture() {
            const video = this.$refs.video;
            const canvas = document.createElement('canvas');
            const targetWidth = 900;
            const targetHeight = 1200;
            const videoWidth = video.videoWidth || targetWidth;
            const videoHeight = video.videoHeight || targetHeight;
            const targetRatio = targetWidth / targetHeight;
            const videoRatio = videoWidth / videoHeight;
            let sourceWidth = videoWidth;
            let sourceHeight = videoHeight;
            let sourceX = 0;
            let sourceY = 0;

            if (videoRatio > targetRatio) {
                sourceWidth = videoHeight * targetRatio;
                sourceX = (videoWidth - sourceWidth) / 2;
            } else {
                sourceHeight = videoWidth / targetRatio;
                sourceY = (videoHeight - sourceHeight) / 2;
            }

            canvas.width = targetWidth;
            canvas.height = targetHeight;
            const context = canvas.getContext('2d');
            context.translate(targetWidth, 0);
            context.scale(-1, 1);
            context.drawImage(video, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, targetWidth, targetHeight);
            this.photoData = canvas.toDataURL('image/jpeg', 0.82);
            this.stopCamera();
        },
        retake() {
            this.photoData = '';
            this.startCamera();
        },
        cancelCamera() {
            this.photoData = '';
            this.error = '';
            this.stopCamera();
        },
        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(function(track) {
                    track.stop();
                });
                this.stream = null;
            }
            this.cameraReady = false;
        },
        buildDeviceFingerprint() {
            const parts = [
                navigator.userAgent || '',
                navigator.language || '',
                screen.width || '',
                screen.height || '',
                screen.colorDepth || '',
                new Date().getTimezoneOffset(),
                navigator.hardwareConcurrency || '',
                navigator.deviceMemory || '',
                navigator.platform || '',
            ];
            let hash = 0;
            const value = parts.join('|');
            for (let i = 0; i < value.length; i++) {
                hash = ((hash << 5) - hash) + value.charCodeAt(i);
                hash |= 0;
            }
            return `web-${Math.abs(hash)}`;
        }
    };
};
</script>
@endpush
