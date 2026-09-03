{{-- resources/views/guru/agenda/archive.blade.php --}}
@extends('layouts.guru')

@section('title', 'Arsip Jurnal')
@section('header', 'Arsip Jurnal')

@section('content')
@php $hasFilter = request()->anyFilled(['search', 'class_id']); @endphp
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-6">

    {{-- Hero Header --}}
    <div class="bg-linear-to-br from-blue-600 to-indigo-600 rounded-2xl p-5 sm:p-7 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-1/4 w-32 h-32 bg-white/5 rounded-full blur-xl"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">Arsip Jurnal</h2>
                    <p class="text-[11px] text-blue-100">Jelajahi kembali catatan kegiatan belajar mengajar.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="-mx-3 sm:mx-0 px-3 sm:px-0 pb-1"
         x-data="{ showFilter: @js($hasFilter) }">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form method="GET" action="{{ route('guru.agenda.archive') }}" id="archiveFilterForm" class="sm:relative">
                {{-- Search row --}}
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
                                    onclick="document.querySelector('input[name=search]').value=''; document.getElementById('archiveFilterForm').submit();"
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
                        @if($hasFilter)
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

                {{-- Expandable filter panel (inside main form) --}}
                <div x-show="showFilter" x-collapse x-cloak
                     class="sm:absolute sm:top-full sm:right-0 sm:mt-2 sm:w-56 border-t sm:border-t-0 bg-gray-50/50 sm:bg-white px-3 sm:px-4 py-2.5 sm:py-3 sm:rounded-2xl sm:shadow-xl sm:border sm:border-gray-200 sm:z-50">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <select name="class_id" onchange="this.form.submit()"
                                class="appearance-none w-full pl-9 pr-8 py-2 bg-white sm:bg-gray-50 border border-gray-200 sm:border-0 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all text-[13px] sm:text-sm text-gray-700">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <div class="sm:hidden flex gap-2 mt-2">
                        <button type="submit" class="flex-1 px-3 py-1.5 text-[11px] font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 active:scale-95 transition-all">Terapkan</button>
                        @if($hasFilter)
                            <a href="{{ route('guru.agenda.archive') }}" class="flex-1 px-3 py-1.5 text-[11px] font-bold text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 active:scale-95 transition-all text-center">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Active Filter Pills --}}
    @if($hasFilter)
    <div class="flex flex-wrap items-center gap-2 px-0.5">
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Filter aktif:</span>
        @if(request('search'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[11px] font-semibold">
                "{{ request('search') }}"
                <a href="{{ route('guru.agenda.archive', request()->except('search')) }}" class="hover:text-blue-900">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </span>
        @endif
        @if(request('class_id'))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[11px] font-semibold">
                {{ $classes->firstWhere('id', request('class_id'))?->name ?? request('class_id') }}
                <a href="{{ route('guru.agenda.archive', request()->except('class_id')) }}" class="hover:text-indigo-900">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </a>
            </span>
        @endif
    </div>
    @endif

    {{-- Count --}}
    <div class="flex items-center justify-between px-0.5">
        <h3 class="text-sm font-bold text-gray-800">Daftar Arsip</h3>
        <span class="text-[10px] font-bold text-gray-400 bg-gray-100 px-2.5 py-1 rounded-lg uppercase tracking-wider">{{ $agendas->total() }} jurnal</span>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden lg:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-50">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kelas</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mata Pelajaran</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Judul</th>
                    <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Guru</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50">
                @forelse($agendas as $agenda)
                @php
                    $colorKeys = ['blue','indigo','violet','emerald','amber','rose'];
                    $colors = [
                        'blue'    => ['badge' => 'bg-blue-50 text-blue-700', 'dot' => 'bg-blue-500'],
                        'indigo'  => ['badge' => 'bg-indigo-50 text-indigo-700', 'dot' => 'bg-indigo-500'],
                        'violet'  => ['badge' => 'bg-violet-50 text-violet-700', 'dot' => 'bg-violet-500'],
                        'emerald' => ['badge' => 'bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500'],
                        'amber'   => ['badge' => 'bg-amber-50 text-amber-700', 'dot' => 'bg-amber-500'],
                        'rose'    => ['badge' => 'bg-rose-50 text-rose-700', 'dot' => 'bg-rose-500'],
                    ];
                    $c = $colors[$colorKeys[($agenda->subject_id ?? 0) % count($colorKeys)]];
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors cursor-pointer" onclick="window.location='{{ route('guru.agenda.show', ['agenda' => $agenda->id, 'from' => 'archive']) }}'">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-xl flex flex-col items-center justify-center shadow-sm shrink-0">
                                <span class="text-[7px] font-black uppercase leading-none opacity-60">{{ $agenda->date->translatedFormat('M') }}</span>
                                <span class="text-xs font-black leading-none">{{ $agenda->date->format('d') }}</span>
                            </div>
                            <span class="text-xs font-bold text-gray-700">{{ $agenda->date->translatedFormat('d M Y') }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-50 text-blue-700 rounded-md">{{ $agenda->class->name }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 {{ $c['badge'] }} rounded-md text-[10px] font-bold">
                            <span class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></span>
                            {{ $agenda->subject->name ?? 'Umum' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $agenda->title }}</td>
                    <td class="px-6 py-4 text-xs text-gray-500 font-medium">{{ $agenda->teacher->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-gray-400">Belum ada arsip</p>
                            <p class="text-[10px] text-gray-300 mt-0.5">Arsip jurnal akan muncul di sini</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="lg:hidden space-y-2.5">
        @forelse($agendas as $agenda)
        @php
            $colorKeys = ['blue','indigo','violet','emerald','amber','rose'];
            $colors = [
                'blue'    => ['badge' => 'bg-blue-50 text-blue-700', 'dot' => 'bg-blue-500'],
                'indigo'  => ['badge' => 'bg-indigo-50 text-indigo-700', 'dot' => 'bg-indigo-500'],
                'violet'  => ['badge' => 'bg-violet-50 text-violet-700', 'dot' => 'bg-violet-500'],
                'emerald' => ['badge' => 'bg-emerald-50 text-emerald-700', 'dot' => 'bg-emerald-500'],
                'amber'   => ['badge' => 'bg-amber-50 text-amber-700', 'dot' => 'bg-amber-500'],
                'rose'    => ['badge' => 'bg-rose-50 text-rose-700', 'dot' => 'bg-rose-500'],
            ];
            $c = $colors[$colorKeys[($agenda->subject_id ?? 0) % count($colorKeys)]];
        @endphp
        <a href="{{ route('guru.agenda.show', ['agenda' => $agenda->id, 'from' => 'archive']) }}"
           class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-4 active:scale-[0.99] transition-all hover:shadow-md">
            <div class="flex items-start gap-3">
                {{-- Date Avatar --}}
                <div class="w-11 h-11 bg-linear-to-br from-indigo-500 to-blue-600 text-white rounded-xl flex flex-col items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                    <span class="text-[7px] font-black uppercase leading-none opacity-60 mb-0.5">{{ $agenda->date->translatedFormat('M') }}</span>
                    <span class="text-sm font-black leading-none">{{ $agenda->date->format('d') }}</span>
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1">
                    <h4 class="text-sm font-bold text-gray-900 leading-snug line-clamp-2">{{ $agenda->title }}</h4>

                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 {{ $c['badge'] }} rounded-md text-[9px] font-bold">
                            <span class="w-1 h-1 rounded-full {{ $c['dot'] }}"></span>
                            {{ $agenda->subject->name ?? 'Umum' }}
                        </span>
                        <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded-md text-[9px] font-bold">{{ $agenda->class->name }}</span>
                    </div>

                    <div class="flex items-center gap-2 mt-2">
                        <span class="inline-flex items-center gap-1 text-[10px] text-gray-400 font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ $agenda->teacher->name ?? '-' }}
                        </span>
                    </div>
                </div>

                {{-- Arrow --}}
                <svg class="w-4 h-4 text-gray-300 mt-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </a>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-800">
                @if($hasFilter) Tidak Ada Hasil @else Belum Ada Arsip @endif
            </h3>
            <p class="text-xs text-gray-400 mt-1 max-w-xs mx-auto">
                @if($hasFilter) Coba ubah kata kunci atau hapus filter. @else Arsip jurnal akan muncul di sini. @endif
            </p>
            @if($hasFilter)
                <a href="{{ route('guru.agenda.archive') }}" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 active:scale-95 transition-all">Reset Filter</a>
            @endif
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($agendas->hasPages())
    <div class="flex justify-center pt-2">
        {{ $agendas->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
