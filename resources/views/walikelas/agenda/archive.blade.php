@extends('layouts.walikelas')

@section('title', 'Arsip Agenda Kelas')
@section('header', 'Arsip Agenda Kelas')
@section('content-padding', 'py-4 sm:py-8 pb-28 lg:pb-8')

@section('content')
@php
    $search = request('search');
    $selectedClassId = request('class_id');
    $selectedClass = $classes->firstWhere('id', (int) $selectedClassId);
    $visibleAgendas = $agendas->getCollection();
    $visibleClassCount = $visibleAgendas->pluck('class_id')->filter()->unique()->count();
    $visibleTeacherCount = $visibleAgendas->pluck('teacher_id')->filter()->unique()->count();
    $latestAgendaDate = optional($visibleAgendas->first())->date ? \Carbon\Carbon::parse($visibleAgendas->first()->date) : null;
    $hasFilter = request()->anyFilled(['search', 'class_id']);
@endphp

<div class="space-y-4 sm:space-y-6">
    {{-- Header --}}
    <div class="relative overflow-hidden bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 rounded-3xl p-4 sm:p-6 text-white shadow-lg shadow-blue-200/60">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -bottom-12 -left-8 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative flex items-center justify-between gap-4">
            <div class="min-w-0 flex-1">
                <p class="text-[11px] sm:text-xs font-semibold text-blue-100/90 tracking-wide">Riwayat jurnal mengajar</p>
                <h1 class="mt-0.5 text-lg sm:text-2xl font-black truncate">Arsip Agenda Kelas</h1>
                <div class="mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-bold backdrop-blur-sm ring-1 ring-white/10">
                        {{ $agendas->total() }} agenda
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 bg-white/15 rounded-lg text-[10px] sm:text-[11px] font-bold backdrop-blur-sm ring-1 ring-white/10">
                        {{ $selectedClass->name ?? 'Semua kelas' }}
                    </span>
                </div>
            </div>

            <a href="{{ route('wali-kelas.agenda.index') }}"
               class="shrink-0 w-11 h-11 sm:w-auto sm:px-4 sm:py-2.5 bg-white/15 rounded-2xl flex items-center justify-center gap-2 backdrop-blur-sm ring-1 ring-white/20 hover:bg-white/25 active:scale-95 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m0 0l6 6m-6-6l6-6"></path>
                </svg>
                <span class="hidden sm:inline text-xs font-black uppercase tracking-widest">Agenda</span>
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="-mx-4 sm:mx-0 px-4 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0">
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Arsip</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $agendas->total() }}</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-indigo-50/70 rounded-2xl border border-indigo-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-indigo-600/70 uppercase tracking-wider mb-1">Kelas Tampil</p>
                <p class="text-xl sm:text-2xl font-black text-indigo-600">{{ $visibleClassCount ?: $classes->count() }}</p>
            </div>
            <div class="snap-center shrink-0 w-33 sm:w-auto bg-emerald-50/70 rounded-2xl border border-emerald-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Guru Tampil</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $visibleTeacherCount }}</p>
            </div>
            <div class="snap-center shrink-0 w-37.5 sm:w-auto bg-amber-50/70 rounded-2xl border border-amber-100 p-3.5 sm:p-4">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Terbaru</p>
                <p class="text-sm sm:text-lg font-black text-amber-600 truncate">{{ $latestAgendaDate ? $latestAgendaDate->translatedFormat('d M Y') : '-' }}</p>
            </div>
        </div>
        <p class="sm:hidden mt-1 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">Geser untuk melihat ringkasan</p>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('wali-kelas.agenda.archive') }}"
          x-data="{ classOpen: false, selectedClass: @js($selectedClassId ?: '') }"
          @click.outside="classOpen = false"
          x-ref="archiveFilter"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-visible">
        <input type="hidden" name="class_id" :value="selectedClass">

        <div class="p-3 sm:p-4 space-y-3 sm:space-y-0 sm:grid sm:grid-cols-[1fr_auto] sm:gap-3 sm:items-center">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul agenda..."
                       class="block w-full pl-9 pr-4 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all text-sm font-semibold placeholder:text-gray-400">
            </div>

            <div class="grid grid-cols-[auto_1fr_auto] gap-2">
                <div class="relative">
                    <button type="button"
                            @click="classOpen = !classOpen"
                            class="h-11 inline-flex items-center gap-2 px-3.5 bg-gray-50 text-gray-700 rounded-xl border border-gray-100 font-black text-[11px] uppercase tracking-widest hover:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 active:scale-95 transition-all">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M6 12h12M10 17h4"></path>
                        </svg>
                        <span class="max-w-18.5 truncate sm:max-w-30">{{ $selectedClass->name ?? 'Kelas' }}</span>
                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform" :class="classOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="classOpen"
                         x-cloak
                         x-transition.origin.top.left
                         class="absolute left-0 top-full z-40 mt-2 w-56 max-h-56 overflow-y-auto rounded-2xl border border-gray-100 bg-white p-1.5 shadow-xl shadow-gray-200/70">
                        <button type="button"
                                @click="selectedClass = ''; classOpen = false; $nextTick(() => $refs.archiveFilter.submit())"
                                class="w-full flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left text-xs font-black transition-all {{ !$selectedClassId ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                            <span>Semua Kelas</span>
                            @if(!$selectedClassId)
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </button>
                        @foreach($classes as $class)
                            <button type="button"
                                    @click="selectedClass = @js((string) $class->id); classOpen = false; $nextTick(() => $refs.archiveFilter.submit())"
                                    class="mt-1 w-full flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left text-xs font-black transition-all {{ $selectedClassId == $class->id ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                                <span class="truncate">{{ $class->name }}</span>
                                @if($selectedClassId == $class->id)
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl font-black text-[11px] uppercase tracking-widest hover:bg-indigo-700 active:scale-95 transition-all shadow-sm shadow-indigo-100">
                    Filter
                </button>
                @if($hasFilter)
                    <a href="{{ route('wali-kelas.agenda.archive') }}" class="w-11 h-11 inline-flex items-center justify-center text-rose-500 bg-rose-50 rounded-xl hover:bg-rose-100 active:scale-95 transition-all border border-rose-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </div>

        @if($hasFilter)
            <div class="px-3 pb-3 sm:px-4">
                <div class="flex gap-2 overflow-x-auto hide-scrollbar">
                    @if($search)
                        <span class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-100 text-[10px] font-black">
                            "{{ $search }}"
                        </span>
                    @endif
                    @if($selectedClass)
                        <span class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-100 text-[10px] font-black">
                            {{ $selectedClass->name }}
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </form>

    {{-- Mobile List --}}
    <div class="lg:hidden space-y-3">
        @forelse($agendas as $agenda)
            @php
                $agendaDate = \Carbon\Carbon::parse($agenda->date);
                $isToday = $agendaDate->isToday();
                $isThisMonth = $agendaDate->isSameMonth(now());
            @endphp
            <a href="{{ route('wali-kelas.agenda.show', ['agenda' => $agenda->id, 'from' => 'archive']) }}"
               class="block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden active:scale-[0.99] transition-all">
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 {{ $isToday ? 'bg-indigo-600 shadow-lg shadow-indigo-100 text-white' : ($isThisMonth ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-500') }} rounded-2xl flex flex-col items-center justify-center shrink-0">
                            <span class="text-[7px] font-black uppercase leading-none opacity-70 mb-0.5">{{ $agendaDate->translatedFormat('M') }}</span>
                            <span class="text-sm font-black leading-none">{{ $agendaDate->format('d') }}</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-black text-gray-900 leading-snug line-clamp-2">{{ $agenda->title }}</p>
                                    <p class="mt-1 text-[11px] font-bold text-indigo-600 truncate">{{ $agenda->subject->name ?? 'Umum' }}</p>
                                </div>
                                <span class="shrink-0 px-2 py-1 bg-gray-50 text-gray-500 border border-gray-100 text-[9px] font-black uppercase tracking-widest rounded-lg">
                                    Arsip
                                </span>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 text-gray-600 rounded-lg border border-gray-100 text-[10px] font-bold">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $agendaDate->translatedFormat('d M Y') }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg border border-indigo-100 text-[10px] font-bold">
                                    {{ $agenda->class->name ?? '-' }}
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
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-gray-700 truncate">{{ $agenda->teacher->name ?? '-' }}</p>
                        <p class="text-[10px] font-bold text-gray-400 truncate">{{ $agendaDate->translatedFormat('l') }}</p>
                    </div>
                    <svg class="ml-auto w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center shadow-sm">
                <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-500">Arsip agenda tidak ditemukan</p>
                <p class="text-xs text-gray-400 mt-1">Coba ubah kelas atau kata kunci pencarian.</p>
                @if($hasFilter)
                    <a href="{{ route('wali-kelas.agenda.archive') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl text-[11px] font-black uppercase tracking-widest border border-indigo-100">
                        Reset Filter
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Desktop Table --}}
    <div class="hidden lg:block bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-black text-gray-900 tracking-tight uppercase">Daftar Arsip Agenda</h3>
            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100">{{ $agendas->total() }} Agenda</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kelas</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Agenda</th>
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
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100">{{ $agenda->class->name ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900 line-clamp-1">{{ $agenda->title }}</p>
                                <p class="mt-0.5 text-[10px] font-bold text-indigo-500 uppercase tracking-wider">{{ $agenda->subject->name ?? 'Umum' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-500">{{ $agenda->teacher->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('wali-kelas.agenda.show', ['agenda' => $agenda->id, 'from' => 'archive']) }}" class="inline-flex items-center px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">Arsip agenda tidak ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($agendas->hasPages())
        <div class="pt-1">
            {{ $agendas->appends(request()->query())->links() }}
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
