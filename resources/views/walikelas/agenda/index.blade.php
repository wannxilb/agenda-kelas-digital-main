@extends('layouts.walikelas')

@section('title', 'Agenda Kelas')
@section('header', 'Agenda Kelas')
@section('content-padding', 'py-4 sm:py-8 pb-28 lg:pb-8')

@section('content')
@if($has_class)
@php
    $period = request('period', 'all');
    $search = request('search');
    $periodLabels = [
        'all' => 'Semua',
        'today' => 'Hari Ini',
        'week' => 'Minggu Ini',
        'month' => 'Bulan Ini',
    ];
    $periodOptions = [
        ['all', 'Semua', $stats['total'] ?? 0],
        ['today', 'Hari Ini', $stats['today'] ?? 0],
        ['week', 'Minggu Ini', $stats['week'] ?? 0],
        ['month', 'Bulan Ini', $stats['month'] ?? 0],
    ];
@endphp

<div class="space-y-4 sm:space-y-6">
    @include('walikelas.partials.context-filter')

    {{-- Header --}}
    <div class="relative overflow-hidden bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-blue-200/60">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-blue-100/90 tracking-wide">Monitoring jurnal mengajar</p>
                <h1 class="mt-0.5 text-lg sm:text-2xl font-black truncate">Kelas {{ $class->name ?? '-' }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-bold backdrop-blur-sm ring-1 ring-white/10">
                        {{ $agendas->total() }} agenda tampil
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-bold backdrop-blur-sm ring-1 ring-white/10">
                        {{ $periodLabels[$period] ?? 'Semua' }}
                    </span>
                </div>
            </div>
            <a href="{{ route('wali-kelas.agenda.archive') }}"
               class="shrink-0 w-11 h-11 sm:w-auto sm:px-4 sm:py-2.5 bg-white/15 rounded-2xl flex items-center justify-center gap-2 backdrop-blur-sm ring-1 ring-white/20 hover:bg-white/25 active:scale-95 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
                <span class="hidden sm:inline text-xs font-black uppercase tracking-widest">Arsip</span>
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Aktif</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-indigo-50/70 rounded-2xl border border-indigo-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-indigo-600/70 uppercase tracking-wider mb-1">Hari Ini</p>
                <p class="text-xl sm:text-2xl font-black text-indigo-600">{{ $stats['today'] ?? 0 }}</p>
            </div>
            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-emerald-50/70 rounded-2xl border border-emerald-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Minggu Ini</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $stats['week'] ?? 0 }}</p>
            </div>
            <div class="snap-center shrink-0 w-32.5 sm:w-auto bg-amber-50/70 rounded-2xl border border-amber-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Bulan Ini</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $stats['month'] ?? 0 }}</p>
            </div>
        </div>
        <p class="sm:hidden mt-1 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">Geser untuk melihat ringkasan</p>
    </div>

    {{-- Filter --}}
    <div class="space-y-3">
        <form method="GET" action="{{ route('wali-kelas.agenda.index') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @if($selectedWaliContextKey ?? null)
                <input type="hidden" name="wali_context" value="{{ $selectedWaliContextKey }}">
            @endif
            <input type="hidden" name="period" value="{{ $period !== 'all' ? $period : '' }}">
            <div class="p-3 sm:p-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul, mapel, atau guru..."
                           class="block w-full pl-9 pr-24 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-semibold">
                    <button type="submit" class="absolute inset-y-1 right-1 px-4 bg-indigo-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 active:scale-95 transition-all">
                        Cari
                    </button>
                </div>
            </div>
        </form>

        <div class="flex gap-2 overflow-x-auto hide-scrollbar pb-1 -mx-1 px-1">
            @foreach($periodOptions as [$value, $label, $count])
                <a href="{{ route('wali-kelas.agenda.index', array_filter(['wali_context' => $selectedWaliContextKey ?? null, 'period' => $value === 'all' ? null : $value, 'search' => $search])) }}"
                   class="shrink-0 inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-[11px] font-bold border transition-all active:scale-95 {{ $period === $value || ($value === 'all' && !$period) ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-100' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-white' }}">
                    <span>{{ $label }}</span>
                    <span class="{{ $period === $value || ($value === 'all' && !$period) ? 'bg-white/20 text-white' : 'bg-white text-gray-400 border border-gray-100' }} px-1.5 py-0.5 rounded-md text-[9px] font-black">{{ $count }}</span>
                </a>
            @endforeach
            @if($search || ($period && $period !== 'all'))
                <a href="{{ route('wali-kelas.agenda.index', array_filter(['wali_context' => $selectedWaliContextKey ?? null])) }}" class="shrink-0 inline-flex items-center px-3.5 py-2 rounded-xl text-[11px] font-bold border bg-rose-50 text-rose-600 border-rose-100">
                    Reset
                </a>
            @endif
        </div>
    </div>

    {{-- Mobile List --}}
    <div class="lg:hidden space-y-3">
        @forelse($agendas as $agenda)
            @php
                $agendaDate = \Carbon\Carbon::parse($agenda->date);
                $isToday = $agendaDate->isToday();
                $isYesterday = $agendaDate->isYesterday();
            @endphp
            <a href="{{ route('wali-kelas.agenda.show', ['agenda' => $agenda->id, 'wali_context' => $selectedWaliContextKey ?? null]) }}"
               class="block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden active:scale-[0.99] transition-all">
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 {{ $isToday ? 'bg-indigo-600 shadow-lg shadow-indigo-100' : ($isYesterday ? 'bg-gray-800' : 'bg-gray-100 text-gray-500') }} {{ $isToday || $isYesterday ? 'text-white' : '' }} rounded-2xl flex flex-col items-center justify-center shrink-0">
                            <span class="text-[7px] font-black uppercase leading-none opacity-70 mb-0.5">{{ $agendaDate->translatedFormat('M') }}</span>
                            <span class="text-sm font-black leading-none">{{ $agendaDate->format('d') }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-black text-gray-900 leading-snug line-clamp-2">{{ $agenda->title }}</p>
                                    <p class="mt-1 text-[11px] font-bold text-indigo-600 truncate">{{ $agenda->subject->name ?? 'Umum' }}</p>
                                </div>
                                @if($isToday)
                                    <span class="shrink-0 px-2 py-1 bg-indigo-50 text-indigo-700 border border-indigo-100 text-[9px] font-black uppercase tracking-widest rounded-lg">Hari Ini</span>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 text-gray-600 rounded-lg border border-gray-100 text-[10px] font-bold">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $agendaDate->translatedFormat('d M Y') }}
                                </span>
                                @if($agenda->room)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-sky-50 text-sky-700 rounded-lg border border-sky-100 text-[10px] font-bold">
                                        {{ $agenda->room }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50/70 border-t border-gray-100 flex items-center gap-2">
                    <div class="w-7 h-7 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-[9px] font-black shrink-0">
                        {{ strtoupper(substr($agenda->teacher->name ?? '-', 0, 1)) }}
                    </div>
                    <p class="text-xs font-bold text-gray-700 truncate">{{ $agenda->teacher->name ?? '-' }}</p>
                    <svg class="ml-auto w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center shadow-sm">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Agenda tidak ditemukan</p>
                <p class="text-xs text-gray-400 mt-1">Coba ubah filter atau kata kunci pencarian.</p>
            </div>
        @endforelse
    </div>

    {{-- Desktop Table --}}
    <div class="hidden lg:block bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 tracking-tight uppercase">Daftar Agenda</h3>
            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100">{{ $agendas->total() }} Agenda</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mapel</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Judul</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Guru</th>
                        <th class="px-6 py-4 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($agendas as $agenda)
                        @php
                            $agendaDate = \Carbon\Carbon::parse($agenda->date);
                            $isToday = $agendaDate->isToday();
                        @endphp
                        <tr class="group hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 {{ $isToday ? 'bg-indigo-600 shadow-lg shadow-indigo-100 text-white' : 'bg-gray-100 text-gray-500' }} rounded-xl flex flex-col items-center justify-center shrink-0">
                                        <span class="text-[7px] font-black uppercase leading-none opacity-70 mb-0.5">{{ $agendaDate->translatedFormat('M') }}</span>
                                        <span class="text-xs font-black leading-none">{{ $agendaDate->format('d') }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ $agendaDate->translatedFormat('d M Y') }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $agendaDate->translatedFormat('l') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100">{{ $agenda->subject->name ?? 'Umum' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900 line-clamp-1">{{ $agenda->title }}</p>
                                @if($agenda->room)
                                    <p class="mt-0.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $agenda->room }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-500">{{ $agenda->teacher->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('wali-kelas.agenda.show', ['agenda' => $agenda->id, 'wali_context' => $selectedWaliContextKey ?? null]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center text-sm font-bold text-gray-400">Agenda tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($agendas->hasPages())
        <div class="pt-1">
            {{ $agendas->links() }}
        </div>
    @endif
</div>
@else
<div class="space-y-4 sm:space-y-6 pb-24 lg:pb-8">
    <div class="bg-white border border-gray-100 rounded-2xl p-10 sm:p-16 text-center shadow-sm">
        <div class="flex flex-col items-center gap-4">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-50 rounded-2xl flex items-center justify-center border-2 border-dashed border-gray-200">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
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
