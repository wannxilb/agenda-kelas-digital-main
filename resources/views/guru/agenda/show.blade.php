{{-- resources/views/guru/agenda/show.blade.php --}}
@extends('layouts.guru')

@section('title', 'Detail Jurnal')
@section('header', 'Detail Jurnal')

@section('content')
<div class="space-y-4 sm:space-y-6 pb-36 sm:pb-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-500">
        <a href="{{ route('guru.agenda.index') }}" class="hover:text-indigo-600 transition-colors font-medium">Jurnal</a>
        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span class="text-gray-900 font-semibold">Detail</span>
    </nav>

    {{-- ===== HEADER CARD ===== --}}
    <div class="bg-linear-to-br from-indigo-600 to-blue-700 rounded-2xl sm:rounded-3xl p-5 sm:p-8 text-white shadow-xl shadow-indigo-200/40 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-12 -mt-12 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-10 -mb-10 blur-3xl"></div>
        <div class="relative flex items-start gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center shrink-0 shadow-lg shadow-black/10">
                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold
                        {{ $agenda->status === 'draft' ? 'bg-amber-400/20 text-amber-200 border border-amber-400/30' : 'bg-emerald-400/20 text-emerald-200 border border-emerald-400/30' }}">
                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full {{ $agenda->status === 'draft' ? 'bg-amber-400' : 'bg-emerald-400' }} animate-pulse"></span>
                        {{ $agenda->status === 'draft' ? 'Draft' : 'Published' }}
                    </span>
                </div>
                <h1 class="text-lg sm:text-2xl font-bold tracking-tight drop-shadow-sm">{{ $agenda->title }}</h1>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-indigo-200">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ \Carbon\Carbon::parse($agenda->date)->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ $agenda->created_at->format('H:i') }} WIB
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== INFO GRID ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        {{-- Mapel --}}
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-linear-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center text-white shadow-sm shadow-blue-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Mata Pelajaran</span>
            </div>
            <p class="text-sm sm:text-base font-bold text-gray-900">{{ $agenda->subject->name }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $agenda->academicYear->name ?? 'Tahun Ajaran' }} {{ $agenda->academicYear->semester ? '/ ' . $agenda->academicYear->semester : '' }}</p>
        </div>

        {{-- Kelas --}}
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-linear-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center text-white shadow-sm shadow-emerald-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kelas</span>
            </div>
            <p class="text-sm sm:text-base font-bold text-gray-900">{{ $agenda->class->name }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $agenda->academicYear->name ?? '' }}</p>
        </div>

        {{-- Ruangan --}}
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 bg-linear-to-br from-amber-500 to-orange-600 rounded-lg flex items-center justify-center text-white shadow-sm shadow-amber-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Ruangan</span>
            </div>
            <p class="text-sm sm:text-base font-bold text-gray-900">{{ $agenda->room ?? '—' }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Ruang Belajar</p>
        </div>
    </div>

    {{-- ===== DESKRIPSI ===== --}}
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-8">
        <div class="flex items-center gap-3 mb-5 sm:mb-6">
            <div class="w-10 h-10 bg-linear-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-sm shadow-indigo-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-base sm:text-lg font-bold text-gray-900">Detail Aktivitas</h2>
                <p class="text-xs sm:text-sm text-gray-500">Catatan pembelajaran sesi ini</p>
            </div>
        </div>

        @if($agenda->description)
        <div class="prose prose-sm sm:prose-base prose-blue max-w-none text-gray-700 leading-relaxed">
            {!! strip_tags($agenda->description, '<p><strong><em><u><ul><ol><li><br><h1><h2><h3><h4><h5><h6><blockquote><pre><code>') !!}
        </div>
        @else
        <div class="text-center py-10 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
            <p class="text-sm font-medium">Tidak ada deskripsi aktivitas</p>
        </div>
        @endif

        @if($agenda->attachments)
        <div class="mt-6 pt-6 border-t border-gray-100">
            <a href="{{ asset('storage/' . $agenda->attachments) }}" target="_blank"
               class="flex items-center gap-4 p-4 bg-blue-50 hover:bg-blue-100 rounded-xl border border-blue-100 transition-colors group">
                <div class="w-10 h-10 bg-linear-to-br from-blue-500 to-indigo-600 text-white rounded-xl flex items-center justify-center shadow-md shadow-blue-200/50 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900">Unduh Lampiran</p>
                    <p class="text-[11px] text-blue-600 font-medium truncate">{{ basename($agenda->attachments) }}</p>
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>
        @endif
    </div>

    {{-- ===== TIMESTAMP ===== --}}
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-8">
        <div class="flex items-center gap-3 mb-5 sm:mb-6">
            <div class="w-10 h-10 bg-linear-to-br from-gray-500 to-slate-600 rounded-xl flex items-center justify-center text-white shadow-sm shadow-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-base sm:text-lg font-bold text-gray-900">Informasi Waktu</h2>
                <p class="text-xs sm:text-sm text-gray-500">Riwayat pembuatan dan perubahan</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex items-center gap-3 p-3 sm:p-4 bg-blue-50/50 rounded-xl border border-blue-100/50">
                <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Dibuat</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $agenda->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 sm:p-4 bg-amber-50/50 rounded-xl border border-amber-100/50">
                <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Diperbarui</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $agenda->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== GURU ===== --}}
    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 p-5 sm:p-8">
        <div class="flex items-center gap-3 mb-5 sm:mb-6">
            <div class="w-10 h-10 bg-linear-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center text-white shadow-sm shadow-emerald-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-base sm:text-lg font-bold text-gray-900">Guru Pengampu</h2>
                <p class="text-xs sm:text-sm text-gray-500">Pengajar yang melaksanakan sesi ini</p>
            </div>
        </div>
        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
            <div class="w-12 h-12 rounded-full overflow-hidden bg-linear-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-md shrink-0">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500">{{ Auth::user()->email ?? '' }}</p>
            </div>
        </div>
    </div>

    {{-- ===== MOBILE DESKTOP ACTION ===== --}}
    <div class="hidden sm:flex items-center justify-end gap-3">
        <a href="{{ request('from') === 'archive' ? route('guru.agenda.archive') : route('guru.agenda.index') }}"
           class="px-5 py-2.5 text-sm font-semibold text-gray-400 hover:text-rose-600 transition-all rounded-xl hover:bg-rose-50 active:scale-95 inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
        <a href="{{ route('guru.agenda.edit', $agenda) }}"
           class="px-5 py-2.5 text-sm font-semibold text-white bg-linear-to-r from-indigo-600 to-blue-600 rounded-xl hover:from-indigo-700 hover:to-blue-700 transition-all shadow-lg shadow-indigo-200 active:scale-95 inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Edit Jurnal
        </a>
        <button onclick="window.print()"
                class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-all shadow-sm active:scale-95 inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak
        </button>
    </div>
</div>

{{-- Mobile: Sticky Bottom Bar --}}
<div class="sm:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-t border-gray-200 safe-bottom shadow-2xl">
    <div class="px-3 py-4 flex items-center gap-2">
        <a href="{{ request('from') === 'archive' ? route('guru.agenda.archive') : route('guru.agenda.index') }}"
           class="flex-1 px-3 py-4 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold hover:bg-gray-200 active:scale-95 transition-all text-center inline-flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
        <button onclick="window.print()"
                class="flex-1 px-3 py-4 bg-white border border-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-50 active:scale-95 transition-all inline-flex items-center justify-center gap-1.5 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak
        </button>
        <a href="{{ route('guru.agenda.edit', $agenda) }}"
           class="flex-[2] px-3 py-4 bg-linear-to-r from-indigo-600 to-blue-600 text-white rounded-xl text-xs font-bold hover:from-indigo-700 hover:to-blue-700 active:scale-95 transition-all shadow-lg shadow-indigo-200 inline-flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Edit Jurnal
        </a>
    </div>
</div>

<style>
    @media print {
        body { background: white !important; }
        .no-print { display: none !important; }
        @page { margin: 1.5cm; }
        .sm\:hidden { display: none !important; }
        .hidden.sm\:flex { display: flex !important; }
    }
    .prose h1, .prose h2, .prose h3, .prose h4 { color: #1e293b; font-weight: 700; }
    .prose p { margin-bottom: 0.75em; }
    .prose ul, .prose ol { padding-left: 1.25em; }
    .prose li { margin-bottom: 0.25em; }
</style>
@endsection