@extends('layouts.wakasek')

@section('title', 'Kinerja Pengajaran Guru')
@section('header', 'Kinerja Guru')

@php
    $maxAgenda = $teachers->max('agendas_count');
@endphp

@push('styles')
<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .anim-card { animation: fadeSlideUp 0.4s ease-out both; }
    .anim-card:nth-child(1) { animation-delay: 0.05s; }
    .anim-card:nth-child(2) { animation-delay: 0.1s; }
    .anim-card:nth-child(3) { animation-delay: 0.15s; }
    .anim-card:nth-child(4) { animation-delay: 0.2s; }

    .teaching-subject-filter .ts-control {
        min-height: 2.5rem;
        padding: 0.55rem 0.7rem;
        border: 0;
        border-radius: 0.75rem;
        background: rgb(249 250 251);
        box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);
        font-size: 0.75rem;
        font-weight: 600;
        color: rgb(55 65 81);
    }
    .teaching-subject-filter .ts-control.focus {
        background: #fff;
        box-shadow: 0 0 0 4px rgb(99 102 241 / 0.10), inset 0 2px 4px 0 rgb(0 0 0 / 0.04);
    }
    .teaching-subject-filter .ts-dropdown {
        max-height: 14rem;
        border-radius: 0.875rem;
        border-color: rgb(229 231 235);
        box-shadow: 0 18px 40px rgb(15 23 42 / 0.12);
        font-size: 0.75rem;
    }
    .teaching-subject-filter .ts-dropdown-content {
        max-height: 13rem;
        overflow-y: auto;
        padding: 0.35rem;
    }
    .teaching-subject-filter .ts-dropdown .option {
        border-radius: 0.625rem;
        padding: 0.55rem 0.7rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6 pb-28 lg:pb-8">
    <!-- Header Card -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-100 shadow-sm relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight">Kinerja Guru</h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">Pantau keaktifan tenaga pendidik secara real-time</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div x-data="teachingFilter()">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-visible">
            <form method="GET" action="{{ route('wakasek.teaching.index') }}" id="filterForm">

                {{-- Search Row --}}
                <div class="p-3 sm:p-4 flex items-center gap-2">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Cari nama atau NIP..."
                               class="block w-full pl-9 pr-8 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-semibold text-gray-700 placeholder:text-gray-400">
                        @if(request('q'))
                            <button type="button"
                                    onclick="document.querySelector('input[name=q]').value=''; document.getElementById('filterForm').requestSubmit();"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        @endif
                    </div>
                    <button type="button" @click="showFilter = !showFilter"
                            class="relative shrink-0 w-10 h-10 flex items-center justify-center rounded-xl transition-all duration-200 border"
                            :class="showFilter ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-200' : 'bg-gray-50 text-gray-500 border-gray-100 hover:bg-gray-100'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        @if(request('subject') || request('status'))
                            <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                        @endif
                    </button>
                    <button type="submit"
                            class="shrink-0 w-10 h-10 flex items-center justify-center bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-200/50 hover:bg-indigo-700 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>

                {{-- Quick Status Chips --}}
                <div class="px-3 sm:px-4 pb-3 flex items-center gap-2 overflow-x-auto hide-scrollbar">
                    @php
                        $chip = function($value, $label) {
                            $active = request('status') === $value || ($value === '' && !request('status'));
                            $base = 'shrink-0 px-3 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap transition-all border';
                            $on = 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-200/50';
                            $off = 'bg-gray-50 text-gray-500 border-gray-100 hover:bg-gray-100';
                            return $base . ' ' . ($active ? $on : $off);
                        };
                    @endphp
                    <a href="{{ route('wakasek.teaching.index', request()->except(['status', 'page'])) }}"
                       class="{{ $chip('', 'Semua') }}">Semua</a>
                    <a href="{{ route('wakasek.teaching.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                       class="{{ $chip('active', 'Aktif') }}">
                        <span class="w-1.5 h-1.5 rounded-full inline-block mr-1 bg-emerald-500"></span>
                        Aktif
                    </a>
                    <a href="{{ route('wakasek.teaching.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                       class="{{ $chip('inactive', 'Tak Aktif') }}">
                        <span class="w-1.5 h-1.5 rounded-full inline-block mr-1 bg-gray-400"></span>
                        Tak Aktif
                    </a>
                </div>

                {{-- Expandable Filter Panel --}}
                <div x-show="showFilter" x-collapse x-cloak
                     class="border-t border-gray-100 bg-gray-50/50 px-3 sm:px-4 py-3 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                        <div class="flex-1 teaching-subject-filter">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Mata Pelajaran</label>
                            <select id="subject-filter" name="subject"
                                    class="w-full bg-gray-50 border-0 rounded-xl text-xs font-semibold text-gray-700 shadow-inner placeholder:text-gray-400">
                                <option value="" disabled {{ request('subject') ? '' : 'selected' }}></option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(request('q') || request('subject') || request('status'))
                            <a href="{{ route('wakasek.teaching.index') }}"
                               class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl transition-colors whitespace-nowrap">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reset Filter
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Active Filter Pills --}}
    @if(request('q') || request('subject') || request('status'))
    <div class="flex flex-wrap items-center gap-2 px-0.5">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Filter aktif:</span>
        @if(request('q'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[11px] font-semibold">
                "{{ request('q') }}"
                <a href="{{ route('wakasek.teaching.index', request()->except(['q', 'page'])) }}" class="hover:text-blue-900 ml-0.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </span>
        @endif
        @if(request('subject'))
            @php $subjectName = optional($subjects->firstWhere('id', request('subject')))->name ?? 'Unknown'; @endphp
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[11px] font-semibold">
                {{ $subjectName }}
                <a href="{{ route('wakasek.teaching.index', request()->except(['subject', 'page'])) }}" class="hover:text-indigo-900 ml-0.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </span>
        @endif
        @if(request('status'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[11px] font-semibold">
                {{ request('status') === 'active' ? 'Aktif' : 'Tak Aktif' }}
                <a href="{{ route('wakasek.teaching.index', request()->except(['status', 'page'])) }}" class="hover:text-emerald-900 ml-0.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </span>
        @endif
    </div>
    @endif

    <!-- Stats Summary -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="anim-card bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-indigo-400 transition-all duration-300">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Guru</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
            </div>
            <p class="text-xl sm:text-3xl font-black text-gray-900">{{ $teachers->count() }}</p>
            <p class="mt-1.5 text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Tenaga Pendidik</p>
        </div>
        <div class="anim-card bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-indigo-400 transition-all duration-300">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Jurnal</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
            </div>
            <p class="text-xl sm:text-3xl font-black text-indigo-600">{{ $totalJournals }}</p>
            <p class="mt-1.5 text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catatan Aktivitas</p>
        </div>
        <div class="anim-card bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-emerald-400 transition-all duration-300">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Guru Aktif</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <p class="text-xl sm:text-3xl font-black text-emerald-600">{{ $activeTeachers }}</p>
            <div class="mt-2 flex items-center gap-2">
                <div class="flex-1 bg-gray-100 rounded-full h-1.5 sm:h-2 overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $teachers->count() > 0 ? round(($activeTeachers / $teachers->count()) * 100) : 0 }}%"></div>
                </div>
                <span class="text-[9px] sm:text-[10px] font-bold text-gray-500">{{ $teachers->count() > 0 ? round(($activeTeachers / $teachers->count()) * 100) : 0 }}%</span>
            </div>
        </div>
        <div class="anim-card bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm group hover:border-indigo-400 transition-all duration-300">
            <div class="flex items-center justify-between mb-1">
                <p class="text-[9px] sm:text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Rata-rata</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <p class="text-xl sm:text-3xl font-black text-gray-900">{{ $avgPerTeacher }}</p>
            <p class="mt-1.5 text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider">Per Guru Aktif</p>
        </div>
    </div>

    <!-- Content Display -->
    <div class="space-y-3 sm:space-y-4">
        {{-- Desktop View (Table) --}}
        <div class="hidden lg:block bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100">
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Guru</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mata Pelajaran</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Jurnal</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Jadwal</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-center">Aktivitas</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($teachers as $teacher)
                            @php
                                $compliance = $teacher->teachingSchedules->count() > 0
                                    ? min(100, round(($teacher->agendas_count / max(1, $teacher->teachingSchedules->count())) * 100))
                                    : 0;
                            @endphp
                            <tr class="group hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-xl flex items-center justify-center text-xs font-black shadow-lg shadow-indigo-200 group-hover:scale-110 transition-transform">
                                            {{ strtoupper(substr($teacher->name, 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors truncate">{{ $teacher->name }}</span>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mt-0.5">NIP: {{ $teacher->nip ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-1.5 max-w-[200px]">
                                        @forelse($teacher->subjects->take(3) as $subject)
                                            <span class="inline-flex items-center px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-[10px] font-bold border border-indigo-100">{{ $subject->name }}</span>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">-</span>
                                        @endforelse
                                        @if($teacher->subjects->count() > 3)
                                            <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md text-[10px] font-bold">+{{ $teacher->subjects->count() - 3 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-sm font-black {{ $teacher->agendas_count > 0 ? 'text-indigo-600' : 'text-gray-300' }}">{{ $teacher->agendas_count }}</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-sm font-bold text-gray-600">{{ $teacher->teachingSchedules->count() }}</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($teacher->agendas_count > 0)
                                        <div class="inline-flex flex-col items-center gap-1">
                                            <div class="w-24 bg-gray-100 rounded-full h-2 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-700 {{ $compliance >= 70 ? 'bg-emerald-500' : ($compliance >= 30 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $compliance }}%"></div>
                                            </div>
                                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-wider">{{ $compliance }}%</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] font-bold text-gray-300 uppercase tracking-wider">Belum ada</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('wakasek.teaching.show', $teacher->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-500">Data guru tidak ditemukan</p>
                                            <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci pencarian yang berbeda</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile View (Cards) --}}
        <div class="lg:hidden space-y-3">
            @forelse($teachers as $teacher)
                @php
                    $compliance = $teacher->teachingSchedules->count() > 0
                        ? min(100, round(($teacher->agendas_count / max(1, $teacher->teachingSchedules->count())) * 100))
                        : 0;
                    $complianceColor = $compliance >= 70 ? 'emerald' : ($compliance >= 30 ? 'amber' : 'rose');
                    $accentBorder = $compliance > 0 ? ($compliance >= 70 ? 'border-l-emerald-500' : ($compliance >= 30 ? 'border-l-amber-400' : 'border-l-rose-500')) : 'border-l-gray-300';
                    $progressBg = $compliance >= 70 ? 'from-emerald-500 to-emerald-600' : ($compliance >= 30 ? 'from-amber-400 to-amber-600' : 'from-rose-500 to-rose-600');
                @endphp
                <a href="{{ route('wakasek.teaching.show', $teacher->id) }}"
                   class="block bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden border-l-4 {{ $accentBorder }} group active:scale-[0.98] transition-all duration-200">
                    <div class="px-4 py-3.5">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-xl flex items-center justify-center text-xs font-black shadow-lg shadow-indigo-200 shrink-0">
                                {{ strtoupper(substr($teacher->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-gray-900 group-hover:text-indigo-600 transition-colors truncate">{{ $teacher->name }}</p>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mt-0.5">NIP: {{ $teacher->nip ?? '-' }}</p>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <span class="text-lg font-black {{ $teacher->agendas_count > 0 ? 'text-indigo-600' : 'text-gray-300' }}">{{ $teacher->agendas_count }}</span>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </div>

                                @if($teacher->subjects->count() > 0)
                                <div class="flex flex-wrap gap-1 mt-2.5">
                                    @foreach($teacher->subjects->take(3) as $subject)
                                        <span class="inline-flex items-center px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-[8px] sm:text-[9px] font-bold border border-indigo-100">{{ $subject->name }}</span>
                                    @endforeach
                                    @if($teacher->subjects->count() > 3)
                                        <span class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-500 rounded-md text-[8px] sm:text-[9px] font-bold">+{{ $teacher->subjects->count() - 3 }}</span>
                                    @endif
                                </div>
                                @endif

                                <div class="mt-3 grid grid-cols-2 gap-2">
                                    <div class="rounded-lg bg-emerald-50/60 border border-emerald-100/50 px-2.5 py-1.5 flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-md bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-black text-emerald-600/70 uppercase tracking-wider leading-none">Jurnal</p>
                                            <p class="text-xs font-black text-emerald-700 leading-none mt-0.5">{{ $teacher->agendas_count }}</p>
                                        </div>
                                    </div>
                                    <div class="rounded-lg bg-sky-50/60 border border-sky-100/50 px-2.5 py-1.5 flex items-center gap-2">
                                        <div class="w-5 h-5 rounded-md bg-sky-100 flex items-center justify-center text-sky-600 shrink-0">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-black text-sky-600/70 uppercase tracking-wider leading-none">Jadwal</p>
                                            <p class="text-xs font-black text-sky-700 leading-none mt-0.5">{{ $teacher->teachingSchedules->count() }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if($teacher->teachingSchedules->count() > 0)
                                <div class="mt-2.5 flex items-center gap-2">
                                    <div class="h-2 flex-1 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-linear-to-r {{ $progressBg }}" style="width: {{ $compliance }}%"></div>
                                    </div>
                                    <span class="text-[9px] font-black {{ $compliance >= 70 ? 'text-emerald-600' : ($compliance >= 30 ? 'text-amber-600' : 'text-rose-600') }}">{{ $compliance }}%</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white p-10 sm:p-12 rounded-2xl border border-gray-100 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-500">Data guru tidak ditemukan</p>
                            <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci pencarian yang berbeda</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function teachingFilter() {
    return {
        showFilter: {{ (request('subject') || request('status')) ? 'true' : 'false' }},
        tomInstance: null,

        init() {
            this.$watch('showFilter', (val) => {
                if (val) {
                    this.$nextTick(() => this.initSubjectSelect());
                } else {
                    this.destroySubjectSelect();
                }
            });
            if (this.showFilter) {
                this.$nextTick(() => this.initSubjectSelect());
            }
        },

        initSubjectSelect() {
            const el = document.getElementById('subject-filter');
            if (!el || this.tomInstance || typeof TomSelect === 'undefined') return;
            this.tomInstance = new TomSelect(el, {
                maxOptions: null,
                allowEmptyOption: false,
                placeholder: 'Semua Mapel',
                onChange: () => {
                    el.closest('form')?.submit();
                },
                render: {
                    no_results: function() {
                        return '<div class="no-results px-3 py-2 text-xs font-semibold text-gray-400">Mata pelajaran tidak ditemukan</div>';
                    },
                },
            });
        },

        destroySubjectSelect() {
            if (this.tomInstance) {
                this.tomInstance.destroy();
                this.tomInstance = null;
            }
        }
    };
}
</script>
@endpush
@endsection
