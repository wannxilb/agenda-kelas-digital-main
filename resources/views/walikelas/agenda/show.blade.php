@extends('layouts.walikelas')

@section('title', 'Detail Agenda Kelas')
@section('header', 'Detail Agenda')
@section('content-padding', 'py-4 sm:py-8 pb-28 lg:pb-8')

@section('content')
@php
    $agendaDate = \Carbon\Carbon::parse($agenda->date);
    $isToday = $agendaDate->isToday();
    $backRoute = request('from') === 'archive'
        ? route('wali-kelas.agenda.archive')
        : route('wali-kelas.agenda.index', array_filter(['wali_context' => $selectedWaliContextKey ?? null]));
    $attachments = collect(is_array($agenda->attachments) ? $agenda->attachments : ($agenda->attachments ? [$agenda->attachments] : []))
        ->filter()
        ->values();
@endphp

<div class="max-w-5xl mx-auto space-y-4 sm:space-y-6">
    {{-- Top Action --}}
    <div class="hidden sm:flex items-center justify-between">
        <a href="{{ $backRoute }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-600 rounded-xl border border-gray-100 shadow-sm text-sm font-bold hover:text-indigo-600 hover:border-indigo-100 active:scale-95 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-100 text-sm font-bold hover:bg-indigo-700 active:scale-95 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Cetak
        </button>
    </div>

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-7 text-white shadow-lg shadow-blue-200/60">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-start gap-4">
            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-white/15 rounded-2xl flex flex-col items-center justify-center shrink-0 ring-1 ring-white/20 shadow-inner">
                <span class="text-[8px] font-black uppercase leading-none text-blue-100 mb-1">{{ $agendaDate->translatedFormat('M') }}</span>
                <span class="text-xl font-black leading-none">{{ $agendaDate->format('d') }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-1 bg-white/15 rounded-lg text-[10px] font-black uppercase tracking-widest ring-1 ring-white/10">
                        {{ $agenda->subject->name ?? 'Umum' }}
                    </span>
                    @if($isToday)
                        <span class="inline-flex items-center px-2.5 py-1 bg-emerald-400/20 text-emerald-100 rounded-lg text-[10px] font-black uppercase tracking-widest ring-1 ring-emerald-300/20">
                            Hari Ini
                        </span>
                    @endif
                </div>
                <h1 class="text-lg sm:text-3xl font-black leading-tight tracking-tight">{{ $agenda->title }}</h1>
                <p class="mt-2 text-xs sm:text-sm font-medium text-blue-100/90">{{ $agendaDate->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Meta Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Kelas</p>
            <p class="text-sm font-black text-gray-900 truncate">{{ $agenda->class->name ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Ruangan</p>
            <p class="text-sm font-black text-gray-900 truncate">{{ $agenda->room ?: '-' }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Status</p>
            <p class="text-sm font-black text-emerald-600 truncate">{{ ucfirst($agenda->status ?? 'published') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Dibuat</p>
            <p class="text-sm font-black text-gray-900 truncate">{{ $agenda->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">
            <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-gray-50 flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm sm:text-base font-black text-gray-900">Detail Aktivitas</h2>
                        <p class="text-[11px] sm:text-xs font-medium text-gray-500">Catatan pembelajaran dari guru pengajar</p>
                    </div>
                </div>
                <div class="p-5 sm:p-6">
                    @if($agenda->description)
                        <div class="agenda-prose text-sm sm:text-base text-gray-700 leading-relaxed">
                            {!! strip_tags($agenda->description, '<p><strong><em><u><ul><ol><li><br><blockquote>') !!}
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-400">Tidak ada deskripsi aktivitas.</p>
                        </div>
                    @endif
                </div>
            </section>

            @if($attachments->isNotEmpty())
                <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-50 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm sm:text-base font-black text-gray-900">Lampiran</h2>
                            <p class="text-[11px] sm:text-xs font-medium text-gray-500">{{ $attachments->count() }} file tersedia</p>
                        </div>
                    </div>
                    <div class="p-4 sm:p-5 space-y-2">
                        @foreach($attachments as $attachment)
                            <a href="{{ asset('storage/' . $attachment) }}" target="_blank"
                               class="flex items-center gap-3 p-3 bg-indigo-50/60 hover:bg-indigo-50 rounded-2xl border border-indigo-100 transition-all active:scale-[0.99]">
                                <div class="w-10 h-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-black text-gray-900 truncate">Lampiran {{ $loop->iteration }}</p>
                                    <p class="text-[11px] font-medium text-indigo-700 truncate">{{ basename($attachment) }}</p>
                                </div>
                                <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-4 sm:space-y-6">
            <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Guru Pengajar</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-2xl flex items-center justify-center font-black shadow-lg shadow-indigo-100 shrink-0">
                            {{ strtoupper(substr($agenda->teacher->name ?? '-', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-black text-gray-900 truncate">{{ $agenda->teacher->name ?? '-' }}</p>
                            <p class="text-[11px] font-medium text-gray-500 truncate">NIP: {{ $agenda->teacher->nip ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 space-y-3">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ringkasan</p>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-3 p-3 bg-gray-50 rounded-2xl">
                            <span class="text-xs font-bold text-gray-500">Mata Pelajaran</span>
                            <span class="text-xs font-black text-gray-900 text-right truncate">{{ $agenda->subject->name ?? 'Umum' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-3 bg-gray-50 rounded-2xl">
                            <span class="text-xs font-bold text-gray-500">Tanggal</span>
                            <span class="text-xs font-black text-gray-900 text-right">{{ $agendaDate->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 p-3 bg-gray-50 rounded-2xl">
                            <span class="text-xs font-bold text-gray-500">Terakhir Update</span>
                            <span class="text-xs font-black text-gray-900 text-right">{{ $agenda->updated_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>

{{-- Mobile Bottom Bar --}}
<div class="sm:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-t border-gray-200 safe-bottom shadow-2xl">
    <div class="px-3 py-4 flex items-center gap-2">
        <a href="{{ $backRoute }}"
           class="flex-1 px-3 py-4 bg-gray-100 text-gray-700 rounded-xl text-xs font-bold active:scale-95 transition-all text-center inline-flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <button type="button" onclick="window.print()"
                class="flex-1 px-3 py-4 bg-indigo-600 text-white rounded-xl text-xs font-bold active:scale-95 transition-all shadow-lg shadow-indigo-100 inline-flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Cetak
        </button>
    </div>
</div>
@endsection

@push('styles')
<style>
    .agenda-prose p { margin-bottom: 0.75rem; }
    .agenda-prose ul,
    .agenda-prose ol { margin: 0.75rem 0; padding-left: 1.25rem; }
    .agenda-prose li { margin-bottom: 0.35rem; }
    .agenda-prose strong { color: #111827; font-weight: 800; }
    .agenda-prose blockquote {
        border-left: 3px solid #6366f1;
        background: #eef2ff;
        color: #3730a3;
        padding: 0.75rem 1rem;
        border-radius: 0 0.75rem 0.75rem 0;
        margin: 0.75rem 0;
        font-weight: 600;
    }
    @media print {
        .fixed,
        button,
        a[href] { display: none !important; }
        body { background: white !important; }
    }
</style>
@endpush
