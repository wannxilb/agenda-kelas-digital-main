@extends('layouts.walikelas')

@section('title', 'Pengajuan Izin / Dispen')
@section('header', 'Pengajuan Izin / Dispen')
@section('content-padding', 'py-4 sm:py-8 pb-28 lg:pb-8')

@php
    $statusOptions = [
        '' => 'Semua status',
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];
    $groupOptions = [
        '' => 'Semua kelompok',
        'ketidakhadiran' => 'Ketidakhadiran',
        'dispensasi' => 'Dispensasi',
        'pulang_cepat' => 'Pulang cepat',
    ];
    $statusBadges = [
        'pending' => ['Menunggu', 'bg-amber-50 text-amber-700 ring-amber-100'],
        'approved' => ['Disetujui', 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
        'rejected' => ['Ditolak', 'bg-rose-50 text-rose-700 ring-rose-100'],
        'cancelled' => ['Dibatalkan', 'bg-gray-50 text-gray-500 ring-gray-200'],
    ];
    $groupLabels = \App\Models\StudentEarlyLeaveRequest::categoryGroups();
    $hasFilter = request()->hasAny(['status', 'group', 'class_id', 'from', 'to']);
@endphp

@section('content')
<div class="space-y-4 sm:space-y-6 pb-24 lg:pb-8"
     x-data="{
         selected: [],
         decision: '',
         note: '',
         submitBatch(action) {
             this.decision = action;
             if (action === 'reject' && !this.note.trim()) { alert('Alasan penolakan wajib diisi.'); return; }
             document.getElementById('batch-form').submit();
         }
     }">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-blue-100/70"></div>
        <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">Pengajuan Izin / Dispen</h1>
                <p class="mt-2 max-w-2xl text-xs font-medium text-gray-500 sm:text-sm">
                    Semua pengajuan izin, dispensasi, dan pulang cepat siswa di kelas Anda.
                    @if($pendingCount > 0)
                        <span class="ml-1 rounded-lg bg-amber-50 px-2 py-0.5 text-[10px] font-black text-amber-700 ring-1 ring-amber-100">{{ $pendingCount }} menunggu review</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('wali-kelas.daily-attendance.index') }}"
               class="inline-flex shrink-0 items-center gap-2 self-start rounded-2xl border border-blue-100 bg-blue-50 px-4 py-2.5 text-[11px] font-black uppercase tracking-wider text-blue-700 transition hover:bg-blue-100">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                Monitor Harian
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm" x-data="{ filterOpen: {{ $hasFilter ? 'true' : 'false' }} }">
        <button type="button" @click="filterOpen = !filterOpen"
                class="flex w-full items-center justify-between px-4 py-3 sm:px-6 sm:py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl transition-colors"
                     :class="filterOpen ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500'">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                </div>
                <div class="text-left">
                    <p class="text-xs font-black text-gray-900">Filter pengajuan</p>
                    <p class="text-[11px] font-semibold text-gray-400">Status, kelompok, kelas, dan rentang tanggal</p>
                </div>
            </div>
            <svg class="h-4 w-4 text-gray-400 transition-transform" :class="filterOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
        </button>

        <div class="grid grid-rows-[0fr] transition-all" :class="filterOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
            <div class="overflow-hidden">
                <form method="GET" action="{{ route('wali-kelas.daily-attendance.early-leave.index') }}" class="grid gap-3 border-t border-gray-100 p-4 sm:grid-cols-2 lg:grid-cols-5 sm:p-6">
                    <label class="grid min-w-0 gap-1.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Status</span>
                        <select name="status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-bold text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value || ($value === '' && !request()->has('status')))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid min-w-0 gap-1.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Kelompok</span>
                        <select name="group" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-bold text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            @foreach($groupOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('group') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    @if($classes->count() > 1)
                        <label class="grid min-w-0 gap-1.5">
                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Kelas</span>
                            <select name="class_id" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-bold text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <option value="">Semua kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" @selected((string) request('class_id') === (string) $class->id)>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <label class="grid min-w-0 gap-1.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Dari tanggal</span>
                        <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-bold text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </label>
                    <label class="grid min-w-0 gap-1.5">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Sampai tanggal</span>
                        <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-bold text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    </label>
                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
                        <button class="rounded-xl bg-gray-900 px-5 py-2.5 text-xs font-black text-white shadow-sm active:scale-[0.98]">Terapkan</button>
                        <a href="{{ route('wali-kelas.daily-attendance.early-leave.index') }}" class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-2.5 text-xs font-black text-gray-600 active:scale-[0.98]">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Batch toolbar --}}
    <form id="batch-form" method="POST" action="{{ route('wali-kelas.daily-attendance.early-leave.batch') }}" class="rounded-2xl border border-gray-100 bg-white p-3 shadow-sm sm:p-4">
        @csrf
        <input type="hidden" name="request_ids" :value="selected.join(',')">
        <input type="hidden" name="decision" x-model="decision">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="flex min-w-0 flex-1 items-center gap-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-500 ring-1 ring-gray-100">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
                <p class="min-w-0 truncate text-xs font-black text-gray-800">
                    <span x-text="selected.length"></span> pengajuan dipilih
                </p>
            </div>
            <input type="text" name="reviewer_note" x-model="note" placeholder="Catatan / alasan (wajib saat menolak)" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs font-bold text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 lg:max-w-xs">
            <div class="flex shrink-0 gap-2">
                <button type="button" @click="submitBatch('approve')" :disabled="selected.length === 0"
                        class="rounded-xl bg-emerald-600 px-4 py-2.5 text-[11px] font-black text-white shadow-sm transition active:scale-[0.98] disabled:opacity-40 disabled:active:scale-100">
                    Setujui terpilih
                </button>
                <button type="button" @click="submitBatch('reject')" :disabled="selected.length === 0"
                        class="rounded-xl bg-rose-600 px-4 py-2.5 text-[11px] font-black text-white shadow-sm transition active:scale-[0.98] disabled:opacity-40 disabled:active:scale-100">
                    Tolak terpilih
                </button>
            </div>
        </div>
    </form>

    {{-- List --}}
    <div class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        @forelse($requests as $request)
            @php
                $badge = $statusBadges[$request->status] ?? ['Diajukan', 'bg-gray-50 text-gray-500 ring-gray-200'];
                $groupLabel = $groupLabels[$request->categoryGroup()] ?? 'Pengajuan';
            @endphp
            <div class="grid gap-3 p-4 sm:p-5 lg:grid-cols-[auto_minmax(0,1fr)_auto] lg:items-start">
                <label class="flex items-center lg:pt-1">
                    @if($request->status === 'pending')
                        <input type="checkbox" :value="{{ $request->id }}" x-model="selected"
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    @endif
                </label>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-black text-gray-900">{{ $request->student?->name ?? 'Siswa' }}</p>
                        @if($request->class)
                            <span class="rounded-lg bg-gray-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-gray-500 ring-1 ring-gray-100">{{ $request->class->name }}</span>
                        @endif
                        <span class="rounded-lg px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-gray-400 ring-1 ring-gray-100">{{ $groupLabel }}</span>
                        <span class="rounded-lg px-2 py-0.5 text-[10px] font-black ring-1 {{ $badge[1] }}">{{ $badge[0] }}</span>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] font-bold text-gray-500">
                        <span>{{ $request->categoryLabel() }}</span>
                        <span class="text-gray-300">•</span>
                        <span>
                            @if($request->isMultiDay())
                                {{ $request->date->translatedFormat('d M Y') }} - {{ $request->date_end->translatedFormat('d M Y') }}
                            @else
                                {{ $request->date->translatedFormat('d M Y') }}
                            @endif
                        </span>
                        <span class="text-gray-300">•</span>
                        <span>Diajukan {{ $request->created_at->translatedFormat('d M Y H:i') }}</span>
                    </div>

                    @if($request->activity_name)
                        <p class="mt-1 text-xs font-bold text-gray-700">Kegiatan: {{ $request->activity_name }}</p>
                    @endif
                    @if($request->activity_start_time || $request->activity_end_time)
                        <p class="mt-0.5 text-[11px] font-semibold text-gray-500">
                            Jam: {{ $request->activity_start_time ? \Illuminate\Support\Carbon::parse($request->activity_start_time)->format('H:i') : '?' }} - {{ $request->activity_end_time ? \Illuminate\Support\Carbon::parse($request->activity_end_time)->format('H:i') : '?' }}
                        </p>
                    @endif
                    <p class="mt-1 break-words text-xs leading-5 text-gray-600">{{ $request->reason }}</p>

                    @if($request->reviewer)
                        <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-semibold text-gray-400">
                            <span>Direview oleh {{ $request->reviewer->name }}</span>
                            @if($request->reviewed_at)
                                <span class="text-gray-300">•</span>
                                <span>{{ $request->reviewed_at->translatedFormat('d M Y H:i') }}</span>
                            @endif
                        </div>
                    @endif
                    @if($request->reviewer_note)
                        <p class="mt-1 break-words text-[11px] font-medium leading-4 text-gray-500">Catatan: {{ $request->reviewer_note }}</p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:flex-col lg:items-end">
                    @if($request->evidence_path)
                        <a href="{{ route('attendance.early-leave.evidence', $request) }}" target="_blank" rel="noopener"
                           class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-[10px] font-black text-gray-600 transition hover:bg-gray-100">
                            Buka bukti
                        </a>
                    @endif
                    @if($request->status === 'pending')
                        <div class="flex flex-wrap items-center gap-2 lg:flex-col lg:items-stretch">
                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.early-leave.review', $request) }}">
                                @csrf
                                <input type="hidden" name="decision" value="approve">
                                <button class="w-full rounded-xl bg-emerald-600 px-4 py-2 text-[11px] font-black text-white shadow-sm transition active:scale-[0.98]">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('wali-kelas.daily-attendance.early-leave.review', $request) }}" class="grid min-w-0 gap-2 lg:grid-cols-[minmax(0,1fr)_auto]">
                                @csrf
                                <input type="hidden" name="decision" value="reject">
                                <input required name="reviewer_note" placeholder="Alasan penolakan" class="min-w-0 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-[11px] focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                <button class="rounded-xl bg-rose-600 px-4 py-2 text-[11px] font-black text-white shadow-sm transition active:scale-[0.98]">Tolak</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <p class="text-sm font-black text-gray-900">Tidak ada pengajuan</p>
                <p class="mt-1 text-xs text-gray-500">Tidak ada pengajuan izin / dispensasi yang cocok dengan filter.</p>
            </div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="flex justify-center">
            {{ $requests->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
