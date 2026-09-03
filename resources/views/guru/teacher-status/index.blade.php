{{-- resources/views/guru/teacher-status/index.blade.php --}}
@extends('layouts.guru')

@section('title', 'Izin / Sakit / Tugas Luar')
@section('header', 'Izin / Sakit / Tugas Luar')

@section('content')
<div class="space-y-4 sm:space-y-6 pb-24 sm:pb-6">
    {{-- Hero Header --}}
    <div class="bg-linear-to-br from-rose-500 to-pink-600 rounded-2xl p-5 sm:p-7 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 left-1/4 w-32 h-32 bg-white/5 rounded-full blur-xl"></div>
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Izin / Sakit / Tugas Luar</h2>
                <p class="text-[11px] text-pink-100">Ajukan izin, sakit, atau dinas luar, tunggu persetujuan wakasek</p>
            </div>
        </div>
    </div>

    @php
        $hasFilter = request()->anyFilled(['search', 'type', 'status', 'month', 'year']);
        $badge = [
            'pending' => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500', 'Menunggu'],
            'approved' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500', 'Disetujui'],
            'rejected' => ['bg-rose-50 text-rose-700 border-rose-200', 'bg-rose-500', 'Ditolak'],
            'cancelled' => ['bg-gray-100 text-gray-500 border-gray-200', 'bg-gray-400', 'Dibatalkan'],
        ];
        $typeBadge = [
            'izin' => ['bg-amber-50 text-amber-600 border-amber-100/50', 'bg-amber-500', 'Izin', 'bg-amber-50', 'text-amber-600'],
            'sakit' => ['bg-orange-50 text-orange-600 border-orange-100/50', 'bg-orange-500', 'Sakit', 'bg-orange-50', 'text-orange-600'],
            'tugas_luar' => ['bg-blue-50 text-blue-600 border-blue-100/50', 'bg-blue-500', 'Tugas Luar', 'bg-blue-50', 'text-blue-600'],
        ];
    @endphp

    {{-- Search & Filter Bar --}}
    <div class="-mx-3 sm:mx-0 px-3 sm:px-0 pb-1"
         x-data="{ showFilter: {{ $hasFilter ? 'true' : 'false' }}, search: '{{ request('search', '') }}' }">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form method="GET" action="{{ route('guru.teacher-status.index') }}" id="filterForm" class="sm:relative">
                <div class="p-3 sm:p-4 flex items-center gap-2">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari tanggal atau keterangan..."
                               class="block w-full pl-9 pr-8 py-2.5 bg-gray-50 border-0 rounded-xl focus:bg-white focus:ring-2 focus:ring-rose-500 transition-all text-sm">
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
                            :class="showFilter ? 'bg-rose-600 text-white shadow-lg shadow-rose-200' : 'bg-gray-50 text-gray-500 hover:bg-gray-100'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        @if($hasFilter)
                            <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                        @endif
                    </button>
                    <button type="submit"
                            class="shrink-0 w-10 h-10 flex items-center justify-center bg-rose-600 text-white rounded-xl shadow-lg shadow-rose-200/50 hover:bg-rose-700 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    <a href="{{ route('guru.teacher-status.create') }}"
                       class="hidden sm:inline-flex shrink-0 items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-linear-to-r from-rose-500 to-pink-600 rounded-xl shadow-lg shadow-rose-200/30 hover:from-rose-600 hover:to-pink-700 active:scale-95 transition-all uppercase tracking-widest">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Baru
                    </a>
                </div>

                {{-- Expandable Filter Panel --}}
                <div x-show="showFilter" x-collapse x-cloak
                     class="sm:absolute sm:top-full sm:right-0 sm:mt-2 sm:w-72 border-t sm:border-t-0 bg-gray-50/50 sm:bg-white px-3 sm:px-4 py-2.5 sm:py-3 sm:rounded-2xl sm:shadow-xl sm:border sm:border-gray-200 sm:z-50">
                    <div class="space-y-2.5">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <select name="type"
                                    class="appearance-none w-full pl-9 pr-8 py-2 bg-white sm:bg-gray-50 border border-gray-200 sm:border-0 rounded-xl focus:ring-2 focus:ring-rose-500 transition-all text-[13px] text-gray-700">
                                <option value="">Semua Tipe</option>
                                <option value="izin" {{ request('type') === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('type') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="tugas_luar" {{ request('type') === 'tugas_luar' ? 'selected' : '' }}>Tugas Luar</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <select name="status"
                                    class="appearance-none w-full pl-9 pr-8 py-2 bg-white sm:bg-gray-50 border border-gray-200 sm:border-0 rounded-xl focus:ring-2 focus:ring-rose-500 transition-all text-[13px] text-gray-700">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <select name="month"
                                        class="appearance-none w-full pl-8 pr-6 py-2 bg-white sm:bg-gray-50 border border-gray-200 sm:border-0 rounded-xl focus:ring-2 focus:ring-rose-500 transition-all text-[13px] text-gray-700">
                                    <option value="">Bulan</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <select name="year"
                                        class="appearance-none w-full pl-8 pr-6 py-2 bg-white sm:bg-gray-50 border border-gray-200 sm:border-0 rounded-xl focus:ring-2 focus:ring-rose-500 transition-all text-[13px] text-gray-700">
                                    <option value="">Tahun</option>
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 pt-1">
                            @if($hasFilter)
                                <a href="{{ route('guru.teacher-status.index') }}"
                                   class="flex-1 px-3 py-2 text-[11px] font-bold text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 active:scale-95 transition-all text-center">Reset</a>
                            @endif
                            <button type="submit"
                                    class="flex-1 px-3 py-2 text-[11px] font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700 active:scale-95 transition-all">Terapkan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick Stats (gaya dashboard: scroll horizontal di HP) --}}
    <div class="-mx-3 sm:mx-0 px-3 sm:px-0">
        <div class="flex sm:grid sm:grid-cols-4 gap-3 sm:gap-4 overflow-x-auto hide-scrollbar snap-x snap-mandatory pb-2 sm:pb-0 scrollbar-hide">
            <a href="{{ route('guru.teacher-status.index') }}"
               class="snap-center shrink-0 w-[130px] sm:w-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Pengajuan</p>
                <p class="text-xl sm:text-2xl font-black text-gray-900">{{ $summary['total'] }}</p>
            </a>
            <a href="{{ route('guru.teacher-status.index', ['status' => 'pending']) }}"
               class="snap-center shrink-0 w-[130px] sm:w-auto bg-amber-50/50 rounded-2xl border border-amber-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-amber-600/70 uppercase tracking-wider mb-1">Menunggu</p>
                <p class="text-xl sm:text-2xl font-black text-amber-600">{{ $summary['pending'] }}</p>
            </a>
            <a href="{{ route('guru.teacher-status.index', ['status' => 'approved']) }}"
               class="snap-center shrink-0 w-[130px] sm:w-auto bg-emerald-50/50 rounded-2xl border border-emerald-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-emerald-600/70 uppercase tracking-wider mb-1">Disetujui</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600">{{ $summary['approved'] }}</p>
            </a>
            <a href="{{ route('guru.teacher-status.index', ['status' => 'rejected']) }}"
               class="snap-center shrink-0 w-[130px] sm:w-auto bg-rose-50/50 rounded-2xl border border-rose-100 p-3.5 sm:p-4 flex flex-col justify-center">
                <p class="text-[9px] sm:text-[10px] font-bold text-rose-600/70 uppercase tracking-wider mb-1">Ditolak</p>
                <p class="text-xl sm:text-2xl font-black text-rose-600">{{ $summary['rejected'] }}</p>
            </a>
        </div>
        <p class="sm:hidden mt-1.5 text-center text-[9px] font-bold text-gray-300 uppercase tracking-widest">← Geser →</p>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-sm font-bold text-gray-900">Riwayat Pengajuan</h3>
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal</th>
                    <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Tipe</th>
                    <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                    <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Keterangan</th>
                    <th class="text-right px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($statuses as $s)
                @php $badgeClass = $badge[$s->status] ?? $badge['pending']; @endphp
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-bold text-gray-900 text-sm">{{ \Carbon\Carbon::parse($s->date)->translatedFormat('d F Y') }}</span>
                        @if($s->date_end && $s->date_end->toDateString() !== $s->date->toDateString())
                            <span class="text-[10px] text-gray-400 block">s.d. {{ \Carbon\Carbon::parse($s->date_end)->translatedFormat('d F Y') }}</span>
                        @else
                            <span class="text-[10px] text-gray-400 block">{{ \Carbon\Carbon::parse($s->date)->translatedFormat('l') }}</span>
                        @endif
                        @if($s->start_time && $s->end_time)
                            <span class="text-[10px] text-indigo-500 font-semibold block">{{ substr($s->start_time, 0, 5) }} – {{ substr($s->end_time, 0, 5) }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @php $typeClass = $typeBadge[$s->type] ?? $typeBadge['izin']; @endphp
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border {{ $typeClass[0] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $typeClass[1] }}"></span> {{ $typeClass[2] }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border {{ $badgeClass[0] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $badgeClass[1] }}"></span> {{ $badgeClass[2] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                        {{ $s->note ?? '-' }}
                        @if($s->attachment)
                            <a href="{{ route('guru.teacher-status.attachment', $s) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-rose-600 hover:underline font-semibold block mt-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7.586a2 2 0 00-2.828 0l-5.657 5.657a4 4 0 005.657 5.657l5.656-5.657a1 1 0 00-1.414-1.414l-5.657 5.657a2 2 0 01-2.828-2.829l5.657-5.657a4 4 0 015.657 5.657l-5.657 5.657"></path>
                                </svg>
                                Lampiran
                            </a>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($s->isPending() && \Carbon\Carbon::parse($s->date)->gte(\Carbon\Carbon::today()))
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('guru.teacher-status.show', $s) }}"
                                   class="px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-lg text-[10px] font-bold hover:bg-gray-50 transition-all uppercase tracking-widest">Detail</a>
                                <a href="{{ route('guru.teacher-status.edit', $s) }}"
                                   class="px-3 py-1.5 bg-white border border-rose-200 text-rose-600 rounded-lg text-[10px] font-bold hover:bg-rose-50 transition-all uppercase tracking-widest">Edit</a>
                                <form action="{{ route('guru.teacher-status.withdraw', $s) }}" method="POST"
                                      onsubmit="return confirm('Tarik pengajuan ini?')" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-white border border-amber-200 text-amber-600 rounded-lg text-[10px] font-bold hover:bg-amber-50 transition-all uppercase tracking-widest">Tarik</button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('guru.teacher-status.show', $s) }}"
                               class="inline-flex px-3 py-1.5 bg-white border border-gray-200 text-gray-500 rounded-lg text-[10px] font-bold hover:bg-gray-50 transition-all uppercase tracking-widest">Detail</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Belum ada pengajuan izin / sakit / tugas luar</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="md:hidden space-y-2.5">
        @forelse($statuses as $s)
        @php
            $badgeClass = $badge[$s->status] ?? $badge['pending'];
            $typeClass = $typeBadge[$s->type] ?? $typeBadge['izin'];
        @endphp
         <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
              x-data="{ open: false }">
            <button type="button" @click="open = !open"
                    class="w-full px-4 py-3.5 flex items-center gap-3 text-left active:bg-gray-50 transition-colors">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $typeClass[3] }}">
                    @if($s->type === 'izin')
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    @elseif($s->type === 'sakit')
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m8-3a8 8 0 11-16 0 8 8 0 0116 0z"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($s->date)->translatedFormat('d F Y') }}</p>
                    <p class="text-[10px] text-gray-400 font-medium">
                        {{ $s->date_end && $s->date_end->toDateString() !== $s->date->toDateString() ? 's.d. ' . \Carbon\Carbon::parse($s->date_end)->translatedFormat('d F Y') : \Carbon\Carbon::parse($s->date)->translatedFormat('l') }}
                    </p>
                </div>
                <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass[0] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $badgeClass[1] }}"></span>
                    {{ $badgeClass[2] }}
                </span>
                <svg class="w-4 h-4 text-gray-300 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="open" x-collapse x-cloak class="border-t border-gray-50 bg-gray-50/30 px-4 py-3 space-y-3">
                <div class="bg-white rounded-xl px-3.5 py-2.5 border border-gray-100">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Keterangan</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $s->note ?? '-' }}</p>
                    @if($s->attachment)
                        <a href="{{ route('guru.teacher-status.attachment', $s) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-rose-600 hover:underline font-semibold inline-block mt-1.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7.586a2 2 0 00-2.828 0l-5.657 5.657a4 4 0 005.657 5.657l5.656-5.657a1 1 0 00-1.414-1.414l-5.657 5.657a2 2 0 01-2.828-2.829l5.657-5.657a4 4 0 015.657 5.657l-5.657 5.657"></path>
                            </svg>
                            Lihat lampiran
                        </a>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('guru.teacher-status.show', $s) }}"
                       class="flex-1 px-3 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl text-[10px] font-bold hover:bg-gray-50 active:scale-95 transition-all text-center uppercase tracking-widest">
                        Detail
                    </a>
                    @if($s->isPending() && \Carbon\Carbon::parse($s->date)->gte(\Carbon\Carbon::today()))
                        <a href="{{ route('guru.teacher-status.edit', $s) }}"
                           class="flex-1 px-3 py-2 bg-white border border-rose-200 text-rose-600 rounded-xl text-[10px] font-bold hover:bg-rose-50 active:scale-95 transition-all text-center uppercase tracking-widest">
                            Edit
                        </a>
                        <form action="{{ route('guru.teacher-status.withdraw', $s) }}" method="POST"
                              onsubmit="return confirm('Tarik pengajuan ini?')" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full px-3 py-2 bg-white border border-amber-200 text-amber-600 rounded-xl text-[10px] font-bold hover:bg-amber-50 active:scale-95 transition-all text-center uppercase tracking-widest">
                                Tarik
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-800">Belum Ada Pengajuan</h3>
            <p class="text-xs text-gray-400 mt-1">Buat pengajuan izin, sakit, atau tugas luar pertama Anda.</p>
            <a href="{{ route('guru.teacher-status.create') }}"
               class="mt-4 inline-flex px-4 py-2 text-xs font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition-all">
                + Buat Pengajuan
            </a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($statuses->hasPages())
    <div class="flex justify-center pt-2">
        {{ $statuses->appends(request()->query())->links() }}
    </div>
    @endif

    {{-- FAB (mobile) --}}
    <a href="{{ route('guru.teacher-status.create') }}"
       class="sm:hidden fixed bottom-24 right-5 z-40 w-14 h-14 flex items-center justify-center bg-linear-to-br from-rose-500 to-pink-600 text-white rounded-2xl shadow-xl shadow-rose-500/30 active:scale-90 transition-transform"
       aria-label="Buat Pengajuan">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
    </a>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
@endpush
@endsection
