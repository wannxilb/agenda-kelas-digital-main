{{-- resources/views/wakasek/teacher-status/show.blade.php --}}
@extends('layouts.wakasek')

@section('title', 'Detail Pengajuan')
@section('header', 'Detail Pengajuan')

@section('content')
@php
    $badge = [
        'pending' => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500', 'Menunggu Persetujuan'],
        'approved' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500', 'Disetujui'],
        'rejected' => ['bg-rose-50 text-rose-700 border-rose-200', 'bg-rose-500', 'Ditolak'],
        'cancelled' => ['bg-gray-100 text-gray-500 border-gray-200', 'bg-gray-400', 'Dibatalkan'],
    ];
    $badgeClass = $badge[$teacherStatus->status] ?? $badge['pending'];
@endphp
<div class="mx-auto max-w-3xl space-y-4 sm:space-y-6 pb-24 sm:pb-6"
     x-data="{ actionModal: null, rejectionReason: '', cancellationReason: '', substituteId: '' }">
    {{-- Header --}}
    <div class="bg-linear-to-br from-blue-600 to-indigo-700 rounded-2xl p-5 sm:p-7 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('wakasek.teacher-status.index') }}" class="w-9 h-9 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center text-white hover:bg-white/30 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-lg font-bold text-white">Detail Pengajuan</h2>
                    <p class="text-[11px] text-blue-100">{{ $teacherStatus->teacher?->name ?? '-' }}</p>
                </div>
            </div>
            <span class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-widest border bg-white {{ $badgeClass[0] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $badgeClass[1] }}"></span> {{ $badgeClass[2] }}
            </span>
        </div>
    </div>

    {{-- Info card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-sm font-bold text-gray-900">Informasi Pengajuan</h3>
        </div>
        <div class="divide-y divide-gray-50">
            <div class="px-5 sm:px-6 py-3.5 flex items-start justify-between gap-4">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider shrink-0">Guru</span>
                <span class="text-sm font-semibold text-gray-800 text-right">{{ $teacherStatus->teacher?->name ?? '-' }}</span>
            </div>
            <div class="px-5 sm:px-6 py-3.5 flex items-start justify-between gap-4">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider shrink-0">Tipe</span>
                <span class="text-sm font-semibold text-gray-800 text-right">{{ $teacherStatus->typeLabel() }}</span>
            </div>
            <div class="px-5 sm:px-6 py-3.5 flex items-start justify-between gap-4">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider shrink-0">Tanggal</span>
                <span class="text-sm font-semibold text-gray-800 text-right">
                    {{ \Carbon\Carbon::parse($teacherStatus->date)->translatedFormat('d F Y') }}
                    @if($teacherStatus->date_end && $teacherStatus->date_end->toDateString() !== $teacherStatus->date->toDateString())
                        – {{ \Carbon\Carbon::parse($teacherStatus->date_end)->translatedFormat('d F Y') }}
                    @endif
                </span>
            </div>
            @if($teacherStatus->start_time || $teacherStatus->end_time)
            <div class="px-5 sm:px-6 py-3.5 flex items-start justify-between gap-4">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider shrink-0">Jam</span>
                <span class="text-sm font-semibold text-gray-800 text-right">{{ substr($teacherStatus->start_time, 0, 5) }} – {{ substr($teacherStatus->end_time, 0, 5) }}</span>
            </div>
            @endif
            <div class="px-5 sm:px-6 py-3.5 flex items-start justify-between gap-4">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider shrink-0">Keterangan</span>
                <span class="text-sm text-gray-700 text-right whitespace-pre-line">{{ $teacherStatus->note ?? '-' }}</span>
            </div>
            @if($teacherStatus->attachment)
            <div class="px-5 sm:px-6 py-3.5 flex items-start justify-between gap-4">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider shrink-0">Lampiran Surat</span>
                <a href="{{ route('wakasek.teacher-status.attachment', $teacherStatus) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7.586a2 2 0 00-2.828 0l-5.657 5.657a4 4 0 005.657 5.657l5.656-5.657a1 1 0 00-1.414-1.414l-5.657 5.657a2 2 0 01-2.828-2.829l5.657-5.657a4 4 0 015.657 5.657l-5.657 5.657"></path>
                    </svg>
                    Buka surat lampiran
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Approval history --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-sm font-bold text-gray-900">Riwayat Persetujuan</h3>
        </div>
        <div class="p-5 sm:p-6 space-y-3">
            @if($teacherStatus->isPending())
                <div class="flex items-center gap-2.5 text-sm text-amber-700">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Menunggu persetujuan Anda.
                </div>
            @elseif($teacherStatus->isApproved())
                <div class="flex items-center gap-2.5 text-sm text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Disetujui oleh {{ $teacherStatus->approver?->name ?? 'Anda' }}
                    @if($teacherStatus->processed_at) · {{ $teacherStatus->processed_at->translatedFormat('d M Y H:i') }} @endif
                </div>
                @if($teacherStatus->substituteTeacher)
                    <div class="text-sm text-gray-600">Guru pengganti: <b>{{ $teacherStatus->substituteTeacher->name }}</b></div>
                @endif
                <button type="button" @click="actionModal='cancel'; cancellationReason=''"
                        class="inline-flex px-4 py-2.5 bg-white border border-rose-200 text-rose-600 rounded-xl text-xs font-bold hover:bg-rose-50 transition-all">
                    Batalkan Persetujuan
                </button>
            @elseif($teacherStatus->isRejected())
                <div class="flex items-center gap-2.5 text-sm text-rose-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Ditolak oleh {{ $teacherStatus->approver?->name ?? 'Anda' }}
                </div>
                <div class="bg-rose-50 border border-rose-100 rounded-xl px-4 py-3 text-sm text-rose-800">
                    <p class="text-[10px] font-bold text-rose-400 uppercase tracking-wider mb-1">Alasan penolakan</p>
                    {{ $teacherStatus->rejection_reason ?? '-' }}
                </div>
            @else
                <div class="flex items-center gap-2.5 text-sm text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Dibatalkan oleh {{ $teacherStatus->cancelledBy?->name ?? 'sistem' }}
                </div>
                @if($teacherStatus->cancellation_reason)
                    <div class="bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm text-gray-700">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Alasan pembatalan</p>
                        {{ $teacherStatus->cancellation_reason }}
                    </div>
                @endif
            @endif

            @if($teacherStatus->isPending())
            <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-100">
                <button type="button" @click="actionModal='approve'; substituteId=''"
                        class="flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all">Setujui</button>
                <button type="button" @click="actionModal='reject'; rejectionReason=''"
                        class="flex-1 px-4 py-2.5 bg-white border border-rose-200 text-rose-600 rounded-xl text-xs font-bold hover:bg-rose-50 transition-all">Tolak</button>
            </div>
            @endif
        </div>
    </div>

    {{-- Modals (sama seperti index) --}}
    <div x-show="actionModal === 'approve'" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="actionModal = null"></div>
        <div class="relative bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md overflow-hidden p-5 sm:p-6">
            <h3 class="text-base font-bold text-gray-900">Setujui Pengajuan</h3>
            <p class="text-xs text-gray-500 mt-1">Konfirmasi persetujuan. Guru pengganti opsional.</p>
            <form action="{{ route('wakasek.teacher-status.approve', $teacherStatus) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-700">Guru Pengganti <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <select name="substitute_teacher_id" x-model="substituteId"
                            class="w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 transition-all text-sm text-gray-700">
                        <option value="">Tidak ada pengganti</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="actionModal = null" class="h-11 flex-1 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200 transition-all">Batal</button>
                    <button type="submit" class="h-11 flex-1 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition-all">Setujui</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="actionModal === 'reject'" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="actionModal = null"></div>
        <div class="relative bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md overflow-hidden p-5 sm:p-6">
            <h3 class="text-base font-bold text-gray-900">Tolak Pengajuan</h3>
            <p class="text-xs text-gray-500 mt-1">Alasan penolakan wajib diisi.</p>
            <form action="{{ route('wakasek.teacher-status.reject', $teacherStatus) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <textarea name="rejection_reason" x-model="rejectionReason" rows="3" required
                          placeholder="Tuliskan alasan penolakan..."
                          class="w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-sm resize-none"></textarea>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="actionModal = null" class="h-11 flex-1 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200 transition-all">Batal</button>
                    <button type="submit" class="h-11 flex-1 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 transition-all">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="actionModal === 'cancel'" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm" @click="actionModal = null"></div>
        <div class="relative bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md overflow-hidden p-5 sm:p-6">
            <h3 class="text-base font-bold text-gray-900">Batalkan Persetujuan</h3>
            <p class="text-xs text-gray-500 mt-1">Alasan pembatalan wajib diisi.</p>
            <form action="{{ route('wakasek.teacher-status.cancel', $teacherStatus) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <textarea name="cancellation_reason" x-model="cancellationReason" rows="3" required
                          placeholder="Tuliskan alasan pembatalan..."
                          class="w-full px-3 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-sm resize-none"></textarea>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="actionModal = null" class="h-11 flex-1 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200 transition-all">Batal</button>
                    <button type="submit" class="h-11 flex-1 bg-rose-600 text-white rounded-xl text-xs font-bold hover:bg-rose-700 transition-all">Batalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
