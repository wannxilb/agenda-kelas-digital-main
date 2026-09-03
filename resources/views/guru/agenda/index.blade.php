{{-- resources/views/guru/agenda/index.blade.php --}}
@extends('layouts.guru')

@section('title', 'Jurnal Mengajar')
@section('header', 'Jurnal Mengajar')
@section('content-padding', 'pt-4 sm:pt-6 pb-24 lg:pb-8')

@section('content')
@php
    $statusFilter = in_array($status ?? null, ['published', 'draft'], true) ? $status : null;
    $selectedAcademicYear = $academicYears->firstWhere('id', (int) ($academicYearId ?? request('academic_year_id')));
    $hasActiveFilters = request()->filled('search')
        || request()->filled('academic_year_id')
        || request()->filled('academic_year_name')
        || request()->filled('date')
        || $statusFilter;
@endphp
<div class="space-y-3 sm:space-y-5 pb-24 sm:pb-6"
     x-data="agendaCalendar(@js($agendaDates ?? []), @js(request('date', '')))"
     x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 12 })">

    {{-- Search & Filter Bar --}}
    <div class="relative z-30 -mx-3 sm:mx-0 px-3 sm:px-0 pb-1 sm:pb-0 transition-all duration-200"
         :class="scrolled ? 'bg-gray-50/90 backdrop-blur-md shadow-sm' : ''">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-3 mb-3" x-show="!scrolled" x-collapse.duration.150ms>
            <div>
                <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                    Kelola dan pantau catatan aktivitas belajar mengajar Anda.
                </p>
            </div>
            <a href="{{ route('guru.agenda.create') }}"
               class="hidden sm:inline-flex shrink-0 items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-linear-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/20 hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition-all uppercase tracking-widest">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Jurnal Baru
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sm:overflow-visible">
            <form method="GET" action="{{ route('guru.agenda.index') }}" id="filterForm" x-ref="filterForm" class="sm:relative">
                <input type="hidden" name="date" x-model="selectedDate">
                {{-- Search Row --}}
                <div class="p-3 sm:p-4 flex items-center gap-2">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari judul jurnal..."
                               class="block w-full pl-9 pr-8 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all text-sm">
                        @if(request('search'))
                            <button type="button"
                                    onclick="document.querySelector('input[name=search]').value=''; document.getElementById('filterForm').submit();"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        @endif
                    </div>
                    <button type="button" @click="showFilter = !showFilter"
                            class="relative shrink-0 w-10 h-10 flex items-center justify-center rounded-xl transition-all duration-200"
                            :class="showFilter ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-gray-50 text-gray-500 hover:bg-gray-100'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        @if(request()->filled('academic_year_id') || request()->filled('academic_year_name') || request()->filled('date') || $statusFilter)
                            <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                        @endif
                    </button>
                    <button type="submit"
                            class="shrink-0 w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-200/50 hover:bg-blue-700 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>

                {{-- Quick status chips --}}
                <div class="px-3 sm:px-4 pb-3 flex items-center gap-2 overflow-x-auto no-scrollbar">
                    @php
                        $chip = function($value) use ($statusFilter) {
                            $active = $value === 'all' ? !$statusFilter : $statusFilter === $value;
                            $base = 'shrink-0 px-3 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap transition-all border';
                            $on = 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200/50';
                            $off = 'bg-gray-50 text-gray-500 border-gray-100 hover:bg-gray-100';
                            return [$base . ' ' . ($active ? $on : $off)];
                        };
                    @endphp
                    <a href="{{ route('guru.agenda.index', request()->except(['page', 'status'])) }}"
                       class="{{ $chip('all')[0] }}">Semua</a>
                    <a href="{{ route('guru.agenda.index', array_merge(request()->except('page'), ['status' => 'published'])) }}"
                       class="{{ $chip('published')[0] }}">Published</a>
                    <a href="{{ route('guru.agenda.index', array_merge(request()->except('page'), ['status' => 'draft'])) }}"
                       class="{{ $chip('draft')[0] }}">Draft</a>
                </div>

                {{-- Expandable Filter Panel --}}
                <div x-show="showFilter" x-collapse x-cloak
                     class="sm:absolute sm:top-full sm:right-0 sm:mt-2 sm:w-[320px] border-t sm:border-t-0 sm:border sm:border-gray-200 bg-gray-50/50 sm:bg-white px-3 sm:px-4 py-3 space-y-3 sm:rounded-2xl sm:shadow-xl sm:z-50">
                    <div class="sm:hidden border-b border-gray-100 pb-2 mb-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider px-1">Pilih Tanggal</label>
                    </div>
                    <div class="bg-white sm:bg-transparent rounded-xl sm:rounded-none border border-gray-200 sm:border-0 p-2 sm:p-0">
                        <div class="flex items-center justify-between mb-2">
                            <button type="button" @click="prevMonth()" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 active:scale-95 transition-all">
                                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <p class="text-xs font-bold text-gray-800" x-text="monthLabel"></p>
                            <button type="button" @click="nextMonth()" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 active:scale-95 transition-all">
                                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-7 gap-0.5 mb-1">
                            @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $d)
                                <div class="text-center text-[8px] font-bold text-gray-400 uppercase py-0.5">{{ $d }}</div>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-0.5">
                            <template x-for="blank in blanks" :key="'fb-'+blank">
                                <div></div>
                            </template>
                            <template x-for="day in days" :key="'fd-'+day.date">
                                <button type="button"
                                        @click="selectDate(day.date)"
                                        class="relative aspect-square flex flex-col items-center justify-center rounded-lg text-[11px] font-bold transition-all duration-150"
                                        :class="{
                                            'bg-blue-600 text-white shadow-sm': selectedDate === day.date,
                                            'bg-blue-50 text-blue-700': day.isToday && selectedDate !== day.date,
                                            'text-gray-700 hover:bg-gray-50': !day.isToday && selectedDate !== day.date && !day.isPast,
                                            'text-gray-400 hover:bg-gray-50': day.isPast && selectedDate !== day.date && !day.isToday
                                        }">
                                    <span x-text="day.num"></span>
                                    <span x-show="day.hasAgenda && selectedDate !== day.date"
                                          class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-0.5 h-0.5 rounded-full bg-blue-500"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <div class="relative">
                        <select name="academic_year_id" onchange="this.form.submit()"
                                class="appearance-none w-full px-3 py-2 bg-white sm:bg-gray-50 border border-gray-200 sm:border-0 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-[13px] sm:text-sm text-gray-700 pr-8">
                            <option value="">Pilih Tahun Ajaran</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (string) ($academicYearId ?? request('academic_year_id')) === (string) $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    @if($hasActiveFilters)
                        <a href="{{ route('guru.agenda.index') }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reset Filter
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Active Filter Pills --}}
    @if($hasActiveFilters)
    <div class="flex flex-wrap items-center gap-2 px-0.5">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Filter aktif:</span>
        @if(request('search'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[11px] font-semibold">
                "{{ request('search') }}"
                <a href="{{ route('guru.agenda.index', request()->except('search')) }}" class="hover:text-blue-900">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </span>
        @endif
        @if(request('date'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[11px] font-semibold">
                {{ \Carbon\Carbon::parse(request('date'))->translatedFormat('d M Y') }}
                <a href="{{ route('guru.agenda.index', request()->except('date')) }}" class="hover:text-indigo-900">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </span>
        @endif
        @if(request('academic_year_id') || request('academic_year_name'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[11px] font-semibold">
                {{ $selectedAcademicYear?->name ?? request('academic_year_name') }}
                <a href="{{ route('guru.agenda.index', request()->except(['academic_year_id', 'academic_year_name'])) }}" class="hover:text-indigo-900">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </span>
        @endif
        @if($statusFilter)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-[11px] font-semibold">
                {{ $statusFilter == 'published' ? 'Published' : 'Draft' }}
                <a href="{{ route('guru.agenda.index', request()->except('status')) }}" class="hover:text-rose-900">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </span>
        @endif
    </div>
    @endif

    {{-- Journal Count --}}
    <div class="flex items-center justify-between px-0.5">
        <h3 class="text-sm font-bold text-gray-800">Daftar Jurnal</h3>
        <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-2.5 py-1 rounded-lg uppercase tracking-wider">{{ $agendas->total() }} jurnal</span>
    </div>

    {{-- Journal Feed --}}
    @if($agendas->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 sm:p-16 text-center">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-linear-to-br from-gray-50 to-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-gray-800">
                @if($hasActiveFilters)
                    Tidak Ada Hasil
                @else
                    Belum Ada Jurnal
                @endif
            </h3>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-xs mx-auto">
                @if($hasActiveFilters)
                    Coba ubah kata kunci atau hapus filter yang sedang aktif.
                @else
                    Mulai buat jurnal mengajar untuk mencatat aktivitas pembelajaran.
                @endif
            </p>
            @if($hasActiveFilters)
                <a href="{{ route('guru.agenda.index') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 active:scale-95 transition-all">
                    Reset Filter
                </a>
            @endif
        </div>
    @else
        <div class="space-y-6">
            @php
                $colors = [
                    'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-l-blue-500', 'badge' => 'bg-blue-50 text-blue-700'],
                    'indigo'  => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-l-indigo-500', 'badge' => 'bg-indigo-50 text-indigo-700'],
                    'violet'  => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'border' => 'border-l-violet-500', 'badge' => 'bg-violet-50 text-violet-700'],
                    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-l-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-700'],
                    'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-l-amber-500', 'badge' => 'bg-amber-50 text-amber-700'],
                    'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-l-rose-500', 'badge' => 'bg-rose-50 text-rose-700'],
                ];
                $colorKeys = array_keys($colors);
            @endphp

            @foreach($groupedAgendas as $date => $dailyAgendas)
                <div class="sticky top-26 sm:static z-20 -mx-3 sm:mx-0 px-3 sm:px-0 py-1.5 sm:py-0 bg-gray-50/95 sm:bg-transparent backdrop-blur-sm sm:backdrop-blur-none flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        @if($date === $todayStr)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-200/50">
                                <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                                Hari Ini
                            </span>
                        @elseif($date === $yesterdayStr)
                            <span class="inline-flex items-center px-3 py-1.5 bg-gray-200 text-gray-700 rounded-xl text-xs font-bold">
                                Kemarin
                            </span>
                        @elseif($date === '')
                            <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-500 rounded-xl text-xs font-bold">
                                Tanpa Tanggal
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-600 rounded-xl text-xs font-bold">
                                {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                            </span>
                        @endif
                    </div>
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-[10px] font-semibold text-gray-400">{{ count($dailyAgendas) }} jurnal</span>
                </div>

                <div class="space-y-3">
                    @foreach($dailyAgendas as $agenda)
                        @php
                            $subjectId = $agenda->subject_id ?? 0;
                            $colorKey = $colorKeys[$subjectId % count($colors)];
                            $c = $colors[$colorKey];
                        @endphp
                        <div class="agenda-card bg-white rounded-2xl shadow-sm border border-gray-100 border-l-[3px] {{ $c['border'] }} overflow-hidden active:scale-[0.98] hover:shadow-md transition-all duration-200 cursor-pointer group"
                             style="animation-delay: {{ min($loop->parent->index ?? 0, 8) * 40 + $loop->index * 40 }}ms"
                             onclick="previewAgenda({{ $agenda->id }})">
                            <div class="p-3.5 sm:p-5">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 {{ $c['badge'] }} rounded-md text-[10px] sm:text-[11px] font-bold uppercase tracking-wide shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $c['text'] }} bg-current opacity-60"></span>
                                            {{ $agenda->subject->name ?? 'Umum' }}
                                        </span>
                                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-widest rounded-md border {{ $agenda->status === 'published' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100' }} shrink-0">
                                            {{ $agenda->status === 'published' ? 'Published' : 'Draft' }}
                                        </span>
                                    </div>
                                    @if($agenda->attachments)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded-md text-[10px] font-semibold shrink-0">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>

                                <h3 class="text-sm sm:text-base font-bold text-gray-900 group-hover:text-blue-600 transition-colors leading-snug line-clamp-2">
                                    {{ $agenda->title }}
                                </h3>

                                @if($agenda->description)
                                    <p class="mt-1.5 text-xs sm:text-sm text-gray-500 line-clamp-2 leading-relaxed">
                                        {{ strip_tags($agenda->description) }}
                                    </p>
                                @endif

                                <div class="mt-3 flex min-w-0 items-center justify-between">
                                    <div class="flex flex-1 min-w-0 items-center gap-2 overflow-hidden">
                                        <span class="inline-flex min-w-0 max-w-[44%] items-center gap-1 text-[10px] sm:max-w-none sm:text-[11px] text-gray-400 font-medium">
                                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <span class="min-w-0 truncate">{{ $agenda->class->name ?? '-' }}</span>
                                        </span>
                                        @if($agenda->room)
                                            <span class="inline-flex min-w-0 max-w-[44%] items-center gap-1 text-[10px] sm:max-w-none sm:text-[11px] text-gray-400 font-medium">
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <span class="min-w-0 truncate">{{ $agenda->room }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="shrink-0 w-6 h-6 rounded-full bg-gray-50 group-hover:bg-blue-50 flex items-center justify-center transition-colors ml-2">
                                        <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($agendas->hasPages())
            <div class="flex justify-center pt-2">
                {{ $agendas->appends(request()->query())->links() }}
            </div>
        @endif
    @endif
</div>

{{-- Floating Action Button (mobile) --}}
<a href="{{ route('guru.agenda.create') }}"
   class="sm:hidden fixed bottom-24 right-5 z-40 w-14 h-14 flex items-center justify-center bg-linear-to-br from-blue-600 to-indigo-600 text-white rounded-2xl shadow-xl shadow-blue-500/30 active:scale-90 transition-transform"
   aria-label="Buat Jurnal">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
</a>

<button x-data="{ show: false }"
        x-init="window.addEventListener('scroll', () => { show = window.scrollY > 500 })"
        x-show="show" x-cloak
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        x-transition
        class="sm:hidden fixed bottom-40 right-5 z-40 w-10 h-10 flex items-center justify-center bg-white text-gray-500 rounded-xl shadow-lg border border-gray-100 active:scale-90 transition-transform"
        aria-label="Ke atas">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"></path></svg>
</button>

<div x-data="agendaModal()" x-cloak x-show="open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="close()"
     class="fixed inset-0 z-100">
    <div class="fixed inset-0 bg-black/40" @click="close()"></div>
    <div class="fixed inset-x-0 bottom-0 sm:inset-0 sm:flex sm:items-center sm:justify-center sm:p-4 z-10">
        <div class="bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl w-full sm:max-w-lg max-h-[88vh] sm:max-h-[85vh] overflow-hidden flex flex-col"
             @click.outside="close()"
             @touchstart.passive="touchStart($event)"
             @touchmove.passive="touchMove($event)"
             @touchend.passive="touchEnd($event)">
            <div class="sm:hidden flex justify-center pt-3 pb-1 cursor-grab active:cursor-grabbing">
                <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
            </div>
            <div class="relative bg-linear-to-br from-blue-600 via-indigo-600 to-indigo-700 px-5 sm:px-6 py-5 sm:py-6 overflow-hidden shrink-0">
                <button @click="close()" class="absolute top-3 right-3 w-9 h-9 flex items-center justify-center bg-white/20 hover:bg-white/30 text-white rounded-xl active:scale-90 transition-all z-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <template x-if="loading">
                    <div class="space-y-2.5 animate-pulse">
                        <div class="h-4 w-20 bg-white/20 rounded-md"></div>
                        <div class="h-6 w-3/4 bg-white/20 rounded-md"></div>
                    </div>
                </template>
                <template x-if="!loading">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 bg-white/15 rounded-lg text-[10px] font-bold uppercase tracking-wider text-white/90 ring-1 ring-white/10 mb-2" x-text="agenda.subject_name || 'Umum'"></span>
                        <h3 class="text-lg sm:text-xl font-bold text-white leading-snug pr-10" x-text="agenda.title"></h3>
                    </div>
                </template>
            </div>
            <div class="flex-1 overflow-y-auto min-h-0">
                <div class="px-5 sm:px-6 py-5 space-y-5">
                    <template x-if="loading">
                        <div class="space-y-5 animate-pulse">
                            <div class="grid grid-cols-2 gap-3">
                                <template x-for="i in 4" :key="i">
                                    <div class="p-3 bg-gray-50 rounded-xl">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 bg-gray-200 rounded-lg shrink-0"></div>
                                            <div class="flex-1 space-y-1.5">
                                                <div class="h-2 w-10 bg-gray-200 rounded"></div>
                                                <div class="h-3 w-16 bg-gray-200 rounded"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                                <div class="h-3 w-full bg-gray-200 rounded"></div>
                                <div class="h-3 w-5/6 bg-gray-200 rounded"></div>
                                <div class="h-3 w-4/6 bg-gray-200 rounded"></div>
                            </div>
                        </div>
                    </template>
                    <template x-if="!loading">
                        <div class="space-y-5">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="flex items-center gap-2.5 p-3 bg-gray-50 rounded-xl">
                                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">Tanggal</p>
                                        <p class="text-xs font-bold text-gray-800 truncate" x-text="agenda.date"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5 p-3 bg-gray-50 rounded-xl">
                                    <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">Guru</p>
                                        <p class="text-xs font-bold text-gray-800 truncate" x-text="agenda.teacher_name"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5 p-3 bg-gray-50 rounded-xl">
                                    <div class="w-8 h-8 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">Kelas</p>
                                        <p class="text-xs font-bold text-gray-800 truncate" x-text="agenda.class_name"></p>
                                    </div>
                                </div>
                                <div x-show="agenda.room" class="flex items-center gap-2.5 p-3 bg-gray-50 rounded-xl">
                                    <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">Ruangan</p>
                                        <p class="text-xs font-bold text-gray-800 truncate" x-text="agenda.room"></p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase mb-2 tracking-widest">Deskripsi / Materi</p>
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap wrap-break-word" x-text="agenda.description"></div>
                                </div>
                            </div>
                            <div x-show="agenda.attachments">
                                <a :href="agenda.attachments" target="_blank" class="flex items-center gap-3 p-3.5 bg-blue-50 hover:bg-blue-100 rounded-xl border border-blue-100 transition-colors group">
                                    <div class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-blue-200/50 shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-900">Unduh Lampiran</p>
                                        <p class="text-[11px] text-blue-600 font-medium">Tap untuk membuka file</p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-300 group-hover:text-blue-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            </div>
                            <div x-show="error" class="text-center py-4">
                                <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-700">Gagal memuat data</p>
                                <p class="text-xs text-gray-500 mt-0.5">Silakan coba lagi nanti</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="border-t border-gray-200 bg-gray-50 px-5 sm:px-6 py-5 pb-[calc(env(safe-area-inset-bottom)+16px)] shrink-0">
                <div class="flex flex-row items-center gap-3">
                    <a :href="agenda.edit_url" x-show="agenda.edit_url"
                       class="flex-1 inline-flex items-center justify-center gap-2 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition-colors active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit
                    </a>
                    <button @click="close()" class="flex-1 inline-flex items-center justify-center py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-bold transition-colors active:scale-[0.98]">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@push('scripts')
<script>
    function agendaCalendar(agendaDates, initialDate) {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const today = new Date();
        const fmt = (y, m, d) => `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());

        return {
            currentMonth: initialDate ? new Date(initialDate + 'T00:00:00').getMonth() : today.getMonth(),
            currentYear: initialDate ? new Date(initialDate + 'T00:00:00').getFullYear() : today.getFullYear(),
            selectedDate: initialDate || '',
            scrolled: false,
            showFilter: false,
            agendaDates: agendaDates,

            get monthLabel() {
                return months[this.currentMonth] + ' ' + this.currentYear;
            },

            get blanks() {
                const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
                return Array.from({ length: firstDay }, (_, i) => i);
            },

            get days() {
                const daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                const result = [];

                for (let d = 1; d <= daysInMonth; d++) {
                    const date = new Date(this.currentYear, this.currentMonth, d);
                    const dateStr = fmt(this.currentYear, this.currentMonth, d);
                    const isToday = date.getTime() === todayMidnight.getTime();
                    const isPast = date < todayMidnight;

                    result.push({
                        num: d,
                        date: dateStr,
                        isToday,
                        isPast,
                        hasAgenda: this.agendaDates.includes(dateStr)
                    });
                }
                return result;
            },

            prevMonth() {
                if (this.currentMonth === 0) {
                    this.currentMonth = 11;
                    this.currentYear--;
                } else {
                    this.currentMonth--;
                }
            },

            nextMonth() {
                if (this.currentMonth === 11) {
                    this.currentMonth = 0;
                    this.currentYear++;
                } else {
                    this.currentMonth++;
                }
            },

            selectDate(dateStr) {
                if (this.selectedDate === dateStr) return;
                this.selectedDate = dateStr;
                document.getElementById('filterForm').submit();
            },
        };
    }

    function agendaModal() {
        return {
            open: false,
            loading: false,
            error: false,
            startY: 0,
            currentY: 0,
            dragOffset: 0,
            agenda: {
                title: '',
                subject_name: '',
                date: '',
                teacher_name: '',
                class_name: '',
                room: null,
                description: '',
                attachments: null,
                status: '',
                edit_url: '',
            },

            fetchAndOpen(id) {
                this.open = true;
                this.loading = true;
                this.error = false;
                document.body.style.overflow = 'hidden';

                fetch(`{{ url('guru/agenda') }}/${id}/preview`)
                    .then(r => {
                        if (!r.ok) throw new Error('Request failed');
                        return r.json();
                    })
                    .then(data => {
                        this.agenda = {
                            title: data.title || '',
                            subject_name: data.subject_name || '',
                            date: data.date || '',
                            teacher_name: data.teacher_name || '',
                            class_name: data.class_name || '',
                            room: data.room || null,
                            description: data.description || '',
                            attachments: data.attachments || null,
                            status: data.status || '',
                            edit_url: data.edit_url || '',
                        };
                        this.loading = false;
                    })
                    .catch(() => {
                        this.loading = false;
                        this.error = true;
                    });
            },

            close() {
                this.open = false;
                this.dragOffset = 0;
                document.body.style.overflow = '';
            },

            touchStart(e) {
                this.startY = e.touches[0].clientY;
            },

            touchMove(e) {
                this.currentY = e.touches[0].clientY;
                const diff = this.currentY - this.startY;
                this.dragOffset = diff > 0 ? diff : 0;
            },

            touchEnd() {
                const diff = this.currentY - this.startY;
                if (diff > 100) {
                    this.close();
                } else {
                    this.dragOffset = 0;
                }
                this.startY = 0;
                this.currentY = 0;
            }
        };
    }

    function previewAgenda(id) {
        const el = document.querySelector('[x-data*="agendaModal"]');
        if (el && el._x_dataStack) {
            Alpine.evaluate(el, `fetchAndOpen(${id})`);
        }
    }
</script>
@endpush
@endsection
